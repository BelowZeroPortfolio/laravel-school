<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\SchoolYear;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    /**
     * Display a listing of students.
     * Teachers see only their students, admins see all.
     * (Requirements 8.2, 8.3)
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = Student::query()->with('classes');

        // Role-based filtering (Requirements 8.2, 8.3)
        if ($user->isTeacher()) {
            // Teachers see only students in their classes (Requirement 8.2)
            $teacherClassIds = $user->getTeacherClassIds();
            $query->whereHas('classes', fn($q) => $q->whereIn('classes.id', $teacherClassIds));
        }
        // Admins and principals see all students (Requirement 8.3)

        // Apply optional filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('lrn', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('class_id')) {
            $query->whereHas('classes', fn($q) => $q->where('classes.id', $request->input('class_id')));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $students = $query->orderBy('last_name')->orderBy('first_name')->paginate(25);

        // Get classes for filter dropdown (role-based)
        $classes = $user->isTeacher()
            ? $user->classes()->active()->get()
            : ClassRoom::active()->get();

        return view('students.index', [
            'students' => $students,
            'classes' => $classes,
        ]);
    }

    /**
     * Show the form for creating a new student.
     */
    public function create(): View
    {
        $classes = ClassRoom::active()->with('schoolYear')->get();
        
        return view('students.create', [
            'classes' => $classes,
        ]);
    }

    /**
     * Store a newly created student.
     * (Requirement 8.1)
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        // LRN and student_id should be unique per school
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'min:2', 'max:50'],
            'last_name' => ['required', 'string', 'min:2', 'max:50'],
            'lrn' => [
                'required',
                'string',
                'size:12',
                'regex:/^[0-9]+$/',
                Rule::unique('students')->where(function ($query) use ($user) {
                    return $query->where('school_id', $user->school_id);
                }),
            ],
            'parent_name' => ['nullable', 'string', 'min:2', 'max:100'],
            'parent_phone' => ['nullable', 'string', 'size:11', 'regex:/^09[0-9]{9}$/'],
            'parent_email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
            'sms_enabled' => ['boolean'],
            'class_ids' => ['nullable', 'array'],
            'class_ids.*' => ['exists:classes,id'],
        ], [
            'first_name.min' => 'First name must be at least 2 characters.',
            'first_name.max' => 'First name must not exceed 50 characters.',
            'last_name.min' => 'Last name must be at least 2 characters.',
            'last_name.max' => 'Last name must not exceed 50 characters.',
            'lrn.size' => 'LRN must be exactly 12 digits.',
            'lrn.regex' => 'LRN must contain only numbers.',
            'parent_name.min' => 'Parent name must be at least 2 characters.',
            'parent_name.max' => 'Parent name must not exceed 100 characters.',
            'parent_phone.size' => 'Phone number must be exactly 11 digits.',
            'parent_phone.regex' => 'Phone number must be a valid Philippine mobile number (e.g., 09171234567).',
        ]);

        // Generate unique student_id (Requirement 8.1)
        $validated['student_id'] = $this->generateStudentId();
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['sms_enabled'] = $validated['sms_enabled'] ?? false;

        // school_id is auto-assigned by BelongsToSchool trait
        $student = Student::create($validated);

        // Enroll in classes if provided (Requirement 8.4)
        if (!empty($validated['class_ids'])) {
            $enrollmentData = [];
            foreach ($validated['class_ids'] as $classId) {
                $enrollmentData[$classId] = [
                    'enrolled_at' => now(),
                    'enrolled_by' => $request->user()->id,
                    'is_active' => true,
                    'enrollment_type' => 'regular',
                    'enrollment_status' => 'enrolled',
                ];
            }
            $student->classes()->attach($enrollmentData);
        }

        return redirect()->route('students.show', $student)
            ->with('success', 'Student created successfully.');
    }

    /**
     * Display the specified student.
     */
    public function show(Request $request, Student $student): View
    {
        $user = $request->user();

        // Check access for teachers (Requirement 8.2)
        if ($user->isTeacher()) {
            $teacherClassIds = $user->getTeacherClassIds();
            $studentClassIds = $student->classes()->pluck('classes.id');
            
            if ($teacherClassIds->intersect($studentClassIds)->isEmpty()) {
                abort(403, 'You do not have access to this student.');
            }
        }

        $student->load(['classes.teacher', 'classes.schoolYear', 'attendances' => function ($q) {
            $q->orderBy('attendance_date', 'desc')->limit(10);
        }]);

        return view('students.show', [
            'student' => $student,
        ]);
    }

    /**
     * Show the form for editing the specified student.
     */
    public function edit(Request $request, Student $student): View
    {
        $user = $request->user();

        // Check access for teachers (Requirement 8.2)
        if ($user->isTeacher()) {
            $teacherClassIds = $user->getTeacherClassIds();
            $studentClassIds = $student->classes()->pluck('classes.id');
            
            if ($teacherClassIds->intersect($studentClassIds)->isEmpty()) {
                abort(403, 'You do not have access to this student.');
            }
        }

        $classes = ClassRoom::active()->with('schoolYear')->get();
        $student->load('classes');

        return view('students.edit', [
            'student' => $student,
            'classes' => $classes,
        ]);
    }

    /**
     * Update the specified student.
     */
    public function update(Request $request, Student $student): RedirectResponse
    {
        $user = $request->user();

        // Check access for teachers (Requirement 8.2)
        if ($user->isTeacher()) {
            $teacherClassIds = $user->getTeacherClassIds();
            $studentClassIds = $student->classes()->pluck('classes.id');

            if ($teacherClassIds->intersect($studentClassIds)->isEmpty()) {
                abort(403, 'You do not have access to this student.');
            }
        }

        // LRN should be unique per school
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'min:2', 'max:50'],
            'last_name' => ['required', 'string', 'min:2', 'max:50'],
            'lrn' => [
                'required',
                'string',
                'size:12',
                'regex:/^[0-9]+$/',
                Rule::unique('students')->where(function ($query) use ($user) {
                    return $query->where('school_id', $user->school_id);
                })->ignore($student->id),
            ],
            'parent_name' => ['nullable', 'string', 'min:2', 'max:100'],
            'parent_phone' => ['nullable', 'string', 'size:11', 'regex:/^09[0-9]{9}$/'],
            'parent_email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
            'sms_enabled' => ['boolean'],
        ], [
            'first_name.min' => 'First name must be at least 2 characters.',
            'first_name.max' => 'First name must not exceed 50 characters.',
            'last_name.min' => 'Last name must be at least 2 characters.',
            'last_name.max' => 'Last name must not exceed 50 characters.',
            'lrn.size' => 'LRN must be exactly 12 digits.',
            'lrn.regex' => 'LRN must contain only numbers.',
            'parent_name.min' => 'Parent name must be at least 2 characters.',
            'parent_name.max' => 'Parent name must not exceed 100 characters.',
            'parent_phone.size' => 'Phone number must be exactly 11 digits.',
            'parent_phone.regex' => 'Phone number must be a valid Philippine mobile number (e.g., 09171234567).',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? false;
        $validated['sms_enabled'] = $validated['sms_enabled'] ?? false;

        $student->update($validated);

        return redirect()->route('students.show', $student)
            ->with('success', 'Student updated successfully.');
    }

    /**
     * Remove the specified student.
     */
    public function destroy(Student $student): RedirectResponse
    {
        // Soft-delete by deactivating instead of hard delete
        $student->update(['is_active' => false]);

        return redirect()->route('students.index')
            ->with('success', 'Student deactivated successfully.');
    }

    /**
     * Generate a unique student ID.
     */
    protected function generateStudentId(): string
    {
        do {
            $studentId = 'STU-' . strtoupper(Str::random(8));
        } while (Student::where('student_id', $studentId)->exists());

        return $studentId;
    }
}

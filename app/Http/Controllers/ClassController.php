<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClassController extends Controller
{
    /**
     * Display a listing of classes.
     * Teachers see only their classes.
     * (Requirement 9.3)
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = ClassRoom::query()->with(['teacher', 'schoolYear']);

        // Role-based filtering (Requirement 9.3)
        if ($user->isTeacher()) {
            // Teachers see only their classes
            $query->where('teacher_id', $user->id);
        }
        // Admins and principals see all classes

        // Apply optional filters
        if ($request->filled('school_year_id')) {
            $query->where('school_year_id', $request->input('school_year_id'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('grade_level')) {
            $query->where('grade_level', $request->input('grade_level'));
        }

        $classes = $query->orderBy('grade_level')->orderBy('section')->paginate(25);

        $schoolYears = SchoolYear::orderBy('start_date', 'desc')->get();

        return view('classes.index', [
            'classes' => $classes,
            'schoolYears' => $schoolYears,
        ]);
    }

    /**
     * Show the form for creating a new class.
     */
    public function create(): View
    {
        $teachers = User::teachers()->active()->orderBy('full_name')->get();
        $schoolYears = SchoolYear::orderBy('start_date', 'desc')->get();

        return view('classes.create', [
            'teachers' => $teachers,
            'schoolYears' => $schoolYears,
        ]);
    }

    /**
     * Store a newly created class.
     * (Requirements 9.1, 9.2)
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'grade_level' => ['required', 'string', 'max:50'],
            'section' => ['required', 'string', 'max:50'],
            'teacher_id' => ['required', 'exists:users,id'],
            'school_year_id' => ['required', 'exists:school_years,id'],
            'max_capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'is_active' => ['boolean'],
        ]);

        // Validate teacher role (Requirement 9.2)
        $teacher = User::find($validated['teacher_id']);
        if (!$teacher || !$teacher->isTeacher()) {
            return back()->withErrors(['teacher_id' => 'The selected user must be a teacher.'])
                ->withInput();
        }

        // Check uniqueness constraint (Requirement 9.1)
        $exists = ClassRoom::where('grade_level', $validated['grade_level'])
            ->where('section', $validated['section'])
            ->where('school_year_id', $validated['school_year_id'])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'section' => 'A class with this grade level and section already exists for this school year.',
            ])->withInput();
        }

        $validated['is_active'] = $validated['is_active'] ?? true;

        $class = ClassRoom::create($validated);

        return redirect()->route('classes.show', $class)
            ->with('success', 'Class created successfully.');
    }

    /**
     * Display the specified class.
     */
    public function show(Request $request, ClassRoom $class): View
    {
        $user = $request->user();

        // Check access for teachers (Requirement 9.3)
        if ($user->isTeacher() && $class->teacher_id !== $user->id) {
            abort(403, 'You do not have access to this class.');
        }

        $class->load(['teacher', 'schoolYear', 'students']);

        return view('classes.show', [
            'class' => $class,
        ]);
    }

    /**
     * Show the form for editing the specified class.
     */
    public function edit(Request $request, ClassRoom $class): View
    {
        $user = $request->user();

        // Only admins can edit classes
        if (!$user->isAdmin()) {
            abort(403, 'Only administrators can edit classes.');
        }

        $teachers = User::teachers()->active()->orderBy('full_name')->get();
        $schoolYears = SchoolYear::orderBy('start_date', 'desc')->get();

        return view('classes.edit', [
            'class' => $class,
            'teachers' => $teachers,
            'schoolYears' => $schoolYears,
        ]);
    }

    /**
     * Update the specified class.
     * (Requirements 9.1, 9.2)
     */
    public function update(Request $request, ClassRoom $class): RedirectResponse
    {
        $user = $request->user();

        // Only admins can update classes
        if (!$user->isAdmin()) {
            abort(403, 'Only administrators can update classes.');
        }

        $validated = $request->validate([
            'grade_level' => ['required', 'string', 'max:50'],
            'section' => ['required', 'string', 'max:50'],
            'teacher_id' => ['required', 'exists:users,id'],
            'school_year_id' => ['required', 'exists:school_years,id'],
            'max_capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'is_active' => ['boolean'],
        ]);

        // Validate teacher role (Requirement 9.2)
        $teacher = User::find($validated['teacher_id']);
        if (!$teacher || !$teacher->isTeacher()) {
            return back()->withErrors(['teacher_id' => 'The selected user must be a teacher.'])
                ->withInput();
        }

        // Check uniqueness constraint excluding current class (Requirement 9.1)
        $exists = ClassRoom::where('grade_level', $validated['grade_level'])
            ->where('section', $validated['section'])
            ->where('school_year_id', $validated['school_year_id'])
            ->where('id', '!=', $class->id)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'section' => 'A class with this grade level and section already exists for this school year.',
            ])->withInput();
        }

        $validated['is_active'] = $validated['is_active'] ?? false;

        $class->update($validated);

        return redirect()->route('classes.show', $class)
            ->with('success', 'Class updated successfully.');
    }

    /**
     * Deactivate the specified class.
     * (Requirement 9.4 - preserves historical records)
     */
    public function destroy(ClassRoom $class): RedirectResponse
    {
        // Soft-delete by deactivating instead of hard delete
        // This preserves historical attendance records (Requirement 9.4)
        $class->update(['is_active' => false]);

        return redirect()->route('classes.index')
            ->with('success', 'Class deactivated successfully.');
    }

    /**
     * Enroll a student in the class.
     * (Requirements 8.4, 8.5)
     */
    public function enrollStudent(Request $request, ClassRoom $class): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'enrollment_type' => ['required', 'in:regular,transferee,returnee'],
        ]);

        // Check capacity (Requirement 8.5)
        if ($class->isAtCapacity()) {
            return back()->withErrors(['student_id' => 'Class has reached maximum capacity.']);
        }

        // Check if already enrolled
        if ($class->students()->where('student_id', $validated['student_id'])->exists()) {
            return back()->withErrors(['student_id' => 'Student is already enrolled in this class.']);
        }

        // Enroll student (Requirement 8.4)
        $class->students()->attach($validated['student_id'], [
            'enrolled_at' => now(),
            'enrolled_by' => $request->user()->id,
            'is_active' => true,
            'enrollment_type' => $validated['enrollment_type'],
            'enrollment_status' => 'enrolled',
        ]);

        return back()->with('success', 'Student enrolled successfully.');
    }

    /**
     * Remove a student from the class.
     */
    public function unenrollStudent(Request $request, ClassRoom $class, Student $student): RedirectResponse
    {
        // Soft-unenroll by setting is_active to false
        $class->students()->updateExistingPivot($student->id, [
            'is_active' => false,
            'enrollment_status' => 'withdrawn',
        ]);

        return back()->with('success', 'Student removed from class.');
    }
}

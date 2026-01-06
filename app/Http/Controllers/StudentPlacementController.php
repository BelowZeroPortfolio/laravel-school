<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Services\StudentPlacementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentPlacementController extends Controller
{
    public function __construct(
        protected StudentPlacementService $placementService
    ) {}

    /**
     * Display students and their placements.
     * (Requirement 19.5)
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        // Get active school year or selected school year
        $schoolYearId = $request->input('school_year_id');
        $activeSchoolYear = $schoolYearId 
            ? SchoolYear::find($schoolYearId) 
            : SchoolYear::active()->first();

        // Build students query with placements
        $query = Student::query()
            ->with(['classes' => function ($q) use ($activeSchoolYear) {
                $q->with(['teacher', 'schoolYear']);
                if ($activeSchoolYear) {
                    $q->where('school_year_id', $activeSchoolYear->id);
                }
            }]);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('lrn', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        // Filter by class
        if ($request->filled('class_id')) {
            $query->whereHas('classes', fn($q) => $q->where('classes.id', $request->input('class_id')));
        }

        // Filter by enrollment status
        if ($request->filled('enrollment_status')) {
            $status = $request->input('enrollment_status');
            if ($status === 'unassigned') {
                // Students without any active enrollment in the selected school year
                $query->whereDoesntHave('classes', function ($q) use ($activeSchoolYear) {
                    $q->wherePivot('is_active', true);
                    if ($activeSchoolYear) {
                        $q->where('school_year_id', $activeSchoolYear->id);
                    }
                });
            } else {
                $query->whereHas('classes', function ($q) use ($status, $activeSchoolYear) {
                    $q->wherePivot('enrollment_status', $status);
                    if ($activeSchoolYear) {
                        $q->where('school_year_id', $activeSchoolYear->id);
                    }
                });
            }
        }

        // Only show active students by default
        if (!$request->filled('show_inactive')) {
            $query->where('is_active', true);
        }

        $students = $query->orderBy('last_name')->orderBy('first_name')->paginate(25);

        // Get classes for the active school year
        $classes = ClassRoom::query()
            ->with('teacher')
            ->when($activeSchoolYear, fn($q) => $q->where('school_year_id', $activeSchoolYear->id))
            ->active()
            ->orderBy('grade_level')
            ->orderBy('section')
            ->get();

        $schoolYears = SchoolYear::orderBy('start_date', 'desc')->get();

        return view('student-placements.index', [
            'students' => $students,
            'classes' => $classes,
            'schoolYears' => $schoolYears,
            'activeSchoolYear' => $activeSchoolYear,
        ]);
    }

    /**
     * Show placement history for a specific student.
     * (Requirement 19.5)
     */
    public function show(Student $student): View
    {
        $history = $this->placementService->getPlacementHistory($student->id);

        return view('student-placements.show', [
            'student' => $student,
            'history' => $history,
        ]);
    }

    /**
     * Transfer a student from one class to another.
     * (Requirement 19.1)
     */
    public function transfer(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'from_class_id' => ['required', 'exists:classes,id'],
            'to_class_id' => ['required', 'exists:classes,id', 'different:from_class_id'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $result = $this->placementService->transferStudent(
            $validated['student_id'],
            $validated['from_class_id'],
            $validated['to_class_id'],
            $request->user()->id,
            $validated['reason'] ?? null
        );

        if (!$result) {
            return back()->withErrors(['transfer' => 'Failed to transfer student. Please check that the student is enrolled in the source class and the target class has capacity.'])
                ->withInput();
        }

        return back()->with('success', 'Student transferred successfully.');
    }

    /**
     * Bulk place multiple students in a class.
     * (Requirement 19.3)
     */
    public function bulkPlace(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['exists:students,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'enrollment_type' => ['required', 'in:regular,transferee,returnee'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $successCount = $this->placementService->bulkPlaceStudents(
            $validated['student_ids'],
            $validated['class_id'],
            $validated['enrollment_type'],
            $request->user()->id,
            $validated['reason'] ?? null
        );

        $totalRequested = count($validated['student_ids']);

        if ($successCount === 0) {
            return back()->withErrors(['bulk_place' => 'Failed to place any students. The class may be at capacity or students may already be enrolled.'])
                ->withInput();
        }

        if ($successCount < $totalRequested) {
            return back()->with('warning', "Placed {$successCount} of {$totalRequested} students. Some students could not be placed (class at capacity or already enrolled).");
        }

        return back()->with('success', "Successfully placed {$successCount} students in the class.");
    }

    /**
     * Place a single student in a class.
     * (Requirement 19.2)
     */
    public function place(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'enrollment_type' => ['required', 'in:regular,transferee,returnee'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $result = $this->placementService->placeStudent(
            $validated['student_id'],
            $validated['class_id'],
            $validated['enrollment_type'],
            $request->user()->id,
            $validated['reason'] ?? null
        );

        if (!$result) {
            return back()->withErrors(['place' => 'Failed to place student. The class may be at capacity or the student may already be enrolled.'])
                ->withInput();
        }

        return back()->with('success', 'Student placed successfully.');
    }
}

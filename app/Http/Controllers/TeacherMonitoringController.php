<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use App\Models\TeacherAttendance;
use App\Models\User;
use App\Services\TeacherAttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherMonitoringController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected TeacherAttendanceService $teacherAttendanceService
    ) {}

    /**
     * Display teacher attendance monitoring dashboard.
     * Read-only for principals.
     * (Requirements 11.1, 11.2, 11.3)
     */
    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $filters = $this->buildFilters($request);

        // Get attendance records with filters (Requirement 11.2)
        $attendances = $this->teacherAttendanceService->getAttendanceRecords($filters);

        // Filter by school (multi-tenancy)
        if ($currentUser->school_id) {
            $attendances = $attendances->filter(function ($attendance) use ($currentUser) {
                return $attendance->teacher && $attendance->teacher->school_id === $currentUser->school_id;
            });
        }

        // Calculate statistics (Requirement 11.3)
        $stats = $this->calculateStatistics($attendances);

        // Get filter options - scoped by school
        $teachers = User::teachers()
            ->active()
            ->when($currentUser->school_id, fn($q) => $q->where('school_id', $currentUser->school_id))
            ->orderBy('full_name')
            ->get();

        // SchoolYear is auto-scoped by BelongsToSchool trait
        $schoolYears = SchoolYear::orderBy('start_date', 'desc')->get();

        return view('teacher-monitoring.index', [
            'attendances' => $attendances,
            'stats' => $stats,
            'teachers' => $teachers,
            'schoolYears' => $schoolYears,
            'filters' => $filters,
            'isReadOnly' => $currentUser->isPrincipal(), // Requirement 11.1
        ]);
    }

    /**
     * Display today's teacher attendance summary.
     * (Requirements 11.1, 11.3)
     */
    public function today(Request $request): View
    {
        $currentUser = $request->user();
        $schoolYearId = $request->input('school_year_id');

        if (!$schoolYearId) {
            $activeSchoolYear = SchoolYear::active()->first();
            $schoolYearId = $activeSchoolYear?->id;
        }

        $attendances = TeacherAttendance::with(['teacher', 'timeRule'])
            ->today()
            ->when($schoolYearId, fn($q) => $q->forSchoolYear($schoolYearId))
            ->whereHas('teacher', function ($q) use ($currentUser) {
                if ($currentUser->school_id) {
                    $q->where('school_id', $currentUser->school_id);
                }
            })
            ->get();

        // Get all active teachers from same school to identify those without records
        $allTeachers = User::teachers()
            ->active()
            ->when($currentUser->school_id, fn($q) => $q->where('school_id', $currentUser->school_id))
            ->get();

        $teachersWithAttendance = $attendances->pluck('teacher_id');
        $teachersWithoutAttendance = $allTeachers->filter(
            fn($t) => !$teachersWithAttendance->contains($t->id)
        );

        // Calculate statistics (Requirement 11.3)
        $stats = $this->calculateStatistics($attendances);
        $stats['not_logged_in'] = $teachersWithoutAttendance->count();
        $stats['total_teachers'] = $allTeachers->count();

        $schoolYears = SchoolYear::orderBy('start_date', 'desc')->get();

        return view('teacher-monitoring.today', [
            'attendances' => $attendances,
            'teachersWithoutAttendance' => $teachersWithoutAttendance,
            'stats' => $stats,
            'schoolYears' => $schoolYears,
            'selectedSchoolYearId' => $schoolYearId,
            'isReadOnly' => $currentUser->isPrincipal(), // Requirement 11.1
        ]);
    }

    /**
     * Display detailed view for a specific teacher.
     * (Requirement 11.1 - read-only)
     */
    public function show(Request $request, User $teacher): View
    {
        $currentUser = $request->user();

        // Ensure the user is a teacher
        if (!$teacher->isTeacher()) {
            abort(404, 'Teacher not found.');
        }

        // Ensure teacher belongs to same school (multi-tenancy)
        if ($currentUser->school_id && $teacher->school_id !== $currentUser->school_id) {
            abort(403, 'You do not have access to this teacher.');
        }

        $schoolYearId = $request->input('school_year_id');

        if (!$schoolYearId) {
            $activeSchoolYear = SchoolYear::active()->first();
            $schoolYearId = $activeSchoolYear?->id;
        }

        // Get attendance history
        $attendances = TeacherAttendance::with(['schoolYear', 'timeRule'])
            ->where('teacher_id', $teacher->id)
            ->when($schoolYearId, fn($q) => $q->forSchoolYear($schoolYearId))
            ->orderBy('attendance_date', 'desc')
            ->paginate(30);

        // Get summary statistics
        $summary = $this->teacherAttendanceService->getSummary($teacher->id, $schoolYearId);

        $schoolYears = SchoolYear::orderBy('start_date', 'desc')->get();

        return view('teacher-monitoring.show', [
            'teacher' => $teacher,
            'attendances' => $attendances,
            'summary' => $summary,
            'schoolYears' => $schoolYears,
            'selectedSchoolYearId' => $schoolYearId,
            'isReadOnly' => $currentUser->isPrincipal(), // Requirement 11.1
        ]);
    }

    /**
     * Build filters from request.
     * (Requirement 11.2)
     */
    protected function buildFilters(Request $request): array
    {
        $filters = [];

        if ($request->filled('teacher_id')) {
            $filters['teacher_id'] = $request->input('teacher_id');
        }

        if ($request->filled('school_year_id')) {
            $filters['school_year_id'] = $request->input('school_year_id');
        } else {
            $activeSchoolYear = SchoolYear::active()->first();
            if ($activeSchoolYear) {
                $filters['school_year_id'] = $activeSchoolYear->id;
            }
        }

        if ($request->filled('date_from')) {
            $filters['date_from'] = $request->input('date_from');
        }

        if ($request->filled('date_to')) {
            $filters['date_to'] = $request->input('date_to');
        }

        if ($request->filled('status')) {
            $filters['status'] = $request->input('status');
        }

        return $filters;
    }

    /**
     * Calculate attendance statistics.
     * (Requirement 11.3)
     */
    protected function calculateStatistics($attendances): array
    {
        return [
            'total' => $attendances->count(),
            'confirmed' => $attendances->where('attendance_status', 'confirmed')->count(),
            'late' => $attendances->where('attendance_status', 'late')->count(),
            'pending' => $attendances->where('attendance_status', 'pending')->count(),
            'absent' => $attendances->where('attendance_status', 'absent')->count(),
            'no_scan' => $attendances->where('attendance_status', 'no_scan')->count(),
        ];
    }
}

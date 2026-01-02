<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Services\StudentAttendanceService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected StudentAttendanceService $studentAttendanceService
    ) {}

    /**
     * Display a listing of attendance records.
     * (Requirements 18.1, 18.2, 18.3)
     * 
     * - Displays today's attendance records by default (18.1)
     * - Supports filtering by date, class, status, and student name (18.2)
     * - Teachers see only students from their classes (18.3)
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        // Default to today's date (Requirement 18.1)
        $date = $request->input('date', Carbon::today()->toDateString());
        $schoolYearId = $request->input('school_year_id');
        $classId = $request->input('class_id');
        $status = $request->input('status');
        $search = $request->input('search'); // Student name search (Requirement 18.2)

        // Get active school year if not specified
        if (!$schoolYearId) {
            $activeSchoolYear = SchoolYear::active()->first();
            $schoolYearId = $activeSchoolYear?->id;
        }

        // Build query with role-based filtering
        $query = Attendance::with(['student', 'schoolYear', 'recorder'])
            ->whereDate('attendance_date', $date);

        if ($schoolYearId) {
            $query->forSchoolYear($schoolYearId);
        }

        // Role-based filtering (Requirement 18.3)
        if ($user->isTeacher()) {
            // Teachers see only attendance for students in their classes
            $teacherClassIds = $user->classes()->pluck('id');
            $query->whereHas('student.classes', fn($q) => $q->whereIn('classes.id', $teacherClassIds));
        }

        if ($classId) {
            $query->forClass($classId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        // Student name search (Requirement 18.2)
        if ($search) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('first_name', 'like', "%{$search}%")
                       ->orWhere('last_name', 'like', "%{$search}%")
                       ->orWhere('lrn', 'like', "%{$search}%")
                       ->orWhere('student_id', 'like', "%{$search}%");
                });
            });
        }

        $attendances = $query->orderBy('check_in_time', 'desc')->paginate(50);

        // Get classes for filter dropdown (role-based)
        $classes = $user->isTeacher()
            ? $user->classes()->active()->get()
            : ClassRoom::active()->get();

        $schoolYears = SchoolYear::orderBy('start_date', 'desc')->get();

        // Calculate statistics for the filtered date
        $statsQuery = Attendance::whereDate('attendance_date', $date)
            ->when($schoolYearId, fn($q) => $q->forSchoolYear($schoolYearId));
        
        // Apply role-based filtering to stats as well
        if ($user->isTeacher()) {
            $teacherClassIds = $user->classes()->pluck('id');
            $statsQuery->whereHas('student.classes', fn($q) => $q->whereIn('classes.id', $teacherClassIds));
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'present' => (clone $statsQuery)->where('status', 'present')->count(),
            'late' => (clone $statsQuery)->where('status', 'late')->count(),
            'absent' => (clone $statsQuery)->where('status', 'absent')->count(),
        ];

        return view('attendance.index', [
            'attendances' => $attendances,
            'classes' => $classes,
            'schoolYears' => $schoolYears,
            'selectedDate' => $date,
            'selectedSchoolYearId' => $schoolYearId,
            'selectedClassId' => $classId,
            'selectedStatus' => $status,
            'search' => $search,
            'stats' => $stats,
        ]);
    }

    /**
     * Display attendance for a specific date.
     */
    public function byDate(Request $request, string $date): View
    {
        $request->merge(['date' => $date]);
        return $this->index($request);
    }

    /**
     * Display attendance summary/report.
     */
    public function report(Request $request): View
    {
        $user = $request->user();
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $schoolYearId = $request->input('school_year_id');
        $classId = $request->input('class_id');

        // Get active school year if not specified
        if (!$schoolYearId) {
            $activeSchoolYear = SchoolYear::active()->first();
            $schoolYearId = $activeSchoolYear?->id;
        }

        // Build query
        $query = Attendance::query()
            ->whereBetween('attendance_date', [$startDate, $endDate]);

        if ($schoolYearId) {
            $query->forSchoolYear($schoolYearId);
        }

        // Role-based filtering
        if ($user->isTeacher()) {
            $teacherClassIds = $user->classes()->pluck('id');
            $query->whereHas('student.classes', fn($q) => $q->whereIn('classes.id', $teacherClassIds));
        }

        if ($classId) {
            $query->forClass($classId);
        }

        // Get summary statistics
        $summary = [
            'total_records' => (clone $query)->count(),
            'present' => (clone $query)->where('status', 'present')->count(),
            'late' => (clone $query)->where('status', 'late')->count(),
            'absent' => (clone $query)->where('status', 'absent')->count(),
            'unique_students' => (clone $query)->distinct('student_id')->count('student_id'),
        ];

        // Get daily breakdown
        $dailyBreakdown = (clone $query)
            ->selectRaw('attendance_date, status, COUNT(*) as count')
            ->groupBy('attendance_date', 'status')
            ->orderBy('attendance_date')
            ->get()
            ->groupBy('attendance_date');

        // Get classes for filter dropdown (role-based)
        $classes = $user->isTeacher()
            ? $user->classes()->active()->get()
            : ClassRoom::active()->get();

        $schoolYears = SchoolYear::orderBy('start_date', 'desc')->get();

        return view('attendance.report', [
            'summary' => $summary,
            'dailyBreakdown' => $dailyBreakdown,
            'classes' => $classes,
            'schoolYears' => $schoolYears,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedSchoolYearId' => $schoolYearId,
            'selectedClassId' => $classId,
        ]);
    }

    /**
     * Show form for manual attendance marking.
     * (Requirement 18.4)
     */
    public function create(Request $request): View
    {
        $user = $request->user();
        $date = $request->input('date', Carbon::today()->toDateString());
        $classId = $request->input('class_id');

        // Get classes for dropdown (role-based)
        $classes = $user->isTeacher()
            ? $user->classes()->active()->get()
            : ClassRoom::active()->with('teacher')->get();

        // Get students for the selected class
        $students = collect();
        if ($classId) {
            $class = ClassRoom::with(['students' => function ($q) {
                $q->wherePivot('is_active', true)->orderBy('last_name')->orderBy('first_name');
            }])->find($classId);
            
            if ($class) {
                // Check authorization for teachers
                if ($user->isTeacher() && $class->teacher_id !== $user->id) {
                    abort(403, 'You can only mark attendance for your own classes.');
                }
                $students = $class->students;
            }
        }

        // Get existing attendance for the date to show who already has records
        $existingAttendance = [];
        if ($classId && $students->isNotEmpty()) {
            $existingAttendance = Attendance::whereDate('attendance_date', $date)
                ->whereIn('student_id', $students->pluck('id'))
                ->pluck('student_id')
                ->toArray();
        }

        $schoolYears = SchoolYear::orderBy('start_date', 'desc')->get();
        $activeSchoolYear = SchoolYear::active()->first();

        return view('attendance.create', [
            'classes' => $classes,
            'students' => $students,
            'selectedDate' => $date,
            'selectedClassId' => $classId,
            'existingAttendance' => $existingAttendance,
            'schoolYears' => $schoolYears,
            'activeSchoolYear' => $activeSchoolYear,
        ]);
    }

    /**
     * Store a manually created attendance record.
     * (Requirement 18.4 - records admin's user_id as recorded_by)
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'attendance_date' => 'required|date',
            'status' => 'required|in:present,late,absent',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ]);

        // Get active school year
        $activeSchoolYear = SchoolYear::active()->first();
        if (!$activeSchoolYear) {
            return back()->with('error', 'No active school year found.');
        }

        // Check if school year is locked
        if ($activeSchoolYear->is_locked) {
            return back()->with('error', 'Cannot modify attendance for a locked school year.');
        }

        // Check for existing attendance record
        $existingAttendance = Attendance::where('student_id', $validated['student_id'])
            ->whereDate('attendance_date', $validated['attendance_date'])
            ->first();

        if ($existingAttendance) {
            return back()->with('error', 'Attendance record already exists for this student on this date.');
        }

        // Verify teacher can only mark attendance for their students
        if ($user->isTeacher()) {
            $student = Student::find($validated['student_id']);
            $teacherClassIds = $user->classes()->pluck('id');
            $studentInTeacherClass = $student->classes()
                ->whereIn('classes.id', $teacherClassIds)
                ->exists();
            
            if (!$studentInTeacherClass) {
                return back()->with('error', 'You can only mark attendance for students in your classes.');
            }
        }

        // Build check_in_time and check_out_time with the attendance date
        $checkInTime = null;
        $checkOutTime = null;
        
        if ($validated['check_in_time']) {
            $checkInTime = Carbon::parse($validated['attendance_date'] . ' ' . $validated['check_in_time']);
        }
        
        if ($validated['check_out_time']) {
            $checkOutTime = Carbon::parse($validated['attendance_date'] . ' ' . $validated['check_out_time']);
        }

        // Create attendance record with audit trail (Requirement 18.4)
        Attendance::create([
            'student_id' => $validated['student_id'],
            'school_year_id' => $activeSchoolYear->id,
            'attendance_date' => $validated['attendance_date'],
            'check_in_time' => $checkInTime,
            'check_out_time' => $checkOutTime,
            'status' => $validated['status'],
            'recorded_by' => $user->id, // Audit trail - records the admin/teacher's user_id
            'notes' => $validated['notes'] ?? 'Manually recorded',
        ]);

        return redirect()
            ->route('attendance.index', ['date' => $validated['attendance_date']])
            ->with('success', 'Attendance record created successfully.');
    }

    /**
     * Update an existing attendance record.
     * (Requirement 18.4 - records admin's user_id as recorded_by)
     */
    public function update(Request $request, Attendance $attendance): RedirectResponse
    {
        $user = $request->user();

        // Check if school year is locked
        if ($attendance->schoolYear && $attendance->schoolYear->is_locked) {
            return back()->with('error', 'Cannot modify attendance for a locked school year.');
        }

        // Verify teacher can only update attendance for their students
        if ($user->isTeacher()) {
            $teacherClassIds = $user->classes()->pluck('id');
            $studentInTeacherClass = $attendance->student->classes()
                ->whereIn('classes.id', $teacherClassIds)
                ->exists();
            
            if (!$studentInTeacherClass) {
                return back()->with('error', 'You can only update attendance for students in your classes.');
            }
        }

        $validated = $request->validate([
            'status' => 'required|in:present,late,absent',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ]);

        // Build check_in_time and check_out_time with the attendance date
        $checkInTime = null;
        $checkOutTime = null;
        
        if ($validated['check_in_time']) {
            $checkInTime = Carbon::parse($attendance->attendance_date->toDateString() . ' ' . $validated['check_in_time']);
        }
        
        if ($validated['check_out_time']) {
            $checkOutTime = Carbon::parse($attendance->attendance_date->toDateString() . ' ' . $validated['check_out_time']);
        }

        // Update attendance record with audit trail (Requirement 18.4)
        $attendance->update([
            'check_in_time' => $checkInTime,
            'check_out_time' => $checkOutTime,
            'status' => $validated['status'],
            'recorded_by' => $user->id, // Audit trail - records the admin/teacher's user_id
            'notes' => $validated['notes'] ?? $attendance->notes,
        ]);

        return back()->with('success', 'Attendance record updated successfully.');
    }

    /**
     * Display attendance history for a specific student.
     * (Requirement 18.5 - displays check_in_time, check_out_time, and status)
     */
    public function history(Request $request, Student $student): View
    {
        $user = $request->user();

        // Verify teacher can only view history for their students
        if ($user->isTeacher()) {
            $teacherClassIds = $user->classes()->pluck('id');
            $studentInTeacherClass = $student->classes()
                ->whereIn('classes.id', $teacherClassIds)
                ->exists();
            
            if (!$studentInTeacherClass) {
                abort(403, 'You can only view attendance history for students in your classes.');
            }
        }

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $schoolYearId = $request->input('school_year_id');

        // Get active school year if not specified
        if (!$schoolYearId) {
            $activeSchoolYear = SchoolYear::active()->first();
            $schoolYearId = $activeSchoolYear?->id;
        }

        // Build query for attendance history
        $query = Attendance::with(['schoolYear', 'recorder'])
            ->where('student_id', $student->id)
            ->whereBetween('attendance_date', [$startDate, $endDate]);

        if ($schoolYearId) {
            $query->forSchoolYear($schoolYearId);
        }

        // Get attendance records with full details (Requirement 18.5)
        $attendances = $query->orderBy('attendance_date', 'desc')->paginate(30);

        // Calculate statistics for the period
        $statsQuery = Attendance::where('student_id', $student->id)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->when($schoolYearId, fn($q) => $q->forSchoolYear($schoolYearId));

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'present' => (clone $statsQuery)->where('status', 'present')->count(),
            'late' => (clone $statsQuery)->where('status', 'late')->count(),
            'absent' => (clone $statsQuery)->where('status', 'absent')->count(),
        ];

        // Calculate attendance rate
        $stats['attendance_rate'] = $stats['total'] > 0 
            ? round((($stats['present'] + $stats['late']) / $stats['total']) * 100, 1) 
            : 0;

        $schoolYears = SchoolYear::orderBy('start_date', 'desc')->get();

        return view('attendance.history', [
            'student' => $student,
            'attendances' => $attendances,
            'stats' => $stats,
            'schoolYears' => $schoolYears,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedSchoolYearId' => $schoolYearId,
        ]);
    }
}

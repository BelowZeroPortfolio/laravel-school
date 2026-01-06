<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\TeacherAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the role-appropriate dashboard.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            return redirect()->route('super-admin.dashboard');
        }

        $activeSchoolYear = SchoolYear::active()->first();

        $data = [
            'user' => $user,
            'activeSchoolYear' => $activeSchoolYear,
        ];

        if ($user->isAdmin()) {
            $data = array_merge($data, $this->getAdminDashboardData($activeSchoolYear));
        } elseif ($user->isPrincipal()) {
            $data = array_merge($data, $this->getPrincipalDashboardData($activeSchoolYear));
        } else {
            $data = array_merge($data, $this->getTeacherDashboardData($user, $activeSchoolYear));
        }

        return view('dashboard', $data);
    }

    /**
     * Get real-time stats via AJAX (only frequently changing data).
     */
    public function liveStats(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return response()->json($this->getAdminLiveStats());
        } elseif ($user->isPrincipal()) {
            return response()->json($this->getPrincipalLiveStats());
        } else {
            return response()->json($this->getTeacherLiveStats($user));
        }
    }

    /**
     * Get admin-specific dashboard data.
     */
    protected function getAdminDashboardData(?SchoolYear $schoolYear): array
    {
        $user = auth()->user();
        $schoolYearId = $schoolYear?->id;

        $teacherCount = User::teachers()
            ->active()
            ->where('school_id', $user->school_id)
            ->count();

        $totalStudents = Student::active()->count();
        $totalClasses = $schoolYearId
            ? ClassRoom::where('school_year_id', $schoolYearId)->active()->count()
            : 0;

        // Weekly attendance trend (last 7 days)
        $weeklyTrend = $this->getWeeklyAttendanceTrend($schoolYearId);

        // Attendance by status today
        $todayStats = $this->getTodayAttendanceStats($schoolYearId);

        // Top classes by attendance rate
        $topClasses = $this->getTopClassesByAttendance($schoolYearId, 5);

        // Recent scans (last 10)
        $recentScans = $this->getRecentScans(10);

        // NEW: Additional analytics
        $attendanceByGrade = $this->getAttendanceByGradeLevel($schoolYearId);
        $hourlyDistribution = $this->getHourlyDistribution();
        $weekComparison = $this->getWeekOverWeekComparison($schoolYearId);
        $bottomClasses = $this->getBottomClassesByAttendance($schoolYearId, 5);
        $monthlyTrend = $this->getMonthlyAttendanceTrend($schoolYearId);
        $absentStudentsCount = $totalStudents - $todayStats['total'];

        return [
            'totalStudents' => $totalStudents,
            'totalTeachers' => $teacherCount,
            'totalClasses' => $totalClasses,
            'todayAttendance' => $todayStats['total'],
            'todayPresent' => $todayStats['present'],
            'todayLate' => $todayStats['late'],
            'todayAbsent' => $absentStudentsCount,
            'attendanceRate' => $totalStudents > 0 
                ? round(($todayStats['total'] / $totalStudents) * 100, 1) 
                : 0,
            'todayTeacherAttendance' => TeacherAttendance::today()->count(),
            'pendingTeachers' => TeacherAttendance::today()->pending()->count(),
            'lateTeachers' => TeacherAttendance::today()->late()->count(),
            'confirmedTeachers' => TeacherAttendance::today()->where('attendance_status', 'confirmed')->count(),
            'weeklyTrend' => $weeklyTrend,
            'topClasses' => $topClasses,
            'recentScans' => $recentScans,
            // New analytics data
            'attendanceByGrade' => $attendanceByGrade,
            'hourlyDistribution' => $hourlyDistribution,
            'weekComparison' => $weekComparison,
            'bottomClasses' => $bottomClasses,
            'monthlyTrend' => $monthlyTrend,
        ];
    }

    /**
     * Get admin live stats (frequently updating data only).
     */
    protected function getAdminLiveStats(): array
    {
        $todayStats = $this->getTodayAttendanceStats(null);
        $totalStudents = Student::active()->count();
        $hourlyDistribution = $this->getHourlyDistribution();

        return [
            'todayAttendance' => $todayStats['total'],
            'todayPresent' => $todayStats['present'],
            'todayLate' => $todayStats['late'],
            'todayAbsent' => $totalStudents - $todayStats['total'],
            'attendanceRate' => $totalStudents > 0 
                ? round(($todayStats['total'] / $totalStudents) * 100, 1) 
                : 0,
            'totalStudents' => $totalStudents,
            'todayTeacherAttendance' => TeacherAttendance::today()->count(),
            'pendingTeachers' => TeacherAttendance::today()->pending()->count(),
            'lateTeachers' => TeacherAttendance::today()->late()->count(),
            'confirmedTeachers' => TeacherAttendance::today()->where('attendance_status', 'confirmed')->count(),
            'recentScans' => $this->getRecentScans(5),
            'hourlyDistribution' => $hourlyDistribution,
        ];
    }

    /**
     * Get attendance breakdown by grade level.
     */
    protected function getAttendanceByGradeLevel(?int $schoolYearId): array
    {
        $grades = ClassRoom::when($schoolYearId, fn ($q) => $q->where('school_year_id', $schoolYearId))
            ->active()
            ->select('grade_level')
            ->distinct()
            ->orderBy('grade_level')
            ->pluck('grade_level');

        $result = [];
        foreach ($grades as $grade) {
            $classIds = ClassRoom::when($schoolYearId, fn ($q) => $q->where('school_year_id', $schoolYearId))
                ->where('grade_level', $grade)
                ->active()
                ->pluck('id');

            $enrolled = Student::whereHas('classes', fn ($q) => $q->whereIn('classes.id', $classIds)->where('student_classes.is_active', true))
                ->active()
                ->count();

            $attended = Attendance::today()
                ->whereHas('student.classes', fn ($q) => $q->whereIn('classes.id', $classIds))
                ->count();

            $result[] = [
                'grade' => $grade,
                'enrolled' => $enrolled,
                'attended' => $attended,
                'rate' => $enrolled > 0 ? round(($attended / $enrolled) * 100, 1) : 0,
            ];
        }

        return $result;
    }

    /**
     * Get hourly scan distribution for today.
     */
    protected function getHourlyDistribution(): array
    {
        $hours = [];
        for ($h = 5; $h <= 12; $h++) {
            $count = Attendance::today()
                ->whereRaw('HOUR(check_in_time) = ?', [$h])
                ->count();
            $hours[] = [
                'hour' => $h,
                'label' => Carbon::createFromTime($h)->format('g A'),
                'count' => $count,
            ];
        }

        return $hours;
    }

    /**
     * Get week-over-week comparison.
     */
    protected function getWeekOverWeekComparison(?int $schoolYearId): array
    {
        $thisWeekStart = Carbon::now()->startOfWeek();
        $lastWeekStart = Carbon::now()->subWeek()->startOfWeek();
        $lastWeekEnd = Carbon::now()->subWeek()->endOfWeek();

        $thisWeekCount = Attendance::whereBetween('attendance_date', [$thisWeekStart, Carbon::now()])
            ->when($schoolYearId, fn ($q) => $q->where('school_year_id', $schoolYearId))
            ->count();

        $lastWeekCount = Attendance::whereBetween('attendance_date', [$lastWeekStart, $lastWeekEnd])
            ->when($schoolYearId, fn ($q) => $q->where('school_year_id', $schoolYearId))
            ->count();

        $change = $lastWeekCount > 0 
            ? round((($thisWeekCount - $lastWeekCount) / $lastWeekCount) * 100, 1) 
            : 0;

        return [
            'thisWeek' => $thisWeekCount,
            'lastWeek' => $lastWeekCount,
            'change' => $change,
            'trend' => $change >= 0 ? 'up' : 'down',
        ];
    }

    /**
     * Get bottom classes by attendance (needs attention).
     */
    protected function getBottomClassesByAttendance(?int $schoolYearId, int $limit = 5): array
    {
        $classes = ClassRoom::with('teacher')
            ->when($schoolYearId, fn ($q) => $q->where('school_year_id', $schoolYearId))
            ->active()
            ->withCount(['students' => fn ($q) => $q->where('student_classes.is_active', true)])
            ->having('students_count', '>', 0)
            ->get();

        $classStats = $classes->map(function ($class) {
            $enrolled = $class->students_count;
            $attended = Attendance::today()
                ->whereHas('student.classes', fn ($q) => $q->where('classes.id', $class->id))
                ->count();

            return [
                'id' => $class->id,
                'name' => "Grade {$class->grade_level} - {$class->section}",
                'teacher' => $class->teacher?->full_name ?? 'Unassigned',
                'enrolled' => $enrolled,
                'attended' => $attended,
                'rate' => $enrolled > 0 ? round(($attended / $enrolled) * 100, 1) : 0,
            ];
        })->sortBy('rate')->take($limit)->values()->toArray();

        return $classStats;
    }

    /**
     * Get monthly attendance trend (last 30 days).
     */
    protected function getMonthlyAttendanceTrend(?int $schoolYearId): array
    {
        $trend = [];
        $totalStudents = Student::active()->count();

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            if ($date->isWeekend()) continue;

            $count = Attendance::whereDate('attendance_date', $date)
                ->when($schoolYearId, fn ($q) => $q->where('school_year_id', $schoolYearId))
                ->count();

            $trend[] = [
                'date' => $date->format('M d'),
                'count' => $count,
                'rate' => $totalStudents > 0 ? round(($count / $totalStudents) * 100, 1) : 0,
            ];
        }

        return $trend;
    }

    /**
     * Get principal-specific dashboard data.
     */
    protected function getPrincipalDashboardData(?SchoolYear $schoolYear): array
    {
        $user = auth()->user();

        $teacherCount = User::teachers()
            ->active()
            ->where('school_id', $user->school_id)
            ->count();

        $todayTeacherAttendance = TeacherAttendance::today()->count();
        $pendingTeachers = TeacherAttendance::today()->pending()->count();
        $lateTeachers = TeacherAttendance::today()->late()->count();
        $confirmedTeachers = TeacherAttendance::today()->where('attendance_status', 'confirmed')->count();
        $absentTeachers = TeacherAttendance::today()->where('attendance_status', 'absent')->count();

        // Weekly teacher attendance trend
        $weeklyTeacherTrend = $this->getWeeklyTeacherAttendanceTrend();

        // Teachers by status
        $teachersByStatus = [
            'confirmed' => $confirmedTeachers,
            'pending' => $pendingTeachers,
            'late' => $lateTeachers,
            'absent' => $absentTeachers,
            'not_logged_in' => $teacherCount - $todayTeacherAttendance,
        ];

        return [
            'totalTeachers' => $teacherCount,
            'todayTeacherAttendance' => $todayTeacherAttendance,
            'pendingTeachers' => $pendingTeachers,
            'lateTeachers' => $lateTeachers,
            'confirmedTeachers' => $confirmedTeachers,
            'absentTeachers' => $absentTeachers,
            'teachersByStatus' => $teachersByStatus,
            'weeklyTeacherTrend' => $weeklyTeacherTrend,
            'attendanceRate' => $teacherCount > 0 
                ? round(($todayTeacherAttendance / $teacherCount) * 100, 1) 
                : 0,
        ];
    }

    /**
     * Get principal live stats.
     */
    protected function getPrincipalLiveStats(): array
    {
        $user = auth()->user();
        $teacherCount = User::teachers()->active()->where('school_id', $user->school_id)->count();
        $todayTeacherAttendance = TeacherAttendance::today()->count();

        return [
            'todayTeacherAttendance' => $todayTeacherAttendance,
            'pendingTeachers' => TeacherAttendance::today()->pending()->count(),
            'lateTeachers' => TeacherAttendance::today()->late()->count(),
            'confirmedTeachers' => TeacherAttendance::today()->where('attendance_status', 'confirmed')->count(),
            'absentTeachers' => TeacherAttendance::today()->where('attendance_status', 'absent')->count(),
            'notLoggedIn' => $teacherCount - $todayTeacherAttendance,
            'attendanceRate' => $teacherCount > 0 
                ? round(($todayTeacherAttendance / $teacherCount) * 100, 1) 
                : 0,
        ];
    }

    /**
     * Get teacher-specific dashboard data.
     */
    protected function getTeacherDashboardData(User $user, ?SchoolYear $schoolYear): array
    {
        $schoolYearId = $schoolYear?->id;

        $classes = $user->classes()
            ->when($schoolYearId, fn ($q) => $q->where('school_year_id', $schoolYearId))
            ->active()
            ->withCount('students')
            ->get();

        $classIds = $classes->pluck('id');
        $totalStudents = $classes->sum('students_count');

        $todayAttendance = Attendance::today()
            ->whereHas('student.classes', fn ($q) => $q->whereIn('classes.id', $classIds))
            ->count();

        $teacherAttendance = TeacherAttendance::where('teacher_id', $user->id)
            ->today()
            ->first();

        // Per-class attendance today
        $classAttendance = $this->getClassAttendanceToday($classIds);

        // Weekly trend for teacher's students
        $weeklyTrend = $this->getTeacherWeeklyTrend($classIds);

        return [
            'classes' => $classes,
            'totalStudents' => $totalStudents,
            'todayAttendance' => $todayAttendance,
            'teacherAttendance' => $teacherAttendance,
            'classAttendance' => $classAttendance,
            'weeklyTrend' => $weeklyTrend,
            'attendanceRate' => $totalStudents > 0 
                ? round(($todayAttendance / $totalStudents) * 100, 1) 
                : 0,
        ];
    }

    /**
     * Get teacher live stats.
     */
    protected function getTeacherLiveStats(User $user): array
    {
        $classIds = $user->getTeacherClassIds();

        $todayAttendance = Attendance::today()
            ->whereHas('student.classes', fn ($q) => $q->whereIn('classes.id', $classIds))
            ->count();

        $totalStudents = Student::whereHas('classes', fn ($q) => $q->whereIn('classes.id', $classIds))
            ->active()
            ->count();

        return [
            'todayAttendance' => $todayAttendance,
            'attendanceRate' => $totalStudents > 0 
                ? round(($todayAttendance / $totalStudents) * 100, 1) 
                : 0,
        ];
    }

    /**
     * Get weekly attendance trend (last 7 days).
     */
    protected function getWeeklyAttendanceTrend(?int $schoolYearId): array
    {
        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $count = Attendance::whereDate('attendance_date', $date)
                ->when($schoolYearId, fn ($q) => $q->where('school_year_id', $schoolYearId))
                ->count();
            $trend[] = [
                'date' => $date->format('M d'),
                'day' => $date->format('D'),
                'count' => $count,
            ];
        }

        return $trend;
    }

    /**
     * Get weekly teacher attendance trend.
     */
    protected function getWeeklyTeacherAttendanceTrend(): array
    {
        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $count = TeacherAttendance::whereDate('attendance_date', $date)->count();
            $late = TeacherAttendance::whereDate('attendance_date', $date)
                ->where('attendance_status', 'late')
                ->count();
            $trend[] = [
                'date' => $date->format('M d'),
                'day' => $date->format('D'),
                'total' => $count,
                'late' => $late,
                'onTime' => $count - $late,
            ];
        }

        return $trend;
    }

    /**
     * Get today's attendance stats by status.
     */
    protected function getTodayAttendanceStats(?int $schoolYearId): array
    {
        $query = Attendance::today()
            ->when($schoolYearId, fn ($q) => $q->where('school_year_id', $schoolYearId));

        return [
            'total' => (clone $query)->count(),
            'present' => (clone $query)->where('status', 'present')->count(),
            'late' => (clone $query)->where('status', 'late')->count(),
        ];
    }

    /**
     * Get top classes by attendance rate.
     */
    protected function getTopClassesByAttendance(?int $schoolYearId, int $limit = 5): array
    {
        $classes = ClassRoom::with('teacher')
            ->when($schoolYearId, fn ($q) => $q->where('school_year_id', $schoolYearId))
            ->active()
            ->withCount(['students' => fn ($q) => $q->where('student_classes.is_active', true)])
            ->get();

        $classStats = $classes->map(function ($class) {
            $enrolled = $class->students_count;
            $attended = Attendance::today()
                ->whereHas('student.classes', fn ($q) => $q->where('classes.id', $class->id))
                ->count();

            return [
                'id' => $class->id,
                'name' => "Grade {$class->grade_level} - {$class->section}",
                'teacher' => $class->teacher?->full_name ?? 'Unassigned',
                'enrolled' => $enrolled,
                'attended' => $attended,
                'rate' => $enrolled > 0 ? round(($attended / $enrolled) * 100, 1) : 0,
            ];
        })->sortByDesc('rate')->take($limit)->values()->toArray();

        return $classStats;
    }

    /**
     * Get recent scans.
     */
    protected function getRecentScans(int $limit = 10): array
    {
        return Attendance::with('student')
            ->today()
            ->orderBy('check_in_time', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn ($a) => [
                'student' => $a->student->full_name,
                'time' => $a->check_in_time->format('h:i A'),
                'status' => $a->status,
            ])
            ->toArray();
    }

    /**
     * Get class attendance for today.
     */
    protected function getClassAttendanceToday($classIds): array
    {
        $classes = ClassRoom::whereIn('id', $classIds)
            ->withCount(['students' => fn ($q) => $q->where('student_classes.is_active', true)])
            ->get();

        return $classes->map(function ($class) {
            $attended = Attendance::today()
                ->whereHas('student.classes', fn ($q) => $q->where('classes.id', $class->id))
                ->count();

            return [
                'id' => $class->id,
                'name' => "Grade {$class->grade_level} - {$class->section}",
                'enrolled' => $class->students_count,
                'attended' => $attended,
                'rate' => $class->students_count > 0 
                    ? round(($attended / $class->students_count) * 100, 1) 
                    : 0,
            ];
        })->toArray();
    }

    /**
     * Get teacher's weekly trend.
     */
    protected function getTeacherWeeklyTrend($classIds): array
    {
        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $count = Attendance::whereDate('attendance_date', $date)
                ->whereHas('student.classes', fn ($q) => $q->whereIn('classes.id', $classIds))
                ->count();
            $trend[] = [
                'date' => $date->format('M d'),
                'day' => $date->format('D'),
                'count' => $count,
            ];
        }

        return $trend;
    }
}

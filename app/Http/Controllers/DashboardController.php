<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\TeacherAttendance;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the role-appropriate dashboard.
     * (Requirement 1.1)
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // Redirect super admin to their dedicated dashboard
        if ($user->isSuperAdmin()) {
            return redirect()->route('super-admin.dashboard');
        }

        $activeSchoolYear = SchoolYear::active()->first();

        $data = [
            'user' => $user,
            'activeSchoolYear' => $activeSchoolYear,
        ];

        // Add role-specific data
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
     * Get admin-specific dashboard data.
     * Data is auto-scoped by BelongsToSchool trait on models.
     */
    protected function getAdminDashboardData(?SchoolYear $schoolYear): array
    {
        $user = auth()->user();
        $schoolYearId = $schoolYear?->id;

        // User model doesn't use BelongsToSchool trait, so scope manually
        $teacherCount = User::teachers()
            ->active()
            ->where('school_id', $user->school_id)
            ->count();

        return [
            'totalStudents' => Student::active()->count(), // Auto-scoped by trait
            'totalTeachers' => $teacherCount,
            'totalClasses' => $schoolYearId
                ? ClassRoom::where('school_year_id', $schoolYearId)->active()->count() // Auto-scoped
                : 0,
            'todayAttendance' => Attendance::today()->count(),
            'todayTeacherAttendance' => TeacherAttendance::today()->count(),
            'pendingTeachers' => TeacherAttendance::today()->pending()->count(),
            'lateTeachers' => TeacherAttendance::today()->late()->count(),
        ];
    }

    /**
     * Get principal-specific dashboard data.
     */
    protected function getPrincipalDashboardData(?SchoolYear $schoolYear): array
    {
        $user = auth()->user();

        // User model doesn't use BelongsToSchool trait, so scope manually
        $teacherCount = User::teachers()
            ->active()
            ->where('school_id', $user->school_id)
            ->count();

        return [
            'totalTeachers' => $teacherCount,
            'todayTeacherAttendance' => TeacherAttendance::today()->count(),
            'pendingTeachers' => TeacherAttendance::today()->pending()->count(),
            'lateTeachers' => TeacherAttendance::today()->late()->count(),
            'confirmedTeachers' => TeacherAttendance::today()
                ->where('attendance_status', 'confirmed')->count(),
            'absentTeachers' => TeacherAttendance::today()
                ->where('attendance_status', 'absent')->count(),
        ];
    }

    /**
     * Get teacher-specific dashboard data.
     */
    protected function getTeacherDashboardData(User $user, ?SchoolYear $schoolYear): array
    {
        $schoolYearId = $schoolYear?->id;

        // Get teacher's classes with student count (avoid N+1)
        $classes = $user->classes()
            ->when($schoolYearId, fn($q) => $q->where('school_year_id', $schoolYearId))
            ->active()
            ->withCount('students')
            ->get();

        // Get today's attendance for teacher's students
        $classIds = $classes->pluck('id');
        $todayAttendance = Attendance::today()
            ->whereHas('student.classes', fn($q) => $q->whereIn('classes.id', $classIds))
            ->count();

        // Get teacher's own attendance status
        $teacherAttendance = TeacherAttendance::where('teacher_id', $user->id)
            ->today()
            ->first();

        return [
            'classes' => $classes,
            'totalStudents' => $classes->sum('students_count'),
            'todayAttendance' => $todayAttendance,
            'teacherAttendance' => $teacherAttendance,
        ];
    }
}

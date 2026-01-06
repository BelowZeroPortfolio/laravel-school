<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the super admin dashboard.
     */
    public function index(): View
    {
        $stats = [
            'total_schools' => School::count(),
            'active_schools' => School::where('is_active', true)->count(),
            'total_users' => User::whereNotNull('school_id')->count(),
            'total_students' => Student::count(),
        ];

        $recentSchools = School::withCount(['users', 'students'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $schoolsByStatus = [
            'active' => School::where('is_active', true)->count(),
            'inactive' => School::where('is_active', false)->count(),
        ];

        return view('super-admin.dashboard', [
            'stats' => $stats,
            'recentSchools' => $recentSchools,
            'schoolsByStatus' => $schoolsByStatus,
        ]);
    }
}

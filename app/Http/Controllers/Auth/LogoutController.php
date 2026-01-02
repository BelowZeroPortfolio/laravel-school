<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TeacherAttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogoutController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected TeacherAttendanceService $teacherAttendanceService
    ) {}

    /**
     * Handle a logout request.
     * Records teacher time_out before destroying session.
     * (Requirement 1.3)
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // Record teacher time_out before logout (Requirement 1.3)
        if ($user && $user->isTeacher()) {
            $this->teacherAttendanceService->recordTimeOut($user->id);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TeacherAttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected TeacherAttendanceService $teacherAttendanceService
    ) {}

    /**
     * Show the login form.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle a login request.
     * (Requirements 1.1, 1.2, 1.4, 3.1)
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Call authenticated hook for teacher attendance
            $this->authenticated($request, Auth::user());

            return redirect()->intended('/dashboard');
        }

        // Invalid credentials - return error (Requirement 1.4)
        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }

    /**
     * Hook called after successful authentication.
     * Records teacher time_in for teacher users.
     * (Requirements 1.2, 3.1)
     */
    protected function authenticated(Request $request, $user): void
    {
        // Update last_login timestamp
        $user->update(['last_login' => now()]);

        // Record teacher attendance if user is a teacher (Requirement 1.2, 3.1)
        if ($user->isTeacher()) {
            $this->teacherAttendanceService->recordTimeIn($user->id);
        }
    }
}

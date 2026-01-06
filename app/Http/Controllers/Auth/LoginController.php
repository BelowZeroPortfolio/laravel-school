<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use App\Services\TeacherAttendanceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Maximum login attempts before lockout.
     */
    protected int $maxAttempts = 5;

    /**
     * Lockout duration in minutes.
     */
    protected int $decayMinutes = 15;

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

        // Check if user is locked out
        $this->checkTooManyFailedAttempts($request);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Clear failed attempts on successful login
            $this->clearLoginAttempts($request);
            
            $request->session()->regenerate();

            // Check if user account is active
            if (!Auth::user()->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                AuditLogService::authEvent('login_inactive_account', $credentials['username']);
                
                return back()->withErrors([
                    'username' => 'Your account has been deactivated. Please contact administrator.',
                ])->onlyInput('username');
            }

            // Log successful login
            AuditLogService::authEvent('login_success');

            // Call authenticated hook for teacher attendance
            $this->authenticated($request, Auth::user());

            return redirect()->intended('/dashboard');
        }

        // Increment failed attempts
        $this->incrementLoginAttempts($request);

        // Log failed login attempt
        AuditLogService::authEvent('login_failed', $credentials['username'], [
            'attempts_remaining' => $this->retriesLeft($request),
        ]);

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

    /**
     * Get the rate limiting throttle key.
     */
    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(
            Str::lower($request->input('username')) . '|' . $request->ip()
        );
    }

    /**
     * Check if too many failed login attempts.
     */
    protected function checkTooManyFailedAttempts(Request $request): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), $this->maxAttempts)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        AuditLogService::authEvent('login_lockout', $request->input('username'), [
            'lockout_seconds' => $seconds,
        ]);

        throw ValidationException::withMessages([
            'username' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ])->status(429);
    }

    /**
     * Increment the login attempts.
     */
    protected function incrementLoginAttempts(Request $request): void
    {
        RateLimiter::hit($this->throttleKey($request), $this->decayMinutes * 60);
    }

    /**
     * Clear the login attempts.
     */
    protected function clearLoginAttempts(Request $request): void
    {
        RateLimiter::clear($this->throttleKey($request));
    }

    /**
     * Get remaining login attempts.
     */
    protected function retriesLeft(Request $request): int
    {
        return RateLimiter::remaining($this->throttleKey($request), $this->maxAttempts);
    }
}

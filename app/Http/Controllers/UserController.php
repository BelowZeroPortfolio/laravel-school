<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     * Scoped to current user's school.
     * (Requirement 2.1)
     */
    public function index(Request $request): View
    {
        $currentUser = $request->user();
        $query = User::query();

        // Scope to current user's school (multi-tenancy)
        if ($currentUser->school_id) {
            $query->where('school_id', $currentUser->school_id);
        }

        // Exclude super_admin from list (only super_admin can manage super_admins)
        $query->where('role', '!=', 'super_admin');

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $users = $query->orderBy('full_name')->paginate(25);

        return view('users.index', [
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        return view('users.create');
    }

    /**
     * Store a newly created user.
     * Automatically assigns to current user's school.
     * (Requirement 2.1)
     */
    public function store(Request $request): RedirectResponse
    {
        $currentUser = $request->user();

        $validated = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[a-zA-Z0-9_]+$/', 'unique:users,username'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:admin,principal,teacher'],
            'full_name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'is_active' => ['boolean'],
            'is_premium' => ['boolean'],
            'premium_expires_at' => ['nullable', 'date'],
        ], [
            'username.min' => 'Username must be at least 3 characters.',
            'username.max' => 'Username must not exceed 50 characters.',
            'username.regex' => 'Username can only contain letters, numbers, and underscores.',
            'full_name.min' => 'Full name must be at least 2 characters.',
            'full_name.max' => 'Full name must not exceed 100 characters.',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['is_premium'] = $validated['is_premium'] ?? false;

        // Assign to current user's school (multi-tenancy)
        $validated['school_id'] = $currentUser->school_id;

        $user = User::create($validated);

        AuditLogService::sensitiveOperation('user_created', [
            'created_user_id' => $user->id,
            'created_username' => $user->username,
            'role' => $user->role,
        ]);

        return redirect()->route('users.show', $user)
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(Request $request, User $user): View
    {
        // Ensure user belongs to same school (multi-tenancy)
        $this->authorizeSchoolAccess($request->user(), $user);

        $user->loadCount(['classes', 'teacherAttendances']);
        $user->load([
            'classes' => function ($q) {
                $q->with('schoolYear')->withCount('students');
            },
            'teacherAttendances' => function ($q) {
                $q->orderBy('attendance_date', 'desc')->limit(10);
            }
        ]);

        return view('users.show', [
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(Request $request, User $user): View
    {
        // Ensure user belongs to same school (multi-tenancy)
        $this->authorizeSchoolAccess($request->user(), $user);

        return view('users.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the specified user.
     * (Requirement 2.1)
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        // Ensure user belongs to same school (multi-tenancy)
        $this->authorizeSchoolAccess($request->user(), $user);

        $validated = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:50', 'regex:/^[a-zA-Z0-9_]+$/', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:admin,principal,teacher'],
            'full_name' => ['required', 'string', 'min:2', 'max:100'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'is_active' => ['boolean'],
            'is_premium' => ['boolean'],
            'premium_expires_at' => ['nullable', 'date'],
        ], [
            'username.min' => 'Username must be at least 3 characters.',
            'username.max' => 'Username must not exceed 50 characters.',
            'username.regex' => 'Username can only contain letters, numbers, and underscores.',
            'full_name.min' => 'Full name must be at least 2 characters.',
            'full_name.max' => 'Full name must not exceed 100 characters.',
        ]);

        // Only update password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $validated['is_active'] ?? false;
        $validated['is_premium'] = $validated['is_premium'] ?? false;

        $user->update($validated);

        return redirect()->route('users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    /**
     * Deactivate the specified user.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        // Ensure user belongs to same school (multi-tenancy)
        $this->authorizeSchoolAccess($request->user(), $user);

        // Prevent self-deactivation
        if ($user->id === auth()->id()) {
            return back()->withErrors(['delete' => 'You cannot deactivate your own account.']);
        }

        // Soft-delete by deactivating
        $user->update(['is_active' => false]);

        AuditLogService::sensitiveOperation('user_deactivated', [
            'target_user_id' => $user->id,
            'target_username' => $user->username,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User deactivated successfully.');
    }

    /**
     * Reactivate a deactivated user.
     */
    public function reactivate(Request $request, User $user): RedirectResponse
    {
        // Ensure user belongs to same school (multi-tenancy)
        $this->authorizeSchoolAccess($request->user(), $user);

        $user->update(['is_active' => true]);

        AuditLogService::sensitiveOperation('user_reactivated', [
            'target_user_id' => $user->id,
            'target_username' => $user->username,
        ]);

        return redirect()->route('users.show', $user)
            ->with('success', 'User reactivated successfully.');
    }

    /**
     * Reset user password.
     */
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        // Ensure user belongs to same school (multi-tenancy)
        $this->authorizeSchoolAccess($request->user(), $user);

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        AuditLogService::sensitiveOperation('password_reset', [
            'target_user_id' => $user->id,
            'target_username' => $user->username,
        ]);

        return redirect()->route('users.show', $user)
            ->with('success', 'Password reset successfully.');
    }

    /**
     * Authorize that the current user can access the target user (same school).
     */
    protected function authorizeSchoolAccess(User $currentUser, User $targetUser): void
    {
        // Super admin can access all
        if ($currentUser->isSuperAdmin()) {
            return;
        }

        // Users must belong to the same school
        if ($currentUser->school_id !== $targetUser->school_id) {
            abort(403, 'You do not have access to this user.');
        }

        // Cannot manage super_admin users
        if ($targetUser->isSuperAdmin()) {
            abort(403, 'You cannot manage super admin users.');
        }
    }
}

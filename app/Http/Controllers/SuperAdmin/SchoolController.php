<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SchoolController extends Controller
{
    /**
     * Display a listing of schools.
     */
    public function index(Request $request): View
    {
        $query = School::query()
            ->withCount(['users', 'students', 'classes']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $schools = $query->orderBy('name')->paginate(25);

        return view('super-admin.schools.index', [
            'schools' => $schools,
        ]);
    }

    /**
     * Show the form for creating a new school.
     */
    public function create(): View
    {
        return view('super-admin.schools.create');
    }

    /**
     * Store a newly created school.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:schools,code'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['boolean'],
            // Admin user fields
            'admin_username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'admin_password' => ['required', 'confirmed', Password::defaults()],
            'admin_full_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
        ]);

        $school = School::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Create school admin
        User::create([
            'school_id' => $school->id,
            'username' => $validated['admin_username'],
            'password' => Hash::make($validated['admin_password']),
            'role' => 'admin',
            'full_name' => $validated['admin_full_name'],
            'email' => $validated['admin_email'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('super-admin.schools.show', $school)
            ->with('success', 'School created successfully with admin account.');
    }

    /**
     * Display the specified school.
     */
    public function show(School $school): View
    {
        $school->load(['users' => function ($q) {
            $q->orderBy('role')->orderBy('full_name');
        }]);

        $stats = [
            'total_users' => $school->users()->count(),
            'total_students' => $school->students()->count(),
            'total_classes' => $school->classes()->count(),
            'active_school_years' => $school->schoolYears()->where('is_active', true)->count(),
        ];

        return view('super-admin.schools.show', [
            'school' => $school,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing the specified school.
     */
    public function edit(School $school): View
    {
        return view('super-admin.schools.edit', [
            'school' => $school,
        ]);
    }

    /**
     * Update the specified school.
     */
    public function update(Request $request, School $school): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('schools')->ignore($school->id)],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $validated['is_active'] ?? false;

        $school->update($validated);

        return redirect()->route('super-admin.schools.show', $school)
            ->with('success', 'School updated successfully.');
    }

    /**
     * Deactivate the specified school.
     */
    public function destroy(School $school): RedirectResponse
    {
        $school->update(['is_active' => false]);

        return redirect()->route('super-admin.schools.index')
            ->with('success', 'School deactivated successfully.');
    }

    /**
     * Reactivate a deactivated school.
     */
    public function reactivate(School $school): RedirectResponse
    {
        $school->update(['is_active' => true]);

        return redirect()->route('super-admin.schools.show', $school)
            ->with('success', 'School reactivated successfully.');
    }
}

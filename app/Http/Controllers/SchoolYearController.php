<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SchoolYearController extends Controller
{
    /**
     * Display a listing of school years.
     * (Requirement 10.1)
     */
    public function index(): View
    {
        $schoolYears = SchoolYear::withCount(['classes', 'attendances', 'teacherAttendances'])
            ->orderBy('start_date', 'desc')
            ->get();

        $activeSchoolYear = SchoolYear::active()->first();

        return view('school-years.index', [
            'schoolYears' => $schoolYears,
            'activeSchoolYear' => $activeSchoolYear,
        ]);
    }

    /**
     * Show the form for creating a new school year.
     */
    public function create(): View
    {
        return view('school-years.create');
    }

    /**
     * Store a newly created school year.
     * (Requirement 10.1)
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:school_years,name'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $validated['is_active'] ?? false;
        $validated['is_locked'] = false;

        // If setting as active, deactivate all others (Requirement 10.1)
        if ($validated['is_active']) {
            SchoolYear::where('is_active', true)->update(['is_active' => false]);
        }

        $schoolYear = SchoolYear::create($validated);

        return redirect()->route('school-years.show', $schoolYear)
            ->with('success', 'School year created successfully.');
    }

    /**
     * Display the specified school year.
     */
    public function show(SchoolYear $schoolYear): View
    {
        $schoolYear->loadCount(['classes', 'attendances', 'teacherAttendances']);
        $schoolYear->load(['classes' => function ($q) {
            $q->with('teacher')->orderBy('grade_level')->orderBy('section');
        }]);

        return view('school-years.show', [
            'schoolYear' => $schoolYear,
        ]);
    }

    /**
     * Show the form for editing the specified school year.
     */
    public function edit(SchoolYear $schoolYear): View
    {
        return view('school-years.edit', [
            'schoolYear' => $schoolYear,
        ]);
    }

    /**
     * Update the specified school year.
     * (Requirement 10.2)
     */
    public function update(Request $request, SchoolYear $schoolYear): RedirectResponse
    {
        // Check if locked (Requirement 10.2)
        if ($schoolYear->is_locked) {
            return back()->withErrors([
                'locked' => 'This school year is locked and cannot be modified.',
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('school_years')->ignore($schoolYear->id)],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ]);

        $schoolYear->update($validated);

        return redirect()->route('school-years.show', $schoolYear)
            ->with('success', 'School year updated successfully.');
    }

    /**
     * Activate the specified school year.
     * (Requirement 10.1)
     */
    public function activate(SchoolYear $schoolYear): RedirectResponse
    {
        // Deactivate all other school years (Requirement 10.1)
        SchoolYear::where('is_active', true)->update(['is_active' => false]);

        // Activate this school year
        $schoolYear->update(['is_active' => true]);

        return redirect()->route('school-years.index')
            ->with('success', 'School year activated successfully.');
    }

    /**
     * Lock the specified school year.
     * (Requirement 10.2)
     */
    public function lock(SchoolYear $schoolYear): RedirectResponse
    {
        // Cannot lock the active school year
        if ($schoolYear->is_active) {
            return back()->withErrors([
                'lock' => 'Cannot lock the active school year. Please activate another school year first.',
            ]);
        }

        $schoolYear->update(['is_locked' => true]);

        return redirect()->route('school-years.show', $schoolYear)
            ->with('success', 'School year locked successfully. Attendance records can no longer be modified.');
    }

    /**
     * Unlock the specified school year.
     */
    public function unlock(SchoolYear $schoolYear): RedirectResponse
    {
        $schoolYear->update(['is_locked' => false]);

        return redirect()->route('school-years.show', $schoolYear)
            ->with('success', 'School year unlocked successfully.');
    }

    /**
     * Remove the specified school year.
     */
    public function destroy(SchoolYear $schoolYear): RedirectResponse
    {
        // Cannot delete active school year
        if ($schoolYear->is_active) {
            return back()->withErrors([
                'delete' => 'Cannot delete the active school year.',
            ]);
        }

        // Cannot delete locked school year
        if ($schoolYear->is_locked) {
            return back()->withErrors([
                'delete' => 'Cannot delete a locked school year.',
            ]);
        }

        // Check for associated records
        if ($schoolYear->attendances()->exists() || $schoolYear->teacherAttendances()->exists()) {
            return back()->withErrors([
                'delete' => 'Cannot delete school year with existing attendance records.',
            ]);
        }

        $schoolYear->delete();

        return redirect()->route('school-years.index')
            ->with('success', 'School year deleted successfully.');
    }
}

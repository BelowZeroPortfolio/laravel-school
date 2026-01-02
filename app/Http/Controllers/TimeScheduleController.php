<?php

namespace App\Http\Controllers;

use App\Models\TimeSchedule;
use App\Services\TimeScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimeScheduleController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected TimeScheduleService $timeScheduleService
    ) {}

    /**
     * Display a listing of time schedules.
     * (Requirement 7.1)
     */
    public function index(): View
    {
        $schedules = TimeSchedule::with('creator')
            ->orderBy('is_active', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $activeSchedule = $this->timeScheduleService->getActive();

        return view('time-schedules.index', [
            'schedules' => $schedules,
            'activeSchedule' => $activeSchedule,
        ]);
    }

    /**
     * Show the form for creating a new time schedule.
     */
    public function create(): View
    {
        return view('time-schedules.create');
    }

    /**
     * Store a newly created time schedule.
     * (Requirement 7.1)
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'time_in' => ['required', 'date_format:H:i'],
            'time_out' => ['required', 'date_format:H:i', 'after:time_in'],
            'late_threshold_minutes' => ['required', 'integer', 'min:0', 'max:120'],
            'effective_date' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $validated['is_active'] ?? false;

        // Create schedule with audit logging (Requirement 7.1)
        $schedule = $this->timeScheduleService->create($validated, $request->user()->id);

        return redirect()->route('time-schedules.show', $schedule)
            ->with('success', 'Time schedule created successfully.');
    }

    /**
     * Display the specified time schedule.
     */
    public function show(TimeSchedule $timeSchedule): View
    {
        $timeSchedule->load('creator');
        
        // Get change logs for this schedule
        $logs = $this->timeScheduleService->getChangeLogs($timeSchedule->id, 20);

        return view('time-schedules.show', [
            'schedule' => $timeSchedule,
            'logs' => $logs,
        ]);
    }

    /**
     * Show the form for editing the specified time schedule.
     */
    public function edit(TimeSchedule $timeSchedule): View
    {
        return view('time-schedules.edit', [
            'schedule' => $timeSchedule,
        ]);
    }

    /**
     * Update the specified time schedule.
     * (Requirement 7.2)
     */
    public function update(Request $request, TimeSchedule $timeSchedule): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'time_in' => ['required', 'date_format:H:i'],
            'time_out' => ['required', 'date_format:H:i', 'after:time_in'],
            'late_threshold_minutes' => ['required', 'integer', 'min:0', 'max:120'],
            'effective_date' => ['nullable', 'date'],
            'change_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $reason = $validated['change_reason'] ?? null;
        unset($validated['change_reason']);

        // Update schedule with audit logging (Requirement 7.2)
        $this->timeScheduleService->update(
            $timeSchedule->id,
            $validated,
            $request->user()->id,
            $reason
        );

        return redirect()->route('time-schedules.show', $timeSchedule)
            ->with('success', 'Time schedule updated successfully.');
    }

    /**
     * Activate the specified time schedule.
     * (Requirement 7.3)
     */
    public function activate(Request $request, TimeSchedule $timeSchedule): RedirectResponse
    {
        // Activate schedule (deactivates all others) (Requirement 7.3)
        $this->timeScheduleService->activate($timeSchedule->id, $request->user()->id);

        return redirect()->route('time-schedules.index')
            ->with('success', 'Time schedule activated successfully.');
    }

    /**
     * Remove the specified time schedule.
     * (Requirement 7.4)
     */
    public function destroy(Request $request, TimeSchedule $timeSchedule): RedirectResponse
    {
        // Attempt to delete (will fail if active) (Requirement 7.4)
        $deleted = $this->timeScheduleService->delete($timeSchedule->id, $request->user()->id);

        if (!$deleted) {
            return back()->withErrors([
                'delete' => 'Cannot delete an active time schedule. Please activate another schedule first.',
            ]);
        }

        return redirect()->route('time-schedules.index')
            ->with('success', 'Time schedule deleted successfully.');
    }

    /**
     * Display change logs for all schedules.
     */
    public function logs(): View
    {
        $logs = $this->timeScheduleService->getChangeLogs(null, 100);

        return view('time-schedules.logs', [
            'logs' => $logs,
        ]);
    }
}

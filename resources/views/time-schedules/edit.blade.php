@extends('layouts.app')

@section('title', 'Edit Time Schedule')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('time-schedules.show', $schedule) }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Schedule
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">Edit Time Schedule</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $schedule->name }}</p>
    </div>

    <form method="POST" action="{{ route('time-schedules.update', $schedule) }}">
        @csrf
        @method('PUT')

        <x-card title="Schedule Details" class="mb-6">
            <div class="space-y-4">
                <x-input 
                    type="text" 
                    name="name" 
                    label="Schedule Name"
                    :value="old('name', $schedule->name)"
                    required
                    placeholder="e.g., Regular Schedule, Summer Schedule"
                />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input 
                        type="time" 
                        name="time_in" 
                        label="Time In"
                        :value="old('time_in', \Carbon\Carbon::parse($schedule->time_in)->format('H:i'))"
                        required
                    />

                    <x-input 
                        type="time" 
                        name="time_out" 
                        label="Time Out"
                        :value="old('time_out', \Carbon\Carbon::parse($schedule->time_out)->format('H:i'))"
                        required
                    />
                </div>

                <x-input 
                    type="number" 
                    name="late_threshold_minutes" 
                    label="Late Threshold (minutes)"
                    :value="old('late_threshold_minutes', $schedule->late_threshold_minutes)"
                    required
                    min="0"
                    max="120"
                    hint="Number of minutes after Time In before marking as late"
                />

                <x-input 
                    type="date" 
                    name="effective_date" 
                    label="Effective Date"
                    :value="old('effective_date', $schedule->effective_date?->format('Y-m-d'))"
                    hint="Optional - when this schedule becomes effective"
                />
            </div>
        </x-card>

        <!-- Change Reason -->
        <x-card title="Change Reason" class="mb-6">
            <div>
                <label for="change_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Reason for Change
                </label>
                <textarea 
                    name="change_reason" 
                    id="change_reason"
                    rows="3"
                    class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-indigo-500 bg-white dark:bg-gray-700"
                    placeholder="Optional - describe why this change is being made"
                >{{ old('change_reason') }}</textarea>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    This will be recorded in the audit log for future reference.
                </p>
            </div>
        </x-card>

        @if($schedule->is_active)
        <x-alert type="warning" class="mb-6">
            <p class="font-medium">This is the active schedule</p>
            <p class="text-sm">Changes will affect how attendance is calculated for all teachers.</p>
        </x-alert>
        @endif

        <!-- Actions -->
        <div class="flex items-center justify-between">
            @if(!$schedule->is_active)
            <form method="POST" action="{{ route('time-schedules.destroy', $schedule) }}" 
                  onsubmit="return confirm('Are you sure you want to delete this schedule?')">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger">Delete Schedule</x-button>
            </form>
            @else
            <div></div>
            @endif

            <div class="flex items-center space-x-3">
                <a href="{{ route('time-schedules.show', $schedule) }}">
                    <x-button type="button" variant="outline">Cancel</x-button>
                </a>
                <x-button type="submit" variant="primary">Save Changes</x-button>
            </div>
        </div>
    </form>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Create Time Schedule')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('time-schedules.index') }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Schedules
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">Create Time Schedule</h1>
    </div>

    <form method="POST" action="{{ route('time-schedules.store') }}">
        @csrf

        <x-card title="Schedule Details" class="mb-6">
            <div class="space-y-4">
                <x-input 
                    type="text" 
                    name="name" 
                    label="Schedule Name"
                    :value="old('name')"
                    required
                    placeholder="e.g., Regular Schedule, Summer Schedule"
                />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input 
                        type="time" 
                        name="time_in" 
                        label="Time In"
                        :value="old('time_in', '07:30')"
                        required
                    />

                    <x-input 
                        type="time" 
                        name="time_out" 
                        label="Time Out"
                        :value="old('time_out', '17:00')"
                        required
                    />
                </div>

                <x-input 
                    type="number" 
                    name="late_threshold_minutes" 
                    label="Late Threshold (minutes)"
                    :value="old('late_threshold_minutes', 15)"
                    required
                    min="0"
                    max="120"
                    hint="Number of minutes after Time In before marking as late"
                />

                <x-input 
                    type="date" 
                    name="effective_date" 
                    label="Effective Date"
                    :value="old('effective_date')"
                    hint="Optional - when this schedule becomes effective"
                />

                <label class="flex items-center">
                    <input type="checkbox" 
                           name="is_active" 
                           value="1"
                           {{ old('is_active') ? 'checked' : '' }}
                           class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700">
                    <span class="ml-3">
                        <span class="block text-sm font-medium text-gray-900 dark:text-white">Set as Active</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400">This will deactivate all other schedules</span>
                    </span>
                </label>
            </div>
        </x-card>

        <!-- Preview -->
        <x-card title="Preview" class="mb-6">
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    With the settings above, teachers will be marked as <span class="font-medium text-red-600 dark:text-red-400">late</span> if they:
                </p>
                <ul class="mt-2 text-sm text-gray-600 dark:text-gray-400 list-disc list-inside">
                    <li>Log in after the cutoff time (Time In + Late Threshold)</li>
                    <li>Have their first student scan after the cutoff time</li>
                </ul>
            </div>
        </x-card>

        <!-- Actions -->
        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('time-schedules.index') }}">
                <x-button type="button" variant="outline">Cancel</x-button>
            </a>
            <x-button type="submit" variant="primary">Create Schedule</x-button>
        </div>
    </form>
</div>
@endsection

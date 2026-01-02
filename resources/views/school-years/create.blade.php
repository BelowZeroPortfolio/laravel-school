@extends('layouts.app')

@section('title', 'Create School Year')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('school-years.index') }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to School Years
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">Create School Year</h1>
    </div>

    <form method="POST" action="{{ route('school-years.store') }}">
        @csrf

        <x-card title="School Year Details" class="mb-6">
            <div class="space-y-4">
                <x-input 
                    type="text" 
                    name="name" 
                    label="Name"
                    :value="old('name')"
                    required
                    placeholder="e.g., 2025-2026"
                    hint="A unique identifier for this school year"
                />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input 
                        type="date" 
                        name="start_date" 
                        label="Start Date"
                        :value="old('start_date')"
                        required
                    />

                    <x-input 
                        type="date" 
                        name="end_date" 
                        label="End Date"
                        :value="old('end_date')"
                        required
                    />
                </div>

                <label class="flex items-center">
                    <input type="checkbox" 
                           name="is_active" 
                           value="1"
                           {{ old('is_active') ? 'checked' : '' }}
                           class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700">
                    <span class="ml-3">
                        <span class="block text-sm font-medium text-gray-900 dark:text-white">Set as Active</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400">This will deactivate all other school years</span>
                    </span>
                </label>
            </div>
        </x-card>

        <x-alert type="info" class="mb-6">
            <p class="font-medium">Important Notes:</p>
            <ul class="mt-1 text-sm list-disc list-inside">
                <li>Only one school year can be active at a time</li>
                <li>All new attendance records will be associated with the active school year</li>
                <li>You can lock a school year later to prevent modifications to its records</li>
            </ul>
        </x-alert>

        <!-- Actions -->
        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('school-years.index') }}">
                <x-button type="button" variant="outline">Cancel</x-button>
            </a>
            <x-button type="submit" variant="primary">Create School Year</x-button>
        </div>
    </form>
</div>
@endsection

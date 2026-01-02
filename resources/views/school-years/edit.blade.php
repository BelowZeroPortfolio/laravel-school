@extends('layouts.app')

@section('title', 'Edit School Year')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('school-years.show', $schoolYear) }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to School Year
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">Edit School Year</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $schoolYear->name }}</p>
    </div>

    @if($schoolYear->is_locked)
    <x-alert type="warning" class="mb-6">
        <p class="font-medium">This school year is locked</p>
        <p class="text-sm">Locked school years cannot be modified. Unlock it first to make changes.</p>
    </x-alert>
    @endif

    <form method="POST" action="{{ route('school-years.update', $schoolYear) }}">
        @csrf
        @method('PUT')

        <x-card title="School Year Details" class="mb-6">
            <div class="space-y-4">
                <x-input 
                    type="text" 
                    name="name" 
                    label="Name"
                    :value="old('name', $schoolYear->name)"
                    required
                    placeholder="e.g., 2025-2026"
                    :disabled="$schoolYear->is_locked"
                />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input 
                        type="date" 
                        name="start_date" 
                        label="Start Date"
                        :value="old('start_date', $schoolYear->start_date->format('Y-m-d'))"
                        required
                        :disabled="$schoolYear->is_locked"
                    />

                    <x-input 
                        type="date" 
                        name="end_date" 
                        label="End Date"
                        :value="old('end_date', $schoolYear->end_date->format('Y-m-d'))"
                        required
                        :disabled="$schoolYear->is_locked"
                    />
                </div>
            </div>
        </x-card>

        <!-- Status Info -->
        <x-card title="Status" class="mb-6">
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Active Status</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $schoolYear->is_active ? 'This is the currently active school year' : 'This school year is not active' }}
                        </p>
                    </div>
                    <x-badge type="{{ $schoolYear->is_active ? 'success' : 'default' }}">
                        {{ $schoolYear->is_active ? 'Active' : 'Inactive' }}
                    </x-badge>
                </div>

                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">Lock Status</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $schoolYear->is_locked ? 'Attendance records cannot be modified' : 'Attendance records can be modified' }}
                        </p>
                    </div>
                    <x-badge type="{{ $schoolYear->is_locked ? 'warning' : 'default' }}">
                        {{ $schoolYear->is_locked ? 'Locked' : 'Unlocked' }}
                    </x-badge>
                </div>
            </div>
        </x-card>

        <!-- Actions -->
        @if(!$schoolYear->is_locked)
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                @if(!$schoolYear->is_active)
                <form method="POST" action="{{ route('school-years.destroy', $schoolYear) }}" 
                      onsubmit="return confirm('Are you sure you want to delete this school year? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <x-button type="submit" variant="danger">Delete</x-button>
                </form>
                @endif
            </div>

            <div class="flex items-center space-x-3">
                <a href="{{ route('school-years.show', $schoolYear) }}">
                    <x-button type="button" variant="outline">Cancel</x-button>
                </a>
                <x-button type="submit" variant="primary">Save Changes</x-button>
            </div>
        </div>
        @else
        <div class="flex items-center justify-end">
            <a href="{{ route('school-years.show', $schoolYear) }}">
                <x-button type="button" variant="outline">Back</x-button>
            </a>
        </div>
        @endif
    </form>
</div>
@endsection

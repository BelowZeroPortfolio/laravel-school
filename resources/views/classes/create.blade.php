@extends('layouts.app')

@section('title', 'Add Class')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('classes.index') }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Classes
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">Add New Class</h1>
    </div>

    <form method="POST" action="{{ route('classes.store') }}">
        @csrf

        <x-card title="Class Information" class="mb-6">
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input 
                        type="text" 
                        name="grade_level" 
                        label="Grade Level"
                        :value="old('grade_level')"
                        required
                        placeholder="e.g., 7, 8, 9"
                    />

                    <x-input 
                        type="text" 
                        name="section" 
                        label="Section"
                        :value="old('section')"
                        required
                        placeholder="e.g., A, B, Einstein"
                    />
                </div>

                <x-select 
                    name="school_year_id" 
                    label="School Year"
                    required
                >
                    @foreach($schoolYears as $schoolYear)
                        <option value="{{ $schoolYear->id }}" {{ old('school_year_id') == $schoolYear->id ? 'selected' : '' }}>
                            {{ $schoolYear->name }}
                            @if($schoolYear->is_active) (Active) @endif
                        </option>
                    @endforeach
                </x-select>

                <x-select 
                    name="teacher_id" 
                    label="Assigned Teacher"
                    required
                >
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}" {{ old('teacher_id') == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->full_name }}
                        </option>
                    @endforeach
                </x-select>

                <x-input 
                    type="number" 
                    name="max_capacity" 
                    label="Maximum Capacity"
                    :value="old('max_capacity', 40)"
                    required
                    min="1"
                    max="100"
                    hint="Maximum number of students that can be enrolled"
                />

                <label class="flex items-center">
                    <input type="checkbox" 
                           name="is_active" 
                           value="1"
                           {{ old('is_active', true) ? 'checked' : '' }}
                           class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700">
                    <span class="ml-3">
                        <span class="block text-sm font-medium text-gray-900 dark:text-white">Active</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400">Class is available for enrollment and attendance</span>
                    </span>
                </label>
            </div>
        </x-card>

        <!-- Actions -->
        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('classes.index') }}">
                <x-button type="button" variant="outline">Cancel</x-button>
            </a>
            <x-button type="submit" variant="primary">Create Class</x-button>
        </div>
    </form>
</div>
@endsection

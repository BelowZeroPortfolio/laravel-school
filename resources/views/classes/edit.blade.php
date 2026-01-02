@extends('layouts.app')

@section('title', 'Edit Class')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('classes.show', $class) }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Class
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">Edit Class</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Grade {{ $class->grade_level }} - {{ $class->section }}</p>
    </div>

    <form method="POST" action="{{ route('classes.update', $class) }}">
        @csrf
        @method('PUT')

        <x-card title="Class Information" class="mb-6">
            <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input 
                        type="text" 
                        name="grade_level" 
                        label="Grade Level"
                        :value="old('grade_level', $class->grade_level)"
                        required
                        placeholder="e.g., 7, 8, 9"
                    />

                    <x-input 
                        type="text" 
                        name="section" 
                        label="Section"
                        :value="old('section', $class->section)"
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
                        <option value="{{ $schoolYear->id }}" {{ old('school_year_id', $class->school_year_id) == $schoolYear->id ? 'selected' : '' }}>
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
                        <option value="{{ $teacher->id }}" {{ old('teacher_id', $class->teacher_id) == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->full_name }}
                        </option>
                    @endforeach
                </x-select>

                <x-input 
                    type="number" 
                    name="max_capacity" 
                    label="Maximum Capacity"
                    :value="old('max_capacity', $class->max_capacity)"
                    required
                    min="1"
                    max="100"
                    hint="Maximum number of students that can be enrolled"
                />

                <label class="flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" 
                           name="is_active" 
                           value="1"
                           {{ old('is_active', $class->is_active) ? 'checked' : '' }}
                           class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700">
                    <span class="ml-3">
                        <span class="block text-sm font-medium text-gray-900 dark:text-white">Active</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400">Class is available for enrollment and attendance</span>
                    </span>
                </label>
            </div>
        </x-card>

        <!-- Actions -->
        <div class="flex items-center justify-between">
            <form method="POST" action="{{ route('classes.destroy', $class) }}" 
                  onsubmit="return confirm('Are you sure you want to deactivate this class?')">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger">Deactivate Class</x-button>
            </form>

            <div class="flex items-center space-x-3">
                <a href="{{ route('classes.show', $class) }}">
                    <x-button type="button" variant="outline">Cancel</x-button>
                </a>
                <x-button type="submit" variant="primary">Save Changes</x-button>
            </div>
        </div>
    </form>
</div>
@endsection

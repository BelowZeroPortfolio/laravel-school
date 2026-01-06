@extends('layouts.app')

@section('title', 'Edit Student')

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('students.show', $student) }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Student
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">Edit Student</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $student->full_name }} ({{ $student->student_id }})</p>
    </div>

    <form method="POST" action="{{ route('students.update', $student) }}" data-validate>
        @csrf
        @method('PUT')

        <!-- Basic Information -->
        <x-card title="Basic Information" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input 
                    type="text" 
                    name="first_name" 
                    label="First Name"
                    :value="old('first_name', $student->first_name)"
                    required
                    placeholder="Enter first name"
                    minlength="2"
                    maxlength="50"
                />

                <x-input 
                    type="text" 
                    name="last_name" 
                    label="Last Name"
                    :value="old('last_name', $student->last_name)"
                    required
                    placeholder="Enter last name"
                    minlength="2"
                    maxlength="50"
                />

                <x-input 
                    type="text" 
                    name="lrn" 
                    label="LRN (Learner Reference Number)"
                    :value="old('lrn', $student->lrn)"
                    placeholder="12-digit LRN"
                    hint="Exactly 12 digits"
                    required
                    minlength="12"
                    maxlength="12"
                    pattern="[0-9]{12}"
                    title="LRN must be exactly 12 digits"
                />

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Student ID
                    </label>
                    <p class="px-3 py-2 text-sm text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-800 rounded-lg">
                        {{ $student->student_id }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Auto-generated, cannot be changed</p>
                </div>
            </div>
        </x-card>

        <!-- Parent/Guardian Information -->
        <x-card title="Parent/Guardian Information" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input 
                    type="text" 
                    name="parent_name" 
                    label="Parent/Guardian Name"
                    :value="old('parent_name', $student->parent_name)"
                    placeholder="Full name"
                    minlength="2"
                    maxlength="100"
                />

                <x-input 
                    type="tel" 
                    name="parent_phone" 
                    label="Contact Number"
                    :value="old('parent_phone', $student->parent_phone)"
                    placeholder="09171234567"
                    minlength="11"
                    maxlength="11"
                    pattern="09[0-9]{9}"
                    title="Enter a valid Philippine mobile number (e.g., 09171234567)"
                    hint="11 digits starting with 09"
                />

                <x-input 
                    type="email" 
                    name="parent_email" 
                    label="Email Address"
                    :value="old('parent_email', $student->parent_email)"
                    placeholder="email@example.com"
                    maxlength="255"
                />

                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Address
                    </label>
                    <textarea 
                        name="address" 
                        id="address"
                        rows="2"
                        maxlength="500"
                        class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-indigo-500 bg-white dark:bg-gray-700"
                        placeholder="Home address"
                    >{{ old('address', $student->address) }}</textarea>
                    @error('address')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-card>

        <!-- Current Enrollments (Read-only) -->
        <x-card title="Current Enrollments" class="mb-6">
            @if($student->classes->count() > 0)
                <div class="space-y-2">
                    @foreach($student->classes as $class)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <div>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                    Grade {{ $class->grade_level }} - {{ $class->section }}
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">
                                    {{ $class->schoolYear->name ?? '' }}
                                </span>
                            </div>
                            <x-badge type="{{ $class->pivot->is_active ? 'success' : 'default' }}" size="sm">
                                {{ $class->pivot->is_active ? 'Active' : 'Inactive' }}
                            </x-badge>
                        </div>
                    @endforeach
                </div>
                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    To manage enrollments, go to the class management page.
                </p>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                    Not enrolled in any classes.
                </p>
            @endif
        </x-card>

        <!-- Settings -->
        <x-card title="Settings" class="mb-6">
            <div class="space-y-4">
                <label class="flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" 
                           name="is_active" 
                           value="1"
                           {{ old('is_active', $student->is_active) ? 'checked' : '' }}
                           class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700">
                    <span class="ml-3">
                        <span class="block text-sm font-medium text-gray-900 dark:text-white">Active</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400">Student can be scanned for attendance</span>
                    </span>
                </label>

                <label class="flex items-center">
                    <input type="hidden" name="sms_enabled" value="0">
                    <input type="checkbox" 
                           name="sms_enabled" 
                           value="1"
                           {{ old('sms_enabled', $student->sms_enabled) ? 'checked' : '' }}
                           class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700">
                    <span class="ml-3">
                        <span class="block text-sm font-medium text-gray-900 dark:text-white">SMS Notifications</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400">Send SMS to parent when student scans</span>
                    </span>
                </label>
            </div>
        </x-card>

        <!-- Actions -->
        <div class="flex items-center justify-between">
            <form method="POST" action="{{ route('students.destroy', $student) }}" 
                  onsubmit="return confirm('Are you sure you want to deactivate this student?')">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger">Deactivate Student</x-button>
            </form>

            <div class="flex items-center space-x-3">
                <a href="{{ route('students.show', $student) }}">
                    <x-button type="button" variant="outline">Cancel</x-button>
                </a>
                <x-button type="submit" variant="primary">Save Changes</x-button>
            </div>
        </div>
    </form>
</div>
@endsection

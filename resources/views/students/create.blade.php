@extends('layouts.app')

@section('title', 'Add Student')

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('students.index') }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Students
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">Add New Student</h1>
    </div>

    <form method="POST" action="{{ route('students.store') }}">
        @csrf

        <!-- Basic Information -->
        <x-card title="Basic Information" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input 
                    type="text" 
                    name="first_name" 
                    label="First Name"
                    :value="old('first_name')"
                    required
                    placeholder="Enter first name"
                />

                <x-input 
                    type="text" 
                    name="last_name" 
                    label="Last Name"
                    :value="old('last_name')"
                    required
                    placeholder="Enter last name"
                />

                <x-input 
                    type="text" 
                    name="lrn" 
                    label="LRN (Learner Reference Number)"
                    :value="old('lrn')"
                    placeholder="12-digit LRN"
                    hint="Optional - 12-digit unique identifier"
                    maxlength="12"
                />
            </div>
        </x-card>

        <!-- Parent/Guardian Information -->
        <x-card title="Parent/Guardian Information" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input 
                    type="text" 
                    name="parent_name" 
                    label="Parent/Guardian Name"
                    :value="old('parent_name')"
                    placeholder="Full name"
                />

                <x-input 
                    type="tel" 
                    name="parent_phone" 
                    label="Contact Number"
                    :value="old('parent_phone')"
                    placeholder="Phone number"
                />

                <x-input 
                    type="email" 
                    name="parent_email" 
                    label="Email Address"
                    :value="old('parent_email')"
                    placeholder="email@example.com"
                />

                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Address
                    </label>
                    <textarea 
                        name="address" 
                        id="address"
                        rows="2"
                        class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-indigo-500 bg-white dark:bg-gray-700"
                        placeholder="Home address"
                    >{{ old('address') }}</textarea>
                    @error('address')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </x-card>

        <!-- Class Enrollment -->
        <x-card title="Class Enrollment" class="mb-6">
            <div class="space-y-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Select the classes to enroll this student in.
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($classes as $class)
                        <label class="flex items-center p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer">
                            <input type="checkbox" 
                                   name="class_ids[]" 
                                   value="{{ $class->id }}"
                                   {{ in_array($class->id, old('class_ids', [])) ? 'checked' : '' }}
                                   class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700">
                            <span class="ml-3">
                                <span class="block text-sm font-medium text-gray-900 dark:text-white">
                                    Grade {{ $class->grade_level }} - {{ $class->section }}
                                </span>
                                <span class="block text-xs text-gray-500 dark:text-gray-400">
                                    {{ $class->schoolYear->name ?? 'No school year' }}
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>

                @if($classes->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                        No classes available for enrollment.
                    </p>
                @endif
            </div>
        </x-card>

        <!-- Settings -->
        <x-card title="Settings" class="mb-6">
            <div class="space-y-4">
                <label class="flex items-center">
                    <input type="checkbox" 
                           name="is_active" 
                           value="1"
                           {{ old('is_active', true) ? 'checked' : '' }}
                           class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700">
                    <span class="ml-3">
                        <span class="block text-sm font-medium text-gray-900 dark:text-white">Active</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400">Student can be scanned for attendance</span>
                    </span>
                </label>

                <label class="flex items-center">
                    <input type="checkbox" 
                           name="sms_enabled" 
                           value="1"
                           {{ old('sms_enabled') ? 'checked' : '' }}
                           class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700">
                    <span class="ml-3">
                        <span class="block text-sm font-medium text-gray-900 dark:text-white">SMS Notifications</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400">Send SMS to parent when student scans</span>
                    </span>
                </label>
            </div>
        </x-card>

        <!-- Actions -->
        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('students.index') }}">
                <x-button type="button" variant="outline">Cancel</x-button>
            </a>
            <x-button type="submit" variant="primary">Create Student</x-button>
        </div>
    </form>
</div>
@endsection

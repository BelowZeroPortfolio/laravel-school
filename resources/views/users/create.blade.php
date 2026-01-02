@extends('layouts.app')

@section('title', 'Add User')

@section('content')
<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('users.index') }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Users
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">Add New User</h1>
    </div>

    <form method="POST" action="{{ route('users.store') }}">
        @csrf

        <!-- Account Information -->
        <x-card title="Account Information" class="mb-6">
            <div class="space-y-4">
                <x-input 
                    type="text" 
                    name="username" 
                    label="Username"
                    :value="old('username')"
                    required
                    placeholder="Enter username"
                    autocomplete="off"
                />

                <x-input 
                    type="text" 
                    name="full_name" 
                    label="Full Name"
                    :value="old('full_name')"
                    required
                    placeholder="Enter full name"
                />

                <x-input 
                    type="email" 
                    name="email" 
                    label="Email Address"
                    :value="old('email')"
                    placeholder="email@example.com"
                />

                <x-select 
                    name="role" 
                    label="Role"
                    required
                >
                    <option value="teacher" {{ old('role') === 'teacher' ? 'selected' : '' }}>Teacher</option>
                    <option value="principal" {{ old('role') === 'principal' ? 'selected' : '' }}>Principal</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                </x-select>
            </div>
        </x-card>

        <!-- Password -->
        <x-card title="Password" class="mb-6">
            <div class="space-y-4">
                <x-input 
                    type="password" 
                    name="password" 
                    label="Password"
                    required
                    placeholder="Enter password"
                    autocomplete="new-password"
                />

                <x-input 
                    type="password" 
                    name="password_confirmation" 
                    label="Confirm Password"
                    required
                    placeholder="Confirm password"
                    autocomplete="new-password"
                />
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
                        <span class="block text-xs text-gray-500 dark:text-gray-400">User can log in to the system</span>
                    </span>
                </label>

                <label class="flex items-center">
                    <input type="checkbox" 
                           name="is_premium" 
                           value="1"
                           {{ old('is_premium') ? 'checked' : '' }}
                           class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700">
                    <span class="ml-3">
                        <span class="block text-sm font-medium text-gray-900 dark:text-white">Premium Account</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400">Access to premium features</span>
                    </span>
                </label>

                <x-input 
                    type="date" 
                    name="premium_expires_at" 
                    label="Premium Expires At"
                    :value="old('premium_expires_at')"
                    hint="Leave empty for no expiration"
                />
            </div>
        </x-card>

        <!-- Actions -->
        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('users.index') }}">
                <x-button type="button" variant="outline">Cancel</x-button>
            </a>
            <x-button type="submit" variant="primary">Create User</x-button>
        </div>
    </form>
</div>
@endsection

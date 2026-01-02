@extends('layouts.guest')

@section('title', 'Login')

@section('content')
<div class="w-full max-w-md">
    <x-card>
        <x-slot name="header">
            <div class="text-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    QR Attendance System
                </h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Sign in to your account
                </p>
            </div>
        </x-slot>

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <!-- Username -->
            <x-input 
                type="text" 
                name="username" 
                label="Username" 
                :value="old('username')"
                required 
                autofocus
                autocomplete="username"
                placeholder="Enter your username"
            />

            <!-- Password -->
            <x-input 
                type="password" 
                name="password" 
                label="Password" 
                required
                autocomplete="current-password"
                placeholder="Enter your password"
            />

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <input type="checkbox" 
                           name="remember" 
                           class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:bg-gray-700 dark:focus:ring-offset-gray-800"
                           {{ old('remember') ? 'checked' : '' }}>
                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Remember me</span>
                </label>
            </div>

            <!-- Submit Button -->
            <x-button type="submit" variant="primary" class="w-full">
                Sign In
            </x-button>
        </form>

        @if($errors->any())
            <div class="mt-4">
                <x-alert type="error" :dismissible="false">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-alert>
            </div>
        @endif
    </x-card>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Edit School')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex items-center space-x-4">
        <a href="{{ route('super-admin.schools.show', $school) }}" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit School</h1>
            <p class="text-gray-600 dark:text-gray-400">{{ $school->name }}</p>
        </div>
    </div>

    <form method="POST" action="{{ route('super-admin.schools.update', $school) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- School Information -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">School Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">School Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $school->name) }}" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">School Code *</label>
                    <input type="text" name="code" id="code" value="{{ old('code', $school->code) }}" required class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 uppercase @error('code') border-red-500 @enderror">
                    @error('code')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $school->email) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $school->phone) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 @error('phone') border-red-500 @enderror">
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Address</label>
                    <textarea name="address" id="address" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 @error('address') border-red-500 @enderror">{{ old('address', $school->address) }}</textarea>
                    @error('address')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $school->is_active) ? 'checked' : '' }} class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('super-admin.schools.show', $school) }}" class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
                Update School
            </button>
        </div>
    </form>
</div>
@endsection

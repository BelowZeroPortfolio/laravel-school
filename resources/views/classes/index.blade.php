@extends('layouts.app')

@section('title', 'Classes')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Classes</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Manage class sections and enrollments
            </p>
        </div>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('classes.create') }}">
            <x-button variant="primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Class
            </x-button>
        </a>
        @endif
    </div>

    <!-- Filters -->
    <x-card>
        <form method="GET" action="{{ route('classes.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-select 
                name="school_year_id" 
                label="School Year"
                placeholder="All School Years"
            >
                @foreach($schoolYears as $schoolYear)
                    <option value="{{ $schoolYear->id }}" {{ request('school_year_id') == $schoolYear->id ? 'selected' : '' }}>
                        {{ $schoolYear->name }}
                        @if($schoolYear->is_active) (Active) @endif
                    </option>
                @endforeach
            </x-select>

            <x-input 
                type="text" 
                name="grade_level" 
                label="Grade Level"
                :value="request('grade_level')"
                placeholder="e.g., 7, 8, 9..."
            />

            <x-select 
                name="is_active" 
                label="Status"
                placeholder="All Status"
            >
                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
            </x-select>

            <div class="flex items-end space-x-2">
                <x-button type="submit" variant="primary">Filter</x-button>
                <a href="{{ route('classes.index') }}">
                    <x-button type="button" variant="outline">Clear</x-button>
                </a>
            </div>
        </form>
    </x-card>

    <!-- Classes Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($classes as $class)
            <x-card :padding="false">
                <div class="p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                Grade {{ $class->grade_level }} - {{ $class->section }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $class->schoolYear->name ?? 'No school year' }}
                            </p>
                        </div>
                        <x-badge type="{{ $class->is_active ? 'success' : 'default' }}" size="sm">
                            {{ $class->is_active ? 'Active' : 'Inactive' }}
                        </x-badge>
                    </div>

                    <div class="mt-4 space-y-2">
                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            {{ $class->teacher->full_name ?? 'No teacher assigned' }}
                        </div>
                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                            </svg>
                            {{ $class->students_count }} / {{ $class->max_capacity }} students
                        </div>
                    </div>
                </div>

                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 flex justify-between">
                    <a href="{{ route('classes.show', $class) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                        View Details
                    </a>
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('classes.edit', $class) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300">
                        Edit
                    </a>
                    @endif
                </div>
            </x-card>
        @empty
            <div class="col-span-full">
                <x-card>
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">No classes found</p>
                    </div>
                </x-card>
            </div>
        @endforelse
    </div>

    @if($classes->hasPages())
        <div class="mt-6">
            {{ $classes->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection

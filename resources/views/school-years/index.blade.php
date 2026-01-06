@extends('layouts.app')

@section('title', 'School Years')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">School Years</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Manage academic year periods
            </p>
        </div>
        <a href="{{ route('school-years.create') }}">
            <x-button variant="primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add School Year
            </x-button>
        </a>
    </div>

    <!-- Active School Year Highlight -->
    @if($activeSchoolYear)
    <x-card class="border-2 border-green-500 dark:border-green-400">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-green-100 dark:bg-green-900/50 rounded-lg">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        {{ $activeSchoolYear->name }}
                        <x-badge type="success" size="sm" class="ml-2">Active</x-badge>
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $activeSchoolYear->start_date->format('M d, Y') }} - {{ $activeSchoolYear->end_date->format('M d, Y') }}
                    </p>
                </div>
            </div>
            <a href="{{ route('school-years.show', $activeSchoolYear) }}">
                <x-button variant="outline" size="sm">View Details</x-button>
            </a>
        </div>
    </x-card>
    @endif

    <!-- School Years Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($schoolYears as $schoolYear)
            <x-card :padding="false" class="{{ $schoolYear->is_active ? 'ring-2 ring-green-500 dark:ring-green-400' : '' }}">
                <div class="p-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                {{ $schoolYear->name }}
                            </h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $schoolYear->start_date->format('M Y') }} - {{ $schoolYear->end_date->format('M Y') }}
                            </p>
                        </div>
                        <div class="flex flex-col items-end space-y-1">
                            @if($schoolYear->is_active)
                                <x-badge type="success" size="sm">Active</x-badge>
                            @endif
                            @if($schoolYear->is_locked)
                                <x-badge type="warning" size="sm">Locked</x-badge>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                        <div class="p-2 bg-gray-50 dark:bg-gray-800 rounded">
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $schoolYear->classes_count ?? 0 }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Classes</p>
                        </div>
                        <div class="p-2 bg-gray-50 dark:bg-gray-800 rounded">
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($schoolYear->attendances_count ?? 0) }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Attendance</p>
                        </div>
                        <div class="p-2 bg-gray-50 dark:bg-gray-800 rounded">
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($schoolYear->teacher_attendances_count ?? 0) }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Teacher Att.</p>
                        </div>
                    </div>
                </div>

                <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 flex justify-between">
                    <a href="{{ route('school-years.show', $schoolYear) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                        View Details
                    </a>
                    <div class="flex items-center space-x-2">
                        @if(!$schoolYear->is_active && !$schoolYear->is_locked)
                        <div x-data="{ showModal: false, confirmText: '' }">
                            <button type="button" @click="showModal = true; confirmText = ''" class="text-sm text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300">
                                Activate
                            </button>
                            
                            <!-- Confirmation Modal -->
                            <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
                                <div class="flex items-center justify-center min-h-screen px-4">
                                    <div class="fixed inset-0 bg-black/50" @click="showModal = false"></div>
                                    <div class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
                                        <div class="flex items-start mb-4">
                                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-yellow-100 dark:bg-yellow-900/50 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                </svg>
                                            </div>
                                            <div class="ml-4">
                                                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Activate School Year</h3>
                                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                                    You are about to activate <span class="font-semibold">{{ $schoolYear->name }}</span>. This will deactivate any currently active school year.
                                                </p>
                                            </div>
                                        </div>
                                        <div class="mb-4">
                                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                                                Type <span class="font-mono font-bold text-red-600 dark:text-red-400">PROCEED</span> to confirm:
                                            </p>
                                            <input type="text" x-model="confirmText" 
                                                   class="w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                                                   placeholder="Type PROCEED">
                                            <p x-show="confirmText.length > 0 && confirmText !== 'PROCEED'" class="mt-1 text-xs text-red-600 dark:text-red-400">
                                                Please type PROCEED exactly.
                                            </p>
                                        </div>
                                        <div class="flex justify-end gap-3">
                                            <x-button type="button" variant="outline" @click="showModal = false">Cancel</x-button>
                                            <form method="POST" action="{{ route('school-years.activate', $schoolYear) }}">
                                                @csrf
                                                <x-button type="submit" variant="success" x-bind:disabled="confirmText !== 'PROCEED'" 
                                                          x-bind:class="confirmText !== 'PROCEED' ? 'opacity-50 cursor-not-allowed' : ''">
                                                    Activate
                                                </x-button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if(!$schoolYear->is_locked)
                        <a href="{{ route('school-years.edit', $schoolYear) }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300">
                            Edit
                        </a>
                        @endif
                    </div>
                </div>
            </x-card>
        @empty
            <div class="col-span-full">
                <x-card>
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="mt-2 text-gray-500 dark:text-gray-400">No school years found</p>
                        <a href="{{ route('school-years.create') }}" class="mt-2 inline-block text-indigo-600 dark:text-indigo-400 hover:underline">
                            Create your first school year
                        </a>
                    </div>
                </x-card>
            </div>
        @endforelse
    </div>
</div>
@endsection

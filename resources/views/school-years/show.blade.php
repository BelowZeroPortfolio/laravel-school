@extends('layouts.app')

@section('title', $schoolYear->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('school-years.index') }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to School Years
            </a>
            <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $schoolYear->name }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $schoolYear->start_date->format('M d, Y') }} - {{ $schoolYear->end_date->format('M d, Y') }}
            </p>
        </div>
        <div class="flex items-center space-x-3">
            @if($schoolYear->is_active)
                <x-badge type="success">Active</x-badge>
            @endif
            @if($schoolYear->is_locked)
                <x-badge type="warning">Locked</x-badge>
            @endif
            @if(!$schoolYear->is_locked)
            <a href="{{ route('school-years.edit', $schoolYear) }}">
                <x-button variant="outline">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </x-button>
            </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- School Year Details -->
            <x-card title="Details">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $schoolYear->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                        <dd class="mt-1 flex items-center space-x-2">
                            <x-badge type="{{ $schoolYear->is_active ? 'success' : 'default' }}" size="sm">
                                {{ $schoolYear->is_active ? 'Active' : 'Inactive' }}
                            </x-badge>
                            @if($schoolYear->is_locked)
                                <x-badge type="warning" size="sm">Locked</x-badge>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Start Date</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $schoolYear->start_date->format('F d, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">End Date</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $schoolYear->end_date->format('F d, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Duration</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $schoolYear->start_date->diffInMonths($schoolYear->end_date) }} months
                        </dd>
                    </div>
                </dl>
            </x-card>

            <!-- Classes -->
            <x-card title="Classes" :padding="false">
                @if($schoolYear->classes->count() > 0)
                    <x-table>
                        <x-slot name="head">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Class</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Teacher</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Students</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            </tr>
                        </x-slot>
                        @foreach($schoolYear->classes as $class)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('classes.show', $class) }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                                        Grade {{ $class->grade_level }} - {{ $class->section }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $class->teacher->full_name ?? 'No teacher' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $class->students_count }} / {{ $class->max_capacity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-badge type="{{ $class->is_active ? 'success' : 'default' }}" size="sm">
                                        {{ $class->is_active ? 'Active' : 'Inactive' }}
                                    </x-badge>
                                </td>
                            </tr>
                        @endforeach
                    </x-table>
                @else
                    <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                        No classes in this school year.
                    </div>
                @endif
            </x-card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Statistics -->
            <x-card title="Statistics">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Classes</span>
                        <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $schoolYear->classes_count ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Student Attendance</span>
                        <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($schoolYear->attendances_count ?? 0) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Teacher Attendance</span>
                        <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($schoolYear->teacher_attendances_count ?? 0) }}</span>
                    </div>
                </div>
            </x-card>

            <!-- Quick Actions -->
            <x-card title="Actions">
                <div class="space-y-2">
                    @if(!$schoolYear->is_active && !$schoolYear->is_locked)
                    <div x-data="{ showModal: false, confirmText: '' }">
                        <x-button type="button" variant="success" class="w-full justify-start" @click="showModal = true; confirmText = ''">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Activate School Year
                        </x-button>
                        
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

                    @if(!$schoolYear->is_active && !$schoolYear->is_locked)
                    <form method="POST" action="{{ route('school-years.lock', $schoolYear) }}" 
                          onsubmit="return confirm('Are you sure you want to lock this school year? Attendance records will no longer be modifiable.')">
                        @csrf
                        <x-button type="submit" variant="warning" class="w-full justify-start">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            Lock School Year
                        </x-button>
                    </form>
                    @endif

                    @if($schoolYear->is_locked)
                    <form method="POST" action="{{ route('school-years.unlock', $schoolYear) }}">
                        @csrf
                        <x-button type="submit" variant="outline" class="w-full justify-start">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z" />
                            </svg>
                            Unlock School Year
                        </x-button>
                    </form>
                    @endif

                    @if(!$schoolYear->is_locked)
                    <a href="{{ route('school-years.edit', $schoolYear) }}" class="block">
                        <x-button variant="outline" class="w-full justify-start">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit School Year
                        </x-button>
                    </a>
                    @endif
                </div>
            </x-card>

            <!-- Timestamps -->
            <x-card title="Info">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">Created</dt>
                        <dd class="text-gray-900 dark:text-white">{{ $schoolYear->created_at->format('M d, Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">Updated</dt>
                        <dd class="text-gray-900 dark:text-white">{{ $schoolYear->updated_at->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </x-card>
        </div>
    </div>
</div>
@endsection

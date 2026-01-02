@extends('layouts.app')

@section('title', 'Time Schedules')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Time Schedules</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Manage attendance time rules and late thresholds
            </p>
        </div>
        <a href="{{ route('time-schedules.create') }}">
            <x-button variant="primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Schedule
            </x-button>
        </a>
    </div>

    <!-- Active Schedule Highlight -->
    @if($activeSchedule)
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
                        {{ $activeSchedule->name }}
                        <x-badge type="success" size="sm" class="ml-2">Active</x-badge>
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ \Carbon\Carbon::parse($activeSchedule->time_in)->format('h:i A') }} - 
                        {{ \Carbon\Carbon::parse($activeSchedule->time_out)->format('h:i A') }}
                        | Late after {{ $activeSchedule->late_threshold_minutes }} minutes
                    </p>
                </div>
            </div>
            <a href="{{ route('time-schedules.show', $activeSchedule) }}">
                <x-button variant="outline" size="sm">View Details</x-button>
            </a>
        </div>
    </x-card>
    @endif

    <!-- Schedules List -->
    <x-card title="All Schedules" :padding="false">
        <x-table>
            <x-slot name="head">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Time In</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Time Out</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Late Threshold</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Created By</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </x-slot>

            @forelse($schedules as $schedule)
                <tr class="{{ $schedule->is_active ? 'bg-green-50 dark:bg-green-900/10' : '' }}">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $schedule->name }}
                        </div>
                        @if($schedule->effective_date)
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                Effective: {{ \Carbon\Carbon::parse($schedule->effective_date)->format('M d, Y') }}
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                        {{ \Carbon\Carbon::parse($schedule->time_in)->format('h:i A') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                        {{ \Carbon\Carbon::parse($schedule->time_out)->format('h:i A') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ $schedule->late_threshold_minutes }} minutes
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <x-badge type="{{ $schedule->is_active ? 'success' : 'default' }}" size="sm">
                            {{ $schedule->is_active ? 'Active' : 'Inactive' }}
                        </x-badge>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ $schedule->creator->full_name ?? 'System' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end space-x-2">
                            <a href="{{ route('time-schedules.show', $schedule) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                View
                            </a>
                            <a href="{{ route('time-schedules.edit', $schedule) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-300">
                                Edit
                            </a>
                            @if(!$schedule->is_active)
                                <form method="POST" action="{{ route('time-schedules.activate', $schedule) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300">
                                        Activate
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="mt-2">No time schedules found</p>
                        <a href="{{ route('time-schedules.create') }}" class="mt-2 inline-block text-indigo-600 dark:text-indigo-400 hover:underline">
                            Create your first schedule
                        </a>
                    </td>
                </tr>
            @endforelse
        </x-table>
    </x-card>

    <!-- Change Logs Link -->
    <div class="text-center">
        <a href="{{ route('time-schedules.logs') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
            View All Change Logs →
        </a>
    </div>
</div>
@endsection

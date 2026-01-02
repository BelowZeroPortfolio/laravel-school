@extends('layouts.app')

@section('title', $schedule->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('time-schedules.index') }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Schedules
            </a>
            <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $schedule->name }}</h1>
        </div>
        <div class="flex items-center space-x-3">
            <x-badge type="{{ $schedule->is_active ? 'success' : 'default' }}">
                {{ $schedule->is_active ? 'Active' : 'Inactive' }}
            </x-badge>
            <a href="{{ route('time-schedules.edit', $schedule) }}">
                <x-button variant="outline">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </x-button>
            </a>
            @if(!$schedule->is_active)
            <form method="POST" action="{{ route('time-schedules.activate', $schedule) }}">
                @csrf
                @method('PATCH')
                <x-button type="submit" variant="success">Activate</x-button>
            </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Schedule Details -->
            <x-card title="Schedule Details">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $schedule->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                        <dd class="mt-1">
                            <x-badge type="{{ $schedule->is_active ? 'success' : 'default' }}" size="sm">
                                {{ $schedule->is_active ? 'Active' : 'Inactive' }}
                            </x-badge>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Time In</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ \Carbon\Carbon::parse($schedule->time_in)->format('h:i A') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Time Out</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ \Carbon\Carbon::parse($schedule->time_out)->format('h:i A') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Late Threshold</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $schedule->late_threshold_minutes }} minutes
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Cutoff Time</dt>
                        <dd class="mt-1 text-sm text-red-600 dark:text-red-400 font-medium">
                            {{ \Carbon\Carbon::parse($schedule->time_in)->addMinutes($schedule->late_threshold_minutes)->format('h:i A') }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Effective Date</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $schedule->effective_date ? $schedule->effective_date->format('M d, Y') : 'Not set' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Created By</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $schedule->creator->full_name ?? 'System' }}
                        </dd>
                    </div>
                </dl>
            </x-card>

            <!-- Change Logs -->
            <x-card title="Change History" :padding="false">
                @if($logs->count() > 0)
                    <div class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($logs as $log)
                            <div class="p-4">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ ucfirst($log->action) }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            by {{ $log->changedBy->full_name ?? 'System' }} 
                                            on {{ $log->created_at->format('M d, Y h:i A') }}
                                        </p>
                                    </div>
                                    <x-badge type="{{ $log->action === 'create' ? 'success' : ($log->action === 'delete' ? 'danger' : 'info') }}" size="sm">
                                        {{ ucfirst($log->action) }}
                                    </x-badge>
                                </div>
                                @if($log->change_reason)
                                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                        <span class="font-medium">Reason:</span> {{ $log->change_reason }}
                                    </p>
                                @endif
                                @if($log->action === 'update' && $log->old_values && $log->new_values)
                                    <div class="mt-2 text-xs">
                                        <details class="cursor-pointer">
                                            <summary class="text-indigo-600 dark:text-indigo-400 hover:underline">View changes</summary>
                                            <div class="mt-2 grid grid-cols-2 gap-4 p-2 bg-gray-50 dark:bg-gray-800 rounded">
                                                <div>
                                                    <p class="font-medium text-gray-500 dark:text-gray-400">Old Values</p>
                                                    <pre class="text-xs text-gray-600 dark:text-gray-300 overflow-x-auto">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-500 dark:text-gray-400">New Values</p>
                                                    <pre class="text-xs text-gray-600 dark:text-gray-300 overflow-x-auto">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                                </div>
                                            </div>
                                        </details>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                        No change history available.
                    </div>
                @endif
            </x-card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Info -->
            <x-card title="Quick Info">
                <div class="space-y-4">
                    <div class="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Working Hours</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ \Carbon\Carbon::parse($schedule->time_in)->diffInHours(\Carbon\Carbon::parse($schedule->time_out)) }} hours
                        </p>
                    </div>
                    <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Late After</p>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                            {{ \Carbon\Carbon::parse($schedule->time_in)->addMinutes($schedule->late_threshold_minutes)->format('h:i A') }}
                        </p>
                    </div>
                </div>
            </x-card>

            <!-- Timestamps -->
            <x-card title="Timestamps">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">Created</dt>
                        <dd class="text-gray-900 dark:text-white">{{ $schedule->created_at->format('M d, Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">Updated</dt>
                        <dd class="text-gray-900 dark:text-white">{{ $schedule->updated_at->format('M d, Y') }}</dd>
                    </div>
                </dl>
            </x-card>
        </div>
    </div>
</div>
@endsection

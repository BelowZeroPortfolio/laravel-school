@extends('layouts.app')

@section('title', 'Schedule Change Logs')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <a href="{{ route('time-schedules.index') }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Schedules
        </a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">Schedule Change Logs</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Audit trail of all time schedule changes</p>
    </div>

    <!-- Logs Table -->
    <x-card :padding="false">
        <x-table>
            <x-slot name="head">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Schedule</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Changed By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Reason</th>
                </tr>
            </x-slot>

            @forelse($logs as $log)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                        {{ $log->created_at->format('M d, Y') }}
                        <span class="block text-xs text-gray-500 dark:text-gray-400">
                            {{ $log->created_at->format('h:i A') }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($log->schedule)
                            <a href="{{ route('time-schedules.show', $log->schedule) }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                                {{ $log->schedule->name }}
                            </a>
                        @else
                            <span class="text-sm text-gray-500 dark:text-gray-400">Deleted Schedule</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <x-badge type="{{ $log->action === 'create' ? 'success' : ($log->action === 'delete' ? 'danger' : ($log->action === 'activate' ? 'primary' : 'info')) }}" size="sm">
                            {{ ucfirst($log->action) }}
                        </x-badge>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ $log->changedBy->full_name ?? 'System' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                        {{ $log->change_reason ?? '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                        No change logs found.
                    </td>
                </tr>
            @endforelse
        </x-table>
    </x-card>
</div>
@endsection

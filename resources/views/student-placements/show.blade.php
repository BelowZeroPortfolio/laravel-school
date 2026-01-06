@extends('layouts.app')

@section('title', 'Placement History - ' . $student->first_name . ' ' . $student->last_name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('student-placements.index') }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Placement History</h1>
            </div>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                View all class enrollments for this student
            </p>
        </div>
    </div>

    <!-- Student Info Card -->
    <x-card>
        <div class="flex items-center">
            <div class="flex-shrink-0 h-16 w-16 bg-indigo-100 dark:bg-indigo-900/50 rounded-full flex items-center justify-center">
                <span class="text-xl font-medium text-indigo-600 dark:text-indigo-400">
                    {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                </span>
            </div>
            <div class="ml-4">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                    {{ $student->last_name }}, {{ $student->first_name }}
                </h2>
                <div class="mt-1 flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                    <span>ID: {{ $student->student_id }}</span>
                    @if($student->lrn)
                        <span>LRN: {{ $student->lrn }}</span>
                    @endif
                    <x-badge type="{{ $student->is_active ? 'success' : 'default' }}" size="sm">
                        {{ $student->is_active ? 'Active' : 'Inactive' }}
                    </x-badge>
                </div>
            </div>
        </div>
    </x-card>

    <!-- Placement History Timeline -->
    <x-card title="Enrollment History" subtitle="All class placements across school years">
        @if($history->count() > 0)
            <div class="flow-root">
                <ul role="list" class="-mb-8">
                    @foreach($history as $index => $placement)
                        <li>
                            <div class="relative pb-8">
                                @if(!$loop->last)
                                    <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200 dark:bg-gray-700" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3">
                                    <div>
                                        @php
                                            $statusColors = [
                                                'enrolled' => 'bg-green-500',
                                                'transferred' => 'bg-yellow-500',
                                                'dropped' => 'bg-red-500',
                                            ];
                                            $dotColor = $statusColors[$placement['enrollment_status']] ?? 'bg-gray-400';
                                        @endphp
                                        <span class="h-8 w-8 rounded-full {{ $dotColor }} flex items-center justify-center ring-8 ring-white dark:ring-gray-800">
                                            @if($placement['enrollment_status'] === 'enrolled')
                                                <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            @elseif($placement['enrollment_status'] === 'transferred')
                                                <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                                </svg>
                                            @else
                                                <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            @endif
                                        </span>
                                    </div>
                                    <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                Grade {{ $placement['grade_level'] }} - {{ $placement['section'] }}
                                            </p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $placement['school_year'] ?? 'Unknown School Year' }}
                                            </p>
                                            <div class="mt-2 flex flex-wrap gap-2">
                                                <x-badge type="{{ $placement['is_active'] ? 'success' : 'default' }}" size="sm">
                                                    {{ $placement['is_active'] ? 'Active' : 'Inactive' }}
                                                </x-badge>
                                                <x-badge type="info" size="sm">
                                                    {{ ucfirst($placement['enrollment_type'] ?? 'regular') }}
                                                </x-badge>
                                                @php
                                                    $statusBadgeType = match($placement['enrollment_status']) {
                                                        'enrolled' => 'success',
                                                        'transferred' => 'warning',
                                                        'dropped' => 'danger',
                                                        default => 'default'
                                                    };
                                                @endphp
                                                <x-badge type="{{ $statusBadgeType }}" size="sm">
                                                    {{ ucfirst($placement['enrollment_status'] ?? 'unknown') }}
                                                </x-badge>
                                            </div>
                                            @if($placement['status_reason'])
                                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400 italic">
                                                    "{{ $placement['status_reason'] }}"
                                                </p>
                                            @endif
                                        </div>
                                        <div class="whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400">
                                            <p>Enrolled: {{ $placement['enrolled_at'] ? \Carbon\Carbon::parse($placement['enrolled_at'])->format('M d, Y') : 'N/A' }}</p>
                                            @if($placement['status_changed_at'] && $placement['status_changed_at'] !== $placement['enrolled_at'])
                                                <p class="text-xs">
                                                    Updated: {{ \Carbon\Carbon::parse($placement['status_changed_at'])->format('M d, Y') }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <p class="mt-2 text-gray-500 dark:text-gray-400">No enrollment history found</p>
                <p class="text-sm text-gray-400 dark:text-gray-500">This student has not been placed in any class yet.</p>
            </div>
        @endif
    </x-card>
</div>
@endsection

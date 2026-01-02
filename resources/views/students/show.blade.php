@extends('layouts.app')

@section('title', $student->full_name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('students.index') }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Students
            </a>
            <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $student->full_name }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $student->student_id }}</p>
        </div>
        <div class="flex items-center space-x-3">
            <x-badge type="{{ $student->is_active ? 'success' : 'default' }}">
                {{ $student->is_active ? 'Active' : 'Inactive' }}
            </x-badge>
            <a href="{{ route('students.edit', $student) }}">
                <x-button variant="outline">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </x-button>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Student Details -->
            <x-card title="Student Information">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Full Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->full_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Student ID</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono">{{ $student->student_id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">LRN</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono">{{ $student->lrn ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">SMS Notifications</dt>
                        <dd class="mt-1">
                            <x-badge type="{{ $student->sms_enabled ? 'success' : 'default' }}" size="sm">
                                {{ $student->sms_enabled ? 'Enabled' : 'Disabled' }}
                            </x-badge>
                        </dd>
                    </div>
                </dl>
            </x-card>

            <!-- Parent/Guardian Info -->
            <x-card title="Parent/Guardian Information">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->parent_name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->parent_phone ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->parent_email ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Address</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->address ?? '-' }}</dd>
                    </div>
                </dl>
            </x-card>

            <!-- Recent Attendance -->
            <x-card title="Recent Attendance" :padding="false">
                @if($student->attendances->count() > 0)
                    <x-table>
                        <x-slot name="head">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Check In</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Check Out</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            </tr>
                        </x-slot>
                        @foreach($student->attendances as $attendance)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $attendance->attendance_date->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $attendance->check_in_time ? $attendance->check_in_time->format('h:i A') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $attendance->check_out_time ? $attendance->check_out_time->format('h:i A') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-badge type="{{ $attendance->status === 'present' ? 'success' : ($attendance->status === 'late' ? 'warning' : 'danger') }}" size="sm">
                                        {{ ucfirst($attendance->status) }}
                                    </x-badge>
                                </td>
                            </tr>
                        @endforeach
                    </x-table>
                @else
                    <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                        No attendance records found.
                    </div>
                @endif
            </x-card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- QR Code -->
            <x-card title="QR Code">
                <div class="text-center">
                    <div class="inline-flex items-center justify-center w-32 h-32 bg-gray-100 dark:bg-gray-800 rounded-lg mb-3">
                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Scan: {{ $student->lrn ?? $student->student_id }}
                    </p>
                </div>
            </x-card>

            <!-- Class Enrollments -->
            <x-card title="Class Enrollments">
                @if($student->classes->count() > 0)
                    <div class="space-y-3">
                        @foreach($student->classes as $class)
                            <a href="{{ route('classes.show', $class) }}" class="block p-3 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            Grade {{ $class->grade_level }} - {{ $class->section }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $class->teacher->full_name ?? 'No teacher' }}
                                        </p>
                                    </div>
                                    <x-badge type="{{ $class->pivot->is_active ? 'success' : 'default' }}" size="sm">
                                        {{ $class->pivot->is_active ? 'Active' : 'Inactive' }}
                                    </x-badge>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                        Not enrolled in any classes.
                    </p>
                @endif
            </x-card>
        </div>
    </div>
</div>
@endsection

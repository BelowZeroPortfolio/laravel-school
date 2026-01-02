@extends('layouts.app')

@section('title', $teacher->full_name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('teacher-monitoring.index') }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Monitoring
            </a>
            <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $teacher->full_name }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Teacher Attendance History</p>
        </div>
        @if($isReadOnly)
            <x-badge type="info">Read-Only Access</x-badge>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Filters -->
            <x-card>
                <form method="GET" action="{{ route('teacher-monitoring.show', $teacher) }}" class="flex items-end space-x-4">
                    <x-select 
                        name="school_year_id" 
                        label="School Year"
                        placeholder="All School Years"
                        class="flex-1"
                    >
                        @foreach($schoolYears as $schoolYear)
                            <option value="{{ $schoolYear->id }}" {{ $selectedSchoolYearId == $schoolYear->id ? 'selected' : '' }}>
                                {{ $schoolYear->name }}
                                @if($schoolYear->is_active) (Active) @endif
                            </option>
                        @endforeach
                    </x-select>
                    <x-button type="submit" variant="primary">Filter</x-button>
                </form>
            </x-card>

            <!-- Attendance History -->
            <x-card title="Attendance History" :padding="false">
                <x-table>
                    <x-slot name="head">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Time In</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">First Scan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Time Out</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        </tr>
                    </x-slot>
                    @forelse($attendances as $attendance)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $attendance->attendance_date->format('M d, Y') }}
                                <span class="text-xs text-gray-500 dark:text-gray-400 block">
                                    {{ $attendance->attendance_date->format('l') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $attendance->time_in ? $attendance->time_in->format('h:i A') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $attendance->first_student_scan ? $attendance->first_student_scan->format('h:i A') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $attendance->time_out ? $attendance->time_out->format('h:i A') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'confirmed' => 'success',
                                        'late' => 'danger',
                                        'pending' => 'warning',
                                        'absent' => 'default',
                                        'no_scan' => 'info',
                                    ];
                                @endphp
                                <x-badge type="{{ $statusColors[$attendance->attendance_status] ?? 'default' }}" size="sm">
                                    {{ ucfirst(str_replace('_', ' ', $attendance->attendance_status)) }}
                                </x-badge>
                                @if($attendance->late_status)
                                    <x-badge type="{{ $attendance->late_status === 'on_time' ? 'success' : 'danger' }}" size="sm" class="ml-1">
                                        {{ ucfirst(str_replace('_', ' ', $attendance->late_status)) }}
                                    </x-badge>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                No attendance records found.
                            </td>
                        </tr>
                    @endforelse
                </x-table>

                @if($attendances->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $attendances->withQueryString()->links() }}
                    </div>
                @endif
            </x-card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Teacher Info -->
            <x-card title="Teacher Information">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0 h-16 w-16 bg-green-100 dark:bg-green-900/50 rounded-full flex items-center justify-center">
                        <span class="text-2xl font-medium text-green-600 dark:text-green-400">
                            {{ strtoupper(substr($teacher->full_name, 0, 2)) }}
                        </span>
                    </div>
                    <div class="ml-4">
                        <p class="text-lg font-medium text-gray-900 dark:text-white">{{ $teacher->full_name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $teacher->email ?? 'No email' }}</p>
                    </div>
                </div>
                <dl class="space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Username</dt>
                        <dd class="text-sm text-gray-900 dark:text-white">{{ $teacher->username }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Status</dt>
                        <dd>
                            <x-badge type="{{ $teacher->is_active ? 'success' : 'default' }}" size="sm">
                                {{ $teacher->is_active ? 'Active' : 'Inactive' }}
                            </x-badge>
                        </dd>
                    </div>
                </dl>
            </x-card>

            <!-- Summary Stats -->
            <x-card title="Summary">
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Total Days</span>
                        <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $summary['total'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Confirmed</span>
                        <span class="text-lg font-semibold text-green-600 dark:text-green-400">{{ $summary['confirmed'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Late</span>
                        <span class="text-lg font-semibold text-red-600 dark:text-red-400">{{ $summary['late'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Absent</span>
                        <span class="text-lg font-semibold text-gray-600 dark:text-gray-400">{{ $summary['absent'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">No Scan</span>
                        <span class="text-lg font-semibold text-blue-600 dark:text-blue-400">{{ $summary['no_scan'] ?? 0 }}</span>
                    </div>
                    @if(isset($summary['total']) && $summary['total'] > 0)
                        <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500 dark:text-gray-400">Punctuality Rate</span>
                                <span class="text-lg font-semibold text-indigo-600 dark:text-indigo-400">
                                    {{ round((($summary['confirmed'] ?? 0) / $summary['total']) * 100) }}%
                                </span>
                            </div>
                        </div>
                    @endif
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection

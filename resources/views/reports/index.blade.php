@extends('layouts.app')

@section('title', 'Attendance Reports')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Attendance Reports</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                View and export attendance records with filters
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('reports.daily-summary') }}">
                <x-button variant="outline">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Daily Summary
                </x-button>
            </a>
        </div>
    </div>

    <!-- Statistics Cards (Requirement 17.2) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-card>
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-blue-100 dark:bg-blue-900/50 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Records</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $statistics['total'] }}</p>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-green-100 dark:bg-green-900/50 rounded-lg">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Present</p>
                    <p class="text-2xl font-semibold text-green-600 dark:text-green-400">{{ $statistics['present']['count'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $statistics['present']['percentage'] }}%</p>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-yellow-100 dark:bg-yellow-900/50 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Late</p>
                    <p class="text-2xl font-semibold text-yellow-600 dark:text-yellow-400">{{ $statistics['late']['count'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $statistics['late']['percentage'] }}%</p>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-red-100 dark:bg-red-900/50 rounded-lg">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Absent</p>
                    <p class="text-2xl font-semibold text-red-600 dark:text-red-400">{{ $statistics['absent']['count'] }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $statistics['absent']['percentage'] }}%</p>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Filters (Requirement 17.1) -->
    <x-card title="Filter Reports">
        <form method="GET" action="{{ route('reports.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <x-input 
                    type="date" 
                    name="start_date" 
                    label="Start Date"
                    :value="$filters['start_date'] ?? ''"
                />

                <x-input 
                    type="date" 
                    name="end_date" 
                    label="End Date"
                    :value="$filters['end_date'] ?? ''"
                />

                <x-select 
                    name="school_year_id" 
                    label="School Year"
                    placeholder="All School Years"
                >
                    @foreach($schoolYears as $schoolYear)
                        <option value="{{ $schoolYear->id }}" {{ ($filters['school_year_id'] ?? '') == $schoolYear->id ? 'selected' : '' }}>
                            {{ $schoolYear->name }}
                            @if($schoolYear->is_active) (Active) @endif
                        </option>
                    @endforeach
                </x-select>

                <x-select 
                    name="class_id" 
                    label="Class"
                    placeholder="All Classes"
                >
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ ($filters['class_id'] ?? '') == $class->id ? 'selected' : '' }}>
                            Grade {{ $class->grade_level }} - {{ $class->section }}
                        </option>
                    @endforeach
                </x-select>

                <x-select 
                    name="student_id" 
                    label="Student"
                    placeholder="All Students"
                >
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ ($filters['student_id'] ?? '') == $student->id ? 'selected' : '' }}>
                            {{ $student->full_name }}
                        </option>
                    @endforeach
                </x-select>

                <x-select 
                    name="status" 
                    label="Status"
                    placeholder="All Status"
                >
                    <option value="present" {{ ($filters['status'] ?? '') === 'present' ? 'selected' : '' }}>Present</option>
                    <option value="late" {{ ($filters['status'] ?? '') === 'late' ? 'selected' : '' }}>Late</option>
                    <option value="absent" {{ ($filters['status'] ?? '') === 'absent' ? 'selected' : '' }}>Absent</option>
                </x-select>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center space-x-2">
                    <x-button type="submit" variant="primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Apply Filters
                    </x-button>
                    <a href="{{ route('reports.index') }}">
                        <x-button type="button" variant="outline">Clear</x-button>
                    </a>
                </div>

                <!-- Export Buttons (Requirement 17.4) -->
                <div class="flex items-center space-x-2">
                    <a href="{{ route('reports.export.csv', request()->query()) }}">
                        <x-button type="button" variant="success">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Export CSV
                        </x-button>
                    </a>
                    <a href="{{ route('reports.export.pdf', request()->query()) }}">
                        <x-button type="button" variant="danger">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            Export PDF
                        </x-button>
                    </a>
                </div>
            </div>
        </form>
    </x-card>

    <!-- Attendance Records Table -->
    <x-card title="Attendance Records" :padding="false">
        <x-slot name="actions">
            <span class="text-sm text-gray-500 dark:text-gray-400">
                Showing {{ $attendances->firstItem() ?? 0 }} - {{ $attendances->lastItem() ?? 0 }} of {{ $attendances->total() }} records
            </span>
        </x-slot>

        <x-table>
            <x-slot name="head">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">LRN</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Class</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Check In</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Check Out</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                </tr>
            </x-slot>

            @forelse($attendances as $attendance)
                @php
                    $studentClass = $attendance->student?->classes()
                        ->wherePivot('is_active', true)
                        ->first();
                @endphp
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                        {{ $attendance->attendance_date?->format('M d, Y') ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 dark:bg-indigo-900/50 rounded-full flex items-center justify-center">
                                <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                    {{ strtoupper(substr($attendance->student->first_name ?? '', 0, 1) . substr($attendance->student->last_name ?? '', 0, 1)) }}
                                </span>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $attendance->student->full_name ?? 'Unknown' }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $attendance->student->student_id ?? '' }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ $attendance->student->lrn ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        @if($studentClass)
                            Grade {{ $studentClass->grade_level }} - {{ $studentClass->section }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                        {{ $attendance->check_in_time?->format('h:i A') ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ $attendance->check_out_time?->format('h:i A') ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $statusType = match($attendance->status) {
                                'present' => 'success',
                                'late' => 'warning',
                                'absent' => 'danger',
                                default => 'default'
                            };
                        @endphp
                        <x-badge :type="$statusType" size="sm">
                            {{ ucfirst($attendance->status ?? 'Unknown') }}
                        </x-badge>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="mt-2">No attendance records found</p>
                        <p class="text-sm">Try adjusting your filters to see more results</p>
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
@endsection

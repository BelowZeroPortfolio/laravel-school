@extends('layouts.app')

@section('title', 'Daily Attendance Summary')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Daily Attendance Summary</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Per-class attendance summary for {{ \Carbon\Carbon::parse($selectedDate)->format('l, F d, Y') }}
            </p>
        </div>
        <a href="{{ route('reports.index') }}">
            <x-button variant="outline">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Reports
            </x-button>
        </a>
    </div>

    <!-- Overall Totals (Requirement 17.5) -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <x-card>
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-blue-100 dark:bg-blue-900/50 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Enrolled</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $totals['enrolled'] }}</p>
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
                    <p class="text-2xl font-semibold text-green-600 dark:text-green-400">{{ $totals['present'] }}</p>
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
                    <p class="text-2xl font-semibold text-yellow-600 dark:text-yellow-400">{{ $totals['late'] }}</p>
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
                    <p class="text-2xl font-semibold text-red-600 dark:text-red-400">{{ $totals['absent'] }}</p>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Attendance Rate</p>
                    <p class="text-2xl font-semibold text-indigo-600 dark:text-indigo-400">{{ $totals['attendance_rate'] }}%</p>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Filters -->
    <x-card title="Select Date">
        <form method="GET" action="{{ route('reports.daily-summary') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-input 
                type="date" 
                name="date" 
                label="Date"
                :value="$selectedDate"
            />

            <x-select 
                name="school_year_id" 
                label="School Year"
                placeholder="All School Years"
            >
                @foreach($schoolYears as $schoolYear)
                    <option value="{{ $schoolYear->id }}" {{ $selectedSchoolYearId == $schoolYear->id ? 'selected' : '' }}>
                        {{ $schoolYear->name }}
                        @if($schoolYear->is_active) (Active) @endif
                    </option>
                @endforeach
            </x-select>

            <div class="flex items-end space-x-2 md:col-span-2">
                <x-button type="submit" variant="primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    View Summary
                </x-button>
                <a href="{{ route('reports.daily-summary') }}">
                    <x-button type="button" variant="outline">Today</x-button>
                </a>
            </div>
        </form>
    </x-card>

    <!-- Per-Class Summary Table (Requirement 17.5) -->
    <x-card title="Class-by-Class Summary" :padding="false">
        <x-slot name="actions">
            <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ $classSummaries->count() }} classes
            </span>
        </x-slot>

        <x-table>
            <x-slot name="head">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Class</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Teacher</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Enrolled</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Present</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Late</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Absent</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Attendance Rate</th>
                </tr>
            </x-slot>

            @forelse($classSummaries as $summary)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg flex items-center justify-center">
                                <span class="text-sm font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ $summary['grade_level'] }}
                                </span>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $summary['display_name'] ?? "Grade {$summary['grade_level']} - {$summary['section']}" }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ $summary['teacher_name'] ?? 'Unassigned' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $summary['enrolled_count'] }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/50 text-green-800 dark:text-green-200">
                            {{ $summary['present_count'] }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/50 text-yellow-800 dark:text-yellow-200">
                            {{ $summary['late_count'] }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/50 text-red-800 dark:text-red-200">
                            {{ $summary['absent_count'] }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        @php
                            $rate = $summary['attendance_rate'];
                            $rateColor = $rate >= 90 ? 'text-green-600 dark:text-green-400' : 
                                        ($rate >= 75 ? 'text-yellow-600 dark:text-yellow-400' : 'text-red-600 dark:text-red-400');
                        @endphp
                        <div class="flex items-center justify-center">
                            <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-2 mr-2">
                                <div class="h-2 rounded-full {{ $rate >= 90 ? 'bg-green-500' : ($rate >= 75 ? 'bg-yellow-500' : 'bg-red-500') }}" 
                                     style="width: {{ min($rate, 100) }}%"></div>
                            </div>
                            <span class="text-sm font-medium {{ $rateColor }}">
                                {{ $rate }}%
                            </span>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <p class="mt-2">No classes found for this school year</p>
                    </td>
                </tr>
            @endforelse

            @if($classSummaries->isNotEmpty())
                <x-slot name="foot">
                    <tr class="font-semibold">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white" colspan="2">
                            Total
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900 dark:text-white">
                            {{ $totals['enrolled'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-green-600 dark:text-green-400">
                            {{ $totals['present'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-yellow-600 dark:text-yellow-400">
                            {{ $totals['late'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-red-600 dark:text-red-400">
                            {{ $totals['absent'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-indigo-600 dark:text-indigo-400">
                            {{ $totals['attendance_rate'] }}%
                        </td>
                    </tr>
                </x-slot>
            @endif
        </x-table>
    </x-card>
</div>
@endsection

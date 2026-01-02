@extends('layouts.app')

@section('title', 'Manual Attendance Entry')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Manual Attendance Entry</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Manually record attendance for students
            </p>
        </div>
        <a href="{{ route('attendance.index') }}">
            <x-button variant="outline">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Attendance
            </x-button>
        </a>
    </div>

    @if(session('error'))
        <x-alert type="danger">{{ session('error') }}</x-alert>
    @endif

    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif

    <!-- Class Selection -->
    <x-card>
        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Select Class and Date</h2>
        <form method="GET" action="{{ route('attendance.create') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-input 
                type="date" 
                name="date" 
                label="Attendance Date"
                :value="$selectedDate"
                required
            />

            <x-select 
                name="class_id" 
                label="Class"
                placeholder="Select a class"
                required
            >
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                        Grade {{ $class->grade_level }} - {{ $class->section }}
                        @if($class->teacher)
                            ({{ $class->teacher->full_name }})
                        @endif
                    </option>
                @endforeach
            </x-select>

            <div class="flex items-end">
                <x-button type="submit" variant="primary">Load Students</x-button>
            </div>
        </form>
    </x-card>

    @if($selectedClassId && $students->isNotEmpty())
    <!-- Student List for Attendance -->
    <x-card>
        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
            Mark Attendance for {{ $selectedDate }}
        </h2>
        
        <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <p class="text-sm text-blue-700 dark:text-blue-300">
                <strong>Note:</strong> Students with existing attendance records for this date are marked with a checkmark.
                You can only create new records for students without existing attendance.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">LRN</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Check In</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Check Out</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($students as $student)
                        @php
                            $hasAttendance = in_array($student->id, $existingAttendance);
                        @endphp
                        <tr class="{{ $hasAttendance ? 'bg-green-50 dark:bg-green-900/20' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 dark:bg-indigo-900/50 rounded-full flex items-center justify-center">
                                        <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                            {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                                        </span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $student->full_name }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $student->lrn ?? $student->student_id }}
                            </td>
                            @if($hasAttendance)
                                <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-green-600 dark:text-green-400">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Attendance already recorded
                                    </div>
                                </td>
                            @else
                                <form method="POST" action="{{ route('attendance.store') }}" class="contents" id="form-{{ $student->id }}">
                                    @csrf
                                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                                    <input type="hidden" name="attendance_date" value="{{ $selectedDate }}">
                                    
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <select name="status" class="text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                            <option value="present">Present</option>
                                            <option value="late">Late</option>
                                            <option value="absent">Absent</option>
                                        </select>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="time" name="check_in_time" class="text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input type="time" name="check_out_time" class="text-sm rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <x-button type="submit" variant="primary" size="sm">Save</x-button>
                                    </td>
                                </form>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>
    @elseif($selectedClassId && $students->isEmpty())
    <x-card>
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <p class="mt-2 text-gray-500 dark:text-gray-400">No students enrolled in this class</p>
        </div>
    </x-card>
    @endif
</div>
@endsection

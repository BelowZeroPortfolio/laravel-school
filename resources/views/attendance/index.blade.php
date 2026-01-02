@extends('layouts.app')

@section('title', 'Attendance')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Student Attendance</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                View and manage student attendance records
            </p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('attendance.create') }}">
                <x-button variant="outline">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Manual Entry
                </x-button>
            </a>
            <a href="{{ route('scan.index') }}">
                <x-button variant="primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                    Scan QR
                </x-button>
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-card>
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-blue-100 dark:bg-blue-900/50 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
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
                    <p class="text-2xl font-semibold text-green-600 dark:text-green-400">{{ $stats['present'] }}</p>
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
                    <p class="text-2xl font-semibold text-yellow-600 dark:text-yellow-400">{{ $stats['late'] }}</p>
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
                    <p class="text-2xl font-semibold text-red-600 dark:text-red-400">{{ $stats['absent'] }}</p>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Filters -->
    <x-card>
        <form method="GET" action="{{ route('attendance.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
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

            <x-select 
                name="class_id" 
                label="Class"
                placeholder="All Classes"
            >
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ $selectedClassId == $class->id ? 'selected' : '' }}>
                        Grade {{ $class->grade_level }} - {{ $class->section }}
                    </option>
                @endforeach
            </x-select>

            <x-select 
                name="status" 
                label="Status"
                placeholder="All Status"
            >
                <option value="present" {{ $selectedStatus === 'present' ? 'selected' : '' }}>Present</option>
                <option value="late" {{ $selectedStatus === 'late' ? 'selected' : '' }}>Late</option>
                <option value="absent" {{ $selectedStatus === 'absent' ? 'selected' : '' }}>Absent</option>
            </x-select>

            <x-input 
                type="text" 
                name="search" 
                label="Search Student"
                placeholder="Name, LRN, or ID"
                :value="$search ?? ''"
            />

            <div class="flex items-end space-x-2">
                <x-button type="submit" variant="primary">Filter</x-button>
                <a href="{{ route('attendance.index') }}">
                    <x-button type="button" variant="outline">Clear</x-button>
                </a>
            </div>
        </form>
    </x-card>

    <!-- Attendance Table -->
    <x-card :padding="false">
        <x-table>
            <x-slot name="head">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Class</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Check In</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Check Out</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Recorded By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </x-slot>

            @forelse($attendances as $attendance)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 dark:bg-indigo-900/50 rounded-full flex items-center justify-center">
                                <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                    {{ strtoupper(substr($attendance->student->first_name ?? '', 0, 1) . substr($attendance->student->last_name ?? '', 0, 1)) }}
                                </span>
                            </div>
                            <div class="ml-4">
                                <a href="{{ route('students.show', $attendance->student) }}" class="text-sm font-medium text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400">
                                    {{ $attendance->student->full_name ?? 'Unknown' }}
                                </a>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $attendance->student->lrn ?? $attendance->student->student_id ?? '' }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        @if($attendance->student->classes->first())
                            Grade {{ $attendance->student->classes->first()->grade_level }} - {{ $attendance->student->classes->first()->section }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
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
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ $attendance->recorder->full_name ?? 'System' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex items-center space-x-2">
                            <a href="{{ route('attendance.history', $attendance->student) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300" title="View History">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </a>
                            <button type="button" onclick="openEditModal({{ $attendance->id }}, '{{ $attendance->status }}', '{{ $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '' }}', '{{ $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '' }}', '{{ $attendance->notes ?? '' }}')" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300" title="Edit">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                        <p class="mt-2">No attendance records found for this date</p>
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

<!-- Edit Attendance Modal -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeEditModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">Edit Attendance</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label for="edit_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select id="edit_status" name="status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="present">Present</option>
                                <option value="late">Late</option>
                                <option value="absent">Absent</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="edit_check_in_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Check In Time</label>
                            <input type="time" id="edit_check_in_time" name="check_in_time" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        
                        <div>
                            <label for="edit_check_out_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Check Out Time</label>
                            <input type="time" id="edit_check_out_time" name="check_out_time" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        
                        <div>
                            <label for="edit_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                            <textarea id="edit_notes" name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <x-button type="submit" variant="primary" class="w-full sm:w-auto sm:ml-3">Save Changes</x-button>
                    <x-button type="button" variant="outline" class="mt-3 w-full sm:mt-0 sm:w-auto" onclick="closeEditModal()">Cancel</x-button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditModal(id, status, checkIn, checkOut, notes) {
    document.getElementById('editForm').action = '/attendance/' + id;
    document.getElementById('edit_status').value = status;
    document.getElementById('edit_check_in_time').value = checkIn;
    document.getElementById('edit_check_out_time').value = checkOut;
    document.getElementById('edit_notes').value = notes;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>
@endsection

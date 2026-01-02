@extends('layouts.app')

@section('title', 'Teacher Monitoring')

@section('content')
<div class="space-y-6" x-data="teacherMonitoringRealtime({{ $filters['school_year_id'] ?? 'null' }})">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Teacher Monitoring</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                @if($isReadOnly)
                    <x-badge type="info" size="sm">Read-Only Access</x-badge>
                @endif
                Monitor teacher attendance and punctuality
                <span x-show="isConnected" class="inline-flex items-center ml-2">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse mr-1"></span>
                    <span class="text-xs text-green-600 dark:text-green-400">Live</span>
                </span>
            </p>
        </div>
        <a href="{{ route('teacher-monitoring.today') }}">
            <x-button variant="primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Today's View
            </x-button>
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
        <x-card>
            <div class="text-center">
                <p class="text-3xl font-bold text-gray-900 dark:text-white" data-stat="total">{{ $stats['total'] }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Total</p>
            </div>
        </x-card>
        <x-card>
            <div class="text-center">
                <p class="text-3xl font-bold text-green-600 dark:text-green-400" data-stat="confirmed">{{ $stats['confirmed'] }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Confirmed</p>
            </div>
        </x-card>
        <x-card>
            <div class="text-center">
                <p class="text-3xl font-bold text-red-600 dark:text-red-400" data-stat="late">{{ $stats['late'] }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Late</p>
            </div>
        </x-card>
        <x-card>
            <div class="text-center">
                <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400" data-stat="pending">{{ $stats['pending'] }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Pending</p>
            </div>
        </x-card>
        <x-card>
            <div class="text-center">
                <p class="text-3xl font-bold text-gray-600 dark:text-gray-400" data-stat="absent">{{ $stats['absent'] }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Absent</p>
            </div>
        </x-card>
        <x-card>
            <div class="text-center">
                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400" data-stat="no_scan">{{ $stats['no_scan'] }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">No Scan</p>
            </div>
        </x-card>
    </div>

    <!-- Filters -->
    <x-card>
        <form method="GET" action="{{ route('teacher-monitoring.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <x-select 
                name="teacher_id" 
                label="Teacher"
                placeholder="All Teachers"
            >
                @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}" {{ ($filters['teacher_id'] ?? '') == $teacher->id ? 'selected' : '' }}>
                        {{ $teacher->full_name }}
                    </option>
                @endforeach
            </x-select>

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

            <x-input 
                type="date" 
                name="date_from" 
                label="From Date"
                :value="$filters['date_from'] ?? ''"
            />

            <x-input 
                type="date" 
                name="date_to" 
                label="To Date"
                :value="$filters['date_to'] ?? ''"
            />

            <x-select 
                name="status" 
                label="Status"
                placeholder="All Status"
            >
                <option value="confirmed" {{ ($filters['status'] ?? '') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                <option value="late" {{ ($filters['status'] ?? '') === 'late' ? 'selected' : '' }}>Late</option>
                <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="absent" {{ ($filters['status'] ?? '') === 'absent' ? 'selected' : '' }}>Absent</option>
                <option value="no_scan" {{ ($filters['status'] ?? '') === 'no_scan' ? 'selected' : '' }}>No Scan</option>
            </x-select>

            <div class="flex items-end space-x-2">
                <x-button type="submit" variant="primary">Filter</x-button>
                <a href="{{ route('teacher-monitoring.index') }}">
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Teacher</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Time In</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">First Scan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Time Out</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Late Status</th>
                </tr>
            </x-slot>

            @forelse($attendances as $attendance)
                <tr data-attendance-id="{{ $attendance->id }}" data-teacher-id="{{ $attendance->teacher_id }}">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="{{ route('teacher-monitoring.show', $attendance->teacher) }}" class="flex items-center hover:text-indigo-600 dark:hover:text-indigo-400">
                            <div class="flex-shrink-0 h-10 w-10 bg-green-100 dark:bg-green-900/50 rounded-full flex items-center justify-center">
                                <span class="text-sm font-medium text-green-600 dark:text-green-400">
                                    {{ strtoupper(substr($attendance->teacher->full_name ?? '', 0, 2)) }}
                                </span>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $attendance->teacher->full_name ?? 'Unknown' }}
                                </div>
                            </div>
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                        {{ $attendance->attendance_date->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" data-field="time_in">
                        {{ $attendance->time_in ? $attendance->time_in->format('h:i A') : '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" data-field="first_student_scan">
                        {{ $attendance->first_student_scan ? $attendance->first_student_scan->format('h:i A') : '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400" data-field="time_out">
                        {{ $attendance->time_out ? $attendance->time_out->format('h:i A') : '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap" data-field="attendance_status">
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
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap" data-field="late_status">
                        @if($attendance->late_status)
                            <x-badge type="{{ $attendance->late_status === 'on_time' ? 'success' : 'danger' }}" size="sm">
                                {{ ucfirst(str_replace('_', ' ', $attendance->late_status)) }}
                            </x-badge>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="mt-2">No attendance records found</p>
                    </td>
                </tr>
            @endforelse
        </x-table>

        @if($attendances instanceof \Illuminate\Pagination\LengthAwarePaginator && $attendances->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $attendances->withQueryString()->links() }}
            </div>
        @endif
    </x-card>
</div>

@push('scripts')
<script>
/**
 * Teacher Monitoring Real-time Updates
 * Listens for TeacherLoggedIn and AttendanceFinalized events
 * Requirements: 11.4, 13.3
 */
function teacherMonitoringRealtime(schoolYearId) {
    return {
        schoolYearId: schoolYearId,
        isConnected: false,
        
        init() {
            if (!this.schoolYearId || typeof window.Echo === 'undefined') {
                return;
            }
            
            // Listen for teacher logged in events
            window.Echo.channel('teacher-monitoring.' + this.schoolYearId)
                .listen('.teacher.logged_in', (e) => {
                    this.handleTeacherLoggedIn(e);
                })
                .listen('.attendance.finalized', (e) => {
                    this.handleAttendanceFinalized(e);
                });
            
            this.isConnected = true;
        },
        
        handleTeacherLoggedIn(event) {
            // Update pending counter
            this.incrementStat('pending');
            this.incrementStat('total');
            
            // Show notification
            this.showNotification(
                'Teacher Logged In',
                `${event.teacher.full_name} logged in at ${new Date(event.attendance.time_in).toLocaleTimeString()}`,
                'info'
            );
        },
        
        handleAttendanceFinalized(event) {
            // Update stats based on status change
            this.decrementStat('pending');
            
            if (event.attendance.attendance_status === 'late') {
                this.incrementStat('late');
            } else if (event.attendance.attendance_status === 'confirmed') {
                this.incrementStat('confirmed');
            }
            
            // Update the row in the table if it exists
            const row = document.querySelector(`tr[data-teacher-id="${event.attendance.teacher_id}"]`);
            if (row) {
                this.updateRowStatus(row, event.attendance);
            }
            
            // Show notification
            const statusText = event.attendance.attendance_status === 'late' ? 'marked late' : 'confirmed';
            this.showNotification(
                'Attendance Finalized',
                `${event.teacher?.full_name || 'Teacher'} attendance ${statusText}`,
                event.attendance.attendance_status === 'late' ? 'warning' : 'success'
            );
        },
        
        updateRowStatus(row, attendance) {
            // Update first scan time
            const firstScanCell = row.querySelector('[data-field="first_student_scan"]');
            if (firstScanCell && attendance.first_student_scan) {
                firstScanCell.textContent = new Date(attendance.first_student_scan).toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });
            }
            
            // Highlight the row briefly
            row.classList.add('bg-yellow-50', 'dark:bg-yellow-900/20');
            setTimeout(() => {
                row.classList.remove('bg-yellow-50', 'dark:bg-yellow-900/20');
            }, 3000);
        },
        
        incrementStat(statName) {
            const element = document.querySelector(`[data-stat="${statName}"]`);
            if (element) {
                const currentCount = parseInt(element.textContent.replace(/,/g, '')) || 0;
                element.textContent = (currentCount + 1).toLocaleString();
            }
        },
        
        decrementStat(statName) {
            const element = document.querySelector(`[data-stat="${statName}"]`);
            if (element) {
                const currentCount = parseInt(element.textContent.replace(/,/g, '')) || 0;
                element.textContent = Math.max(0, currentCount - 1).toLocaleString();
            }
        },
        
        showNotification(title, message, type = 'info') {
            const toast = document.createElement('div');
            const bgColor = {
                'success': 'bg-green-500',
                'warning': 'bg-yellow-500',
                'error': 'bg-red-500',
                'info': 'bg-blue-500'
            }[type] || 'bg-blue-500';
            
            toast.className = `fixed bottom-4 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300 translate-y-0 opacity-100`;
            toast.innerHTML = `
                <div class="font-semibold">${title}</div>
                <div class="text-sm opacity-90">${message}</div>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('translate-y-2', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }
    };
}
</script>
@endpush
@endsection

@extends('layouts.app')

@section('title', "Today's Teacher Attendance")

@section('content')
<div class="space-y-6" x-data="todayMonitoringRealtime({{ $selectedSchoolYearId ?? 'null' }})">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('teacher-monitoring.index') }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Monitoring
            </a>
            <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">Today's Teacher Attendance</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ now()->format('l, F d, Y') }}
                <span x-show="isConnected" class="inline-flex items-center ml-2">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse mr-1"></span>
                    <span class="text-xs text-green-600 dark:text-green-400">Live</span>
                </span>
            </p>
        </div>
        @if($isReadOnly)
            <x-badge type="info">Read-Only Access</x-badge>
        @endif
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
        <x-card>
            <div class="text-center">
                <p class="text-3xl font-bold text-gray-900 dark:text-white" data-stat="total_teachers">{{ $stats['total_teachers'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Total Teachers</p>
            </div>
        </x-card>
        <x-card>
            <div class="text-center">
                <p class="text-3xl font-bold text-green-600 dark:text-green-400" data-stat="confirmed">{{ $stats['confirmed'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Confirmed</p>
            </div>
        </x-card>
        <x-card>
            <div class="text-center">
                <p class="text-3xl font-bold text-red-600 dark:text-red-400" data-stat="late">{{ $stats['late'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Late</p>
            </div>
        </x-card>
        <x-card>
            <div class="text-center">
                <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400" data-stat="pending">{{ $stats['pending'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Pending</p>
            </div>
        </x-card>
        <x-card>
            <div class="text-center">
                <p class="text-3xl font-bold text-gray-600 dark:text-gray-400" data-stat="absent">{{ $stats['absent'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Absent</p>
            </div>
        </x-card>
        <x-card>
            <div class="text-center">
                <p class="text-3xl font-bold text-blue-600 dark:text-blue-400" data-stat="no_scan">{{ $stats['no_scan'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">No Scan</p>
            </div>
        </x-card>
        <x-card>
            <div class="text-center">
                <p class="text-3xl font-bold text-purple-600 dark:text-purple-400" data-stat="not_logged_in">{{ $stats['not_logged_in'] }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Not Logged In</p>
            </div>
        </x-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Logged In Teachers -->
        <x-card title="Logged In Teachers" :padding="false">
            <div id="logged-in-teachers-list">
                @if($attendances->count() > 0)
                    <div class="divide-y divide-gray-200 dark:divide-gray-700 max-h-96 overflow-y-auto">
                        @foreach($attendances as $attendance)
                            <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50" data-teacher-id="{{ $attendance->teacher_id }}">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full flex items-center justify-center
                                            {{ $attendance->attendance_status === 'confirmed' ? 'bg-green-100 dark:bg-green-900/50' : 
                                               ($attendance->attendance_status === 'late' ? 'bg-red-100 dark:bg-red-900/50' : 'bg-yellow-100 dark:bg-yellow-900/50') }}">
                                            <span class="text-sm font-medium 
                                                {{ $attendance->attendance_status === 'confirmed' ? 'text-green-600 dark:text-green-400' : 
                                                   ($attendance->attendance_status === 'late' ? 'text-red-600 dark:text-red-400' : 'text-yellow-600 dark:text-yellow-400') }}">
                                                {{ strtoupper(substr($attendance->teacher->full_name ?? '', 0, 2)) }}
                                            </span>
                                        </div>
                                        <div class="ml-3">
                                            <a href="{{ route('teacher-monitoring.show', $attendance->teacher) }}" class="text-sm font-medium text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400">
                                                {{ $attendance->teacher->full_name ?? 'Unknown' }}
                                            </a>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                In: {{ $attendance->time_in ? $attendance->time_in->format('h:i A') : '-' }}
                                                @if($attendance->first_student_scan)
                                                    | Scan: {{ $attendance->first_student_scan->format('h:i A') }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <x-badge type="{{ $attendance->attendance_status === 'confirmed' ? 'success' : ($attendance->attendance_status === 'late' ? 'danger' : 'warning') }}" size="sm">
                                        {{ ucfirst($attendance->attendance_status) }}
                                    </x-badge>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                        No teachers have logged in today.
                    </div>
                @endif
            </div>
        </x-card>

        <!-- Not Logged In Teachers -->
        <x-card title="Not Logged In" :padding="false">
            <div id="not-logged-in-teachers-list">
                @if($teachersWithoutAttendance->count() > 0)
                    <div class="divide-y divide-gray-200 dark:divide-gray-700 max-h-96 overflow-y-auto">
                        @foreach($teachersWithoutAttendance as $teacher)
                            <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50" data-teacher-id="{{ $teacher->id }}">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                                {{ strtoupper(substr($teacher->full_name, 0, 2)) }}
                                            </span>
                                        </div>
                                        <div class="ml-3">
                                            <a href="{{ route('teacher-monitoring.show', $teacher) }}" class="text-sm font-medium text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400">
                                                {{ $teacher->full_name }}
                                            </a>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $teacher->email ?? 'No email' }}
                                            </div>
                                        </div>
                                    </div>
                                    <x-badge type="default" size="sm">Not Logged In</x-badge>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                        All teachers have logged in today.
                    </div>
                @endif
            </div>
        </x-card>
    </div>
</div>

@push('scripts')
<script>
/**
 * Today's Teacher Monitoring Real-time Updates
 * Listens for TeacherLoggedIn and AttendanceFinalized events
 * Requirements: 11.4, 13.3
 */
function todayMonitoringRealtime(schoolYearId) {
    return {
        schoolYearId: schoolYearId,
        isConnected: false,
        
        init() {
            if (!this.schoolYearId || typeof window.Echo === 'undefined') {
                return;
            }
            
            // Listen for teacher events
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
            // Update stats
            this.incrementStat('pending');
            this.decrementStat('not_logged_in');
            
            // Move teacher from "Not Logged In" to "Logged In" list
            const notLoggedInItem = document.querySelector(`#not-logged-in-teachers-list [data-teacher-id="${event.teacher.id}"]`);
            if (notLoggedInItem) {
                notLoggedInItem.remove();
            }
            
            // Add to logged in list
            this.addToLoggedInList(event.teacher, event.attendance);
            
            // Show notification
            this.showNotification(
                'Teacher Logged In',
                `${event.teacher.full_name} logged in at ${new Date(event.attendance.time_in).toLocaleTimeString()}`,
                'info'
            );
        },
        
        handleAttendanceFinalized(event) {
            // Update stats
            this.decrementStat('pending');
            
            if (event.attendance.attendance_status === 'late') {
                this.incrementStat('late');
            } else if (event.attendance.attendance_status === 'confirmed') {
                this.incrementStat('confirmed');
            }
            
            // Update the teacher's card in the logged in list
            const teacherCard = document.querySelector(`#logged-in-teachers-list [data-teacher-id="${event.attendance.teacher_id}"]`);
            if (teacherCard) {
                this.updateTeacherCard(teacherCard, event.attendance);
            }
            
            // Show notification
            const statusText = event.attendance.attendance_status === 'late' ? 'marked late' : 'confirmed';
            this.showNotification(
                'Attendance Finalized',
                `${event.teacher?.full_name || 'Teacher'} attendance ${statusText}`,
                event.attendance.attendance_status === 'late' ? 'warning' : 'success'
            );
        },
        
        addToLoggedInList(teacher, attendance) {
            const list = document.querySelector('#logged-in-teachers-list .divide-y');
            if (!list) return;
            
            const initials = teacher.full_name.substring(0, 2).toUpperCase();
            const timeIn = new Date(attendance.time_in).toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
            
            const newItem = document.createElement('div');
            newItem.className = 'p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 bg-green-50 dark:bg-green-900/20';
            newItem.setAttribute('data-teacher-id', teacher.id);
            newItem.innerHTML = `
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full flex items-center justify-center bg-yellow-100 dark:bg-yellow-900/50">
                            <span class="text-sm font-medium text-yellow-600 dark:text-yellow-400">${initials}</span>
                        </div>
                        <div class="ml-3">
                            <span class="text-sm font-medium text-gray-900 dark:text-white">${teacher.full_name}</span>
                            <div class="text-xs text-gray-500 dark:text-gray-400">In: ${timeIn}</div>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-400">Pending</span>
                </div>
            `;
            
            list.insertBefore(newItem, list.firstChild);
            
            // Remove highlight after 3 seconds
            setTimeout(() => {
                newItem.classList.remove('bg-green-50', 'dark:bg-green-900/20');
            }, 3000);
        },
        
        updateTeacherCard(card, attendance) {
            const avatar = card.querySelector('.flex-shrink-0');
            const badge = card.querySelector('.inline-flex');
            
            const isLate = attendance.attendance_status === 'late';
            const isConfirmed = attendance.attendance_status === 'confirmed';
            
            // Update avatar color
            if (avatar) {
                avatar.className = avatar.className.replace(/bg-\w+-100|bg-\w+-900\/50/g, '');
                if (isConfirmed) {
                    avatar.classList.add('bg-green-100', 'dark:bg-green-900/50');
                } else if (isLate) {
                    avatar.classList.add('bg-red-100', 'dark:bg-red-900/50');
                }
                
                const span = avatar.querySelector('span');
                if (span) {
                    span.className = span.className.replace(/text-\w+-600|text-\w+-400/g, '');
                    if (isConfirmed) {
                        span.classList.add('text-green-600', 'dark:text-green-400');
                    } else if (isLate) {
                        span.classList.add('text-red-600', 'dark:text-red-400');
                    }
                }
            }
            
            // Update badge
            if (badge) {
                badge.className = badge.className.replace(/bg-\w+-100|text-\w+-800|bg-\w+-900\/50|text-\w+-400/g, '');
                if (isConfirmed) {
                    badge.classList.add('bg-green-100', 'text-green-800', 'dark:bg-green-900/50', 'dark:text-green-400');
                } else if (isLate) {
                    badge.classList.add('bg-red-100', 'text-red-800', 'dark:bg-red-900/50', 'dark:text-red-400');
                }
                badge.textContent = attendance.attendance_status.charAt(0).toUpperCase() + attendance.attendance_status.slice(1);
            }
            
            // Update time info
            const timeInfo = card.querySelector('.text-xs');
            if (timeInfo && attendance.first_student_scan) {
                const scanTime = new Date(attendance.first_student_scan).toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });
                const currentText = timeInfo.textContent;
                if (!currentText.includes('Scan:')) {
                    timeInfo.textContent = currentText + ' | Scan: ' + scanTime;
                }
            }
            
            // Highlight briefly
            card.classList.add('bg-yellow-50', 'dark:bg-yellow-900/20');
            setTimeout(() => {
                card.classList.remove('bg-yellow-50', 'dark:bg-yellow-900/20');
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

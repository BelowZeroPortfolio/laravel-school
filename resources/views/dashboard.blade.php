@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6" x-data="dashboardRealtime({{ $activeSchoolYear?->id ?? 'null' }})">
    <!-- Welcome Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Welcome, {{ $user->full_name }}
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                @if($activeSchoolYear)
                    School Year: {{ $activeSchoolYear->name }}
                @else
                    No active school year
                @endif
            </p>
        </div>
        <x-badge type="{{ $user->role === 'admin' ? 'danger' : ($user->role === 'principal' ? 'warning' : 'primary') }}">
            {{ ucfirst($user->role) }}
        </x-badge>
    </div>

    @if($user->isAdmin())
        {{-- Admin Dashboard --}}
        @include('dashboard.partials.admin', [
            'totalStudents' => $totalStudents,
            'totalTeachers' => $totalTeachers,
            'totalClasses' => $totalClasses,
            'todayAttendance' => $todayAttendance,
            'todayTeacherAttendance' => $todayTeacherAttendance,
            'pendingTeachers' => $pendingTeachers,
            'lateTeachers' => $lateTeachers,
        ])
    @elseif($user->isPrincipal())
        {{-- Principal Dashboard --}}
        @include('dashboard.partials.principal', [
            'totalTeachers' => $totalTeachers,
            'todayTeacherAttendance' => $todayTeacherAttendance,
            'pendingTeachers' => $pendingTeachers,
            'lateTeachers' => $lateTeachers,
            'confirmedTeachers' => $confirmedTeachers,
            'absentTeachers' => $absentTeachers,
        ])
    @else
        {{-- Teacher Dashboard --}}
        @include('dashboard.partials.teacher', [
            'classes' => $classes,
            'totalStudents' => $totalStudents,
            'todayAttendance' => $todayAttendance,
            'teacherAttendance' => $teacherAttendance,
        ])
    @endif
</div>

@push('scripts')
<script>
/**
 * Dashboard Real-time Updates
 * Listens for StudentScanned events and updates dashboard stats
 * Requirements: 13.1
 */
function dashboardRealtime(schoolYearId) {
    return {
        schoolYearId: schoolYearId,
        
        init() {
            if (!this.schoolYearId || typeof window.Echo === 'undefined') {
                return;
            }
            
            // Listen for student scanned events
            window.Echo.channel('attendance.' + this.schoolYearId)
                .listen('.student.scanned', (e) => {
                    this.handleStudentScanned(e);
                });
            
            // Listen for teacher logged in events
            window.Echo.channel('teacher-monitoring.' + this.schoolYearId)
                .listen('.teacher.logged_in', (e) => {
                    this.handleTeacherLoggedIn(e);
                });
            
            // Listen for attendance finalized events
            window.Echo.channel('teacher-monitoring.' + this.schoolYearId)
                .listen('.attendance.finalized', (e) => {
                    this.handleAttendanceFinalized(e);
                });
        },
        
        handleStudentScanned(event) {
            // Update today's scans counter
            const scansElement = document.querySelector('[data-stat="today-scans"]');
            if (scansElement) {
                const currentCount = parseInt(scansElement.textContent.replace(/,/g, '')) || 0;
                scansElement.textContent = (currentCount + 1).toLocaleString();
            }
            
            // Show notification
            this.showNotification(
                'Student Scanned',
                `${event.student.full_name} checked in at ${new Date(event.attendance.check_in_time).toLocaleTimeString()}`,
                'success'
            );
        },
        
        handleTeacherLoggedIn(event) {
            // Update teacher attendance counters
            const loggedInElement = document.querySelector('[data-stat="teachers-logged-in"]');
            if (loggedInElement) {
                const currentCount = parseInt(loggedInElement.textContent.replace(/,/g, '')) || 0;
                loggedInElement.textContent = (currentCount + 1).toLocaleString();
            }
            
            const pendingElement = document.querySelector('[data-stat="teachers-pending"]');
            if (pendingElement) {
                const currentCount = parseInt(pendingElement.textContent.replace(/,/g, '')) || 0;
                pendingElement.textContent = (currentCount + 1).toLocaleString();
            }
            
            // Show notification
            this.showNotification(
                'Teacher Logged In',
                `${event.teacher.full_name} logged in`,
                'info'
            );
        },
        
        handleAttendanceFinalized(event) {
            // Update pending counter (decrease)
            const pendingElement = document.querySelector('[data-stat="teachers-pending"]');
            if (pendingElement) {
                const currentCount = parseInt(pendingElement.textContent.replace(/,/g, '')) || 0;
                pendingElement.textContent = Math.max(0, currentCount - 1).toLocaleString();
            }
            
            // Update appropriate status counter (increase)
            if (event.attendance.attendance_status === 'late') {
                const lateElement = document.querySelector('[data-stat="teachers-late"]');
                if (lateElement) {
                    const currentCount = parseInt(lateElement.textContent.replace(/,/g, '')) || 0;
                    lateElement.textContent = (currentCount + 1).toLocaleString();
                }
            } else if (event.attendance.attendance_status === 'confirmed') {
                const confirmedElement = document.querySelector('[data-stat="teachers-confirmed"]');
                if (confirmedElement) {
                    const currentCount = parseInt(confirmedElement.textContent.replace(/,/g, '')) || 0;
                    confirmedElement.textContent = (currentCount + 1).toLocaleString();
                }
            }
            
            // Show notification
            const statusText = event.attendance.attendance_status === 'late' ? 'marked late' : 'confirmed';
            this.showNotification(
                'Attendance Updated',
                `${event.teacher?.full_name || 'Teacher'} attendance ${statusText}`,
                event.attendance.attendance_status === 'late' ? 'warning' : 'success'
            );
        },
        
        showNotification(title, message, type = 'info') {
            // Create toast notification
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
            
            // Auto-remove after 5 seconds
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

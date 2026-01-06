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
                <span class="ml-2">•</span>
                <span class="ml-2">{{ now()->format('l, F j, Y') }}</span>
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="text-right hidden sm:block">
                <p class="text-sm text-gray-500 dark:text-gray-400">Last updated</p>
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300" x-text="lastUpdated">Just now</p>
            </div>
            <x-badge type="{{ $user->role === 'admin' ? 'danger' : ($user->role === 'principal' ? 'warning' : 'primary') }}">
                {{ ucfirst($user->role) }}
            </x-badge>
        </div>
    </div>

    @if($user->isAdmin())
        @include('dashboard.partials.admin', [
            'totalStudents' => $totalStudents,
            'totalTeachers' => $totalTeachers,
            'totalClasses' => $totalClasses,
            'todayAttendance' => $todayAttendance,
            'todayPresent' => $todayPresent,
            'todayLate' => $todayLate,
            'todayAbsent' => $todayAbsent ?? 0,
            'attendanceRate' => $attendanceRate,
            'todayTeacherAttendance' => $todayTeacherAttendance,
            'pendingTeachers' => $pendingTeachers,
            'lateTeachers' => $lateTeachers,
            'confirmedTeachers' => $confirmedTeachers,
            'weeklyTrend' => $weeklyTrend,
            'topClasses' => $topClasses,
            'bottomClasses' => $bottomClasses ?? [],
            'recentScans' => $recentScans,
            'attendanceByGrade' => $attendanceByGrade ?? [],
            'hourlyDistribution' => $hourlyDistribution ?? [],
            'weekComparison' => $weekComparison ?? ['thisWeek' => 0, 'lastWeek' => 0, 'change' => 0, 'trend' => 'up'],
            'monthlyTrend' => $monthlyTrend ?? [],
        ])
    @elseif($user->isPrincipal())
        @include('dashboard.partials.principal', [
            'totalTeachers' => $totalTeachers,
            'todayTeacherAttendance' => $todayTeacherAttendance,
            'pendingTeachers' => $pendingTeachers,
            'lateTeachers' => $lateTeachers,
            'confirmedTeachers' => $confirmedTeachers,
            'absentTeachers' => $absentTeachers,
            'teachersByStatus' => $teachersByStatus,
            'weeklyTeacherTrend' => $weeklyTeacherTrend,
            'attendanceRate' => $attendanceRate,
        ])
    @else
        @include('dashboard.partials.teacher', [
            'classes' => $classes,
            'totalStudents' => $totalStudents,
            'todayAttendance' => $todayAttendance,
            'teacherAttendance' => $teacherAttendance,
            'classAttendance' => $classAttendance,
            'weeklyTrend' => $weeklyTrend,
            'attendanceRate' => $attendanceRate,
        ])
    @endif
</div>

@push('scripts')
<script>
function dashboardRealtime(schoolYearId) {
    return {
        schoolYearId: schoolYearId,
        lastUpdated: 'Just now',
        pollInterval: null,
        
        init() {
            // Start polling for live stats every 30 seconds
            this.pollInterval = setInterval(() => this.fetchLiveStats(), 30000);
            
            // Also listen for WebSocket events for instant updates
            if (this.schoolYearId && typeof window.Echo !== 'undefined') {
                this.setupWebSocketListeners();
            }
        },
        
        destroy() {
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
            }
        },
        
        async fetchLiveStats() {
            try {
                const response = await fetch('{{ route("dashboard.live-stats") }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.updateStats(data);
                    this.lastUpdated = new Date().toLocaleTimeString();
                }
            } catch (error) {
                console.error('Failed to fetch live stats:', error);
            }
        },
        
        updateStats(data) {
            // Update all stat elements (supports both data-stat and data-live attributes)
            Object.keys(data).forEach(key => {
                const kebabKey = this.camelToKebab(key);
                const element = document.querySelector(`[data-stat="${kebabKey}"]`) || 
                               document.querySelector(`[data-live="${kebabKey}"]`);
                if (element && typeof data[key] !== 'object') {
                    const newValue = typeof data[key] === 'number' 
                        ? data[key].toLocaleString() 
                        : data[key];
                    
                    if (element.textContent !== newValue.toString()) {
                        element.textContent = newValue;
                        element.classList.add('animate-pulse');
                        setTimeout(() => element.classList.remove('animate-pulse'), 1000);
                    }
                }
            });
            
            // Update recent scans if present
            if (data.recentScans && document.getElementById('recent-scans')) {
                this.updateRecentScans(data.recentScans);
            }
            
            // Update progress bar if present
            if (data.attendanceRate !== undefined) {
                const progressBar = document.querySelector('[data-stat="progress-bar"]') ||
                                   document.querySelector('[data-live="progress-bar"]');
                if (progressBar) {
                    progressBar.style.width = data.attendanceRate + '%';
                }
            }

            // Update hourly distribution chart
            if (data.hourlyDistribution) {
                const maxHourly = Math.max(...data.hourlyDistribution.map(h => h.count)) || 1;
                data.hourlyDistribution.forEach(hour => {
                    const bar = document.querySelector(`[data-live-hour="${hour.hour}"]`);
                    const count = document.querySelector(`[data-live-hour-count="${hour.hour}"]`);
                    if (bar) bar.style.height = Math.max(2, (hour.count / maxHourly) * 100) + '%';
                    if (count) count.textContent = hour.count;
                });
            }
        },
        
        updateRecentScans(scans) {
            const container = document.getElementById('recent-scans');
            if (!container || scans.length === 0) return;
            
            container.innerHTML = scans.map(scan => `
                <div class="px-4 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">${scan.student}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">${scan.time}</p>
                    </div>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${scan.status === 'present' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400'}">
                        ${scan.status.charAt(0).toUpperCase() + scan.status.slice(1)}
                    </span>
                </div>
            `).join('');
        },
        
        camelToKebab(str) {
            return str.replace(/([a-z])([A-Z])/g, '$1-$2').toLowerCase();
        },
        
        setupWebSocketListeners() {
            // Listen for student scanned events
            window.Echo.channel('attendance.' + this.schoolYearId)
                .listen('.student.scanned', (e) => {
                    this.handleStudentScanned(e);
                });
            
            // Listen for teacher events
            window.Echo.channel('teacher-monitoring.' + this.schoolYearId)
                .listen('.teacher.logged_in', (e) => this.handleTeacherLoggedIn(e))
                .listen('.attendance.finalized', (e) => this.handleAttendanceFinalized(e));
        },
        
        handleStudentScanned(event) {
            // Increment counters
            this.incrementStat('today-scans');
            if (event.status === 'present') {
                this.incrementStat('today-present');
            } else if (event.status === 'late') {
                this.incrementStat('today-late');
            }
            
            this.showNotification(
                'Student Scanned',
                `${event.student.full_name} - ${event.status}`,
                event.status === 'present' ? 'success' : 'warning'
            );
            
            // Refresh live stats to get updated recent scans
            this.fetchLiveStats();
        },
        
        handleTeacherLoggedIn(event) {
            this.incrementStat('teachers-logged-in');
            this.incrementStat('teachers-pending');
            
            this.showNotification(
                'Teacher Logged In',
                event.teacher.full_name,
                'info'
            );
        },
        
        handleAttendanceFinalized(event) {
            this.decrementStat('teachers-pending');
            
            if (event.attendance.attendance_status === 'late') {
                this.incrementStat('teachers-late');
            } else if (event.attendance.attendance_status === 'confirmed') {
                this.incrementStat('teachers-confirmed');
            }
        },
        
        incrementStat(statName) {
            const element = document.querySelector(`[data-stat="${statName}"]`) ||
                           document.querySelector(`[data-live="${statName}"]`);
            if (element) {
                const current = parseInt(element.textContent.replace(/,/g, '')) || 0;
                element.textContent = (current + 1).toLocaleString();
                element.classList.add('animate-pulse');
                setTimeout(() => element.classList.remove('animate-pulse'), 1000);
            }
        },
        
        decrementStat(statName) {
            const element = document.querySelector(`[data-stat="${statName}"]`) ||
                           document.querySelector(`[data-live="${statName}"]`);
            if (element) {
                const current = parseInt(element.textContent.replace(/,/g, '')) || 0;
                element.textContent = Math.max(0, current - 1).toLocaleString();
            }
        },
        
        showNotification(title, message, type = 'info') {
            const colors = {
                'success': 'bg-green-500',
                'warning': 'bg-yellow-500',
                'error': 'bg-red-500',
                'info': 'bg-blue-500'
            };
            
            const toast = document.createElement('div');
            toast.className = `fixed bottom-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform transition-all duration-300`;
            toast.innerHTML = `
                <div class="font-semibold">${title}</div>
                <div class="text-sm opacity-90">${message}</div>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('translate-y-2', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }
    };
}
</script>
@endpush
@endsection

{{-- Admin Dashboard Partial --}}

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Total Students -->
    <x-card>
        <div class="flex items-center">
            <div class="flex-shrink-0 p-3 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg">
                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Students</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($totalStudents) }}</p>
            </div>
        </div>
    </x-card>

    <!-- Total Teachers -->
    <x-card>
        <div class="flex items-center">
            <div class="flex-shrink-0 p-3 bg-green-100 dark:bg-green-900/50 rounded-lg">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Teachers</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($totalTeachers) }}</p>
            </div>
        </div>
    </x-card>

    <!-- Total Classes -->
    <x-card>
        <div class="flex items-center">
            <div class="flex-shrink-0 p-3 bg-yellow-100 dark:bg-yellow-900/50 rounded-lg">
                <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Classes</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($totalClasses) }}</p>
            </div>
        </div>
    </x-card>

    <!-- Today's Attendance Rate -->
    <x-card>
        <div class="flex items-center">
            <div class="flex-shrink-0 p-3 bg-blue-100 dark:bg-blue-900/50 rounded-lg">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Attendance Rate</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white" data-live="attendance-rate">{{ $attendanceRate }}%</p>
            </div>
        </div>
    </x-card>
</div>

<!-- Today's Attendance Overview + Week Comparison -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <x-card title="Today's Student Attendance" class="lg:col-span-2">
        <div class="grid grid-cols-4 gap-3 mb-4">
            <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400" data-live="today-scans">{{ $todayAttendance }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Total Scans</p>
            </div>
            <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <p class="text-2xl font-bold text-green-600 dark:text-green-400" data-live="today-present">{{ $todayPresent }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">On Time</p>
            </div>
            <div class="text-center p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
                <p class="text-2xl font-bold text-amber-600 dark:text-amber-400" data-live="today-late">{{ $todayLate }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Late</p>
            </div>
            <div class="text-center p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                <p class="text-2xl font-bold text-red-600 dark:text-red-400" data-live="today-absent">{{ $todayAbsent }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Absent</p>
            </div>
        </div>
        <div class="mt-4">
            <div class="flex justify-between text-sm mb-1">
                <span class="text-gray-500 dark:text-gray-400">Progress</span>
                <span class="text-gray-700 dark:text-gray-300" data-live="progress-text">{{ $todayAttendance }} / {{ $totalStudents }} students</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                <div class="h-3 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 transition-all duration-500" 
                     style="width: {{ $attendanceRate }}%" data-live="progress-bar"></div>
            </div>
        </div>
    </x-card>

    <!-- Week Comparison -->
    <x-card title="Week Comparison">
        <div class="flex flex-col h-full justify-center">
            <div class="text-center mb-4">
                <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $weekComparison['trend'] === 'up' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' }}">
                    @if($weekComparison['trend'] === 'up')
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    @else
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    @endif
                    {{ abs($weekComparison['change']) }}%
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 text-center">
                <div>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($weekComparison['thisWeek']) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">This Week</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-500 dark:text-gray-400">{{ number_format($weekComparison['lastWeek']) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Last Week</p>
                </div>
            </div>
        </div>
    </x-card>
</div>


<!-- Hourly Distribution + Recent Scans -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <!-- Hourly Scan Distribution -->
    <x-card title="Today's Scan Distribution" class="lg:col-span-2">
        <div class="h-40" id="hourly-chart">
            <div class="flex items-end justify-between h-full gap-1">
                @php $maxHourly = max(array_column($hourlyDistribution, 'count')) ?: 1; @endphp
                @foreach($hourlyDistribution as $hour)
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-t relative" style="height: 100px;">
                            <div class="absolute bottom-0 w-full bg-gradient-to-t from-indigo-600 to-indigo-400 dark:from-indigo-500 dark:to-indigo-300 rounded-t transition-all duration-500"
                                 style="height: {{ max(2, ($hour['count'] / $maxHourly) * 100) }}%"
                                 data-live-hour="{{ $hour['hour'] }}">
                            </div>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $hour['label'] }}</span>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300" data-live-hour-count="{{ $hour['hour'] }}">{{ $hour['count'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </x-card>

    <!-- Recent Scans (Live) -->
    <x-card title="Recent Scans" :padding="false">
        <div class="divide-y divide-gray-200 dark:divide-gray-700 max-h-56 overflow-y-auto" id="recent-scans">
            @forelse($recentScans as $scan)
                <div class="px-4 py-2.5 flex items-center justify-between">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $scan['student'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $scan['time'] }}</p>
                    </div>
                    <x-badge type="{{ $scan['status'] === 'present' ? 'success' : 'danger' }}" size="sm">
                        {{ ucfirst($scan['status']) }}
                    </x-badge>
                </div>
            @empty
                <div class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                    No scans yet today
                </div>
            @endforelse
        </div>
    </x-card>
</div>

<!-- Attendance by Grade Level -->
<x-card title="Attendance by Grade Level">
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
        @forelse($attendanceByGrade as $grade)
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 text-center">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Grade {{ $grade['grade'] }}</p>
                <p class="text-xl font-bold {{ $grade['rate'] >= 80 ? 'text-green-600 dark:text-green-400' : ($grade['rate'] >= 50 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400') }}">
                    {{ $grade['rate'] }}%
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $grade['attended'] }}/{{ $grade['enrolled'] }}</p>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5 mt-2">
                    <div class="h-1.5 rounded-full {{ $grade['rate'] >= 80 ? 'bg-green-500' : ($grade['rate'] >= 50 ? 'bg-amber-500' : 'bg-red-500') }}"
                         style="width: {{ $grade['rate'] }}%"></div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center text-gray-500 dark:text-gray-400 py-4">No grade data available</div>
        @endforelse
    </div>
</x-card>

<!-- Weekly Trend & Teacher Attendance -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <!-- Weekly Attendance Trend -->
    <x-card title="Weekly Attendance Trend">
        <div class="h-44">
            <div class="flex items-end justify-between h-full gap-2">
                @php $maxWeekly = max(array_column($weeklyTrend, 'count')) ?: 1; @endphp
                @foreach($weeklyTrend as $day)
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-t relative" style="height: 120px;">
                            <div class="absolute bottom-0 w-full bg-indigo-500 dark:bg-indigo-600 rounded-t transition-all duration-500"
                                 style="height: {{ max(2, ($day['count'] / $maxWeekly) * 100) }}%">
                            </div>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ $day['day'] }}</span>
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $day['count'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </x-card>

    <!-- Teacher Attendance Summary -->
    <x-card title="Teacher Attendance Today">
        <div class="grid grid-cols-2 gap-3">
            <div class="text-center p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <p class="text-2xl font-bold text-gray-900 dark:text-white" data-live="teachers-logged-in">{{ $todayTeacherAttendance }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Logged In</p>
            </div>
            <div class="text-center p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
                <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400" data-live="teachers-pending">{{ $pendingTeachers }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Pending</p>
            </div>
            <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                <p class="text-2xl font-bold text-green-600 dark:text-green-400" data-live="teachers-confirmed">{{ $confirmedTeachers }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">On Time</p>
            </div>
            <div class="text-center p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                <p class="text-2xl font-bold text-red-600 dark:text-red-400" data-live="teachers-late">{{ $lateTeachers }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Late</p>
            </div>
        </div>
    </x-card>
</div>


<!-- Top & Bottom Classes -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    <!-- Top Classes -->
    <x-card title="Top Performing Classes">
        <div class="space-y-3">
            @forelse($topClasses as $index => $class)
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold
                        {{ $index === 0 ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/50 dark:text-yellow-400' : 
                           ($index === 1 ? 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300' : 
                           'bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-400') }}">
                        {{ $index + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $class['name'] }}</p>
                            <span class="text-sm font-semibold text-green-600 dark:text-green-400">{{ $class['rate'] }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full bg-green-500" style="width: {{ $class['rate'] }}%"></div>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500 dark:text-gray-400 py-4">No class data available</p>
            @endforelse
        </div>
    </x-card>

    <!-- Bottom Classes (Needs Attention) -->
    <x-card title="Classes Needing Attention">
        <div class="space-y-3">
            @forelse($bottomClasses as $class)
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $class['name'] }}</p>
                            <span class="text-sm font-semibold {{ $class['rate'] < 30 ? 'text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-400' }}">{{ $class['rate'] }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full {{ $class['rate'] < 30 ? 'bg-red-500' : 'bg-amber-500' }}" style="width: {{ max(5, $class['rate']) }}%"></div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $class['attended'] }}/{{ $class['enrolled'] }} • {{ $class['teacher'] }}</p>
                    </div>
                </div>
            @empty
                <p class="text-center text-gray-500 dark:text-gray-400 py-4">All classes performing well!</p>
            @endforelse
        </div>
    </x-card>
</div>

<!-- Quick Actions -->
<x-card title="Quick Actions">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('scan.index') }}" class="flex flex-col items-center p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg hover:bg-indigo-100 dark:hover:bg-indigo-900/40 transition-colors">
            <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
            </svg>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Scan QR</span>
        </a>
        <a href="{{ route('students.index') }}" class="flex flex-col items-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-8 h-8 text-green-600 dark:text-green-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
            </svg>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Students</span>
        </a>
        <a href="{{ route('reports.index') }}" class="flex flex-col items-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Reports</span>
        </a>
        <a href="{{ route('teacher-monitoring.index') }}" class="flex flex-col items-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-8 h-8 text-blue-600 dark:text-blue-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Teachers</span>
        </a>
    </div>
</x-card>



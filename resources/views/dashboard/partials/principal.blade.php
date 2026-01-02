{{-- Principal Dashboard Partial --}}

<!-- Teacher Attendance Stats -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <!-- Total Teachers -->
    <x-card>
        <div class="flex items-center">
            <div class="flex-shrink-0 p-3 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg">
                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Teachers</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($totalTeachers) }}</p>
            </div>
        </div>
    </x-card>

    <!-- Logged In Today -->
    <x-card>
        <div class="flex items-center">
            <div class="flex-shrink-0 p-3 bg-green-100 dark:bg-green-900/50 rounded-lg">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Logged In Today</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white" data-stat="teachers-logged-in">{{ number_format($todayTeacherAttendance) }}</p>
            </div>
        </div>
    </x-card>

    <!-- Not Logged In -->
    <x-card>
        <div class="flex items-center">
            <div class="flex-shrink-0 p-3 bg-gray-100 dark:bg-gray-700 rounded-lg">
                <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Not Logged In</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white" data-stat="teachers-not-logged-in">{{ number_format($totalTeachers - $todayTeacherAttendance) }}</p>
            </div>
        </div>
    </x-card>
</div>

<!-- Attendance Status Breakdown -->
<x-card title="Today's Teacher Attendance Status">
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
            <p class="text-3xl font-bold text-green-600 dark:text-green-400" data-stat="teachers-confirmed">{{ $confirmedTeachers }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Confirmed</p>
        </div>
        <div class="text-center p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
            <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400" data-stat="teachers-pending">{{ $pendingTeachers }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Pending</p>
        </div>
        <div class="text-center p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
            <p class="text-3xl font-bold text-red-600 dark:text-red-400" data-stat="teachers-late">{{ $lateTeachers }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Late</p>
        </div>
        <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <p class="text-3xl font-bold text-gray-600 dark:text-gray-400" data-stat="teachers-absent">{{ $absentTeachers }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Absent</p>
        </div>
        <div class="text-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <p class="text-3xl font-bold text-blue-600 dark:text-blue-400" data-stat="teachers-not-logged-in">{{ $totalTeachers - $todayTeacherAttendance }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Not Logged In</p>
        </div>
    </div>
</x-card>

<!-- Quick Actions -->
<x-card title="Quick Actions">
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <a href="{{ route('teacher-monitoring.index') }}" class="flex flex-col items-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Teacher Monitoring</span>
        </a>
        <a href="{{ route('teacher-monitoring.today') }}" class="flex flex-col items-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-8 h-8 text-green-600 dark:text-green-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Today's Attendance</span>
        </a>
        <a href="{{ route('attendance.index') }}" class="flex flex-col items-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-8 h-8 text-blue-600 dark:text-blue-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Student Attendance</span>
        </a>
    </div>
</x-card>

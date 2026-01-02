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

    <!-- Today's Attendance -->
    <x-card>
        <div class="flex items-center">
            <div class="flex-shrink-0 p-3 bg-blue-100 dark:bg-blue-900/50 rounded-lg">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Today's Scans</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white" data-stat="today-scans">{{ number_format($todayAttendance) }}</p>
            </div>
        </div>
    </x-card>
</div>

<!-- Teacher Attendance Summary -->
<x-card title="Teacher Attendance Today">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="text-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <p class="text-3xl font-bold text-gray-900 dark:text-white" data-stat="teachers-logged-in">{{ $todayTeacherAttendance }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Logged In</p>
        </div>
        <div class="text-center p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg">
            <p class="text-3xl font-bold text-yellow-600 dark:text-yellow-400" data-stat="teachers-pending">{{ $pendingTeachers }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Pending</p>
        </div>
        <div class="text-center p-4 bg-red-50 dark:bg-red-900/20 rounded-lg">
            <p class="text-3xl font-bold text-red-600 dark:text-red-400" data-stat="teachers-late">{{ $lateTeachers }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Late</p>
        </div>
        <div class="text-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
            <p class="text-3xl font-bold text-green-600 dark:text-green-400" data-stat="teachers-confirmed">{{ $todayTeacherAttendance - $pendingTeachers - $lateTeachers }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">On Time</p>
        </div>
    </div>
</x-card>

<!-- Quick Actions -->
<x-card title="Quick Actions">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('students.index') }}" class="flex flex-col items-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
            </svg>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Students</span>
        </a>
        <a href="{{ route('classes.index') }}" class="flex flex-col items-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-8 h-8 text-green-600 dark:text-green-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Classes</span>
        </a>
        <a href="{{ route('users.index') }}" class="flex flex-col items-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Users</span>
        </a>
        <a href="{{ route('time-schedules.index') }}" class="flex flex-col items-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-8 h-8 text-blue-600 dark:text-blue-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Schedules</span>
        </a>
    </div>
</x-card>

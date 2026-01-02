{{-- Teacher Dashboard Partial --}}

<!-- Teacher's Own Attendance Status -->
@if($teacherAttendance)
<x-card title="Your Attendance Today">
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <div class="flex-shrink-0">
                @if($teacherAttendance->attendance_status === 'confirmed')
                    <div class="p-3 bg-green-100 dark:bg-green-900/50 rounded-full">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                @elseif($teacherAttendance->attendance_status === 'late')
                    <div class="p-3 bg-red-100 dark:bg-red-900/50 rounded-full">
                        <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                @else
                    <div class="p-3 bg-yellow-100 dark:bg-yellow-900/50 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                @endif
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                <x-badge type="{{ $teacherAttendance->attendance_status === 'confirmed' ? 'success' : ($teacherAttendance->attendance_status === 'late' ? 'danger' : 'warning') }}">
                    {{ ucfirst($teacherAttendance->attendance_status) }}
                </x-badge>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-6 text-center">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Time In</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ $teacherAttendance->time_in ? $teacherAttendance->time_in->format('h:i A') : '-' }}
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">First Scan</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ $teacherAttendance->first_student_scan ? $teacherAttendance->first_student_scan->format('h:i A') : '-' }}
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Time Out</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ $teacherAttendance->time_out ? $teacherAttendance->time_out->format('h:i A') : '-' }}
                </p>
            </div>
        </div>
    </div>
</x-card>
@endif

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <!-- My Classes -->
    <x-card>
        <div class="flex items-center">
            <div class="flex-shrink-0 p-3 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg">
                <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">My Classes</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $classes->count() }}</p>
            </div>
        </div>
    </x-card>

    <!-- My Students -->
    <x-card>
        <div class="flex items-center">
            <div class="flex-shrink-0 p-3 bg-green-100 dark:bg-green-900/50 rounded-lg">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">My Students</p>
                <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ number_format($totalStudents) }}</p>
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

<!-- My Classes List -->
@if($classes->count() > 0)
<x-card title="My Classes">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($classes as $class)
        <a href="{{ route('classes.show', $class) }}" class="block p-4 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <div class="flex items-center justify-between mb-2">
                <h4 class="font-medium text-gray-900 dark:text-white">
                    Grade {{ $class->grade_level }} - {{ $class->section }}
                </h4>
                <x-badge type="{{ $class->is_active ? 'success' : 'default' }}" size="sm">
                    {{ $class->is_active ? 'Active' : 'Inactive' }}
                </x-badge>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $class->students->count() }} / {{ $class->max_capacity }} students
            </p>
        </a>
        @endforeach
    </div>
</x-card>
@endif

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
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">My Students</span>
        </a>
        <a href="{{ route('classes.index') }}" class="flex flex-col items-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">My Classes</span>
        </a>
        <a href="{{ route('attendance.index') }}" class="flex flex-col items-center p-4 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-8 h-8 text-blue-600 dark:text-blue-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Attendance</span>
        </a>
    </div>
</x-card>

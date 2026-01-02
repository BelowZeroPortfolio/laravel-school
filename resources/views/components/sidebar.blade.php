<!-- Mobile sidebar backdrop -->
<div x-show="sidebarOpen" 
     x-transition:enter="transition-opacity ease-linear duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-300"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="sidebarOpen = false"
     class="fixed inset-0 bg-gray-600 bg-opacity-75 z-20 lg:hidden">
</div>

<!-- Sidebar -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
       class="fixed inset-y-0 left-0 z-30 w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0">
    
    <!-- Logo -->
    <div class="flex items-center justify-center h-16 border-b border-gray-200 dark:border-gray-700">
        <a href="{{ Route::has('dashboard') ? route('dashboard') : '#' }}" class="flex items-center space-x-2">
            <svg class="h-8 w-8 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
            </svg>
            <span class="text-xl font-bold text-gray-900 dark:text-white">QR Attendance</span>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
        @auth
            @php
                $user = auth()->user();
            @endphp

            <!-- Dashboard - All authenticated users -->
            @if(Route::has('dashboard'))
                <x-sidebar-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                    <x-slot name="icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </x-slot>
                    Dashboard
                </x-sidebar-link>
            @endif

            <!-- QR Scan - Teachers and above -->
            @if(($user->isTeacher() || $user->isPrincipal() || $user->isAdmin()) && Route::has('scan.index'))
                <x-sidebar-link href="{{ route('scan.index') }}" :active="request()->routeIs('scan.*')">
                    <x-slot name="icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                    </x-slot>
                    QR Scan
                </x-sidebar-link>
            @endif

            <!-- Students - Teachers and above -->
            @if(($user->isTeacher() || $user->isPrincipal() || $user->isAdmin()) && Route::has('students.index'))
                <x-sidebar-link href="{{ route('students.index') }}" :active="request()->routeIs('students.*')">
                    <x-slot name="icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </x-slot>
                    Students
                </x-sidebar-link>
            @endif

            <!-- Classes - Teachers and above -->
            @if(($user->isTeacher() || $user->isPrincipal() || $user->isAdmin()) && Route::has('classes.index'))
                <x-sidebar-link href="{{ route('classes.index') }}" :active="request()->routeIs('classes.*')">
                    <x-slot name="icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </x-slot>
                    Classes
                </x-sidebar-link>
            @endif

            <!-- Attendance - Teachers and above -->
            @if(($user->isTeacher() || $user->isPrincipal() || $user->isAdmin()) && Route::has('attendance.index'))
                <x-sidebar-link href="{{ route('attendance.index') }}" :active="request()->routeIs('attendance.*')">
                    <x-slot name="icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </x-slot>
                    Attendance
                </x-sidebar-link>
            @endif

            <!-- Teacher Monitoring - Principals and Admins only -->
            @if(($user->isPrincipal() || $user->isAdmin()) && Route::has('teacher-monitoring.index'))
                <x-sidebar-link href="{{ route('teacher-monitoring.index') }}" :active="request()->routeIs('teacher-monitoring.*')">
                    <x-slot name="icon">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </x-slot>
                    Teacher Monitoring
                </x-sidebar-link>
            @endif

            <!-- Admin Section Divider -->
            @if($user->isAdmin())
                <div class="pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
                    <p class="px-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        Administration
                    </p>
                </div>

                <!-- Users - Admin only -->
                @if(Route::has('users.index'))
                    <x-sidebar-link href="{{ route('users.index') }}" :active="request()->routeIs('users.*')">
                        <x-slot name="icon">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </x-slot>
                        Users
                    </x-sidebar-link>
                @endif

                <!-- Time Schedules - Admin only -->
                @if(Route::has('time-schedules.index'))
                    <x-sidebar-link href="{{ route('time-schedules.index') }}" :active="request()->routeIs('time-schedules.*')">
                        <x-slot name="icon">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </x-slot>
                        Time Schedules
                    </x-sidebar-link>
                @endif

                <!-- School Years - Admin only -->
                @if(Route::has('school-years.index'))
                    <x-sidebar-link href="{{ route('school-years.index') }}" :active="request()->routeIs('school-years.*')">
                        <x-slot name="icon">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </x-slot>
                        School Years
                    </x-sidebar-link>
                @endif
            @endif
        @endauth
    </nav>

    <!-- User Info at Bottom -->
    @auth
        <div class="border-t border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="h-10 w-10 rounded-full bg-indigo-600 dark:bg-indigo-500 flex items-center justify-center">
                        <span class="text-white font-medium text-sm">
                            {{ strtoupper(substr(auth()->user()->full_name ?? auth()->user()->username, 0, 2)) }}
                        </span>
                    </div>
                </div>
                <div class="ml-3 min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                        {{ auth()->user()->full_name ?? auth()->user()->username }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">
                        {{ auth()->user()->role }}
                    </p>
                </div>
            </div>
        </div>
    @endauth
</aside>

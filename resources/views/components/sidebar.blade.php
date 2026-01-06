@php
    $user = auth()->user();
    $userRole = $user?->role ?? 'teacher';
@endphp

<!-- Desktop Sidebar -->
<aside class="hidden lg:block bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 fixed left-0 top-0 h-screen z-50 transition-all duration-300"
       :class="sidebarCollapsed ? 'w-20' : 'w-64'">
    
    <!-- Scrollable content wrapper -->
    <div class="h-full overflow-y-auto pb-20">
    
    <!-- Logo Section -->
    <div class="border-b border-gray-200 dark:border-gray-700 flex items-center h-16"
         :class="sidebarCollapsed ? 'justify-center px-4' : 'justify-between px-6'">
        <a x-show="!sidebarCollapsed" x-transition href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden">
                <img src="{{ asset('images/lex.png') }}" alt="Lexite PH" class="w-8 h-8 object-contain">
            </div>
            <div class="overflow-hidden">
                <span class="text-sm font-semibold text-gray-900 dark:text-white block leading-tight whitespace-nowrap">Lexite PH</span>
                <span class="text-xs text-gray-500 dark:text-gray-400 block leading-tight whitespace-nowrap">Attendance System</span>
            </div>
        </a>
        <!-- Burger menu only when collapsed -->
        <button x-show="sidebarCollapsed" @click="sidebarCollapsed = !sidebarCollapsed" 
                class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none transition-colors">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
        <button x-show="!sidebarCollapsed" @click="sidebarCollapsed = !sidebarCollapsed" 
                class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none transition-colors flex-shrink-0">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
        </button>
    </div>

    <nav class="py-4 px-3">
        <!-- MAIN Section -->
        <div class="mb-4">
            <div x-show="!sidebarCollapsed" class="px-3 mb-2">
                <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Main</span>
            </div>
            <div x-show="sidebarCollapsed" class="border-b border-gray-100 dark:border-gray-700 mx-2 mb-3"></div>

            <!-- Dashboard -->
            <x-sidebar-item 
                href="{{ route('dashboard') }}" 
                :active="request()->routeIs('dashboard')"
                icon="home"
                label="Dashboard" />

            <!-- Scan Attendance - Admin and Teacher only -->
            @if(in_array($userRole, ['admin', 'teacher']) && Route::has('scan.index'))
                <x-sidebar-item 
                    href="{{ route('scan.index') }}" 
                    :active="request()->routeIs('scan.*')"
                    icon="qr-code"
                    label="Scan Attendance"
                    activeColor="green" />
            @endif
        </div>

        <!-- STUDENT MANAGEMENT Section -->
        @if(in_array($userRole, ['admin', 'principal', 'teacher']))
            <div class="mb-4">
                <div x-show="!sidebarCollapsed" class="px-3 mb-2">
                    <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">
                        {{ $userRole === 'principal' ? 'School Overview' : 'Student Management' }}
                    </span>
                </div>
                <div x-show="sidebarCollapsed" class="border-b border-gray-100 dark:border-gray-700 mx-2 mb-3"></div>

                <!-- Classes -->
                @if(Route::has('classes.index'))
                    <x-sidebar-item 
                        href="{{ route('classes.index') }}" 
                        :active="request()->routeIs('classes.*')"
                        icon="building"
                        :label="$userRole === 'teacher' ? 'My Class' : 'Classes'" />
                @endif

                <!-- Students -->
                @if(Route::has('students.index'))
                    <x-sidebar-item 
                        href="{{ route('students.index') }}" 
                        :active="request()->routeIs('students.*')"
                        icon="users"
                        :label="$userRole === 'teacher' ? 'My Students' : 'Students'" />
                @endif

                <!-- Generate ID Cards - Admin only -->
                @if($userRole === 'admin' && Route::has('id-cards.index'))
                    <x-sidebar-item 
                        href="{{ route('id-cards.index') }}" 
                        :active="request()->routeIs('id-cards.*')"
                        icon="id-card"
                        label="Generate ID Cards" />
                @endif
            </div>
        @endif

        <!-- ATTENDANCE & REPORTS Section -->
        <div class="mb-4">
            <div x-show="!sidebarCollapsed" class="px-3 mb-2">
                <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Attendance & Reports</span>
            </div>
            <div x-show="sidebarCollapsed" class="border-b border-gray-100 dark:border-gray-700 mx-2 mb-3"></div>

            <!-- Attendance -->
            @if(Route::has('attendance.index'))
                <x-sidebar-item 
                    href="{{ route('attendance.index') }}" 
                    :active="request()->routeIs('attendance.*')"
                    icon="clipboard"
                    label="Attendance" />
            @endif

            <!-- Reports -->
            @if(Route::has('reports.index'))
                <x-sidebar-item 
                    href="{{ route('reports.index') }}" 
                    :active="request()->routeIs('reports.*')"
                    icon="chart"
                    label="Reports" />
            @endif
        </div>

        <!-- ADMINISTRATION Section (Admin and Principal) -->
        @if(in_array($userRole, ['admin', 'principal']))
            <div class="mb-4">
                <div x-show="!sidebarCollapsed" class="px-3 mb-2">
                    <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">
                        {{ $userRole === 'principal' ? 'School Administration' : 'Administration' }}
                    </span>
                </div>
                <div x-show="sidebarCollapsed" class="border-b border-gray-100 dark:border-gray-700 mx-2 mb-3"></div>

                <!-- Teacher Monitoring -->
                @if(Route::has('teacher-monitoring.index'))
                    <x-sidebar-item 
                        href="{{ route('teacher-monitoring.index') }}" 
                        :active="request()->routeIs('teacher-monitoring.*')"
                        icon="eye"
                        label="Teacher Monitoring" />
                @endif

                <!-- Users - Admin only -->
                @if($userRole === 'admin' && Route::has('users.index'))
                    <x-sidebar-item 
                        href="{{ route('users.index') }}" 
                        :active="request()->routeIs('users.*')"
                        icon="user-group"
                        label="Users" />
                @endif

                <!-- School Years - Admin only -->
                @if($userRole === 'admin' && Route::has('school-years.index'))
                    <x-sidebar-item 
                        href="{{ route('school-years.index') }}" 
                        :active="request()->routeIs('school-years.*')"
                        icon="calendar"
                        label="School Years" />
                @endif

                <!-- Student Placement - Admin only -->
                @if($userRole === 'admin' && Route::has('student-placements.index'))
                    <x-sidebar-item 
                        href="{{ route('student-placements.index') }}" 
                        :active="request()->routeIs('student-placements.*')"
                        icon="switch"
                        label="Student Placement" />
                @endif

                <!-- Time Schedules -->
                @if(Route::has('time-schedules.index'))
                    <x-sidebar-item 
                        href="{{ route('time-schedules.index') }}" 
                        :active="request()->routeIs('time-schedules.*')"
                        icon="clock"
                        label="Time Schedules" />
                @endif

                <!-- Subscriptions - Admin only -->
                @if($userRole === 'admin' && Route::has('subscriptions.index'))
                    <x-sidebar-item 
                        href="{{ route('subscriptions.index') }}" 
                        :active="request()->routeIs('subscriptions.*')"
                        icon="sparkles"
                        label="Subscriptions" />
                @endif

                <!-- Settings - Admin only -->
                @if($userRole === 'admin' && Route::has('settings.index'))
                    <x-sidebar-item 
                        href="{{ route('settings.index') }}" 
                        :active="request()->routeIs('settings.*')"
                        icon="cog"
                        label="Settings" />
                @endif
            </div>
        @endif

        <!-- SUPER ADMIN Section -->
        @if($user?->isSuperAdmin())
            <div class="mb-4">
                <div x-show="!sidebarCollapsed" class="px-3 mb-2">
                    <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Super Admin</span>
                </div>
                <div x-show="sidebarCollapsed" class="border-b border-gray-100 dark:border-gray-700 mx-2 mb-3"></div>

                @if(Route::has('super-admin.dashboard'))
                    <x-sidebar-item 
                        href="{{ route('super-admin.dashboard') }}" 
                        :active="request()->routeIs('super-admin.dashboard')"
                        icon="home"
                        label="Super Dashboard"
                        activeColor="red" />
                @endif

                @if(Route::has('super-admin.schools.index'))
                    <x-sidebar-item 
                        href="{{ route('super-admin.schools.index') }}" 
                        :active="request()->routeIs('super-admin.schools.*')"
                        icon="building"
                        label="Schools"
                        activeColor="red" />
                @endif
            </div>
        @endif
    </nav>
    </div>

    <!-- User Info at Bottom -->
    @auth
        <div class="absolute bottom-0 left-0 right-0 border-t border-gray-200 dark:border-gray-700 p-4 bg-white dark:bg-gray-800">
            <div class="flex items-center" :class="sidebarCollapsed ? 'justify-center' : ''">
                <div class="flex-shrink-0">
                    <div class="h-10 w-10 rounded-full bg-indigo-600 dark:bg-indigo-500 flex items-center justify-center">
                        <span class="text-white font-medium text-sm">
                            {{ strtoupper(substr($user->full_name ?? $user->username, 0, 2)) }}
                        </span>
                    </div>
                </div>
                <div x-show="!sidebarCollapsed" x-transition class="ml-3 min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                        {{ $user->full_name ?? $user->username }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 capitalize">
                        {{ str_replace('_', ' ', $user->role) }}
                    </p>
                </div>
            </div>
        </div>
    @endauth
</aside>

<!-- Mobile Sidebar Backdrop -->
<div x-show="sidebarOpen" 
     x-transition:enter="transition-opacity ease-linear duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-linear duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="sidebarOpen = false"
     class="fixed inset-0 bg-gray-600/75 z-40 lg:hidden">
</div>

<!-- Mobile Sidebar -->
<aside x-show="sidebarOpen"
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="-translate-x-full"
       x-transition:enter-end="translate-x-0"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="translate-x-0"
       x-transition:leave-end="-translate-x-full"
       class="fixed inset-y-0 left-0 w-64 bg-white dark:bg-gray-800 shadow-xl z-50 transform lg:hidden overflow-y-auto">
    
    <!-- Mobile Header -->
    <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-2.5">
            <img src="{{ asset('images/lex.png') }}" alt="Lexite PH" class="w-8 h-8 object-contain">
            <div>
                <span class="text-sm font-semibold text-gray-900 dark:text-white block leading-tight">Lexite PH</span>
                <span class="text-xs text-gray-500 dark:text-gray-400 block leading-tight">Attendance System</span>
            </div>
        </div>
        <button @click="sidebarOpen = false" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
            <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <!-- Mobile Navigation -->
    <nav class="py-4 px-4">
        @include('components.sidebar-mobile-nav')
    </nav>
</aside>

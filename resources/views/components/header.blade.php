<header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
    <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
        <!-- Mobile menu button -->
        <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-md text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Page Title -->
        <div class="flex-1 lg:flex-none">
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white">
                @yield('page-title', 'Dashboard')
            </h1>
        </div>

        <!-- Right side items -->
        <div class="flex items-center space-x-4">
            <!-- School Year Selector -->
            @auth
                <x-school-year-selector />
            @endauth

            <!-- Theme Toggle -->
            <x-theme-toggle />

            <!-- User Menu -->
            @auth
                <x-user-menu />
            @endauth
        </div>
    </div>
</header>

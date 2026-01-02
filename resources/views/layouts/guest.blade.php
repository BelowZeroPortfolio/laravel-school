<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="{ darkMode: localStorage.getItem('theme') === 'dark' || !localStorage.getItem('theme') }" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'QR Attendance System') }} - @yield('title', 'Welcome')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen" data-authenticated="false">
    <div class="min-h-screen flex flex-col">
        <!-- Header with theme toggle -->
        <header class="absolute top-0 right-0 p-4">
            <x-theme-toggle />
        </header>

        <!-- Main Content -->
        <main class="flex-1 flex items-center justify-center p-4">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="py-4 text-center text-sm text-gray-500 dark:text-gray-400">
            &copy; {{ date('Y') }} QR Attendance System. All rights reserved.
        </footer>
    </div>

    @stack('scripts')
</body>
</html>

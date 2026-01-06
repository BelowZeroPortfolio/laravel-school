<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Lexite PH') }} - @yield('title', 'Dashboard')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/lex.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/lex.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
    
    <!-- Prevent FOUC for dark mode -->
    <script>
        (function() {
            const theme = localStorage.getItem('theme');
            const isAuth = true;
            if (theme === 'dark' || (!theme && !isAuth)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
</head>
<body class="font-sans antialiased bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen" data-authenticated="true">
    <div x-data="{ 
            sidebarOpen: false, 
            sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true',
            darkMode: localStorage.getItem('theme') === 'dark'
         }" 
         x-init="$watch('sidebarCollapsed', val => localStorage.setItem('sidebarCollapsed', val))"
         @toggle-dark-mode.window="darkMode = !darkMode; localStorage.setItem('theme', darkMode ? 'dark' : 'light'); document.documentElement.classList.toggle('dark', darkMode)"
         class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <x-sidebar />

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden transition-all duration-300"
             :class="sidebarCollapsed ? 'lg:ml-20' : 'lg:ml-64'">
            <!-- Header -->
            <x-header />

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Flash Messages -->
                @if(session('success'))
                    <x-alert type="success" :message="session('success')" class="mb-4" />
                @endif

                @if(session('error'))
                    <x-alert type="error" :message="session('error')" class="mb-4" />
                @endif

                @if(session('warning'))
                    <x-alert type="warning" :message="session('warning')" class="mb-4" />
                @endif

                @if(session('info'))
                    <x-alert type="info" :message="session('info')" class="mb-4" />
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
    
    <script>
    /**
     * Real-time Form Validation
     */
    (function() {
        const FormValidator = {
            rules: {
                required: (value) => value.trim() !== '',
                minlength: (value, min) => value.length >= parseInt(min),
                maxlength: (value, max) => value.length <= parseInt(max),
                pattern: (value, pattern) => new RegExp(pattern).test(value) || value === '',
                email: (value) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value) || value === '',
                confirmed: (value, fieldName, input) => {
                    const form = input.closest('form');
                    const confirmField = form.querySelector(`[name="${fieldName}_confirmation"]`);
                    return !confirmField || confirmField.value === '' || value === confirmField.value;
                },
                confirmation: (value, fieldName, input) => {
                    const form = input.closest('form');
                    const originalField = form.querySelector(`[name="${fieldName.replace('_confirmation', '')}"]`);
                    return !originalField || value === '' || value === originalField.value;
                },
            },

            messages: {
                required: 'This field is required.',
                minlength: (min) => `Must be at least ${min} characters.`,
                maxlength: (max) => `Must not exceed ${max} characters.`,
                pattern: 'Please match the requested format.',
                email: 'Please enter a valid email address.',
                confirmed: 'Passwords do not match.',
                confirmation: 'Passwords do not match.',
            },

            init() {
                document.querySelectorAll('form[data-validate]').forEach(form => this.setupForm(form));
            },

            setupForm(form) {
                const inputs = form.querySelectorAll('[data-validate]');
                
                inputs.forEach(input => {
                    input.addEventListener('blur', () => this.validateField(input));
                    
                    let timeout;
                    input.addEventListener('input', () => {
                        clearTimeout(timeout);
                        timeout = setTimeout(() => {
                            this.validateField(input);
                            // Also validate related password field
                            if (input.name === 'password') {
                                const confirmField = form.querySelector('[name="password_confirmation"]');
                                if (confirmField && confirmField.value) this.validateField(confirmField);
                            }
                            if (input.name === 'password_confirmation') {
                                const passwordField = form.querySelector('[name="password"]');
                                if (passwordField) this.validateField(passwordField);
                            }
                        }, 300);
                    });
                });

                form.addEventListener('submit', (e) => {
                    let isValid = true;
                    inputs.forEach(input => {
                        if (!this.validateField(input)) isValid = false;
                    });
                    
                    if (!isValid) {
                        e.preventDefault();
                        const firstInvalid = form.querySelector('[data-input-wrapper].validation-error input');
                        if (firstInvalid) firstInvalid.focus();
                    }
                });
            },

            validateField(input) {
                const rules = this.parseRules(input);
                const value = input.value;
                const wrapper = input.closest('[data-input-wrapper]');
                
                if (!wrapper) return true;
                
                this.clearError(input, wrapper);
                
                if (value === '' && !rules.required) return true;

                for (const [rule, param] of Object.entries(rules)) {
                    let isValid = true;
                    if (rule === 'confirmed' || rule === 'confirmation') {
                        isValid = this.rules[rule](value, input.name, input);
                    } else if (this.rules[rule]) {
                        isValid = this.rules[rule](value, param);
                    }
                    
                    if (!isValid) {
                        this.showError(input, wrapper, this.getMessage(rule, param, input));
                        return false;
                    }
                }

                this.showSuccess(input, wrapper);
                return true;
            },

            parseRules(input) {
                const rules = {};
                if (input.hasAttribute('required')) rules.required = true;
                if (input.hasAttribute('minlength')) rules.minlength = input.getAttribute('minlength');
                if (input.hasAttribute('maxlength')) rules.maxlength = input.getAttribute('maxlength');
                if (input.hasAttribute('pattern')) rules.pattern = input.getAttribute('pattern');
                if (input.type === 'email') rules.email = true;
                // Password confirmation check
                if (input.name === 'password') rules.confirmed = true;
                if (input.name === 'password_confirmation') rules.confirmation = true;
                return rules;
            },

            getMessage(rule, param, input) {
                if (input.title && rule === 'pattern') return input.title;
                const msg = this.messages[rule];
                return typeof msg === 'function' ? msg(param) : msg;
            },

            showError(input, wrapper, message) {
                wrapper.classList.add('validation-error');
                wrapper.classList.remove('validation-success');
                
                input.classList.remove('border-gray-300', 'dark:border-gray-600', 'border-green-500', 'dark:border-green-500');
                input.classList.add('border-red-500', 'dark:border-red-500', 'bg-red-50', 'dark:bg-red-900/20');
                
                wrapper.querySelectorAll('.validation-message').forEach(el => el.remove());
                wrapper.querySelectorAll('.hint-text').forEach(el => el.classList.add('hidden'));
                
                const errorEl = document.createElement('p');
                errorEl.className = 'validation-message mt-1 text-xs text-red-600 dark:text-red-400';
                errorEl.textContent = message;
                wrapper.appendChild(errorEl);
            },

            showSuccess(input, wrapper) {
                wrapper.classList.add('validation-success');
                wrapper.classList.remove('validation-error');
                
                input.classList.remove('border-gray-300', 'dark:border-gray-600', 'border-red-500', 'dark:border-red-500', 'bg-red-50', 'dark:bg-red-900/20');
                input.classList.add('border-green-500', 'dark:border-green-500');
            },

            clearError(input, wrapper) {
                wrapper.classList.remove('validation-error', 'validation-success');
                
                input.classList.remove('border-red-500', 'dark:border-red-500', 'border-green-500', 'dark:border-green-500', 'bg-red-50', 'dark:bg-red-900/20');
                input.classList.add('border-gray-300', 'dark:border-gray-600');
                
                wrapper.querySelectorAll('.validation-message').forEach(el => el.remove());
                wrapper.querySelectorAll('.hint-text').forEach(el => el.classList.remove('hidden'));
            }
        };

        document.addEventListener('DOMContentLoaded', () => FormValidator.init());
        window.FormValidator = FormValidator;
    })();
    </script>
</body>
</html>

@extends('layouts.app')

@section('title', $user->full_name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('users.index') }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Users
            </a>
            <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $user->full_name }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->username }}</p>
        </div>
        <div class="flex items-center space-x-3">
            @php
                $roleBadgeTypes = [
                    'admin' => 'danger',
                    'principal' => 'warning',
                    'teacher' => 'success',
                ];
            @endphp
            <x-badge type="{{ $roleBadgeTypes[$user->role] ?? 'default' }}">
                {{ ucfirst($user->role) }}
            </x-badge>
            <x-badge type="{{ $user->is_active ? 'success' : 'default' }}">
                {{ $user->is_active ? 'Active' : 'Inactive' }}
            </x-badge>
            <a href="{{ route('users.edit', $user) }}">
                <x-button variant="outline">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </x-button>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- User Details -->
            <x-card title="User Information">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Full Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $user->full_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Username</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono">{{ $user->username }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $user->email ?? 'Not set' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Role</dt>
                        <dd class="mt-1">
                            <x-badge type="{{ $roleBadgeTypes[$user->role] ?? 'default' }}" size="sm">
                                {{ ucfirst($user->role) }}
                            </x-badge>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Login</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $user->last_login ? $user->last_login->format('M d, Y h:i A') : 'Never' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Premium Status</dt>
                        <dd class="mt-1">
                            <x-badge type="{{ $user->is_premium ? 'primary' : 'default' }}" size="sm">
                                {{ $user->is_premium ? 'Premium' : 'Standard' }}
                            </x-badge>
                            @if($user->is_premium && $user->premium_expires_at)
                                <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">
                                    Expires: {{ $user->premium_expires_at->format('M d, Y') }}
                                </span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </x-card>

            <!-- Classes (for teachers) -->
            @if($user->isTeacher() && $user->classes->count() > 0)
            <x-card title="Assigned Classes" :padding="false">
                <x-table>
                    <x-slot name="head">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Class</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">School Year</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Students</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        </tr>
                    </x-slot>
                    @foreach($user->classes as $class)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('classes.show', $class) }}" class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                                    Grade {{ $class->grade_level }} - {{ $class->section }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $class->schoolYear->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $class->students_count ?? $class->students->count() }} / {{ $class->max_capacity }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-badge type="{{ $class->is_active ? 'success' : 'default' }}" size="sm">
                                    {{ $class->is_active ? 'Active' : 'Inactive' }}
                                </x-badge>
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            </x-card>
            @endif

            <!-- Recent Attendance (for teachers) -->
            @if($user->isTeacher() && $user->teacherAttendances->count() > 0)
            <x-card title="Recent Attendance" :padding="false">
                <x-table>
                    <x-slot name="head">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Time In</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">First Scan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        </tr>
                    </x-slot>
                    @foreach($user->teacherAttendances as $attendance)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $attendance->attendance_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $attendance->time_in ? $attendance->time_in->format('h:i A') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $attendance->first_student_scan ? $attendance->first_student_scan->format('h:i A') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'confirmed' => 'success',
                                        'late' => 'danger',
                                        'pending' => 'warning',
                                        'absent' => 'default',
                                        'no_scan' => 'info',
                                    ];
                                @endphp
                                <x-badge type="{{ $statusColors[$attendance->attendance_status] ?? 'default' }}" size="sm">
                                    {{ ucfirst(str_replace('_', ' ', $attendance->attendance_status)) }}
                                </x-badge>
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            </x-card>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Profile Card -->
            <x-card>
                <div class="text-center">
                    @php
                        $roleColors = [
                            'admin' => 'bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-400',
                            'principal' => 'bg-yellow-100 dark:bg-yellow-900/50 text-yellow-600 dark:text-yellow-400',
                            'teacher' => 'bg-green-100 dark:bg-green-900/50 text-green-600 dark:text-green-400',
                        ];
                    @endphp
                    <div class="mx-auto h-20 w-20 rounded-full flex items-center justify-center {{ $roleColors[$user->role] ?? 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }}">
                        <span class="text-2xl font-bold">
                            {{ strtoupper(substr($user->full_name, 0, 2)) }}
                        </span>
                    </div>
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">{{ $user->full_name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ ucfirst($user->role) }}</p>
                </div>
            </x-card>

            <!-- Quick Actions -->
            <x-card title="Quick Actions">
                <div class="space-y-2">
                    <a href="{{ route('users.edit', $user) }}" class="block w-full">
                        <x-button variant="outline" class="w-full justify-start">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit User
                        </x-button>
                    </a>
                    @if(!$user->is_active)
                    <form method="POST" action="{{ route('users.reactivate', $user) }}">
                        @csrf
                        @method('PATCH')
                        <x-button type="submit" variant="success" class="w-full justify-start">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Reactivate User
                        </x-button>
                    </form>
                    @endif
                    @if($user->isTeacher())
                    <a href="{{ route('teacher-monitoring.show', $user) }}" class="block w-full">
                        <x-button variant="outline" class="w-full justify-start">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            View Attendance
                        </x-button>
                    </a>
                    @endif
                </div>
            </x-card>

            <!-- Timestamps -->
            <x-card title="Account Info">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">Created</dt>
                        <dd class="text-gray-900 dark:text-white">{{ $user->created_at->format('M d, Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">Updated</dt>
                        <dd class="text-gray-900 dark:text-white">{{ $user->updated_at->format('M d, Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500 dark:text-gray-400">Last Login</dt>
                        <dd class="text-gray-900 dark:text-white">{{ $user->last_login ? $user->last_login->diffForHumans() : 'Never' }}</dd>
                    </div>
                </dl>
            </x-card>
        </div>
    </div>
</div>
@endsection

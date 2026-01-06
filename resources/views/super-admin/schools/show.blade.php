@extends('layouts.app')

@section('title', $school->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
            <a href="{{ route('super-admin.schools.index') }}" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $school->name }}</h1>
                <p class="text-gray-600 dark:text-gray-400">
                    <span class="font-mono text-sm bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">{{ $school->code }}</span>
                    @if($school->is_active)
                        <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Active</span>
                    @else
                        <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Inactive</span>
                    @endif
                </p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('super-admin.schools.edit', $school) }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Edit
            </a>
            @if($school->is_active)
                <form method="POST" action="{{ route('super-admin.schools.destroy', $school) }}" onsubmit="return confirm('Are you sure you want to deactivate this school?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                        Deactivate
                    </button>
                </form>
            @else
                <form method="POST" action="{{ route('super-admin.schools.reactivate', $school) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                        Reactivate
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">Users</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_users'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">Students</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_students'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">Classes</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_classes'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-4 border border-gray-200 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400">Active School Years</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['active_school_years'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- School Details -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">School Details</h2>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
                    <p class="text-gray-900 dark:text-white">{{ $school->email ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Phone</p>
                    <p class="text-gray-900 dark:text-white">{{ $school->phone ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Address</p>
                    <p class="text-gray-900 dark:text-white">{{ $school->address ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Created</p>
                    <p class="text-gray-900 dark:text-white">{{ $school->created_at->format('M d, Y') }}</p>
                </div>
            </div>
        </div>

        <!-- Users -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Users</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($school->users as $user)
                            <tr>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->full_name }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->username }}</div>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full 
                                        @if($user->role === 'admin') bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200
                                        @elseif($user->role === 'principal') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 @endif">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap">
                                    @if($user->is_active)
                                        <span class="text-green-600 dark:text-green-400">Active</span>
                                    @else
                                        <span class="text-red-600 dark:text-red-400">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No users found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

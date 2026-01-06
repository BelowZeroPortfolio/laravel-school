@extends('layouts.app')

@section('title', 'Subscriptions')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Subscriptions</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Manage teacher premium subscriptions
        </p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-card>
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-indigo-100 dark:bg-indigo-900/50 rounded-lg">
                    <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Teachers</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $teachers->count() }}</p>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-green-100 dark:bg-green-900/50 rounded-lg">
                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Premium Active</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $teachers->where('premium_active', true)->count() }}</p>
                </div>
            </div>
        </x-card>

        <x-card>
            <div class="flex items-center">
                <div class="flex-shrink-0 p-3 bg-gray-100 dark:bg-gray-700 rounded-lg">
                    <svg class="h-6 w-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Free Plan</p>
                    <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $teachers->where('premium_active', false)->count() }}</p>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Teachers Table -->
    <x-card :padding="false">
        <x-table>
            <x-slot name="head">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Teacher</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subscription</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Expires</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </x-slot>

            @forelse($teachers as $teacher)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-green-100 dark:bg-green-900/50 flex items-center justify-center">
                                <span class="text-sm font-medium text-green-600 dark:text-green-400">
                                    {{ strtoupper(substr($teacher->full_name, 0, 2)) }}
                                </span>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $teacher->full_name }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $teacher->email ?? $teacher->username }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <x-badge type="{{ $teacher->is_active ? 'success' : 'default' }}" size="sm">
                            {{ $teacher->is_active ? 'Active' : 'Inactive' }}
                        </x-badge>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($teacher->premium_active)
                            <x-badge type="warning" size="sm">Premium</x-badge>
                        @else
                            <x-badge type="default" size="sm">Free</x-badge>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        @if($teacher->premium_expires_at)
                            {{ $teacher->premium_expires_at->format('M d, Y') }}
                            @if($teacher->premium_expires_at->isPast())
                                <span class="text-red-500">(Expired)</span>
                            @elseif($teacher->premium_expires_at->diffInDays(now()) <= 7)
                                <span class="text-yellow-500">(Expiring soon)</span>
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        @if($teacher->premium_active)
                            <form action="{{ route('subscriptions.revoke') }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to revoke premium access?')">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $teacher->id }}">
                                <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                                    Revoke
                                </button>
                            </form>
                        @else
                            <button type="button" 
                                    onclick="openGrantModal({{ $teacher->id }}, '{{ $teacher->full_name }}')"
                                    class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                Grant Premium
                            </button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <p class="mt-2">No teachers found</p>
                    </td>
                </tr>
            @endforelse
        </x-table>
    </x-card>
</div>

<!-- Grant Premium Modal -->
<x-modal name="grant-premium-modal" id="grant-premium-modal">
    <form action="{{ route('subscriptions.grant') }}" method="POST">
        @csrf
        <input type="hidden" name="user_id" id="grant-user-id">
        
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                Grant Premium Access
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Granting premium to: <span id="grant-user-name" class="font-medium text-gray-900 dark:text-white"></span>
            </p>
            
            <x-input 
                type="date" 
                name="expires_at" 
                label="Expiration Date"
                :value="now()->addYear()->format('Y-m-d')"
                min="{{ now()->addDay()->format('Y-m-d') }}"
                required
            />
        </div>
        
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-800 flex justify-end space-x-3">
            <x-button type="button" variant="outline" onclick="closeGrantModal()">
                Cancel
            </x-button>
            <x-button type="submit" variant="primary">
                Grant Premium
            </x-button>
        </div>
    </form>
</x-modal>

@push('scripts')
<script>
    function openGrantModal(userId, userName) {
        document.getElementById('grant-user-id').value = userId;
        document.getElementById('grant-user-name').textContent = userName;
        document.getElementById('grant-premium-modal').classList.remove('hidden');
    }
    
    function closeGrantModal() {
        document.getElementById('grant-premium-modal').classList.add('hidden');
    }
</script>
@endpush
@endsection

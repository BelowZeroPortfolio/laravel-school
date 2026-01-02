@extends('layouts.app')

@section('title', 'ID Card Preview - ' . $student->full_name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">ID Card Preview</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Preview ID card for {{ $student->full_name }}
            </p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('id-cards.index') }}">
                <x-button variant="outline">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back
                </x-button>
            </a>
            <form action="{{ route('id-cards.export') }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="student_ids[]" value="{{ $student->id }}">
                <x-button type="submit" variant="primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Download PDF
                </x-button>
            </form>
        </div>
    </div>

    <!-- ID Card Preview -->
    <div class="flex justify-center">
        <x-card class="max-w-md">
            <div class="border-2 border-indigo-600 dark:border-indigo-500 rounded-lg overflow-hidden" style="width: 340px;">
                <!-- Card Header -->
                <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 text-white p-4 text-center">
                    <h3 class="text-lg font-bold uppercase tracking-wide">QR Attendance School</h3>
                    <p class="text-xs opacity-90 mt-1">Student Identification Card</p>
                </div>

                <!-- Card Body -->
                <div class="p-4 bg-white dark:bg-gray-800">
                    <div class="flex">
                        <!-- Photo Section -->
                        <div class="flex-shrink-0">
                            <div class="w-24 h-28 border border-gray-300 dark:border-gray-600 rounded bg-gray-100 dark:bg-gray-700 flex items-center justify-center overflow-hidden">
                                @if($card['photo_path'])
                                    <img src="{{ asset('storage/' . $card['photo_path']) }}" alt="Student Photo" class="w-full h-full object-cover">
                                @else
                                    <div class="text-center text-gray-400 dark:text-gray-500">
                                        <svg class="w-10 h-10 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span class="text-xs">No Photo</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Info Section -->
                        <div class="ml-4 flex-1">
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white">
                                {{ $card['full_name'] }}
                            </h4>

                            @if($card['lrn'])
                            <div class="mt-2">
                                <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">LRN</span>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $card['lrn'] }}</p>
                            </div>
                            @endif

                            <div class="mt-2">
                                <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">Student ID</span>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $card['student_id'] }}</p>
                            </div>

                            <div class="mt-2">
                                <span class="text-xs text-gray-500 dark:text-gray-400 uppercase">Grade & Section</span>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $card['grade_level'] ?? 'N/A' }} - {{ $card['section'] ?? 'N/A' }}
                                </p>
                            </div>
                        </div>

                        <!-- QR Code Section -->
                        <div class="flex-shrink-0 ml-2">
                            <div class="w-20 h-20 border border-gray-300 dark:border-gray-600 rounded bg-white p-1">
                                @if($card['qrcode_path'])
                                    <img src="{{ asset('storage/' . $card['qrcode_path']) }}" alt="QR Code" class="w-full h-full">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400 text-xs text-center">
                                        No QR
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card Footer -->
                <div class="bg-gray-100 dark:bg-gray-700 px-4 py-2 text-center">
                    <p class="text-xs text-gray-600 dark:text-gray-400">
                        S.Y. {{ $card['school_year'] ?? 'N/A' }}
                    </p>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Student Details -->
    <x-card>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Student Details</h2>
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Full Name</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $card['full_name'] }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Student ID</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $card['student_id'] }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">LRN</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $card['lrn'] ?? 'Not assigned' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Grade Level</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $card['grade_level'] ?? 'Not enrolled' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Section</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $card['section'] ?? 'Not enrolled' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">School Year</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $card['school_year'] ?? 'Not enrolled' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">QR Code Path</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono text-xs">{{ $card['qrcode_path'] ?? 'Not generated' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Photo Path</dt>
                <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono text-xs">{{ $card['photo_path'] ?? 'Not uploaded' }}</dd>
            </div>
        </dl>
    </x-card>

    <!-- Actions -->
    <x-card>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Actions</h2>
        <div class="flex flex-wrap gap-3">
            <form action="{{ route('id-cards.generate', $student) }}" method="POST" class="inline">
                @csrf
                <x-button type="submit" variant="primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                    </svg>
                    Regenerate ID Card
                </x-button>
            </form>

            <form action="{{ route('id-cards.qrcode', $student) }}" method="POST" class="inline">
                @csrf
                <x-button type="submit" variant="outline">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                    Regenerate QR Code Only
                </x-button>
            </form>

            <a href="{{ route('students.show', $student) }}">
                <x-button type="button" variant="outline">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    View Student Profile
                </x-button>
            </a>
        </div>
    </x-card>
</div>
@endsection

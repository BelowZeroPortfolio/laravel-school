@extends('layouts.app')

@section('title', "Grade {$class->grade_level} - {$class->section}")

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('classes.index') }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Classes
            </a>
            <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                Grade {{ $class->grade_level }} - {{ $class->section }}
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $class->schoolYear->name ?? 'No school year' }}</p>
        </div>
        <div class="flex items-center space-x-3">
            <x-badge type="{{ $class->is_active ? 'success' : 'default' }}">
                {{ $class->is_active ? 'Active' : 'Inactive' }}
            </x-badge>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('classes.edit', $class) }}">
                <x-button variant="outline">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </x-button>
            </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Class Details -->
            <x-card title="Class Information">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Grade Level</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $class->grade_level }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Section</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $class->section }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">School Year</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $class->schoolYear->name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Assigned Teacher</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $class->teacher->full_name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Capacity</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                            {{ $class->students->count() }} / {{ $class->max_capacity }} students
                        </dd>
                    </div>
                </dl>
            </x-card>

            <!-- Enrolled Students -->
            <x-card title="Enrolled Students" :padding="false">
                <x-slot name="actions">
                    @if(auth()->user()->isAdmin() && $class->students->count() < $class->max_capacity)
                    <x-button variant="outline" size="sm" @click="$dispatch('open-modal', 'enroll-student')">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Enroll Student
                    </x-button>
                    @endif
                </x-slot>

                @if($class->students->count() > 0)
                    <x-table>
                        <x-slot name="head">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">LRN</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Enrolled</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Actions</th>
                            </tr>
                        </x-slot>
                        @foreach($class->students as $student)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 bg-indigo-100 dark:bg-indigo-900/50 rounded-full flex items-center justify-center">
                                            <span class="text-xs font-medium text-indigo-600 dark:text-indigo-400">
                                                {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                                            </span>
                                        </div>
                                        <div class="ml-3">
                                            <a href="{{ route('students.show', $student) }}" class="text-sm font-medium text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400">
                                                {{ $student->last_name }}, {{ $student->first_name }}
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 font-mono">
                                    {{ $student->lrn ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $student->pivot->enrolled_at ? \Carbon\Carbon::parse($student->pivot->enrolled_at)->format('M d, Y') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-badge type="{{ $student->pivot->is_active ? 'success' : 'default' }}" size="sm">
                                        {{ $student->pivot->is_active ? 'Active' : 'Inactive' }}
                                    </x-badge>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                    @if(auth()->user()->isAdmin() && $student->pivot->is_active)
                                    <form method="POST" action="{{ route('classes.unenroll', [$class, $student]) }}" class="inline"
                                          onsubmit="return confirm('Remove this student from the class?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300">
                                            Remove
                                        </button>
                                    </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </x-table>
                @else
                    <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                        <p class="mt-2">No students enrolled</p>
                    </div>
                @endif
            </x-card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Stats -->
            <x-card title="Statistics">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Total Students</span>
                        <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $class->students->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Capacity</span>
                        <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $class->max_capacity }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Available Slots</span>
                        <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ $class->max_capacity - $class->students->count() }}</span>
                    </div>
                    <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ ($class->students->count() / $class->max_capacity) * 100 }}%"></div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 text-center">
                            {{ round(($class->students->count() / $class->max_capacity) * 100) }}% filled
                        </p>
                    </div>
                </div>
            </x-card>

            <!-- Teacher Info -->
            @if($class->teacher)
            <x-card title="Teacher">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-12 w-12 bg-green-100 dark:bg-green-900/50 rounded-full flex items-center justify-center">
                        <span class="text-lg font-medium text-green-600 dark:text-green-400">
                            {{ strtoupper(substr($class->teacher->full_name, 0, 2)) }}
                        </span>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $class->teacher->full_name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $class->teacher->email ?? 'No email' }}</p>
                    </div>
                </div>
            </x-card>
            @endif
        </div>
    </div>
</div>

<!-- Enroll Student Modal -->
<x-modal name="enroll-student" title="Enroll Student">
    <form method="POST" action="{{ route('classes.enroll', $class) }}">
        @csrf
        <div class="space-y-4">
            <x-select name="student_id" label="Select Student" required>
                <option value="">Choose a student...</option>
                {{-- This would need to be populated with available students --}}
            </x-select>

            <x-select name="enrollment_type" label="Enrollment Type" required>
                <option value="regular">Regular</option>
                <option value="transferee">Transferee</option>
                <option value="returnee">Returnee</option>
            </x-select>
        </div>

        <x-slot name="footer">
            <x-button type="button" variant="outline" @click="$dispatch('close-modal', 'enroll-student')">Cancel</x-button>
            <x-button type="submit" variant="primary">Enroll Student</x-button>
        </x-slot>
    </form>
</x-modal>
@endsection

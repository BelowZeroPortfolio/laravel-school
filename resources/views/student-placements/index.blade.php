@extends('layouts.app')

@section('title', 'Student Placements')

@section('content')
<div class="space-y-6" x-data="{ 
    selectedStudents: [],
    showTransferModal: false,
    showPlaceModal: false,
    showBulkPlaceModal: false,
    transferStudent: null,
    placeStudent: null,
    
    toggleStudent(id) {
        const index = this.selectedStudents.indexOf(id);
        if (index === -1) {
            this.selectedStudents.push(id);
        } else {
            this.selectedStudents.splice(index, 1);
        }
    },
    
    selectAll(students) {
        if (this.selectedStudents.length === students.length) {
            this.selectedStudents = [];
        } else {
            this.selectedStudents = [...students];
        }
    },
    
    openTransfer(student) {
        this.transferStudent = student;
        this.showTransferModal = true;
    },
    
    openPlace(student) {
        this.placeStudent = student;
        this.showPlaceModal = true;
    }
}">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Student Placements</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Manage student class enrollments and transfers
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <template x-if="selectedStudents.length > 0">
                <x-button variant="primary" @click="showBulkPlaceModal = true">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Bulk Place (<span x-text="selectedStudents.length"></span>)
                </x-button>
            </template>
        </div>
    </div>

    <!-- Filters -->
    <x-card>
        <form method="GET" action="{{ route('student-placements.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <x-input 
                type="text" 
                name="search" 
                label="Search"
                :value="request('search')"
                placeholder="Name, LRN, or ID..."
            />

            <x-select 
                name="school_year_id" 
                label="School Year"
                placeholder="All School Years"
            >
                @foreach($schoolYears as $year)
                    <option value="{{ $year->id }}" {{ (request('school_year_id') ?? $activeSchoolYear?->id) == $year->id ? 'selected' : '' }}>
                        {{ $year->name }} {{ $year->is_active ? '(Active)' : '' }}
                    </option>
                @endforeach
            </x-select>

            <x-select 
                name="class_id" 
                label="Class"
                placeholder="All Classes"
            >
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                        Grade {{ $class->grade_level }} - {{ $class->section }}
                    </option>
                @endforeach
            </x-select>

            <x-select 
                name="enrollment_status" 
                label="Enrollment Status"
                placeholder="All Status"
            >
                <option value="enrolled" {{ request('enrollment_status') === 'enrolled' ? 'selected' : '' }}>Enrolled</option>
                <option value="transferred" {{ request('enrollment_status') === 'transferred' ? 'selected' : '' }}>Transferred</option>
                <option value="dropped" {{ request('enrollment_status') === 'dropped' ? 'selected' : '' }}>Dropped</option>
                <option value="unassigned" {{ request('enrollment_status') === 'unassigned' ? 'selected' : '' }}>Unassigned</option>
            </x-select>

            <div class="flex items-end space-x-2">
                <x-button type="submit" variant="primary">Filter</x-button>
                <a href="{{ route('student-placements.index') }}">
                    <x-button type="button" variant="outline">Clear</x-button>
                </a>
            </div>
        </form>
    </x-card>

    <!-- Students Table -->
    <x-card :padding="false">
        <x-table>
            <x-slot name="head">
                <tr>
                    <th class="px-6 py-3 text-left">
                        <input type="checkbox" 
                               class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700"
                               @click="selectAll({{ $students->pluck('id')->toJson() }})">
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Student</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">LRN</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Current Class</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Enrollment Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </x-slot>

            @forelse($students as $student)
                @php
                    $activeEnrollment = $student->classes->where('pivot.is_active', true)->first();
                    $enrollmentStatus = $activeEnrollment?->pivot?->enrollment_status ?? 'unassigned';
                @endphp
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="checkbox" 
                               class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700"
                               :checked="selectedStudents.includes({{ $student->id }})"
                               @click="toggleStudent({{ $student->id }})">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 dark:bg-indigo-900/50 rounded-full flex items-center justify-center">
                                <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                    {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                                </span>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $student->last_name }}, {{ $student->first_name }}
                                </div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $student->student_id }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                        {{ $student->lrn ?? '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        @if($activeEnrollment)
                            <div>
                                <span class="font-medium text-gray-900 dark:text-white">
                                    Grade {{ $activeEnrollment->grade_level }} - {{ $activeEnrollment->section }}
                                </span>
                                @if($activeEnrollment->teacher)
                                    <div class="text-xs text-gray-400">
                                        {{ $activeEnrollment->teacher->full_name }}
                                    </div>
                                @endif
                            </div>
                        @else
                            <span class="text-gray-400 italic">Not enrolled</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $statusColors = [
                                'enrolled' => 'success',
                                'transferred' => 'warning',
                                'dropped' => 'danger',
                                'unassigned' => 'default',
                            ];
                        @endphp
                        <x-badge type="{{ $statusColors[$enrollmentStatus] ?? 'default' }}" size="sm">
                            {{ ucfirst($enrollmentStatus) }}
                        </x-badge>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end space-x-2">
                            <a href="{{ route('student-placements.show', $student) }}" 
                               class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                History
                            </a>
                            @if($activeEnrollment)
                                <button type="button"
                                        @click="openTransfer({ id: {{ $student->id }}, name: '{{ $student->last_name }}, {{ $student->first_name }}', currentClassId: {{ $activeEnrollment->id }}, currentClass: 'Grade {{ $activeEnrollment->grade_level }} - {{ $activeEnrollment->section }}' })"
                                        class="text-yellow-600 dark:text-yellow-400 hover:text-yellow-900 dark:hover:text-yellow-300">
                                    Transfer
                                </button>
                            @else
                                <button type="button"
                                        @click="openPlace({ id: {{ $student->id }}, name: '{{ $student->last_name }}, {{ $student->first_name }}' })"
                                        class="text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-300">
                                    Place
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                        </svg>
                        <p class="mt-2">No students found</p>
                    </td>
                </tr>
            @endforelse
        </x-table>

        @if($students->hasPages())
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $students->withQueryString()->links() }}
            </div>
        @endif
    </x-card>

    <!-- Transfer Modal -->
    @include('student-placements.partials.transfer-modal')

    <!-- Place Modal -->
    @include('student-placements.partials.place-modal')

    <!-- Bulk Place Modal -->
    @include('student-placements.partials.bulk-place-modal')
</div>
@endsection

@extends('layouts.app')

@section('title', 'ID Card Generation')

@section('content')
<div class="space-y-6" x-data="idCardManager()">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">ID Card Generation</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Generate and print student ID cards with QR codes
            </p>
        </div>
    </div>

    <!-- Generation Options -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Single Student Generation -->
        <x-card>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Generate for Single Student</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Select a student to generate their ID card with QR code.
            </p>
            
            <form action="" method="POST" x-ref="singleForm">
                @csrf
                <x-select 
                    name="student_id" 
                    label="Select Student"
                    x-model="selectedStudent"
                    @change="updateSingleFormAction()"
                >
                    <option value="">Choose a student...</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}">
                            {{ $student->last_name }}, {{ $student->first_name }} 
                            @if($student->lrn)({{ $student->lrn }})@endif
                        </option>
                    @endforeach
                </x-select>

                <div class="mt-4 flex space-x-2">
                    <x-button 
                        type="submit" 
                        variant="primary"
                        :disabled="true"
                        x-bind:disabled="!selectedStudent"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                        </svg>
                        Generate ID Card
                    </x-button>
                    <a x-bind:href="selectedStudent ? '{{ url('id-cards/students') }}/' + selectedStudent + '/preview' : '#'" 
                       x-bind:class="{ 'pointer-events-none opacity-50': !selectedStudent }">
                        <x-button type="button" variant="outline">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Preview
                        </x-button>
                    </a>
                </div>
            </form>
        </x-card>

        <!-- Batch Generation by Class -->
        <x-card>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Batch Generate by Class</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Generate ID cards for all students in a class at once.
            </p>
            
            <form action="" method="POST" x-ref="batchForm">
                @csrf
                <x-select 
                    name="class_id" 
                    label="Select Class"
                    x-model="selectedClass"
                    @change="updateBatchFormAction()"
                >
                    <option value="">Choose a class...</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">
                            Grade {{ $class->grade_level }} - {{ $class->section }}
                            @if($class->schoolYear) ({{ $class->schoolYear->name }})@endif
                        </option>
                    @endforeach
                </x-select>

                <div class="mt-4 flex space-x-2">
                    <x-button 
                        type="submit" 
                        variant="primary"
                        :disabled="true"
                        x-bind:disabled="!selectedClass"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Generate All
                    </x-button>
                    <button 
                        type="button"
                        @click="exportClassPdf()"
                        x-bind:disabled="!selectedClass"
                        x-bind:class="{ 'opacity-50 cursor-not-allowed': !selectedClass }"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export PDF
                    </button>
                </div>
            </form>
        </x-card>
    </div>

    <!-- Multi-Select Export -->
    <x-card>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Export Selected Students</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
            Select multiple students to export their ID cards as a PDF.
        </p>

        <form action="{{ route('id-cards.export') }}" method="POST" x-ref="exportForm">
            @csrf
            
            <!-- Select All / Deselect All -->
            <div class="mb-4 flex items-center space-x-4">
                <button type="button" @click="selectAll()" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                    Select All
                </button>
                <button type="button" @click="deselectAll()" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">
                    Deselect All
                </button>
                <span class="text-sm text-gray-500 dark:text-gray-400" x-text="'Selected: ' + selectedStudents.length"></span>
            </div>

            <!-- Students Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 max-h-96 overflow-y-auto p-2">
                @foreach($students as $student)
                    <label class="flex items-center p-3 border border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                           :class="{ 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-300 dark:border-indigo-700': selectedStudents.includes({{ $student->id }}) }">
                        <input 
                            type="checkbox" 
                            name="student_ids[]" 
                            value="{{ $student->id }}"
                            x-model.number="selectedStudents"
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 rounded"
                        >
                        <div class="ml-3 flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                {{ $student->last_name }}, {{ $student->first_name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                {{ $student->lrn ?? $student->student_id }}
                            </p>
                        </div>
                    </label>
                @endforeach
            </div>

            <div class="mt-4">
                <x-button 
                    type="submit" 
                    variant="primary"
                    :disabled="true"
                    x-bind:disabled="selectedStudents.length === 0"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export Selected as PDF
                </x-button>
            </div>
        </form>
    </x-card>
</div>

@push('scripts')
<script>
function idCardManager() {
    return {
        selectedStudent: '',
        selectedClass: '',
        selectedStudents: [],

        updateSingleFormAction() {
            if (this.selectedStudent) {
                this.$refs.singleForm.action = '{{ url("id-cards/students") }}/' + this.selectedStudent + '/generate';
            }
        },

        updateBatchFormAction() {
            if (this.selectedClass) {
                this.$refs.batchForm.action = '{{ url("id-cards/classes") }}/' + this.selectedClass + '/batch';
            }
        },

        exportClassPdf() {
            if (this.selectedClass) {
                // Create a form and submit it
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ url("id-cards/classes") }}/' + this.selectedClass + '/export';
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = '{{ csrf_token() }}';
                form.appendChild(csrfInput);
                
                document.body.appendChild(form);
                form.submit();
            }
        },

        selectAll() {
            this.selectedStudents = [
                @foreach($students as $student)
                    {{ $student->id }},
                @endforeach
            ];
        },

        deselectAll() {
            this.selectedStudents = [];
        }
    }
}
</script>
@endpush
@endsection

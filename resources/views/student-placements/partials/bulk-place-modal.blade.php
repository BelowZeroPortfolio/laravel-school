<!-- Bulk Place Students Modal -->
<div x-show="showBulkPlaceModal"
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75 z-40"
     @click="showBulkPlaceModal = false"
     style="display: none;">
</div>

<div x-show="showBulkPlaceModal"
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
             @click.away="showBulkPlaceModal = false">
            
            <!-- Header -->
            <div class="px-4 py-4 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Bulk Place Students
                    </h3>
                    <button @click="showBulkPlaceModal = false" 
                            type="button" 
                            class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Body -->
            <form method="POST" action="{{ route('student-placements.bulk-place') }}">
                @csrf
                <div class="px-4 py-4 sm:px-6 space-y-4">
                    <!-- Selected Students Info -->
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Selected Students</p>
                        <p class="text-lg font-medium text-gray-900 dark:text-white">
                            <span x-text="selectedStudents.length"></span> student(s) selected
                        </p>
                        <p class="text-xs text-gray-400 mt-1">
                            Students already enrolled in the target class will be skipped.
                        </p>
                    </div>

                    <!-- Hidden inputs for selected students -->
                    <template x-for="studentId in selectedStudents" :key="studentId">
                        <input type="hidden" name="student_ids[]" :value="studentId">
                    </template>

                    <!-- Target Class -->
                    <x-select 
                        name="class_id" 
                        label="Assign to Class"
                        placeholder="Select class..."
                        required
                    >
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}">
                                Grade {{ $class->grade_level }} - {{ $class->section }}
                                @if($class->teacher)
                                    ({{ $class->teacher->full_name }})
                                @endif
                            </option>
                        @endforeach
                    </x-select>

                    <!-- Enrollment Type -->
                    <x-select 
                        name="enrollment_type" 
                        label="Enrollment Type"
                        placeholder="Select type..."
                        required
                    >
                        <option value="regular">Regular</option>
                        <option value="transferee">Transferee</option>
                        <option value="returnee">Returnee</option>
                    </x-select>

                    <!-- Reason -->
                    <div>
                        <label for="bulk_place_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Reason (Optional)
                        </label>
                        <textarea name="reason" 
                                  id="bulk_place_reason"
                                  rows="3"
                                  class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-700 focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Enter reason for bulk placement..."></textarea>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-4 py-3 sm:px-6 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-2">
                    <x-button type="button" variant="outline" @click="showBulkPlaceModal = false">
                        Cancel
                    </x-button>
                    <x-button type="submit" variant="primary">
                        Place Students
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</div>

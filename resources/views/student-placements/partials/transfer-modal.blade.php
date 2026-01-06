<!-- Transfer Student Modal -->
<div x-show="showTransferModal"
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75 z-40"
     @click="showTransferModal = false"
     style="display: none;">
</div>

<div x-show="showTransferModal"
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
             @click.away="showTransferModal = false">
            
            <!-- Header -->
            <div class="px-4 py-4 sm:px-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                        Transfer Student
                    </h3>
                    <button @click="showTransferModal = false" 
                            type="button" 
                            class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Body -->
            <form method="POST" action="{{ route('student-placements.transfer') }}">
                @csrf
                <div class="px-4 py-4 sm:px-6 space-y-4">
                    <!-- Student Info -->
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg p-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Student</p>
                        <p class="text-lg font-medium text-gray-900 dark:text-white" x-text="transferStudent?.name"></p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Current Class: <span x-text="transferStudent?.currentClass" class="font-medium text-gray-900 dark:text-white"></span>
                        </p>
                    </div>

                    <input type="hidden" name="student_id" :value="transferStudent?.id">
                    <input type="hidden" name="from_class_id" :value="transferStudent?.currentClassId">

                    <!-- Target Class -->
                    <x-select 
                        name="to_class_id" 
                        label="Transfer To Class"
                        placeholder="Select target class..."
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

                    <!-- Reason -->
                    <div>
                        <label for="transfer_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Reason (Optional)
                        </label>
                        <textarea name="reason" 
                                  id="transfer_reason"
                                  rows="3"
                                  class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm text-gray-900 dark:text-white bg-white dark:bg-gray-700 focus:border-indigo-500 focus:ring-indigo-500"
                                  placeholder="Enter reason for transfer..."></textarea>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-4 py-3 sm:px-6 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-2">
                    <x-button type="button" variant="outline" @click="showTransferModal = false">
                        Cancel
                    </x-button>
                    <x-button type="submit" variant="warning">
                        Transfer Student
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</div>

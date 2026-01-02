@extends('layouts.app')

@section('title', 'Scan QR Code')

@section('content')
<div class="max-w-2xl mx-auto" x-data="qrScanner()">
    <!-- Header -->
    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">QR Code Scanner</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Scan student QR codes to record attendance</p>
    </div>

    <!-- Mode Toggle -->
    <div class="flex justify-center mb-6">
        <div class="inline-flex rounded-lg border border-gray-200 dark:border-gray-700 p-1 bg-gray-100 dark:bg-gray-800">
            <button type="button" 
                    @click="mode = 'arrival'"
                    :class="mode === 'arrival' ? 'bg-white dark:bg-gray-700 shadow-sm' : ''"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-colors">
                <span class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    Arrival
                </span>
            </button>
            <button type="button" 
                    @click="mode = 'departure'"
                    :class="mode === 'departure' ? 'bg-white dark:bg-gray-700 shadow-sm' : ''"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-colors">
                <span class="flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Departure
                </span>
            </button>
        </div>
    </div>

    <!-- Scan Input Card -->
    <x-card>
        <form @submit.prevent="submitScan" class="space-y-4">
            <div class="relative">
                <label for="qr_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    QR Code / LRN / Student ID
                </label>
                <div class="relative">
                    <input type="text" 
                           id="qr_code"
                           x-model="qrCode"
                           x-ref="qrInput"
                           @keydown.enter.prevent="submitScan"
                           class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-3 text-lg text-center font-mono text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:border-indigo-500 focus:ring-indigo-500 bg-white dark:bg-gray-700"
                           placeholder="Scan or enter QR code..."
                           autofocus
                           :disabled="isLoading">
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                        <svg x-show="!isLoading" class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                        <svg x-show="isLoading" class="w-6 h-6 text-indigo-500 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <button type="submit" 
                    x-bind:disabled="isLoading"
                    class="w-full inline-flex items-center justify-center font-medium rounded-lg transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-900 disabled:opacity-50 disabled:cursor-not-allowed bg-indigo-600 hover:bg-indigo-700 text-white focus:ring-indigo-500 dark:bg-indigo-500 dark:hover:bg-indigo-600 px-4 py-2 text-sm">
                <span x-text="isLoading ? 'Processing...' : 'Record Attendance'"></span>
            </button>
        </form>
    </x-card>

    <!-- Result Display -->
    <div x-show="lastResult" x-transition class="mt-6">
        <x-card :padding="false">
            <div class="p-6" :class="lastResult?.success ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20'">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <template x-if="lastResult?.success">
                            <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </template>
                        <template x-if="!lastResult?.success">
                            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </template>
                    </div>
                    <div class="ml-4 flex-1">
                        <h3 class="text-lg font-medium" :class="lastResult?.success ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200'" x-text="lastResult?.message"></h3>
                        <template x-if="lastResult?.data?.student">
                            <div class="mt-2 text-sm" :class="lastResult?.success ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300'">
                                <p><span class="font-medium">Student:</span> <span x-text="lastResult.data.student.full_name"></span></p>
                                <p x-show="lastResult.data.student.lrn"><span class="font-medium">LRN:</span> <span x-text="lastResult.data.student.lrn"></span></p>
                                <p x-show="lastResult.data.status"><span class="font-medium">Status:</span> <span x-text="lastResult.data.status" class="capitalize"></span></p>
                                <p x-show="lastResult.data.check_in_time"><span class="font-medium">Time:</span> <span x-text="lastResult.data.check_in_time"></span></p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Recent Scans -->
    <div x-show="recentScans.length > 0" class="mt-6">
        <x-card title="Recent Scans">
            <div class="space-y-2 max-h-64 overflow-y-auto">
                <template x-for="(scan, index) in recentScans" :key="index">
                    <div class="flex items-center justify-between p-3 rounded-lg" :class="scan.success ? 'bg-green-50 dark:bg-green-900/10' : 'bg-red-50 dark:bg-red-900/10'">
                        <div class="flex items-center">
                            <template x-if="scan.success">
                                <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </template>
                            <template x-if="!scan.success">
                                <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </template>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="scan.data?.student?.full_name || scan.qrCode"></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400" x-text="scan.time"></p>
                            </div>
                        </div>
                        <x-badge x-bind:type="scan.success ? 'success' : 'danger'" size="sm">
                            <span x-text="scan.success ? (scan.data?.status || 'Success') : 'Failed'"></span>
                        </x-badge>
                    </div>
                </template>
            </div>
        </x-card>
    </div>
</div>

@push('scripts')
<script>
function qrScanner() {
    return {
        qrCode: '',
        mode: 'arrival',
        isLoading: false,
        lastResult: null,
        recentScans: [],

        async submitScan() {
            if (!this.qrCode.trim() || this.isLoading) return;

            this.isLoading = true;
            const scannedCode = this.qrCode.trim();

            try {
                const response = await fetch('{{ route("scan.process") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        qr_code: scannedCode,
                        mode: this.mode
                    })
                });

                const data = await response.json();
                this.lastResult = data;

                // Add to recent scans
                this.recentScans.unshift({
                    ...data,
                    qrCode: scannedCode,
                    time: new Date().toLocaleTimeString()
                });

                // Keep only last 10 scans
                if (this.recentScans.length > 10) {
                    this.recentScans.pop();
                }

                // Play sound feedback
                this.playSound(data.success);

            } catch (error) {
                this.lastResult = {
                    success: false,
                    message: 'Network error. Please try again.'
                };
            } finally {
                this.isLoading = false;
                this.qrCode = '';
                this.$refs.qrInput.focus();
            }
        },

        playSound(success) {
            // Simple beep using Web Audio API
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);

                oscillator.frequency.value = success ? 800 : 300;
                oscillator.type = 'sine';
                gainNode.gain.value = 0.1;

                oscillator.start();
                oscillator.stop(audioContext.currentTime + 0.15);
            } catch (e) {
                // Audio not supported
            }
        }
    }
}
</script>
@endpush
@endsection

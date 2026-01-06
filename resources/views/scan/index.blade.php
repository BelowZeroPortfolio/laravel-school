@extends('layouts.app')

@section('title', 'Scan QR Code')

@section('content')
<div x-data="qrScanner()" class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">QR Code Scanner</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Scan student QR codes to record attendance</p>
        </div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span x-text="currentTime"></span>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Left Column: Scanner -->
        <div class="space-y-6">
            <!-- Mode Toggle -->
            <x-card :padding="false">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex rounded-lg p-1 bg-gray-100 dark:bg-gray-800">
                        <button type="button" 
                                @click="mode = 'arrival'"
                                :class="mode === 'arrival' 
                                    ? 'bg-green-500 text-white shadow-sm' 
                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                                class="flex-1 flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium rounded-md transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            <span>Arrival (Check In)</span>
                        </button>
                        <button type="button" 
                                @click="mode = 'departure'"
                                :class="mode === 'departure' 
                                    ? 'bg-blue-500 text-white shadow-sm' 
                                    : 'text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                                class="flex-1 flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium rounded-md transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span>Departure (Check Out)</span>
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Current Mode Indicator -->
                    <div class="mb-4 p-3 rounded-lg text-center"
                         :class="mode === 'arrival' ? 'bg-green-50 dark:bg-green-900/20' : 'bg-blue-50 dark:bg-blue-900/20'">
                        <p class="text-sm font-medium"
                           :class="mode === 'arrival' ? 'text-green-700 dark:text-green-300' : 'text-blue-700 dark:text-blue-300'">
                            <span x-text="mode === 'arrival' ? '📥 Recording Check-In' : '📤 Recording Check-Out'"></span>
                        </p>
                    </div>

                    <!-- Scan Input -->
                    <form @submit.prevent="submitScan">
                        <div class="relative">
                            <input type="text" 
                                   id="qr_code"
                                   x-model="qrCode"
                                   x-ref="qrInput"
                                   @keydown.enter.prevent="submitScan"
                                   class="block w-full rounded-xl border-2 px-4 py-4 text-xl text-center font-mono transition-colors focus:outline-none"
                                   :class="mode === 'arrival' 
                                       ? 'border-green-300 dark:border-green-700 focus:border-green-500 focus:ring-green-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white' 
                                       : 'border-blue-300 dark:border-blue-700 focus:border-blue-500 focus:ring-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white'"
                                   placeholder="Scan QR code here..."
                                   autofocus
                                   :disabled="isLoading">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                                <svg x-show="!isLoading" class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                </svg>
                                <svg x-show="isLoading" class="w-6 h-6 animate-spin" :class="mode === 'arrival' ? 'text-green-500' : 'text-blue-500'" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>

                        <button type="submit" 
                                :disabled="isLoading || !qrCode.trim()"
                                class="mt-4 w-full py-3 px-4 rounded-xl font-medium text-white transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                :class="mode === 'arrival' 
                                    ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500' 
                                    : 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500'">
                            <span x-text="isLoading ? 'Processing...' : (mode === 'arrival' ? 'Record Check-In' : 'Record Check-Out')"></span>
                        </button>
                    </form>
                </div>
            </x-card>

            <!-- Last Scan Result -->
            <div x-show="lastResult" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0">
                <x-card :padding="false">
                    <div class="p-6" :class="lastResult?.success 
                        ? 'bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20' 
                        : 'bg-gradient-to-r from-red-50 to-rose-50 dark:from-red-900/20 dark:to-rose-900/20'">
                        <div class="flex items-start gap-4">
                            <div class="flex-shrink-0">
                                <div class="w-14 h-14 rounded-full flex items-center justify-center"
                                     :class="lastResult?.success ? 'bg-green-100 dark:bg-green-800' : 'bg-red-100 dark:bg-red-800'">
                                    <template x-if="lastResult?.success">
                                        <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </template>
                                    <template x-if="!lastResult?.success">
                                        <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </template>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-lg font-semibold" 
                                    :class="lastResult?.success ? 'text-green-800 dark:text-green-200' : 'text-red-800 dark:text-red-200'" 
                                    x-text="lastResult?.message"></h3>
                                <template x-if="lastResult?.data?.student">
                                    <div class="mt-2 space-y-1">
                                        <p class="text-base font-medium text-gray-900 dark:text-white" x-text="lastResult.data.student.full_name"></p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            <span x-show="lastResult.data.student.lrn">LRN: <span x-text="lastResult.data.student.lrn"></span></span>
                                        </p>
                                        <div class="flex items-center gap-3 mt-2">
                                            <span x-show="lastResult.data.status" 
                                                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                                  :class="lastResult.data.status === 'present' ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100'"
                                                  x-text="lastResult.data.status === 'present' ? '✓ On Time' : '⏰ Late'"></span>
                                            <span x-show="lastResult.data.check_in_time" class="text-sm text-gray-500 dark:text-gray-400" x-text="lastResult.data.check_in_time"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </x-card>
            </div>
        </div>

        <!-- Right Column: Recent Scans -->
        <div>
            <x-card title="Recent Scans" :padding="false">
                <x-slot name="actions">
                    <button @click="recentScans = []" x-show="recentScans.length > 0" 
                            class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        Clear All
                    </button>
                </x-slot>

                <div class="divide-y divide-gray-200 dark:divide-gray-700 max-h-[500px] overflow-y-auto">
                    <template x-if="recentScans.length === 0">
                        <div class="p-8 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No scans yet</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Scanned students will appear here</p>
                        </div>
                    </template>

                    <template x-for="(scan, index) in recentScans" :key="scan.id">
                        <div class="p-4 transition-all duration-500"
                             :class="{ 
                                 'opacity-100': scan.opacity === 1,
                                 'opacity-60': scan.opacity < 1 && scan.opacity >= 0.5,
                                 'opacity-30': scan.opacity < 0.5
                             }"
                             :style="'opacity: ' + scan.opacity">
                            <div class="flex items-center gap-3">
                                <!-- Status Icon -->
                                <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                                     :class="scan.success 
                                         ? (scan.mode === 'arrival' ? 'bg-green-100 dark:bg-green-900/50' : 'bg-blue-100 dark:bg-blue-900/50')
                                         : 'bg-red-100 dark:bg-red-900/50'">
                                    <template x-if="scan.success && scan.mode === 'arrival'">
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14" />
                                        </svg>
                                    </template>
                                    <template x-if="scan.success && scan.mode === 'departure'">
                                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7" />
                                        </svg>
                                    </template>
                                    <template x-if="!scan.success">
                                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </template>
                                </div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate" 
                                       x-text="scan.data?.student?.full_name || scan.qrCode"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        <span x-text="scan.time"></span>
                                        <span class="mx-1">•</span>
                                        <span x-text="scan.mode === 'arrival' ? 'Check In' : 'Check Out'"></span>
                                    </p>
                                </div>

                                <!-- Badge -->
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                          :class="scan.success 
                                              ? (scan.data?.status === 'late' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300' : 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300')
                                              : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300'">
                                        <span x-text="scan.success ? (scan.data?.status || (scan.mode === 'departure' ? 'Out' : 'OK')) : 'Failed'"></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </x-card>

            <!-- Today's Stats -->
            <div class="mt-6 grid grid-cols-2 gap-4">
                <x-card class="text-center">
                    <div class="text-3xl font-bold text-green-600 dark:text-green-400" x-text="todayStats.arrivals">0</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Check-Ins Today</div>
                </x-card>
                <x-card class="text-center">
                    <div class="text-3xl font-bold text-blue-600 dark:text-blue-400" x-text="todayStats.departures">0</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Check-Outs Today</div>
                </x-card>
            </div>
        </div>
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
        currentTime: '',
        scanIdCounter: 0,
        todayStats: {
            arrivals: 0,
            departures: 0
        },

        init() {
            this.updateTime();
            setInterval(() => this.updateTime(), 1000);
            setInterval(() => this.decayScans(), 5000);
            this.$refs.qrInput.focus();
        },

        updateTime() {
            this.currentTime = new Date().toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit',
                second: '2-digit'
            });
        },

        decayScans() {
            this.recentScans = this.recentScans.map(scan => {
                const age = (Date.now() - scan.timestamp) / 1000;
                if (age > 300) return { ...scan, opacity: 0.2 };
                if (age > 180) return { ...scan, opacity: 0.4 };
                if (age > 60) return { ...scan, opacity: 0.7 };
                return { ...scan, opacity: 1 };
            }).filter(scan => {
                const age = (Date.now() - scan.timestamp) / 1000;
                return age < 600; // Remove after 10 minutes
            });
        },

        async submitScan() {
            if (!this.qrCode.trim() || this.isLoading) return;

            this.isLoading = true;
            const scannedCode = this.qrCode.trim();
            const scanMode = this.mode;

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
                        mode: scanMode
                    })
                });

                const data = await response.json();
                this.lastResult = data;

                // Update stats
                if (data.success) {
                    if (scanMode === 'arrival') {
                        this.todayStats.arrivals++;
                    } else {
                        this.todayStats.departures++;
                    }
                }

                // Add to recent scans with unique ID
                this.recentScans.unshift({
                    id: ++this.scanIdCounter,
                    ...data,
                    qrCode: scannedCode,
                    mode: scanMode,
                    time: new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }),
                    timestamp: Date.now(),
                    opacity: 1
                });

                // Keep only last 20 scans
                if (this.recentScans.length > 20) {
                    this.recentScans = this.recentScans.slice(0, 20);
                }

                this.playSound(data.success);

            } catch (error) {
                this.lastResult = {
                    success: false,
                    message: 'Network error. Please try again.'
                };
                this.playSound(false);
            } finally {
                this.isLoading = false;
                this.qrCode = '';
                this.$nextTick(() => this.$refs.qrInput.focus());
            }
        },

        playSound(success) {
            try {
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);

                oscillator.frequency.value = success ? 880 : 220;
                oscillator.type = success ? 'sine' : 'square';
                gainNode.gain.value = 0.1;

                oscillator.start();
                oscillator.stop(audioContext.currentTime + (success ? 0.1 : 0.3));
            } catch (e) {}
        }
    }
}
</script>
@endpush
@endsection

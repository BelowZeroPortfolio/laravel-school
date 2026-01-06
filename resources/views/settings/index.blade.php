@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">System Settings</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Configure system-wide settings for the QR Attendance System
            </p>
        </div>
    </div>

    <!-- Error Messages (success handled by layout) -->
    @if($errors->any())
        <x-alert type="error">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    <!-- Settings Form -->
    <form method="POST" action="{{ route('settings.update') }}" id="settingsForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <!-- School Settings -->
        <x-card title="School Information" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-input 
                        name="settings[school_name][value]" 
                        label="School Name" 
                        :value="$settingsGrouped['school']['school_name'] ?? ''"
                        data-original="{{ $settingsGrouped['school']['school_name'] ?? '' }}"
                        placeholder="Enter school name"
                    />
                    <input type="hidden" name="settings[school_name][key]" value="school_name">
                    <input type="hidden" name="settings[school_name][group]" value="school">
                </div>
                <div>
                    <x-input 
                        name="settings[school_address][value]" 
                        label="School Address" 
                        :value="$settingsGrouped['school']['school_address'] ?? ''"
                        data-original="{{ $settingsGrouped['school']['school_address'] ?? '' }}"
                        placeholder="Enter school address"
                    />
                    <input type="hidden" name="settings[school_address][key]" value="school_address">
                    <input type="hidden" name="settings[school_address][group]" value="school">
                </div>
                <div>
                    <x-input 
                        name="settings[school_contact][value]" 
                        label="Contact Number" 
                        :value="$settingsGrouped['school']['school_contact'] ?? ''"
                        data-original="{{ $settingsGrouped['school']['school_contact'] ?? '' }}"
                        placeholder="Enter contact number"
                    />
                    <input type="hidden" name="settings[school_contact][key]" value="school_contact">
                    <input type="hidden" name="settings[school_contact][group]" value="school">
                </div>
                <div>
                    <x-input 
                        name="settings[school_email][value]" 
                        label="Email Address" 
                        type="email"
                        :value="$settingsGrouped['school']['school_email'] ?? ''"
                        data-original="{{ $settingsGrouped['school']['school_email'] ?? '' }}"
                        placeholder="Enter email address"
                    />
                    <input type="hidden" name="settings[school_email][key]" value="school_email">
                    <input type="hidden" name="settings[school_email][group]" value="school">
                </div>
                
                <!-- School Logo Upload -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        School Logo
                    </label>
                    <div class="flex items-start gap-6">
                        <!-- Preview Area -->
                        <div id="logoPreviewContainer" class="flex-shrink-0">
                            @if(!empty($settingsGrouped['school']['school_logo_path']))
                                <div id="logoPreview" class="relative group">
                                    <img src="{{ asset('storage/' . $settingsGrouped['school']['school_logo_path']) }}" 
                                         alt="School Logo" 
                                         class="h-24 w-24 object-contain rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
                                    <button type="button" 
                                            onclick="clearLogo()" 
                                            class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full p-1 shadow-lg opacity-0 group-hover:opacity-100 transition-opacity"
                                            title="Remove logo">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            @else
                                <div id="logoPreview" class="h-24 w-24 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center bg-gray-50 dark:bg-gray-800">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Upload Controls -->
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <label class="cursor-pointer inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                    </svg>
                                    Choose Image
                                    <input type="file" 
                                           name="school_logo" 
                                           id="schoolLogoInput" 
                                           accept="image/png,image/jpeg,image/gif,image/webp"
                                           class="hidden"
                                           onchange="previewLogo(this)">
                                </label>
                                <button type="button" 
                                        onclick="clearLogo()" 
                                        id="clearLogoBtn"
                                        class="inline-flex items-center px-4 py-2 border border-red-300 dark:border-red-600 rounded-lg text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors {{ empty($settingsGrouped['school']['school_logo_path']) ? 'hidden' : '' }}">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Clear
                                </button>
                            </div>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                PNG, JPG, GIF or WebP. Max 20MB. Used on ID cards and reports.
                            </p>
                            <input type="hidden" name="settings[school_logo_path][value]" id="schoolLogoPathValue" value="{{ $settingsGrouped['school']['school_logo_path'] ?? '' }}">
                            <input type="hidden" name="settings[school_logo_path][key]" value="school_logo_path">
                            <input type="hidden" name="settings[school_logo_path][group]" value="school">
                            <input type="hidden" name="clear_logo" id="clearLogoFlag" value="0">
                        </div>
                    </div>
                </div>
            </div>
        </x-card>

        <!-- SMS Settings -->
        <x-card title="SMS Notifications" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        SMS Notifications
                    </label>
                    <select 
                        name="settings[sms_enabled][value]" 
                        data-original="{{ ($settingsGrouped['sms']['sms_enabled'] ?? false) ? '1' : '0' }}"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="1" {{ ($settingsGrouped['sms']['sms_enabled'] ?? false) ? 'selected' : '' }}>Enabled</option>
                        <option value="0" {{ !($settingsGrouped['sms']['sms_enabled'] ?? false) ? 'selected' : '' }}>Disabled</option>
                    </select>
                    <input type="hidden" name="settings[sms_enabled][key]" value="sms_enabled">
                    <input type="hidden" name="settings[sms_enabled][group]" value="sms">
                </div>
                <div>
                    <x-input 
                        name="settings[sms_provider][value]" 
                        label="SMS Provider" 
                        :value="$settingsGrouped['sms']['sms_provider'] ?? ''"
                        data-original="{{ $settingsGrouped['sms']['sms_provider'] ?? '' }}"
                        placeholder="e.g., Twilio, Nexmo"
                    />
                    <input type="hidden" name="settings[sms_provider][key]" value="sms_provider">
                    <input type="hidden" name="settings[sms_provider][group]" value="sms">
                </div>
                <div>
                    <x-input 
                        name="settings[sms_api_key][value]" 
                        label="SMS API Key" 
                        type="password"
                        :value="$settingsGrouped['sms']['sms_api_key'] ?? ''"
                        data-original="{{ $settingsGrouped['sms']['sms_api_key'] ?? '' }}"
                        placeholder="Enter API key"
                    />
                    <input type="hidden" name="settings[sms_api_key][key]" value="sms_api_key">
                    <input type="hidden" name="settings[sms_api_key][group]" value="sms">
                </div>
                <div>
                    <x-input 
                        name="settings[sms_sender_id][value]" 
                        label="SMS Sender ID" 
                        :value="$settingsGrouped['sms']['sms_sender_id'] ?? ''"
                        data-original="{{ $settingsGrouped['sms']['sms_sender_id'] ?? '' }}"
                        placeholder="Enter sender ID"
                    />
                    <input type="hidden" name="settings[sms_sender_id][key]" value="sms_sender_id">
                    <input type="hidden" name="settings[sms_sender_id][group]" value="sms">
                </div>
            </div>
        </x-card>

        <!-- Attendance Settings -->
        <x-card title="Attendance Settings" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Default Scan Mode
                    </label>
                    <select 
                        name="settings[default_scan_mode][value]" 
                        data-original="{{ $settingsGrouped['attendance']['default_scan_mode'] ?? 'arrival' }}"
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="arrival" {{ ($settingsGrouped['attendance']['default_scan_mode'] ?? 'arrival') === 'arrival' ? 'selected' : '' }}>Arrival</option>
                        <option value="dismissal" {{ ($settingsGrouped['attendance']['default_scan_mode'] ?? '') === 'dismissal' ? 'selected' : '' }}>Dismissal</option>
                    </select>
                    <input type="hidden" name="settings[default_scan_mode][key]" value="default_scan_mode">
                    <input type="hidden" name="settings[default_scan_mode][group]" value="attendance">
                </div>
                <div>
                    <x-input 
                        name="settings[duplicate_scan_window][value]" 
                        label="Duplicate Scan Prevention (minutes)" 
                        type="number"
                        min="0"
                        max="60"
                        :value="$settingsGrouped['attendance']['duplicate_scan_window'] ?? '5'"
                        data-original="{{ $settingsGrouped['attendance']['duplicate_scan_window'] ?? '5' }}"
                        placeholder="Minutes to prevent duplicate scans"
                    />
                    <input type="hidden" name="settings[duplicate_scan_window][key]" value="duplicate_scan_window">
                    <input type="hidden" name="settings[duplicate_scan_window][group]" value="attendance">
                </div>
            </div>
        </x-card>

        <!-- Submit Button -->
        <div class="flex justify-end">
            <x-button type="submit" variant="primary">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Save Settings
            </x-button>
        </div>
    </form>

    <!-- Recent Changes -->
    @if($logs->count() > 0)
    <x-card title="Recent Changes" :padding="false">
        <x-table>
            <x-slot name="head">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Setting</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Old Value</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">New Value</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Changed By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                </tr>
            </x-slot>

            @foreach($logs as $log)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                        {{ $log->setting_key }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ is_array($log->old_value) ? json_encode($log->old_value) : ($log->old_value ?? '-') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ is_array($log->new_value) ? json_encode($log->new_value) : ($log->new_value ?? '-') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ $log->changedBy->full_name ?? 'System' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ $log->created_at->format('M d, Y h:i A') }}
                    </td>
                </tr>
            @endforeach
        </x-table>
    </x-card>
    @endif
</div>

@push('scripts')
<script>
    function previewLogo(input) {
        const preview = document.getElementById('logoPreview');
        const clearBtn = document.getElementById('clearLogoBtn');
        const clearFlag = document.getElementById('clearLogoFlag');
        
        if (input.files && input.files[0]) {
            const file = input.files[0];
            
            // Validate file size (20MB max)
            if (file.size > 20 * 1024 * 1024) {
                alert('File size must be less than 20MB');
                input.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `
                    <div class="relative group">
                        <img src="${e.target.result}" 
                             alt="Logo Preview" 
                             class="h-24 w-24 object-contain rounded-lg border-2 border-indigo-500 bg-white dark:bg-gray-800">
                        <span class="absolute bottom-0 left-0 right-0 bg-indigo-500 text-white text-xs text-center py-0.5 rounded-b-lg">New</span>
                    </div>
                `;
                clearBtn.classList.remove('hidden');
                clearFlag.value = '0';
            };
            reader.readAsDataURL(file);
        }
    }
    
    function clearLogo() {
        const preview = document.getElementById('logoPreview');
        const clearBtn = document.getElementById('clearLogoBtn');
        const fileInput = document.getElementById('schoolLogoInput');
        const pathValue = document.getElementById('schoolLogoPathValue');
        const clearFlag = document.getElementById('clearLogoFlag');
        
        // Reset file input
        fileInput.value = '';
        
        // Clear the path value and set clear flag
        pathValue.value = '';
        clearFlag.value = '1';
        
        // Show empty placeholder
        preview.innerHTML = `
            <div class="h-24 w-24 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center bg-gray-50 dark:bg-gray-800">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        `;
        
        // Hide clear button
        clearBtn.classList.add('hidden');
    }
</script>
@endpush
@endsection

<?php

namespace App\Http\Controllers;

use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected SettingsService $settingsService
    ) {}

    /**
     * Display all settings.
     * (Requirement 21.1)
     */
    public function index(): View
    {
        // Get all settings grouped by their group name
        $settingsGrouped = $this->settingsService->getAllGrouped();
        
        // Get recent change logs
        $logs = $this->settingsService->getChangeLogs(null, 20);

        return view('settings.index', [
            'settingsGrouped' => $settingsGrouped,
            'logs' => $logs,
        ]);
    }

    /**
     * Update settings.
     * (Requirement 21.2)
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'settings' => ['required', 'array'],
            'settings.*.key' => ['required', 'string', 'max:255'],
            'settings.*.value' => ['nullable'],
            'settings.*.group' => ['required', 'string', 'max:50'],
            'school_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,gif,webp', 'max:20480'],
            'clear_logo' => ['nullable', 'in:0,1'],
        ]);

        $userId = $request->user()->id;
        $updatedCount = 0;

        // Handle logo upload
        if ($request->hasFile('school_logo')) {
            $file = $request->file('school_logo');
            
            // Delete old logo if exists
            $oldLogoPath = $this->settingsService->get('school_logo_path');
            if ($oldLogoPath && Storage::disk('public')->exists($oldLogoPath)) {
                Storage::disk('public')->delete($oldLogoPath);
            }
            
            // Store new logo
            $path = $file->store('logos', 'public');
            
            $this->settingsService->set('school_logo_path', $path, $userId, 'school');
            $updatedCount++;
            
            // Remove from settings array to avoid double processing
            unset($validated['settings']['school_logo_path']);
        } elseif ($request->input('clear_logo') === '1') {
            // Clear logo was requested
            $oldLogoPath = $this->settingsService->get('school_logo_path');
            if ($oldLogoPath) {
                if (Storage::disk('public')->exists($oldLogoPath)) {
                    Storage::disk('public')->delete($oldLogoPath);
                }
                $this->settingsService->set('school_logo_path', '', $userId, 'school');
                $updatedCount++;
            }
            unset($validated['settings']['school_logo_path']);
        }

        foreach ($validated['settings'] as $key => $setting) {
            // Skip logo path if already handled
            if ($setting['key'] === 'school_logo_path') {
                continue;
            }
            
            // Only update if value has changed
            $currentValue = $this->settingsService->get($setting['key']);
            $newValue = $setting['value'];
            
            // Normalize for comparison (handle null vs empty string)
            $currentNormalized = $currentValue === null ? '' : (string) $currentValue;
            $newNormalized = $newValue === null ? '' : (string) $newValue;
            
            if ($currentNormalized !== $newNormalized) {
                $this->settingsService->set(
                    $setting['key'],
                    $setting['value'],
                    $userId,
                    $setting['group']
                );
                $updatedCount++;
            }
        }

        $message = $updatedCount > 0 
            ? "Settings updated successfully. ({$updatedCount} setting(s) changed)"
            : 'No changes were made.';

        return redirect()->route('settings.index')
            ->with('success', $message);
    }
}

<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\SettingLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    /**
     * Cache key prefix for settings.
     */
    private const CACHE_PREFIX = 'settings:';

    /**
     * Cache TTL in seconds (1 hour).
     */
    private const CACHE_TTL = 3600;

    /**
     * Get a setting value by key with caching.
     * (Requirement 21.2)
     *
     * @param string $key The setting key
     * @param mixed $default Default value if setting doesn't exist
     * @return mixed The setting value or default
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = self::CACHE_PREFIX . $key;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($key, $default) {
            $setting = Setting::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            return $setting->value;
        });
    }

    /**
     * Set a setting value with caching and audit logging.
     * (Requirement 21.2, 21.5)
     *
     * @param string $key The setting key
     * @param mixed $value The value to set
     * @param int $userId The ID of the user making the change
     * @param string $group The setting group (default: 'general')
     * @return bool True if successful
     */
    public function set(string $key, mixed $value, int $userId, string $group = 'general'): bool
    {
        $setting = Setting::where('key', $key)->first();
        $oldValue = $setting?->value;

        if ($setting) {
            // Update existing setting
            $setting->update([
                'value' => $value,
                'group' => $group,
            ]);
        } else {
            // Create new setting
            $setting = Setting::create([
                'key' => $key,
                'value' => $value,
                'group' => $group,
            ]);
        }

        // Log the change (Requirement 21.5)
        $this->logChange($key, $oldValue, $value, $userId);

        // Clear cache for this key
        $this->clearCache($key);

        return true;
    }


    /**
     * Get all settings.
     * (Requirement 21.1)
     *
     * @return array All settings as key-value pairs
     */
    public function getAll(): array
    {
        $settings = Setting::all();

        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->value;
        }

        return $result;
    }

    /**
     * Get all settings in a specific group.
     * (Requirement 21.1, 21.3, 21.4)
     *
     * @param string $group The group name (e.g., 'school', 'sms', 'attendance')
     * @return array Settings in the group as key-value pairs
     */
    public function getGroup(string $group): array
    {
        $settings = Setting::group($group)->get();

        $result = [];
        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->value;
        }

        return $result;
    }

    /**
     * Get all settings grouped by their group name.
     *
     * @return array Settings organized by group
     */
    public function getAllGrouped(): array
    {
        $settings = Setting::all();

        $result = [];
        foreach ($settings as $setting) {
            if (!isset($result[$setting->group])) {
                $result[$setting->group] = [];
            }
            $result[$setting->group][$setting->key] = $setting->value;
        }

        return $result;
    }

    /**
     * Log a setting change.
     * (Requirement 21.5)
     *
     * @param string $key The setting key
     * @param mixed $oldValue The previous value
     * @param mixed $newValue The new value
     * @param int $userId The ID of the user making the change
     * @return void
     */
    private function logChange(string $key, mixed $oldValue, mixed $newValue, int $userId): void
    {
        SettingLog::create([
            'setting_key' => $key,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'changed_by' => $userId,
            'created_at' => now(),
        ]);
    }

    /**
     * Clear cache for a specific setting key.
     *
     * @param string $key The setting key
     * @return void
     */
    private function clearCache(string $key): void
    {
        Cache::forget(self::CACHE_PREFIX . $key);
    }

    /**
     * Clear all settings cache.
     *
     * @return void
     */
    public function clearAllCache(): void
    {
        // Get all settings and clear their cache
        $settings = Setting::all();
        foreach ($settings as $setting) {
            $this->clearCache($setting->key);
        }
    }

    /**
     * Get change logs for a specific setting or all settings.
     *
     * @param string|null $key The setting key (null for all)
     * @param int $limit Maximum number of logs to return
     * @return Collection Collection of setting logs
     */
    public function getChangeLogs(?string $key = null, int $limit = 50): Collection
    {
        $query = SettingLog::with('changedBy')
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($key !== null) {
            $query->where('setting_key', $key);
        }

        return $query->get();
    }

    /**
     * Check if a setting exists.
     *
     * @param string $key The setting key
     * @return bool True if the setting exists
     */
    public function exists(string $key): bool
    {
        return Setting::where('key', $key)->exists();
    }

    /**
     * Delete a setting.
     *
     * @param string $key The setting key
     * @param int $userId The ID of the user making the change
     * @return bool True if successful
     */
    public function delete(string $key, int $userId): bool
    {
        $setting = Setting::where('key', $key)->first();

        if (!$setting) {
            return false;
        }

        // Log the deletion
        $this->logChange($key, $setting->value, null, $userId);

        // Clear cache
        $this->clearCache($key);

        // Delete the setting
        $setting->delete();

        return true;
    }
}

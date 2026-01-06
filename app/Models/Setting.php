<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Setting extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'value' => 'json',
    ];

    /**
     * Get the logs for this setting.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(SettingLog::class, 'setting_key', 'key');
    }

    /**
     * Scope a query to filter by group.
     */
    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }
}

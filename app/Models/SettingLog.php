<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SettingLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'settings_logs';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'setting_key',
        'old_value',
        'new_value',
        'changed_by',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'old_value' => 'json',
        'new_value' => 'json',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user who made the change.
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}

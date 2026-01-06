<?php

namespace App\Models;

use App\Models\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimeSchedule extends Model
{
    use HasFactory, BelongsToSchool;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'name',
        'time_in',
        'time_out',
        'late_threshold_minutes',
        'is_active',
        'effective_date',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'time_in' => 'datetime:H:i:s',
            'time_out' => 'datetime:H:i:s',
            'late_threshold_minutes' => 'integer',
            'is_active' => 'boolean',
            'effective_date' => 'date',
        ];
    }

    /**
     * Get the user who created this schedule.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the logs for this schedule.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(TimeScheduleLog::class, 'schedule_id');
    }

    /**
     * Get the teacher attendance records using this time rule.
     */
    public function teacherAttendances(): HasMany
    {
        return $this->hasMany(TeacherAttendance::class, 'time_rule_id');
    }

    /**
     * Scope a query to only include active schedules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

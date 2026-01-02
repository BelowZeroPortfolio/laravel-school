<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherAttendance extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'teacher_attendance';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'teacher_id',
        'school_year_id',
        'attendance_date',
        'time_in',
        'time_out',
        'first_student_scan',
        'attendance_status',
        'late_status',
        'time_rule_id',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'time_in' => 'datetime',
            'time_out' => 'datetime',
            'first_student_scan' => 'datetime',
        ];
    }

    /**
     * Get the teacher this attendance belongs to.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the school year this attendance belongs to.
     */
    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class, 'school_year_id');
    }

    /**
     * Get the time rule locked for this attendance.
     */
    public function timeRule(): BelongsTo
    {
        return $this->belongsTo(TimeSchedule::class, 'time_rule_id');
    }

    /**
     * Scope a query to only include today's attendance.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('attendance_date', today());
    }

    /**
     * Scope a query to only include pending attendance.
     */
    public function scopePending($query)
    {
        return $query->where('attendance_status', 'pending');
    }

    /**
     * Scope a query to only include late attendance.
     */
    public function scopeLate($query)
    {
        return $query->where('attendance_status', 'late');
    }

    /**
     * Scope a query to filter by school year.
     */
    public function scopeForSchoolYear($query, $schoolYearId)
    {
        return $query->where('school_year_id', $schoolYearId);
    }
}

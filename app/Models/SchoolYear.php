<?php

namespace App\Models;

use App\Models\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolYear extends Model
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
        'is_active',
        'is_locked',
        'start_date',
        'end_date',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_locked' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * Get the classes for this school year.
     */
    public function classes(): HasMany
    {
        return $this->hasMany(ClassRoom::class, 'school_year_id');
    }

    /**
     * Get the student attendance records for this school year.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'school_year_id');
    }

    /**
     * Get the teacher attendance records for this school year.
     */
    public function teacherAttendances(): HasMany
    {
        return $this->hasMany(TeacherAttendance::class, 'school_year_id');
    }

    /**
     * Scope a query to only include active school years.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

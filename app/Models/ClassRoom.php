<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ClassRoom extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'classes';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'grade_level',
        'section',
        'teacher_id',
        'school_year_id',
        'is_active',
        'max_capacity',
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
            'max_capacity' => 'integer',
        ];
    }

    /**
     * Get the teacher assigned to this class.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the school year this class belongs to.
     */
    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class, 'school_year_id');
    }

    /**
     * Get the students enrolled in this class.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_classes', 'class_id', 'student_id')
            ->withPivot([
                'enrolled_at',
                'enrolled_by',
                'is_active',
                'enrollment_type',
                'enrollment_status',
                'status_changed_at',
                'status_changed_by',
                'status_reason',
            ])
            ->withTimestamps();
    }

    /**
     * Get the class display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->grade_level} - {$this->section}";
    }

    /**
     * Scope a query to only include active classes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Check if the class has reached maximum capacity.
     */
    public function isAtCapacity(): bool
    {
        return $this->students()->wherePivot('is_active', true)->count() >= $this->max_capacity;
    }
}

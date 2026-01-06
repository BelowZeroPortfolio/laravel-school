<?php

namespace App\Models;

use App\Models\Traits\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory, BelongsToSchool;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'student_id',
        'lrn',
        'first_name',
        'last_name',
        'qrcode_path',
        'photo_path',
        'parent_name',
        'parent_phone',
        'parent_email',
        'address',
        'is_active',
        'sms_enabled',
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
            'sms_enabled' => 'boolean',
        ];
    }

    /**
     * Get the attendance records for this student.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'student_id');
    }

    /**
     * Get the classes this student is enrolled in.
     */
    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(ClassRoom::class, 'student_classes', 'student_id', 'class_id')
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
     * Get the student's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Scope a query to only include active students.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

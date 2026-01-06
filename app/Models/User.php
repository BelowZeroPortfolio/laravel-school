<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Cached teacher class IDs to prevent N+1 queries in policies and controllers.
     */
    protected ?Collection $cachedTeacherClassIds = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'school_id',
        'username',
        'password',
        'role',
        'full_name',
        'email',
        'is_active',
        'is_premium',
        'premium_expires_at',
        'last_login',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'is_premium' => 'boolean',
            'premium_expires_at' => 'date',
            'last_login' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the classes taught by this user.
     */
    public function classes(): HasMany
    {
        return $this->hasMany(ClassRoom::class, 'teacher_id');
    }

    /**
     * Get the teacher attendance records for this user.
     */
    public function teacherAttendances(): HasMany
    {
        return $this->hasMany(TeacherAttendance::class, 'teacher_id');
    }

    /**
     * Get the time schedules created by this user.
     */
    public function createdSchedules(): HasMany
    {
        return $this->hasMany(TimeSchedule::class, 'created_by');
    }

    /**
     * Get the time schedule logs changed by this user.
     */
    public function scheduleChanges(): HasMany
    {
        return $this->hasMany(TimeScheduleLog::class, 'changed_by');
    }

    /**
     * Get the attendance records recorded by this user.
     */
    public function recordedAttendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'recorded_by');
    }

    /**
     * Get the school this user belongs to.
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Check if user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is a principal.
     */
    public function isPrincipal(): bool
    {
        return $this->role === 'principal';
    }

    /**
     * Check if user is a teacher.
     */
    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user can manage all schools.
     */
    public function canManageAllSchools(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Scope a query to only include teachers.
     */
    public function scopeTeachers($query)
    {
        return $query->where('role', 'teacher');
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get cached teacher class IDs to prevent N+1 queries.
     * Caches the result for the lifetime of the model instance.
     */
    public function getTeacherClassIds(): Collection
    {
        if ($this->cachedTeacherClassIds === null) {
            $this->cachedTeacherClassIds = $this->classes()->pluck('id');
        }

        return $this->cachedTeacherClassIds;
    }

    /**
     * Clear the cached teacher class IDs.
     * Call this after modifying class assignments.
     */
    public function clearTeacherClassIdsCache(): void
    {
        $this->cachedTeacherClassIds = null;
    }
}

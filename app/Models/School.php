<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'email',
        'logo_path',
        'is_active',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(ClassRoom::class);
    }

    public function schoolYears(): HasMany
    {
        return $this->hasMany(SchoolYear::class);
    }

    public function timeSchedules(): HasMany
    {
        return $this->hasMany(TimeSchedule::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

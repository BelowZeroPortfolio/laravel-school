<?php

namespace App\Models\Traits;

use App\Models\School;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToSchool
{
    protected static function bootBelongsToSchool(): void
    {
        // Auto-scope queries to current user's school
        static::addGlobalScope('school', function (Builder $query) {
            if (auth()->check() && !auth()->user()->isSuperAdmin() && auth()->user()->school_id) {
                $query->where($query->getModel()->getTable() . '.school_id', auth()->user()->school_id);
            }
        });

        // Auto-assign school_id when creating records
        static::creating(function ($model) {
            if (!$model->school_id && auth()->check() && auth()->user()->school_id) {
                $model->school_id = auth()->user()->school_id;
            }
        });
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Scope to filter by specific school.
     */
    public function scopeForSchool(Builder $query, int $schoolId): Builder
    {
        return $query->withoutGlobalScope('school')->where('school_id', $schoolId);
    }

    /**
     * Scope to include all schools (bypass tenant scope).
     */
    public function scopeAllSchools(Builder $query): Builder
    {
        return $query->withoutGlobalScope('school');
    }
}

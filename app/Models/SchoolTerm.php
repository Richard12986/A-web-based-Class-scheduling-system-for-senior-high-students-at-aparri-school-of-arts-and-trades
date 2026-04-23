<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolTerm extends Model
{
    protected $fillable = [
        'school_year_id',
        'name',
        'term_order',
        'is_active',
        'starts_on',
        'ends_on',
    ];

    protected $casts = [
        'term_order' => 'integer',
        'is_active' => 'boolean',
        'starts_on' => 'date',
        'ends_on' => 'date',
    ];

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    public function pathwaySubjects(): HasMany
    {
        return $this->hasMany(PathwaySubject::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }
}
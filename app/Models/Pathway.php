<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pathway extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function pathwaySubjects(): HasMany
    {
        return $this->hasMany(PathwaySubject::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'pathway_subjects')
            ->withPivot(['grade_level_id', 'school_term_id', 'is_active'])
            ->withTimestamps();
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }
}
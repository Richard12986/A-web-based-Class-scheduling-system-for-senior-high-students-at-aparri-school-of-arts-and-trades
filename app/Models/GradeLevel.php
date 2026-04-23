<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradeLevel extends Model
{
    protected $fillable = [
        'code',
        'name',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function pathwaySubjects(): HasMany
    {
        return $this->hasMany(PathwaySubject::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }
}
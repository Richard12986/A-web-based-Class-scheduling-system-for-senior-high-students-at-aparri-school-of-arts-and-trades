<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SchoolYear extends Model
{
    protected $fillable = [
        'name',
        'is_active',
        'starts_on',
        'ends_on',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_on' => 'date',
        'ends_on' => 'date',
    ];

    public function schoolTerms(): HasMany
    {
        return $this->hasMany(SchoolTerm::class);
    }

    public function activeSchoolTerm(): HasOne
    {
        return $this->hasOne(SchoolTerm::class)->where('is_active', true);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }
}
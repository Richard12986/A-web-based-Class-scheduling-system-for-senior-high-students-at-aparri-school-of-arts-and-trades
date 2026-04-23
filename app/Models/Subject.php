<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Subject extends Model
{
    protected $fillable = [
        'code',
        'name',
        'subject_type',
        'weekly_hours',
        'total_hours',
        'offering_type',
        'requires_special_room',
        'is_active',
    ];

    protected $casts = [
        'weekly_hours' => 'decimal:2',
        'total_hours' => 'decimal:2',
        'requires_special_room' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function pathwaySubjects(): HasMany
    {
        return $this->hasMany(PathwaySubject::class);
    }

    public function pathways(): BelongsToMany
    {
        return $this->belongsToMany(Pathway::class, 'pathway_subjects')
            ->withPivot(['grade_level_id', 'school_term_id', 'is_active'])
            ->withTimestamps();
    }

    public function teacherSubjects(): HasMany
    {
        return $this->hasMany(TeacherSubject::class);
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'teacher_subjects')
            ->withTimestamps();
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }
}
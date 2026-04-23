<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Teacher extends Model
{
    protected $fillable = [
        'employee_number',
        'first_name',
        'middle_name',
        'last_name',
        'sex',
        'contact_number',
        'email',
        'maximum_weekly_load',
        'is_active',
    ];

    protected $casts = [
        'maximum_weekly_load' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'full_name',
    ];

    public function teacherSubjects(): HasMany
    {
        return $this->hasMany(TeacherSubject::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'teacher_subjects')
            ->withTimestamps();
    }

    public function teacherLoans(): HasMany
    {
        return $this->hasMany(TeacherLoan::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function getFullNameAttribute(): string
    {
        $middleInitial = $this->middle_name
            ? ' ' . strtoupper(substr($this->middle_name, 0, 1)) . '.'
            : '';

        return trim($this->first_name . $middleInitial . ' ' . $this->last_name);
    }

    public function getAssignedWeeklyHoursAttribute(): float
    {
        return (float) $this->schedules()->with('subject')->get()->sum(function ($schedule) {
            return (float) ($schedule->subject?->weekly_hours ?? 0);
        });
    }

    public function getRemainingWeeklyLoadAttribute(): float
    {
        return (float) $this->maximum_weekly_load - (float) $this->assigned_weekly_hours;
    }
}
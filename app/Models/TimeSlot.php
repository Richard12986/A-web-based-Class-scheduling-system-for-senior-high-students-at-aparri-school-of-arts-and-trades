<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimeSlot extends Model
{
    protected $fillable = [
        'shift_type_id',
        'label',
        'start_time',
        'end_time',
        'slot_order',
        'is_break',
        'is_active',
    ];

    protected $casts = [
        'slot_order' => 'integer',
        'is_break' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function shiftType(): BelongsTo
    {
        return $this->belongsTo(ShiftType::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function getTimeRangeAttribute(): string
    {
        return date('g:i A', strtotime($this->start_time)) . ' - ' . date('g:i A', strtotime($this->end_time));
    }
}
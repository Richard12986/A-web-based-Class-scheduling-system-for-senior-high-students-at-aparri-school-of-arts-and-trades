<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherLoan extends Model
{
    protected $fillable = [
        'teacher_id',
        'loan_type',
        'principal_amount',
        'loan_date',
        'remarks',
        'status',
    ];

    protected $casts = [
        'principal_amount' => 'decimal:2',
        'loan_date' => 'date',
    ];

    protected $appends = [
        'total_paid',
        'outstanding_balance',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(TeacherLoanPayment::class);
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments()->sum('amount_paid');
    }

    public function getOutstandingBalanceAttribute(): float
    {
        return (float) $this->principal_amount - (float) $this->total_paid;
    }
}
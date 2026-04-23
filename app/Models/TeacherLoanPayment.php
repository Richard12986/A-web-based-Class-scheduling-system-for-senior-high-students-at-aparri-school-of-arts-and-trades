<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherLoanPayment extends Model
{
    protected $fillable = [
        'teacher_loan_id',
        'payment_date',
        'amount_paid',
        'remarks',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount_paid' => 'decimal:2',
    ];

    public function teacherLoan(): BelongsTo
    {
        return $this->belongsTo(TeacherLoan::class);
    }
}
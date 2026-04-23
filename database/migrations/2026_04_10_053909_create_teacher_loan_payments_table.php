<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_loan_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('teacher_loan_id')
                ->constrained('teacher_loans')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->date('payment_date');
            $table->decimal('amount_paid', 12, 2);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_loan_payments');
    }
};
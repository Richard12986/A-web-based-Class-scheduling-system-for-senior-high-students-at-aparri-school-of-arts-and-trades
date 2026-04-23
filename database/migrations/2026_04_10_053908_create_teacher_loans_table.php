<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_loans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->string('loan_type', 50);
            $table->decimal('principal_amount', 12, 2);
            $table->date('loan_date');
            $table->text('remarks')->nullable();
            $table->enum('status', ['active', 'paid', 'cancelled'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_loans');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_year_id')
                ->constrained('school_years')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->string('name', 30);
            $table->unsignedTinyInteger('term_order');
            $table->boolean('is_active')->default(false);
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->timestamps();

            $table->unique(['school_year_id', 'term_order'], 'school_terms_sy_term_order_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_terms');
    }
};
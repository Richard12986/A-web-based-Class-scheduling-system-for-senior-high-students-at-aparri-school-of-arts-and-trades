<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pathway_subjects', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pathway_id')
                ->constrained('pathways')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('grade_level_id')
                ->nullable()
                ->constrained('grade_levels')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('school_term_id')
                ->nullable()
                ->constrained('school_terms')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(
                ['pathway_id', 'subject_id', 'grade_level_id', 'school_term_id'],
                'pathway_subjects_unique_context'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pathway_subjects');
    }
};
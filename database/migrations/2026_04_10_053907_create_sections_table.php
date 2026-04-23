<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();

            $table->foreignId('school_year_id')
                ->constrained('school_years')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('grade_level_id')
                ->constrained('grade_levels')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('pathway_id')
                ->constrained('pathways')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('shift_type_id')
                ->constrained('shift_types')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('room_id')
                ->nullable()
                ->constrained('rooms')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->string('name', 100);
            $table->unsignedSmallInteger('student_count')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_year_id', 'name'], 'sections_sy_name_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
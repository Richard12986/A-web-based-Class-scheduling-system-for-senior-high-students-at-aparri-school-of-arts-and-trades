<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('school_term_id')
                ->constrained('school_terms')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('section_id')
                ->constrained('sections')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('room_id')
                ->nullable()
                ->constrained('rooms')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('time_slot_id')
                ->constrained('time_slots')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->enum('day_of_week', [
                'monday',
                'tuesday',
                'wednesday',
                'thursday',
                'friday',
            ]);

            $table->timestamps();

            $table->unique(
                ['school_term_id', 'section_id', 'day_of_week', 'time_slot_id'],
                'schedules_term_section_day_slot_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
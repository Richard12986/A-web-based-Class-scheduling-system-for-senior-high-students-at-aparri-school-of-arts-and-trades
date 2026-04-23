<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shift_type_id')
                ->constrained('shift_types')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->string('label', 50)->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('slot_order');
            $table->boolean('is_break')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['shift_type_id', 'slot_order'], 'time_slots_shift_order_unique');
            $table->unique(['shift_type_id', 'start_time', 'end_time'], 'time_slots_shift_time_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
};
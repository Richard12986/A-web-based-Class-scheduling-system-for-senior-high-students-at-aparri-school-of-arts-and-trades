<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 150)->unique();
            $table->enum('subject_type', ['core', 'elective', 'hgp']);
            $table->decimal('weekly_hours', 5, 2)->default(0);
            $table->decimal('total_hours', 6, 2)->nullable();
            $table->enum('offering_type', ['semester', 'year'])->default('semester');
            $table->boolean('requires_special_room')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
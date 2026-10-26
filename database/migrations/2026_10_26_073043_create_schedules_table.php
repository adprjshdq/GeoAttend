<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('lecturer_id')->constrained('lecturers')->cascadeOnDelete();
            $table->string('day_of_week', 20); // e.g., Senin, Selasa, dst.
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room', 100);
            $table->decimal('location_latitude', 10, 8);
            $table->decimal('location_longitude', 11, 8);
            $table->unsignedInteger('geofence_radius')->default(100); // meters
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};

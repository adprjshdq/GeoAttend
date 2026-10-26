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
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->cascadeOnDelete();
            $table->unsignedTinyInteger('session_number')->default(1);
            $table->string('title', 150);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->enum('status', ['scheduled', 'active', 'closed'])->default('scheduled')->index();
            $table->decimal('override_latitude', 10, 8)->nullable();
            $table->decimal('override_longitude', 11, 8)->nullable();
            $table->unsignedInteger('override_radius')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};

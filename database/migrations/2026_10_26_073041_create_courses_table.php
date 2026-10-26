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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('course_code', 30)->unique();
            $table->string('course_name', 150);
            $table->unsignedTinyInteger('credits')->default(3);
            $table->unsignedTinyInteger('semester')->default(1);
            $table->string('academic_year', 50)->default('2025/2026 Ganjil');
            $table->foreignId('lecturer_id')->nullable()->constrained('lecturers')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};

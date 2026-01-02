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
        Schema::create('teacher_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('school_year_id')->constrained('school_years')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->datetime('time_in')->nullable();
            $table->datetime('time_out')->nullable();
            $table->datetime('first_student_scan')->nullable();
            $table->enum('attendance_status', ['pending', 'confirmed', 'late', 'absent', 'no_scan'])->default('pending');
            $table->enum('late_status', ['on_time', 'late'])->nullable();
            $table->foreignId('time_rule_id')->nullable()->constrained('time_schedules')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['teacher_id', 'attendance_date']);
            $table->index('attendance_date');
            $table->index('teacher_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_attendance');
    }
};

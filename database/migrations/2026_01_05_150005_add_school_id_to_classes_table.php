<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->after('id')->constrained('schools')->cascadeOnDelete();
            
            // Drop old unique constraint and add new one with school_id
            $table->dropUnique(['grade_level', 'section', 'school_year_id']);
            $table->unique(['school_id', 'grade_level', 'section', 'school_year_id'], 'classes_school_grade_section_year_unique');
        });
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropUnique('classes_school_grade_section_year_unique');
            $table->unique(['grade_level', 'section', 'school_year_id']);
            $table->dropForeign(['school_id']);
            $table->dropColumn('school_id');
        });
    }
};

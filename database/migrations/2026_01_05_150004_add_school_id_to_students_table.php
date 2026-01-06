<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->after('id')->constrained('schools')->cascadeOnDelete();
            
            // Drop old unique constraints and add new ones with school_id
            $table->dropUnique(['lrn']);
            $table->dropUnique(['student_id']);
            $table->unique(['school_id', 'lrn']);
            $table->unique(['school_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique(['school_id', 'lrn']);
            $table->dropUnique(['school_id', 'student_id']);
            $table->unique('lrn');
            $table->unique('student_id');
            $table->dropForeign(['school_id']);
            $table->dropColumn('school_id');
        });
    }
};

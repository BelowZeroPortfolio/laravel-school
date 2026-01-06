<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('school_years', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->after('id')->constrained('schools')->cascadeOnDelete();
            
            // Drop old unique constraint and add new one with school_id
            $table->dropUnique(['name']);
            $table->unique(['school_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::table('school_years', function (Blueprint $table) {
            $table->dropUnique(['school_id', 'name']);
            $table->unique('name');
            $table->dropForeign(['school_id']);
            $table->dropColumn('school_id');
        });
    }
};

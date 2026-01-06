<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update role enum to include super_admin
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->after('id')->constrained('schools')->nullOnDelete();
        });

        // Change role enum to include super_admin
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin', 'admin', 'principal', 'teacher') NOT NULL DEFAULT 'teacher'");

        // Add index for school_id
        Schema::table('users', function (Blueprint $table) {
            $table->index('school_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropIndex(['school_id']);
            $table->dropColumn('school_id');
        });

        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'principal', 'teacher') NOT NULL DEFAULT 'teacher'");
    }
};

<?php

use App\Models\School;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Create default school for existing data
        $school = School::firstOrCreate(
            ['code' => 'SCH-DEFAULT'],
            [
                'name' => 'Default School',
                'address' => null,
                'phone' => null,
                'email' => null,
                'is_active' => true,
            ]
        );

        // Assign existing records to default school
        DB::table('users')->whereNull('school_id')->where('role', '!=', 'super_admin')->update(['school_id' => $school->id]);
        DB::table('students')->whereNull('school_id')->update(['school_id' => $school->id]);
        DB::table('school_years')->whereNull('school_id')->update(['school_id' => $school->id]);
        DB::table('classes')->whereNull('school_id')->update(['school_id' => $school->id]);
        DB::table('time_schedules')->whereNull('school_id')->update(['school_id' => $school->id]);
    }

    public function down(): void
    {
        // Remove default school assignment
        $school = School::where('code', 'SCH-DEFAULT')->first();
        
        if ($school) {
            DB::table('users')->where('school_id', $school->id)->update(['school_id' => null]);
            DB::table('students')->where('school_id', $school->id)->update(['school_id' => null]);
            DB::table('school_years')->where('school_id', $school->id)->update(['school_id' => null]);
            DB::table('classes')->where('school_id', $school->id)->update(['school_id' => null]);
            DB::table('time_schedules')->where('school_id', $school->id)->update(['school_id' => null]);
            
            $school->delete();
        }
    }
};

<?php

namespace App\Console\Commands;

use App\Services\TeacherAttendanceService;
use Illuminate\Console\Command;

/**
 * Mark teachers without any attendance record as absent.
 * Scheduled to run daily at 17:30.
 * (Requirements 12.1, 12.3)
 */
class MarkAbsentTeachersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:mark-absent-teachers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark teachers without any attendance record today as absent';

    /**
     * Execute the console command.
     */
    public function handle(TeacherAttendanceService $service): int
    {
        $this->info('Marking absent teachers...');

        $count = $service->markAbsentTeachers();

        $this->info("Marked {$count} teacher(s) as absent.");

        return Command::SUCCESS;
    }
}

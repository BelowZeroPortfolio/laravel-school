<?php

namespace App\Console\Commands;

use App\Services\TeacherAttendanceService;
use Illuminate\Console\Command;

/**
 * Mark teachers with pending status as no_scan.
 * Scheduled to run daily at 18:00.
 * (Requirement 12.2)
 */
class MarkNoScanTeachersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:mark-no-scan-teachers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark teachers with pending attendance status as no_scan';

    /**
     * Execute the console command.
     */
    public function handle(TeacherAttendanceService $service): int
    {
        $this->info('Marking no-scan teachers...');

        $count = $service->markNoScanTeachers();

        $this->info("Marked {$count} teacher(s) as no_scan.");

        return Command::SUCCESS;
    }
}

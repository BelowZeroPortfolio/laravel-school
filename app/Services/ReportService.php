<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\SchoolYear;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ReportService
{
    /**
     * Get attendance report with filters and pagination.
     * (Requirement 17.1)
     *
     * @param array $filters Filter options:
     *   - start_date: string (Y-m-d format)
     *   - end_date: string (Y-m-d format)
     *   - class_id: int|null
     *   - student_id: int|null
     *   - school_year_id: int|null
     *   - teacher_id: int|null (for role-based filtering)
     *   - status: string|null ('present', 'late', 'absent')
     *   - search: string|null (student name search)
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getAttendanceReport(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Attendance::with(['student', 'schoolYear', 'recorder']);

        // Date range filter (Requirement 17.1)
        if (!empty($filters['start_date'])) {
            $query->whereDate('attendance_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('attendance_date', '<=', $filters['end_date']);
        }

        // School year filter
        if (!empty($filters['school_year_id'])) {
            $query->forSchoolYear($filters['school_year_id']);
        }

        // Class filter (Requirement 17.1)
        if (!empty($filters['class_id'])) {
            $query->forClass($filters['class_id']);
        }

        // Student filter (Requirement 17.1)
        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Teacher filter - for role-based visibility (Requirement 17.3)
        if (!empty($filters['teacher_id'])) {
            $classIds = ClassRoom::where('teacher_id', $filters['teacher_id'])
                ->where('is_active', true)
                ->pluck('id');

            $query->whereHas('student.classes', function ($q) use ($classIds) {
                $q->whereIn('classes.id', $classIds);
            });
        }

        // Student name search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('lrn', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        // Order by date descending, then by student name
        $query->orderBy('attendance_date', 'desc')
            ->orderBy('check_in_time', 'desc');

        return $query->paginate($perPage);
    }


    /**
     * Calculate attendance statistics from a collection of records.
     * (Requirement 17.2)
     *
     * @param Collection $records Collection of Attendance records
     * @return array Statistics array with counts and percentages
     */
    public function calculateStatistics(Collection $records): array
    {
        $total = $records->count();

        if ($total === 0) {
            return [
                'total' => 0,
                'present' => ['count' => 0, 'percentage' => 0.0],
                'late' => ['count' => 0, 'percentage' => 0.0],
                'absent' => ['count' => 0, 'percentage' => 0.0],
            ];
        }

        // Count by status (Requirement 17.2)
        $presentCount = $records->where('status', 'present')->count();
        $lateCount = $records->where('status', 'late')->count();
        $absentCount = $records->where('status', 'absent')->count();

        // Calculate percentages
        $presentPercentage = round(($presentCount / $total) * 100, 2);
        $latePercentage = round(($lateCount / $total) * 100, 2);
        $absentPercentage = round(($absentCount / $total) * 100, 2);

        return [
            'total' => $total,
            'present' => [
                'count' => $presentCount,
                'percentage' => $presentPercentage,
            ],
            'late' => [
                'count' => $lateCount,
                'percentage' => $latePercentage,
            ],
            'absent' => [
                'count' => $absentCount,
                'percentage' => $absentPercentage,
            ],
        ];
    }


    /**
     * Get per-class attendance summary for a specific date.
     * (Requirement 17.5)
     *
     * @param string $date The date (Y-m-d format)
     * @param int|null $schoolYearId Optional school year filter
     * @param int|null $teacherId Optional teacher filter for role-based visibility
     * @return Collection Collection of class summaries
     */
    public function getDailySummary(string $date, ?int $schoolYearId = null, ?int $teacherId = null): Collection
    {
        // Get active school year if not specified
        if (!$schoolYearId) {
            $activeSchoolYear = SchoolYear::where('is_active', true)->first();
            $schoolYearId = $activeSchoolYear?->id;
        }

        // Build query for classes
        $classQuery = ClassRoom::with(['teacher', 'schoolYear'])
            ->where('is_active', true);

        if ($schoolYearId) {
            $classQuery->where('school_year_id', $schoolYearId);
        }

        // Filter by teacher for role-based visibility (Requirement 17.3)
        if ($teacherId) {
            $classQuery->where('teacher_id', $teacherId);
        }

        $classes = $classQuery->get();

        // Build summary for each class (Requirement 17.5)
        return $classes->map(function (ClassRoom $class) use ($date) {
            // Get enrolled students count
            $enrolledCount = $class->students()
                ->wherePivot('is_active', true)
                ->count();

            // Get attendance records for this class on the specified date
            $attendanceRecords = Attendance::whereDate('attendance_date', $date)
                ->whereHas('student.classes', function ($q) use ($class) {
                    $q->where('classes.id', $class->id)
                        ->wherePivot('is_active', true);
                })
                ->get();

            // Calculate statistics
            $presentCount = $attendanceRecords->where('status', 'present')->count();
            $lateCount = $attendanceRecords->where('status', 'late')->count();
            $absentCount = $enrolledCount - $presentCount - $lateCount;

            // Ensure absent count is not negative
            $absentCount = max(0, $absentCount);

            return [
                'class_id' => $class->id,
                'grade_level' => $class->grade_level,
                'section' => $class->section,
                'display_name' => $class->display_name,
                'teacher_name' => $class->teacher?->full_name,
                'enrolled_count' => $enrolledCount,
                'present_count' => $presentCount,
                'late_count' => $lateCount,
                'absent_count' => $absentCount,
                'attendance_rate' => $enrolledCount > 0
                    ? round((($presentCount + $lateCount) / $enrolledCount) * 100, 2)
                    : 0.0,
            ];
        });
    }


    /**
     * Export attendance records to CSV format.
     * (Requirement 17.4)
     *
     * @param Collection $records Collection of Attendance records
     * @return string The path to the generated CSV file
     */
    public function exportToCsv(Collection $records): string
    {
        // Define CSV headers
        $headers = [
            'Date',
            'Student ID',
            'LRN',
            'Student Name',
            'Grade Level',
            'Section',
            'Check In Time',
            'Check Out Time',
            'Status',
            'Recorded By',
        ];

        // Build CSV content
        $csvContent = implode(',', $headers) . "\n";

        foreach ($records as $record) {
            // Get student's class info
            $class = $record->student?->classes()
                ->wherePivot('is_active', true)
                ->first();

            $row = [
                $record->attendance_date?->format('Y-m-d'),
                $this->escapeCsvField($record->student?->student_id ?? ''),
                $this->escapeCsvField($record->student?->lrn ?? ''),
                $this->escapeCsvField($record->student?->full_name ?? ''),
                $this->escapeCsvField($class?->grade_level ?? ''),
                $this->escapeCsvField($class?->section ?? ''),
                $record->check_in_time?->format('H:i:s') ?? '',
                $record->check_out_time?->format('H:i:s') ?? '',
                ucfirst($record->status ?? ''),
                $this->escapeCsvField($record->recorder?->full_name ?? 'System'),
            ];

            $csvContent .= implode(',', $row) . "\n";
        }

        // Generate filename
        $filename = 'reports/attendance_' . now()->format('Y-m-d_His') . '.csv';

        // Store the CSV file
        Storage::disk('public')->put($filename, $csvContent);

        return $filename;
    }

    /**
     * Escape a field for CSV output.
     *
     * @param string $field
     * @return string
     */
    protected function escapeCsvField(string $field): string
    {
        // If field contains comma, quote, or newline, wrap in quotes and escape quotes
        if (preg_match('/[,"\n\r]/', $field)) {
            return '"' . str_replace('"', '""', $field) . '"';
        }

        return $field;
    }


    /**
     * Export attendance report to PDF format.
     * (Requirement 17.4)
     *
     * @param Collection $records Collection of Attendance records
     * @param array $filters The filters used to generate the report (for display)
     * @return string The path to the generated PDF file
     */
    public function exportToPdf(Collection $records, array $filters = []): string
    {
        // Calculate statistics for the report
        $statistics = $this->calculateStatistics($records);

        // Get school year name
        $schoolYearName = 'All School Years';
        if (!empty($filters['school_year_id'])) {
            $schoolYear = SchoolYear::find($filters['school_year_id']);
            $schoolYearName = $schoolYear?->name ?? 'Unknown';
        }

        // Get class name if filtered
        $className = 'All Classes';
        if (!empty($filters['class_id'])) {
            $class = ClassRoom::find($filters['class_id']);
            $className = $class?->display_name ?? 'Unknown';
        }

        // Prepare date range display
        $dateRange = 'All Dates';
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $dateRange = Carbon::parse($filters['start_date'])->format('M d, Y') 
                . ' - ' 
                . Carbon::parse($filters['end_date'])->format('M d, Y');
        } elseif (!empty($filters['start_date'])) {
            $dateRange = 'From ' . Carbon::parse($filters['start_date'])->format('M d, Y');
        } elseif (!empty($filters['end_date'])) {
            $dateRange = 'Until ' . Carbon::parse($filters['end_date'])->format('M d, Y');
        }

        // Group records by date for better organization
        $groupedRecords = $records->groupBy(function ($record) {
            return $record->attendance_date?->format('Y-m-d');
        });

        // Generate PDF using the report template
        $pdf = Pdf::loadView('reports.pdf', [
            'records' => $records,
            'groupedRecords' => $groupedRecords,
            'statistics' => $statistics,
            'schoolYearName' => $schoolYearName,
            'className' => $className,
            'dateRange' => $dateRange,
            'generatedAt' => now()->format('F d, Y h:i A'),
            'filters' => $filters,
        ]);

        // Set paper size
        $pdf->setPaper('letter', 'portrait');

        // Generate filename
        $filename = 'reports/attendance_report_' . now()->format('Y-m-d_His') . '.pdf';

        // Store the PDF
        Storage::disk('public')->put($filename, $pdf->output());

        return $filename;
    }

    /**
     * Get attendance records without pagination (for export).
     *
     * @param array $filters Same filters as getAttendanceReport()
     * @return Collection
     */
    public function getAttendanceRecordsForExport(array $filters): Collection
    {
        $query = Attendance::with(['student', 'schoolYear', 'recorder']);

        // Date range filter
        if (!empty($filters['start_date'])) {
            $query->whereDate('attendance_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('attendance_date', '<=', $filters['end_date']);
        }

        // School year filter
        if (!empty($filters['school_year_id'])) {
            $query->forSchoolYear($filters['school_year_id']);
        }

        // Class filter
        if (!empty($filters['class_id'])) {
            $query->forClass($filters['class_id']);
        }

        // Student filter
        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Teacher filter - for role-based visibility
        if (!empty($filters['teacher_id'])) {
            $classIds = ClassRoom::where('teacher_id', $filters['teacher_id'])
                ->where('is_active', true)
                ->pluck('id');

            $query->whereHas('student.classes', function ($q) use ($classIds) {
                $q->whereIn('classes.id', $classIds);
            });
        }

        // Student name search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('lrn', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        // Order by date descending
        $query->orderBy('attendance_date', 'desc')
            ->orderBy('check_in_time', 'desc');

        return $query->get();
    }
}

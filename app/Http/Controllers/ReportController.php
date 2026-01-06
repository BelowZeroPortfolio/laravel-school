<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ReportService $reportService
    ) {}

    /**
     * Display attendance report with filters.
     * (Requirement 17.1, 17.3)
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Build filters from request
        $filters = [
            'start_date' => $request->input('start_date', Carbon::now()->startOfMonth()->toDateString()),
            'end_date' => $request->input('end_date', Carbon::now()->toDateString()),
            'school_year_id' => $request->input('school_year_id'),
            'class_id' => $request->input('class_id'),
            'student_id' => $request->input('student_id'),
            'status' => $request->input('status'),
            'search' => $request->input('search'),
        ];

        // Get active school year if not specified
        if (empty($filters['school_year_id'])) {
            $activeSchoolYear = SchoolYear::active()->first();
            $filters['school_year_id'] = $activeSchoolYear?->id;
        }

        // Apply role-based filtering (Requirement 17.3)
        // Teachers can only see data for students in their classes
        if ($user->isTeacher()) {
            $filters['teacher_id'] = $user->id;
        }

        // Get paginated attendance records
        $attendances = $this->reportService->getAttendanceReport($filters, 25);

        // Get records for statistics calculation (without pagination)
        $allRecords = $this->reportService->getAttendanceRecordsForExport($filters);
        $statistics = $this->reportService->calculateStatistics($allRecords);

        // Get filter options
        $schoolYears = SchoolYear::orderBy('start_date', 'desc')->get();

        // Classes - role-based (Requirement 17.3)
        $classes = $user->isTeacher()
            ? $user->classes()->active()->get()
            : ClassRoom::active()->get();

        // Students - role-based
        if ($user->isTeacher()) {
            $classIds = $user->getTeacherClassIds();
            $students = Student::whereHas('classes', function ($q) use ($classIds) {
                $q->whereIn('classes.id', $classIds);
            })->active()->orderBy('last_name')->get();
        } else {
            $students = Student::active()->orderBy('last_name')->get();
        }

        return view('reports.index', [
            'attendances' => $attendances,
            'statistics' => $statistics,
            'schoolYears' => $schoolYears,
            'classes' => $classes,
            'students' => $students,
            'filters' => $filters,
        ]);
    }


    /**
     * Export attendance report to CSV format.
     * (Requirement 17.4)
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $user = $request->user();

        // Build filters from request
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'school_year_id' => $request->input('school_year_id'),
            'class_id' => $request->input('class_id'),
            'student_id' => $request->input('student_id'),
            'status' => $request->input('status'),
            'search' => $request->input('search'),
        ];

        // Apply role-based filtering (Requirement 17.3)
        if ($user->isTeacher()) {
            $filters['teacher_id'] = $user->id;
        }

        // Get records for export
        $records = $this->reportService->getAttendanceRecordsForExport($filters);

        // Generate CSV file
        $filePath = $this->reportService->exportToCsv($records);

        // Generate filename for download
        $downloadFilename = 'attendance_report_' . now()->format('Y-m-d') . '.csv';

        // Return streamed response for download
        return response()->streamDownload(function () use ($filePath) {
            echo \Illuminate\Support\Facades\Storage::disk('public')->get($filePath);
        }, $downloadFilename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Export attendance report to Excel format.
     * (Requirement 17.4)
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $user = $request->user();

        // Build filters from request
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'school_year_id' => $request->input('school_year_id'),
            'class_id' => $request->input('class_id'),
            'student_id' => $request->input('student_id'),
            'status' => $request->input('status'),
            'search' => $request->input('search'),
        ];

        // Apply role-based filtering (Requirement 17.3)
        if ($user->isTeacher()) {
            $filters['teacher_id'] = $user->id;
        }

        // Get records for export
        $records = $this->reportService->getAttendanceRecordsForExport($filters);

        // Generate filename for download - use .csv extension which Excel opens properly
        $downloadFilename = 'attendance_report_' . now()->format('Y-m-d') . '.csv';

        // Return streamed response for Excel download
        return response()->streamDownload(function () use ($records) {
            $output = fopen('php://output', 'w');

            // Add BOM for Excel UTF-8 compatibility
            fwrite($output, "\xEF\xBB\xBF");

            // Header row
            fputcsv($output, [
                'Date',
                'Student ID',
                'LRN',
                'Student Name',
                'Grade Level',
                'Section',
                'Check In',
                'Check Out',
                'Status',
                'Recorded By',
            ]);

            // Data rows
            foreach ($records as $record) {
                $studentClass = $record->student?->classes()
                    ->where('student_classes.is_active', true)
                    ->first();

                fputcsv($output, [
                    $record->attendance_date?->format('Y-m-d') ?? '',
                    $record->student?->student_id ?? '',
                    $record->student?->lrn ?? '',
                    $record->student?->full_name ?? '',
                    $studentClass?->grade_level ?? '',
                    $studentClass?->section ?? '',
                    $record->check_in_time?->format('H:i:s') ?? '',
                    $record->check_out_time?->format('H:i:s') ?? '',
                    ucfirst($record->status ?? ''),
                    $record->recorder?->full_name ?? '',
                ]);
            }

            fclose($output);
        }, $downloadFilename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $downloadFilename . '"',
        ]);
    }

    /**
     * Display daily attendance summary per class.
     * (Requirement 17.5)
     */
    public function dailySummary(Request $request): View
    {
        $user = $request->user();
        $date = $request->input('date', Carbon::today()->toDateString());
        $schoolYearId = $request->input('school_year_id');

        // Get active school year if not specified
        if (empty($schoolYearId)) {
            $activeSchoolYear = SchoolYear::active()->first();
            $schoolYearId = $activeSchoolYear?->id;
        }

        // Apply role-based filtering (Requirement 17.3)
        $teacherId = $user->isTeacher() ? $user->id : null;

        // Get daily summary per class
        $classSummaries = $this->reportService->getDailySummary($date, $schoolYearId, $teacherId);

        // Calculate overall totals
        $totals = [
            'enrolled' => $classSummaries->sum('enrolled_count'),
            'present' => $classSummaries->sum('present_count'),
            'late' => $classSummaries->sum('late_count'),
            'absent' => $classSummaries->sum('absent_count'),
        ];

        $totals['attendance_rate'] = $totals['enrolled'] > 0
            ? round((($totals['present'] + $totals['late']) / $totals['enrolled']) * 100, 2)
            : 0.0;

        // Get filter options
        $schoolYears = SchoolYear::orderBy('start_date', 'desc')->get();

        return view('reports.daily-summary', [
            'classSummaries' => $classSummaries,
            'totals' => $totals,
            'schoolYears' => $schoolYears,
            'selectedDate' => $date,
            'selectedSchoolYearId' => $schoolYearId,
        ]);
    }
}

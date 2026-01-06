<?php

namespace App\Http\Controllers;

use App\Models\ClassRoom;
use App\Models\Student;
use App\Services\IdCardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class IdCardController extends Controller
{
    public function __construct(
        protected IdCardService $idCardService
    ) {}

    /**
     * Display the ID card generation page.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Get classes based on user role
        $classesQuery = $user->isTeacher()
            ? $user->classes()->active()->with('schoolYear')
            : ClassRoom::active()->with('schoolYear');

        $classes = $classesQuery->get();

        // Get unique grade levels and sections for filters
        $gradeLevels = $classes->pluck('grade_level')->unique()->sort()->values();
        $sections = $classes->pluck('section')->unique()->sort()->values();

        // Get students based on user role and filters
        $studentsQuery = Student::active()->with(['classes' => function ($query) {
            $query->wherePivot('is_active', true)->with('schoolYear');
        }]);

        if ($user->isTeacher()) {
            $teacherClassIds = $user->classes()->pluck('id');
            $studentsQuery->whereHas('classes', fn ($q) => $q->whereIn('classes.id', $teacherClassIds));
        }

        // Apply filters
        if ($request->filled('grade_level')) {
            $studentsQuery->whereHas('classes', fn ($q) => $q->where('grade_level', $request->input('grade_level'))
                ->where('student_classes.is_active', true));
        }

        if ($request->filled('section')) {
            $studentsQuery->whereHas('classes', fn ($q) => $q->where('section', $request->input('section'))
                ->where('student_classes.is_active', true));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $studentsQuery->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('lrn', 'like', "%{$search}%")
                    ->orWhere('student_id', 'like', "%{$search}%");
            });
        }

        // Only show students if filters are applied
        $students = ($request->hasAny(['grade_level', 'section', 'search']))
            ? $studentsQuery->orderBy('last_name')->orderBy('first_name')->get()
            : collect();

        // Get active school year
        $activeSchoolYear = \App\Models\SchoolYear::active()->first();

        return view('id-cards.index', [
            'classes' => $classes,
            'students' => $students,
            'gradeLevels' => $gradeLevels,
            'sections' => $sections,
            'activeSchoolYear' => $activeSchoolYear,
        ]);
    }

    /**
     * Generate ID card for a single student.
     * (Requirements: 16.1, 16.2)
     */
    public function generate(Request $request, Student $student): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        // Check access for teachers
        if ($user->isTeacher()) {
            $teacherClassIds = $user->classes()->pluck('id');
            $studentClassIds = $student->classes()->pluck('classes.id');

            if ($teacherClassIds->intersect($studentClassIds)->isEmpty()) {
                if ($request->wantsJson()) {
                    return response()->json(['error' => 'You do not have access to this student.'], 403);
                }
                abort(403, 'You do not have access to this student.');
            }
        }

        // Generate the ID card data (Requirement 16.2)
        $idCardData = $this->idCardService->generateIdCard($student);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ID card generated successfully.',
                'data' => $idCardData,
            ]);
        }

        return redirect()->back()
            ->with('success', 'ID card generated successfully for ' . $student->full_name)
            ->with('idCardData', $idCardData);
    }

    /**
     * Generate QR code for a single student.
     * (Requirement: 16.1)
     */
    public function generateQrCode(Request $request, Student $student): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        // Check access for teachers
        if ($user->isTeacher()) {
            $teacherClassIds = $user->classes()->pluck('id');
            $studentClassIds = $student->classes()->pluck('classes.id');

            if ($teacherClassIds->intersect($studentClassIds)->isEmpty()) {
                if ($request->wantsJson()) {
                    return response()->json(['error' => 'You do not have access to this student.'], 403);
                }
                abort(403, 'You do not have access to this student.');
            }
        }

        // Generate the QR code (Requirement 16.1)
        $qrCodePath = $this->idCardService->generateQRCode($student);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'QR code generated successfully.',
                'qrcode_path' => $qrCodePath,
            ]);
        }

        return redirect()->back()
            ->with('success', 'QR code generated successfully for ' . $student->full_name);
    }

    /**
     * Generate ID cards for all students in a class (batch generation).
     * (Requirement: 16.3)
     */
    public function batchGenerate(Request $request, ClassRoom $class): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        // Check access for teachers
        if ($user->isTeacher()) {
            if ($class->teacher_id !== $user->id) {
                if ($request->wantsJson()) {
                    return response()->json(['error' => 'You do not have access to this class.'], 403);
                }
                abort(403, 'You do not have access to this class.');
            }
        }

        // Generate batch ID cards (Requirement 16.3)
        $idCards = $this->idCardService->generateBatchIdCards($class->id);

        if ($idCards->isEmpty()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No students enrolled in this class.',
                ], 404);
            }

            return redirect()->back()
                ->with('error', 'No students enrolled in this class.');
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'ID cards generated successfully for ' . $idCards->count() . ' students.',
                'count' => $idCards->count(),
                'data' => $idCards,
            ]);
        }

        return redirect()->back()
            ->with('success', 'ID cards generated successfully for ' . $idCards->count() . ' students.')
            ->with('batchIdCards', $idCards);
    }

    /**
     * Export ID cards to PDF.
     * (Requirement: 16.5)
     */
    public function exportPdf(Request $request): BinaryFileResponse|RedirectResponse
    {
        $validated = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['required', 'integer', 'exists:students,id'],
        ]);

        $user = $request->user();

        // Check access for teachers
        if ($user->isTeacher()) {
            $teacherClassIds = $user->classes()->pluck('id');
            $accessibleStudentIds = Student::whereHas('classes', function ($query) use ($teacherClassIds) {
                $query->whereIn('classes.id', $teacherClassIds);
            })->pluck('id')->toArray();

            $requestedIds = $validated['student_ids'];
            $unauthorizedIds = array_diff($requestedIds, $accessibleStudentIds);

            if (!empty($unauthorizedIds)) {
                return redirect()->back()
                    ->with('error', 'You do not have access to some of the selected students.');
            }
        }

        // Generate PDF (Requirement 16.5)
        $pdfPath = $this->idCardService->exportToPdf($validated['student_ids']);

        $fullPath = Storage::disk('public')->path($pdfPath);

        return response()->download($fullPath, 'student-id-cards.pdf', [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend(false);
    }

    /**
     * Export ID cards for an entire class to PDF.
     * (Requirements: 16.3, 16.5)
     */
    public function exportClassPdf(Request $request, ClassRoom $class): BinaryFileResponse|RedirectResponse
    {
        $user = $request->user();

        // Check access for teachers
        if ($user->isTeacher()) {
            if ($class->teacher_id !== $user->id) {
                return redirect()->back()
                    ->with('error', 'You do not have access to this class.');
            }
        }

        // Get all active student IDs in the class
        $studentIds = $class->students()
            ->wherePivot('is_active', true)
            ->pluck('students.id')
            ->toArray();

        if (empty($studentIds)) {
            return redirect()->back()
                ->with('error', 'No students enrolled in this class.');
        }

        // Generate PDF (Requirement 16.5)
        $pdfPath = $this->idCardService->exportToPdf($studentIds);

        $fullPath = Storage::disk('public')->path($pdfPath);

        $filename = 'id-cards-' . $class->grade_level . '-' . $class->section . '.pdf';

        return response()->download($fullPath, $filename, [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend(false);
    }

    /**
     * Preview ID card for a student (returns HTML).
     */
    public function preview(Request $request, Student $student): View
    {
        $user = $request->user();

        // Check access for teachers
        if ($user->isTeacher()) {
            $teacherClassIds = $user->classes()->pluck('id');
            $studentClassIds = $student->classes()->pluck('classes.id');

            if ($teacherClassIds->intersect($studentClassIds)->isEmpty()) {
                abort(403, 'You do not have access to this student.');
            }
        }

        $idCardData = $this->idCardService->generateIdCard($student);

        return view('id-cards.preview', [
            'card' => $idCardData,
            'student' => $student,
        ]);
    }
}

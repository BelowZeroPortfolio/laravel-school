<?php

namespace App\Services;

use App\Models\ClassRoom;
use App\Models\SchoolYear;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class IdCardService
{
    /**
     * Generate a QR code for a student.
     * The QR code contains the student's LRN if present, otherwise the student_id.
     * (Requirements: 16.1, 16.4)
     *
     * @param Student $student
     * @return string The path to the generated QR code
     */
    public function generateQRCode(Student $student): string
    {
        // Use LRN if present, otherwise use student_id (Requirement 16.1)
        $identifier = $student->lrn ?? $student->student_id;

        // Generate QR code as PNG
        $qrCode = QrCode::format('png')
            ->size(200)
            ->margin(1)
            ->generate($identifier);

        // Create the storage path
        $filename = 'qrcodes/' . $student->student_id . '_' . time() . '.png';
        
        // Store the QR code in public storage
        Storage::disk('public')->put($filename, $qrCode);

        // Update the student's qrcode_path (Requirement 16.4)
        $student->update(['qrcode_path' => $filename]);

        return $filename;
    }

    /**
     * Generate ID card data for a student.
     * Compiles student data including photo, name, grade, section, school year.
     * (Requirement 16.2)
     *
     * @param Student $student
     * @return array The ID card data
     */
    public function generateIdCard(Student $student): array
    {
        // Get the student's active class enrollment
        $activeClass = $student->classes()
            ->wherePivot('is_active', true)
            ->with('schoolYear')
            ->first();

        // Generate QR code if not exists
        if (!$student->qrcode_path || !Storage::disk('public')->exists($student->qrcode_path)) {
            $this->generateQRCode($student);
            $student->refresh();
        }

        // Compile ID card data (Requirement 16.2)
        return [
            'student_id' => $student->student_id,
            'lrn' => $student->lrn,
            'full_name' => $student->full_name,
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'photo_path' => $student->photo_path,
            'qrcode_path' => $student->qrcode_path,
            'grade_level' => $activeClass?->grade_level,
            'section' => $activeClass?->section,
            'school_year' => $activeClass?->schoolYear?->name,
            'school_year_id' => $activeClass?->school_year_id,
        ];
    }


    /**
     * Generate ID cards for all enrolled students in a class.
     * (Requirement 16.3)
     *
     * @param int $classId
     * @return Collection Collection of ID card data arrays
     */
    public function generateBatchIdCards(int $classId): Collection
    {
        $class = ClassRoom::with(['students' => function ($query) {
            $query->wherePivot('is_active', true);
        }, 'schoolYear'])->find($classId);

        if (!$class) {
            return collect([]);
        }

        // Generate ID cards for all enrolled students (Requirement 16.3)
        return $class->students->map(function (Student $student) use ($class) {
            // Generate QR code if not exists
            if (!$student->qrcode_path || !Storage::disk('public')->exists($student->qrcode_path)) {
                $this->generateQRCode($student);
                $student->refresh();
            }

            return [
                'student_id' => $student->student_id,
                'lrn' => $student->lrn,
                'full_name' => $student->full_name,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'photo_path' => $student->photo_path,
                'qrcode_path' => $student->qrcode_path,
                'grade_level' => $class->grade_level,
                'section' => $class->section,
                'school_year' => $class->schoolYear?->name,
                'school_year_id' => $class->school_year_id,
            ];
        });
    }

    /**
     * Export ID cards to PDF format.
     * (Requirement 16.5)
     *
     * @param array $studentIds Array of student IDs to include
     * @return string The path to the generated PDF file
     */
    public function exportToPdf(array $studentIds): string
    {
        $students = Student::whereIn('id', $studentIds)->get();

        // Generate ID card data for each student
        $idCards = $students->map(function (Student $student) {
            return $this->generateIdCard($student);
        });

        // Get school year for the PDF title
        $activeSchoolYear = SchoolYear::where('is_active', true)->first();

        // Generate PDF using the ID card template
        $pdf = Pdf::loadView('id-cards.pdf', [
            'idCards' => $idCards,
            'schoolYear' => $activeSchoolYear?->name ?? 'Unknown School Year',
            'generatedAt' => now()->format('F d, Y'),
        ]);

        // Set paper size for ID cards (typically credit card size)
        $pdf->setPaper('letter', 'portrait');

        // Create filename
        $filename = 'id-cards/batch_' . time() . '.pdf';

        // Store the PDF
        Storage::disk('public')->put($filename, $pdf->output());

        return $filename;
    }

    /**
     * Get the QR code content for a student.
     * Returns LRN if present, otherwise student_id.
     *
     * @param Student $student
     * @return string
     */
    public function getQRCodeContent(Student $student): string
    {
        return $student->lrn ?? $student->student_id;
    }

    /**
     * Regenerate QR code for a student.
     * Useful when student identifier changes.
     *
     * @param Student $student
     * @return string The new QR code path
     */
    public function regenerateQRCode(Student $student): string
    {
        // Delete old QR code if exists
        if ($student->qrcode_path && Storage::disk('public')->exists($student->qrcode_path)) {
            Storage::disk('public')->delete($student->qrcode_path);
        }

        return $this->generateQRCode($student);
    }
}

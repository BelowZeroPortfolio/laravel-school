<?php

namespace App\Http\Controllers;

use App\Services\StudentAttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScanController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected StudentAttendanceService $studentAttendanceService
    ) {}

    /**
     * Display the QR code scanning interface.
     * (Requirement 6.1)
     */
    public function index(): View
    {
        return view('scan.index');
    }

    /**
     * Process a QR code scan for attendance.
     * (Requirements 6.1, 6.2, 6.3)
     */
    public function scan(Request $request): JsonResponse
    {
        $request->validate([
            'qr_code' => ['required', 'string', 'max:255'],
            'mode' => ['sometimes', 'string', 'in:arrival,departure'],
        ]);

        $qrCode = $request->input('qr_code');
        $mode = $request->input('mode', 'arrival');

        // Process the QR code scan using the service
        $result = $this->studentAttendanceService->processQRCodeScan($qrCode, $mode);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'student' => isset($result['student']) ? [
                        'id' => $result['student']->id,
                        'student_id' => $result['student']->student_id,
                        'full_name' => $result['student']->full_name,
                        'lrn' => $result['student']->lrn,
                    ] : null,
                    'status' => $result['status'] ?? null,
                    'check_in_time' => $result['check_in_time'] ?? null,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
            'error_code' => $result['error_code'] ?? 'UNKNOWN_ERROR',
            'data' => isset($result['student']) ? [
                'student' => [
                    'id' => $result['student']->id,
                    'full_name' => $result['student']->full_name,
                ],
            ] : null,
        ], $result['error_code'] === 'STUDENT_NOT_FOUND' ? 404 : 422);
    }
}

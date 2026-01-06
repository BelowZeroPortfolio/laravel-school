<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IdCardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\SchoolYearController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherMonitoringController;
use App\Http\Controllers\TimeScheduleController;
use App\Http\Controllers\StudentPlacementController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\SchoolController as SuperAdminSchoolController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group.
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Authentication Routes (Requirements 1.1, 1.2, 1.3)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    // Login form and submission (Requirement 1.1)
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    // Logout (Requirement 1.3 - records teacher time_out before logout)
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (Requirements 6.1, 8.1, 9.1)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Dashboard (Requirement 1.1)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/live-stats', [DashboardController::class, 'liveStats'])->name('dashboard.live-stats');

    // QR Code Scanning (Requirement 6.1)
    Route::get('/scan', [ScanController::class, 'index'])->name('scan.index');
    Route::post('/scan', [ScanController::class, 'scan'])->name('scan.process');

    // Students (Requirements 8.1, 8.2, 8.3)
    Route::resource('students', StudentController::class);

    // Classes (Requirements 9.1, 9.2, 9.3)
    Route::resource('classes', ClassController::class);
    Route::post('/classes/{class}/enroll', [ClassController::class, 'enrollStudent'])->name('classes.enroll');
    Route::delete('/classes/{class}/students/{student}', [ClassController::class, 'unenrollStudent'])->name('classes.unenroll');

    // Attendance (Requirements 6.1, 18.1, 18.2, 18.4, 18.5)
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/create', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::put('/attendance/{attendance}', [AttendanceController::class, 'update'])->name('attendance.update');
    Route::get('/attendance/date/{date}', [AttendanceController::class, 'byDate'])->name('attendance.by-date');
    Route::get('/attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');
    Route::get('/attendance/history/{student}', [AttendanceController::class, 'history'])->name('attendance.history');

    // ID Card Generation (Requirements 16.1, 16.2, 16.3, 16.5)
    Route::get('/id-cards', [IdCardController::class, 'index'])->name('id-cards.index');
    Route::post('/id-cards/students/{student}/generate', [IdCardController::class, 'generate'])->name('id-cards.generate');
    Route::post('/id-cards/students/{student}/qrcode', [IdCardController::class, 'generateQrCode'])->name('id-cards.qrcode');
    Route::get('/id-cards/students/{student}/preview', [IdCardController::class, 'preview'])->name('id-cards.preview');
    Route::post('/id-cards/classes/{class}/batch', [IdCardController::class, 'batchGenerate'])->name('id-cards.batch');
    Route::post('/id-cards/export', [IdCardController::class, 'exportPdf'])->name('id-cards.export');
    Route::post('/id-cards/classes/{class}/export', [IdCardController::class, 'exportClassPdf'])->name('id-cards.export-class');

    // Reports (Requirements 17.1, 17.3, 17.4, 17.5)
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export.csv');
    Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
    Route::get('/reports/daily-summary', [ReportController::class, 'dailySummary'])->name('reports.daily-summary');

    // Student Placement (Requirements 19.1, 19.2, 19.3, 19.5)
    Route::get('/student-placements', [StudentPlacementController::class, 'index'])->name('student-placements.index');
    Route::get('/student-placements/{student}', [StudentPlacementController::class, 'show'])->name('student-placements.show');
    Route::post('/student-placements/transfer', [StudentPlacementController::class, 'transfer'])->name('student-placements.transfer');
    Route::post('/student-placements/place', [StudentPlacementController::class, 'place'])->name('student-placements.place');
    Route::post('/student-placements/bulk-place', [StudentPlacementController::class, 'bulkPlace'])->name('student-placements.bulk-place');
});

/*
|--------------------------------------------------------------------------
| Admin/Principal Routes (Requirement 11.1)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,principal'])->group(function () {
    // Teacher Monitoring (Requirement 11.1 - read-only for principals)
    Route::get('/teacher-monitoring', [TeacherMonitoringController::class, 'index'])->name('teacher-monitoring.index');
    Route::get('/teacher-monitoring/today', [TeacherMonitoringController::class, 'today'])->name('teacher-monitoring.today');
    Route::get('/teacher-monitoring/{teacher}', [TeacherMonitoringController::class, 'show'])->name('teacher-monitoring.show');
});

/*
|--------------------------------------------------------------------------
| Admin-Only Routes (Requirements 2.1, 7.1, 10.1)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->group(function () {
    // User Management (Requirement 2.1)
    Route::resource('users', UserController::class);
    Route::post('/users/{user}/reactivate', [UserController::class, 'reactivate'])->name('users.reactivate');
    Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

    // Time Schedule Management (Requirement 7.1)
    Route::resource('time-schedules', TimeScheduleController::class);
    Route::post('/time-schedules/{time_schedule}/activate', [TimeScheduleController::class, 'activate'])->name('time-schedules.activate');
    Route::get('/time-schedules-logs', [TimeScheduleController::class, 'logs'])->name('time-schedules.logs');

    // School Year Management (Requirement 10.1)
    Route::resource('school-years', SchoolYearController::class);
    Route::post('/school-years/{school_year}/activate', [SchoolYearController::class, 'activate'])->name('school-years.activate');
    Route::post('/school-years/{school_year}/lock', [SchoolYearController::class, 'lock'])->name('school-years.lock');
    Route::post('/school-years/{school_year}/unlock', [SchoolYearController::class, 'unlock'])->name('school-years.unlock');

    // Subscription Management (Requirements 20.1, 20.2, 20.3)
    Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::post('/subscriptions/grant', [SubscriptionController::class, 'grantPremium'])->name('subscriptions.grant');
    Route::post('/subscriptions/revoke', [SubscriptionController::class, 'revokePremium'])->name('subscriptions.revoke');

    // System Settings (Requirements 21.1, 21.2)
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
});

/*
|--------------------------------------------------------------------------
| Super Admin Routes (Multi-Tenancy Management)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    // Super Admin Dashboard
    Route::get('/', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

    // School Management
    Route::resource('schools', SuperAdminSchoolController::class);
    Route::post('/schools/{school}/reactivate', [SuperAdminSchoolController::class, 'reactivate'])->name('schools.reactivate');
});

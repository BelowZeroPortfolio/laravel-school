# Implementation Plan

## Phase 1: Foundation and Database

- [x] 1. Set up Laravel 11 project foundation









  - [x] 1.1 Configure database connection in .env for MySQL/SQLite


    - Set DB_CONNECTION, DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD
    - _Requirements: 15.1_
  - [x] 1.2 Install required packages (Laravel Reverb, Eris for property testing)





    - Run `composer require laravel/reverb` and `composer require --dev giorgiosironi/eris`
    - _Requirements: 13.1_
  - [x] 1.3 Configure session driver to use database




    - Update config/session.php driver to 'database'
    - _Requirements: 1.1_

- [x] 2. Create database migrations in dependency order






  - [x] 2.1 Create users table migration

    - Include: username, password, role enum, full_name, email, is_active, is_premium, premium_expires_at, last_login
    - _Requirements: 15.1, 15.2_

  - [x] 2.2 Create school_years table migration

    - Include: name, is_active, is_locked, start_date, end_date
    - _Requirements: 15.1, 10.1_

  - [x] 2.3 Create time_schedules table migration

    - Include: name, time_in, time_out, late_threshold_minutes, is_active, effective_date, created_by FK
    - _Requirements: 15.1, 7.1_
  - [x] 2.4 Create time_schedule_logs table migration


    - Include: schedule_id FK, action enum, changed_by FK, old_values JSON, new_values JSON, change_reason
    - _Requirements: 15.1, 7.2_

  - [x] 2.5 Create students table migration

    - Include: student_id, lrn, first_name, last_name, qrcode_path, photo_path, parent fields, is_active, sms_enabled
    - _Requirements: 15.1, 8.1_

  - [x] 2.6 Create classes table migration

    - Include: grade_level, section, teacher_id FK, school_year_id FK, is_active, max_capacity
    - Add unique constraint on (grade_level, section, school_year_id)
    - _Requirements: 15.1, 9.1_


  - [x] 2.7 Create student_classes pivot table migration


    - Include: student_id FK, class_id FK, enrolled_at, enrolled_by FK, is_active, enrollment_type enum, enrollment_status enum
    - _Requirements: 15.1, 8.4_
  - [x] 2.8 Create attendance table migration


    - Include: student_id FK, school_year_id FK, attendance_date, check_in_time, check_out_time, status enum, recorded_by FK
    - Add unique constraint on (student_id, attendance_date)

    - Add indexes on attendance_date, student_id
    - _Requirements: 15.1, 15.3, 15.4_
  - [x] 2.9 Create teacher_attendance table migration

    - Include: teacher_id FK, school_year_id FK, attendance_date, time_in, time_out, first_student_scan, attendance_status enum, late_status enum, time_rule_id FK
    - Add unique constraint on (teacher_id, attendance_date)
    - _Requirements: 15.1, 15.4_
  - [x] 2.10 Write property test for attendance uniqueness constraint

    - **Property 46: Attendance uniqueness per day**
    - **Validates: Requirements 15.4**

- [ ] 3. Checkpoint - Verify migrations run successfully
  - Ensure all tests pass, ask the user if questions arise.

## Phase 2: Eloquent Models

- [x] 4. Create Eloquent models with relationships





  - [x] 4.1 Create User model with relationships and scopes


    - Relationships: hasMany(ClassRoom), hasMany(TeacherAttendance), hasMany(TimeSchedule, 'created_by')
    - Scopes: scopeTeachers, scopeActive
    - Helpers: isAdmin(), isPrincipal(), isTeacher(), hasRole()
    - _Requirements: 2.1, 2.2_
  - [x] 4.2 Create SchoolYear model with relationships and scopes


    - Relationships: hasMany(ClassRoom), hasMany(Attendance), hasMany(TeacherAttendance)
    - Scopes: scopeActive
    - _Requirements: 10.1_
  - [x] 4.3 Create TimeSchedule model with relationships and scopes


    - Relationships: belongsTo(User, 'created_by'), hasMany(TimeScheduleLog), hasMany(TeacherAttendance, 'time_rule_id')
    - Scopes: scopeActive
    - _Requirements: 7.3, 7.5_
  - [x] 4.4 Create TimeScheduleLog model


    - Relationships: belongsTo(TimeSchedule), belongsTo(User, 'changed_by')
    - _Requirements: 7.1, 7.2_
  - [x] 4.5 Create Student model with relationships


    - Relationships: hasMany(Attendance), belongsToMany(ClassRoom) with pivot
    - _Requirements: 8.1_
  - [x] 4.6 Create ClassRoom model with relationships


    - Relationships: belongsTo(User, 'teacher_id'), belongsTo(SchoolYear), belongsToMany(Student) with pivot
    - _Requirements: 9.1, 9.2_
  - [x] 4.7 Create Attendance model with relationships and scopes


    - Relationships: belongsTo(Student), belongsTo(SchoolYear), belongsTo(User, 'recorded_by')
    - Scopes: scopeToday, scopeForSchoolYear, scopeForClass
    - _Requirements: 6.1, 10.3_
  - [x] 4.8 Create TeacherAttendance model with relationships and scopes


    - Relationships: belongsTo(User, 'teacher_id'), belongsTo(SchoolYear), belongsTo(TimeSchedule, 'time_rule_id')
    - Scopes: scopeToday, scopePending, scopeLate, scopeForSchoolYear
    - _Requirements: 3.1, 4.1, 5.1_
  - [x] 4.9 Write property test for single active school year invariant


    - **Property 35: Single active school year invariant**
    - **Validates: Requirements 10.1**
  - [x] 4.10 Write property test for single active time schedule invariant


    - **Property 26: Single active time schedule invariant**
    - **Validates: Requirements 7.3**

- [x] 5. Create model factories for testing


  - [x] 5.1 Create UserFactory with role states (admin, principal, teacher)
    - _Requirements: 2.1_
  - [x] 5.2 Create SchoolYearFactory with active state

    - _Requirements: 10.1_

  - [x] 5.3 Create TimeScheduleFactory with active state

    - _Requirements: 7.3_

  - [x] 5.4 Create StudentFactory

    - _Requirements: 8.1_

  - [x] 5.5 Create ClassRoomFactory

    - _Requirements: 9.1_

  - [x] 5.6 Create AttendanceFactory

    - _Requirements: 6.1_


  - [ ] 5.7 Create TeacherAttendanceFactory with status states
    - _Requirements: 3.1, 5.1_

- [ ] 6. Checkpoint - Verify models and factories work
  - Ensure all tests pass, ask the user if questions arise.

## Phase 3: Authentication and Role-Based Access


- [x] 7. Implement authentication system




  - [x] 7.1 Create CheckRole middleware


    - Admin bypasses all checks, Principal inherits teacher access
    - _Requirements: 2.1, 2.2, 2.3_
  - [x] 7.2 Register middleware in bootstrap/app.php


    - _Requirements: 2.1_
  - [x] 7.3 Create LoginController with teacher attendance hook


    - Override authenticated() to call TeacherAttendanceService::recordTimeIn()
    - _Requirements: 1.2, 3.1_
  - [x] 7.4 Create LogoutController with teacher attendance hook


    - Call TeacherAttendanceService::recordTimeOut() before logout
    - _Requirements: 1.3_
  - [x] 7.5 Write property test for admin bypasses role checks


    - **Property 4: Admin bypasses all role checks**
    - **Validates: Requirements 2.1**
  - [x] 7.6 Write property test for principal inherits teacher access

    - **Property 5: Principal inherits teacher access**
    - **Validates: Requirements 2.2**
  - [x] 7.7 Write property test for teacher denied admin routes

    - **Property 6: Teacher denied admin routes**
    - **Validates: Requirements 2.3**
  - [x] 7.8 Write property test for unauthenticated redirects

    - **Property 7: Unauthenticated users redirected**
    - **Validates: Requirements 2.4**

- [ ] 8. Checkpoint - Verify authentication works
  - Ensure all tests pass, ask the user if questions arise.

## Phase 4: Core Services - Teacher Attendance

- [x] 9. Implement TeacherAttendanceService



  - [x] 9.1 Implement recordTimeIn() method

    - Create/update teacher_attendance with pending status, associate with active school year
    - _Requirements: 1.2, 3.1, 3.2, 3.3_

  - [x] 9.2 Implement recordTimeOut() method
    - Update time_out for today's record

    - _Requirements: 1.3_
  - [x] 9.3 Implement recordFirstStudentScan() method
    - Set first_student_scan, lock time_rule_id, call finalizeAttendance()
    - _Requirements: 4.1, 4.2, 4.3_

  - [x] 9.4 Implement finalizeAttendance() method
    - Apply late determination logic using locked time_rule_id
    - _Requirements: 5.1, 5.2, 5.3, 5.4_

  - [x] 9.5 Implement markAbsentTeachers() method
    - Create absent records for teachers without attendance today

    - _Requirements: 12.1, 12.3_
  - [x] 9.6 Implement markNoScanTeachers() method
    - Update pending records to no_scan status

    - _Requirements: 12.2_
  - [x] 9.7 Write property test for teacher login creates pending attendance

    - **Property 1: Teacher login creates pending attendance**
    - **Validates: Requirements 1.2, 3.1**
  - [x] 9.8 Write property test for multiple logins update existing record

    - **Property 8: Multiple logins update existing record**
    - **Validates: Requirements 3.2**
  - [x] 9.9 Write property test for login does not evaluate lateness

    - **Property 10: Login does not evaluate lateness**
    - **Validates: Requirements 3.4**
  - [x] 9.10 Write property test for first scan locks time_rule_id

    - **Property 12: First scan locks time_rule_id**
    - **Validates: Requirements 4.2**

  - [x] 9.11 Write property test for subsequent scans preserve first_student_scan
    - **Property 14: Subsequent scans preserve first_student_scan**
    - **Validates: Requirements 4.4**
  - [x] 9.12 Write property test for late determination logic

    - **Property 15: Late determination logic**
    - **Validates: Requirements 5.1, 5.2, 5.3**
  - [x] 9.13 Write property test for historical records immutable on rule change


    - **Property 17: Historical records immutable on rule change**
    - **Validates: Requirements 5.5**

- [ ] 10. Checkpoint - Verify teacher attendance service
  - Ensure all tests pass, ask the user if questions arise.

## Phase 5: Core Services - Student Attendance

- [x] 11. Implement StudentAttendanceService
  - [x] 11.1 Implement findStudentByQRCode() method
    - Search by LRN first, then by student_id
    - _Requirements: 6.2_
  - [x] 11.2 Implement hasAttendanceToday() method
    - Check for existing attendance record
    - _Requirements: 6.3_
  - [x] 11.3 Implement recordAttendance() method
    - Create attendance record, auto-calculate late status, trigger teacher Phase 2
    - _Requirements: 6.1, 6.4, 6.5_
  - [x] 11.4 Implement processQRCodeScan() method
    - Main entry point combining lookup, duplicate check, and recording
    - _Requirements: 6.1, 6.2, 6.3, 6.6_
  - [x] 11.5 Implement calculateLateStatus() method
    - Compare check_in_time against active schedule cutoff
    - _Requirements: 6.4_
  - [x] 11.6 Write property test for valid QR code creates attendance
    - **Property 18: Valid QR code creates attendance**
    - **Validates: Requirements 6.1**
  - [x] 11.7 Write property test for QR code lookup priority
    - **Property 19: QR code lookup priority**
    - **Validates: Requirements 6.2**
  - [x] 11.8 Write property test for duplicate scan prevention
    - **Property 20: Duplicate scan prevention**
    - **Validates: Requirements 6.3**
  - [x] 11.9 Write property test for auto-calculate student late status
    - **Property 21: Auto-calculate student late status**
    - **Validates: Requirements 6.4**
  - [x] 11.10 Write property test for student scan triggers teacher Phase 2
    - **Property 22: Student scan triggers teacher Phase 2**
    - **Validates: Requirements 6.5**

- [ ] 12. Checkpoint - Verify student attendance service
  - Ensure all tests pass, ask the user if questions arise.

## Phase 6: Core Services - Time Schedule

- [x] 13. Implement TimeScheduleService




  - [x] 13.1 Implement getActive() method


    - Return currently active schedule
    - _Requirements: 7.5_

  - [x] 13.2 Implement create() method with audit logging
    - Create schedule and log to time_schedule_logs

    - _Requirements: 7.1_
  - [x] 13.3 Implement update() method with audit logging
    - Update schedule and log old/new values

    - _Requirements: 7.2_
  - [x] 13.4 Implement activate() method

    - Deactivate all others, activate specified, log action
    - _Requirements: 7.3_
  - [x] 13.5 Implement delete() method with active check
    - Prevent deletion of active schedule
    - _Requirements: 7.4_

  - [x] 13.6 Write property test for time schedule creation logged

    - **Property 24: Time schedule creation logged**
    - **Validates: Requirements 7.1**
  - [x] 13.7 Write property test for time schedule update logged with values

    - **Property 25: Time schedule update logged with values**
    - **Validates: Requirements 7.2**
  - [x] 13.8 Write property test for active schedule deletion prevented

    - **Property 27: Active schedule deletion prevented**
    - **Validates: Requirements 7.4**

- [ ] 14. Checkpoint - Verify time schedule service
  - Ensure all tests pass, ask the user if questions arise.

## Phase 7: Event Broadcasting

- [x] 15. Implement WebSocket events





  - [x] 15.1 Create StudentScanned event


    - Broadcast to attendance.{school_year_id} channel
    - _Requirements: 13.1_
  - [x] 15.2 Create TeacherLoggedIn event


    - Broadcast to teacher-monitoring.{school_year_id} channel
    - _Requirements: 13.2_
  - [x] 15.3 Create AttendanceFinalized event


    - Broadcast when teacher attendance status changes from pending
    - _Requirements: 13.3_
  - [x] 15.4 Integrate events into services


    - Dispatch events from TeacherAttendanceService and StudentAttendanceService
    - _Requirements: 13.1, 13.2, 13.3_
  - [x] 15.5 Write property test for student scan broadcasts event


    - **Property 42: Student scan broadcasts event**
    - **Validates: Requirements 13.1**
  - [x] 15.6 Write property test for teacher login broadcasts event


    - **Property 43: Teacher login broadcasts event**
    - **Validates: Requirements 13.2**

- [ ] 16. Checkpoint - Verify event broadcasting
  - Ensure all tests pass, ask the user if questions arise.

## Phase 8: Controllers

- [x] 17. Create controllers




  - [x] 17.1 Create DashboardController


    - Display role-appropriate dashboard
    - _Requirements: 1.1_
  - [x] 17.2 Create ScanController


    - Handle QR code scanning for attendance
    - _Requirements: 6.1, 6.2, 6.3_
  - [x] 17.3 Create StudentController with role-based filtering


    - Teachers see only their students, admins see all
    - _Requirements: 8.2, 8.3_
  - [x] 17.4 Create ClassController with role-based filtering


    - Teachers see only their classes
    - _Requirements: 9.3_
  - [x] 17.5 Create AttendanceController


    - View and filter attendance records
    - _Requirements: 6.1_
  - [x] 17.6 Create TeacherMonitoringController (read-only for principals)


    - Display teacher attendance with filters and statistics
    - _Requirements: 11.1, 11.2, 11.3_
  - [x] 17.7 Create TimeScheduleController (admin only)


    - CRUD operations with audit logging
    - _Requirements: 7.1, 7.2, 7.3, 7.4_
  - [x] 17.8 Create UserController (admin only)


    - User management
    - _Requirements: 2.1_
  - [x] 17.9 Create SchoolYearController (admin only)


    - School year management with single-active enforcement
    - _Requirements: 10.1, 10.2_
  - [x] 17.10 Write property test for role-based data visibility


    - **Property 29: Role-based data visibility**
    - **Validates: Requirements 8.2, 9.3**
  - [x] 17.11 Write property test for attendance statistics accuracy


    - **Property 38: Attendance statistics accuracy**
    - **Validates: Requirements 11.3**

- [ ] 18. Checkpoint - Verify controllers work
  - Ensure all tests pass, ask the user if questions arise.

## Phase 9: Scheduled Tasks
-

- [x] 19. Implement scheduled commands



  - [x] 19.1 Create MarkAbsentTeachersCommand


    - Schedule for 17:30 daily
    - _Requirements: 12.1_
  - [x] 19.2 Create MarkNoScanTeachersCommand


    - Schedule for 18:00 daily
    - _Requirements: 12.2_

  - [x] 19.3 Register commands in console/Kernel.php or routes/console.php

    - _Requirements: 12.1, 12.2_
  - [x] 19.4 Write property test for mark absent teachers


    - **Property 39: Mark absent teachers creates correct records**
    - **Validates: Requirements 12.1, 12.3**
  - [x] 19.5 Write property test for mark no_scan teachers


    - **Property 40: Mark no_scan updates pending records**
    - **Validates: Requirements 12.2**

- [ ] 20. Checkpoint - Verify scheduled tasks
  - Ensure all tests pass, ask the user if questions arise.

## Phase 10: Blade Views and UI

- [x] 21. Create layout and components





  - [x] 21.1 Create app layout with sidebar and header


    - Include theme toggle, school year selector, user menu
    - _Requirements: 14.1, 10.4_

  - [x] 21.2 Create sidebar component with role-based menu items
    - Show/hide items based on user role
    - _Requirements: 2.5_
  - [x] 21.3 Create theme system (CSS variables, JavaScript toggle)


    - Dark/light mode with localStorage persistence
    - _Requirements: 14.1, 14.2, 14.3, 14.4_
  - [x] 21.4 Create reusable Blade components (alert, badge, card)


    - _Requirements: 14.1_

- [x] 22. Create page views




  - [x] 22.1 Create login view


    - _Requirements: 1.1, 1.4_


  - [x] 22.2 Create dashboard view


    - _Requirements: 1.1_
  - [x] 22.3 Create scan view with QR code input

    - _Requirements: 6.1_



  - [-] 22.4 Create students index/create/edit views

    - _Requirements: 8.1, 8.2, 8.3_


  - [-] 22.5 Create classes index/create/edit views


    - _Requirements: 9.1, 9.2, 9.3_

  - [-] 22.6 Create attendance view

    - _Requirements: 6.1_
  - [x] 22.7 Create teacher monitoring view (read-only)


    - _Requirements: 11.1, 11.2, 11.3_


  - [x] 22.8 Create time schedules index/create/edit views













    - _Requirements: 7.1, 7.2, 7.3_

  - [x] 22.9 Create users index/create/edit views


    - _Requirements: 2.1_
  - [ ] 22.10 Create school years index/create views
    - _Requirements: 10.1_

- [ ] 23. Integrate Laravel Echo for real-time updates

  - [ ] 23.1 Configure Laravel Echo with Reverb
    - _Requirements: 13.1_
  - [ ] 23.2 Add real-time listeners to dashboard
    - _Requirements: 13.1_
  - [ ] 23.3 Add real-time listeners to teacher monitoring
    - _Requirements: 11.4, 13.3_

- [x] 24. Checkpoint - Verify UI works



  - Ensure all tests pass, ask the user if questions arise.

## Phase 11: Policies and Authorization


- [x] 25. Create authorization policies

  - [x] 25.1 Create StudentPolicy
    - Teachers can only view/edit students in their classes
    - _Requirements: 8.2_

  - [x] 25.2 Create ClassRoomPolicy
    - Teachers can only view their classes
    - _Requirements: 9.3_

  - [x] 25.3 Create TimeSchedulePolicy
    - Only admin can manage
    - _Requirements: 7.1_

  - [x] 25.4 Create SchoolYearPolicy
    - Only admin can manage
    - _Requirements: 10.1_

  - [x] 25.5 Register policies in AuthServiceProvider

    - _Requirements: 2.1_

- [ ] 26. Checkpoint - Verify policies work
  - Ensure all tests pass, ask the user if questions arise.

## Phase 12: Routes

- [x] 27. Define application routes




  - [x] 27.1 Define authentication routes


    - Login, logout
    - _Requirements: 1.1, 1.2, 1.3_


  - [x] 27.2 Define admin-only routes with middleware


    - Users, time schedules, school years






    - _Requirements: 2.1, 7.1, 10.1_
  - [ ] 27.3 Define admin/principal routes
    - Teacher monitoring
    - _Requirements: 11.1_
  - [ ] 27.4 Define authenticated routes
    - Dashboard, scan, students, classes, attendance
    - _Requirements: 6.1, 8.1, 9.1_

- [ ] 28. Checkpoint - Verify routes work
  - Ensure all tests pass, ask the user if questions arise.

## Phase 13: ID Card Generation

- [x] 29. Implement IdCardService






  - [x] 29.1 Implement generateQRCode() method

    - Generate QR code containing LRN (if present) or student_id
    - Store QR code image and update student's qrcode_path
    - _Requirements: 16.1, 16.4_


  - [x] 29.2 Implement generateIdCard() method

    - Compile student data including photo, name, grade, section, school year

    - _Requirements: 16.2_
  - [x] 29.3 Implement generateBatchIdCards() method

    - Generate ID cards for all enrolled students in a class
    - _Requirements: 16.3_

  - [x] 29.4 Implement exportToPdf() method

    - Generate printable PDF with ID card layout
    - _Requirements: 16.5_
  - [ ]* 29.5 Write property test for QR code contains student identifier
    - **Property 47: QR code contains student identifier**
    - **Validates: Requirements 16.1**
  - [ ]* 29.6 Write property test for ID card contains required fields
    - **Property 48: ID card contains required fields**
    - **Validates: Requirements 16.2**
  - [ ]* 29.7 Write property test for batch generation covers all enrolled students
    - **Property 49: Batch generation covers all enrolled students**
    - **Validates: Requirements 16.3**

- [x] 30. Create IdCardController




  - [x] 30.1 Create generate action for single student


    - _Requirements: 16.1, 16.2_










  - [ ] 30.2 Create batch generate action for class
    - _Requirements: 16.3_
  - [ ] 30.3 Create export PDF action
    - _Requirements: 16.5_



- [ ] 31. Create ID card views

  - [ ] 31.1 Create generate ID cards page with student/class selection
    - _Requirements: 16.1, 16.3_
  - [ ] 31.2 Create ID card preview component
    - _Requirements: 16.2_

- [ ] 32. Checkpoint - Verify ID card generation
  - Ensure all tests pass, ask the user if questions arise.

## Phase 14: Reports

- [x] 33. Implement ReportService



  - [x] 33.1 Implement getAttendanceReport() method


    - Filter by date range, class, student with pagination
    - _Requirements: 17.1_
  - [x] 33.2 Implement calculateStatistics() method


    - Calculate present, late, absent counts and percentages
    - _Requirements: 17.2_
  - [x] 33.3 Implement getDailySummary() method


    - Get per-class attendance summary for a date
    - _Requirements: 17.5_
  - [x] 33.4 Implement exportToCsv() method


    - Export attendance records to CSV format
    - _Requirements: 17.4_
  - [x] 33.5 Implement exportToPdf() method


    - Export attendance report to PDF format
    - _Requirements: 17.4_
  - [ ]* 33.6 Write property test for report filtering returns matching records
    - **Property 51: Report filtering returns matching records**
    - **Validates: Requirements 17.1**
  - [ ]* 33.7 Write property test for statistics calculation accuracy
    - **Property 52: Statistics calculation accuracy**
    - **Validates: Requirements 17.2, 17.5**
  - [ ]* 33.8 Write property test for teacher report data visibility
    - **Property 53: Teacher report data visibility**
    - **Validates: Requirements 17.3**
-

- [x] 34. Create ReportController




  - [x] 34.1 Create index action with filters


    - _Requirements: 17.1, 17.3_

  - [x] 34.2 Create export actions for CSV and PDF

    - _Requirements: 17.4_
  - [x] 34.3 Create daily summary action
    - _Requirements: 17.5_


- [x] 35. Create report views



  - [x] 35.1 Create reports index page with filters and statistics


    - _Requirements: 17.1, 17.2_
  - [x] 35.2 Create daily summary view


    - _Requirements: 17.5_

- [ ] 36. Checkpoint - Verify reports
  - Ensure all tests pass, ask the user if questions arise.

## Phase 15: Attendance Management Page

- [x] 37. Enhance AttendanceController





  - [x] 37.1 Implement index with date filter defaulting to today


    - _Requirements: 18.1_
  - [x] 37.2 Implement filtering by date, class, status, student name

    - _Requirements: 18.2_
  - [x] 37.3 Implement manual attendance marking with audit trail

    - _Requirements: 18.4_
  - [x] 37.4 Implement attendance history view

    - _Requirements: 18.5_
  - [ ]* 37.5 Write property test for attendance filtering
    - **Property 54: Attendance filtering returns matching records**
    - **Validates: Requirements 18.2**
  - [ ]* 37.6 Write property test for manual attendance audit trail
    - **Property 55: Manual attendance records audit trail**
    - **Validates: Requirements 18.4**

- [x] 38. Create attendance views





  - [x] 38.1 Create attendance index page with filters


    - _Requirements: 18.1, 18.2_
  - [x] 38.2 Create attendance history view with full record details


    - _Requirements: 18.5_
  - [x] 38.3 Create manual attendance marking modal/form


    - _Requirements: 18.4_

- [ ] 39. Checkpoint - Verify attendance management
  - Ensure all tests pass, ask the user if questions arise.

## Phase 16: Student Placement

- [x] 40. Implement StudentPlacementService




  - [x] 40.1 Implement transferStudent() method


    - Update old enrollment to 'transferred_out', create new enrollment
    - _Requirements: 19.1, 19.2_
  - [x] 40.2 Implement placeStudent() method


    - Create new student_classes record with enrollment metadata
    - _Requirements: 19.2, 19.4_
  - [x] 40.3 Implement bulkPlaceStudents() method


    - Place multiple students in a target class
    - _Requirements: 19.3_
  - [x] 40.4 Implement getPlacementHistory() method


    - Get all enrollments for a student across school years
    - _Requirements: 19.5_
  - [ ]* 40.5 Write property test for transfer updates enrollment status
    - **Property 57: Transfer updates enrollment status**
    - **Validates: Requirements 19.1, 19.2**
  - [ ]* 40.6 Write property test for bulk placement creates enrollments
    - **Property 58: Bulk placement creates enrollments for all students**
    - **Validates: Requirements 19.3**
  - [ ]* 40.7 Write property test for placement audit trail
    - **Property 59: Placement audit trail completeness**
    - **Validates: Requirements 19.4**


- [x] 41. Create StudentPlacementController







  - [x] 41.1 Create index action showing students and placements

    - _Requirements: 19.5_
  - [x] 41.2 Create transfer action

    - _Requirements: 19.1_
  - [x] 41.3 Create bulk placement action

    - _Requirements: 19.3_

-

- [x] 42. Create student placement views










  - [ ] 42.1 Create placement index page with student list
    - _Requirements: 19.5_
  - [ ] 42.2 Create transfer modal/form
    - _Requirements: 19.1_
  - [ ] 42.3 Create bulk placement page with student selection
    - _Requirements: 19.3_






- [x] 43. Checkpoint - Verify student placement

  - Ensure all tests pass, ask the user if questions arise.


## Phase 17: Subscription Management



- [ ] 44. Implement SubscriptionService


  - [ ] 44.1 Implement getTeachersWithSubscriptions() method
    - Get all teachers with premium status and expiration
    - _Requirements: 20.1_
  - [ ] 44.2 Implement grantPremium() method
    - Set is_premium=true and premium_expires_at
    - _Requirements: 20.2_
  - [ ] 44.3 Implement revokePremium() method
    - Set is_premium=false
    - _Requirements: 20.3_







  - [x] 44.4 Implement isPremiumActive() method

    - Check is_premium and premium_expires_at
    - _Requirements: 20.4_
  - [ ]* 44.5 Write property test for subscription list includes all teachers
    - **Property 61: Subscription list includes all teachers**
    - **Validates: Requirements 20.1**
  - [ ]* 44.6 Write property test for premium status management
    - **Property 62: Premium status management**
    - **Validates: Requirements 20.2, 20.3**
  - [ ]* 44.7 Write property test for premium expiration enforcement
    - **Property 63: Premium expiration enforcement**

    - **Validates: Requirements 20.4**







- [x] 45. Create SubscriptionController








  - [x] 45.1 Create index action showing all teachers with status


    - _Requirements: 20.1_


  - [ ] 45.2 Create grant premium action
    - _Requirements: 20.2_
  - [ ] 45.3 Create revoke premium action

    - _Requirements: 20.3_










- [ ] 46. Create subscription views
  - [ ] 46.1 Create subscriptions index page with teacher list
    - _Requirements: 20.1, 20.5_
  - [ ] 46.2 Create grant/revoke premium modals
    - _Requirements: 20.2, 20.3_

- [ ] 47. Checkpoint - Verify subscription management
  - Ensure all tests pass, ask the user if questions arise.

## Phase 18: System Settings

- [ ] 48. Create settings database migration

  - [ ] 48.1 Create settings table migration
    - Include: key, value (JSON), group, created_at, updated_at
    - _Requirements: 21.1_
  - [ ] 48.2 Create settings_logs table migration
    - Include: setting_key, old_value, new_value, changed_by, created_at
    - _Requirements: 21.5_

- [ ] 49. Implement SettingsService

  - [ ] 49.1 Implement get() and set() methods
    - Get/set individual settings with caching
    - _Requirements: 21.2_
  - [ ] 49.2 Implement getAll() and getGroup() methods
    - Get all settings or by group (school, sms, attendance)
    - _Requirements: 21.1, 21.3, 21.4_
  - [ ] 49.3 Implement settings change logging
    - Log all changes with user and timestamp
    - _Requirements: 21.5_
  - [ ]* 49.4 Write property test for settings persistence
    - **Property 64: Settings persistence and retrieval**
    - **Validates: Requirements 21.2, 21.3, 21.4**
  - [ ]* 49.5 Write property test for settings change audit logging
    - **Property 65: Settings change audit logging**
    - **Validates: Requirements 21.5**

- [ ] 50. Create SettingsController

  - [ ] 50.1 Create index action showing all settings
    - _Requirements: 21.1_
  - [ ] 50.2 Create update action for settings
    - _Requirements: 21.2_

- [ ] 51. Create settings views
  - [ ] 51.1 Create settings index page with grouped settings
    - _Requirements: 21.1_
  - [ ] 51.2 Create school settings section (name, logo, contact)
    - _Requirements: 21.1_
  - [ ] 51.3 Create SMS settings section
    - _Requirements: 21.3_
  - [ ] 51.4 Create attendance settings section
    - _Requirements: 21.4_

- [ ] 52. Checkpoint - Verify settings
  - Ensure all tests pass, ask the user if questions arise.

## Phase 19: Final Integration

- [ ] 53. Update routes for new features
  - [ ] 53.1 Add ID card generation routes (admin, teacher)
    - _Requirements: 16.1_
  - [ ] 53.2 Add reports routes (all authenticated)
    - _Requirements: 17.1_
  - [ ] 53.3 Add student placement routes (admin only)
    - _Requirements: 19.1_
  - [ ] 53.4 Add subscription routes (admin only)
    - _Requirements: 20.1_
  - [ ] 53.5 Add settings routes (admin only)
    - _Requirements: 21.1_

- [ ] 54. Update sidebar navigation
  - [ ] 54.1 Add Generate ID Cards menu item
    - _Requirements: 16.1_
  - [ ] 54.2 Add Reports menu item
    - _Requirements: 17.1_
  - [ ] 54.3 Add Student Placement menu item (admin only)
    - _Requirements: 19.1_
  - [ ] 54.4 Add Subscriptions menu item (admin only)
    - _Requirements: 20.1_
  - [ ] 54.5 Add Settings menu item (admin only)
    - _Requirements: 21.1_

- [ ] 55. Final Checkpoint - Complete system verification
  - Ensure all tests pass, ask the user if questions arise.

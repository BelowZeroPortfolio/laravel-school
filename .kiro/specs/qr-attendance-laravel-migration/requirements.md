# Requirements Document

## Introduction

This specification defines the requirements for migrating an existing PHP-based QR Attendance System to Laravel 11. The system manages student and teacher attendance tracking for a school environment with approximately 3000 students, 100 teachers, and 1 principal. The migration preserves all existing business logic, particularly the two-phase teacher attendance model and strict late determination rules, while modernizing the codebase with Laravel conventions, Eloquent ORM, and real-time WebSocket updates.

## Glossary

- **QR_Attendance_System**: The Laravel 11 web application for tracking student and teacher attendance via QR code scanning
- **Teacher**: A user with role 'teacher' who manages classes and has attendance tracked via the two-phase model
- **Principal**: A user with role 'principal' who has read-only monitoring access plus most teacher features
- **Admin**: A user with role 'admin' who has full system access including user and settings management
- **Two-Phase Attendance**: The teacher attendance model where Phase 1 (login) records intent and Phase 2 (first student scan) confirms presence
- **Time_Schedule**: A configurable rule defining time_in, time_out, and late_threshold_minutes for attendance evaluation
- **School_Year**: An academic year period that scopes attendance records and class assignments
- **LRN**: Learner Reference Number - a unique 12-digit identifier for students
- **Cutoff_Time**: The calculated time (time_in_rule + late_threshold_minutes) used to determine lateness

## Requirements

### Requirement 1: User Authentication and Session Management

**User Story:** As a user, I want to securely log in and out of the system, so that I can access features appropriate to my role while maintaining session security.

#### Acceptance Criteria

1. WHEN a user submits valid credentials THEN the QR_Attendance_System SHALL create an authenticated session and redirect to the dashboard
2. WHEN a teacher logs in THEN the QR_Attendance_System SHALL automatically create a teacher_attendance record with time_in set to the current timestamp and attendance_status set to 'pending'
3. WHEN a teacher logs out THEN the QR_Attendance_System SHALL update the teacher_attendance record with time_out set to the current timestamp before destroying the session
4. WHEN a user submits invalid credentials THEN the QR_Attendance_System SHALL display an error message and maintain the unauthenticated state
5. WHEN a session expires or is invalidated THEN the QR_Attendance_System SHALL redirect the user to the login page

### Requirement 2: Role-Based Access Control

**User Story:** As a system administrator, I want to enforce role-based permissions, so that users can only access features appropriate to their role level.

#### Acceptance Criteria

1. WHEN an admin user accesses any route THEN the QR_Attendance_System SHALL grant access regardless of route-specific role requirements
2. WHEN a principal user accesses teacher-level routes THEN the QR_Attendance_System SHALL grant access to those routes
3. WHEN a teacher user accesses admin-only routes THEN the QR_Attendance_System SHALL deny access and return a 403 response
4. WHEN an unauthenticated user accesses protected routes THEN the QR_Attendance_System SHALL redirect to the login page
5. WHEN rendering navigation elements THEN the QR_Attendance_System SHALL display only menu items appropriate to the user's role

### Requirement 3: Teacher Attendance - Phase 1 (Intent Recording)

**User Story:** As a teacher, I want my attendance intent recorded when I log in, so that the system tracks when I arrived at school.

#### Acceptance Criteria

1. WHEN a teacher logs in for the first time on a given day THEN the QR_Attendance_System SHALL create a new teacher_attendance record with attendance_status 'pending'
2. WHEN a teacher logs in and a pending record exists for today THEN the QR_Attendance_System SHALL update the existing record's time_in timestamp
3. WHEN recording teacher time_in THEN the QR_Attendance_System SHALL associate the record with the active school_year_id
4. WHEN a teacher logs in THEN the QR_Attendance_System SHALL NOT evaluate lateness until Phase 2 confirmation

### Requirement 4: Teacher Attendance - Phase 2 (Presence Confirmation)

**User Story:** As a teacher, I want my presence confirmed when my first student scans their QR code, so that the system accurately records when I began class activities.

#### Acceptance Criteria

1. WHEN the first student in a teacher's class scans their QR code THEN the QR_Attendance_System SHALL record the first_student_scan timestamp on the teacher's attendance record
2. WHEN recording first_student_scan THEN the QR_Attendance_System SHALL lock the time_rule_id to the currently active Time_Schedule
3. WHEN first_student_scan is recorded THEN the QR_Attendance_System SHALL invoke the late determination logic to finalize attendance_status
4. WHEN a student scans and first_student_scan already exists for the teacher today THEN the QR_Attendance_System SHALL NOT update the first_student_scan timestamp

### Requirement 5: Late Determination Logic

**User Story:** As a principal, I want accurate late status calculations for teachers, so that I can monitor punctuality based on established time rules.

#### Acceptance Criteria

1. WHEN teacher_time_in exceeds Cutoff_Time THEN the QR_Attendance_System SHALL set attendance_status to 'late' and late_status to 'late'
2. WHEN first_student_scan exceeds Cutoff_Time THEN the QR_Attendance_System SHALL set attendance_status to 'late' and late_status to 'late'
3. WHEN both teacher_time_in and first_student_scan are at or before Cutoff_Time THEN the QR_Attendance_System SHALL set attendance_status to 'confirmed' and late_status to 'on_time'
4. WHEN evaluating lateness THEN the QR_Attendance_System SHALL use the time_rule_id locked at first_student_scan, not the currently active schedule
5. WHEN time schedule rules change THEN the QR_Attendance_System SHALL NOT recalculate historical attendance records

### Requirement 6: Student QR Code Scanning

**User Story:** As a teacher, I want to scan student QR codes to record their attendance, so that I can efficiently track which students are present.

#### Acceptance Criteria

1. WHEN a valid student QR code is scanned THEN the QR_Attendance_System SHALL create an attendance record with the current timestamp
2. WHEN scanning a QR code THEN the QR_Attendance_System SHALL search by LRN first, then by student_id
3. WHEN a student has already scanned today THEN the QR_Attendance_System SHALL reject the duplicate scan and display an appropriate message
4. WHEN recording student attendance THEN the QR_Attendance_System SHALL auto-calculate late status using the active Time_Schedule
5. WHEN a student scans and their teacher has a pending attendance record THEN the QR_Attendance_System SHALL trigger the teacher's first_student_scan recording
6. WHEN an invalid QR code is scanned THEN the QR_Attendance_System SHALL display an error message without creating any records

### Requirement 7: Time Schedule Management

**User Story:** As an admin, I want to manage time schedules with audit logging, so that I can configure attendance rules while maintaining a history of changes.

#### Acceptance Criteria

1. WHEN an admin creates a new Time_Schedule THEN the QR_Attendance_System SHALL log the creation action with the creator's user_id
2. WHEN an admin updates a Time_Schedule THEN the QR_Attendance_System SHALL log old_values, new_values, and change_reason to time_schedule_logs
3. WHEN an admin activates a Time_Schedule THEN the QR_Attendance_System SHALL deactivate all other schedules and log the activation
4. WHEN an admin attempts to delete an active Time_Schedule THEN the QR_Attendance_System SHALL prevent deletion and display an error message
5. WHILE only one Time_Schedule is active THEN the QR_Attendance_System SHALL use that schedule for all attendance calculations

### Requirement 8: Student Management

**User Story:** As a teacher, I want to manage students in my classes, so that I can maintain accurate class rosters.

#### Acceptance Criteria

1. WHEN a user creates a new student THEN the QR_Attendance_System SHALL generate a unique student_id and validate LRN uniqueness
2. WHEN a teacher views students THEN the QR_Attendance_System SHALL display only students enrolled in the teacher's classes
3. WHEN an admin views students THEN the QR_Attendance_System SHALL display all students with filtering options
4. WHEN enrolling a student in a class THEN the QR_Attendance_System SHALL record enrollment_type, enrolled_by, and enrolled_at
5. WHEN a class reaches max_capacity THEN the QR_Attendance_System SHALL prevent additional enrollments

### Requirement 9: Class Management

**User Story:** As an admin, I want to manage classes and assign teachers, so that the system reflects the school's organizational structure.

#### Acceptance Criteria

1. WHEN creating a class THEN the QR_Attendance_System SHALL enforce uniqueness of grade_level, section, and school_year_id combination
2. WHEN assigning a teacher to a class THEN the QR_Attendance_System SHALL validate that the user has role 'teacher'
3. WHEN a teacher views classes THEN the QR_Attendance_System SHALL display only classes assigned to that teacher
4. WHEN deactivating a class THEN the QR_Attendance_System SHALL preserve historical attendance records associated with that class

### Requirement 10: School Year Management

**User Story:** As an admin, I want to manage school years, so that attendance records are properly scoped to academic periods.

#### Acceptance Criteria

1. WHEN an admin activates a school year THEN the QR_Attendance_System SHALL deactivate all other school years
2. WHEN a school year is locked THEN the QR_Attendance_System SHALL prevent modifications to attendance records within that year
3. WHEN creating attendance records THEN the QR_Attendance_System SHALL associate them with the active school_year_id
4. WHEN displaying school year selector THEN the QR_Attendance_System SHALL show the active year with a visual badge indicator

### Requirement 11: Teacher Monitoring Dashboard

**User Story:** As a principal, I want to monitor teacher attendance in real-time, so that I can oversee staff punctuality without modification capabilities.

#### Acceptance Criteria

1. WHEN a principal accesses teacher monitoring THEN the QR_Attendance_System SHALL display attendance records in read-only mode
2. WHEN filtering teacher attendance THEN the QR_Attendance_System SHALL support filters for teacher_id, school_year_id, date range, and attendance_status
3. WHEN displaying teacher attendance THEN the QR_Attendance_System SHALL show statistics for confirmed, late, pending, and absent counts
4. WHEN a teacher's attendance status changes THEN the QR_Attendance_System SHALL broadcast the update via WebSocket for real-time display

### Requirement 12: End-of-Day Attendance Processing

**User Story:** As a system administrator, I want automated end-of-day processing, so that teacher attendance records are properly finalized.

#### Acceptance Criteria

1. WHEN the scheduled time of 17:30 is reached THEN the QR_Attendance_System SHALL mark teachers without any attendance record as 'absent'
2. WHEN the scheduled time of 18:00 is reached THEN the QR_Attendance_System SHALL update teachers with 'pending' status to 'no_scan'
3. WHEN marking teachers absent THEN the QR_Attendance_System SHALL create records only for teachers who never logged in that day
4. WHEN processing end-of-day tasks THEN the QR_Attendance_System SHALL scope operations to the active school_year_id

### Requirement 13: Real-Time Updates via WebSocket

**User Story:** As a user, I want to see attendance updates in real-time, so that dashboards reflect current status without manual refresh.

#### Acceptance Criteria

1. WHEN a student scans their QR code THEN the QR_Attendance_System SHALL broadcast a StudentScanned event to relevant channels
2. WHEN a teacher logs in THEN the QR_Attendance_System SHALL broadcast a TeacherLoggedIn event to the teacher-monitoring channel
3. WHEN a teacher's attendance is finalized THEN the QR_Attendance_System SHALL broadcast an AttendanceFinalized event with status details
4. WHEN subscribing to channels THEN the QR_Attendance_System SHALL scope subscriptions by school_year_id

### Requirement 14: UI Theme System

**User Story:** As a user, I want to switch between light and dark themes, so that I can use the interface comfortably in different lighting conditions.

#### Acceptance Criteria

1. WHEN a user toggles the theme THEN the QR_Attendance_System SHALL apply the selected theme immediately without page reload
2. WHEN a user selects a theme THEN the QR_Attendance_System SHALL persist the preference in localStorage
3. WHEN a user visits a public page without saved preference THEN the QR_Attendance_System SHALL default to dark theme
4. WHEN a user visits an authenticated page without saved preference THEN the QR_Attendance_System SHALL default to light theme

### Requirement 15: Database Migration and Data Integrity

**User Story:** As a system administrator, I want the database schema properly migrated, so that all existing data relationships and constraints are preserved.

#### Acceptance Criteria

1. WHEN running migrations THEN the QR_Attendance_System SHALL create tables in dependency order to satisfy foreign key constraints
2. WHEN defining foreign keys THEN the QR_Attendance_System SHALL implement appropriate cascade or nullify behaviors as specified in the migration plan
3. WHEN creating indexes THEN the QR_Attendance_System SHALL optimize for frequently queried columns including attendance_date, student_id, and teacher_id
4. WHEN enforcing uniqueness THEN the QR_Attendance_System SHALL prevent duplicate attendance records per student per day and per teacher per day

### Requirement 16: Student ID Card Generation

**User Story:** As an admin or teacher, I want to generate ID cards with QR codes for students, so that students can use them for attendance scanning.

#### Acceptance Criteria

1. WHEN an admin or teacher requests ID card generation for a student THEN the QR_Attendance_System SHALL generate a QR code containing the student's LRN or student_id
2. WHEN generating an ID card THEN the QR_Attendance_System SHALL include student photo, full name, grade level, section, and school year
3. WHEN generating multiple ID cards THEN the QR_Attendance_System SHALL support batch generation for an entire class
4. WHEN an ID card is generated THEN the QR_Attendance_System SHALL store the QR code path in the student's qrcode_path field
5. WHEN exporting ID cards THEN the QR_Attendance_System SHALL provide PDF format suitable for printing

### Requirement 17: Attendance Reports

**User Story:** As an admin, principal, or teacher, I want to view and export attendance reports, so that I can analyze attendance patterns and generate official records.

#### Acceptance Criteria

1. WHEN a user requests an attendance report THEN the QR_Attendance_System SHALL display attendance records filtered by date range, class, and student
2. WHEN generating a report THEN the QR_Attendance_System SHALL calculate statistics including total present, late, and absent counts with percentages
3. WHEN a teacher requests reports THEN the QR_Attendance_System SHALL display only data for students in the teacher's classes
4. WHEN exporting a report THEN the QR_Attendance_System SHALL support CSV and PDF export formats
5. WHEN displaying daily attendance THEN the QR_Attendance_System SHALL show a summary view with present, late, and absent counts per class

### Requirement 18: Attendance Management Page

**User Story:** As a teacher or admin, I want to view and manage daily attendance records, so that I can track student presence and make manual corrections when needed.

#### Acceptance Criteria

1. WHEN a user accesses the attendance page THEN the QR_Attendance_System SHALL display today's attendance records by default
2. WHEN filtering attendance THEN the QR_Attendance_System SHALL support filters for date, class, status, and search by student name
3. WHEN a teacher views attendance THEN the QR_Attendance_System SHALL display only students from the teacher's classes
4. WHEN an admin manually marks attendance THEN the QR_Attendance_System SHALL record the admin's user_id as recorded_by
5. WHEN viewing attendance history THEN the QR_Attendance_System SHALL display check_in_time, check_out_time, and status for each record

### Requirement 19: Student Placement

**User Story:** As an admin, I want to manage student class placements, so that I can transfer students between classes and manage enrollments across school years.

#### Acceptance Criteria

1. WHEN an admin transfers a student to a new class THEN the QR_Attendance_System SHALL update the student_classes record with enrollment_status 'transferred_out' for the old class
2. WHEN placing a student in a new class THEN the QR_Attendance_System SHALL create a new student_classes record with the appropriate enrollment_type
3. WHEN bulk placing students THEN the QR_Attendance_System SHALL support selecting multiple students and assigning them to a target class
4. WHEN a student is placed THEN the QR_Attendance_System SHALL record status_changed_at, status_changed_by, and status_reason
5. WHEN viewing placement history THEN the QR_Attendance_System SHALL display all class enrollments for a student across school years

### Requirement 20: Subscription Management

**User Story:** As an admin, I want to manage teacher premium subscriptions, so that I can control access to premium features.

#### Acceptance Criteria

1. WHEN an admin views subscriptions THEN the QR_Attendance_System SHALL display all teachers with their premium status and expiration dates
2. WHEN an admin grants premium access THEN the QR_Attendance_System SHALL set is_premium to true and premium_expires_at to the specified date
3. WHEN an admin revokes premium access THEN the QR_Attendance_System SHALL set is_premium to false
4. WHEN premium_expires_at date passes THEN the QR_Attendance_System SHALL treat the user as non-premium
5. WHEN displaying premium status THEN the QR_Attendance_System SHALL show a visual badge indicating premium or free status

### Requirement 21: System Settings

**User Story:** As an admin, I want to configure system settings, so that I can customize the application behavior for the school's needs.

#### Acceptance Criteria

1. WHEN an admin accesses settings THEN the QR_Attendance_System SHALL display configurable options including school name, logo, and contact information
2. WHEN an admin updates settings THEN the QR_Attendance_System SHALL persist changes and apply them immediately
3. WHEN configuring SMS settings THEN the QR_Attendance_System SHALL allow enabling/disabling SMS notifications and setting the SMS provider credentials
4. WHEN configuring attendance settings THEN the QR_Attendance_System SHALL allow setting default scan mode (arrival/dismissal) and duplicate scan prevention window
5. WHEN settings are changed THEN the QR_Attendance_System SHALL log the change with the admin's user_id and timestamp

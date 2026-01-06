# QR Attendance System

A multi-tenant school attendance management system built with Laravel that enables QR code-based student check-in/check-out tracking across multiple schools.

## Core Features

- **Multi-Tenancy**: Support for multiple schools with isolated data per school
- **QR Code Scanning**: Students scan QR codes (LRN or student_id) for attendance recording
- **Role-Based Access**: Four user roles - Super Admin, Admin, Principal, Teacher - with distinct permissions
- **Teacher Attendance**: Two-phase teacher attendance (login time + first student scan)
- **School Year Management**: Academic year tracking with lock/unlock capabilities (per school)
- **Time Schedules**: Configurable schedules with late threshold calculations (per school)
- **Class Management**: Grade levels, sections, student enrollment, and teacher assignments
- **ID Card Generation**: QR code and PDF ID card generation for students
- **Reports**: Attendance reports with CSV/PDF export
- **Real-time Updates**: WebSocket broadcasting via Laravel Reverb for live attendance feeds

## User Roles

| Role | Capabilities |
|------|-------------|
| Super Admin | Cross-school access, manage all schools, create school admins |
| Admin | Full school access, user management, settings, school year control |
| Principal | Read-only teacher monitoring, view all students and classes within school |
| Teacher | Manage own classes, view/record attendance for assigned students |

## Multi-Tenancy Architecture

- **Tenant Isolation**: Data is scoped by `school_id` column on tenant-aware models
- **Global Scope**: `BelongsToSchool` trait auto-filters queries by authenticated user's school
- **Middleware**: `EnsureSchoolContext` validates school assignment for non-super-admin users
- **Super Admin**: Can bypass school scoping to manage all schools

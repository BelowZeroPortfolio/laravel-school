@extends('layouts.app')

@section('title', $student->full_name)

@section('content')
<div class="space-y-6" x-data="studentIdCard()">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('students.index') }}" class="inline-flex items-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Students
            </a>
            <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $student->full_name }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $student->student_id }}</p>
        </div>
        <div class="flex items-center space-x-3">
            <x-badge type="{{ $student->is_active ? 'success' : 'default' }}">
                {{ $student->is_active ? 'Active' : 'Inactive' }}
            </x-badge>
            <button type="button" x-on:click="showIdCard = true" class="inline-flex items-center px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
                </svg>
                Generate ID Card
            </button>
            <a href="{{ route('students.edit', $student) }}">
                <x-button variant="outline">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </x-button>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Student Details -->
            <x-card title="Student Information">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Full Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->full_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Student ID</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono">{{ $student->student_id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">LRN</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white font-mono">{{ $student->lrn ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">SMS Notifications</dt>
                        <dd class="mt-1">
                            <x-badge type="{{ $student->sms_enabled ? 'success' : 'default' }}" size="sm">
                                {{ $student->sms_enabled ? 'Enabled' : 'Disabled' }}
                            </x-badge>
                        </dd>
                    </div>
                </dl>
            </x-card>

            <!-- Parent/Guardian Info -->
            <x-card title="Parent/Guardian Information">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->parent_name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->parent_phone ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->parent_email ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Address</dt>
                        <dd class="mt-1 text-sm text-gray-900 dark:text-white">{{ $student->address ?? '-' }}</dd>
                    </div>
                </dl>
            </x-card>

            <!-- Recent Attendance -->
            <x-card title="Recent Attendance" :padding="false">
                @if($student->attendances->count() > 0)
                    <x-table>
                        <x-slot name="head">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Check In</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Check Out</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                            </tr>
                        </x-slot>
                        @foreach($student->attendances as $attendance)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $attendance->attendance_date->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $attendance->check_in_time ? $attendance->check_in_time->format('h:i A') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $attendance->check_out_time ? $attendance->check_out_time->format('h:i A') : '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-badge type="{{ $attendance->status === 'present' ? 'success' : ($attendance->status === 'late' ? 'warning' : 'danger') }}" size="sm">
                                        {{ ucfirst($attendance->status) }}
                                    </x-badge>
                                </td>
                            </tr>
                        @endforeach
                    </x-table>
                @else
                    <div class="p-6 text-center text-gray-500 dark:text-gray-400">
                        No attendance records found.
                    </div>
                @endif
            </x-card>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- QR Code -->
            <x-card title="QR Code">
                <div class="text-center">
                    <div id="student-qrcode" class="inline-flex items-center justify-center w-32 h-32 bg-white rounded-lg mb-3 p-2"></div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Scan: {{ $student->lrn ?? $student->student_id }}
                    </p>
                </div>
            </x-card>

            <!-- Class Enrollments -->
            <x-card title="Class Enrollments">
                @if($student->classes->count() > 0)
                    <div class="space-y-3">
                        @foreach($student->classes as $class)
                            <a href="{{ route('classes.show', $class) }}" class="block p-3 bg-gray-50 dark:bg-gray-800 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            Grade {{ $class->grade_level }} - {{ $class->section }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $class->teacher->full_name ?? 'No teacher' }}
                                        </p>
                                    </div>
                                    <x-badge type="{{ $class->pivot->is_active ? 'success' : 'default' }}" size="sm">
                                        {{ $class->pivot->is_active ? 'Active' : 'Inactive' }}
                                    </x-badge>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                        Not enrolled in any classes.
                    </p>
                @endif
            </x-card>
        </div>
    </div>

    <!-- ID Card Modal -->
    <div x-show="showIdCard" x-cloak class="fixed inset-0 z-50 overflow-y-auto" x-transition>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" x-on:click="showIdCard = false"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl transform transition-all sm:max-w-2xl sm:w-full mx-auto max-h-[90vh] overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Student ID Card</h3>
                    <div class="flex items-center gap-2">
                        <button x-on:click="printCard()" class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Print
                        </button>
                        <button x-on:click="showIdCard = false" class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="p-6 overflow-auto">
                    <div id="idCardContainer" class="flex flex-col sm:flex-row gap-4 justify-center items-center"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@php
    $activeClass = $student->classes->where('pivot.is_active', true)->first();
    $activeSchoolYear = \App\Models\SchoolYear::active()->first();
@endphp

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const qrContainer = document.getElementById('student-qrcode');
    if (qrContainer && typeof QRCode !== 'undefined') {
        new QRCode(qrContainer, {
            text: '{{ $student->lrn ?? $student->student_id }}',
            width: 112,
            height: 112,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M
        });
    }
});

function studentIdCard() {
    return {
        showIdCard: false,
        cardGenerated: false,

        init() {
            this.$watch('showIdCard', (value) => {
                if (value && !this.cardGenerated) {
                    this.generateCard();
                    this.cardGenerated = true;
                }
            });
        },

        generateCard() {
            const container = document.getElementById('idCardContainer');
            const student = {
                first_name: '{{ $student->first_name }}',
                last_name: '{{ $student->last_name }}',
                lrn: '{{ $student->lrn ?? "" }}',
                student_id: '{{ $student->student_id }}',
                photo_path: '{{ $student->photo_path ?? "" }}',
                parent_name: '{{ $student->parent_name ?? "" }}',
                parent_phone: '{{ $student->parent_phone ?? "" }}',
                parent_email: '{{ $student->parent_email ?? "" }}',
                address: '{{ addslashes($student->address ?? "") }}',
                grade_level: '{{ $activeClass?->grade_level ?? "" }}',
                section: '{{ $activeClass?->section ?? "" }}'
            };
            const schoolName = '{{ config("app.name", "School Name") }}';
            const schoolYear = '{{ $activeSchoolYear?->name ?? now()->year . "-" . (now()->year + 1) }}';
            const appUrl = '{{ url("/") }}';

            const name = (student.first_name + ' ' + student.last_name).toUpperCase();
            const lrn = student.lrn || student.student_id;
            const gradeSection = (student.grade_level || '') + (student.section ? ' - ' + student.section : '');
            const parentName = student.parent_name || '—';
            const parentPhone = student.parent_phone || '—';
            const parentEmail = student.parent_email || '—';
            const address = student.address || '—';

            const studentPhotoUrl = student.photo_path ? appUrl + '/storage/' + student.photo_path : '';
            const studentPhotoHtml = studentPhotoUrl 
                ? '<img src="' + studentPhotoUrl + '" alt="Photo" style="width: 100%; height: 100%; object-fit: cover;">'
                : '<div style="width: 100%; height: 100%; background: #1a2a3a; display: flex; align-items: center; justify-content: center;"><svg style="width: 40px; height: 40px; color: #3a4a5a;" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg></div>';

            const schoolLogoHtml = '<div style="width: 100%; height: 100%; background: linear-gradient(180deg, #4a8ac4 0%, #2a5a8a 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #F4D35E;"><span style="font-size: 7px; color: white; font-weight: bold;">LOGO</span></div>';

            container.innerHTML = 
                '<!-- FRONT SIDE -->' +
                '<div style="width: 204px; height: 324px; background: #0D1B2A; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 25px rgba(0,0,0,0.4); display: flex; flex-direction: column; font-family: Segoe UI, Arial, sans-serif;">' +
                    '<div style="background: #0D1B2A; padding: 12px 10px 8px; text-align: center;">' +
                        '<h3 style="font-size: 13px; font-weight: bold; color: #FFFFFF; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.25; margin: 0;">' + schoolName + '</h3>' +
                    '</div>' +
                    '<div style="height: 4px; background: linear-gradient(to right, #D62828 0%, #D62828 30%, #0D1B2A 50%, #F4D35E 70%, #F4D35E 100%);"></div>' +
                    '<div style="padding: 10px 12px; display: flex; justify-content: space-between; align-items: flex-start; background: #0D1B2A;">' +
                        '<div style="width: 78px; height: 98px; border: 3px solid #FFFFFF; border-radius: 4px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">' + studentPhotoHtml + '</div>' +
                        '<div style="display: flex; flex-direction: column; align-items: center;">' +
                            '<div style="width: 58px; height: 58px; border-radius: 50%; overflow: hidden; background: white; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.3);">' + schoolLogoHtml + '</div>' +
                            '<div style="text-align: center; margin-top: 8px;">' +
                                '<p style="font-size: 8px; color: rgba(255,255,255,0.7); margin: 0;">S.Y.</p>' +
                                '<p style="font-size: 11px; font-weight: bold; color: #FFFFFF; margin: 0;">' + schoolYear + '</p>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div style="flex: 1; background: #FFFFFF; padding: 10px 12px; text-align: center;">' +
                        '<div style="margin-bottom: 6px;">' +
                            '<p style="font-size: 8px; color: #333333; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 2px 0;">LRN</p>' +
                            '<p style="font-size: 15px; font-weight: bold; color: #0D1B2A; font-family: Courier New, monospace; margin: 0;">' + lrn + '</p>' +
                        '</div>' +
                        '<div style="margin-bottom: 6px;">' +
                            '<p style="font-size: 8px; color: #333333; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 2px 0;">Student Name</p>' +
                            '<p style="font-size: 12px; font-weight: bold; color: #0D1B2A; margin: 0;">' + name + '</p>' +
                        '</div>' +
                        '<div>' +
                            '<p style="font-size: 8px; color: #333333; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 2px 0;">Grade & Section</p>' +
                            '<p style="font-size: 13px; font-weight: bold; color: #0D1B2A; margin: 0;">' + gradeSection + '</p>' +
                        '</div>' +
                    '</div>' +
                    '<div style="background: #0D1B2A; padding: 8px 10px; text-align: center;">' +
                        '<p style="font-size: 8px; font-weight: bold; color: #FFFFFF; text-transform: uppercase; letter-spacing: 1.5px; margin: 0;">Student Identification Card</p>' +
                    '</div>' +
                '</div>' +
                '<!-- BACK SIDE -->' +
                '<div style="width: 204px; height: 324px; background: #0D1B2A; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 25px rgba(0,0,0,0.4); display: flex; flex-direction: column; font-family: Segoe UI, Arial, sans-serif;">' +
                    '<div style="background: #0D1B2A; padding: 12px 10px 8px; text-align: center;">' +
                        '<h3 style="font-size: 14px; font-weight: bold; color: #FFFFFF; text-transform: uppercase; letter-spacing: 1px; margin: 0;">Emergency Contact</h3>' +
                    '</div>' +
                    '<div style="height: 4px; background: linear-gradient(to right, #D62828 0%, #D62828 30%, #0D1B2A 50%, #F4D35E 70%, #F4D35E 100%);"></div>' +
                    '<div style="flex: 1; background: #FFFFFF; margin: 8px; border-radius: 8px; display: flex; flex-direction: column; align-items: center; padding: 10px;">' +
                        '<div style="background: #f5f5f5; border-radius: 8px; padding: 8px; margin-bottom: 6px; border: 2px solid #0D1B2A;">' +
                            '<div id="modal-qr" style="width: 85px; height: 85px;"></div>' +
                        '</div>' +
                        '<p style="font-size: 9px; color: #333333; margin: 0 0 8px 0;">Scan to verify student</p>' +
                        '<div style="width: 100%; text-align: center;">' +
                            '<div style="border-bottom: 1px solid #0D1B2A; padding-bottom: 2px; margin-bottom: 4px;">' +
                                '<p style="font-size: 8px; font-weight: bold; color: #0D1B2A; text-transform: uppercase; letter-spacing: 0.3px; margin: 0;">In Case of Emergency</p>' +
                            '</div>' +
                            '<div style="margin-bottom: 3px;">' +
                                '<p style="font-size: 6px; color: #333333; text-transform: uppercase; margin: 0;">Guardian</p>' +
                                '<p style="font-size: 9px; font-weight: bold; color: #0D1B2A; margin: 0;">' + parentName + '</p>' +
                            '</div>' +
                            '<div style="margin-bottom: 3px;">' +
                                '<p style="font-size: 6px; color: #333333; text-transform: uppercase; margin: 0;">Contact Number</p>' +
                                '<p style="font-size: 10px; font-weight: bold; color: #D62828; margin: 0;">' + parentPhone + '</p>' +
                            '</div>' +
                            '<div style="margin-bottom: 3px;">' +
                                '<p style="font-size: 6px; color: #333333; text-transform: uppercase; margin: 0;">Email</p>' +
                                '<p style="font-size: 7px; color: #333333; margin: 0; word-break: break-all;">' + parentEmail + '</p>' +
                            '</div>' +
                            '<div>' +
                                '<p style="font-size: 7px; color: #333333; margin: 0; line-height: 1.2;">' + address + '</p>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div style="background: #0D1B2A; padding: 8px 10px; text-align: center;">' +
                        '<p style="font-size: 7px; color: #FFFFFF; margin: 0;">If found, please return to school administration.</p>' +
                    '</div>' +
                '</div>';

            setTimeout(function() {
                const qrContainer = document.getElementById('modal-qr');
                if (qrContainer && typeof QRCode !== 'undefined') {
                    new QRCode(qrContainer, {
                        text: lrn,
                        width: 85,
                        height: 85,
                        colorDark: '#000000',
                        colorLight: '#ffffff',
                        correctLevel: QRCode.CorrectLevel.M
                    });
                }
            }, 100);
        },

        printCard() {
            const container = document.getElementById('idCardContainer');
            const printWindow = window.open('', '_blank');
            printWindow.document.write(
                '<!DOCTYPE html><html><head><title>Student ID Card</title>' +
                '<style>' +
                '* { margin: 0; padding: 0; box-sizing: border-box; }' +
                '@media print { @page { size: A4; margin: 10mm; } body { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; } }' +
                'body { font-family: Segoe UI, Arial, sans-serif; background: #fff; display: flex; justify-content: center; padding: 20px; }' +
                '.card-container { display: flex; gap: 10mm; }' +
                '</style></head><body>' +
                '<div class="card-container">' + container.innerHTML + '</div>' +
                '</body></html>'
            );
            printWindow.document.close();
            setTimeout(function() { printWindow.print(); }, 500);
        }
    }
}
</script>
@endpush
@endsection

@extends('layouts.app')

@section('title', 'Generate ID Cards')

@section('content')
<div class="space-y-6" x-data="idCardGenerator()">
    <!-- Page Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Generate ID Cards</h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">Select students to generate ID cards in bulk</p>
        </div>
        <button type="button" 
                x-on:click="generateSelectedIDs()" 
                x-bind:disabled="selectedStudents.length === 0"
                class="inline-flex items-center px-4 py-2.5 bg-emerald-600 text-white text-sm font-medium rounded-xl hover:bg-emerald-700 transition-colors shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/>
            </svg>
            Generate Selected (<span x-text="selectedStudents.length">0</span>)
        </button>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Filter Students</h3>
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Grade Level</label>
                <select name="grade_level" onchange="this.form.submit()" 
                        class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    <option value="">All Grades</option>
                    @foreach($gradeLevels as $grade)
                        <option value="{{ $grade }}" {{ request('grade_level') == $grade ? 'selected' : '' }}>{{ $grade }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Section</label>
                <select name="section" onchange="this.form.submit()"
                        class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
                    <option value="">All Sections</option>
                    @foreach($sections as $sec)
                        <option value="{{ $sec }}" {{ request('section') == $sec ? 'selected' : '' }}>{{ $sec }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, LRN, or ID..."
                       class="w-full px-3 py-2 border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                    Apply Filter
                </button>
                <a href="{{ route('id-cards.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <!-- Students Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        @if($students->isEmpty() && (request('grade_level') || request('section') || request('search')))
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No students found matching your filters.</p>
            </div>
        @elseif($students->isEmpty())
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">Select filters to view students</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Choose a grade level or section above to display students for ID card generation.</p>
            </div>
        @else
            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <input type="checkbox" x-on:change="toggleSelectAll($event)" x-bind:checked="allSelected" x-bind:indeterminate="someSelected"
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 rounded">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Select All</label>
                </div>
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $students->count() }} students found</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12"></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">LRN</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Class</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">QR Code</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($students as $student)
                            @php
                                $activeClass = $student->classes->first();
                                $studentJson = json_encode([
                                    'id' => $student->id,
                                    'student_id' => $student->student_id,
                                    'lrn' => $student->lrn,
                                    'first_name' => $student->first_name,
                                    'last_name' => $student->last_name,
                                    'photo_path' => $student->photo_path,
                                    'parent_name' => $student->parent_name,
                                    'parent_phone' => $student->parent_phone,
                                    'parent_email' => $student->parent_email,
                                    'address' => $student->address,
                                    'grade_level' => $activeClass?->grade_level,
                                    'section' => $activeClass?->section,
                                ]);
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-4">
                                    <input type="checkbox" 
                                           x-bind:checked="selectedStudents.includes({{ $student->id }})"
                                           x-on:change="toggleStudent({{ $student->id }}, {{ $studentJson }})"
                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 rounded">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                            {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $student->first_name }} {{ $student->last_name }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-mono text-gray-600 dark:text-gray-400">{{ $student->lrn ?? $student->student_id }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ $activeClass ? $activeClass->grade_level . ' - ' . $activeClass->section : 'Not enrolled' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($student->qrcode_path)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            Ready
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800">
                                            No QR
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('students.show', $student) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Bulk ID Card Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" x-transition>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" x-on:click="showModal = false"></div>
            <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl transform transition-all sm:max-w-6xl sm:w-full mx-auto max-h-[90vh] overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Generated ID Cards (<span x-text="selectedStudents.length">0</span>)</h3>
                    <div class="flex items-center gap-2">
                        <button x-on:click="printAllCards()" class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Print All
                        </button>
                        <button x-on:click="showModal = false" class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="p-6 overflow-auto max-h-[75vh]">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="cardsGrid"></div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
function idCardGenerator() {
    return {
        selectedStudents: [],
        studentData: {},
        showModal: false,
        schoolName: '{{ config("app.name", "School Name") }}',
        schoolYear: '{{ $activeSchoolYear?->name ?? now()->year . "-" . (now()->year + 1) }}',
        appUrl: '{{ url("/") }}',

        get allSelected() {
            return this.selectedStudents.length === {{ $students->count() }} && this.selectedStudents.length > 0;
        },

        get someSelected() {
            return this.selectedStudents.length > 0 && this.selectedStudents.length < {{ $students->count() }};
        },

        toggleSelectAll(event) {
            if (event.target.checked) {
                this.selectedStudents = [];
                this.studentData = {};
                @foreach($students as $student)
                    @php
                        $activeClass = $student->classes->first();
                        $data = [
                            'id' => $student->id,
                            'student_id' => $student->student_id,
                            'lrn' => $student->lrn,
                            'first_name' => $student->first_name,
                            'last_name' => $student->last_name,
                            'photo_path' => $student->photo_path,
                            'parent_name' => $student->parent_name,
                            'parent_phone' => $student->parent_phone,
                            'parent_email' => $student->parent_email,
                            'address' => $student->address,
                            'grade_level' => $activeClass?->grade_level,
                            'section' => $activeClass?->section,
                        ];
                    @endphp
                    this.selectedStudents.push({{ $student->id }});
                    this.studentData[{{ $student->id }}] = @json($data);
                @endforeach
            } else {
                this.selectedStudents = [];
                this.studentData = {};
            }
        },

        toggleStudent(id, data) {
            const index = this.selectedStudents.indexOf(id);
            if (index === -1) {
                this.selectedStudents.push(id);
                this.studentData[id] = data;
            } else {
                this.selectedStudents.splice(index, 1);
                delete this.studentData[id];
            }
        },

        generateSelectedIDs() {
            if (this.selectedStudents.length === 0) return;
            
            const cardsGrid = document.getElementById('cardsGrid');
            cardsGrid.innerHTML = '';
            this.showModal = true;

            this.selectedStudents.forEach((id, index) => {
                const student = this.studentData[id];
                const cardHtml = this.createIDCardHTML(student, index);
                cardsGrid.insertAdjacentHTML('beforeend', cardHtml);
                
                setTimeout(() => {
                    const qrContainer = document.getElementById('qr-' + index);
                    if (qrContainer && typeof QRCode !== 'undefined') {
                        new QRCode(qrContainer, {
                            text: student.lrn || student.student_id,
                            width: 85,
                            height: 85,
                            colorDark: '#000000',
                            colorLight: '#ffffff',
                            correctLevel: QRCode.CorrectLevel.M
                        });
                    }
                }, 100);
            });
        },

        createIDCardHTML(student, index) {
            const name = (student.first_name + ' ' + student.last_name).toUpperCase();
            const lrn = student.lrn || student.student_id;
            const gradeSection = (student.grade_level || '') + (student.section ? ' - ' + student.section : '');
            const parentName = student.parent_name || '—';
            const parentPhone = student.parent_phone || '—';
            const parentEmail = student.parent_email || '—';
            const address = student.address || '—';

            const studentPhotoUrl = student.photo_path ? this.appUrl + '/storage/' + student.photo_path : '';
            const studentPhotoHtml = studentPhotoUrl 
                ? '<img src="' + studentPhotoUrl + '" alt="Photo" style="width: 100%; height: 100%; object-fit: cover;">'
                : '<div style="width: 100%; height: 100%; background: #1a2a3a; display: flex; align-items: center; justify-content: center;"><svg style="width: 40px; height: 40px; color: #3a4a5a;" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg></div>';

            const schoolLogoHtml = '<div style="width: 100%; height: 100%; background: linear-gradient(180deg, #4a8ac4 0%, #2a5a8a 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #F4D35E;"><span style="font-size: 7px; color: white; font-weight: bold;">LOGO</span></div>';

            return '<div class="id-card-wrapper col-span-1">' +
                '<div class="flex flex-col sm:flex-row gap-4 justify-center items-center">' +
                    '<!-- FRONT SIDE -->' +
                    '<div style="width: 204px; height: 324px; background: #0D1B2A; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 25px rgba(0,0,0,0.4); display: flex; flex-direction: column; font-family: Segoe UI, Arial, sans-serif;">' +
                        '<div style="background: #0D1B2A; padding: 12px 10px 8px; text-align: center;">' +
                            '<h3 style="font-size: 13px; font-weight: bold; color: #FFFFFF; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.25; margin: 0;">' + this.schoolName + '</h3>' +
                        '</div>' +
                        '<div style="height: 4px; background: linear-gradient(to right, #D62828 0%, #D62828 30%, #0D1B2A 50%, #F4D35E 70%, #F4D35E 100%);"></div>' +
                        '<div style="padding: 10px 12px; display: flex; justify-content: space-between; align-items: flex-start; background: #0D1B2A;">' +
                            '<div style="width: 78px; height: 98px; border: 3px solid #FFFFFF; border-radius: 4px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">' + studentPhotoHtml + '</div>' +
                            '<div style="display: flex; flex-direction: column; align-items: center;">' +
                                '<div style="width: 58px; height: 58px; border-radius: 50%; overflow: hidden; background: white; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(0,0,0,0.3);">' + schoolLogoHtml + '</div>' +
                                '<div style="text-align: center; margin-top: 8px;">' +
                                    '<p style="font-size: 8px; color: rgba(255,255,255,0.7); margin: 0;">S.Y.</p>' +
                                    '<p style="font-size: 11px; font-weight: bold; color: #FFFFFF; margin: 0;">' + this.schoolYear + '</p>' +
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
                                '<div id="qr-' + index + '" style="width: 85px; height: 85px;"></div>' +
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
                    '</div>' +
                '</div>' +
                '<p class="text-center text-xs text-gray-500 mt-2">' + student.first_name + ' ' + student.last_name + '</p>' +
            '</div>';
        },

        printAllCards() {
            const container = document.getElementById('cardsGrid');
            const printWindow = window.open('', '_blank');
            printWindow.document.write(
                '<!DOCTYPE html><html><head><title>Student ID Cards</title>' +
                '<style>' +
                '* { margin: 0; padding: 0; box-sizing: border-box; }' +
                '@media print { @page { size: A4; margin: 8mm; } body { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; } }' +
                'body { font-family: Segoe UI, Arial, sans-serif; background: #fff; }' +
                '.print-grid { display: flex; flex-wrap: wrap; justify-content: flex-start; gap: 8mm; padding: 5mm; }' +
                '.id-card-wrapper { page-break-inside: avoid; break-inside: avoid; margin-bottom: 5mm; }' +
                '.id-card-wrapper > div:first-child { display: flex; flex-direction: row; gap: 5mm; }' +
                '.id-card-wrapper p.text-center { display: none; }' +
                '</style></head><body>' +
                '<div class="print-grid">' + container.innerHTML + '</div>' +
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

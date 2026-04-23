@extends('layouts.app')

@php
    $pageTitle = 'Reports';
    $pageSubtitle = 'Preview and print schedule reports';

    $currentReportType = $reportType ?? 'section_schedule';

    $selectedSchoolYear = $schoolYears->firstWhere('id', $selectedSchoolYearId ?? null);
    $selectedTerm = $terms->firstWhere('id', $selectedTermId ?? null);
    $selectedGradeLevel = $gradeLevels->firstWhere('id', $selectedGradeLevelId ?? null);
    $selectedSection = $sections->firstWhere('id', $selectedSectionId ?? null);
    $selectedTeacher = $teachers->firstWhere('id', $selectedTeacherId ?? null);
    $selectedRoom = $rooms->firstWhere('id', $selectedRoomId ?? null);

    $days = [
        'monday' => 'Monday',
        'tuesday' => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday' => 'Thursday',
        'friday' => 'Friday',
    ];

    $reportMeta = [
        'section_schedule' => [
            'title' => 'Section Schedule Preview',
            'subtitle' => 'Preview of the weekly schedule for the selected section.',
            'badge' => 'Section Report',
        ],
        'teacher_schedule' => [
            'title' => 'Teacher Schedule Preview',
            'subtitle' => 'Preview of all assigned class entries for the selected teacher.',
            'badge' => 'Teacher Report',
        ],
        'room_utilization' => [
            'title' => 'Room Schedule Preview',
            'subtitle' => 'Preview of room usage across scheduled class entries.',
            'badge' => 'Room Report',
        ],
        'teacher_load' => [
            'title' => 'Teacher Load Preview',
            'subtitle' => 'Summary of assigned hours and load status per teacher.',
            'badge' => 'Teacher Load',
        ],
        'subject_hours_summary' => [
            'title' => 'Subject Hours Summary Preview',
            'subtitle' => 'Comparison of scheduled hours and expected subject hours.',
            'badge' => 'Subject Hours',
        ],
    ];

    $activeMeta = $reportMeta[$currentReportType] ?? $reportMeta['section_schedule'];

    $buildRouteParams = function (array $extra = []) use (
        $selectedSchoolYearId,
        $selectedTermId,
        $selectedGradeLevelId,
        $selectedSectionId,
        $selectedTeacherId,
        $selectedRoomId,
        $currentReportType
    ) {
        return array_filter([
            'school_year_id' => $selectedSchoolYearId,
            'term_id' => $selectedTermId,
            'grade_level_id' => $selectedGradeLevelId,
            'section_id' => $selectedSectionId,
            'teacher_id' => $selectedTeacherId,
            'room_id' => $selectedRoomId,
            'report_type' => $currentReportType,
            ...$extra,
        ], fn ($value) => !is_null($value) && $value !== '');
    };
@endphp

@section('content')

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h5 class="fw-bold mb-1">Reports Workspace</h5>
        <p class="text-muted mb-0">
            Preview and prepare printable schedule reports by section, teacher, and room.
        </p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <form method="GET" action="{{ route('reports.print') }}" target="_blank" class="d-inline">
            @foreach ($buildRouteParams() as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-printer me-1"></i> Print Report
            </button>
        </form>

        <a href="{{ route('reports.export.pdf', $buildRouteParams()) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
        </a>

        <a href="{{ route('reports.export.excel', $buildRouteParams()) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
        </a>
    </div>
</div>

<form method="GET" action="{{ route('reports.index') }}" id="reportFilterForm">
    <input type="hidden" name="report_type" id="reportTypeInput" value="{{ $currentReportType }}">

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card report-type-card {{ $currentReportType === 'section_schedule' ? 'active' : '' }}" data-report="section_schedule">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="fw-bold mb-1">Section Schedule</h6>
                            <p class="text-muted small mb-0">View the weekly class schedule of a selected section.</p>
                        </div>
                        <i class="bi bi-grid-3x3-gap-fill fs-4 text-primary"></i>
                    </div>
                    <div class="small text-muted">Best for section-based academic schedule review.</div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card report-type-card {{ $currentReportType === 'teacher_schedule' ? 'active' : '' }}" data-report="teacher_schedule">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="fw-bold mb-1">Teacher Schedule</h6>
                            <p class="text-muted small mb-0">View all assigned classes of a selected teacher.</p>
                        </div>
                        <i class="bi bi-person-lines-fill fs-4 text-primary"></i>
                    </div>
                    <div class="small text-muted">Best for teacher load and class allocation review.</div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card report-type-card {{ $currentReportType === 'room_utilization' ? 'active' : '' }}" data-report="room_utilization">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="fw-bold mb-1">Room Schedule</h6>
                            <p class="text-muted small mb-0">View the weekly usage of a selected room.</p>
                        </div>
                        <i class="bi bi-door-open-fill fs-4 text-primary"></i>
                    </div>
                    <div class="small text-muted">Best for room utilization and schedule reference.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card content-card mb-4">
        <div class="card-body p-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">School Year</label>
                    <select class="form-select" name="school_year_id" id="schoolYearSelect" onchange="document.getElementById('reportFilterForm').submit()">
                        @forelse ($schoolYears as $schoolYear)
                            <option value="{{ $schoolYear->id }}" {{ (int) $schoolYear->id === (int) $selectedSchoolYearId ? 'selected' : '' }}>
                                {{ $schoolYear->name }}
                            </option>
                        @empty
                            <option value="">No school year available</option>
                        @endforelse
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Term</label>
                    <select class="form-select" name="term_id" id="termSelect" onchange="document.getElementById('reportFilterForm').submit()">
                        @forelse ($terms as $term)
                            <option value="{{ $term->id }}" {{ (int) $term->id === (int) $selectedTermId ? 'selected' : '' }}>
                                {{ $term->name }}
                            </option>
                        @empty
                            <option value="">No term available</option>
                        @endforelse
                    </select>
                </div>

                <div class="col-md-2" id="gradeLevelFilterWrap">
                    <label class="form-label fw-semibold">Grade Level</label>
                    <select class="form-select" name="grade_level_id" id="reportGradeLevel" onchange="document.getElementById('reportFilterForm').submit()">
                        <option value="">All Grade Levels</option>
                        @foreach ($gradeLevels as $gradeLevel)
                            <option value="{{ $gradeLevel->id }}" {{ (int) $gradeLevel->id === (int) $selectedGradeLevelId ? 'selected' : '' }}>
                                {{ $gradeLevel->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2" id="sectionFilterWrap">
                    <label class="form-label fw-semibold">Section</label>
                    <select class="form-select" name="section_id" id="sectionSelect">
                        <option value="">Select section</option>
                        @foreach ($sections as $section)
                            <option value="{{ $section->id }}" {{ (int) $section->id === (int) $selectedSectionId ? 'selected' : '' }}>
                                {{ $section->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 {{ $currentReportType === 'teacher_schedule' ? '' : 'd-none' }}" id="teacherFilterWrap">
                    <label class="form-label fw-semibold">Teacher</label>
                    <select class="form-select" name="teacher_id" id="teacherSelect">
                        <option value="">Select teacher</option>
                        @foreach ($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ (int) $teacher->id === (int) $selectedTeacherId ? 'selected' : '' }}>
                                {{ $teacher->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 {{ $currentReportType === 'room_utilization' ? '' : 'd-none' }}" id="roomFilterWrap">
                    <label class="form-label fw-semibold">Room</label>
                    <select class="form-select" name="room_id" id="roomSelect">
                        <option value="">Select room</option>
                        @foreach ($rooms as $room)
                            <option value="{{ $room->id }}" {{ (int) $room->id === (int) $selectedRoomId ? 'selected' : '' }}>
                                {{ $room->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-1"></i> Preview
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="card content-card">
    <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-1" id="reportPreviewTitle">{{ $activeMeta['title'] }}</h5>
                <p class="text-muted small mb-0" id="reportPreviewSubtitle">{{ $activeMeta['subtitle'] }}</p>
            </div>

            <span class="badge text-bg-light border" id="reportTypeBadge">{{ $activeMeta['badge'] }}</span>
        </div>
    </div>

    <div class="card-body p-4">
        <div class="report-preview-panel {{ $currentReportType === 'section_schedule' ? '' : 'd-none' }}" id="sectionPreview">
            @if (!empty($reportData['section']) && !empty($reportData['grid']))
                <div class="mb-3">
                    <div class="fw-semibold">Section: {{ $reportData['section']->name }}</div>
                    <div class="text-muted small">
                        {{ $reportData['section']->gradeLevel?->name ?? 'No Grade Level' }}
                        @if ($reportData['section']->pathway?->name)
                            • {{ $reportData['section']->pathway->name }}
                        @endif
                        @if ($reportData['section']->shiftType?->name)
                            • {{ $reportData['section']->shiftType->name }}
                        @endif
                        @if ($selectedTerm?->name)
                            • {{ $selectedTerm->name }}
                        @endif
                        @if ($selectedSchoolYear?->name)
                            • SY {{ $selectedSchoolYear->name }}
                        @endif
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle report-table">
                        <thead class="table-light">
                            <tr>
                                <th>Time</th>
                                @foreach ($days as $dayLabel)
                                    <th>{{ $dayLabel }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reportData['grid'] as $row)
                                @php
                                    $timeSlot = $row['time_slot'];
                                    $isBreak = (bool) ($timeSlot->is_break ?? false);
                                    $timeLabel = $timeSlot->label ?: ($timeSlot->time_range ?? '');
                                @endphp
                                <tr>
                                    <th>{{ $timeLabel }}</th>

                                    @foreach (array_keys($days) as $dayKey)
                                        @php $entry = $row['days'][$dayKey] ?? null; @endphp

                                        @if ($isBreak)
                                            <td class="break-cell">Break</td>
                                        @elseif ($entry)
                                            <td>
                                                <div class="fw-semibold">{{ $entry['subject'] ?? '—' }}</div>
                                                <div class="text-muted small">
                                                    {{ $entry['teacher'] ?? 'No Teacher' }}
                                                    @if (!empty($entry['room']))
                                                        • {{ $entry['room'] }}
                                                    @endif
                                                </div>
                                            </td>
                                        @else
                                            <td class="text-muted small">—</td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    Select a valid section and term to preview the section schedule.
                </div>
            @endif
        </div>

        <div class="report-preview-panel {{ $currentReportType === 'teacher_schedule' ? '' : 'd-none' }}" id="teacherPreview">
            @if (!empty($reportData['teacher']) && !empty($reportData['grid']))
                <div class="mb-3">
                    <div class="fw-semibold">Teacher: {{ $reportData['teacher']->full_name }}</div>
                    <div class="text-muted small">
                        @if ($selectedTerm?->name)
                            {{ $selectedTerm->name }}
                        @endif
                        @if ($selectedSchoolYear?->name)
                            • SY {{ $selectedSchoolYear->name }}
                        @endif
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle report-table">
                        <thead class="table-light">
                            <tr>
                                <th>Time</th>
                                @foreach ($days as $dayLabel)
                                    <th>{{ $dayLabel }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reportData['grid'] as $row)
                                @php
                                    $timeSlot = $row['time_slot'];
                                    $isBreak = (bool) ($timeSlot->is_break ?? false);
                                    $timeLabel = $timeSlot->label ?: ($timeSlot->time_range ?? '');
                                @endphp
                                <tr>
                                    <th>{{ $timeLabel }}</th>

                                    @foreach (array_keys($days) as $dayKey)
                                        @php $entry = $row['days'][$dayKey] ?? null; @endphp

                                        @if ($isBreak)
                                            <td class="break-cell">Break</td>
                                        @elseif ($entry)
                                            <td>
                                                <div class="fw-semibold">{{ $entry['subject'] ?? '—' }}</div>
                                                <div class="text-muted small">
                                                    {{ $entry['section'] ?? 'No Section' }}
                                                    @if (!empty($entry['room']))
                                                        • {{ $entry['room'] }}
                                                    @endif
                                                </div>
                                            </td>
                                        @else
                                            <td class="text-muted small">—</td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    Select a valid teacher and term to preview the teacher schedule.
                </div>
            @endif
        </div>

        <div class="report-preview-panel {{ $currentReportType === 'room_utilization' ? '' : 'd-none' }}" id="roomPreview">
            @if (!empty($reportData['room']) && !empty($reportData['grid']))
                <div class="mb-3">
                    <div class="fw-semibold">Room: {{ $reportData['room']->name }}</div>
                    <div class="text-muted small">
                        {{ ucfirst($reportData['room']->room_type ?? 'general') }} Room
                        @if ($selectedTerm?->name)
                            • {{ $selectedTerm->name }}
                        @endif
                        @if ($selectedSchoolYear?->name)
                            • SY {{ $selectedSchoolYear->name }}
                        @endif
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle report-table">
                        <thead class="table-light">
                            <tr>
                                <th>Time</th>
                                @foreach ($days as $dayLabel)
                                    <th>{{ $dayLabel }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reportData['grid'] as $row)
                                @php
                                    $timeSlot = $row['time_slot'];
                                    $isBreak = (bool) ($timeSlot->is_break ?? false);
                                    $timeLabel = $timeSlot->label ?: ($timeSlot->time_range ?? '');
                                @endphp
                                <tr>
                                    <th>{{ $timeLabel }}</th>

                                    @foreach (array_keys($days) as $dayKey)
                                        @php $entry = $row['days'][$dayKey] ?? null; @endphp

                                        @if ($isBreak)
                                            <td class="break-cell">Break</td>
                                        @elseif ($entry)
                                            <td>
                                                <div class="fw-semibold">{{ $entry['subject'] ?? '—' }}</div>
                                                <div class="text-muted small">
                                                    {{ $entry['section'] ?? 'No Section' }}
                                                    @if (!empty($entry['teacher']))
                                                        • {{ $entry['teacher'] }}
                                                    @endif
                                                </div>
                                            </td>
                                        @else
                                            <td class="text-muted small">—</td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if (!empty($reportData['utilizationRows']) && count($reportData['utilizationRows']) > 0)
                    <div class="mt-4">
                        <h6 class="fw-bold mb-3">Utilization Summary</h6>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Day</th>
                                        <th>Total Entries</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($reportData['utilizationRows'] as $row)
                                        <tr>
                                            <td>{{ $row['day'] }}</td>
                                            <td>{{ $row['entries'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @else
                <div class="empty-state">
                    Select a valid room and term to preview the room schedule.
                </div>
            @endif
        </div>

        <div class="report-preview-panel {{ $currentReportType === 'teacher_load' ? '' : 'd-none' }}" id="teacherLoadPreview">
            @if (!empty($reportData['rows']) && count($reportData['rows']) > 0)
                <div class="mb-3">
                    <div class="fw-semibold">Teacher Load Summary</div>
                    <div class="text-muted small">
                        @if ($selectedTerm?->name)
                            {{ $selectedTerm->name }}
                        @endif
                        @if ($selectedSchoolYear?->name)
                            • SY {{ $selectedSchoolYear->name }}
                        @endif
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle report-table">
                        <thead class="table-light">
                            <tr>
                                <th>Teacher</th>
                                <th>Assigned Hours</th>
                                <th>Maximum Load</th>
                                <th>Remaining Hours</th>
                                <th>Status</th>
                                <th>Total Entries</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reportData['rows'] as $row)
                                <tr>
                                    <td>{{ $row['teacher']->full_name }}</td>
                                    <td>{{ number_format((float) $row['assigned_hours'], 2) }}</td>
                                    <td>{{ number_format((float) $row['max_load'], 2) }}</td>
                                    <td>{{ number_format((float) $row['remaining_hours'], 2) }}</td>
                                    <td>
                                        <span class="badge {{ $row['status'] === 'overload' ? 'text-bg-danger' : ($row['status'] === 'underload' ? 'text-bg-warning' : ($row['status'] === 'unassigned' ? 'text-bg-secondary' : 'text-bg-success')) }}">
                                            {{ ucfirst($row['status']) }}
                                        </span>
                                    </td>
                                    <td>{{ $row['total_schedule_entries'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    No teacher load data is available for the selected term.
                </div>
            @endif
        </div>

        <div class="report-preview-panel {{ $currentReportType === 'subject_hours_summary' ? '' : 'd-none' }}" id="subjectHoursPreview">
            @if (!empty($reportData['section']) && !empty($reportData['rows']) && count($reportData['rows']) > 0)
                <div class="mb-3">
                    <div class="fw-semibold">Section: {{ $reportData['section']->name }}</div>
                    <div class="text-muted small">
                        {{ $reportData['section']->gradeLevel?->name ?? 'No Grade Level' }}
                        @if ($reportData['section']->pathway?->name)
                            • {{ $reportData['section']->pathway->name }}
                        @endif
                        @if ($selectedTerm?->name)
                            • {{ $selectedTerm->name }}
                        @endif
                        @if ($selectedSchoolYear?->name)
                            • SY {{ $selectedSchoolYear->name }}
                        @endif
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle report-table">
                        <thead class="table-light">
                            <tr>
                                <th>Subject</th>
                                <th>Scheduled Hours</th>
                                <th>Expected Weekly Hours</th>
                                <th>Difference</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reportData['rows'] as $row)
                                <tr>
                                    <td>{{ $row['subject']?->name ?? 'Unknown Subject' }}</td>
                                    <td>{{ number_format((float) $row['scheduled_hours'], 2) }}</td>
                                    <td>{{ number_format((float) $row['expected_weekly_hours'], 2) }}</td>
                                    <td class="{{ (float) $row['difference'] === 0.0 ? '' : ((float) $row['difference'] > 0 ? 'text-danger fw-semibold' : 'text-warning fw-semibold') }}">
                                        {{ number_format((float) $row['difference'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-state">
                    Select a valid section and term to preview the subject hours summary.
                </div>
            @endif
        </div>

        <div class="mt-4 d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm summary-switch {{ $currentReportType === 'teacher_load' ? 'active' : '' }}" data-report="teacher_load">
                Teacher Load Summary
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm summary-switch {{ $currentReportType === 'subject_hours_summary' ? 'active' : '' }}" data-report="subject_hours_summary">
                Subject Hours Summary
            </button>
        </div>
    </div>
</div>

<style>
.summary-card {
    border-radius: 16px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.05);
}

.report-type-card {
    border: 0;
    border-radius: 18px;
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.report-type-card:hover {
    transform: translateY(-3px);
}

.report-type-card.active {
    outline: 2px solid #2563eb;
    background: #eff6ff;
}

.report-table th,
.report-table td {
    vertical-align: middle;
}

.break-cell {
    background: #f8fafc;
    color: #6b7280;
    font-weight: 600;
    text-align: center;
}

.empty-state {
    border: 1px dashed #d1d5db;
    border-radius: 16px;
    padding: 32px 20px;
    text-align: center;
    color: #6b7280;
    background: #f9fafb;
}

.summary-switch.active {
    background: #111827;
    border-color: #111827;
    color: #fff;
}
</style>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const reportCards = document.querySelectorAll('.report-type-card');
    const summarySwitches = document.querySelectorAll('.summary-switch');
    const reportTypeInput = document.getElementById('reportTypeInput');
    const form = document.getElementById('reportFilterForm');

    const sectionFilterWrap = document.getElementById('sectionFilterWrap');
    const teacherFilterWrap = document.getElementById('teacherFilterWrap');
    const roomFilterWrap = document.getElementById('roomFilterWrap');
    const gradeLevelFilterWrap = document.getElementById('gradeLevelFilterWrap');

    const sectionSelect = document.getElementById('sectionSelect');
    const teacherSelect = document.getElementById('teacherSelect');
    const roomSelect = document.getElementById('roomSelect');

    function toggleFilters(type) {
        sectionFilterWrap.classList.add('d-none');
        teacherFilterWrap.classList.add('d-none');
        roomFilterWrap.classList.add('d-none');
        gradeLevelFilterWrap.classList.remove('d-none');

        if (type === 'section_schedule' || type === 'subject_hours_summary') {
            sectionFilterWrap.classList.remove('d-none');
        }

        if (type === 'teacher_schedule') {
            teacherFilterWrap.classList.remove('d-none');
            gradeLevelFilterWrap.classList.add('d-none');
        }

        if (type === 'room_utilization') {
            roomFilterWrap.classList.remove('d-none');
            gradeLevelFilterWrap.classList.add('d-none');
        }

        if (type === 'teacher_load') {
            gradeLevelFilterWrap.classList.add('d-none');
        }
    }

    function setCardActive(type) {
        reportCards.forEach(card => {
            card.classList.toggle('active', card.dataset.report === type);
        });

        summarySwitches.forEach(button => {
            button.classList.toggle('active', button.dataset.report === type);
        });

        toggleFilters(type);
        reportTypeInput.value = type;
    }

    reportCards.forEach(card => {
        card.addEventListener('click', function () {
            setCardActive(this.dataset.report);

            if (this.dataset.report === 'teacher_schedule' && teacherSelect && !teacherSelect.value) {
                return;
            }

            if (this.dataset.report === 'room_utilization' && roomSelect && !roomSelect.value) {
                return;
            }

            if (this.dataset.report === 'section_schedule' && sectionSelect && !sectionSelect.value) {
                return;
            }

            form.submit();
        });
    });

    summarySwitches.forEach(button => {
        button.addEventListener('click', function () {
            setCardActive(this.dataset.report);
            form.submit();
        });
    });

    setCardActive(reportTypeInput.value);
});
</script>
@endpush
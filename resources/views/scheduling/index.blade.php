@extends('layouts.app')

@php
    $pageTitle = 'Scheduling';
    $pageSubtitle = 'Create and manage section-based class schedules';

    $selectedGrade = $gradeLevels->firstWhere('id', $selectedGradeLevelId);
    $encodedEntriesCount = $scheduleEntries->count();
    $selectedShiftName = $selectedSection?->shiftType?->name ?? 'No Shift';
    $selectedRoomName = $selectedSection?->room?->name ?? 'No Default Room';

    $firstFilledEntry = $scheduleEntries->first();
    $initialDetailDay = $firstFilledEntry ? ucfirst($firstFilledEntry->day_of_week) : 'No selected day';
    $initialDetailTime = $firstFilledEntry?->timeSlot?->time_range ?? 'No selected time';
    $initialDetailSubject = $firstFilledEntry?->subject?->name ?? 'No assigned subject';
    $initialDetailTeacher = $firstFilledEntry?->teacher?->full_name ?? 'No assigned teacher';
    $initialDetailRoom = $firstFilledEntry?->room?->name ?? 'No assigned room';

    $overloadTeacherCount = collect($sectionTeachers)->filter(fn ($item) => $item['remaining_hours'] < 0)->count();
    $teacherAssignmentCoverage = $subjects->count() > 0
        ? $subjects->filter(fn ($subject) => $teachers->contains(fn ($teacher) => $teacher->teacherSubjects->contains('subject_id', $subject->id)))->count()
        : 0;
@endphp

@section('content')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <div class="fw-semibold mb-1">Please check the schedule entry details.</div>
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h5 class="fw-bold mb-1">Scheduling Workspace</h5>
        <p class="text-muted mb-0">
            Build and review class schedules by grade level, section, and shift-based timetable.
        </p>
    </div>

    <div class="d-flex gap-2 flex-wrap">
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
            <i class="bi bi-plus-lg me-1"></i> Add Schedule Entry
        </button>
        <button class="btn btn-outline-secondary btn-sm" type="button" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Print Preview
        </button>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card summary-card p-3">
            <div class="text-muted small">Selected Grade</div>
            <div class="fw-bold fs-4">{{ $selectedGrade?->name ?? 'No Grade' }}</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card summary-card p-3">
            <div class="text-muted small">Selected Section</div>
            <div class="fw-bold fs-4">{{ $selectedSection?->name ?? 'No Section' }}</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card summary-card p-3">
            <div class="text-muted small">Shift Type</div>
            <div class="fw-bold fs-4">{{ $selectedShiftName }}</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card summary-card p-3">
            <div class="text-muted small">Encoded Entries</div>
            <div class="fw-bold fs-4">{{ $encodedEntriesCount }}</div>
        </div>
    </div>
</div>

<form method="GET" action="{{ route('scheduling.index') }}" class="card content-card mb-4">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-semibold">School Year</label>
                <select class="form-select" disabled>
                    <option selected>{{ $activeSchoolYear?->name ?? 'No Active School Year' }}</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Term</label>
                <select class="form-select" disabled>
                    <option selected>{{ $activeTerm?->name ?? 'No Active Term' }}</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Grade Level</label>
                <select class="form-select" id="gradeLevelFilter" name="grade_level_id" onchange="this.form.submit()">
                    @foreach ($gradeLevels as $gradeLevel)
                        <option value="{{ $gradeLevel->id }}" {{ (int) $selectedGradeLevelId === (int) $gradeLevel->id ? 'selected' : '' }}>
                            {{ $gradeLevel->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold">Section</label>
                <select class="form-select" id="sectionFilter" name="section_id" onchange="this.form.submit()">
                    @forelse ($sections as $section)
                        <option value="{{ $section->id }}" {{ (int) $selectedSectionId === (int) $section->id ? 'selected' : '' }}>
                            {{ $section->name }}
                        </option>
                    @empty
                        <option value="">No sections available</option>
                    @endforelse
                </select>
            </div>
        </div>
    </div>
</form>

<div class="row g-4">
    <div class="col-12 col-xl-8">
        <div class="card content-card h-100">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-1">Weekly Timetable Grid</h5>
                        <p class="text-muted small mb-0">
                            Click a schedule cell to view details. Click an empty slot to encode a new schedule entry.
                        </p>
                    </div>

                    <span class="badge text-bg-light border">
                        {{ $selectedSection?->shiftType?->name ?? 'No Shift' }}
                    </span>
                </div>
            </div>

            <div class="card-body p-4">
                @if ($selectedSection && $timeSlots->isNotEmpty())
                    <div class="table-responsive schedule-grid-wrapper">
                        <table class="table table-bordered align-middle schedule-grid mb-0" id="scheduleGrid">
                            <thead class="table-light">
                                <tr>
                                    <th style="min-width: 140px;">Time</th>
                                    @foreach ($daysOfWeek as $dayKey => $dayLabel)
                                        <th style="min-width: 160px;">{{ $dayLabel }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody id="scheduleGridBody">
                                @foreach ($timeSlots as $timeSlot)
                                    <tr>
                                        <th>{{ $timeSlot->time_range }}</th>

                                        @foreach ($daysOfWeek as $dayKey => $dayLabel)
                                            @php
                                                $entry = $scheduleMap[$timeSlot->id][$dayKey] ?? null;
                                            @endphp

                                            @if ($timeSlot->is_break)
                                                <td class="schedule-cell break-cell">Break</td>
                                            @elseif ($entry)
                                                <td
                                                    class="schedule-cell filled"
                                                    data-entry-id="{{ $entry->id }}"
                                                    data-day="{{ $dayLabel }}"
                                                    data-time="{{ $entry->timeSlot?->time_range }}"
                                                    data-subject="{{ $entry->subject?->name }}"
                                                    data-teacher="{{ $entry->teacher?->full_name }}"
                                                    data-room="{{ $entry->room?->name ?? 'No assigned room' }}"
                                                >
                                                    <div class="cell-subject">{{ $entry->subject?->name }}</div>
                                                    <div class="cell-meta">{{ $entry->teacher?->full_name }}</div>
                                                    <div class="cell-meta">{{ $entry->room?->name ?? 'No assigned room' }}</div>
                                                </td>
                                            @else
                                                <td
                                                    class="schedule-cell empty-cell"
                                                    data-day-key="{{ $dayKey }}"
                                                    data-day="{{ $dayLabel }}"
                                                    data-time-slot-id="{{ $timeSlot->id }}"
                                                    data-time="{{ $timeSlot->time_range }}"
                                                >
                                                    <div class="empty-label">Available Slot</div>
                                                </td>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="border rounded-4 p-4 text-center text-muted">
                        Select a section with an available shift timeframe to display the scheduling grid.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card content-card mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold mb-1">Selected Schedule Details</h5>
                <p class="text-muted small mb-0">Live preview of the selected timetable cell.</p>
            </div>

            <div class="card-body p-4">
                <div class="border rounded-4 p-3 bg-light mb-3">
                    <div class="text-muted small">Day</div>
                    <div class="fw-semibold" id="detailDay">{{ $initialDetailDay }}</div>
                </div>

                <div class="border rounded-4 p-3 bg-light mb-3">
                    <div class="text-muted small">Time</div>
                    <div class="fw-semibold" id="detailTime">{{ $initialDetailTime }}</div>
                </div>

                <div class="border rounded-4 p-3 bg-light mb-3">
                    <div class="text-muted small">Subject</div>
                    <div class="fw-semibold" id="detailSubject">{{ $initialDetailSubject }}</div>
                </div>

                <div class="border rounded-4 p-3 bg-light mb-3">
                    <div class="text-muted small">Teacher</div>
                    <div class="fw-semibold" id="detailTeacher">{{ $initialDetailTeacher }}</div>
                </div>

                <div class="border rounded-4 p-3 bg-light mb-3">
                    <div class="text-muted small">Room</div>
                    <div class="fw-semibold" id="detailRoom">{{ $initialDetailRoom }}</div>
                </div>

                <div class="d-grid gap-2">
                    <button class="btn btn-primary btn-sm" type="button" id="detailEditButton" disabled>
                        Edit Entry
                    </button>
                    <button class="btn btn-outline-danger btn-sm" type="button" id="detailDeleteButton" disabled>
                        Delete Entry
                    </button>
                </div>
            </div>
        </div>

        <div class="card content-card mb-4">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold mb-1">Subject Hours Tracker</h5>
                <p class="text-muted small mb-0">Monitor weekly hour allocation for the selected section.</p>
            </div>

            <div class="card-body p-4">
                @forelse ($subjectHours as $hourItem)
                    @php
                        $required = max((float) $hourItem['required_hours'], 0.01);
                        $assigned = (float) $hourItem['assigned_hours'];
                        $percent = min(100, round(($assigned / $required) * 100, 2));
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>{{ $hourItem['subject']->name }}</span>
                            <span>{{ number_format($assigned, 2) }} / {{ number_format((float) $hourItem['required_hours'], 2) }} hrs</span>
                        </div>
                            <div class="progress" style="height: 8px;">
                                <div
                                    class="progress-bar progress-bar-dynamic"
                                    role="progressbar"
                                    data-width="{{ number_format($percent, 2, '.', '') }}"
                                    aria-valuenow="{{ $percent }}"
                                    aria-valuemin="0"
                                    aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted small">No subjects available for the selected section and term.</div>
                @endforelse
            </div>
        </div>

        <div class="card content-card">
            <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold mb-1">Validation Status</h5>
                <p class="text-muted small mb-0">Prototype scheduling checks shown as status indicators.</p>
            </div>

            <div class="card-body p-4">
                <div class="border rounded-4 p-3 mb-3">
                    <div class="fw-semibold mb-1">Teacher Conflict</div>
                    <div class="{{ $scheduleEntries->isNotEmpty() ? 'text-success' : 'text-muted' }} small">
                        Teacher conflict validation is active for saving and updating schedule entries.
                    </div>
                </div>

                <div class="border rounded-4 p-3 mb-3">
                    <div class="fw-semibold mb-1">Section Conflict</div>
                    <div class="{{ $selectedSection ? 'text-success' : 'text-muted' }} small">
                        Duplicate time-slot protection is active for the selected section.
                    </div>
                </div>

                <div class="border rounded-4 p-3 mb-3">
                    <div class="fw-semibold mb-1">Teacher Assignment</div>
                    <div class="{{ $teacherAssignmentCoverage > 0 ? 'text-success' : 'text-warning' }} small">
                        {{ $teacherAssignmentCoverage }} subject(s) currently have at least one qualified teacher assignment.
                    </div>
                </div>

                <div class="border rounded-4 p-3">
                    <div class="fw-semibold mb-1">Teacher Load Warning</div>
                    <div class="{{ $overloadTeacherCount > 0 ? 'text-warning' : 'text-success' }} small">
                        {{ $overloadTeacherCount > 0 ? $overloadTeacherCount . ' teacher(s) currently exceed maximum load.' : 'No overload warning detected in the current loaded data.' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if ($selectedSection && $activeTerm)
    <div class="modal fade" id="addScheduleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form method="POST" action="{{ route('scheduling.entries.store') }}" id="addScheduleForm">
                    @csrf
                    <input type="hidden" name="school_term_id" value="{{ $activeTerm->id }}">
                    <input type="hidden" name="section_id" value="{{ $selectedSection->id }}">

                    <div class="modal-header">
                        <h5 class="modal-title">Add Schedule Entry</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Section</label>
                                <input type="text" class="form-control" value="{{ $selectedSection->name }}" disabled>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Shift Type</label>
                                <input type="text" class="form-control" value="{{ $selectedSection->shiftType?->name }}" disabled>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Day of Week</label>
                                <select name="day_of_week" id="addDayOfWeek" class="form-select" required>
                                    <option value="">Select day</option>
                                    @foreach ($daysOfWeek as $dayKey => $dayLabel)
                                        <option value="{{ $dayKey }}">{{ $dayLabel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Time Slot</label>
                                <select name="time_slot_id" id="addTimeSlotId" class="form-select" required>
                                    <option value="">Select time slot</option>
                                    @foreach ($timeSlots->where('is_break', false) as $timeSlot)
                                        <option value="{{ $timeSlot->id }}">{{ $timeSlot->time_range }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Subject</label>
                                <select name="subject_id" id="addSubjectId" class="form-select subject-select" data-target-teacher="#addTeacherId" required>
                                    <option value="">Select subject</option>
                                    @foreach ($subjects as $subject)
                                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Teacher</label>
                                <select name="teacher_id" id="addTeacherId" class="form-select teacher-select" required>
                                    <option value="">Select teacher</option>
                                    @foreach ($teachers as $teacher)
                                        <option
                                            value="{{ $teacher->id }}"
                                            data-subject-ids="{{ $teacher->teacherSubjects->pluck('subject_id')->implode(',') }}"
                                        >
                                            {{ $teacher->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Teachers are filtered based on subject assignment.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Room</label>
                                <select name="room_id" class="form-select">
                                    <option value="">No room assigned</option>
                                    @foreach ($rooms as $room)
                                        <option value="{{ $room->id }}" {{ (int) $selectedSection->room_id === (int) $room->id ? 'selected' : '' }}>
                                            {{ $room->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Default Room Reference</label>
                                <input type="text" class="form-control" value="{{ $selectedRoomName }}" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-primary" type="submit">Save Schedule Entry</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @foreach ($scheduleEntries as $entry)
        <div class="modal fade" id="editScheduleModal{{ $entry->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <form method="POST" action="{{ route('scheduling.entries.update', $entry) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="school_term_id" value="{{ $activeTerm->id }}">
                        <input type="hidden" name="section_id" value="{{ $selectedSection->id }}">

                        <div class="modal-header">
                            <h5 class="modal-title">Edit Schedule Entry</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Section</label>
                                    <input type="text" class="form-control" value="{{ $selectedSection->name }}" disabled>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Shift Type</label>
                                    <input type="text" class="form-control" value="{{ $selectedSection->shiftType?->name }}" disabled>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Day of Week</label>
                                    <select name="day_of_week" class="form-select" required>
                                        @foreach ($daysOfWeek as $dayKey => $dayLabel)
                                            <option value="{{ $dayKey }}" {{ $entry->day_of_week === $dayKey ? 'selected' : '' }}>
                                                {{ $dayLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Time Slot</label>
                                    <select name="time_slot_id" class="form-select" required>
                                        @foreach ($timeSlots->where('is_break', false) as $timeSlot)
                                            <option value="{{ $timeSlot->id }}" {{ (int) $entry->time_slot_id === (int) $timeSlot->id ? 'selected' : '' }}>
                                                {{ $timeSlot->time_range }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Subject</label>
                                    <select
                                        name="subject_id"
                                        class="form-select subject-select"
                                        data-target-teacher="#editTeacherId{{ $entry->id }}"
                                        required
                                    >
                                        @foreach ($subjects as $subject)
                                            <option value="{{ $subject->id }}" {{ (int) $entry->subject_id === (int) $subject->id ? 'selected' : '' }}>
                                                {{ $subject->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Teacher</label>
                                    <select name="teacher_id" id="editTeacherId{{ $entry->id }}" class="form-select teacher-select" required>
                                        @foreach ($teachers as $teacher)
                                            <option
                                                value="{{ $teacher->id }}"
                                                data-subject-ids="{{ $teacher->teacherSubjects->pluck('subject_id')->implode(',') }}"
                                                {{ (int) $entry->teacher_id === (int) $teacher->id ? 'selected' : '' }}
                                            >
                                                {{ $teacher->full_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">Teachers are filtered based on subject assignment.</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Room</label>
                                    <select name="room_id" class="form-select">
                                        <option value="">No room assigned</option>
                                        @foreach ($rooms as $room)
                                            <option value="{{ $room->id }}" {{ (int) $entry->room_id === (int) $room->id ? 'selected' : '' }}>
                                                {{ $room->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Default Room Reference</label>
                                    <input type="text" class="form-control" value="{{ $selectedRoomName }}" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-primary" type="submit">Update Schedule Entry</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="deleteScheduleModal{{ $entry->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('scheduling.entries.destroy', $entry) }}">
                        @csrf
                        @method('DELETE')

                        <div class="modal-header">
                            <h5 class="modal-title">Delete Schedule Entry</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <p class="mb-2">Are you sure you want to delete this schedule entry?</p>
                            <div class="small text-muted">
                                {{ ucfirst($entry->day_of_week) }} • {{ $entry->timeSlot?->time_range }} • {{ $entry->subject?->name }}
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-danger" type="submit">Delete Entry</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endif

<style>
.summary-card {
    border-radius: 16px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.05);
}

.schedule-grid th,
.schedule-grid td {
    vertical-align: top;
}

.schedule-cell {
    cursor: pointer;
    background: #fff;
    transition: background-color 0.2s ease, transform 0.2s ease;
}

.schedule-cell:hover {
    background: #f8fbff;
}

.schedule-cell.filled {
    background: #eff6ff;
}

.schedule-cell.active-cell {
    outline: 2px solid #2563eb;
    background: #dbeafe;
}

.break-cell {
    background: #f3f4f6;
    color: #6b7280;
    font-weight: 600;
    text-align: center;
    cursor: default;
}

.empty-cell {
    background: #ffffff;
}

.empty-label {
    color: #9ca3af;
    font-size: 0.85rem;
}

.cell-subject {
    font-weight: 700;
    font-size: 0.92rem;
    color: #111827;
    margin-bottom: 4px;
}

.cell-meta {
    font-size: 0.78rem;
    color: #4b5563;
    line-height: 1.3;
}

.schedule-grid-wrapper {
    overflow-x: auto;
}

@media (max-width: 991.98px) {
    .schedule-grid {
        min-width: 950px;
    }
}
</style>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const scheduleCells = document.querySelectorAll('.schedule-cell.filled, .schedule-cell.empty-cell');
    const detailDay = document.getElementById('detailDay');
    const detailTime = document.getElementById('detailTime');
    const detailSubject = document.getElementById('detailSubject');
    const detailTeacher = document.getElementById('detailTeacher');
    const detailRoom = document.getElementById('detailRoom');
    const detailEditButton = document.getElementById('detailEditButton');
    const detailDeleteButton = document.getElementById('detailDeleteButton');

    const addScheduleModalElement = document.getElementById('addScheduleModal');
    const addScheduleModal = addScheduleModalElement ? new bootstrap.Modal(addScheduleModalElement) : null;
    const addDayOfWeek = document.getElementById('addDayOfWeek');
    const addTimeSlotId = document.getElementById('addTimeSlotId');

    function activateCell(cell) {
        scheduleCells.forEach(c => c.classList.remove('active-cell'));
        cell.classList.add('active-cell');
    }

    function resetDetailButtons() {
        if (detailEditButton) {
            detailEditButton.disabled = true;
            detailEditButton.onclick = null;
        }

        if (detailDeleteButton) {
            detailDeleteButton.disabled = true;
            detailDeleteButton.onclick = null;
        }
    }

    scheduleCells.forEach(cell => {
        cell.addEventListener('click', function () {
            activateCell(this);

            const entryId = this.dataset.entryId || null;
            const day = this.dataset.day || 'No selected day';
            const time = this.dataset.time || 'No selected time';
            const subject = this.dataset.subject || 'No assigned subject';
            const teacher = this.dataset.teacher || 'No assigned teacher';
            const room = this.dataset.room || 'No assigned room';

            detailDay.textContent = day;
            detailTime.textContent = time;
            detailSubject.textContent = subject;
            detailTeacher.textContent = teacher;
            detailRoom.textContent = room;

            if (entryId) {
                if (detailEditButton) {
                    detailEditButton.disabled = false;
                    detailEditButton.onclick = function () {
                        const modalElement = document.getElementById('editScheduleModal' + entryId);
                        if (modalElement) {
                            bootstrap.Modal.getOrCreateInstance(modalElement).show();
                        }
                    };
                }

                if (detailDeleteButton) {
                    detailDeleteButton.disabled = false;
                    detailDeleteButton.onclick = function () {
                        const modalElement = document.getElementById('deleteScheduleModal' + entryId);
                        if (modalElement) {
                            bootstrap.Modal.getOrCreateInstance(modalElement).show();
                        }
                    };
                }
            } else {
                resetDetailButtons();

                if (addDayOfWeek && this.dataset.dayKey) {
                    addDayOfWeek.value = this.dataset.dayKey;
                }

                if (addTimeSlotId && this.dataset.timeSlotId) {
                    addTimeSlotId.value = this.dataset.timeSlotId;
                }

                if (addScheduleModal) {
                    addScheduleModal.show();
                }
            }
        });
    });

    function applyTeacherFilter(subjectSelect) {
        const teacherTargetSelector = subjectSelect.dataset.targetTeacher;
        if (!teacherTargetSelector) {
            return;
        }

        const teacherSelect = document.querySelector(teacherTargetSelector);
        if (!teacherSelect) {
            return;
        }

        const selectedSubjectId = subjectSelect.value;
        const options = teacherSelect.querySelectorAll('option');

        options.forEach(option => {
            if (!option.value) {
                option.hidden = false;
                return;
            }

            const subjectIds = (option.dataset.subjectIds || '')
                .split(',')
                .map(value => value.trim())
                .filter(Boolean);

            const allowed = !selectedSubjectId || subjectIds.includes(selectedSubjectId);

            option.hidden = !allowed;
        });

        const currentSelected = teacherSelect.options[teacherSelect.selectedIndex];
        if (currentSelected && currentSelected.hidden) {
            teacherSelect.value = '';
        }
    }

    document.querySelectorAll('.subject-select').forEach(subjectSelect => {
        applyTeacherFilter(subjectSelect);

        subjectSelect.addEventListener('change', function () {
            applyTeacherFilter(this);
        });
    });
});
</script>
@endpush
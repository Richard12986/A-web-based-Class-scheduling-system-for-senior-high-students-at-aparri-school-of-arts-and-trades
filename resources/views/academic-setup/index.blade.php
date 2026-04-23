@extends('layouts.app')

@php
    $pageTitle = 'Academic Setup';
    $pageSubtitle = 'Manage academic structure and scheduling configuration';

    $totalSections = $sections->count();
    $totalShiftTypes = $shiftTypes->count();

    $subjectTypeLabels = [
        'core' => 'Core',
        'elective' => 'Elective',
        'hgp' => 'HGP',
    ];

    $roomTypeLabels = [
        'general' => 'General',
        'laboratory' => 'Laboratory',
        'workshop' => 'Workshop',
    ];

    $shiftTypeMap = $shiftTypes->keyBy('id');
@endphp

@section('content')

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <div class="fw-semibold mb-1">Please check the form input.</div>
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
        <h5 class="fw-bold mb-1">Academic Configuration Workspace</h5>
        <p class="text-muted mb-0">
            Configure the academic structure required before scheduling.
        </p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card summary-card p-3">
            <div class="text-muted small">Active School Year</div>
            <div class="fw-bold fs-4">{{ $activeSchoolYear?->name ?? 'Not Set' }}</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card summary-card p-3">
            <div class="text-muted small">Active Term</div>
            <div class="fw-bold fs-4">{{ $activeSchoolTerm?->name ?? 'Not Set' }}</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card summary-card p-3">
            <div class="text-muted small">Total Sections</div>
            <div class="fw-bold fs-4">{{ $totalSections }}</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card summary-card p-3">
            <div class="text-muted small">Shift Types</div>
            <div class="fw-bold fs-4">{{ $totalShiftTypes }}</div>
        </div>
    </div>
</div>

<div class="card content-card mb-4 active">
    <div class="card-header fw-bold d-flex justify-content-between align-items-center toggle-btn">
        Current Academic Period
        <i class="bi bi-chevron-down"></i>
    </div>

    <div class="card-body toggle-content">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="border rounded p-3">
                    <div class="text-muted small">School Year</div>
                    <div class="fw-semibold fs-5">{{ $activeSchoolYear?->name ?? 'No Active School Year' }}</div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="border rounded p-3">
                    <div class="text-muted small">Term</div>
                    <div class="fw-semibold fs-5">{{ $activeSchoolTerm?->name ?? 'No Active Term' }}</div>
                </div>
            </div>
        </div>

        <div class="mt-3 text-muted small">
            Only the active academic period is managed in this prototype.
            Previous school years remain for reporting and historical reference only.
        </div>
    </div>
</div>

<div class="card content-card mb-4">
    <div class="card-header fw-bold d-flex justify-content-between align-items-center toggle-btn">
        Pathways and Subjects
        <i class="bi bi-chevron-down"></i>
    </div>

    <div class="card-body toggle-content">
        <div class="row g-4">
            <div class="col-md-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Pathways</h6>
                </div>

                <ul class="list-group">
                    @forelse ($pathways as $pathway)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold">{{ $pathway->name }}</div>
                                @if ($pathway->code)
                                    <div class="text-muted small">{{ $pathway->code }}</div>
                                @endif
                            </div>
                            <span class="badge {{ $pathway->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $pathway->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No pathways found.</li>
                    @endforelse
                </ul>

                <div class="mt-3">
                    <h6 class="fw-bold mb-2">Grade Levels</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @forelse ($gradeLevels as $gradeLevel)
                            <span class="badge bg-light text-dark border px-3 py-2">{{ $gradeLevel->name }}</span>
                        @empty
                            <span class="text-muted small">No grade levels found.</span>
                        @endforelse
                    </div>
                </div>

                <div class="mt-3">
                    <h6 class="fw-bold mb-2">Shift Types</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @forelse ($shiftTypes as $shiftType)
                            <span class="badge bg-light text-dark border px-3 py-2">{{ $shiftType->name }}</span>
                        @empty
                            <span class="text-muted small">No shift types found.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Subjects</h6>

                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSubjectModal">
                        <i class="bi bi-plus-lg me-1"></i> Add Subject
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Subject</th>
                                <th>Type</th>
                                <th>Weekly Hours</th>
                                <th>Offering</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($subjects as $subject)
                                <tr>
                                    <td>{{ $subject->code }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $subject->name }}</div>
                                        <div class="text-muted small">
                                            Total Hours: {{ $subject->total_hours ?? '-' }}
                                            @if ($subject->requires_special_room)
                                                • Special Room Required
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $subjectTypeLabels[$subject->subject_type] ?? ucfirst($subject->subject_type) }}</td>
                                    <td>{{ rtrim(rtrim(number_format((float) $subject->weekly_hours, 2, '.', ''), '0'), '.') }}</td>
                                    <td>{{ ucfirst($subject->offering_type) }}</td>
                                    <td>
                                        <span class="badge {{ $subject->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                            {{ $subject->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <button
                                                        type="button"
                                                        class="dropdown-item"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editSubjectModal{{ $subject->id }}">
                                                        Edit
                                                    </button>
                                                </li>
                                                <li>
                                                    <form method="POST" action="{{ route('academic-setup.subjects.destroy', $subject) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No subjects found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 text-muted small">
                    Pathways, grade levels, and shift types are treated as fixed prototype references.
                    Subjects remain editable because they support actual school-specific offerings.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card content-card mb-4">
    <div class="card-header fw-bold d-flex justify-content-between align-items-center toggle-btn">
        Sections and Rooms
        <i class="bi bi-chevron-down"></i>
    </div>

    <div class="card-body toggle-content">
        <div class="row g-4">
            <div class="col-md-7">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Sections</h6>

                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSectionModal">
                        <i class="bi bi-plus-lg me-1"></i> Add Section
                    </button>
                </div>

                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Grade</th>
                            <th>Pathway</th>
                            <th>Shift</th>
                            <th>Room</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($sections as $section)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $section->name }}</div>
                                    <div class="text-muted small">
                                        Student Count: {{ $section->student_count ?? '-' }}
                                    </div>
                                </td>
                                <td>{{ $section->gradeLevel?->name ?? '-' }}</td>
                                <td>{{ $section->pathway?->name ?? '-' }}</td>
                                <td>{{ $section->shiftType?->name ?? '-' }}</td>
                                <td>{{ $section->room?->name ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $section->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                        {{ $section->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <button
                                                    type="button"
                                                    class="dropdown-item"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editSectionModal{{ $section->id }}">
                                                    Edit
                                                </button>
                                            </li>
                                            <li>
                                                <form method="POST" action="{{ route('academic-setup.sections.destroy', $section) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No sections found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="col-md-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Rooms</h6>

                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                        <i class="bi bi-plus-lg me-1"></i> Add Room
                    </button>
                </div>

                <ul class="list-group">
                    @forelse ($rooms as $room)
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div>
                                    <div class="fw-semibold">{{ $room->name }}</div>
                                    <div class="text-muted small">
                                        {{ $roomTypeLabels[$room->room_type] ?? ucfirst($room->room_type) }}
                                        @if ($room->capacity)
                                            • Capacity: {{ $room->capacity }}
                                        @endif
                                    </div>
                                    @if ($room->notes)
                                        <div class="text-muted small">{{ $room->notes }}</div>
                                    @endif
                                </div>

                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                        <i class="bi bi-three-dots"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <button
                                                type="button"
                                                class="dropdown-item"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editRoomModal{{ $room->id }}">
                                                Edit
                                            </button>
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('academic-setup.rooms.destroy', $room) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    Delete
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="mt-2">
                                <span class="badge {{ $room->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $room->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No rooms found.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="card content-card mb-4">
    <div class="card-header fw-bold d-flex justify-content-between align-items-center toggle-btn">
        Shift Timeframe Setup
        <i class="bi bi-chevron-down"></i>
    </div>

    <div class="card-body toggle-content">
        <div class="row g-4">
            @forelse ($shiftTypes as $shiftType)
                @php
                    $shiftTimeSlots = $timeSlots->get($shiftType->id, collect());
                @endphp

                <div class="col-md-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">{{ $shiftType->name }}</h6>

                        <button
                            class="btn btn-primary btn-sm"
                            data-bs-toggle="modal"
                            data-bs-target="#addTimeSlotModal"
                            data-shift-id="{{ $shiftType->id }}"
                            data-shift-name="{{ $shiftType->name }}">
                            <i class="bi bi-plus-lg me-1"></i> Add Slot
                        </button>
                    </div>

                    <div class="border rounded p-3 h-100">
                        <div class="text-muted small mb-3">
                            This full timeframe will be reused by all sections assigned to {{ strtolower($shiftType->name) }} shift.
                        </div>

                        @if ($shiftTimeSlots->isEmpty())
                            <div class="text-muted small">
                                No timeframe has been set for this shift yet.
                            </div>
                        @else
                            <ul class="list-group list-group-flush">
                                @foreach ($shiftTimeSlots as $slot)
                                    <li class="list-group-item px-0 d-flex justify-content-between align-items-start gap-3">
                                        <div>
                                            <div class="fw-semibold">
                                                {{ \Carbon\Carbon::parse($slot->start_time)->format('g:i A') }}
                                                –
                                                {{ \Carbon\Carbon::parse($slot->end_time)->format('g:i A') }}
                                            </div>

                                            <div class="text-muted small">
                                                Order: {{ $slot->slot_order }}
                                                @if ($slot->label)
                                                    • {{ $slot->label }}
                                                @endif
                                            </div>

                                            <div class="mt-1 d-flex flex-wrap gap-1">
                                                @if ($slot->is_break)
                                                    <span class="badge bg-warning text-dark">Break</span>
                                                @endif

                                                <span class="badge {{ $slot->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                                    {{ $slot->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                                <i class="bi bi-three-dots"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <button
                                                        type="button"
                                                        class="dropdown-item"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editTimeSlotModal{{ $slot->id }}">
                                                        Edit
                                                    </button>
                                                </li>
                                                <li>
                                                    <form method="POST" action="{{ route('academic-setup.time-slots.destroy', $slot) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-muted">No shift types found.</div>
                </div>
            @endforelse
        </div>
    </div>
</div>

<style>
.summary-card {
    border-radius: 16px;
    box-shadow: 0 6px 16px rgba(0,0,0,0.05);
}

.toggle-content {
    display: none;
}

.card.active .toggle-content {
    display: block;
}

.card-header {
    cursor: pointer;
}
</style>

@endsection

@section('modals')

<div class="modal fade" id="addSubjectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('academic-setup.subjects.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Subject Code</label>
                        <input type="text" name="code" class="form-control" required>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Subject Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Subject Type</label>
                        <select name="subject_type" class="form-select" required>
                            <option value="core">Core</option>
                            <option value="elective">Elective</option>
                            <option value="hgp">HGP</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Weekly Hours</label>
                        <input type="number" name="weekly_hours" class="form-control" min="0" step="0.01" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Total Hours</label>
                        <input type="number" name="total_hours" class="form-control" min="0" step="0.01">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Offering Type</label>
                        <select name="offering_type" class="form-select" required>
                            <option value="semester">Semester</option>
                            <option value="year">Year</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input type="hidden" name="requires_special_room" value="0">
                            <input class="form-check-input" type="checkbox" name="requires_special_room" id="add_subject_special_room" value="1">
                            <label class="form-check-label" for="add_subject_special_room">
                                Requires Special Room
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save Subject</button>
            </div>
        </form>
    </div>
</div>

@foreach ($subjects as $subject)
<div class="modal fade" id="editSubjectModal{{ $subject->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('academic-setup.subjects.update', $subject) }}" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Edit Subject</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Subject Code</label>
                        <input type="text" name="code" class="form-control" value="{{ $subject->code }}" required>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Subject Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $subject->name }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Subject Type</label>
                        <select name="subject_type" class="form-select" required>
                            <option value="core" @selected($subject->subject_type === 'core')>Core</option>
                            <option value="elective" @selected($subject->subject_type === 'elective')>Elective</option>
                            <option value="hgp" @selected($subject->subject_type === 'hgp')>HGP</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Weekly Hours</label>
                        <input type="number" name="weekly_hours" class="form-control" min="0" step="0.01" value="{{ $subject->weekly_hours }}" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Total Hours</label>
                        <input type="number" name="total_hours" class="form-control" min="0" step="0.01" value="{{ $subject->total_hours }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Offering Type</label>
                        <select name="offering_type" class="form-select" required>
                            <option value="semester" @selected($subject->offering_type === 'semester')>Semester</option>
                            <option value="year" @selected($subject->offering_type === 'year')>Year</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="1" @selected($subject->is_active)>Active</option>
                            <option value="0" @selected(!$subject->is_active)>Inactive</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input type="hidden" name="requires_special_room" value="0">
                            <input class="form-check-input" type="checkbox" name="requires_special_room" id="edit_subject_special_room_{{ $subject->id }}" value="1" @checked($subject->requires_special_room)>
                            <label class="form-check-label" for="edit_subject_special_room_{{ $subject->id }}">
                                Requires Special Room
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Update Subject</button>
            </div>
        </form>
    </div>
</div>
@endforeach

<div class="modal fade" id="addSectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('academic-setup.sections.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Section</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Section Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Student Count</label>
                        <input type="number" name="student_count" class="form-control" min="0">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Grade Level</label>
                        <select name="grade_level_id" class="form-select" required>
                            @foreach ($gradeLevels as $gradeLevel)
                                <option value="{{ $gradeLevel->id }}">{{ $gradeLevel->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Pathway</label>
                        <select name="pathway_id" class="form-select" required>
                            @foreach ($pathways as $pathway)
                                <option value="{{ $pathway->id }}">{{ $pathway->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Shift Type</label>
                        <select name="shift_type_id" class="form-select" required>
                            @foreach ($shiftTypes as $shiftType)
                                <option value="{{ $shiftType->id }}">{{ $shiftType->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Assigned Room</label>
                        <select name="room_id" class="form-select">
                            <option value="">No Assigned Room</option>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->id }}">{{ $room->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save Section</button>
            </div>
        </form>
    </div>
</div>

@foreach ($sections as $section)
<div class="modal fade" id="editSectionModal{{ $section->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('academic-setup.sections.update', $section) }}" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Edit Section</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Section Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $section->name }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Student Count</label>
                        <input type="number" name="student_count" class="form-control" min="0" value="{{ $section->student_count }}">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Grade Level</label>
                        <select name="grade_level_id" class="form-select" required>
                            @foreach ($gradeLevels as $gradeLevel)
                                <option value="{{ $gradeLevel->id }}" @selected($section->grade_level_id === $gradeLevel->id)>
                                    {{ $gradeLevel->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Pathway</label>
                        <select name="pathway_id" class="form-select" required>
                            @foreach ($pathways as $pathway)
                                <option value="{{ $pathway->id }}" @selected($section->pathway_id === $pathway->id)>
                                    {{ $pathway->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Shift Type</label>
                        <select name="shift_type_id" class="form-select" required>
                            @foreach ($shiftTypes as $shiftType)
                                <option value="{{ $shiftType->id }}" @selected($section->shift_type_id === $shiftType->id)>
                                    {{ $shiftType->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Assigned Room</label>
                        <select name="room_id" class="form-select">
                            <option value="">No Assigned Room</option>
                            @foreach ($rooms as $room)
                                <option value="{{ $room->id }}" @selected($section->room_id === $room->id)>
                                    {{ $room->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="1" @selected($section->is_active)>Active</option>
                            <option value="0" @selected(!$section->is_active)>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Update Section</button>
            </div>
        </form>
    </div>
</div>
@endforeach

<div class="modal fade" id="addRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('academic-setup.rooms.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Room Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Room Type</label>
                        <select name="room_type" class="form-select" required>
                            <option value="general">General</option>
                            <option value="laboratory">Laboratory</option>
                            <option value="workshop">Workshop</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Capacity</label>
                        <input type="number" name="capacity" class="form-control" min="1">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save Room</button>
            </div>
        </form>
    </div>
</div>

@foreach ($rooms as $room)
<div class="modal fade" id="editRoomModal{{ $room->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('academic-setup.rooms.update', $room) }}" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Edit Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Room Name</label>
                        <input type="text" name="name" class="form-control" value="{{ $room->name }}" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Room Type</label>
                        <select name="room_type" class="form-select" required>
                            <option value="general" @selected($room->room_type === 'general')>General</option>
                            <option value="laboratory" @selected($room->room_type === 'laboratory')>Laboratory</option>
                            <option value="workshop" @selected($room->room_type === 'workshop')>Workshop</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Capacity</label>
                        <input type="number" name="capacity" class="form-control" min="1" value="{{ $room->capacity }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="1" @selected($room->is_active)>Active</option>
                            <option value="0" @selected(!$room->is_active)>Inactive</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ $room->notes }}</textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Update Room</button>
            </div>
        </form>
    </div>
</div>
@endforeach

<div class="modal fade" id="addTimeSlotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('academic-setup.time-slots.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Add Time Slot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="border rounded p-3 bg-light mb-3">
                    <div class="text-muted small">Shift-Based Timeframe</div>
                    <div class="fw-semibold" id="addTimeSlotShiftLabel">Select a shift</div>
                    <div class="text-muted small">
                        This slot becomes part of the full reusable timeframe of the selected shift.
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Shift Type</label>
                        <select name="shift_type_id" id="addTimeSlotShiftInput" class="form-select" required>
                            @foreach ($shiftTypes as $shiftType)
                                <option value="{{ $shiftType->id }}">{{ $shiftType->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="start_time" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">End Time</label>
                        <input type="time" name="end_time" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Slot Order</label>
                        <input type="number" name="slot_order" class="form-control" min="1" required>
                    </div>

                    <div class="col-md-8">
                        <label class="form-label">Label</label>
                        <input type="text" name="label" class="form-control" placeholder="Optional, e.g. Break or Lunch">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="1" selected>Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-check">
                            <input type="hidden" name="is_break" value="0">
                            <input class="form-check-input" type="checkbox" name="is_break" id="add_time_slot_break" value="1">
                            <label class="form-check-label" for="add_time_slot_break">
                                Mark as Break
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save Time Slot</button>
            </div>
        </form>
    </div>
</div>

@foreach ($timeSlots as $shiftId => $slotGroup)
    @foreach ($slotGroup as $slot)
    <div class="modal fade" id="editTimeSlotModal{{ $slot->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="{{ route('academic-setup.time-slots.update', $slot) }}" class="modal-content">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit Time Slot</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Shift Type</label>
                            <select name="shift_type_id" class="form-select" required>
                                @foreach ($shiftTypes as $shiftType)
                                    <option value="{{ $shiftType->id }}" @selected($slot->shift_type_id === $shiftType->id)>
                                        {{ $shiftType->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Start Time</label>
                            <input type="time" name="start_time" class="form-control" value="{{ \Carbon\Carbon::parse($slot->start_time)->format('H:i') }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">End Time</label>
                            <input type="time" name="end_time" class="form-control" value="{{ \Carbon\Carbon::parse($slot->end_time)->format('H:i') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Slot Order</label>
                            <input type="number" name="slot_order" class="form-control" min="1" value="{{ $slot->slot_order }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Label</label>
                            <input type="text" name="label" class="form-control" value="{{ $slot->label }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="is_active" class="form-select">
                                <option value="1" @selected($slot->is_active)>Active</option>
                                <option value="0" @selected(!$slot->is_active)>Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check">
                                <input type="hidden" name="is_break" value="0">
                                <input class="form-check-input" type="checkbox" name="is_break" id="edit_time_slot_break_{{ $slot->id }}" value="1" @checked($slot->is_break)>
                                <label class="form-check-label" for="edit_time_slot_break_{{ $slot->id }}">
                                    Mark as Break
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update Time Slot</button>
                </div>
            </form>
        </div>
    </div>
    @endforeach
@endforeach

@endsection

@push('scripts')
<script>
document.querySelectorAll('.toggle-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        btn.closest('.card').classList.toggle('active');
    });
});

document.addEventListener('DOMContentLoaded', function () {
    const addTimeSlotModal = document.getElementById('addTimeSlotModal');

    if (addTimeSlotModal) {
        addTimeSlotModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const shiftId = button?.getAttribute('data-shift-id');
            const shiftName = button?.getAttribute('data-shift-name');

            const shiftInput = document.getElementById('addTimeSlotShiftInput');
            const shiftLabel = document.getElementById('addTimeSlotShiftLabel');

            if (shiftInput && shiftId) {
                shiftInput.value = shiftId;
            }

            if (shiftLabel && shiftName) {
                shiftLabel.textContent = shiftName + ' Shift';
            }
        });

        const defaultShiftInput = document.getElementById('addTimeSlotShiftInput');
        const defaultShiftLabel = document.getElementById('addTimeSlotShiftLabel');

        if (defaultShiftInput && defaultShiftLabel && defaultShiftInput.options.length > 0) {
            defaultShiftLabel.textContent = defaultShiftInput.options[defaultShiftInput.selectedIndex].text + ' Shift';

            defaultShiftInput.addEventListener('change', function () {
                defaultShiftLabel.textContent = this.options[this.selectedIndex].text + ' Shift';
            });
        }
    }
});
</script>
@endpush
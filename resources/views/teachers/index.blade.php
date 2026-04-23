@extends('layouts.app')

@php
    $pageTitle = 'Teachers';
    $pageSubtitle = 'Manage teacher profiles, teaching assignments, load monitoring, and TEA records';
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
        <div class="fw-semibold mb-1">Please check the form fields.</div>
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
        <h5 class="fw-bold mb-1">Teacher Management Workspace</h5>
        <p class="text-muted mb-0">
            Manage teacher records, subject assignments, workload status, and TEA support records.
        </p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card summary-card p-3">
            <div class="text-muted small">Total Teachers</div>
            <div class="fw-bold fs-4">{{ $summary['total_teachers'] ?? 0 }}</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card summary-card p-3">
            <div class="text-muted small">Assigned Teachers</div>
            <div class="fw-bold fs-4">{{ $summary['assigned_teachers'] ?? 0 }}</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card summary-card p-3">
            <div class="text-muted small">Overload Alerts</div>
            <div class="fw-bold fs-4 text-danger">{{ $summary['overload_alerts'] ?? 0 }}</div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card summary-card p-3">
            <div class="text-muted small">Active TEA Loans</div>
            <div class="fw-bold fs-4">{{ $summary['active_tea_loans'] ?? 0 }}</div>
        </div>
    </div>
</div>

<div class="card content-card mb-4 active">
    <div class="card-header fw-bold d-flex justify-content-between align-items-center toggle-btn">
        Teacher Directory
        <i class="bi bi-chevron-down"></i>
    </div>

    <div class="card-body toggle-content">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div class="text-muted small">
                Basic teacher and employee records used for scheduling, TEA reference, and current room monitoring.
            </div>

            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
                <i class="bi bi-plus-lg me-1"></i> Add Teacher
            </button>
        </div>

        <form method="GET" action="{{ route('teachers.index') }}" class="row g-3 mb-3">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <i class="bi bi-search"></i>
                    </span>
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        id="teacherSearchInput"
                        value="{{ $search ?? '' }}"
                        placeholder="Search teacher by name, employee no., or room">
                    <button class="btn btn-outline-secondary" type="submit">Search</button>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-4">
                <div class="border rounded-3 px-3 py-2 bg-light small text-muted h-100 d-flex align-items-center">
                    Current room location is based on the active class schedule for the current time slot.
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle" id="teacherDirectoryTable">
                <thead class="table-light">
                    <tr>
                        <th>Employee No.</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Max Weekly Load</th>
                        <th>Current Room</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($teacherDirectory as $directoryItem)
                        @php
                            $teacher = $directoryItem['teacher'];
                            $currentSchedule = $directoryItem['current_schedule'];
                        @endphp
                        <tr>
                            <td>{{ $teacher->employee_number }}</td>
                            <td>
                                <div class="fw-semibold">{{ $teacher->full_name }}</div>
                                <div class="text-muted small">{{ $teacher->email ?: 'No email provided' }}</div>
                            </td>
                            <td>{{ $teacher->contact_number ?: '—' }}</td>
                            <td>{{ number_format((float) $teacher->maximum_weekly_load, 2) }} hrs</td>
                            <td>
                                @if ($currentSchedule)
                                    <div class="fw-semibold">{{ $directoryItem['current_room_name'] }}</div>
                                    <div class="text-muted small">{{ $directoryItem['current_room_meta'] }}</div>
                                @else
                                    <div class="fw-semibold text-muted">No active class</div>
                                    <div class="text-muted small">{{ $directoryItem['current_room_meta'] }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $teacher->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $teacher->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                        Manage
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <button
                                                type="button"
                                                class="dropdown-item"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editTeacherModal{{ $teacher->id }}">
                                                Edit Record
                                            </button>
                                        </li>
                                        <li>
                                            <button
                                                type="button"
                                                class="dropdown-item"
                                                data-bs-toggle="modal"
                                                data-bs-target="#assignSubjectModal{{ $teacher->id }}">
                                                Assign Subject
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('teachers.records.destroy', $teacher) }}"
                                                onsubmit="return confirm('Delete this teacher record?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">Delete</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No teacher records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card content-card mb-4">
    <div class="card-header fw-bold d-flex justify-content-between align-items-center toggle-btn">
        Teaching Assignments
        <i class="bi bi-chevron-down"></i>
    </div>

    <div class="card-body toggle-content">
        <div class="text-muted small mb-3">
            Assign which subjects each teacher can handle. This is the basis for teacher filtering during scheduling.
        </div>

        <div class="row g-4">
            @forelse ($teachers as $teacher)
                <div class="col-12 col-lg-6">
                    <div class="border rounded-4 p-3 h-100">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                            <div>
                                <div class="fw-semibold">{{ $teacher->full_name }}</div>
                                <div class="text-muted small">{{ $teacher->employee_number }}</div>
                            </div>

                            <button
                                type="button"
                                class="btn btn-outline-primary btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#assignSubjectModal{{ $teacher->id }}">
                                Add Assignment
                            </button>
                        </div>

                        @if ($teacher->teacherSubjects->isNotEmpty())
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($teacher->teacherSubjects as $teacherSubject)
                                    <div class="border rounded-pill px-3 py-2 d-flex align-items-center gap-2 bg-light">
                                        <span class="small">{{ $teacherSubject->subject?->name }}</span>
                                        <form method="POST" action="{{ route('teachers.subject-assignments.destroy', $teacherSubject) }}"
                                            onsubmit="return confirm('Remove this teaching assignment?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent text-danger">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-muted small">No subject assignments yet.</div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="border rounded-4 p-4 text-center text-muted">
                        No teachers available for subject assignment.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

<div class="card content-card mb-4">
    <div class="card-header fw-bold d-flex justify-content-between align-items-center toggle-btn">
        Load Monitoring
        <i class="bi bi-chevron-down"></i>
    </div>

    <div class="card-body toggle-content">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div class="text-muted small">
                Computed teaching load based on assigned schedules in the active term.
            </div>
            <span class="badge text-bg-light border">
                {{ $activeTerm?->name ?? 'No Active Term' }}
            </span>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Teacher</th>
                        <th>Max Load</th>
                        <th>Assigned Hours</th>
                        <th>Remaining</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($loadMonitoring as $loadItem)
                        <tr>
                            <td>{{ $loadItem['teacher']->full_name }}</td>
                            <td>{{ number_format((float) $loadItem['teacher']->maximum_weekly_load, 2) }} hrs</td>
                            <td>{{ number_format((float) $loadItem['assigned_hours'], 2) }} hrs</td>
                            <td>{{ number_format((float) $loadItem['remaining_hours'], 2) }} hrs</td>
                            <td>
                                @if ($loadItem['status'] === 'overload')
                                    <span class="badge text-bg-danger">Overload</span>
                                @elseif ($loadItem['status'] === 'underload')
                                    <span class="badge text-bg-warning">Underload</span>
                                @elseif ($loadItem['status'] === 'unassigned')
                                    <span class="badge text-bg-secondary">Unassigned</span>
                                @else
                                    <span class="badge text-bg-success">Normal</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No load monitoring data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3 text-muted small">
            In this prototype, load status is shown as a monitoring view only. Final validation will be connected in the Scheduling module.
        </div>
    </div>
</div>

<div class="card content-card mb-4">
    <div class="card-header fw-bold d-flex justify-content-between align-items-center toggle-btn">
        TEA Credit and Loan Records
        <i class="bi bi-chevron-down"></i>
    </div>

    <div class="card-body toggle-content">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div class="text-muted small">
                Prototype support records for teacher and employee credit or loan entries.
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addLoanModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Loan
                </button>
                <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                    Record Payment
                </button>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="border rounded-4 p-3 bg-light h-100">
                    <div class="text-muted small">Total Active Loans</div>
                    <div class="fw-bold fs-4">{{ $summary['active_tea_loans'] ?? 0 }}</div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="border rounded-4 p-3 bg-light h-100">
                    <div class="text-muted small">Paid Loans</div>
                    <div class="fw-bold fs-4">{{ $summary['paid_loans'] ?? 0 }}</div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="border rounded-4 p-3 bg-light h-100">
                    <div class="text-muted small">Teachers with Balance</div>
                    <div class="fw-bold fs-4">{{ $summary['teachers_with_balance'] ?? 0 }}</div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="border rounded-4 p-3 h-100">
                    <h6 class="fw-bold mb-3">Loan Records</h6>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Teacher</th>
                                    <th>Loan Type</th>
                                    <th>Principal</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($teacherLoans as $loan)
                                    <tr>
                                        <td>{{ $loan->teacher?->full_name }}</td>
                                        <td>{{ $loan->loan_type }}</td>
                                        <td>₱{{ number_format((float) $loan->principal_amount, 2) }}</td>
                                        <td>₱{{ number_format((float) $loan->outstanding_balance, 2) }}</td>
                                        <td>
                                            @if ($loan->status === 'active')
                                                <span class="badge text-bg-primary">Active</span>
                                            @elseif ($loan->status === 'paid')
                                                <span class="badge text-bg-success">Paid</span>
                                            @else
                                                <span class="badge text-bg-secondary">Cancelled</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                                    View
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <button
                                                            type="button"
                                                            class="dropdown-item"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editLoanModal{{ $loan->id }}">
                                                            Edit Loan
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button
                                                            type="button"
                                                            class="dropdown-item"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#loanDetailsModal{{ $loan->id }}">
                                                            View Details
                                                        </button>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form method="POST" action="{{ route('teachers.loans.destroy', $loan) }}"
                                                            onsubmit="return confirm('Delete this loan record?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">Delete</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No loan records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="border rounded-4 p-3 h-100">
                    <h6 class="fw-bold mb-3">Payment History Preview</h6>

                    @if ($paymentHistory->isNotEmpty())
                        <ul class="list-group">
                            @foreach ($paymentHistory->take(6) as $payment)
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between gap-2">
                                        <div>
                                            <div class="fw-semibold">
                                                {{ $payment->teacherLoan?->teacher?->full_name ?? 'Unknown Teacher' }}
                                            </div>
                                            <div class="text-muted small">
                                                {{ $payment->teacherLoan?->loan_type ?? 'Loan Record' }}
                                            </div>
                                            <div class="text-muted small">
                                                {{ optional($payment->payment_date)->format('F d, Y') }}
                                            </div>
                                        </div>
                                        <div class="fw-semibold">
                                            ₱{{ number_format((float) $payment->amount_paid, 2) }}
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-muted small">No payment history available yet.</div>
                    @endif

                    <div class="mt-3 text-muted small">
                        This section remains prototype-level and does not yet include payroll deduction integration.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ADD TEACHER --}}
<div class="modal fade" id="addTeacherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="{{ route('teachers.records.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Teacher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Employee Number</label>
                            <input type="text" name="employee_number" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Middle Name</label>
                            <input type="text" name="middle_name" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sex</label>
                            <select name="sex" class="form-select">
                                <option value="">Select sex</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Maximum Weekly Load</label>
                            <input type="number" step="0.01" min="0" name="maximum_weekly_load" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Number</label>
                            <input type="text" name="contact_number" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="is_active" class="form-select" required>
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Save Teacher</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- EDIT TEACHER MODALS --}}
@foreach ($teachers as $teacher)
    <div class="modal fade" id="editTeacherModal{{ $teacher->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form method="POST" action="{{ route('teachers.records.update', $teacher) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Teacher</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Employee Number</label>
                                <input type="text" name="employee_number" class="form-control" value="{{ $teacher->employee_number }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" value="{{ $teacher->first_name }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Middle Name</label>
                                <input type="text" name="middle_name" class="form-control" value="{{ $teacher->middle_name }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="{{ $teacher->last_name }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Sex</label>
                                <select name="sex" class="form-select">
                                    <option value="">Select sex</option>
                                    <option value="male" {{ $teacher->sex === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ $teacher->sex === 'female' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Maximum Weekly Load</label>
                                <input type="number" step="0.01" min="0" name="maximum_weekly_load" class="form-control" value="{{ $teacher->maximum_weekly_load }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Number</label>
                                <input type="text" name="contact_number" class="form-control" value="{{ $teacher->contact_number }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="{{ $teacher->email }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="is_active" class="form-select" required>
                                    <option value="1" {{ $teacher->is_active ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ !$teacher->is_active ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" type="submit">Update Teacher</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

{{-- ASSIGN SUBJECT MODALS --}}
@foreach ($teachers as $teacher)
    <div class="modal fade" id="assignSubjectModal{{ $teacher->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('teachers.subject-assignments.store') }}">
                    @csrf
                    <input type="hidden" name="teacher_id" value="{{ $teacher->id }}">
                    <div class="modal-header">
                        <h5 class="modal-title">Assign Subject to {{ $teacher->full_name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Subject</label>
                        <select name="subject_id" class="form-select" required>
                            <option value="">Select subject</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" type="submit">Save Assignment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

{{-- ADD LOAN --}}
<div class="modal fade" id="addLoanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('teachers.loans.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add TEA Loan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Teacher</label>
                            <select name="teacher_id" class="form-select" required>
                                <option value="">Select teacher</option>
                                @foreach ($teachers as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Loan Type</label>
                            <input type="text" name="loan_type" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Principal Amount</label>
                            <input type="number" step="0.01" min="0.01" name="principal_amount" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Loan Date</label>
                            <input type="date" name="loan_date" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="active" selected>Active</option>
                                <option value="paid">Paid</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Save Loan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- EDIT LOAN + DETAIL MODALS --}}
@foreach ($teacherLoans as $loan)
    <div class="modal fade" id="editLoanModal{{ $loan->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('teachers.loans.update', $loan) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit TEA Loan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Teacher</label>
                                <select name="teacher_id" class="form-select" required>
                                    @foreach ($teachers as $teacher)
                                        <option value="{{ $teacher->id }}" {{ $loan->teacher_id == $teacher->id ? 'selected' : '' }}>
                                            {{ $teacher->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Loan Type</label>
                                <input type="text" name="loan_type" class="form-control" value="{{ $loan->loan_type }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Principal Amount</label>
                                <input type="number" step="0.01" min="0.01" name="principal_amount" class="form-control" value="{{ $loan->principal_amount }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Loan Date</label>
                                <input type="date" name="loan_date" class="form-control" value="{{ optional($loan->loan_date)->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="active" {{ $loan->status === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="paid" {{ $loan->status === 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="cancelled" {{ $loan->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" class="form-control" rows="3">{{ $loan->remarks }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" type="submit">Update Loan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="loanDetailsModal{{ $loan->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Loan Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="border rounded-4 p-3 bg-light">
                                <div class="text-muted small">Teacher</div>
                                <div class="fw-semibold">{{ $loan->teacher?->full_name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-4 p-3 bg-light">
                                <div class="text-muted small">Loan Type</div>
                                <div class="fw-semibold">{{ $loan->loan_type }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 bg-light">
                                <div class="text-muted small">Principal</div>
                                <div class="fw-semibold">₱{{ number_format((float) $loan->principal_amount, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 bg-light">
                                <div class="text-muted small">Total Paid</div>
                                <div class="fw-semibold">₱{{ number_format((float) $loan->total_paid, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 bg-light">
                                <div class="text-muted small">Balance</div>
                                <div class="fw-semibold">₱{{ number_format((float) $loan->outstanding_balance, 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-3">Payment Records</h6>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Remarks</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($loan->payments as $payment)
                                    <tr>
                                        <td>{{ optional($payment->payment_date)->format('F d, Y') }}</td>
                                        <td>₱{{ number_format((float) $payment->amount_paid, 2) }}</td>
                                        <td>{{ $payment->remarks ?: '—' }}</td>
                                        <td class="text-end">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown">
                                                    Manage
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <button
                                                            type="button"
                                                            class="dropdown-item"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editPaymentModal{{ $payment->id }}">
                                                            Edit Payment
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <form method="POST" action="{{ route('teachers.loan-payments.destroy', $payment) }}"
                                                            onsubmit="return confirm('Delete this payment record?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">Delete</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No payment records yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($loan->remarks)
                        <div class="mt-3 text-muted small">
                            <span class="fw-semibold">Remarks:</span> {{ $loan->remarks }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endforeach

{{-- ADD PAYMENT --}}
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('teachers.loan-payments.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Record Loan Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Loan Record</label>
                            <select name="teacher_loan_id" class="form-select" required>
                                <option value="">Select loan</option>
                                @foreach ($teacherLoans as $loan)
                                    <option value="{{ $loan->id }}">
                                        {{ $loan->teacher?->full_name }} — {{ $loan->loan_type }} — ₱{{ number_format((float) $loan->outstanding_balance, 2) }} balance
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Payment Date</label>
                            <input type="date" name="payment_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Amount Paid</label>
                            <input type="number" step="0.01" min="0.01" name="amount_paid" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Save Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- EDIT PAYMENT MODALS --}}
@foreach ($paymentHistory as $payment)
    <div class="modal fade" id="editPaymentModal{{ $payment->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('teachers.loan-payments.update', $payment) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Loan Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Loan Record</label>
                                <select name="teacher_loan_id" class="form-select" required>
                                    @foreach ($teacherLoans as $loan)
                                        <option value="{{ $loan->id }}" {{ $payment->teacher_loan_id == $loan->id ? 'selected' : '' }}>
                                            {{ $loan->teacher?->full_name }} — {{ $loan->loan_type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Date</label>
                                <input type="date" name="payment_date" class="form-control" value="{{ optional($payment->payment_date)->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Amount Paid</label>
                                <input type="number" step="0.01" min="0.01" name="amount_paid" class="form-control" value="{{ $payment->amount_paid }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" class="form-control" rows="3">{{ $payment->remarks }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" type="submit">Update Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.toggle-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.closest('.card').classList.toggle('active');
        });
    });
});
</script>
@endpush
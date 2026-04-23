

<?php
    $pageTitle = 'Teachers';
    $pageSubtitle = 'Manage teacher profiles, teaching assignments, load monitoring, and TEA records';
?>

<?php $__env->startSection('content'); ?>

<?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <?php echo e(session('success')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if($errors->any()): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <div class="fw-semibold mb-1">Please check the form fields.</div>
        <ul class="mb-0 ps-3">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

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
            <div class="fw-bold fs-4"><?php echo e($summary['total_teachers'] ?? 0); ?></div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card summary-card p-3">
            <div class="text-muted small">Assigned Teachers</div>
            <div class="fw-bold fs-4"><?php echo e($summary['assigned_teachers'] ?? 0); ?></div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card summary-card p-3">
            <div class="text-muted small">Overload Alerts</div>
            <div class="fw-bold fs-4 text-danger"><?php echo e($summary['overload_alerts'] ?? 0); ?></div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card summary-card p-3">
            <div class="text-muted small">Active TEA Loans</div>
            <div class="fw-bold fs-4"><?php echo e($summary['active_tea_loans'] ?? 0); ?></div>
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

        <form method="GET" action="<?php echo e(route('teachers.index')); ?>" class="row g-3 mb-3">
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
                        value="<?php echo e($search ?? ''); ?>"
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
                    <?php $__empty_1 = true; $__currentLoopData = $teacherDirectory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $directoryItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $teacher = $directoryItem['teacher'];
                            $currentSchedule = $directoryItem['current_schedule'];
                        ?>
                        <tr>
                            <td><?php echo e($teacher->employee_number); ?></td>
                            <td>
                                <div class="fw-semibold"><?php echo e($teacher->full_name); ?></div>
                                <div class="text-muted small"><?php echo e($teacher->email ?: 'No email provided'); ?></div>
                            </td>
                            <td><?php echo e($teacher->contact_number ?: '—'); ?></td>
                            <td><?php echo e(number_format((float) $teacher->maximum_weekly_load, 2)); ?> hrs</td>
                            <td>
                                <?php if($currentSchedule): ?>
                                    <div class="fw-semibold"><?php echo e($directoryItem['current_room_name']); ?></div>
                                    <div class="text-muted small"><?php echo e($directoryItem['current_room_meta']); ?></div>
                                <?php else: ?>
                                    <div class="fw-semibold text-muted">No active class</div>
                                    <div class="text-muted small"><?php echo e($directoryItem['current_room_meta']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?php echo e($teacher->is_active ? 'text-bg-success' : 'text-bg-secondary'); ?>">
                                    <?php echo e($teacher->is_active ? 'Active' : 'Inactive'); ?>

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
                                                data-bs-target="#editTeacherModal<?php echo e($teacher->id); ?>">
                                                Edit Record
                                            </button>
                                        </li>
                                        <li>
                                            <button
                                                type="button"
                                                class="dropdown-item"
                                                data-bs-toggle="modal"
                                                data-bs-target="#assignSubjectModal<?php echo e($teacher->id); ?>">
                                                Assign Subject
                                            </button>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="<?php echo e(route('teachers.records.destroy', $teacher)); ?>"
                                                onsubmit="return confirm('Delete this teacher record?')">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button type="submit" class="dropdown-item text-danger">Delete</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No teacher records found.</td>
                        </tr>
                    <?php endif; ?>
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
            <?php $__empty_1 = true; $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="col-12 col-lg-6">
                    <div class="border rounded-4 p-3 h-100">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                            <div>
                                <div class="fw-semibold"><?php echo e($teacher->full_name); ?></div>
                                <div class="text-muted small"><?php echo e($teacher->employee_number); ?></div>
                            </div>

                            <button
                                type="button"
                                class="btn btn-outline-primary btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#assignSubjectModal<?php echo e($teacher->id); ?>">
                                Add Assignment
                            </button>
                        </div>

                        <?php if($teacher->teacherSubjects->isNotEmpty()): ?>
                            <div class="d-flex flex-wrap gap-2">
                                <?php $__currentLoopData = $teacher->teacherSubjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacherSubject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="border rounded-pill px-3 py-2 d-flex align-items-center gap-2 bg-light">
                                        <span class="small"><?php echo e($teacherSubject->subject?->name); ?></span>
                                        <form method="POST" action="<?php echo e(route('teachers.subject-assignments.destroy', $teacherSubject)); ?>"
                                            onsubmit="return confirm('Remove this teaching assignment?')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-sm p-0 border-0 bg-transparent text-danger">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <div class="text-muted small">No subject assignments yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-12">
                    <div class="border rounded-4 p-4 text-center text-muted">
                        No teachers available for subject assignment.
                    </div>
                </div>
            <?php endif; ?>
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
                <?php echo e($activeTerm?->name ?? 'No Active Term'); ?>

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
                    <?php $__empty_1 = true; $__currentLoopData = $loadMonitoring; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loadItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($loadItem['teacher']->full_name); ?></td>
                            <td><?php echo e(number_format((float) $loadItem['teacher']->maximum_weekly_load, 2)); ?> hrs</td>
                            <td><?php echo e(number_format((float) $loadItem['assigned_hours'], 2)); ?> hrs</td>
                            <td><?php echo e(number_format((float) $loadItem['remaining_hours'], 2)); ?> hrs</td>
                            <td>
                                <?php if($loadItem['status'] === 'overload'): ?>
                                    <span class="badge text-bg-danger">Overload</span>
                                <?php elseif($loadItem['status'] === 'underload'): ?>
                                    <span class="badge text-bg-warning">Underload</span>
                                <?php elseif($loadItem['status'] === 'unassigned'): ?>
                                    <span class="badge text-bg-secondary">Unassigned</span>
                                <?php else: ?>
                                    <span class="badge text-bg-success">Normal</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No load monitoring data available.</td>
                        </tr>
                    <?php endif; ?>
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
                    <div class="fw-bold fs-4"><?php echo e($summary['active_tea_loans'] ?? 0); ?></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="border rounded-4 p-3 bg-light h-100">
                    <div class="text-muted small">Paid Loans</div>
                    <div class="fw-bold fs-4"><?php echo e($summary['paid_loans'] ?? 0); ?></div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="border rounded-4 p-3 bg-light h-100">
                    <div class="text-muted small">Teachers with Balance</div>
                    <div class="fw-bold fs-4"><?php echo e($summary['teachers_with_balance'] ?? 0); ?></div>
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
                                <?php $__empty_1 = true; $__currentLoopData = $teacherLoans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($loan->teacher?->full_name); ?></td>
                                        <td><?php echo e($loan->loan_type); ?></td>
                                        <td>₱<?php echo e(number_format((float) $loan->principal_amount, 2)); ?></td>
                                        <td>₱<?php echo e(number_format((float) $loan->outstanding_balance, 2)); ?></td>
                                        <td>
                                            <?php if($loan->status === 'active'): ?>
                                                <span class="badge text-bg-primary">Active</span>
                                            <?php elseif($loan->status === 'paid'): ?>
                                                <span class="badge text-bg-success">Paid</span>
                                            <?php else: ?>
                                                <span class="badge text-bg-secondary">Cancelled</span>
                                            <?php endif; ?>
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
                                                            data-bs-target="#editLoanModal<?php echo e($loan->id); ?>">
                                                            Edit Loan
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <button
                                                            type="button"
                                                            class="dropdown-item"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#loanDetailsModal<?php echo e($loan->id); ?>">
                                                            View Details
                                                        </button>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form method="POST" action="<?php echo e(route('teachers.loans.destroy', $loan)); ?>"
                                                            onsubmit="return confirm('Delete this loan record?')">
                                                            <?php echo csrf_field(); ?>
                                                            <?php echo method_field('DELETE'); ?>
                                                            <button type="submit" class="dropdown-item text-danger">Delete</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No loan records found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="border rounded-4 p-3 h-100">
                    <h6 class="fw-bold mb-3">Payment History Preview</h6>

                    <?php if($paymentHistory->isNotEmpty()): ?>
                        <ul class="list-group">
                            <?php $__currentLoopData = $paymentHistory->take(6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between gap-2">
                                        <div>
                                            <div class="fw-semibold">
                                                <?php echo e($payment->teacherLoan?->teacher?->full_name ?? 'Unknown Teacher'); ?>

                                            </div>
                                            <div class="text-muted small">
                                                <?php echo e($payment->teacherLoan?->loan_type ?? 'Loan Record'); ?>

                                            </div>
                                            <div class="text-muted small">
                                                <?php echo e(optional($payment->payment_date)->format('F d, Y')); ?>

                                            </div>
                                        </div>
                                        <div class="fw-semibold">
                                            ₱<?php echo e(number_format((float) $payment->amount_paid, 2)); ?>

                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-muted small">No payment history available yet.</div>
                    <?php endif; ?>

                    <div class="mt-3 text-muted small">
                        This section remains prototype-level and does not yet include payroll deduction integration.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="addTeacherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="<?php echo e(route('teachers.records.store')); ?>">
                <?php echo csrf_field(); ?>
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


<?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="modal fade" id="editTeacherModal<?php echo e($teacher->id); ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <form method="POST" action="<?php echo e(route('teachers.records.update', $teacher)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Teacher</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Employee Number</label>
                                <input type="text" name="employee_number" class="form-control" value="<?php echo e($teacher->employee_number); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">First Name</label>
                                <input type="text" name="first_name" class="form-control" value="<?php echo e($teacher->first_name); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Middle Name</label>
                                <input type="text" name="middle_name" class="form-control" value="<?php echo e($teacher->middle_name); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="<?php echo e($teacher->last_name); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Sex</label>
                                <select name="sex" class="form-select">
                                    <option value="">Select sex</option>
                                    <option value="male" <?php echo e($teacher->sex === 'male' ? 'selected' : ''); ?>>Male</option>
                                    <option value="female" <?php echo e($teacher->sex === 'female' ? 'selected' : ''); ?>>Female</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Maximum Weekly Load</label>
                                <input type="number" step="0.01" min="0" name="maximum_weekly_load" class="form-control" value="<?php echo e($teacher->maximum_weekly_load); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contact Number</label>
                                <input type="text" name="contact_number" class="form-control" value="<?php echo e($teacher->contact_number); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo e($teacher->email); ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="is_active" class="form-select" required>
                                    <option value="1" <?php echo e($teacher->is_active ? 'selected' : ''); ?>>Active</option>
                                    <option value="0" <?php echo e(!$teacher->is_active ? 'selected' : ''); ?>>Inactive</option>
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
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>


<?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="modal fade" id="assignSubjectModal<?php echo e($teacher->id); ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="<?php echo e(route('teachers.subject-assignments.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="teacher_id" value="<?php echo e($teacher->id); ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">Assign Subject to <?php echo e($teacher->full_name); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Subject</label>
                        <select name="subject_id" class="form-select" required>
                            <option value="">Select subject</option>
                            <?php $__currentLoopData = $subjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subject): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($subject->id); ?>"><?php echo e($subject->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" type="submit">Save Assignment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>


<div class="modal fade" id="addLoanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="<?php echo e(route('teachers.loans.store')); ?>">
                <?php echo csrf_field(); ?>
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
                                <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($teacher->id); ?>"><?php echo e($teacher->full_name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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


<?php $__currentLoopData = $teacherLoans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="modal fade" id="editLoanModal<?php echo e($loan->id); ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="<?php echo e(route('teachers.loans.update', $loan)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="modal-header">
                        <h5 class="modal-title">Edit TEA Loan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Teacher</label>
                                <select name="teacher_id" class="form-select" required>
                                    <?php $__currentLoopData = $teachers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $teacher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($teacher->id); ?>" <?php echo e($loan->teacher_id == $teacher->id ? 'selected' : ''); ?>>
                                            <?php echo e($teacher->full_name); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Loan Type</label>
                                <input type="text" name="loan_type" class="form-control" value="<?php echo e($loan->loan_type); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Principal Amount</label>
                                <input type="number" step="0.01" min="0.01" name="principal_amount" class="form-control" value="<?php echo e($loan->principal_amount); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Loan Date</label>
                                <input type="date" name="loan_date" class="form-control" value="<?php echo e(optional($loan->loan_date)->format('Y-m-d')); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="active" <?php echo e($loan->status === 'active' ? 'selected' : ''); ?>>Active</option>
                                    <option value="paid" <?php echo e($loan->status === 'paid' ? 'selected' : ''); ?>>Paid</option>
                                    <option value="cancelled" <?php echo e($loan->status === 'cancelled' ? 'selected' : ''); ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" class="form-control" rows="3"><?php echo e($loan->remarks); ?></textarea>
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

    <div class="modal fade" id="loanDetailsModal<?php echo e($loan->id); ?>" tabindex="-1" aria-hidden="true">
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
                                <div class="fw-semibold"><?php echo e($loan->teacher?->full_name); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-4 p-3 bg-light">
                                <div class="text-muted small">Loan Type</div>
                                <div class="fw-semibold"><?php echo e($loan->loan_type); ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 bg-light">
                                <div class="text-muted small">Principal</div>
                                <div class="fw-semibold">₱<?php echo e(number_format((float) $loan->principal_amount, 2)); ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 bg-light">
                                <div class="text-muted small">Total Paid</div>
                                <div class="fw-semibold">₱<?php echo e(number_format((float) $loan->total_paid, 2)); ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded-4 p-3 bg-light">
                                <div class="text-muted small">Balance</div>
                                <div class="fw-semibold">₱<?php echo e(number_format((float) $loan->outstanding_balance, 2)); ?></div>
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
                                <?php $__empty_1 = true; $__currentLoopData = $loan->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e(optional($payment->payment_date)->format('F d, Y')); ?></td>
                                        <td>₱<?php echo e(number_format((float) $payment->amount_paid, 2)); ?></td>
                                        <td><?php echo e($payment->remarks ?: '—'); ?></td>
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
                                                            data-bs-target="#editPaymentModal<?php echo e($payment->id); ?>">
                                                            Edit Payment
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <form method="POST" action="<?php echo e(route('teachers.loan-payments.destroy', $payment)); ?>"
                                                            onsubmit="return confirm('Delete this payment record?')">
                                                            <?php echo csrf_field(); ?>
                                                            <?php echo method_field('DELETE'); ?>
                                                            <button type="submit" class="dropdown-item text-danger">Delete</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">No payment records yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if($loan->remarks): ?>
                        <div class="mt-3 text-muted small">
                            <span class="fw-semibold">Remarks:</span> <?php echo e($loan->remarks); ?>

                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>


<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?php echo e(route('teachers.loan-payments.store')); ?>">
                <?php echo csrf_field(); ?>
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
                                <?php $__currentLoopData = $teacherLoans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($loan->id); ?>">
                                        <?php echo e($loan->teacher?->full_name); ?> — <?php echo e($loan->loan_type); ?> — ₱<?php echo e(number_format((float) $loan->outstanding_balance, 2)); ?> balance
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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


<?php $__currentLoopData = $paymentHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="modal fade" id="editPaymentModal<?php echo e($payment->id); ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="<?php echo e(route('teachers.loan-payments.update', $payment)); ?>">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Loan Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Loan Record</label>
                                <select name="teacher_loan_id" class="form-select" required>
                                    <?php $__currentLoopData = $teacherLoans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($loan->id); ?>" <?php echo e($payment->teacher_loan_id == $loan->id ? 'selected' : ''); ?>>
                                            <?php echo e($loan->teacher?->full_name); ?> — <?php echo e($loan->loan_type); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Date</label>
                                <input type="date" name="payment_date" class="form-control" value="<?php echo e(optional($payment->payment_date)->format('Y-m-d')); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Amount Paid</label>
                                <input type="number" step="0.01" min="0.01" name="amount_paid" class="form-control" value="<?php echo e($payment->amount_paid); ?>" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" class="form-control" rows="3"><?php echo e($payment->remarks); ?></textarea>
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
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

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

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.toggle-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.closest('.card').classList.toggle('active');
        });
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\class-scheduling-system\resources\views/teachers/index.blade.php ENDPATH**/ ?>
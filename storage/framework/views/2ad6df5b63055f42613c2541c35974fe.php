

<?php
    $pageTitle = 'Dashboard';
    $pageSubtitle = 'Overview of Grade 11 and Grade 12 scheduling status';
?>

<?php $__env->startSection('content'); ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h5 class="fw-bold mb-1">Scheduling Overview</h5>
            <p class="text-muted mb-0">
                Monitor summary counts, academic status, and quick access to prototype modules.
            </p>
        </div>

        <div class="btn-group" role="group" aria-label="Grade Level Switch">
            <a
                href="<?php echo e(route('dashboard.index', ['grade_level' => '11'])); ?>"
                class="btn <?php echo e(($selectedGradeParam ?? '11') === '11' ? 'btn-primary active' : 'btn-outline-primary'); ?>"
            >
                Grade 11
            </a>
            <a
                href="<?php echo e(route('dashboard.index', ['grade_level' => '12'])); ?>"
                class="btn <?php echo e(($selectedGradeParam ?? '11') === '12' ? 'btn-primary active' : 'btn-outline-primary'); ?>"
            >
                Grade 12
            </a>
        </div>
    </div>

    <div class="row g-4 mb-4" id="dashboardSummaryCards">
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card summary-card h-100 border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">Total Sections</div>
                    <h2 class="fw-bold mb-1"><?php echo e($totalSections ?? 0); ?></h2>
                    <div class="text-muted small">Active sections under selected grade level</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card summary-card h-100 border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">Total Teachers</div>
                    <h2 class="fw-bold mb-1"><?php echo e($totalTeachers ?? 0); ?></h2>
                    <div class="text-muted small">Teachers currently assigned in this level</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card summary-card h-100 border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">Total Subjects</div>
                    <h2 class="fw-bold mb-1"><?php echo e($totalSubjects ?? 0); ?></h2>
                    <div class="text-muted small">Core and elective subjects in use</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card summary-card h-100 border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">Rooms Used</div>
                    <h2 class="fw-bold mb-1"><?php echo e($totalRooms ?? 0); ?></h2>
                    <div class="text-muted small">Rooms currently involved in class scheduling</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card summary-card h-100 border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">Scheduled Classes</div>
                    <h2 class="fw-bold mb-1"><?php echo e($totalSchedules ?? 0); ?></h2>
                    <div class="text-muted small">Encoded class schedule entries</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card summary-card h-100 border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="text-muted small mb-2">Incomplete Schedules</div>
                    <h2 class="fw-bold mb-1 text-danger"><?php echo e($incompleteSchedules ?? 0); ?></h2>
                    <div class="text-muted small">Sections still requiring schedule completion</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-5">
            <div class="card content-card h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3">Active Academic Period</h5>

                    <div class="border rounded-4 p-3 mb-3 bg-light">
                        <div class="text-muted small">School Year</div>
                        <div class="fw-semibold fs-5"><?php echo e($activeSchoolYear?->name ?? 'Not Set'); ?></div>
                    </div>

                    <div class="border rounded-4 p-3 mb-3 bg-light">
                        <div class="text-muted small">Term</div>
                        <div class="fw-semibold fs-5"><?php echo e($activeSchoolTerm?->name ?? 'Not Set'); ?></div>
                    </div>

                    <div class="border rounded-4 p-3 bg-light">
                        <div class="text-muted small">Selected Grade Level</div>
                        <div class="fw-semibold fs-5"><?php echo e($selectedGradeLabel ?? 'Grade 11'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="card content-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <h5 class="fw-bold mb-0">Quick Actions</h5>
                        <span class="badge text-bg-light border">Prototype Navigation</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <a href="<?php echo e(route('academic-setup.index')); ?>" class="text-decoration-none">
                                <div class="border rounded-4 p-3 h-100 bg-white hover-shadow">
                                    <div class="fw-semibold mb-1 text-dark">Open Academic Setup</div>
                                    <div class="text-muted small">
                                        Manage school year, terms, pathways, subjects, sections, rooms, and shift setup.
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-12 col-md-6">
                            <a href="<?php echo e(route('teachers.index')); ?>" class="text-decoration-none">
                                <div class="border rounded-4 p-3 h-100 bg-white">
                                    <div class="fw-semibold mb-1 text-dark">Open Teachers</div>
                                    <div class="text-muted small">
                                        Manage teacher profiles, subject assignments, load monitoring, and TEA records.
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-12 col-md-6">
                            <a href="<?php echo e(route('scheduling.index')); ?>" class="text-decoration-none">
                                <div class="border rounded-4 p-3 h-100 bg-white">
                                    <div class="fw-semibold mb-1 text-dark">Open Scheduling</div>
                                    <div class="text-muted small">
                                        Create and monitor section-based class schedules using the timetable grid.
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="col-12 col-md-6">
                            <a href="<?php echo e(route('reports.index')); ?>" class="text-decoration-none">
                                <div class="border rounded-4 p-3 h-100 bg-white">
                                    <div class="fw-semibold mb-1 text-dark">Open Reports</div>
                                    <div class="text-muted small">
                                        Preview and print section, teacher, and room schedule reports.
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card content-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <h5 class="fw-bold mb-0">Scheduling Status</h5>
                        <span class="badge text-bg-warning">Prototype Preview</span>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <div class="border rounded-4 p-3 bg-light h-100">
                                <div class="text-muted small mb-1">Pathway Availability</div>
                                <div class="fw-semibold">
                                    <?php echo e(($configuredPathwayCount ?? 0) > 0 ? ($configuredPathwayCount . ' configured pathway/s in selected grade') : 'No pathways yet for selected grade'); ?>

                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="border rounded-4 p-3 bg-light h-100">
                                <div class="text-muted small mb-1">Shift Setup Status</div>
                                <div class="fw-semibold">
                                    <?php echo e(($configuredShiftCount ?? 0) > 0 ? ($configuredShiftCount . ' shift type/s used by sections') : 'No shift setup yet for selected grade'); ?>

                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="border rounded-4 p-3 bg-light h-100">
                                <div class="text-muted small mb-1">Current Focus</div>
                                <div class="fw-semibold">
                                    Dynamic dashboard view for <?php echo e($selectedGradeLabel ?? 'Grade 11'); ?>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .summary-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .summary-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08) !important;
        }
    </style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\class-scheduling-system\resources\views/dashboard/index.blade.php ENDPATH**/ ?>
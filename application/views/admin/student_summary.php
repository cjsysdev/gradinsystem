<?php $this->load->view('header'); ?>

<div class="container">
    <?php $this->load->view('profile_only'); ?>
    <?php $this->load->view('admin/nav_bar'); ?>

    <!-- Flash messages -->
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger mt-2"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>

    <!-- Back link -->
    <div class="mt-3">
        <a href="<?= base_url('admin/students_by_section') ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Back to Section Roster
        </a>
    </div>

    <!-- Student header -->
    <div class="card mt-3 shadow-sm">
        <div class="card-body d-flex align-items-center">
            <?php if (!empty($profile_pic)): ?>
                <img src="<?= base_url('uploads/profile_pics/' . htmlspecialchars($profile_pic)) ?>"
                     alt="Profile"
                     class="rounded-circle mr-3"
                     style="width:90px;height:90px;object-fit:cover;">
            <?php else: ?>
                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mr-3"
                     style="width:90px;height:90px;flex-shrink:0;">
                    <i class="fa fa-user fa-2x text-white"></i>
                </div>
            <?php endif; ?>
            <div>
                <h4 class="mb-0"><?= htmlspecialchars($student['lastname'] . ', ' . $student['firstname']) ?></h4>
                <span class="text-muted">ID: <?= htmlspecialchars($student['trans_no']) ?></span>
            </div>
        </div>
    </div>

    <!-- Grades -->
    <?php $grades = $grades ?? []; ?>
    <h5 class="mt-4">Grades</h5>
    <?php if (empty($grades)): ?>
        <div class="alert alert-info py-2">
            No enrollment in the active semester, so there is nothing to grade yet.
        </div>
    <?php else: ?>
        <?php
        $term_labels  = ['midterm' => 'Midterm', 'final' => 'Final', 'overall' => 'Overall'];
        $remark_class = ['passed' => 'success', 'failed' => 'danger', 'inc' => 'secondary'];
        ?>
        <?php foreach ($grades as $i => $g): ?>
            <div class="card mb-3 shadow-sm">
                <div class="card-header py-2 d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <strong><?= htmlspecialchars($g['class_code']) ?></strong>
                        <span class="text-muted"><?= htmlspecialchars($g['class_name']) ?></span>
                    </div>
                    <small class="text-muted">
                        <?= htmlspecialchars($g['section']) ?>
                        <?= $g['type'] ? '(' . htmlspecialchars($g['type']) . ')' : '' ?>
                        <?= $g['schedule'] ? '&middot; ' . htmlspecialchars($g['schedule']) : '' ?>
                    </small>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Term</th>
                                <th>Grade</th>
                                <th>Percentage</th>
                                <th>Remark</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($term_labels as $key => $label): ?>
                                <?php $t = $g['terms'][$key]; ?>
                                <tr<?= $key === 'overall' ? ' class="font-weight-bold"' : '' ?>>
                                    <td><?= $label ?></td>
                                    <td class="<?= $t['grade_point'] === 'INC' ? 'text-danger' : '' ?>">
                                        <?= htmlspecialchars($t['grade_point']) ?>
                                    </td>
                                    <td><?= htmlspecialchars($t['percentage']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $remark_class[$t['remark']['key']] ?? 'secondary' ?>">
                                            <?= htmlspecialchars($t['remark']['label']) ?>
                                        </span>
                                    </td>
                                    <td class="small font-weight-normal">
                                        <?php if ($t['inc_reason']): ?>
                                            <span class="text-muted"><?= htmlspecialchars($t['inc_reason']) ?></span>
                                        <?php endif; ?>
                                        <?php if ($t['provisional'] !== null): ?>
                                            <span class="font-italic text-info ml-1"
                                                  title="Computed from the components recorded so far, rescaled to their weight. Not the official grade.">
                                                &mdash; standing so far: <?= htmlspecialchars($t['provisional']) ?>*
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($t['pending_count'] > 0): ?>
                                            <span class="badge badge-warning ml-1"
                                                  title="Submitted but not yet scored; each counts as 0 until it is graded."><?= $t['pending_count'] ?> ungraded</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-body py-2">
                    <a class="small" data-bs-toggle="collapse" data-bs-target="#gradeBreakdown<?= $i ?>" href="#gradeBreakdown<?= $i ?>" role="button"
                       aria-expanded="false" aria-controls="gradeBreakdown<?= $i ?>">
                        Show component breakdown
                    </a>
                    <div class="collapse mt-2" id="gradeBreakdown<?= $i ?>">
                        <div class="row">
                            <?php foreach (['midterm' => 'Midterm', 'final' => 'Final'] as $tkey => $tlabel): ?>
                                <div class="col-md-6">
                                    <div class="text-muted small mb-1"><?= $tlabel ?></div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Component</th>
                                                    <th>Weight</th>
                                                    <th>Score</th>
                                                    <th>%</th>
                                                    <th>Items</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($g['components'][$tkey] as $c): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($c['iotype_name']) ?></td>
                                                        <td><?= htmlspecialchars((string) $c['iotype_percentage']) ?>%</td>
                                                        <td><?= $c['total_score'] ?> / <?= $c['total_max_score'] ?></td>
                                                        <td><?= $c['percentage'] === null ? '<span class="text-muted">&mdash;</span>' : $c['percentage'] ?></td>
                                                        <td>
                                                            <?= (int) $c['n_assessments'] ?>
                                                            <?php if ((int) $c['n_ungraded'] > 0): ?>
                                                                <span class="text-warning">(<?= (int) $c['n_ungraded'] ?> ungraded)</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                                <?php if (empty($g['components'][$tkey])): ?>
                                                    <tr><td colspan="5" class="text-muted">Nothing recorded.</td></tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <small class="text-muted d-block mb-2">
            A term reads <strong>INC</strong> until every component has at least one assessment,
            and a submitted-but-unscored item counts as 0.
            <span class="font-italic text-info">Italic *</span> values are <strong>provisional</strong> &mdash;
            the standing so far, rescaled over the components recorded to date. They are not
            official grades and will change as the remaining components are added.
        </small>
    <?php endif; ?>

    <!-- Attendance summary -->
    <h5 class="mt-4">Attendance</h5>
    <div class="row">
        <div class="col-6 col-md-3 mb-3">
            <div class="card text-center shadow-sm">
                <div class="card-body py-3">
                    <h3 class="mb-0 text-success"><?= (int)$attendance['present_count'] ?></h3>
                    <small class="text-muted">Present</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card text-center shadow-sm">
                <div class="card-body py-3">
                    <h3 class="mb-0 text-danger"><?= (int)$attendance['absent_count'] ?></h3>
                    <small class="text-muted">Absent</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card text-center shadow-sm">
                <div class="card-body py-3">
                    <h3 class="mb-0 text-warning"><?= (int)$attendance['late_count'] ?></h3>
                    <small class="text-muted">Late</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card text-center shadow-sm">
                <div class="card-body py-3">
                    <h3 class="mb-0 text-info"><?= (int)$attendance['excuse_count'] ?></h3>
                    <small class="text-muted">Excused</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Violations summary -->
    <?php
    $minor    = (int)($vio_summary['minor']    ?? 0);
    $moderate = (int)($vio_summary['moderate'] ?? 0);
    $major    = (int)($vio_summary['major']    ?? 0);
    $total_vio = $minor + $moderate + $major;
    ?>
    <h5 class="mt-2">Violations
        <?php if ($total_vio > 0): ?>
            <span class="badge badge-danger"><?= $total_vio ?></span>
        <?php endif; ?>
    </h5>
    <?php if ($total_vio === 0): ?>
        <div class="alert alert-success py-2">No violations on record.</div>
    <?php else: ?>
        <div class="d-flex flex-wrap mb-3" style="gap:.5rem;">
            <?php if ($minor > 0): ?>
                <span class="badge badge-warning p-2" style="font-size:.9rem;">Minor: <?= $minor ?></span>
            <?php endif; ?>
            <?php if ($moderate > 0): ?>
                <span class="badge badge-orange p-2" style="font-size:.9rem;background:#fd7e14;color:#fff;">Moderate: <?= $moderate ?></span>
            <?php endif; ?>
            <?php if ($major > 0): ?>
                <span class="badge badge-danger p-2" style="font-size:.9rem;">Major: <?= $major ?></span>
            <?php endif; ?>
        </div>
        <table class="table table-sm table-bordered table-hover">
            <thead class="thead-light">
                <tr>
                    <th>Type</th>
                    <th>Severity</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($violations as $v): ?>
                    <tr>
                        <td><?= htmlspecialchars($v['violation_type']) ?></td>
                        <td>
                            <?php
                            $badge = ['minor' => 'warning', 'moderate' => 'warning', 'major' => 'danger'];
                            $bc = $badge[$v['severity']] ?? 'secondary';
                            ?>
                            <span class="badge badge-<?= $bc ?>"><?= ucfirst($v['severity']) ?></span>
                        </td>
                        <td><?= htmlspecialchars($v['date_of_violation']) ?></td>
                        <td>
                            <?php
                            $sc = ['pending' => 'warning', 'resolved' => 'success', 'dismissed' => 'secondary'];
                            $s = $v['status'] ?? 'pending';
                            ?>
                            <span class="badge badge-<?= $sc[$s] ?? 'secondary' ?>"><?= ucfirst($s) ?></span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Classwork submissions -->
    <?php
    $total_score = array_sum(array_column(array_filter($classworks, function($c) { return $c['score'] !== null; }), 'score'));
    $total_max   = array_sum(array_column(array_filter($classworks, function($c) { return $c['max_score'] !== null; }), 'max_score'));
    $pct = $total_max > 0 ? round(($total_score / $total_max) * 100, 1) : null;

    $unsubmitted = $unsubmitted ?? [];
    // Past due vs. still open: a missing assessment that isn't due yet is not
    // the teacher's problem yet, so the two are counted and coloured apart.
    $today   = date('Y-m-d');
    $overdue = array_filter($unsubmitted, function($a) use ($today) {
        return !empty($a['due']) && substr($a['due'], 0, 10) > '0000-00-00' && substr($a['due'], 0, 10) < $today;
    });
    ?>
    <h5 class="mt-4">Classwork
        <small class="text-muted">(<?= count($classworks) ?> submitted<?= count($unsubmitted) ? ', ' . count($unsubmitted) . ' unsubmitted' : '' ?>)</small>
        <?php if ($pct !== null): ?>
            <span class="badge badge-info" title="Score over max, submitted work only"><?= $pct ?>%</span>
        <?php endif; ?>
        <?php if (count($overdue) > 0): ?>
            <span class="badge badge-danger"><?= count($overdue) ?> missing</span>
        <?php endif; ?>
    </h5>

    <?php if (empty($classworks) && empty($unsubmitted)): ?>
        <div class="alert alert-info py-2">No classwork assigned to this student's sections this semester.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>Title</th>
                        <th>Score</th>
                        <th>Max</th>
                        <th>Due</th>
                        <th>Submitted</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($classworks as $cw): ?>
                        <tr>
                            <td><?= htmlspecialchars($cw['title']) ?></td>
                            <td><?= $cw['score'] !== null ? $cw['score'] : '<span class="text-muted">—</span>' ?></td>
                            <td><?= $cw['max_score'] ?? '—' ?></td>
                            <td class="text-muted">—</td>
                            <td><?= $cw['created_at'] ? date('M j, Y', strtotime($cw['created_at'])) : '—' ?></td>
                            <td>
                                <?php if ($cw['score'] !== null): ?>
                                    <span class="badge badge-success">Graded</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Ungraded</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php foreach ($unsubmitted as $a): ?>
                        <?php
                        $due_date = (!empty($a['due']) && substr($a['due'], 0, 10) > '0000-00-00')
                            ? substr($a['due'], 0, 10) : null;
                        $is_overdue = $due_date !== null && $due_date < $today;
                        ?>
                        <tr class="<?= $is_overdue ? 'table-danger' : 'table-warning' ?>">
                            <td><?= htmlspecialchars($a['title']) ?></td>
                            <td><span class="text-muted">—</span></td>
                            <td><?= $a['max_score'] ?? '—' ?></td>
                            <td><?= $due_date ? date('M j, Y', strtotime($due_date)) : '<span class="text-muted">—</span>' ?></td>
                            <td><span class="text-muted">—</span></td>
                            <td>
                                <?php if ($is_overdue): ?>
                                    <span class="badge badge-danger">Missing</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Not submitted</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <small class="text-muted d-block mb-2">
            The percentage above covers submitted work only and is not a grade —
            see the Grades section above for the official standing.
        </small>
    <?php endif; ?>

    <!-- Emergency contacts -->
    <h5 class="mt-4">Emergency Contacts</h5>
    <?php if (empty($contacts)): ?>
        <div class="alert alert-warning py-2">No emergency contacts on file.</div>
    <?php else: ?>
        <ul class="list-group mb-4">
            <?php foreach ($contacts as $c): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong><?= htmlspecialchars($c['full_name']) ?></strong>
                        <span class="text-muted ml-2"><?= htmlspecialchars($c['relationship']) ?></span>
                        <br>
                        <small><i class="fa fa-phone"></i> <?= htmlspecialchars($c['contact_no']) ?></small>
                    </div>
                    <?php if ($c['is_primary']): ?>
                        <span class="badge badge-primary">Primary</span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <!-- Quick links -->
    <div class="d-flex flex-wrap mb-5" style="gap:.5rem;">
        <a href="<?= base_url('student_submissions/' . $student['trans_no']) ?>" class="btn btn-outline-primary btn-sm">
            <i class="fa fa-folder-open"></i> View Submissions
        </a>
        <a href="<?= base_url('admin/student_violations?student_id=' . $student['trans_no']) ?>" class="btn btn-outline-warning btn-sm">
            <i class="fa fa-exclamation-triangle"></i> Violations
        </a>
        <a href="<?= base_url('admin/emergency_contacts?student_id=' . $student['trans_no']) ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-phone"></i> Contacts
        </a>
        <?php if ($has_account): ?>
            <a href="<?= base_url('AdminController/login_as_student/' . $student['trans_no']) ?>" class="btn btn-outline-danger btn-sm"
               onclick="return confirm('Log in as <?= htmlspecialchars(addslashes($student['firstname'] . ' ' . $student['lastname'])) ?>? You\'ll leave the admin panel until you click \'Return to Admin\'.');">
                <i class="fa fa-user-secret"></i> Login as Student
            </a>
        <?php endif; ?>
    </div>
</div>

<?php $this->load->view('footer'); ?>

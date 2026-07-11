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
    ?>
    <h5 class="mt-4">Classwork
        <small class="text-muted">(<?= count($classworks) ?> submitted)</small>
        <?php if ($pct !== null): ?>
            <span class="badge badge-info"><?= $pct ?>%</span>
        <?php endif; ?>
    </h5>

    <?php if (empty($classworks)): ?>
        <div class="alert alert-info py-2">No submitted classwork found for this student.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>Title</th>
                        <th>Score</th>
                        <th>Max</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($classworks as $cw): ?>
                        <tr>
                            <td><?= htmlspecialchars($cw['title']) ?></td>
                            <td><?= $cw['score'] !== null ? $cw['score'] : '<span class="text-muted">—</span>' ?></td>
                            <td><?= $cw['max_score'] ?? '—' ?></td>
                            <td><?= $cw['created_at'] ? date('M j, Y', strtotime($cw['created_at'])) : '—' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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

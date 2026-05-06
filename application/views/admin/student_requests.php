<?php $this->load->view('header'); ?>

<div class="container">
    <?php $this->load->view('profile_only'); ?>
    <?php $this->load->view('admin/nav_bar'); ?>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa fa-exclamation-circle"></i> <?= $this->session->flashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php
    $base = base_url('admin/student_requests');
    $mk   = function($type, $status) use ($base) {
        $p = array_filter(['type' => $type, 'status' => $status]);
        return $base . ($p ? '?' . http_build_query($p) : '');
    };
    ?>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h4><i class="fa fa-sign-out-alt"></i> Student Requests</h4>
        <div>
            <!-- Type filter -->
            <a href="<?= $mk(null, $selected_status) ?>"
               class="btn btn-sm <?= !$selected_type ? 'btn-secondary' : 'btn-outline-secondary' ?>">All Types</a>
            <a href="<?= $mk('absence', $selected_status) ?>"
               class="btn btn-sm <?= $selected_type === 'absence' ? 'btn-info' : 'btn-outline-info' ?>">Absences</a>
            <a href="<?= $mk('pass', $selected_status) ?>"
               class="btn btn-sm <?= $selected_type === 'pass' ? 'btn-warning' : 'btn-outline-warning' ?>">Passes</a>
            &nbsp;
            <!-- Status filter -->
            <a href="<?= $mk($selected_type, null) ?>"
               class="btn btn-sm <?= !$selected_status ? 'btn-secondary' : 'btn-outline-secondary' ?>">All</a>
            <a href="<?= $mk($selected_type, 'pending') ?>"
               class="btn btn-sm <?= $selected_status === 'pending' ? 'btn-warning' : 'btn-outline-warning' ?>">Pending</a>
            <a href="<?= $mk($selected_type, 'approved') ?>"
               class="btn btn-sm <?= $selected_status === 'approved' ? 'btn-success' : 'btn-outline-success' ?>">Approved</a>
            <a href="<?= $mk($selected_type, 'rejected') ?>"
               class="btn btn-sm <?= $selected_status === 'rejected' ? 'btn-danger' : 'btn-outline-danger' ?>">Rejected</a>
        </div>
    </div>

    <?php if ($total > 0): ?>
        <p class="text-muted small mb-2">
            Showing <?= $offset + 1 ?>–<?= min($offset + $per_page, $total) ?> of <?= $total ?> request<?= $total != 1 ? 's' : '' ?>
        </p>
    <?php endif; ?>

    <?php if (!empty($requests)): ?>
        <table class="table table-bordered table-sm table-hover">
            <thead class="thead-light">
                <tr>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Student</th>
                    <th>Class</th>
                    <th>Section / Schedule</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $r): ?>
                    <?php
                    $status_class = $r['status'] === 'approved' ? 'success' : ($r['status'] === 'rejected' ? 'danger' : 'warning');
                    $type_class   = $r['type'] === 'absence' ? 'info' : 'warning';
                    $type_label   = $r['type'] === 'absence' ? 'Absence' : 'Pass';
                    ?>
                    <tr>
                        <td><span class="badge badge-<?= $type_class ?>"><?= $type_label ?></span></td>
                        <td><?= date('M d, Y', strtotime($r['request_date'])) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($r['lastname'] . ', ' . $r['firstname']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($r['student_no']) ?></small>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($r['class_code']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($r['class_name']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($r['section']) ?><br><small><?= htmlspecialchars($r['day']) ?> <?= date('h:i A', strtotime($r['time_start'])) ?></small></td>
                        <td><?= htmlspecialchars(substr($r['reason'], 0, 60)) ?><?= strlen($r['reason']) > 60 ? '…' : '' ?></td>
                        <td><span class="badge badge-<?= $status_class ?>"><?= ucfirst($r['status']) ?></span></td>
                        <td><small><?= date('M d, Y', strtotime($r['created_at'])) ?></small></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-info"
                                    data-bs-toggle="modal" data-bs-target="#reviewModal<?= $r['request_id'] ?>">
                                <i class="fa fa-eye"></i> Review
                            </button>
                        </td>
                    </tr>

                    <!-- Review Modal -->
                    <div class="modal fade" id="reviewModal<?= $r['request_id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-light">
                                    <h5 class="modal-title">
                                        <span class="badge badge-<?= $type_class ?>"><?= $type_label ?></span>
                                        Request Details
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <p class="text-muted small mb-1">Student</p>
                                            <p class="font-weight-bold"><?= htmlspecialchars($r['lastname'] . ', ' . $r['firstname']) ?></p>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-muted small mb-1"><?= $type_label ?> Date</p>
                                            <p class="font-weight-bold"><?= date('l, F j, Y', strtotime($r['request_date'])) ?></p>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <p class="text-muted small mb-1">Class</p>
                                            <p><?= htmlspecialchars($r['class_code'] . ' — ' . $r['class_name']) ?></p>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-muted small mb-1">Section / Schedule</p>
                                            <p><?= htmlspecialchars($r['section']) ?> &bull; <?= htmlspecialchars($r['day']) ?> <?= date('h:i A', strtotime($r['time_start'])) ?></p>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <p class="text-muted small mb-1">Reason</p>
                                        <p><?= nl2br(htmlspecialchars($r['reason'])) ?></p>
                                    </div>
                                    <div class="mb-3">
                                        <p class="text-muted small mb-1">Current Status</p>
                                        <span class="badge badge-<?= $status_class ?>"><?= ucfirst($r['status']) ?></span>
                                        <?php if ($r['admin_notes']): ?>
                                            <p class="mt-1 small"><?= htmlspecialchars($r['admin_notes']) ?></p>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($r['status'] === 'pending'): ?>
                                        <hr>
                                        <form method="post" action="<?= base_url('admin/process_student_request') ?>">
                                            <input type="hidden" name="request_id" value="<?= $r['request_id'] ?>">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Admin Notes <small class="text-muted">(optional)</small></label>
                                                <textarea name="admin_notes" class="form-control" rows="2"
                                                          placeholder="Reason for approval or rejection…"></textarea>
                                            </div>
                                            <div class="d-flex justify-content-end">
                                                <button type="submit" name="action" value="rejected" class="btn btn-danger mr-2">
                                                    <i class="fa fa-times"></i> Reject
                                                </button>
                                                <button type="submit" name="action" value="approved" class="btn btn-success">
                                                    <i class="fa fa-check"></i> Approve
                                                </button>
                                            </div>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($pagination): ?>
            <nav aria-label="Page navigation">
                <?= $pagination ?>
            </nav>
        <?php endif; ?>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i> No requests found
            <?php if ($selected_type || $selected_status): ?>
                for
                <?= $selected_type ? '<strong>' . ($selected_type === 'absence' ? 'Absences' : 'Passes') . '</strong>' : '' ?>
                <?= $selected_status ? 'with status <strong>' . htmlspecialchars($selected_status) . '</strong>' : '' ?>
            <?php endif; ?>.
        </div>
    <?php endif; ?>
</div>

<?php $this->load->view('footer'); ?>

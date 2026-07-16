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

    <?php $base = base_url('admin/password_resets'); ?>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h4><i class="fa fa-key"></i> Password Reset Requests</h4>
        <div>
            <a href="<?= $base ?>"
               class="btn btn-sm <?= !$selected_status ? 'btn-secondary' : 'btn-outline-secondary' ?>">All</a>
            <a href="<?= $base ?>?status=pending"
               class="btn btn-sm <?= $selected_status === 'pending' ? 'btn-warning' : 'btn-outline-warning' ?>">Pending</a>
            <a href="<?= $base ?>?status=approved"
               class="btn btn-sm <?= $selected_status === 'approved' ? 'btn-success' : 'btn-outline-success' ?>">Approved</a>
            <a href="<?= $base ?>?status=rejected"
               class="btn btn-sm <?= $selected_status === 'rejected' ? 'btn-danger' : 'btn-outline-danger' ?>">Rejected</a>
        </div>
    </div>

    <p class="text-muted small">
        Approving a request resets the student to a temporary password
        (both username and password become their student number) and forces them
        to set a new password on their next login. Relay the temporary credentials
        shown below to the student.
    </p>

    <?php if (!empty($requests)): ?>
        <table class="table table-bordered table-sm table-hover align-middle">
            <thead class="thead-light">
                <tr>
                    <th>Student</th>
                    <th>Student No.</th>
                    <th>Requested</th>
                    <th>Status</th>
                    <th>Temporary Credentials</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $r): ?>
                    <?php
                    $status_class = $r['status'] === 'approved' ? 'success' : ($r['status'] === 'rejected' ? 'danger' : 'warning');
                    ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($r['lastname'] . ', ' . $r['firstname']) ?></strong></td>
                        <td><?= htmlspecialchars($r['student_no']) ?></td>
                        <td><small><?= date('M d, Y g:i A', strtotime($r['created_at'])) ?></small></td>
                        <td><span class="badge badge-<?= $status_class ?>"><?= ucfirst($r['status']) ?></span></td>
                        <td>
                            <?php if ($r['status'] === 'approved' && $r['default_username']): ?>
                                <div><small class="text-muted">Username:</small> <code><?= htmlspecialchars($r['default_username']) ?></code></div>
                                <div><small class="text-muted">Password:</small> <code><?= htmlspecialchars($r['default_password']) ?></code></div>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                            <?php if (!empty($r['admin_notes'])): ?>
                                <div class="mt-1 small text-muted"><i class="fa fa-sticky-note"></i> <?= htmlspecialchars($r['admin_notes']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($r['status'] === 'pending'): ?>
                                <button type="button" class="btn btn-sm btn-info"
                                        data-bs-toggle="modal" data-bs-target="#resetModal<?= $r['request_id'] ?>">
                                    <i class="fa fa-gavel"></i> Review
                                </button>
                            <?php else: ?>
                                <span class="text-muted small">Processed</span>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <?php if ($r['status'] === 'pending'): ?>
                        <!-- Review Modal -->
                        <div class="modal fade" id="resetModal<?= $r['request_id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-light">
                                        <h5 class="modal-title"><i class="fa fa-key"></i> Password Reset</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p>
                                            <strong><?= htmlspecialchars($r['lastname'] . ', ' . $r['firstname']) ?></strong>
                                            (<?= htmlspecialchars($r['student_no']) ?>) requested a password reset.
                                        </p>
                                        <p class="small text-muted">
                                            Approving will set both their username and password to
                                            <code><?= htmlspecialchars($r['student_no']) ?></code>
                                            and require them to change it after logging in.
                                        </p>
                                        <form method="post" action="<?= base_url('admin/process_password_reset') ?>">
                                            <input type="hidden" name="request_id" value="<?= $r['request_id'] ?>">
                                            <div class="form-group mb-3">
                                                <label class="font-weight-bold">Notes <small class="text-muted">(optional)</small></label>
                                                <textarea name="admin_notes" class="form-control" rows="2"
                                                          placeholder="Reason for approval or rejection…"></textarea>
                                            </div>
                                            <div class="d-flex justify-content-end">
                                                <button type="submit" name="action" value="rejected" class="btn btn-danger mr-2">
                                                    <i class="fa fa-times"></i> Reject
                                                </button>
                                                <button type="submit" name="action" value="approved" class="btn btn-success">
                                                    <i class="fa fa-check"></i> Approve &amp; Reset
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i> No password reset requests found
            <?= $selected_status ? 'with status <strong>' . htmlspecialchars($selected_status) . '</strong>' : '' ?>.
        </div>
    <?php endif; ?>
</div>

<?php $this->load->view('footer'); ?>

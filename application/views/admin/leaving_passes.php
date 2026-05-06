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

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4><i class="fa fa-sign-out-alt"></i> Class Leaving Pass Requests</h4>
        <div>
            <a href="<?= base_url('admin/leaving_passes') ?>"
               class="btn btn-sm <?= !$selected_status ? 'btn-secondary' : 'btn-outline-secondary' ?>">All</a>
            <a href="<?= base_url('admin/leaving_passes?status=pending') ?>"
               class="btn btn-sm <?= $selected_status === 'pending' ? 'btn-warning' : 'btn-outline-warning' ?>">Pending</a>
            <a href="<?= base_url('admin/leaving_passes?status=approved') ?>"
               class="btn btn-sm <?= $selected_status === 'approved' ? 'btn-success' : 'btn-outline-success' ?>">Approved</a>
            <a href="<?= base_url('admin/leaving_passes?status=rejected') ?>"
               class="btn btn-sm <?= $selected_status === 'rejected' ? 'btn-danger' : 'btn-outline-danger' ?>">Rejected</a>
        </div>
    </div>

    <?php if ($total > 0): ?>
        <p class="text-muted small mb-2">
            Showing <?= $offset + 1 ?>–<?= min($offset + $per_page, $total) ?> of <?= $total ?> request<?= $total != 1 ? 's' : '' ?>
        </p>
    <?php endif; ?>

    <?php if (!empty($passes)): ?>
        <table class="table table-bordered table-sm table-hover">
            <thead class="thead-light">
                <tr>
                    <th>Pass Date</th>
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
                <?php foreach ($passes as $p): ?>
                    <?php
                    $status_class = $p['status'] === 'approved' ? 'success' : ($p['status'] === 'rejected' ? 'danger' : 'warning');
                    ?>
                    <tr>
                        <td><?= date('M d, Y', strtotime($p['pass_date'])) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($p['lastname'] . ', ' . $p['firstname']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($p['student_no']) ?></small>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($p['class_code']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($p['class_name']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($p['section']) ?><br><small><?= htmlspecialchars($p['day']) ?> <?= date('h:i A', strtotime($p['time_start'])) ?></small></td>
                        <td><?= htmlspecialchars(substr($p['reason'], 0, 60)) ?><?= strlen($p['reason']) > 60 ? '…' : '' ?></td>
                        <td><span class="badge badge-<?= $status_class ?>"><?= ucfirst($p['status']) ?></span></td>
                        <td><small><?= date('M d, Y', strtotime($p['created_at'])) ?></small></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-info"
                                    data-bs-toggle="modal" data-bs-target="#reviewModal<?= $p['pass_id'] ?>">
                                <i class="fa fa-eye"></i> Review
                            </button>
                        </td>
                    </tr>

                    <!-- Review Modal -->
                    <div class="modal fade" id="reviewModal<?= $p['pass_id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-light">
                                    <h5 class="modal-title"><i class="fa fa-sign-out-alt"></i> Leaving Pass Request</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <p class="text-muted small mb-1">Student</p>
                                            <p class="font-weight-bold"><?= htmlspecialchars($p['lastname'] . ', ' . $p['firstname']) ?></p>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-muted small mb-1">Pass Date</p>
                                            <p class="font-weight-bold"><?= date('l, F j, Y', strtotime($p['pass_date'])) ?></p>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <p class="text-muted small mb-1">Class</p>
                                            <p><?= htmlspecialchars($p['class_code'] . ' — ' . $p['class_name']) ?></p>
                                        </div>
                                        <div class="col-6">
                                            <p class="text-muted small mb-1">Section / Schedule</p>
                                            <p><?= htmlspecialchars($p['section']) ?> &bull; <?= htmlspecialchars($p['day']) ?> <?= date('h:i A', strtotime($p['time_start'])) ?></p>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <p class="text-muted small mb-1">Reason</p>
                                        <p><?= nl2br(htmlspecialchars($p['reason'])) ?></p>
                                    </div>
                                    <div class="mb-3">
                                        <p class="text-muted small mb-1">Current Status</p>
                                        <span class="badge badge-<?= $status_class ?>"><?= ucfirst($p['status']) ?></span>
                                        <?php if ($p['admin_notes']): ?>
                                            <p class="mt-1 small"><?= htmlspecialchars($p['admin_notes']) ?></p>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($p['status'] === 'pending'): ?>
                                        <hr>
                                        <form method="post" action="<?= base_url('admin/process_leaving_pass') ?>">
                                            <input type="hidden" name="pass_id" value="<?= $p['pass_id'] ?>">
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
            <i class="fa fa-info-circle"></i> No leaving pass requests found
            <?= $selected_status ? 'with status <strong>' . htmlspecialchars($selected_status) . '</strong>' : '' ?>.
        </div>
    <?php endif; ?>
</div>

<?php $this->load->view('footer'); ?>

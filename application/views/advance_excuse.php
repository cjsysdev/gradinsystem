<?php $this->load->view('header'); ?>

<div class="container">
    <?php $this->load->view('profile_only'); ?>
    <?php $this->load->view('nav_bar'); ?>

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
        <h4><i class="fa fa-calendar-times"></i> Advance Excuse Requests</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#submitExcuseModal">
            <i class="fa fa-plus"></i> New Request
        </button>
    </div>

    <?php if (!empty($requests)): ?>
        <table class="table table-bordered table-sm">
            <thead class="thead-light">
                <tr>
                    <th>Absence Date</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Schedule</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Admin Notes</th>
                    <th>Submitted</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $r): ?>
                    <?php
                    $status_class = $r['status'] === 'approved' ? 'success' : ($r['status'] === 'rejected' ? 'danger' : 'warning');
                    ?>
                    <tr>
                        <td><?= date('M d, Y', strtotime($r['absence_date'])) ?></td>
                        <td><strong><?= htmlspecialchars($r['class_code']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($r['class_name']) ?></small></td>
                        <td><?= htmlspecialchars($r['section']) ?></td>
                        <td><?= htmlspecialchars($r['day']) ?> <?= date('h:i A', strtotime($r['time_start'])) ?></td>
                        <td><?= htmlspecialchars(substr($r['reason'], 0, 60)) ?><?= strlen($r['reason']) > 60 ? '…' : '' ?></td>
                        <td><span class="badge badge-<?= $status_class ?>"><?= ucfirst($r['status']) ?></span></td>
                        <td><?= htmlspecialchars($r['admin_notes'] ?? '—') ?></td>
                        <td><small><?= date('M d, Y', strtotime($r['created_at'])) ?></small></td>
                        <td>
                            <?php if ($r['status'] === 'pending'): ?>
                                <a href="<?= base_url('advance_excuse/cancel/' . $r['request_id']) ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Cancel this request?')">
                                    <i class="fa fa-times"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i> You have no excuse requests yet. Click <strong>New Request</strong> to submit one.
        </div>
    <?php endif; ?>
</div>

<!-- Submit Excuse Modal -->
<div class="modal fade" id="submitExcuseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fa fa-calendar-times"></i> Submit Advance Excuse</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="<?= base_url('advance_excuse/submit') ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="font-weight-bold">Class / Section <span class="text-danger">*</span></label>
                        <select name="schedule_id" class="form-control" required>
                            <option value="">— Select class —</option>
                            <?php foreach ($schedules as $s): ?>
                                <option value="<?= $s['schedule_id'] ?>">
                                    <?= htmlspecialchars($s['class_code'] . ' — ' . $s['section'] . ' (' . $s['day'] . ' ' . date('h:i A', strtotime($s['time_start'])) . ')') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Absence Date <span class="text-danger">*</span></label>
                        <input type="date" name="absence_date" class="form-control" required
                               min="<?= date('Y-m-d', strtotime('-7 days')) ?>"
                               max="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                        <small class="text-muted">You may request up to 30 days in advance or up to 7 days past.</small>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" class="form-control" rows="4"
                                  placeholder="Explain the reason for your absence…" required maxlength="1000"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-paper-plane"></i> Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $this->load->view('footer'); ?>

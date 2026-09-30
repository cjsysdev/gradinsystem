<?php $this->load->view('header'); ?>

<div class="container mb-5">
    <?php $this->load->view('profile_only'); ?>
    <?php $this->load->view('admin/nav_bar'); ?>

    <div class="row mt-3">
        <div class="col">
            <h4><i class="fa fa-bullhorn"></i> Announcement History</h4>
        </div>
        <div class="col-auto">
            <a href="<?= base_url('admin/announcements') ?>" class="btn btn-sm btn-outline-primary">
                <i class="fa fa-plus"></i> New announcement
            </a>
        </div>
    </div>

    <?php if ($announcement): ?>
        <div class="card shadow-sm mt-3">
            <div class="card-body">
                <h6>
                    <?= htmlspecialchars($announcement['section'] ?: '—') ?>
                    <span class="text-muted small">
                        · <?= htmlspecialchars($announcement['audience']) ?>
                        · <?= htmlspecialchars($announcement['created_at']) ?>
                    </span>
                </h6>
                <p class="mb-2"><?= nl2br(htmlspecialchars($announcement['message'])) ?></p>
                <div class="small text-muted">
                    <?= (int) $announcement['segments'] ?> credit(s) each ·
                    <?= (int) $announcement['sent_count'] ?> sent ·
                    <?= (int) $announcement['failed_count'] ?> failed ·
                    status <?= htmlspecialchars($announcement['status']) ?>
                </div>
            </div>
        </div>

        <div class="table-responsive mt-3">
            <table class="table table-sm table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Recipient</th>
                        <th>Type</th>
                        <th>Number</th>
                        <th>Status</th>
                        <th>Error</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= htmlspecialchars($log['recipient_name']) ?></td>
                            <td><?= htmlspecialchars($log['recipient_type']) ?></td>
                            <td><?= htmlspecialchars($log['msisdn']) ?></td>
                            <td>
                                <span class="badge badge-<?= $log['status'] === 'sent' ? 'success' : 'danger' ?>">
                                    <?= htmlspecialchars($log['status']) ?>
                                </span>
                            </td>
                            <td class="small text-muted"><?= htmlspecialchars($log['error'] ?: '') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <h6 class="mt-4">All announcements</h6>
    <?php if (empty($recent)): ?>
        <p class="text-muted">Nothing sent yet.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>When</th>
                        <th>Section</th>
                        <th>Audience</th>
                        <th>Message</th>
                        <th>Sent</th>
                        <th>Failed</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent as $row): ?>
                        <tr onclick="window.location='<?= base_url('admin/sms_history/' . (int) $row['announcement_id']) ?>'"
                            style="cursor:pointer;">
                            <td class="small"><?= htmlspecialchars($row['created_at']) ?></td>
                            <td><?= htmlspecialchars($row['section'] ?: '—') ?></td>
                            <td class="small"><?= htmlspecialchars($row['audience']) ?></td>
                            <td class="small"><?= htmlspecialchars(mb_substr($row['message'], 0, 70)) ?>…</td>
                            <td><?= (int) $row['sent_count'] ?></td>
                            <td><?= (int) $row['failed_count'] ?></td>
                            <td><?= htmlspecialchars($row['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php $this->load->view('footer'); ?>

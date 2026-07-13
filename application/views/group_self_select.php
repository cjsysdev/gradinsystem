<?php
// Student-facing self-select group picker — shown by
// GroupWorkController::workspace() whenever the resolved grouping set has
// self_select on and the student's group (if any) hasn't reached its
// target size (set.min_members) yet. Three states below: not present today,
// present + ungrouped (create/join), present + in a still-forming group
// (waiting/leave). Once the group reaches min_members, workspace() renders
// the normal group_workspace.php instead — this view is never shown again.
$my_group = $my_group ?? null;
$member_count = $my_group ? count($my_group['members']) : 0;
?>
<?php $this->load->view('header'); ?>

<div class="container">
    <?php $this->load->view('profile_info'); ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($this->session->flashdata('error')) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0"><?= htmlspecialchars($assessment['title']) ?></h5>
        </div>
        <div class="card-body">
            <?php if (!$is_present): ?>
                <div class="text-center py-5">
                    <i class="fas fa-user-clock fa-2x mb-3 text-muted"></i>
                    <p class="mb-0">You must be marked present in today's session to form or join a group for this assessment.</p>
                    <p class="text-muted">Check in for attendance, then come back here.</p>
                </div>

            <?php elseif ($my_group): ?>
                <h6>Your group: <?= htmlspecialchars($my_group['group_name']) ?></h6>
                <p class="text-muted">
                    <?= $member_count ?>/<?= (int) $set['min_members'] ?> members &mdash;
                    waiting for <?= (int) $set['min_members'] - $member_count ?> more before you can start.
                </p>
                <ul class="list-group mb-3">
                    <?php foreach ($my_group['members'] as $m): ?>
                        <li class="list-group-item"><?= htmlspecialchars(trim($m['firstname'] . ' ' . $m['lastname'])) ?></li>
                    <?php endforeach; ?>
                </ul>
                <form method="post" action="<?= base_url('GroupWorkController/leave_group/' . $assessment['assessment_id']) ?>"
                      onsubmit="return confirm('Leave this group?');">
                    <button type="submit" class="btn btn-outline-danger btn-sm">Leave Group</button>
                </form>

            <?php else: ?>
                <div class="row">
                    <div class="col-md-5 mb-4">
                        <h6>Create a New Group</h6>
                        <form method="post" action="<?= base_url('GroupWorkController/create_group/' . $assessment['assessment_id']) ?>">
                            <div class="form-group">
                                <input type="text" name="group_name" class="form-control" placeholder="e.g. Team Alpha" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Create Group</button>
                        </form>
                    </div>
                    <div class="col-md-7">
                        <h6>Join an Existing Group</h6>
                        <?php if (empty($open_groups)): ?>
                            <p class="text-muted">No open groups yet &mdash; be the first to create one!</p>
                        <?php else: ?>
                            <?php foreach ($open_groups as $g): ?>
                                <div class="card mb-2">
                                    <div class="card-body py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <b><?= htmlspecialchars($g['group_name']) ?></b>
                                                <span class="text-muted"> &mdash; <?= count($g['members']) ?>/<?= (int) $set['min_members'] ?> members</span>
                                                <div class="text-muted" style="font-size:13px;">
                                                    <?= htmlspecialchars(implode(', ', array_map(function ($m) {
                                                        return trim($m['firstname'] . ' ' . $m['lastname']);
                                                    }, $g['members']))) ?>
                                                </div>
                                            </div>
                                            <form method="post" action="<?= base_url('GroupWorkController/join_group/' . $assessment['assessment_id'] . '/' . $g['group_id']) ?>">
                                                <button type="submit" class="btn btn-outline-primary btn-sm">Join</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $this->load->view('footer'); ?>

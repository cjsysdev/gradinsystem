<?php $this->load->view('header'); ?>
<div class="container mt-4">
    <?php $this->load->view('profile_only'); ?>
    <?php $this->load->view('admin/nav_bar'); ?>

    <a href="<?= base_url('Groupings/sets/' . urlencode($set['section_id'])) ?>" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="fa fa-arrow-left"></i> All Sets &mdash; <?= htmlspecialchars($set['section_id']) ?>
    </a>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">
            <?= htmlspecialchars($set['name']) ?>
            <?php if (!empty($set['self_select'])): ?>
                <span class="badge badge-info">Self-select</span>
            <?php else: ?>
                <span class="badge badge-secondary">Auto-assigned</span>
            <?php endif; ?>
        </h3>
        <form method="post" action="<?= base_url('Groupings/delete_set/' . $set['set_id']) ?>"
              onsubmit="return confirm('Delete this grouping set and all its groups?');">
            <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="fa fa-trash"></i> Delete Set
            </button>
        </form>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?= htmlspecialchars($this->session->flashdata('success')) ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($this->session->flashdata('error')) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('Groupings/add_group/' . $set['set_id']) ?>" class="form-inline mb-3">
        <input type="text" name="group_name" class="form-control form-control-sm mr-2" placeholder="New group name" required>
        <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fa fa-plus"></i> Add Group</button>
    </form>

    <?php if (empty($groups)): ?>
        <div class="alert alert-info">
            No groups found.
            <?php if (!empty($set['self_select'])): ?>students haven't formed any yet.<?php endif; ?>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($groups as $grp): ?>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <form method="post" action="<?= base_url('Groupings/rename_group/' . $grp['group_id']) ?>" class="form-inline mb-2">
                                <input type="text" name="group_name" value="<?= htmlspecialchars($grp['group_name']) ?>"
                                       class="form-control form-control-sm mr-2" style="max-width: 65%;" required>
                                <button type="submit" class="btn btn-sm btn-outline-primary" title="Rename group">
                                    <i class="fa fa-save"></i>
                                </button>
                                <?php if (!empty($set['self_select'])): ?>
                                    <small class="text-muted ml-2">(<?= count($grp['members']) ?>/<?= (int) $set['min_members'] ?>)</small>
                                <?php endif; ?>
                            </form>
                            <ul class="list-unstyled mb-0">
                                <?php foreach ($grp['members'] as $m): ?>
                                    <li class="d-flex justify-content-between align-items-center mb-1">
                                        <span><?= htmlspecialchars($m['firstname'] . ' ' . $m['lastname'] . ' (' . $m['trans_no'] . ')') ?></span>
                                        <span class="text-nowrap">
                                            <?php if (count($groups) > 1): ?>
                                                <form method="post" action="<?= base_url('Groupings/move_member') ?>" class="form-inline d-inline">
                                                    <input type="hidden" name="from_group_id" value="<?= $grp['group_id'] ?>">
                                                    <input type="hidden" name="student_id" value="<?= htmlspecialchars($m['trans_no']) ?>">
                                                    <select name="to_group_id" class="form-control form-control-sm d-inline-block" style="width: auto;">
                                                        <?php foreach ($groups as $other): ?>
                                                            <?php if ($other['group_id'] == $grp['group_id']) continue; ?>
                                                            <option value="<?= $other['group_id'] ?>"><?= htmlspecialchars($other['group_name']) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Move to group">
                                                        <i class="fa fa-arrow-right"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <form method="post" action="<?= base_url('Groupings/remove_member') ?>" class="d-inline"
                                                  onsubmit="return confirm('Remove this student from the group?');">
                                                <input type="hidden" name="group_id" value="<?= $grp['group_id'] ?>">
                                                <input type="hidden" name="student_id" value="<?= htmlspecialchars($m['trans_no']) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove from group">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </form>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                                <?php if (empty($grp['members'])): ?>
                                    <li class="text-muted">No members yet.</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($ungrouped)): ?>
        <div class="card mt-3">
            <div class="card-body">
                <h5>Not Yet Grouped <small class="text-muted">(<?= count($ungrouped) ?>)</small></h5>
                <p class="text-muted small">
                    Students enrolled in this section who aren't in a group yet &mdash; e.g. marked late/absent
                    when this set was auto-assigned, or enrolled afterward. Add them to a group below.
                </p>
                <ul class="list-group list-group-flush">
                    <?php foreach ($ungrouped as $u): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <span><?= htmlspecialchars($u['firstname'] . ' ' . $u['lastname']) ?></span>
                            <?php if (!empty($groups)): ?>
                                <form method="post" action="<?= base_url('Groupings/add_member') ?>" class="form-inline">
                                    <input type="hidden" name="student_id" value="<?= htmlspecialchars($u['student_id']) ?>">
                                    <select name="group_id" class="form-control form-control-sm mr-2" required>
                                        <option value="">Add to group...</option>
                                        <?php foreach ($groups as $grp): ?>
                                            <option value="<?= $grp['group_id'] ?>"><?= htmlspecialchars($grp['group_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                        <i class="fa fa-plus"></i> Add
                                    </button>
                                </form>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php $this->load->view('footer'); ?>

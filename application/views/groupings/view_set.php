<?php $this->load->view('header'); ?>
<div class="container mt-4">
    <?php $this->load->view('profile_only'); ?>
    <?php $this->load->view('admin/nav_bar'); ?>

    <a href="<?= base_url('Groupings/sets/' . urlencode($set['section_id'])) ?>" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="fa fa-arrow-left"></i> All Sets &mdash; <?= htmlspecialchars($set['section_id']) ?>
    </a>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><?= htmlspecialchars($set['name']) ?></h3>
        <form method="post" action="<?= base_url('Groupings/delete_set/' . $set['set_id']) ?>"
              onsubmit="return confirm('Delete this grouping set and all its groups?');">
            <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="fa fa-trash"></i> Delete Set
            </button>
        </form>
    </div>

    <?php if (empty($groups)): ?>
        <div class="alert alert-info">No groups found.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($groups as $grp): ?>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5><?= htmlspecialchars($grp['group_name']) ?></h5>
                            <ul>
                                <?php foreach ($grp['members'] as $m): ?>
                                    <li><?= htmlspecialchars($m['firstname'] . ' ' . $m['lastname'] . ' (' . $m['trans_no'] . ')') ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php $this->load->view('footer'); ?>

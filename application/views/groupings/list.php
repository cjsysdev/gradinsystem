<?php $this->load->view('header'); ?>
<div class="container mt-4">
    <h3>Groups for section: <?= htmlspecialchars($section) ?></h3>
    <a href="<?= base_url('Groupings/create') ?>" class="btn btn-sm btn-outline-secondary mb-3">Create New</a>
    <?php if (empty($groups)): ?>
        <div class="alert alert-info">No groups found.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($groups as $grp): ?>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5><?= htmlspecialchars($grp['group_name']) ?></h5>
                            <p>Min members: <?= htmlspecialchars($grp['min_members']) ?></p>
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
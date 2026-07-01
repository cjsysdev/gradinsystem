<?php $this->load->view('header'); ?>
<div class="container mt-4">
    <?php $this->load->view('profile_only'); ?>
    <?php $this->load->view('admin/nav_bar'); ?>

    <a href="<?= base_url('Groupings') ?>" class="btn btn-sm btn-outline-secondary mb-3">
        <i class="fa fa-arrow-left"></i> All Sections
    </a>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Grouping Sets &mdash; <?= htmlspecialchars($section) ?></h3>
        <a href="<?= base_url('Groupings/create/' . urlencode($section)) ?>" class="btn btn-primary">
            <i class="fa fa-plus"></i> New Grouping Set
        </a>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?= htmlspecialchars($this->session->flashdata('success')) ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($this->session->flashdata('error')) ?></div>
    <?php endif; ?>

    <?php if (empty($sets)): ?>
        <div class="alert alert-info">
            No grouping sets yet for this section. Create one (e.g. "Lab Groups", "Project Teams") to get started —
            you can create several independent sets and pick which one applies to each assessment.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($sets as $set): ?>
                <div class="col-md-4 mb-3">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <h5><?= htmlspecialchars($set['name']) ?></h5>
                            <p class="text-muted small mb-3">Min members: <?= (int) $set['min_members'] ?></p>
                            <div class="mt-auto d-flex justify-content-between">
                                <a href="<?= base_url('Groupings/view_set/' . $set['set_id']) ?>"
                                   class="btn btn-sm btn-outline-primary">View Groups</a>
                                <form method="post" action="<?= base_url('Groupings/delete_set/' . $set['set_id']) ?>"
                                      onsubmit="return confirm('Delete this grouping set and all its groups?');">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php $this->load->view('footer'); ?>

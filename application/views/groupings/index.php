<?php $this->load->view('header'); ?>
<div class="container">
    <?php $this->load->view('profile_only'); ?>
    <?php $this->load->view('admin/nav_bar'); ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="fa fa-people-group"></i> Groupings</h3>
        <a href="<?= base_url('Groupings/create') ?>" class="btn btn-primary">
            <i class="fa fa-plus"></i> Create Groups
        </a>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?= htmlspecialchars($this->session->flashdata('success')) ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($this->session->flashdata('error')) ?></div>
    <?php endif; ?>

    <?php if (empty($sections)): ?>
        <div class="alert alert-info">No sections found.</div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($sections as $s): ?>
                <a href="<?= base_url('Groupings/sets/' . urlencode($s['section'])) ?>"
                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <?= htmlspecialchars($s['section']) ?>
                    <i class="fa fa-chevron-right text-muted"></i>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php $this->load->view('footer'); ?>

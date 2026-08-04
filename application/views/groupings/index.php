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
        <div class="row mt-3">
            <?php foreach ($sections as $sec): ?>
                <?php $is_active = ($selected_section === $sec['section']); ?>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
                    <a href="<?= base_url('Groupings/sets/' . urlencode($sec['section'])) ?>"
                       class="text-decoration-none">
                        <div class="card h-100 shadow-sm section-card text-center <?= $is_active ? 'border-primary active-section' : '' ?>">
                            <div class="card-body p-3">
                                <!-- <i class="fa fa-users fa-2x <?= $is_active ? 'text-primary' : 'text-secondary' ?> mb-2"></i> -->
                                <p class="card-text mb-1" style="font-weight:600;line-height:1.2;">
                                    <?= htmlspecialchars($sec['section']) ?>
                                </p>
                                <small class="text-muted">
                                    <?= (int) $sec['student_count'] ?> student<?= (int) $sec['student_count'] !== 1 ? 's' : '' ?>
                                </small>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?php $this->load->view('footer'); ?>

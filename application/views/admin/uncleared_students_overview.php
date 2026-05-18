<?php $this->load->view('header'); ?>

<div class="container">
    <?php $this->load->view('profile_only'); ?>
    <?php $this->load->view('admin/nav_bar'); ?>

    <div class="row mt-3 mb-3">
        <div class="col">
            <h4>Uncleared Students</h4>
            <p class="text-muted mb-0">Select a section to view and clear students.</p>
        </div>
    </div>

    <?php if (empty($sections)): ?>
        <div class="alert alert-success">
            <i class="fa fa-check-circle mr-1"></i> All students are cleared for the active semester.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($sections as $sec): ?>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4">
                    <a href="<?= base_url('uncleared_students/' . urlencode($sec['section'])) ?>"
                       class="text-decoration-none text-dark">
                        <div class="card h-100 shadow-sm section-card text-center">
                            <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center">
                                <div class="rounded-circle bg-warning d-inline-flex align-items-center justify-content-center mb-2"
                                     style="width:56px;height:56px;">
                                    <i class="fa fa-user-times fa-lg text-white"></i>
                                </div>
                                <p class="card-text mb-1" style="font-size:.9rem;font-weight:600;">
                                    <?= htmlspecialchars($sec['section']) ?>
                                </p>
                                <span class="badge badge-danger" style="font-size:.8rem;">
                                    <?= (int)$sec['uncleared_count'] ?> uncleared
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.section-card {
    transition: transform .15s, box-shadow .15s;
    cursor: pointer;
}
.section-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 .4rem 1rem rgba(0,0,0,.15) !important;
}
</style>

<?php $this->load->view('footer'); ?>

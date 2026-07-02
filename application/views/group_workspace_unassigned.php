<?php $this->load->view('header'); ?>

<div class="container">
    <?php $this->load->view('profile_info'); ?>

    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0"><?= htmlspecialchars($assessment['title']) ?></h5>
        </div>
        <div class="card-body text-center py-5">
            <i class="fas fa-users-slash fa-2x mb-3 text-muted"></i>
            <p class="mb-0">You're not assigned to a group for this assessment yet.</p>
            <p class="text-muted">Please contact your teacher to be added to a group.</p>
        </div>
    </div>
</div>

<?php $this->load->view('footer'); ?>

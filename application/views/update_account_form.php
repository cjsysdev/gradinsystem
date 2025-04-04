<?php $this->load->view('header'); ?>

<div class="container ">
    <?php $this->load->view('profile_info') ?>

    <h2 class="text-center">Update Your Account</h2>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>

    <form action="<?= base_url('StudentController/update_account') ?>" class="m-2" method="POST">
        <!-- Hidden field for student_id -->
        <input type="hidden" name="student_id" value="<?= $this->session->student_id ?>">

        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" id="username" class="form-control" placeholder="Enter Username" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control" placeholder="Enter Password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block mt-5">Update Account</button>
    </form>
</div>


<?php $this->load->view('footer'); ?>
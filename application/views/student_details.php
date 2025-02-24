<?php $this->load->view('header') ?>


<div class="container">
    <div class="dashboard">
        <div class="card-body p-1 text-center">
            <h1><span class="badge badge-primary mb-3"><?= $student_no ?></span></h1>
            <h6 class="card-subtitle text-body-secondary m-2"><?= $lastname ?>, <?= $firstname ?></h6>
            <h6 class="card-subtitle text-body-secondary"><?= $course ?> - <?= $current_year ?></h6>
        </div>
        <a href="/gradingsystem" class="btn btn-outline-secondary col m-2">Back</a>
    </div>
</div>

<?php $this->load->view('footer') ?>
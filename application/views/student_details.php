<?php $this->load->view('header') ?>


<div class="container">
    <div class="dashboard">
        <div class="card-body p-1 text-center">
            <h1><span class="badge badge-primary mb-3"><?= $student_no ?></span></h1>
            <h6 class="card-subtitle text-body-secondary m-2"><?= $lastname ?>, <?= $firstname ?></h6>
            <h6 class="card-subtitle text-body-secondary"><?= $course ?> - <?= $current_year ?></h6>
        </div>
        <form action="login" method="POST">
            <div class="form-group">
                <input type="text" class="form-control" placeholder="Username" name="username" value="<?= $student_no ?>" required hidden>
            </div>
            <div class="form-group">
                <input type="password" class="form-control" placeholder="Password" name="password" value="<?= $student_no ?>" required hidden>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">Login</button>
                <button href="/gradingsystem" class="btn btn-outline-secondary btn-block">Back</button>
            </div>
        </form>
    </div>
</div>

<?php $this->load->view('footer') ?>
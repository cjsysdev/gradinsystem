<?php $this->load->view('header') ?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="col-md-5 col-lg-4">

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-body p-4 text-center">

                <div class="mb-4">
                    <span class="badge rounded-pill bg-primary text-light px-3 mb-3"><?= $student_no ?></span>
                    <h4 class="fw-bold mb-1"><?= $lastname ?>, <?= $firstname ?></h4>
                    <p class="text-muted"><?= $course ?></p>
                </div>

                <hr class="my-4 text-secondary opacity-25">

                <form action="login" method="POST">
                    <input type="hidden" name="username" value="<?= $student_no ?>">
                    <input type="hidden" name="password" value="<?= $student_no ?>">

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                            Confirm & Login
                        </button>
                        <a href="<?= base_url('login') ?>" class="btn btn-link text-decoration-none text-muted mt-2">
                            Not you? Go Back
                        </a>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>

<?php $this->load->view('footer') ?>
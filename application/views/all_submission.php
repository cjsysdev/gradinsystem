<?php $this->load->view('header'); ?>

<div class="container">
    <div class="dashboard">
        <div class="row justify-content-center mt-5">
            <div class="col">

                <?php foreach ($submissions as $row) : ?>
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            <h3 class="card-title mb-1"><?= $row['classwork_id'] . " - " . $row['lastname'] . ", " . $row['firstname']  ?></h3>
                            <hr>
                            <p class="card-text mb-3"><?= $row['created_at'] ?></p>
                            <a href="<?= base_url('add_score/' . $row['classwork_id'] . '/1') ?>" type="button" class="btn btn-outline-secondary" name="score" value="good"><i class="fa fa-star" aria-hidden="true"></i></a>
                            <a href="<?= base_url('add_score/' . $row['classwork_id'] . '/2') ?>" type="button" class="btn btn-outline-secondary" name="score" value="average"><i class="fa fa-star" aria-hidden="true"></i></a>
                            <a href="<?= base_url('add_score/' . $row['classwork_id'] . '/3') ?>" type="button" class="btn btn-outline-secondary" name="score" value="excellent"><i class="fa fa-star" aria-hidden="true"></i></a>
                            <a href="<?= base_url('add_score/' . $row['classwork_id'] . '/4') ?>" type="button" class="btn btn-outline-secondary" name="score" value="elite"><i class="fa fa-star" aria-hidden="true"></i></a>
                            <a href="<?= base_url('add_score/' . $row['classwork_id'] . '/5') ?>" type="button" class="btn btn-outline-secondary" name="score" value="elite"><i class="fa fa-star" aria-hidden="true"></i></a>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>
    </div>
</div>
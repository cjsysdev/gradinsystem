<?php $this->load->view('header'); ?>

<div class="container">
    <div class="dashboard">
        <?php $this->load->view('profile_info') ?>
        <div class="row justify-content-center">
            <div class="col">

                <?php foreach ($submissions as $row) : ?>
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            <h3 class="card-title mb-1"><?= $row['classwork_id'] . " - " . $row['lastname'] . ", " . $row['firstname']  ?></h3>
                            <hr>
                            <p class="card-text mb-3"><?= $row['created_at'] ?></p>
                            <a href="<?= base_url('add_score/' . $row['classwork_id'] . '/1') ?>" type="button" class="btn btn-outline-secondary" name="score" value="good">Good</a>
                            <a href="<?= base_url('add_score/' . $row['classwork_id'] . '/2') ?>" type="button" class="btn btn-outline-secondary" name="score" value="average">Average</a>
                            <a href="<?= base_url('add_score/' . $row['classwork_id'] . '/3') ?>" type="button" class="btn btn-outline-secondary" name="score" value="excellent">Excellent</a>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>
    </div>
</div>
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

                            <!-- Form to submit score -->
                            <form action="<?= base_url('ClassworkController/add_score') ?>" method="POST">
                                <input type="hidden" name="classwork_id" value="<?= $row['classwork_id'] ?>">
                                <input type="hidden" name="student_id" value="<?= $row['trans_no'] ?>">
                                <div class="input-group mb-3">
                                    <input type="number" name="score" class="form-control" placeholder="Enter score" min="0" required>
                                    <button type="submit" class="btn btn-info">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>
    </div>
</div>
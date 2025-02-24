<?php $this->load->view('header') ?>


<div class="container">
    <div class="dashboard">
        <?php $this->load->view('profile_info') ?>

        <div class="card-body p-1 text-center">
            <span class="badge badge-secondary mb-3"><?= $class_code ?? NULL ?></span>
            <h6 class="card-subtitle text-body-secondary"><?= $class_name ?></h6>
            <p class="card-text m-0"><?= $section, ' ', $day, ' : ', $time_start, '-', $time_end ?></p>
            <p class="card-text m-0 mt-3"><?= date('H:i:s') ?></p>
        </div>
        <!-- <form action="">
            <div class="form-group">
                <input type="text" class="form-control" placeholder="<?= $class_id ?>"
                value="<?= $class_id ?>" 
                name="class_id" required hidden>
            </div>
            <div class="form-group">
                <input type="text" class="form-control" placeholder="present"
                value="present"
                name="class_id" required hidden>
            </div>
            <div class="form-group">
                <select class="form-control" name="status" required>
                    <option selected default value="present">Present</option>
                    <option value="absent">Absent</option>
                    <option value="excuse">Excuse</option>
                </select>
            </div>
            <div class="total-section mt-3">
                <button class="btn btn-success btn-total">Submit</button>
            </div>
        </form> -->
    </div>
</div>

<?php $this->load->view('footer') ?>
<?php $this->load->view('header') ?>


<div class="container">
    <div class="dashboard">
        <?php $this->load->view('profile_info') ?>
        <?php if (!$input_id) : ?>

            <div class="card-body p-1 text-center">
                <span class="badge badge-secondary mb-2"><?= $input_id ?? NULL ?></span>
                <span class="badge badge-secondary mb-2"><?= $this->session->type, ' ',  $this->session->input_id ?></span>
                <h6 class="card-subtitle text-body-secondary"><?= $this->session->subject_title ?></h6>
                <p class="card-text m-0"><?= $this->session->year_level, $this->session->section, ' : ', $this->session->schedule ?></p>
            </div>
            <form action="upload_activity" method="POST" enctype="multipart/form-data">
                <div class="category-btns row">
                    <div class="col-12 form-section p-2">
                        <label for="activities-score" class="form-label">Activity Score</label>
                        <input type="number" class="form-control" min="1" max="<?= $_SESSION['max_score'] ?>" onKeyPress="if(this.value.length==2) return false" placeholder="Enter Score" id="activities-score" name="score" />
                    </div>
                    <div class="col-12 form-section p-2">
                        <label for="photo-upload" class="form-label">Upload Activity</label>
                        <input type="file" class="form-control" id="photo-upload" name="photo-upload" accept="image/*" capture="camera">
                    </div>
                </div>
                <div class="total-section pt-3">
                    <button class="btn btn-success btn-total" type="submit">Upload</button>
                </div>
            </form>

        <?php else : ?>
            <div class="text-center">
                <span class="badge badge-success mb-2 p-2">Successfully Uploaded</span>
            </div>
        <?php endif ?>
        <!-- <div class="total-section">
            <button class="btn btn-secondary btn-total">Total</button>
        </div> -->

    </div>
</div>

<?php $this->load->view('footer') ?>
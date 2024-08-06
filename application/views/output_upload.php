<?php $this->load->view('header') ?>


<div class="container">
    <div class="dashboard">
        <?php $this->load->view('profile_info') ?>
        <div class="card-body p-1 text-center">
            <!-- <h5 class="card-title mb-0">Activity title</h5> -->
            <span class="badge badge-secondary mb-2">Activity</span>
            <h6 class="card-subtitle text-body-secondary">CC102 : Computer Programming 1</h6>
            <p class="card-text">1B : Lec - 8:00 - 9:00</p>
        </div>
        <form class="p-2" action="" method="POST" >
            <div class="category-btns row">
                <div class="col-12 form-section p-2">
                    <label for="activities-score" class="form-label">Activity Score</label>
                    <input type="number" class="form-control" id="activities-score" placeholder="Enter Activity Score">
                </div>
                <div class="col-12 form-section p-2">
                    <label for="photo-upload" class="form-label">Upload Activity</label>
                    <input type="file" class="form-control" id="photo-upload" accept="image/*" capture="camera">
                </div>
            </div>
            <div class="total-section pt-3">
                <button class="btn btn-success btn-total" type="submit">Upload</button>
            </div>
        </form>

        <!-- <div class="total-section">
            <button class="btn btn-secondary btn-total">Total</button>
        </div> -->

    </div>
</div>

<?php $this->load->view('footer') ?>
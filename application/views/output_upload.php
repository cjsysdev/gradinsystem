<?php $this->load->view('header') ?>


<div class="container">
    <div class="dashboard">
        <?php $this->load->view('profile_info') ?>
        <?php if ($this->session->flashdata('error') !== null): ?>
            <div class="alert alert-danger">
                <?= $this->session->flashdata('error'); ?>
            </div>
        <?php elseif ($this->session->flashdata('success') !== null): ?>
            <div class="alert alert-success">
                <?= $this->session->flashdata('success'); ?>
            </div>
        <?php endif; ?>
        <!-- <div class="container">
            <form action="upload_activity" method="POST" enctype="multipart/form-data">
                <div class="category-btns row">
                    <div class="col-12 form-section p-2">
                        <label for="project_title" class="form-label">Project Name</label>
                        <input type="text" class="form-control mb-2" placeholder="Database design for a grocery point of sale system" name="project_title">
                        <label for="members" class="form-label">Members Separated by semi-colon (;)</label>
                        <input type="text" class="form-control mb-2" name="members" placeholder="Firstname Lastname ; Firstname Lastname ; Firstname Lastname ; Firstname Lastname ; " required>
                        <label for="photo-upload" class="form-label">Upload File (SQL)</label>
                        <input type="file" class="form-control" id="photo-upload" name="photo-upload" accept="*/*" required>
                    </div>
                </div>
                <div class="total-section pt-3">
                    <button class="btn btn-info btn-total btn-block" type="submit"><i class="fa fa-upload" aria-hidden="true"> </i> Upload</button>
                </div>
            </form>
        </div> -->
    </div>
</div>

<?php $this->load->view('footer') ?>
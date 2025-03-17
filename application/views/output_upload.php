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
        <form action="upload_activity" method="POST" enctype="multipart/form-data">
            <div class="category-btns row">
                <div class="col-12 form-section p-2">
                    <label for="photo-upload" class="form-label">Upload Midterm Project</label>
                    <input type="file" class="form-control" id="photo-upload" name="photo-upload" accept="pdf/doc/docx">
                </div>
            </div>
            <div class="total-section pt-3">
                <button class="btn btn-success btn-total" type="submit">Upload</button>
            </div>
        </form>
    </div>
</div>

<?php $this->load->view('footer') ?>
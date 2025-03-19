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
        <?php
        $course = $this->class_student->get(['student_id' => $this->session->student_id])->class_id;
        if (!isset($course)) redirect('attendance');
        if ($course === '1')
            $desc = 'IM';
        else
            $desc = 'CP2';
        ?>
        <a class="btn alert-secondary btn-block" href="./uploads/<?= $desc ?>_Midterm_Project.docx" download="<?= $desc ?>_Midterm_Project.docx" src="./uploads/<?= $desc ?>_Midterm_Project.docx"><i class="fa fa-download" aria-hidden="true" style="margin-right: 10px"> </i>Download Project Details</a>
        <form action="upload_activity" method="POST" enctype="multipart/form-data">
            <div class="category-btns row">
                <div class="col-12 form-section p-2">
                    <label for="photo-upload" class="form-label">Upload Midterm Project</label>
                    <input type="file" class="form-control" id="photo-upload" name="photo-upload" accept="*">
                </div>
            </div>
            <div class="total-section pt-3">
                <button class="btn btn-info btn-total btn-block" type="submit"><i class="fa fa-upload" aria-hidden="true"> </i> Upload</button>
            </div>
        </form>
    </div>
</div>

<?php $this->load->view('footer') ?>
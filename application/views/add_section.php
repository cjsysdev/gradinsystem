<?php $this->load->view('header') ?>

<?php if ($class): ?>
    <div class="card-body p-1 text-center">
        <h5><span class="badge badge-secondary mb-1"><?= $class['class_code'],
                                                        ' ',
                                                        $class['type'] ?? null ?></span></h5>
        <h6 class="card-subtitle text-body-secondary"><?= $class['class_name'],
                                                        ' ',
                                                        $class['type'] ?></h6>
        <p class="card-text m-0"><?= $class['section'],
                                    ' ',
                                    $class['day'],
                                    ' : ',
                                    convert_time($class['time_start']),
                                    '-',
                                    convert_time($class['time_end']) ?></p>
        <p class="card-text m-0 mt-3" id="txt"></p>
        <div id="txt"></div>
    </div>
<?php endif; ?>

<div class="container mt-5">
    <div class="login-form">
        <form action="<?= base_url('student/section') ?>" method="POST">
            <h4 class="text-center mb-4">Add Section</h4>
            <?php if ($this->session->flashdata('error')) : ?>
                <div class="alert alert-danger">
                    <?= $this->session->flashdata('error'); ?>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <input type="text" class="form-control" placeholder="Section" name="section" value="<?= $class['section'] ?? NULL ?>" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-success btn-block">Add Section</button>
            </div>
        </form>
    </div>
</div>

<?php $this->load->view('footer') ?>
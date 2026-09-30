<?php $this->load->view('header'); ?>

<div class="container">
    <?php $this->load->view('profile_only'); ?>
    <?php $this->load->view('admin/nav_bar'); ?>

    <div class="row mt-3">
        <div class="col text-center">
            <h4>Settings</h4>
        </div>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <?= htmlspecialchars($this->session->flashdata('success')) ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <?php
    $groups = [
        'student' => [
            'title' => 'Student Nav Bar',
            'note'  => 'Links students see at the top of their pages. Project Log and Materials still only appear for students who have one. Exam mode replaces this bar with a single Exam button.',
            'links' => $student_nav,
        ],
        'admin' => [
            'title' => 'Admin Nav Bar',
            'note'  => 'Dashboard and Settings are always shown.',
            'links' => $admin_nav,
        ],
    ];
    ?>

    <div class="row mt-3">
        <?php foreach ($groups as $group => $g): ?>
            <div class="col-md-6 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-header font-weight-bold"><?= $g['title'] ?></div>
                    <div class="card-body">
                        <p class="small text-muted"><?= $g['note'] ?></p>
                        <form method="POST" action="<?= base_url('admin/save_nav_settings') ?>">
                            <input type="hidden" name="group" value="<?= $group ?>">
                            <?php foreach ($g['links'] as $key => $link): ?>
                                <div class="custom-control custom-switch mb-2">
                                    <input type="checkbox" class="custom-control-input" id="<?= $key ?>"
                                           name="enabled[]" value="<?= $key ?>" <?= $link['enabled'] ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="<?= $key ?>"><?= htmlspecialchars($link['label']) ?></label>
                                </div>
                            <?php endforeach; ?>
                            <div class="mt-3">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setAll(this.form, true)">Show all</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setAll(this.form, false)">Hide all</button>
                                <button type="submit" class="btn btn-sm btn-primary float-right">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    function setAll(form, on) {
        form.querySelectorAll('input[name="enabled[]"]').forEach(function (cb) { cb.checked = on; });
    }
</script>

<?php $this->load->view('footer'); ?>

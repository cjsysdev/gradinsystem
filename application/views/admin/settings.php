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
        <div class="col-12 mb-3">
            <div class="card shadow-sm <?= !empty($exam_term) ? 'border-danger' : '' ?>">
                <div class="card-header font-weight-bold d-flex align-items-center">
                    Exam Mode
                    <span class="badge <?= !empty($exam_term) ? 'badge-danger' : 'badge-secondary' ?> ml-2"><?= !empty($exam_term) ? 'ON' : 'OFF' ?></span>
                </div>
                <div class="card-body">
                    <p class="small text-muted">While on, students see a single Exam button instead of their nav bar, and submitted work and quiz answer reviews are hidden. It takes effect on their next page load, so nobody needs to log in again.</p>
                    <form method="POST" action="<?= base_url('admin/save_exam_settings') ?>">
                        <div class="custom-control custom-switch mb-2">
                            <input type="checkbox" class="custom-control-input" id="exam_term"
                                   name="exam_term" value="1" <?= !empty($exam_term) ? 'checked' : '' ?>>
                            <label class="custom-control-label" for="exam_term">Exam mode (exam term)</label>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>

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

        <div class="col-md-6 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header font-weight-bold">Past Due Classwork</div>
                <div class="card-body">
                    <p class="small text-muted">Types of unsubmitted classwork students still see on their Classwork page after it is past due. Open work, and work reopened for a student, always shows.</p>
                    <form method="POST" action="<?= base_url('admin/save_past_due_settings') ?>">
                        <?php foreach ($past_due_types as $t): ?>
                            <div class="custom-control custom-switch mb-2">
                                <input type="checkbox" class="custom-control-input" id="past_due_<?= (int) $t['iotype_id'] ?>"
                                       name="iotype_ids[]" value="<?= (int) $t['iotype_id'] ?>" <?= $t['enabled'] ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="past_due_<?= (int) $t['iotype_id'] ?>"><?= htmlspecialchars($t['type']) ?></label>
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
    </div>
</div>

<script>
    function setAll(form, on) {
        form.querySelectorAll('input[type="checkbox"]').forEach(function (cb) { cb.checked = on; });
    }
</script>

<?php $this->load->view('footer'); ?>

<?php $this->load->view('header') ?>

<div class="container mt-5">
    <div class="add-section-form">

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($this->session->flashdata('error')) ?>
            </div>
        <?php endif; ?>

        <h4 class="text-center mb-1">Enroll for This Semester</h4>
        <?php if (!empty($active_semester)): ?>
            <p class="text-center text-muted mb-4">
                <?= htmlspecialchars($active_semester['description'] ?? '') ?>
            </p>
        <?php endif; ?>

        <form id="sectionForm" action="<?= base_url('student/section') ?>" method="POST">
            <div class="form-group">
                <label for="schedule_id">Select Your Class / Section</label>
                <select name="schedule_id" id="schedule_id" class="form-control" required>
                    <option value="">-- Choose a class --</option>
                    <?php foreach ($schedules as $s): ?>
                        <option value="<?= (int)$s['schedule_id'] ?>">
                            <?= htmlspecialchars($s['class_code'] . ' — ' . $s['class_name'] . ' [' . $s['section'] . '] ' . ($s['type'] ?? '')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-success btn-block">Enroll</button>
            </div>
        </form>

    </div>
</div>

<?php $this->load->view('footer') ?>
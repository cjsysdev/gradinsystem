<?php $this->load->view('header'); ?>

<div class="container">
    <?php $this->load->view('profile_only'); ?>
    <?php $this->load->view('admin/nav_bar'); ?>

    <div class="row mt-3">
        <div class="col text-center">
            <h4>Students by Section</h4>
        </div>
    </div>

    <!-- Section selector -->
    <div class="row justify-content-center mt-3">
        <div class="col-md-6">
            <form method="get" action="<?= base_url('admin/students_by_section') ?>">
                <div class="input-group">
                    <select name="section" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Select a Section --</option>
                        <?php foreach ($sections as $sec): ?>
                            <option value="<?= htmlspecialchars($sec['section']) ?>"
                                <?= ($selected_section === $sec['section']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sec['section']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">View</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if ($selected_section): ?>
        <div class="row mt-3">
            <div class="col">
                <h5 class="text-muted">Section: <strong><?= htmlspecialchars($selected_section) ?></strong>
                    &mdash; <?= count($students) ?> student<?= count($students) !== 1 ? 's' : '' ?>
                </h5>
            </div>
        </div>

        <?php if (empty($students)): ?>
            <div class="alert alert-warning mt-2">No students found in this section.</div>
        <?php else: ?>
            <div class="row mt-2">
                <?php foreach ($students as $student): ?>
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4">
                        <a href="<?= base_url('admin/student_summary/' . $student['student_id']) ?>"
                           class="text-decoration-none text-dark">
                            <div class="card h-100 shadow-sm student-card text-center">
                                <div class="card-body p-2">
                                    <?php if (!empty($student['profile_pic'])): ?>
                                        <img src="<?= base_url('uploads/profile_pics/' . htmlspecialchars($student['profile_pic'])) ?>"
                                             alt="<?= htmlspecialchars($student['firstname']) ?>"
                                             class="rounded-circle mb-2"
                                             style="width:80px;height:80px;object-fit:cover;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-2"
                                             style="width:80px;height:80px;">
                                            <i class="fa fa-user fa-2x text-white"></i>
                                        </div>
                                    <?php endif; ?>
                                    <p class="card-text mb-0" style="font-size:.85rem;font-weight:600;line-height:1.2;">
                                        <?= htmlspecialchars($student['lastname'] . ', ' . $student['firstname']) ?>
                                    </p>
                                    <small class="text-muted"><?= htmlspecialchars($student['student_id']) ?></small>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.student-card {
    transition: transform .15s, box-shadow .15s;
    cursor: pointer;
}
.student-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 .4rem 1rem rgba(0,0,0,.15) !important;
}
</style>

<?php $this->load->view('footer'); ?>

<?php $this->load->view('header'); ?>

<div class="container">
    <?php $this->load->view('profile_only'); ?>
    <?php $this->load->view('admin/nav_bar'); ?>

    <div class="row mt-3">
        <div class="col text-center">
            <h4>Register New Student</h4>
        </div>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <?= htmlspecialchars($this->session->flashdata('success')) ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            <?= htmlspecialchars($this->session->flashdata('error')) ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?= base_url('admin/register_student') ?>" id="reg-form" class="mt-3">

        <!-- Personal Information -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header font-weight-bold">
                <i class="fa fa-user mr-2"></i>Personal Information
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Student No. <span class="text-danger">*</span></label>
                        <input type="text" name="student_no" id="student_no" class="form-control"
                               required maxlength="12"
                               value="<?= htmlspecialchars($this->input->post('student_no') ?? '') ?>">
                        <small id="student_no_feedback" class="form-text"></small>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="lastname" class="form-control" required maxlength="35"
                               value="<?= htmlspecialchars($this->input->post('lastname') ?? '') ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label>First Name <span class="text-danger">*</span></label>
                        <input type="text" name="firstname" class="form-control" required maxlength="35"
                               value="<?= htmlspecialchars($this->input->post('firstname') ?? '') ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Middle Name</label>
                        <input type="text" name="middlename" class="form-control" maxlength="35"
                               value="<?= htmlspecialchars($this->input->post('middlename') ?? '') ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Ext. (Jr., III)</label>
                        <input type="text" name="extname" class="form-control" maxlength="10"
                               value="<?= htmlspecialchars($this->input->post('extname') ?? '') ?>">
                    </div>
                    <div class="form-group col-md-3">
                        <label>Gender</label>
                        <select name="gender" class="form-control">
                            <option value="">-- Select --</option>
                            <option value="M" <?= $this->input->post('gender') === 'M' ? 'selected' : '' ?>>Male</option>
                            <option value="F" <?= $this->input->post('gender') === 'F' ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Birthday</label>
                        <input type="date" name="birthday" class="form-control"
                               value="<?= htmlspecialchars($this->input->post('birthday') ?? '') ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Contact No.</label>
                        <input type="text" name="contact_no" class="form-control" maxlength="20"
                               value="<?= htmlspecialchars($this->input->post('contact_no') ?? '') ?>">
                    </div>
                    <div class="form-group col-md-6">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control"
                               value="<?= htmlspecialchars($this->input->post('email') ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Academic Details -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header font-weight-bold">
                <i class="fa fa-graduation-cap mr-2"></i>Academic Details
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Course <span class="text-danger">*</span></label>
                        <input type="text" name="course" class="form-control" required maxlength="12"
                               placeholder="e.g. BSCS"
                               value="<?= htmlspecialchars($this->input->post('course') ?? '') ?>">
                    </div>
                    <div class="form-group col-md-2">
                        <label>Year Level <span class="text-danger">*</span></label>
                        <select name="current_year" class="form-control" required>
                            <option value="">--</option>
                            <?php for ($y = 1; $y <= 4; $y++): ?>
                                <option value="<?= $y ?>" <?= (int)$this->input->post('current_year') === $y ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label>Year &amp; Section</label>
                        <input type="text" name="year_section" class="form-control" maxlength="10"
                               placeholder="e.g. 2A"
                               value="<?= htmlspecialchars($this->input->post('year_section') ?? '') ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label>School Year</label>
                        <input type="text" name="SY" class="form-control" maxlength="20"
                               placeholder="e.g. 2025-2026"
                               value="<?= htmlspecialchars($this->input->post('SY') ?? ($active_semester['semyear'] ? ($active_semester['semyear'] . '-' . ($active_semester['semyear'] + 1)) : '')) ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Credentials -->
        <div class="card mb-3 shadow-sm">
            <div class="card-header font-weight-bold">
                <i class="fa fa-lock mr-2"></i>Account Credentials
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" id="username" class="form-control"
                               required maxlength="50"
                               value="<?= htmlspecialchars($this->input->post('username') ?? '') ?>">
                        <small id="username_feedback" class="form-text"></small>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Confirm Password <span class="text-danger">*</span></label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                        <small id="pw_feedback" class="form-text"></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Assignment -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header font-weight-bold">
                <i class="fa fa-chalkboard mr-2"></i>Section Assignment
                <small class="text-muted font-weight-normal ml-2">(optional — can be done later)</small>
            </div>
            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-8">
                        <label>Assign to Class / Section</label>
                        <select name="schedule_id" class="form-control">
                            <option value="">-- No assignment yet --</option>
                            <?php foreach ($schedules as $sched): ?>
                                <option value="<?= $sched['schedule_id'] ?>"
                                    <?= (int)$this->input->post('schedule_id') === (int)$sched['schedule_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sched['class_code'] . ' — ' . $sched['class_name'] . ' [' . $sched['section'] . '] ' . $sched['type']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($active_semester): ?>
                            <small class="text-muted">Active semester: <?= htmlspecialchars($active_semester['description']) ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col">
                <button type="submit" class="btn btn-primary px-4" id="submit-btn">
                    <i class="fa fa-user-plus mr-2"></i>Register Student
                </button>
                <a href="<?= base_url('admin/students_by_section') ?>" class="btn btn-secondary ml-2">Cancel</a>
            </div>
        </div>

    </form>
</div>

<script>
(function () {
    var debounceTimer;

    function debounce(fn, delay) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(fn, delay);
    }

    // Live student_no check
    document.getElementById('student_no').addEventListener('input', function () {
        var val = this.value.trim();
        var fb  = document.getElementById('student_no_feedback');
        if (!val) { fb.textContent = ''; return; }
        debounce(function () {
            fetch('<?= base_url('admin/check_student_no') ?>?student_no=' + encodeURIComponent(val))
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (d.exists) {
                        fb.textContent = 'This student number is already registered.';
                        fb.className = 'form-text text-danger';
                    } else {
                        fb.textContent = 'Available.';
                        fb.className = 'form-text text-success';
                    }
                });
        }, 400);
    });

    // Live username check
    document.getElementById('username').addEventListener('input', function () {
        var val = this.value.trim();
        var fb  = document.getElementById('username_feedback');
        if (!val) { fb.textContent = ''; return; }
        debounce(function () {
            fetch('<?= base_url('admin/check_username') ?>?username=' + encodeURIComponent(val))
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    if (d.exists) {
                        fb.textContent = 'Username already taken.';
                        fb.className = 'form-text text-danger';
                    } else {
                        fb.textContent = 'Username available.';
                        fb.className = 'form-text text-success';
                    }
                });
        }, 400);
    });

    // Password match feedback
    function checkPasswords() {
        var pw  = document.getElementById('password').value;
        var cpw = document.getElementById('confirm_password').value;
        var fb  = document.getElementById('pw_feedback');
        if (!cpw) { fb.textContent = ''; return; }
        if (pw === cpw) {
            fb.textContent = 'Passwords match.';
            fb.className = 'form-text text-success';
        } else {
            fb.textContent = 'Passwords do not match.';
            fb.className = 'form-text text-danger';
        }
    }
    document.getElementById('password').addEventListener('input', checkPasswords);
    document.getElementById('confirm_password').addEventListener('input', checkPasswords);

    // Block submit if duplicates detected
    document.getElementById('reg-form').addEventListener('submit', function (e) {
        var snFb = document.getElementById('student_no_feedback');
        var unFb = document.getElementById('username_feedback');
        var pwFb = document.getElementById('pw_feedback');

        if (snFb.classList.contains('text-danger') ||
            unFb.classList.contains('text-danger') ||
            pwFb.classList.contains('text-danger')) {
            e.preventDefault();
            alert('Please fix the highlighted errors before submitting.');
        }
    });
})();
</script>

<?php $this->load->view('footer'); ?>

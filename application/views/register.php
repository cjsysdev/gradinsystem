<?php $this->load->view('header'); ?>

<style>
  :root {
    --primary-color: #28a745;
    --bg-subtle: #f8f9fa;
  }

  body {
    background-color: var(--bg-subtle);
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  }

  .reg-wrapper {
    min-height: 100vh;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 2.5rem 1rem 3rem;
  }

  .reg-card {
    background: #ffffff;
    border: none;
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.07);
    width: 100%;
    max-width: 720px;
    padding: 2.5rem;
  }

  .section-label {
    font-size: .75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: #6c757d;
    border-bottom: 1px solid #e9ecef;
    padding-bottom: .4rem;
    margin-bottom: 1.2rem;
  }

  .form-control {
    border-radius: 8px;
    padding: 10px 14px;
    border: 1px solid #e0e0e0;
    transition: all .2s ease;
    font-size: .9rem;
  }

  .form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(40, 167, 69, .1);
  }

  label {
    font-size: .82rem;
    font-weight: 600;
    color: #495057;
  }

  .btn-register {
    background-color: var(--primary-color);
    border: none;
    border-radius: 8px;
    padding: 11px 28px;
    font-weight: 600;
    letter-spacing: .4px;
    transition: transform .1s ease;
  }

  .btn-register:active { transform: scale(.98); }

  .helper-links { font-size: .875rem; }
  .helper-links a { color: #6c757d; text-decoration: none; }
  .helper-links a:hover { color: var(--primary-color); text-decoration: underline; text-underline-offset: 3px; }

  .feedback-ok   { color: #28a745; font-size: .78rem; margin-top: .2rem; }
  .feedback-err  { color: #dc3545; font-size: .78rem; margin-top: .2rem; }
</style>

<div class="container reg-wrapper">
  <div class="reg-card">

    <!-- Branding -->
    <div class="text-center mb-3">
      <img src="<?= base_url('assets/cmc_logo_no_bg.png') ?>" alt="CMC" style="width:52px;">
    </div>
    <h4 class="text-center fw-bold mb-1">Student Registration</h4>
    <p class="text-muted text-center mb-4 small">Fill in your details to create an account</p>

    <!-- Flash messages -->
    <?php if ($this->session->flashdata('success')): ?>
      <div class="alert alert-success border-0 small py-2">
        <?= htmlspecialchars($this->session->flashdata('success')) ?>
        <a href="<?= base_url('login') ?>" class="ml-2 font-weight-bold">Sign in &rarr;</a>
      </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
      <div class="alert alert-danger border-0 small py-2">
        <?= htmlspecialchars($this->session->flashdata('error')) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="<?= base_url('register') ?>" id="reg-form">

      <!-- Personal Information -->
      <div class="section-label mt-2">Personal Information</div>
      <div class="form-row">
        <div class="form-group col-sm-6">
          <label>Last Name <span class="text-danger">*</span></label>
          <input type="text" name="lastname" class="form-control" required maxlength="35"
                 value="<?= htmlspecialchars($this->input->post('lastname') ?? '') ?>">
        </div>
        <div class="form-group col-sm-6">
          <label>First Name <span class="text-danger">*</span></label>
          <input type="text" name="firstname" class="form-control" required maxlength="35"
                 value="<?= htmlspecialchars($this->input->post('firstname') ?? '') ?>">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-sm-4">
          <label>Middle Name</label>
          <input type="text" name="middlename" class="form-control" maxlength="35"
                 value="<?= htmlspecialchars($this->input->post('middlename') ?? '') ?>">
        </div>
        <div class="form-group col-sm-2">
          <label>Ext.</label>
          <input type="text" name="extname" class="form-control" maxlength="10"
                 placeholder="Jr., III"
                 value="<?= htmlspecialchars($this->input->post('extname') ?? '') ?>">
        </div>
        <div class="form-group col-sm-3">
          <label>Gender</label>
          <select name="gender" class="form-control">
            <option value="">--</option>
            <option value="M" <?= $this->input->post('gender') === 'M' ? 'selected' : '' ?>>Male</option>
            <option value="F" <?= $this->input->post('gender') === 'F' ? 'selected' : '' ?>>Female</option>
          </select>
        </div>
        <div class="form-group col-sm-3">
          <label>Birthday</label>
          <input type="date" name="birthday" class="form-control"
                 value="<?= htmlspecialchars($this->input->post('birthday') ?? '') ?>">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group col-sm-6">
          <label>Contact No.</label>
          <input type="text" name="contact_no" class="form-control" maxlength="20"
                 value="<?= htmlspecialchars($this->input->post('contact_no') ?? '') ?>">
        </div>
        <div class="form-group col-sm-6">
          <label>Email</label>
          <input type="email" name="email" class="form-control"
                 value="<?= htmlspecialchars($this->input->post('email') ?? '') ?>">
        </div>
      </div>

      <!-- Academic Details -->
      <div class="section-label mt-3">Academic Details</div>
      <div class="form-row">
        <div class="form-group col-sm-3">
          <label>Course <span class="text-danger">*</span></label>
          <input type="text" name="course" class="form-control" required maxlength="12"
                 placeholder="e.g. BSCS"
                 value="<?= htmlspecialchars($this->input->post('course') ?? '') ?>">
        </div>
        <div class="form-group col-sm-2">
          <label>Year <span class="text-danger">*</span></label>
          <select name="current_year" class="form-control" required>
            <option value="">--</option>
            <?php for ($y = 1; $y <= 4; $y++): ?>
              <option value="<?= $y ?>" <?= (int)$this->input->post('current_year') === $y ? 'selected' : '' ?>><?= $y ?></option>
            <?php endfor; ?>
          </select>
        </div>
        <div class="form-group col-sm-3">
          <label>Year &amp; Section</label>
          <input type="text" name="year_section" class="form-control" maxlength="10"
                 placeholder="e.g. 2A"
                 value="<?= htmlspecialchars($this->input->post('year_section') ?? '') ?>">
        </div>
        <div class="form-group col-sm-4">
          <label>School Year</label>
          <?php
            $sy_default = '';
            if (!empty($active_semester['semyear'])) {
                $sy_default = $active_semester['semyear'] . '-' . ($active_semester['semyear'] + 1);
            }
          ?>
          <input type="text" name="SY" class="form-control" maxlength="20"
                 placeholder="e.g. 2025-2026"
                 value="<?= htmlspecialchars($this->input->post('SY') ?? $sy_default) ?>">
        </div>
      </div>

      <!-- Section Assignment -->
      <?php if (!empty($schedules)): ?>
      <div class="section-label mt-3">Section Assignment <span class="text-muted font-weight-normal text-lowercase">(optional)</span></div>
      <div class="form-row">
        <div class="form-group col-sm-10">
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
            <small class="text-muted"><?= htmlspecialchars($active_semester['description']) ?></small>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Account Credentials -->
      <div class="section-label mt-3">Account Credentials</div>
      <div class="form-row">
        <div class="form-group col-sm-4">
          <label>Username <span class="text-danger">*</span></label>
          <input type="text" name="username" id="username" class="form-control"
                 required maxlength="50"
                 value="<?= htmlspecialchars($this->input->post('username') ?? '') ?>">
          <div id="username_fb"></div>
        </div>
        <div class="form-group col-sm-4">
          <label>Password <span class="text-danger">*</span></label>
          <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <div class="form-group col-sm-4">
          <label>Confirm Password <span class="text-danger">*</span></label>
          <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
          <div id="pw_fb"></div>
        </div>
      </div>

      <!-- Submit -->
      <div class="mt-4">
        <button type="submit" class="btn btn-success btn-register w-100 shadow-sm" id="submit-btn">
          Create Account
        </button>
      </div>

    </form>

    <div class="mt-4 pt-3 border-top text-center helper-links">
      <span class="text-muted">Already have an account?</span>
      <a href="<?= base_url('login') ?>" class="ml-1 text-primary font-weight-medium">Sign in</a>
    </div>

  </div>
</div>

<script>
(function () {
  var timer;
  function debounce(fn) { clearTimeout(timer); timer = setTimeout(fn, 420); }

  function setFb(el, msg, ok) {
    el.textContent = msg;
    el.className = ok ? 'feedback-ok' : 'feedback-err';
  }

  document.getElementById('username').addEventListener('input', function () {
    var val = this.value.trim();
    var fb = document.getElementById('username_fb');
    if (!val) { fb.textContent = ''; return; }
    debounce(function () {
      fetch('<?= base_url('check_username_public') ?>?username=' + encodeURIComponent(val))
        .then(function (r) { return r.json(); })
        .then(function (d) {
          d.exists ? setFb(fb, 'Username taken.', false)
                   : setFb(fb, 'Available.', true);
        });
    });
  });

  function checkPw() {
    var pw  = document.getElementById('password').value;
    var cpw = document.getElementById('confirm_password').value;
    var fb  = document.getElementById('pw_fb');
    if (!cpw) { fb.textContent = ''; return; }
    pw === cpw ? setFb(fb, 'Passwords match.', true)
               : setFb(fb, 'Passwords do not match.', false);
  }
  document.getElementById('password').addEventListener('input', checkPw);
  document.getElementById('confirm_password').addEventListener('input', checkPw);

  document.getElementById('reg-form').addEventListener('submit', function (e) {
    var haserr = document.querySelector('.feedback-err');
    if (haserr && haserr.textContent) {
      e.preventDefault();
      haserr.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  });
})();
</script>

<?php $this->load->view('footer'); ?>

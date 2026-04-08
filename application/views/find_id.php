<?php $this->load->view('header') ?>

<style>
  :root {
    --primary-color: #28a745;
    /* Professional Green */
    --bg-subtle: #f8f9fa;
  }

  body {
    background-color: var(--bg-subtle);
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  }

  .login-wrapper {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .login-card {
    background: #ffffff;
    border: none;
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
    width: 100%;
    max-width: 400px;
    padding: 2.5rem;
  }

  .lms-logo {
    font-weight: 700;
    color: var(--primary-color);
    font-size: 1.5rem;
    text-decoration: none;
    display: block;
    text-align: center;
    margin-bottom: 1.5rem;
  }

  .form-control {
    border-radius: 8px;
    padding: 12px 15px;
    border: 1px solid #e0e0e0;
    transition: all 0.2s ease;
  }

  .form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
  }

  .btn-search {
    /* background-color: var(--primary-color); */
    border: none;
    border-radius: 8px;
    padding: 12px;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: transform 0.1s ease;
  }

  .btn-search:active {
    transform: scale(0.98);
  }

  .helper-links {
    font-size: 0.875rem;
  }

  .helper-links a {
    color: #6c757d;
    text-decoration: none;
  }

  .helper-links a:hover {
    color: var(--primary-color);
    text-underline-offset: 4px;
    text-decoration: underline;
  }
</style>

<div class="container login-wrapper mt-5">
  <div class="login-card">
    <div class="row justify-content-center mb-3">
      <a href="<?= base_url("update_account_form") ?>"><img src="<?= base_url("assets/cmc_logo_no_bg.png") ?>" alt="Profile Picture" style="length: 3rem; width: 3rem;" /></a>
    </div>

    <h4 class="text-center fw-bold mb-1">Find Your ID</h4>
    <p class="text-muted text-center mb-4 small">Enter your name to retrieve your account ID</p>

    <?php if ($this->session->flashdata('error')) : ?>
      <div class="alert alert-danger border-0 small py-2" role="alert">
        <i class="bi bi-exclamation-circle me-2"></i>
        <?= $this->session->flashdata('error'); ?>
      </div>
    <?php elseif ($this->session->flashdata('success')) : ?>
      <div class="alert alert-success border-0 small py-2" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        <?= $this->session->flashdata('success'); ?>
      </div>
    <?php endif; ?>

    <form action="<?= base_url('get_id') ?>" method="POST">
      <div class="mb-3">
        <label class="form-label small fw-semibold text-secondary">First Name</label>
        <input type="text" class="form-control" name="firstname" placeholder="Enter your first name" required>
      </div>

      <div class="mb-4">
        <label class="form-label small fw-semibold text-secondary">Last Name</label>
        <input type="text" class="form-control" name="lastname" placeholder="Enter your last name" required>
      </div>

      <button type="submit" class="btn btn-primary btn-search w-100 shadow-sm">Search ID</button>
    </form>

    <div class="mt-4 pt-3 border-top text-center helper-links">
      <span class="text-muted">Remembered your credentials?</span><br>
      <a href="<?= base_url() ?>" class="fw-medium text-success">Back to Login</a>
    </div>
  </div>
</div>

<?php $this->load->view('footer') ?>
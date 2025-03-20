<?php $this->load->view('header') ?>

<div class="container mt-5">
  <div class="login-form">
    <div class="form-group row">
      <a href="/gradingsystem" class="btn btn-outline-secondary col m-2">Login</a>
      <!-- <a href="signup" class="btn btn-outline-secondary col m-2">Register</a> -->
      <a href="find_id" class="btn btn-outline-success col m-2">Find ID</a>
    </div>
    <form action="login" method="POST">
      <h4 class="text-center mb-4">Login</h4>
      <?php if ($this->session->flashdata('error')) : ?>
        <div class="alert alert-danger">
          <?= $this->session->flashdata('error'); ?>
        </div>
      <?php endif; ?>
      <div class="form-group">
        <input type="text" class="form-control" placeholder="Username" name="username" required>
      </div>
      <div class="form-group">
        <input type="password" class="form-control" placeholder="Password" name="password" required>
      </div>
      <div class="form-group">
        <button type="submit" class="btn btn-success btn-block">Login</button>
      </div>
    </form>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    fetch('<?= site_url('quiz/check_session') ?>', {
        method: 'GET'
      })
      .then(response => response.json())
      .then(data => {
        if (!data.logged_in) {
          console.log('User not logged in, clearing localStorage');
          clearLocalStorage();
        }
      })
      .catch(error => console.error('Session check failed:', error));
  });

  // Define clearLocalStorage function
  function clearLocalStorage() {
    // Assuming 10 questions max; adjust if needed
    for (let i = 0; i < 50; i++) {
      localStorage.removeItem(`quiz_answer_${i}`);
    }
    // Optionally clear all localStorage if other keys exist
    localStorage.clear();
  }
</script>

<?php $this->load->view('footer') ?>
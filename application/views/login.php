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

<?php $this->load->view('footer') ?>
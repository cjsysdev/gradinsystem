<?php $this->load->view('header') ?>

<div class="container">
  <div class="login-form">
    <div class="form-group row">
      <a href="/gradingsystem" class="btn btn-outline-secondary col m-2">Login</a>
      <a href="signup" class="btn btn-outline-secondary col m-2">Register</a>
    </div>
    <form action="signup_submit" method="POST">
      <h4 class="text-center mb-4">Sign Up</h4>
      <?php if ($this->session->flashdata('error')) : ?>
        <div class="alert alert-danger">
          <?php echo $this->session->flashdata('error'); ?>
        </div>
      <?php elseif ($this->session->flashdata('success')) : ?>
        <div class="alert alert-success">
          <?php echo $this->session->flashdata('success'); ?>
        </div>
      <?php endif; ?>
      <div class="form-group">
        <input type="text" class="form-control" name="username" placeholder="Username" required>
      </div>
      <div class="form-group">
        <input type="password" class="form-control" name="password" placeholder="Password" required>
      </div>
      <div class="form-group">
        <input type="text" class="form-control" placeholder="First Name" name="firstname" required>
      </div>
      <div class="form-group">
        <input type="text" class="form-control" placeholder="Last Name" name="lastname" required>
      </div>
      <div class="form-group">
        <select class="form-control" name="gender" required>
          <option selected disabled>Gender</option>
          <option value="M">Male</option>
          <option value="F">Female</option>
        </select>
      </div>
      <div class="form-group">
        <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
      </div>
    </form>
  </div>
</div>

<?php $this->load->view('footer') ?>
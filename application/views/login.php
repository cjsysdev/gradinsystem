<?php $this->load->view('header') ?>

<div class="container">
  <div class="login-form">
    <div class="form-group row">
      <a href="/gradingsystem" class="btn btn-outline-secondary col m-2">Login</a>
      <a href="signup" class="btn btn-outline-secondary col m-2">Register</a>
    </div>
    <form>
      <h4 class="text-center mb-4">Login</h4>
      <div class="form-group">
        <input type="text" class="form-control" placeholder="Username" required>
      </div>
      <div class="form-group">
        <input type="password" class="form-control" placeholder="Password" required>
      </div>
      <div class="form-group">
        <button type="submit" class="btn btn-success btn-block">Login</button>
      </div>
    </form>
  </div>
</div>

<?php $this->load->view('footer') ?>
<?php $this->load->view('header') ?>

<div class="container">
  <div class="login-form">
    <div class="form-group row">
      <a href="/gradingsystem" class="btn btn-outline-secondary col m-2">Login</a>
      <a href="signup" class="btn btn-outline-secondary col m-2">Register</a>
    </div>
    <form action="signup_submit" method="POST">
      <h4 class="text-center mb-4">Sign Up</h4>
      <div class="form-group">
        <input type="text" class="form-control" name="username" placeholder="Username" required>
      </div>
      <div class="form-group">
        <input type="password" class="form-control" name="password" placeholder="Password" required>
      </div>
      <div class="form-group">
        <input type="password" class="form-control" name="confirm" placeholder="Confirm Password" required>
      </div>
      <div class="form-group">
        <input type="text" class="form-control" placeholder="First Name" name="firstname" required>
      </div>
      <div class="form-group">
        <input type="text" class="form-control" placeholder="Last Name" name="lastname" required>
      </div>
      <div class="form-group">
        <input type="number" class="form-control" min="1" onKeyPress="if(this.value.length==11) return false" placeholder="Phone Number" />
      </div>
      <div class="form-group">
        <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
      </div>
    </form>
  </div>
</div>

<?php $this->load->view('footer') ?>
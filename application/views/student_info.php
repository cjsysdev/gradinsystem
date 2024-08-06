<?php $this->load->view('header') ?>

<div class="container">
  <div class="login-form">
    <div class="form-group row">
      <a href="/gradingsystem" class="btn btn-outline-secondary col m-2">Login</a>
      <a href="signup" class="btn btn-outline-secondary col m-2">Register</a>
      <a href="student_info" class="btn btn-outline-secondary col m-2">Info</a>
    </div>
    <form>
      <h4 class="text-center">Student Info</h4>
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
        <button type="submit" class="btn btn-primary btn-block">Submit</button>
      </div>
    </form>
  </div>
</div>

<?php $this->load->view('footer') ?>
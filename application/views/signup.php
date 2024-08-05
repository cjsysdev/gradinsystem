<?php $this->load->view('header') ?>

  <div class="container">
    <div class="login-form">
      <form>
        <h4 class="text-center">Sign Up</h4>
        <div class="form-group">
          <input type="text" class="form-control" placeholder="Username" required>
        </div>
        <div class="form-group">
          <input type="password" class="form-control" placeholder="Password" required>
        </div>
        <div class="form-group">
          <input type="password" class="form-control" placeholder="Confirm Password" required>
        </div>
        <div class="form-group">
          <button type="submit" class="btn btn-primary btn-block">Sign Up</button>
        </div>
      </form>
    </div>
  </div>

  <?php $this->load->view('footer') ?>


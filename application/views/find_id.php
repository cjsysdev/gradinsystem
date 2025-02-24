<?php $this->load->view('header') ?>

<div class="container">
  <div class="login-form">
    <div class="form-group row">
      <a href="/gradingsystem" class="btn btn-outline-secondary col m-2">Login</a>
      <a href="find_id" class="btn btn-outline-secondary col m-2">Find ID</a>
    </div>
    <form action="get_id" method="POST">
      <h4 class="text-center mb-4">Find ID</h4>
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
        <input type="text" class="form-control" name="firstname" placeholder="Firstname" required>
      </div>
      <div class="form-group">
        <input type="text" class="form-control" name="lastname" placeholder="Lastname" required>
      </div>
      <div class="form-group">
        <button type="submit" class="btn btn-primary btn-block">Search</button>
      </div>
    </form>
  </div>
</div>

<?php $this->load->view('footer') ?>
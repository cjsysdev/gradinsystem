<?php $this->load->view('header') ?>

<div class="container">
  <div class="login-form">
    <form>
      <h4 class="text-center">Student Info</h4>
      <div class="form-group">
        <input type="text" class="form-control" placeholder="First Name" required>
      </div>
      <div class="form-group">
        <input type="text" class="form-control" placeholder="Last Name" required>
      </div>
      <div class="form-group">
        <input type="text" class="form-control" placeholder="E-mail">
      </div>
      <div class="form-group">
        <input type="number" class="form-control"  min="1" onKeyPress="if(this.value.length==11) return false" placeholder="Phone Number"/>
      </div>
      <div class="form-group">
        <button type="submit" class="btn btn-primary btn-block">Submit</button>
      </div>
    </form>
  </div>
</div>

<?php $this->load->view('footer') ?>
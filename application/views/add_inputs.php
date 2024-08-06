<?php $this->load->view('header') ?>

<div class="container">
  <div class="login-form">
    <form action="input_submit" method="POST">
      <h4 class="text-center">Input Details</h4>
      <div class="form-group pt-3">
        <select class="form-control" name="iotype_id" required>
          <option selected disabled>Input Type</option>
          <option value="1">Activity</option>
          <option value="2">Performance task</option>
          <option value="3">Major Exam</option>
          <option value="4">Quiz</option>
        </select>
      </div>
      <div class="form-group">
        <input type="text" class="form-control" placeholder="Description" name="description">
      </div>
      <div class="form-group">
        <input type="number" class="form-control" name="max_score" min="1" onKeyPress="if(this.value.length==11) return false" placeholder="Maximum Score" required/>
      </div>
      <div class="form-group">
        <input type="text" class="form-control" placeholder="Description" value="active" name="status" hidden>
      </div>
      <div class="form-group">
        <button type="submit" class="btn btn-primary btn-block">Submit</button>
      </div>
    </form>
  </div>
</div>

<?php $this->load->view('footer') ?>
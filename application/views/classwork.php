<?php $this->load->view('header') ?>
<div class="container">
  <div class="dashboard">
    <?php $this->load->view('profile_info') ?>
    <div class="row justify-content-center">
      <div class="col-md-8">
        <?php if ($this->session->flashdata('success')) : ?>
          <div class="alert alert-success">
            <?= $this->session->flashdata('success'); ?>
          </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('warning')) : ?>
          <div class="alert alert-warning">
            <?= $this->session->flashdata('warning'); ?>
          </div>
        <?php endif; ?>

        <?php foreach ($assessments as $row) : ?>
          <div class="card mb-3 shadow-sm">
            <div class="card-body">
              <h3 class="card-title mb-1"><?= $row['title'] ?></h3>
              <p class="card-text mb-1" style="font-size: small;"> <span class="text-secondary"><?= convert_datetime_string($row['due']) ?> • </span> <span class="text-danger">Missing
                  <hr>
                  <p class="card-text mb-3"><?= $row['description'] ?></p>
                  <a href="<?= base_url('assessment/' . $row['assessment_id']) ?>" class="btn btn-info">Submit</a>
            </div>
          </div>
        <?php endforeach; ?>

        <?php foreach ($submitted as $row) : ?>
          <div class="card mb-3 shadow-sm">
            <div class="card-body">
              <h3 class="card-title mb-1"><?= $row['title'] ?></h3>
              <p class="card-text mb-1" style="font-size: small;"> <span class="text-secondary"><?= convert_datetime_string($row['due'])  ?> • </span> <span class="text-success">Submitted
                  <hr>
                  <p class="card-text mb-3"><?= $row['description'] ?></p>
                  <a class="btn btn-outline-info">View</a>
            </div>
          </div>
        <?php endforeach; ?>

      </div>
    </div>
  </div>
</div>
</div>
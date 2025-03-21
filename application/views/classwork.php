<?php $this->load->view('header') ?>

<div class="container">
  <div class="dashboard">
    <?php $this->load->view('profile_info') ?>
    <div class="row justify-content-center">
      <div class="col">
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
                  <a href="<?= base_url('assessment/' . $row['assessment_id']) ?>" class="btn btn-info btn-block">
                    <?= ($row['iotype_id'] == 3) ? "Start Exam" : "Create" ?>
                  </a>
            </div>
          </div>
        <?php endforeach; ?>
        <?php if (!$this->session->exam_term): ?>
          <?php foreach ($submitted as $row) : ?>
            <div class="card mb-3 shadow-sm">
              <div class="card-body">
                <h3 class="card-title mb-1"><?= $row['title'] ?></h3>
                <p class="card-text mb-1" style="font-size: small;"> <span class="text-secondary"><?= convert_datetime_string($row['due'])  ?> • </span> <span class="text-success">Submitted
                    <hr>
                    <p class="card-text mb-3 text-truncate"><?= $row['description'] ?></p>
                    <a href="<?= base_url('student_submission/' . $row['classwork_id']) ?>" class="btn btn-outline-info btn-block">View</a>
                    <!-- <a type="button" class="btn btn-outline-link">Score</a> -->
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>

      </div>
    </div>
  </div>
</div>
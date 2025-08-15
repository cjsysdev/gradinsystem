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

        <!-- Dropdown to choose between Assessments and Submitted -->
        <div class="mb-4">
          <div class="dropdown">
            <button class="btn btn-secondary btn-block dropdown-toggle w-100" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              Filter: All
            </button>
            <ul class="dropdown-menu w-100 shadow-sm" aria-labelledby="filterDropdown">
              <li><a class="dropdown-item filter-option" href="#" data-filter="all">All</a></li>
              <li><a class="dropdown-item filter-option" href="#" data-filter="assessments">Missing</a></li>
              <li><a class="dropdown-item filter-option" href="#" data-filter="submitted">Submitted</a></li>
            </ul>
          </div>
        </div>

        <!-- Assessments and Submitted Cards -->
        <div id="cards-container">
          <?php foreach ($assessments as $row) : ?>
            <div class="card mb-4 assessment-card">
              <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                  <h4 class="card-title mb-1"><?= $row['title'] ?></h4>
                  <span class="badge badge-danger">Missing</span>
                </div>
                <p class="card-text mb-1" style="font-size: small;">
                  <span class="text-secondary"><?= convert_datetime_string($row['due']) ?> • <span><?= $row['type'] ?> • <?= $row['assessment_id'] ?></span>
                </p>
              </div>
              <div class="card-body">
                <p class="card-text mb-3 text-truncate"><?= $row['description'] ?></p>
                <a href="<?= base_url('assessment/' . $row['assessment_id']) ?>" class="btn btn-info btn-block">
                  <?= ($row['iotype_id'] == 3) ? "Start Exam" : "Create" ?>
                </a>
              </div>
            </div>
          <?php endforeach; ?>

          <?php if (!$this->session->exam_term): ?>
            <?php foreach ($submitted as $row) : ?>
              <div class="card mb-4 submitted-card">
                <div class="card-header">
                  <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-1"><?= $row['title'] ?></h4>
                    <span class="badge badge-success">Submitted</span>
                  </div>
                  <p class="card-text mb-1" style="font-size: small;">
                    <span class="text-secondary"><?= convert_datetime_string($row['due']) ?> • <span><?= $row['type'] ?> • <?= $row['assessment_id'] ?></span>
                  </p>
                </div>
                <div class="card-body">
                  <p class="card-text mb-3 text-truncate"><?= $row['description'] ?></p>
                  <a href="<?= base_url('student_submission/' . $row['classwork_id']) ?>" class="btn btn-outline-info btn-block">View</a>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  document.querySelectorAll('.filter-option').forEach(option => {
    option.addEventListener('click', function(e) {
      e.preventDefault();
      const filterValue = this.getAttribute('data-filter');
      const filterButton = document.getElementById('filterDropdown');
      const assessmentCards = document.querySelectorAll('.assessment-card');
      const submittedCards = document.querySelectorAll('.submitted-card');

      // Update the dropdown button text
      filterButton.textContent = `Filter: ${this.textContent}`;

      // Filter cards based on the selected option
      if (filterValue === 'all') {
        assessmentCards.forEach(card => card.style.display = 'block');
        submittedCards.forEach(card => card.style.display = 'block');
      } else if (filterValue === 'assessments') {
        assessmentCards.forEach(card => card.style.display = 'block');
        submittedCards.forEach(card => card.style.display = 'none');
      } else if (filterValue === 'submitted') {
        assessmentCards.forEach(card => card.style.display = 'none');
        submittedCards.forEach(card => card.style.display = 'block');
      }
    });
  });
</script>

<?php $this->load->view('footer') ?>
<?php $this->load->view('header') ?>

<style>
  .number-overlay {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-family: Arial, sans-serif;
    font-size: 120px;
    /* Increased font size */
    font-weight: bold;
    color: #333;
    z-index: 9999;
    /* Ensures it appears on top */
    background: rgba(255, 255, 255, 0.8);
    /* Semi-transparent background */
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    pointer-events: none;
    /* Allows clicks to pass through the number itself */
  }

  .overlay-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9998;
    background: rgba(0, 0, 0, 0.2);
    /* Slight gray overlay */
  }
</style>


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
                  <a href="<?= base_url('assessment/' . $row['assessment_id']) ?>" class="btn btn-info">Create</a>
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
                  <a href="<?= base_url('student_submission/' . $row['classwork_id']) ?>" class="btn btn-outline-info">View</a>
                  <!-- <a type="button" class="btn btn-outline-link">Score</a> -->
            </div>
          </div>
        <?php endforeach; ?>

      </div>
    </div>
  </div>
</div>


<script>
  function animateNumber(finalNumber, duration = 2000) {
    // Create overlay elements
    const wrapper = document.createElement('div');
    const numberDisplay = document.createElement('div');

    wrapper.className = 'overlay-wrapper';
    numberDisplay.className = 'number-overlay';
    numberDisplay.textContent = '0.00';

    wrapper.appendChild(numberDisplay);
    document.body.appendChild(wrapper);

    const startNumber = 0;
    const startTime = performance.now();

    function updateNumber(currentTime) {
      const elapsedTime = currentTime - startTime;
      const progress = Math.min(elapsedTime / duration, 1);
      const easedProgress = 1 - Math.pow(1 - progress, 4);
      const currentNumber = startNumber + (finalNumber - startNumber) * easedProgress;

      numberDisplay.textContent = currentNumber.toFixed(2);

      if (progress < 1) {
        requestAnimationFrame(updateNumber);
      } else {
        numberDisplay.textContent = finalNumber.toFixed(2);
      }
    }

    // Start animation
    requestAnimationFrame(updateNumber);

    // Remove overlay when clicking outside
    wrapper.addEventListener('click', function(e) {
      // Only remove if click is on the wrapper itself, not the number
      if (e.target === wrapper) {
        document.body.removeChild(wrapper);
      }
    });
  }

  // Start animation when page loads
  // window.onload = function() {
  //   animateNumber(<?= randomizeNumber(8.9, 10.0) ?>, 2500);
  // }
</script>

<?php $this->load->view('footer') ?>
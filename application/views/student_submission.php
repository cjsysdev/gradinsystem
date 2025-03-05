<?php $this->load->view('header') ?>
<style>
  /* Default font size */
  .CodeMirror {
    font-size: 14px;
  }

  /* Increase font size on smaller screens */
  @media (max-width: 600px) {
    .CodeMirror {
      font-size: 16px;
    }
  }

  /* Disable touch actions */
  .CodeMirror {
    touch-action: manipulation;
  }
</style>


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

    <div class="form-group">
      <textarea id="code-editor" name="code" style=" overflow: hidden;">
    <?= $classwork['code'] ?>
    </textarea>
      <?php if ($classwork['score'] !== NULL): ?>
        <button type="button" class="btn btn-info btn-block mt-3" onclick="view_score()">View score</button>
      <?php endif; ?>
    </div>

    <!-- CodeMirror JavaScript -->
    <script src="<?= base_url('./assets/codemirror.min.js ?>') ?> "></script>
    <script src="<?= base_url('./assets/clike.min.js') ?>"></script>

    <script>
      // Start animation when page loads
      function view_score() {
        animateNumber(<?= $classwork['score'] ?>, 2500);
      }
    </script>
  </div>
</div>

<?php $this->load->view('footer');
?>
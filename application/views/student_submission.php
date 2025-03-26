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

  .correct {
    color: green;
  }

  .incorrect {
    color: red;
  }

  .btn-block {
    width: 100%;
    padding: 15px;
    font-size: 18px;
  }

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
    <?php if ($classwork['assessments'][0]->iotype_id == 3): ?>
      <div class="card-body">
        <?php foreach (json_decode($classwork['code'], true) as $index => $result): ?>
          <div class="mb-4">
            <p class="fw-bold"><b>Question <?= $index + 1 ?>: </b><?= $result['question'] ?></p>
            <p>Your answer: <span class="<?= $result['is_correct'] ? 'correct' : 'incorrect' ?>"><?= $result['user_answer'] ?></span></p>
            <p>Correct answer: <?= $result['correct_answer'] ?></p>
          </div>
          <hr>
        <?php endforeach; ?>
        <div class="text-center">
          <a href="<?= site_url('attendance') ?>" class="btn btn-outline-dark btn-block">Exit</a>
        </div>
      </div>
    <?php else: ?>
      <pre><code id="highlightedCode" class="language-c"><?= $classwork['code'] ?></code></pre>
    <?php endif; ?>

    <?php if ($classwork['score'] !== NULL): ?>
      <!-- <button type="button" class="btn btn-info btn-block mt-3" onclick="view_score()">View score</button> -->
    <?php endif; ?>
  </div>

  <script src="<?= base_url('assets/highlights/11.7.0-highlight.min.js') ?>"></script>

  <script>
    hljs.highlightAll();
  </script>

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
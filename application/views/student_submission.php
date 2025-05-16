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

  /* Custom scrollbar styling for highlightedCode */
  #highlightedCode::-webkit-scrollbar {
    width: 12px;
  }

  #highlightedCode::-webkit-scrollbar-track {
    background: #f1f1f1;
  }

  #highlightedCode::-webkit-scrollbar-thumb {
    background: #888;
  }

  #highlightedCode::-webkit-scrollbar-thumb:hover {
    background: #555;
  }

  .submission-container {
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    background-color: #f9f9f9;
    margin-top: 20px;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
  }

  .submission-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
  }

  .submission-header h5 {
    margin: 0;
    font-size: 18px;
    font-weight: bold;
  }

  .submission-status {
    font-size: 14px;
    font-weight: bold;
    color: <?= $classwork['status'] === 'submitted' ? 'green' : 'red' ?>;
  }

  .submission-body {
    margin-top: 10px;
    text-align: center;
  }

  .file-preview {
    display: block;
    margin: 10px auto;
    max-width: 100%;
    height: auto;
  }

  .file-link {
    display: inline-block;
    margin-top: 10px;
    font-size: 14px;
    color: #1a73e8;
    text-decoration: none;
  }

  .file-link:hover {
    text-decoration: underline;
  }

  .submission-actions {
    margin-top: 20px;
    text-align: center;
  }

  .btn-unsubmit {
    background-color: #d93025;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
  }

  .btn-unsubmit:hover {
    background-color: #a61b1b;
  }

  .due-date {
    font-size: 12px;
    color: #666;
    margin-top: 10px;
    text-align: center;
  }

  pre {
    background-color: #f1f1f1;
    padding: 10px;
    border-radius: 4px;
    overflow-x: auto;
  }
</style>

<div class="container">

  <?php $this->load->view('profile_info') ?>

  <div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Your Work - <?= $classwork['classwork_id'] ?></h5>
      <span class="badge badge-success">
        <?php if ($classwork['assessments'][0]->max_score == 0 || true)
          echo 'Turned In';
        else
          echo $classwork['score'] . '/' . $classwork['assessments'][0]->max_score;
        ?>
      </span>
    </div>
    <div class="card-body text-center">
      <?php if ($classwork['file_upload']): ?>
        <!-- Display uploaded file -->
        <h6>Uploaded File:</h6>
        <a href="#" class="btn btn-outline-primary" onclick="previewFile('<?= base_url('uploads/classworks/' . $classwork['file_upload']) ?>')">
          <?= $classwork['file_upload'] ?>
        </a>
      <?php elseif ($classwork['assessments'][0]->iotype_id == 4): ?>
        <?php
        $assessment_id = $classwork['assessment_id'];
        $query = $this->db->query("
                    SELECT student_id, score 
                    FROM gradingsystem.classworks 
                    WHERE assessment_id = {$classwork['assessment_id']} 
                    ORDER BY score DESC 
                    LIMIT 10
                ");
        $top_students = $query->result_array();
        ?>

        <?php if (isset($classwork['code'])): ?>
          <?php foreach (json_decode($classwork['code'], true) as $index => $result): ?>
            <div class="mb-4 text-left">
              <p class="fw-bold"><b>Question <?= $index + 1 ?>: </b><?= nl2br(htmlspecialchars($result['question'])) ?></p>
              <p>Your answer: <span class="<?= $result['is_correct'] ? 'correct' : 'incorrect' ?>"><?= $result['user_answer'] ?></span></p>
              <p>Correct answer: <?= $result['correct_answer'] ?></p>
            </div>
            <hr>
          <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($top_students)): ?>
          <div class="mt-4">
            <h3 class="text-center">Top 10 Students</h3>
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>Rank</th>
                  <th>Student ID</th>
                  <th>Score</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($top_students as $index => $student): ?>
                  <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($student['student_id']) ?></td>
                    <td><?= htmlspecialchars($student['score']) ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      <?php elseif ($classwork['code']): ?>
        <!-- Display submitted code -->
        <h6>Submitted Code:</h6>
        <pre class="bg-light p-3 rounded border text-left"><code id="highlightedCode" class="language-c"><?= htmlspecialchars($classwork['code']) ?></code></pre>
      <?php else: ?>
        <p class="text-muted">No submission found.</p>
      <?php endif; ?>
    </div>
    <div class="card-footer text-center">
      <?php if (
        $classwork['status'] === 'submitted' &&
        $classwork['score'] <= 0 ||  $classwork['score'] == null
      ): ?>
        <button id="unsubmit" class="btn btn-outline-secondary btn-block" onclick="unsubmitWork(<?= $classwork['classwork_id'] ?>)">Unsubmit</button>
      <?php endif; ?>
      <p class="text-muted mt-3">Work cannot be turned in after the due date.</p>
    </div>
  </div>
</div>

<!-- Modal for File Preview -->
<div class="modal fade" id="filePreviewModal" tabindex="-1" aria-labelledby="filePreviewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="filePreviewModalLabel">File Preview</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <iframe id="filePreviewIframe" src="" frameborder="0" style="width: 100%; height: 600px;"></iframe>
      </div>
    </div>
  </div>
</div>

<script>
  function previewFile(fileUrl) {
    const iframe = document.getElementById('filePreviewIframe');
    iframe.src = fileUrl;
    const modal = new bootstrap.Modal(document.getElementById('filePreviewModal'));
    modal.show();
  }

  function unsubmitWork(classworkId) {
    if (confirm('Are you sure you want to unsubmit your work?')) {
      // Send an AJAX request to delete the classwork
      fetch('<?= base_url('ClassworkController/unsubmit_work') ?>', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: JSON.stringify({
            classwork_id: classworkId
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            alert('Your work has been unsubmitted.');
            window.location.href = '<?= base_url('classwork') ?>';
          } else {
            alert('Failed to unsubmit your work. Please try again.');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          alert('An error occurred. Please try again.');
        });
    }
  }

  hljs.highlightAll();
</script>

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

<?php $this->load->view('footer') ?>
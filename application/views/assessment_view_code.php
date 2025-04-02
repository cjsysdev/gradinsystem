<?php
if ($classwork['iotype_id'] == '4' || $classwork['iotype_id'] == '3') {
  redirect("quiz/" . $classwork['assessment_id']);
}
?>

<?php $this->load->view('header') ?>

<div class="container">
  <?php $this->load->view('profile_info') ?>

  <div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Your Work</h5>
      <span class="badge badge-success">Assigned</span>
    </div>
    <div class="card-body">
      <p><?= $classwork["description"] ?></p>

      <form id="submission-form" action="<?= base_url('AssessmentController/submit_classwork') ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="assessment_id" value="<?= $classwork['assessment_id'] ?>">
        <input type="hidden" name="student_id" value="<?= $this->session->student_id ?>">

        <!-- Option to choose between code input or file upload -->
        <div class="mb-3">
          <label for="submission-type" class="form-label">Choose Submission Type:</label>
          <select id="submission-type" class="form-select" onchange="toggleSubmissionType()">
            <option value="code">Input Code</option>
            <option value="file">Upload File</option>
          </select>
        </div>

        <!-- Code input section -->
        <div id="code-input-section" class="mb-3">
          <label for="code-editor" class="form-label">Enter your submission (Code/Text):</label>
          <textarea id="code-editor" name="code" class="form-control" rows="10" placeholder="Write your code or text here..."></textarea>
        </div>

        <!-- File upload section -->
        <div id="file-upload-section" class="mb-3" style="display: none;">
          <label for="file-upload" class="form-label">Upload a file:</label>
          <input type="file" id="file-upload" name="file-upload" class="form-control">
        </div>
        <div class="text-center">
          <button type="button" class="btn btn-primary btn-block" onclick="confirmSubmission()">Turn In</button>
        </div>
      </form>
    </div>
    <div class="card-footer text-center">
      <p class="text-muted">Work cannot be turned in after the due date.</p>
    </div>
  </div>
</div>

<!-- Modal for Submission Confirmation -->
<div class="modal fade" id="submissionModal" tabindex="-1" aria-labelledby="submissionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="submissionModalLabel">Confirm Submission</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Are you sure you want to submit your work? Please ensure that you have either entered code or uploaded a file.
      </div>
      <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
      <button type="button" class="btn btn-primary" onclick="submitForm()">Confirm</button>
      </div>
    </div>
  </div>
</div>

<script>
  function toggleSubmissionType() {
    const submissionType = document.getElementById('submission-type').value;
    const codeInputSection = document.getElementById('code-input-section');
    const fileUploadSection = document.getElementById('file-upload-section');

    if (submissionType === 'code') {
      codeInputSection.style.display = 'block';
      fileUploadSection.style.display = 'none';
    } else if (submissionType === 'file') {
      codeInputSection.style.display = 'none';
      fileUploadSection.style.display = 'block';
    }
  }

  function confirmSubmission() {
    const codeEditor = document.getElementById('code-editor').value.trim();
    const fileUpload = document.getElementById('file-upload').value;

    // if (!codeEditor || !fileUpload) {
    //   alert('Please enter code or upload a file before submitting.');
    //   return;
    // }

    const modal = new bootstrap.Modal(document.getElementById('submissionModal'));
    modal.show();
  }

  function submitForm() {
    document.getElementById('submission-form').submit();
  }
</script>

<?php $this->load->view('footer') ?>
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

<div class="container">
  <div class="dashboard">
    <?php $this->load->view('profile_info') ?>

    <div class="form-group">
      <form id="code" action="<?= base_url('submit_classwork') ?>" method="POST">
        <textarea id="code-editor" name="code" style=" overflow: hidden;"><?= $classwork['given'] ?></textarea>
        <input type="text" name="assessment_id" value="<?= $classwork['assessment_id'] ?>" hidden>
        <input type="text" name="student_id" value="<?= $this->session->student_id ?>" hidden>
        <button type="button" class="btn btn-info btn-block mt-3" data-toggle="modal" data-target="#confirmationModal">Save</button>
      </form>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="confirmationModalLabel">Confirm Save</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            Are you sure you want to save this code?
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="button" id="confirmSave" class="btn btn-info">Confirm</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap 4.5.2 JS (with Popper and jQuery) -->
  <script src="<?= base_url("/assets/2-jquery-3.5.1.slim.min.js") ?>"></script>
  <script src="<?= base_url("/assets/4.5.2.bootstrap.bundle.min") ?>"></script>

  <!-- JavaScript to Handle Form Submission -->
  <script>
    document.getElementById('confirmSave').addEventListener('click', function() {
      // Submit the form programmatically
      document.getElementById('code').submit();
    });
  </script>

  <!-- CodeMirror JavaScript -->
  <script src="<?= base_url('./assets/codemirror.min.js ?>') ?> "></script>
  <script src="<?= base_url('./assets/clike.min.js') ?>"></script>

  <script>
    // Initialize CodeMirror
    const editor = CodeMirror.fromTextArea(document.getElementById('code-editor'), {
      mode: 'text/x-csrc',
      lineNumbers: true,
      indentUnit: 4,
      matchBrackets: true,
      autoCloseBrackets: true,
    });
  </script>
</div>
</div>
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
      <form action="<?= base_url('submit_classwork') ?>" method="POST">
        <textarea id="code-editor" name="code" style=" overflow: hidden;"></textarea>
        <input type="text" name="assessment_id" value="<?= $classwork['assessment_id'] ?>" hidden>
        <input type="text" name="student_id" value="<?= $this->session->student_id ?>" hidden>
        <button type="submit" class="btn btn-info btn-block mt-3">Save</button>
      </form>
    </div>

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
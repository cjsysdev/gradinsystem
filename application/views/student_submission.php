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
      <textarea id="code-editor" name="code" style=" overflow: hidden;">
    <?= $classwork['code'] ?>
    </textarea>
      <!-- <button type="submit" class="btn btn-info btn-block mt-3">Back</button> -->
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
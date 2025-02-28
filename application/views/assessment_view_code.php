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
    <textarea id="code-editor" style=" overflow: hidden;"></textarea>

    <div class="form-group mt-2">
      <button type="submit" class="btn btn-info btn-block">Save</button>
    </div>

    <!-- CodeMirror JavaScript -->
    <script src="./assets/codemirror.min.js"></script>
    <script src="./assets/clike.min.js"></script>

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
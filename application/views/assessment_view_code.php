<?php $this->load->view('header') ?>

<div class="container">
  <div class="dashboard">
    <?php $this->load->view('profile_info') ?>
    <textarea id="code-editor" style=" overflow: hidden;"></textarea>

    <div class="form-group mt-2">
      <button type="submit" class="btn btn-outline-success btn-block">Save</button>
    </div>

    <!-- CodeMirror JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7/mode/clike/clike.min.js"></script>

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
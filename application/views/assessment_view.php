<?php $this->load->view('header') ?>

<style>
  textarea {
    width: 100%;
    min-height: 50px;
    font-family: monospace;
    font-size: 14px;
    padding: 10px;
    resize: none;
    overflow: hidden;
    border: none;
  }
</style>
<link rel="stylesheet" href="<? base_url('assets/highlights/atom-one-light.min.css') ?>">
<div class="container">
  <div class="dashboard">
    <?php $this->load->view('profile_info') ?>
    <textarea id="codeInput" placeholder="Enter your code here..." spellcheck="false"><?= $classwork['given'] ?></textarea>
    <pre><code id="highlightedCode" class="language-c"></code></pre>
    <div class="form-group">
      <button type="submit" class="btn btn-success btn-block">Submit</button>
    </div>
    <script src="<?= base_url('assets/highlights/11.7.0-highlight.min.js') ?>"></script>
    <script>
      function autoAdjustTextarea(textarea) {
        textarea.style.height = "auto"; // Reset height to auto
        textarea.style.height = textarea.scrollHeight + "px"; // Set height to scroll height
      }

      function updateHighlightedCode() {
        const codeInput = document.getElementById("codeInput").value;
        const highlightedCodeElement = document.getElementById("highlightedCode");

        highlightedCodeElement.textContent = codeInput;
        hljs.highlightElement(highlightedCodeElement);
      }

      const textarea = document.getElementById("codeInput");

      textarea.addEventListener("input", () => {
        autoAdjustTextarea(textarea); // Adjust textarea height
        updateHighlightedCode(); // Update highlighted code
      });

      textarea.value = `#include <stdio.h>
      int main() {
        return 0;
      }
      `;
      autoAdjustTextarea(textarea); // Adjust height initially
      updateHighlightedCode(); // Highlight the initial code
    </script>
  </div>
</div>
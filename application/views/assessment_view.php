<?php $this->load->view('header') ?>

<div class="container">
  <div class="dashboard">
    <?php $this->load->view('profile_info') ?>
    <pre><code id="highlightedCode" class="language-c">#include <stdio.h>
      int main() {
        return 0;
      }</code></pre>
    <div class="form-group">
      <button type="submit" class="btn btn-success btn-block">Submit</button>
    </div>
    <script src="<?= base_url('assets/highlights/11.7.0-highlight.min.js') ?>"></script>
  </div>
</div>


<script>
  hljs.highlightAll();
</script>
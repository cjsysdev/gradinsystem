<?php $this->load->view('header'); ?>
<style>
  .question-card { border: 1px solid #dee2e6; border-radius: 8px; padding: 16px; margin-bottom: 16px; background: #fff; }
  .option-row { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
  .option-row input { flex: 1; }
  .option-row .btn-remove-opt { flex-shrink: 0; }
  .drag-handle { cursor: grab; color: #aaa; margin-right: 8px; }
</style>

<div class="container mt-4" style="max-width:720px">
  <div class="d-flex align-items-center mb-4">
    <a href="<?= base_url('poll/dashboard') ?>" class="btn btn-sm btn-outline-secondary mr-3">
      <i class="fas fa-arrow-left"></i>
    </a>
    <h3 class="mb-0">Create New Poll</h3>
  </div>

  <?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($this->session->flashdata('error')) ?></div>
  <?php endif; ?>

  <form method="post" action="<?= base_url('poll/create') ?>" id="poll-form">
    <div class="form-group">
      <label><strong>Poll Title</strong></label>
      <input type="text" name="title" class="form-control form-control-lg"
             placeholder="e.g. Chapter 3 Review" required>
    </div>

    <div id="questions-container"></div>

    <button type="button" class="btn btn-outline-primary btn-block mb-3" id="btn-add-question">
      <i class="fas fa-plus"></i> Add Question
    </button>

    <button type="submit" class="btn btn-primary btn-lg btn-block">
      <i class="fas fa-save"></i> Save &amp; Open Presenter
    </button>
  </form>
</div>

<!-- Question template (hidden) -->
<template id="question-template">
  <div class="question-card" data-qi="">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <strong class="q-label">Question 1</strong>
      <button type="button" class="btn btn-sm btn-outline-danger btn-remove-question">
        <i class="fas fa-times"></i>
      </button>
    </div>
    <div class="form-group">
      <input type="text" class="form-control" name="" placeholder="Type your question…" required>
    </div>
    <div class="options-container"></div>
    <button type="button" class="btn btn-sm btn-outline-secondary btn-add-option">
      <i class="fas fa-plus"></i> Add Option
    </button>
  </div>
</template>

<?php $this->load->view('footer'); ?>
<script>
(function () {
  const container = document.getElementById('questions-container');
  const tmpl      = document.getElementById('question-template');
  let   qi        = 0;

  function makeOptionRow(qi, oi) {
    const div = document.createElement('div');
    div.className = 'option-row';
    div.innerHTML = `
      <span class="drag-handle"><i class="fas fa-grip-vertical"></i></span>
      <input type="text" class="form-control form-control-sm"
             name="questions[${qi}][options][]"
             placeholder="Option ${oi + 1}" required>
      <button type="button" class="btn btn-sm btn-outline-danger btn-remove-opt">
        <i class="fas fa-times"></i>
      </button>`;
    div.querySelector('.btn-remove-opt').addEventListener('click', () => div.remove());
    return div;
  }

  function addQuestion() {
    const node = tmpl.content.cloneNode(true);
    const card = node.querySelector('.question-card');
    card.dataset.qi = qi;
    card.querySelector('.q-label').textContent = 'Question ' + (qi + 1);
    card.querySelector('input[type=text]').name = `questions[${qi}][text]`;

    // Start with 4 blank options
    const opts = card.querySelector('.options-container');
    for (let i = 0; i < 4; i++) opts.appendChild(makeOptionRow(qi, i));

    card.querySelector('.btn-add-option').addEventListener('click', () => {
      const count = opts.querySelectorAll('.option-row').length;
      opts.appendChild(makeOptionRow(card.dataset.qi, count));
    });

    card.querySelector('.btn-remove-question').addEventListener('click', () => {
      card.remove();
      renumberQuestions();
    });

    container.appendChild(card);
    qi++;
    renumberQuestions();
  }

  function renumberQuestions() {
    container.querySelectorAll('.question-card').forEach((card, i) => {
      card.dataset.qi = i;
      card.querySelector('.q-label').textContent = 'Question ' + (i + 1);
      card.querySelector('input[type=text]').name = `questions[${i}][text]`;
      card.querySelectorAll('.option-row input').forEach(inp => {
        inp.name = `questions[${i}][options][]`;
      });
    });
    qi = container.querySelectorAll('.question-card').length;
  }

  document.getElementById('btn-add-question').addEventListener('click', addQuestion);

  // Start with one question
  addQuestion();
})();
</script>

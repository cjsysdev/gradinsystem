<?php $this->load->view('header'); ?>
<style>
  .question-card { border: 1px solid #dee2e6; border-radius: 8px; padding: 16px; margin-bottom: 16px; background: #fff; }
  .type-toggle { display: flex; gap: 0; margin-bottom: 12px; border-radius: 8px; overflow: hidden; border: 1px solid #dee2e6; }
  .type-toggle label { flex: 1; margin: 0; padding: 8px 12px; text-align: center; cursor: pointer; font-size: .85rem; font-weight: 600; transition: background .15s, color .15s; background: #f8f9fa; color: #555; }
  .type-toggle input[type=radio] { display: none; }
  .type-toggle input[type=radio]:checked + label { background: #0f3460; color: #fff; }
  .type-toggle label.oe-lbl { border-left: 1px solid #dee2e6; }
  .option-row { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; }
  .option-row input { flex: 1; }
  .oe-hint { color: #888; font-size: .85rem; padding: 10px 14px; background: #f4f6f9; border-radius: 8px; }
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

<!-- Question template -->
<template id="question-template">
  <div class="question-card" data-qi="">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <strong class="q-label">Question 1</strong>
      <button type="button" class="btn btn-sm btn-outline-danger btn-remove-question">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <!-- Type toggle -->
    <div class="type-toggle mb-3">
      <input type="radio" name="" value="multiple_choice" id="" checked>
      <label class="mc-lbl"><i class="fas fa-list-ul"></i> Multiple Choice</label>
      <input type="radio" name="" value="open_ended" id="">
      <label class="oe-lbl"><i class="fas fa-keyboard"></i> Open-Ended</label>
    </div>

    <div class="form-group">
      <input type="text" class="form-control q-text-input" name="" placeholder="Type your question…" required>
    </div>

    <!-- Multiple choice options panel -->
    <div class="mc-panel">
      <div class="options-container"></div>
      <button type="button" class="btn btn-sm btn-outline-secondary btn-add-option">
        <i class="fas fa-plus"></i> Add Option
      </button>
    </div>

    <!-- Open-ended hint (hidden by default) -->
    <div class="oe-panel" style="display:none">
      <div class="oe-hint">
        <i class="fas fa-keyboard"></i> Students will type a short text response (up to 100 characters).
        Responses appear as a live word cloud on the presenter screen.
      </div>
    </div>
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
    const idx  = qi;
    card.dataset.qi = idx;
    card.querySelector('.q-label').textContent = 'Question ' + (idx + 1);

    // Wire up type radios
    const radios    = card.querySelectorAll('input[type=radio]');
    const mcLbl     = card.querySelector('.mc-lbl');
    const oeLbl     = card.querySelector('.oe-lbl');
    const mcPanel   = card.querySelector('.mc-panel');
    const oePanel   = card.querySelector('.oe-panel');
    const qTextInput = card.querySelector('.q-text-input');

    radios[0].name = `questions[${idx}][type]`;
    radios[0].id   = `q${idx}_mc`;
    radios[1].name = `questions[${idx}][type]`;
    radios[1].id   = `q${idx}_oe`;
    mcLbl.setAttribute('for', `q${idx}_mc`);
    oeLbl.setAttribute('for', `q${idx}_oe`);
    qTextInput.name = `questions[${idx}][text]`;

    function switchType() {
      const isOE = radios[1].checked;
      mcPanel.style.display = isOE ? 'none' : '';
      oePanel.style.display = isOE ? '' : 'none';
      // Options are only required for MC
      card.querySelectorAll('.mc-panel input').forEach(i => i.required = !isOE);
    }

    radios[0].addEventListener('change', switchType);
    radios[1].addEventListener('change', switchType);

    // Default 4 options for MC
    const opts = card.querySelector('.options-container');
    for (let i = 0; i < 4; i++) opts.appendChild(makeOptionRow(idx, i));

    card.querySelector('.btn-add-option').addEventListener('click', () => {
      const count = opts.querySelectorAll('.option-row').length;
      opts.appendChild(makeOptionRow(card.dataset.qi, count));
    });

    card.querySelector('.btn-remove-question').addEventListener('click', () => {
      card.remove();
      renumber();
    });

    container.appendChild(card);
    qi++;
    renumber();
  }

  function renumber() {
    container.querySelectorAll('.question-card').forEach((card, i) => {
      card.dataset.qi = i;
      card.querySelector('.q-label').textContent = 'Question ' + (i + 1);

      const radios = card.querySelectorAll('input[type=radio]');
      radios[0].name = `questions[${i}][type]`;
      radios[0].id   = `q${i}_mc`;
      radios[1].name = `questions[${i}][type]`;
      radios[1].id   = `q${i}_oe`;
      card.querySelector('.mc-lbl').setAttribute('for', `q${i}_mc`);
      card.querySelector('.oe-lbl').setAttribute('for', `q${i}_oe`);
      card.querySelector('.q-text-input').name = `questions[${i}][text]`;
      card.querySelectorAll('.option-row input[type=text]').forEach(inp => {
        inp.name = `questions[${i}][options][]`;
      });
    });
    qi = container.querySelectorAll('.question-card').length;
  }

  document.getElementById('btn-add-question').addEventListener('click', addQuestion);
  addQuestion(); // start with one
})();
</script>

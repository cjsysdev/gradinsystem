<?php $this->load->view('header'); ?>
<link rel="stylesheet" href="<?= base_url('assets/fontawesome/css/all.min.css') ?>">
<style>
  body { background: #1a1a2e; color: #eee; }
  .presenter-wrap { max-width: 960px; margin: 0 auto; padding: 24px 16px; }
  .pin-badge { background: #16213e; border: 2px solid #0f3460; border-radius: 12px; padding: 10px 20px; display: inline-block; }
  .pin-badge .pin-code { font-size: 2.4rem; font-weight: 800; letter-spacing: 6px; color: #e94560; }
  .sidebar { background: #16213e; border-radius: 12px; padding: 16px; height: 100%; }
  .q-btn { width: 100%; text-align: left; border-radius: 8px; margin-bottom: 8px; font-weight: 600;
           background: #0f3460; border: none; color: #eee; padding: 10px 14px; cursor: pointer; transition: background .2s; }
  .q-btn:hover { background: #e94560; }
  .q-btn.active { background: #e94560; }
  .q-btn .type-badge { font-size: .65rem; font-weight: 700; text-transform: uppercase;
                       letter-spacing: 1px; background: rgba(255,255,255,.15); border-radius: 4px;
                       padding: 2px 6px; margin-left: 6px; vertical-align: middle; }
  .main-panel { background: #16213e; border-radius: 12px; padding: 24px; min-height: 420px;
                display: flex; flex-direction: column; align-items: center; justify-content: center; }
  .question-text { font-size: 1.6rem; font-weight: 700; text-align: center; margin-bottom: 24px; line-height: 1.4; }
  .chart-wrap { width: 100%; max-width: 640px; }
  .status-row { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; justify-content: center; }
  .vote-count { font-size: 1.1rem; color: #aaa; }
  .waiting-msg { color: #666; font-size: 1.1rem; text-align: center; }
  .controls { display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; margin-top: 16px; }

  /* Word cloud */
  #word-cloud-wrap { width: 100%; max-width: 640px; min-height: 240px; display: flex;
                     flex-wrap: wrap; gap: 12px 16px; align-items: center; justify-content: center;
                     padding: 16px; }
  .wc-word { border-radius: 6px; padding: 4px 12px; font-weight: 700; line-height: 1.3;
             transition: transform .3s; cursor: default; }
  .wc-word:hover { transform: scale(1.1); }
  #oe-list { width: 100%; max-width: 640px; max-height: 180px; overflow-y: auto;
             display: flex; flex-wrap: wrap; gap: 6px; justify-content: center; margin-top: 12px; }
  .oe-chip { background: rgba(255,255,255,.1); border-radius: 20px; padding: 4px 12px;
             font-size: .85rem; color: #ddd; }
  .q-type-label { font-size: .75rem; text-transform: uppercase; letter-spacing: 2px;
                  color: #888; margin-bottom: 8px; text-align: center; }
</style>

<div class="presenter-wrap">
  <!-- Header -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
      <h4 class="mb-1" style="color:#eee"><?= htmlspecialchars($poll['title']) ?></h4>
      <div class="pin-badge">
        PIN: <span class="pin-code"><?= $poll['pin'] ?></span>
      </div>
    </div>
    <div class="mt-2">
      <a href="<?= base_url('poll/dashboard') ?>" class="btn btn-outline-secondary btn-sm mr-2">
        <i class="fas fa-arrow-left"></i> Back
      </a>
      <button id="btn-close-poll" class="btn btn-danger btn-sm" data-poll="<?= $poll['poll_id'] ?>">
        <i class="fas fa-stop-circle"></i> Close Poll
      </button>
    </div>
  </div>

  <div class="row">
    <!-- Question sidebar -->
    <div class="col-md-3 mb-3">
      <div class="sidebar">
        <p class="text-uppercase text-muted small mb-2" style="letter-spacing:1px">Questions</p>
        <?php foreach ($questions as $q): ?>
          <button class="q-btn <?= ($poll['active_question_id'] == $q['question_id']) ? 'active' : '' ?>"
                  data-qid="<?= $q['question_id'] ?>"
                  data-type="<?= $q['question_type'] ?>"
                  id="qbtn-<?= $q['question_id'] ?>">
            Q<?= $q['sort_order'] + 1 ?>.
            <?= mb_strimwidth(htmlspecialchars($q['question_text']), 0, 24, '…') ?>
            <span class="type-badge"><?= $q['question_type'] === 'open_ended' ? 'OE' : 'MC' ?></span>
          </button>
        <?php endforeach; ?>
        <?php if (empty($questions)): ?>
          <p class="text-muted small">No questions.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Main panel -->
    <div class="col-md-9 mb-3">
      <div class="main-panel" id="main-panel">
        <p class="waiting-msg" id="waiting-msg">
          <i class="fas fa-hand-pointer fa-2x mb-3 d-block"></i>
          Click a question on the left to launch it
        </p>

        <div id="active-view" style="display:none; width:100%; text-align:center">
          <p class="q-type-label" id="active-type-label"></p>
          <p class="question-text" id="active-question-text"></p>

          <div class="status-row">
            <span class="vote-count"><i class="fas fa-users"></i> <span id="vote-total">0</span> responses</span>
            <button id="btn-toggle-results" class="btn btn-sm btn-outline-light">
              <i class="fas fa-eye"></i> Show Results to Students
            </button>
          </div>

          <!-- Multiple-choice bar chart -->
          <div class="chart-wrap" id="mc-chart-wrap" style="display:none">
            <canvas id="results-chart" height="300"></canvas>
          </div>

          <!-- Open-ended word cloud -->
          <div id="oe-cloud-wrap" style="display:none; width:100%">
            <div id="word-cloud-wrap"></div>
            <div id="oe-list"></div>
          </div>
        </div>
      </div>

      <div class="controls" id="ctrl-row" style="display:none">
        <button id="btn-prev" class="btn btn-outline-light btn-sm"><i class="fas fa-chevron-left"></i> Prev</button>
        <button id="btn-next" class="btn btn-outline-light btn-sm">Next <i class="fas fa-chevron-right"></i></button>
      </div>
    </div>
  </div>
</div>

<script src="<?= base_url('assets/jquery-3.5.1.slim.min.js') ?>"></script>
<script src="<?= base_url('assets/bootstrap.bundle.min.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
(function () {
  const BASE      = '<?= base_url() ?>';
  const pollId    = <?= (int)$poll['poll_id'] ?>;
  const questions = <?= json_encode(array_values($questions)) ?>;
  let activeQid   = <?= $poll['active_question_id'] ? (int)$poll['active_question_id'] : 'null' ?>;
  let activeType  = null;
  let showResults = false;
  let chart       = null;
  let pollTimer   = null;

  const PALETTE = ['#e94560','#0f3460','#533483','#2ecc71','#f39c12','#3498db','#9b59b6','#1abc9c'];
  const WC_COLORS = ['#e94560','#f39c12','#2ecc71','#3498db','#9b59b6','#1abc9c','#e67e22','#16a085'];

  // ── Chart (MC) ─────────────────────────────────────────────────────────

  function buildChart(labels, data) {
    const ctx = document.getElementById('results-chart').getContext('2d');
    if (chart) chart.destroy();
    chart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          label: 'Votes',
          data,
          backgroundColor: labels.map((_, i) => PALETTE[i % PALETTE.length]),
          borderRadius: 8,
          borderSkipped: false,
        }]
      },
      options: {
        responsive: true,
        animation: { duration: 400 },
        plugins: { legend: { display: false } },
        scales: {
          y: { beginAtZero: true, ticks: { stepSize: 1, color: '#ccc' }, grid: { color: 'rgba(255,255,255,.1)' } },
          x: { ticks: { color: '#ccc' }, grid: { display: false } }
        }
      }
    });
  }

  function updateChart(results) {
    if (!chart) return;
    chart.data.labels = results.map(r => r.option_text);
    chart.data.datasets[0].data = results.map(r => parseInt(r.votes));
    chart.update('active');
  }

  // ── Word Cloud (OE) ────────────────────────────────────────────────────

  function renderWordCloud(results) {
    const wrap = document.getElementById('word-cloud-wrap');
    const list = document.getElementById('oe-list');
    wrap.innerHTML = '';
    list.innerHTML = '';

    if (!results.length) {
      wrap.innerHTML = '<span style="color:#555;font-size:1rem">Waiting for responses…</span>';
      return;
    }

    const maxCount = Math.max(...results.map(r => parseInt(r.count)));

    results.forEach((r, i) => {
      const count = parseInt(r.count);
      const ratio = count / maxCount;
      // Font size 1rem – 3.5rem based on frequency
      const size  = (1 + ratio * 2.5).toFixed(2);
      const span  = document.createElement('span');
      span.className   = 'wc-word';
      span.textContent = r.response_text;
      span.style.fontSize        = size + 'rem';
      span.style.backgroundColor = WC_COLORS[i % WC_COLORS.length] + '33'; // 20% opacity bg
      span.style.color           = WC_COLORS[i % WC_COLORS.length];
      span.title = count + ' response' + (count !== 1 ? 's' : '');
      wrap.appendChild(span);
    });

    // Show recent individual responses as chips
    results.slice(0, 30).forEach(r => {
      const chip = document.createElement('span');
      chip.className   = 'oe-chip';
      chip.textContent = r.response_text + (r.count > 1 ? ' ×' + r.count : '');
      list.appendChild(chip);
    });
  }

  // ── Polling ─────────────────────────────────────────────────────────────

  function startPolling(qid) {
    stopPolling();
    pollTimer = setInterval(() => fetchResults(qid), 2000);
    fetchResults(qid);
  }

  function stopPolling() {
    if (pollTimer) clearInterval(pollTimer);
    pollTimer = null;
  }

  function fetchResults(qid) {
    fetch(BASE + 'poll/results/' + qid)
      .then(r => r.json())
      .then(d => {
        if (!d.ok) return;
        document.getElementById('vote-total').textContent = d.total;
        showResults = d.show_results;
        updateToggleBtn();

        if (d.question_type === 'open_ended') {
          renderWordCloud(d.results);
        } else {
          updateChart(d.results);
        }
      });
  }

  // ── Launch question ──────────────────────────────────────────────────────

  function launchQuestion(qid) {
    fetch(BASE + 'poll/activate_question/' + qid, { method: 'POST' })
      .then(r => r.json())
      .then(d => {
        if (!d.ok) return;
        activeQid = qid;
        renderActiveView(qid);
        highlightBtn(qid);
        startPolling(qid);
      });
  }

  function renderActiveView(qid) {
    const q = questions.find(q => q.question_id == qid);
    if (!q) return;
    activeType = q.question_type;

    document.getElementById('waiting-msg').style.display = 'none';
    document.getElementById('active-view').style.display = '';
    document.getElementById('ctrl-row').style.display    = 'flex';
    document.getElementById('active-question-text').textContent = q.question_text;
    document.getElementById('active-type-label').textContent =
      activeType === 'open_ended' ? 'Open-Ended · Word Cloud' : 'Multiple Choice';

    const mcWrap = document.getElementById('mc-chart-wrap');
    const oeWrap = document.getElementById('oe-cloud-wrap');

    if (activeType === 'open_ended') {
      mcWrap.style.display = 'none';
      oeWrap.style.display = '';
      if (chart) { chart.destroy(); chart = null; }
      renderWordCloud([]);
    } else {
      oeWrap.style.display = 'none';
      mcWrap.style.display = '';
      const labels = q.options.map(o => o.option_text);
      buildChart(labels, new Array(labels.length).fill(0));
    }
  }

  function highlightBtn(qid) {
    document.querySelectorAll('.q-btn').forEach(b => b.classList.remove('active'));
    const btn = document.getElementById('qbtn-' + qid);
    if (btn) btn.classList.add('active');
  }

  // ── Toggle results ───────────────────────────────────────────────────────

  function updateToggleBtn() {
    const btn = document.getElementById('btn-toggle-results');
    if (showResults) {
      btn.innerHTML = '<i class="fas fa-eye-slash"></i> Hide Results from Students';
      btn.classList.replace('btn-outline-light', 'btn-success');
    } else {
      btn.innerHTML = '<i class="fas fa-eye"></i> Show Results to Students';
      if (btn.classList.contains('btn-success'))
        btn.classList.replace('btn-success', 'btn-outline-light');
    }
  }

  document.getElementById('btn-toggle-results').addEventListener('click', () => {
    if (!activeQid) return;
    fetch(BASE + 'poll/toggle_results/' + activeQid, { method: 'POST' })
      .then(r => r.json())
      .then(d => { if (d.ok) { showResults = d.show_results; updateToggleBtn(); } });
  });

  // ── Sidebar + Prev/Next ──────────────────────────────────────────────────

  document.querySelectorAll('.q-btn').forEach(btn => {
    btn.addEventListener('click', () => launchQuestion(parseInt(btn.dataset.qid)));
  });

  function currentIndex() { return questions.findIndex(q => q.question_id == activeQid); }

  document.getElementById('btn-prev').addEventListener('click', () => {
    const i = currentIndex();
    if (i > 0) launchQuestion(questions[i - 1].question_id);
  });

  document.getElementById('btn-next').addEventListener('click', () => {
    const i = currentIndex();
    if (i < questions.length - 1) launchQuestion(questions[i + 1].question_id);
  });

  // ── Close poll ───────────────────────────────────────────────────────────

  document.getElementById('btn-close-poll').addEventListener('click', () => {
    if (!confirm('Close this poll? Students will no longer be able to answer.')) return;
    fetch(BASE + 'poll/close_poll/' + pollId, { method: 'POST' })
      .then(r => r.json())
      .then(d => { if (d.ok) { stopPolling(); window.location = BASE + 'poll/dashboard'; } });
  });

  // ── Init ─────────────────────────────────────────────────────────────────
  if (activeQid) {
    renderActiveView(activeQid);
    highlightBtn(activeQid);
    startPolling(activeQid);
  }
})();
</script>

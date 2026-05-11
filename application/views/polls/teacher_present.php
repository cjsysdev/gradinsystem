<?php $this->load->view('header'); ?>
<link rel="stylesheet" href="<?= base_url('assets/fontawesome/css/all.min.css') ?>">
<style>
  body { background: #1a1a2e; color: #eee; }
  .presenter-wrap { max-width: 960px; margin: 0 auto; padding: 24px 16px; }
  .pin-badge { background: #16213e; border: 2px solid #0f3460; border-radius: 12px; padding: 10px 20px; display: inline-block; }
  .pin-badge .pin-code { font-size: 2.4rem; font-weight: 800; letter-spacing: 6px; color: #e94560; }
  .sidebar { background: #16213e; border-radius: 12px; padding: 16px; height: 100%; }
  .q-btn { width: 100%; text-align: left; border-radius: 8px; margin-bottom: 8px; font-weight: 600;
           background: #0f3460; border: none; color: #eee; padding: 10px 14px; cursor: pointer;
           transition: background .2s; }
  .q-btn:hover  { background: #e94560; }
  .q-btn.active { background: #e94560; }
  .main-panel { background: #16213e; border-radius: 12px; padding: 24px; min-height: 420px;
                display: flex; flex-direction: column; align-items: center; justify-content: center; }
  .question-text { font-size: 1.6rem; font-weight: 700; text-align: center; margin-bottom: 24px; line-height: 1.4; }
  .chart-wrap { width: 100%; max-width: 640px; }
  .status-row { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; justify-content: center; }
  .vote-count { font-size: 1.1rem; color: #aaa; }
  .waiting-msg { color: #666; font-size: 1.1rem; text-align: center; }
  .controls { display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; margin-top: 16px; }
</style>

<div class="presenter-wrap">
  <!-- Header row -->
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
    <div>
      <h4 class="mb-1" style="color:#eee"><?= htmlspecialchars($poll['title']) ?></h4>
      <div class="pin-badge">
        Join at <strong>this page</strong> &nbsp;·&nbsp; PIN: <span class="pin-code"><?= $poll['pin'] ?></span>
      </div>
    </div>
    <div class="mt-2">
      <a href="<?= base_url('poll/dashboard') ?>" class="btn btn-outline-secondary btn-sm mr-2">
        <i class="fas fa-arrow-left"></i> Back
      </a>
      <button id="btn-close-poll" class="btn btn-danger btn-sm"
              data-poll="<?= $poll['poll_id'] ?>">
        <i class="fas fa-stop-circle"></i> Close Poll
      </button>
    </div>
  </div>

  <div class="row">
    <!-- Question list sidebar -->
    <div class="col-md-3 mb-3">
      <div class="sidebar">
        <p class="text-uppercase text-muted small mb-2" style="letter-spacing:1px">Questions</p>
        <?php foreach ($questions as $q): ?>
          <button class="q-btn <?= ($poll['active_question_id'] == $q['question_id']) ? 'active' : '' ?>"
                  data-qid="<?= $q['question_id'] ?>"
                  id="qbtn-<?= $q['question_id'] ?>">
            Q<?= $q['sort_order'] + 1 ?>. <?= mb_strimwidth(htmlspecialchars($q['question_text']), 0, 28, '…') ?>
          </button>
        <?php endforeach; ?>
        <?php if (empty($questions)): ?>
          <p class="text-muted small">No questions yet.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Main presentation panel -->
    <div class="col-md-9 mb-3">
      <div class="main-panel" id="main-panel">
        <p class="waiting-msg" id="waiting-msg">
          <i class="fas fa-hand-pointer fa-2x mb-3 d-block"></i>
          Click a question on the left to launch it
        </p>

        <!-- Active question view (hidden until a question is launched) -->
        <div id="active-view" style="display:none; width:100%;">
          <p class="question-text" id="active-question-text"></p>
          <div class="status-row">
            <span class="vote-count"><i class="fas fa-users"></i> <span id="vote-total">0</span> responses</span>
            <button id="btn-toggle-results" class="btn btn-sm btn-outline-light">
              <i class="fas fa-eye"></i> Show Results to Students
            </button>
          </div>
          <div class="chart-wrap">
            <canvas id="results-chart" height="300"></canvas>
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
  const BASE       = '<?= base_url() ?>';
  const pollId     = <?= $poll['poll_id'] ?>;
  const questions  = <?= json_encode(array_values($questions)) ?>;
  let activeQid    = <?= $poll['active_question_id'] ? (int)$poll['active_question_id'] : 'null' ?>;
  let showResults  = false;
  let chart        = null;
  let pollInterval = null;
  const PALETTE    = ['#e94560','#0f3460','#533483','#e94560','#2ecc71','#f39c12','#3498db','#9b59b6'];

  // ── Chart helpers ──────────────────────────────────────────────────────

  function buildChart(labels, data) {
    const ctx = document.getElementById('results-chart').getContext('2d');
    if (chart) chart.destroy();
    chart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Votes',
          data:  data,
          backgroundColor: labels.map((_, i) => PALETTE[i % PALETTE.length]),
          borderRadius: 8,
          borderSkipped: false,
        }]
      },
      options: {
        responsive: true,
        animation: { duration: 400 },
        plugins: {
          legend: { display: false },
          tooltip: { callbacks: { label: ctx => ` ${ctx.raw} vote${ctx.raw !== 1 ? 's' : ''}` } }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: { stepSize: 1, color: '#ccc' },
            grid: { color: 'rgba(255,255,255,.1)' }
          },
          x: { ticks: { color: '#ccc' }, grid: { display: false } }
        }
      }
    });
  }

  function updateChart(results) {
    if (!chart) return;
    chart.data.labels   = results.map(r => r.option_text);
    chart.data.datasets[0].data = results.map(r => parseInt(r.votes));
    chart.update('active');
  }

  // ── Polling ────────────────────────────────────────────────────────────

  function startPolling(qid) {
    stopPolling();
    pollInterval = setInterval(() => fetchResults(qid), 2000);
    fetchResults(qid);
  }

  function stopPolling() {
    if (pollInterval) clearInterval(pollInterval);
    pollInterval = null;
  }

  function fetchResults(qid) {
    fetch(BASE + 'poll/results/' + qid)
      .then(r => r.json())
      .then(d => {
        if (!d.ok) return;
        document.getElementById('vote-total').textContent = d.total;
        showResults = d.show_results;
        updateToggleBtn();
        updateChart(d.results);
      });
  }

  // ── Activate question ──────────────────────────────────────────────────

  function launchQuestion(qid) {
    fetch(BASE + 'poll/activate_question/' + qid, { method: 'POST' })
      .then(r => r.json())
      .then(d => {
        if (!d.ok) return;
        activeQid = qid;
        renderActiveView(qid);
        highlightSidebarBtn(qid);
        startPolling(qid);
      });
  }

  function renderActiveView(qid) {
    const q = questions.find(q => q.question_id == qid);
    if (!q) return;

    document.getElementById('waiting-msg').style.display   = 'none';
    document.getElementById('active-view').style.display   = '';
    document.getElementById('ctrl-row').style.display      = 'flex';
    document.getElementById('active-question-text').textContent = q.question_text;

    const labels = q.options.map(o => o.option_text);
    const data   = new Array(labels.length).fill(0);
    buildChart(labels, data);
  }

  function highlightSidebarBtn(qid) {
    document.querySelectorAll('.q-btn').forEach(b => b.classList.remove('active'));
    const btn = document.getElementById('qbtn-' + qid);
    if (btn) btn.classList.add('active');
  }

  // ── Toggle show results ────────────────────────────────────────────────

  function updateToggleBtn() {
    const btn = document.getElementById('btn-toggle-results');
    if (showResults) {
      btn.innerHTML = '<i class="fas fa-eye-slash"></i> Hide Results from Students';
      btn.classList.replace('btn-outline-light', 'btn-success');
    } else {
      btn.innerHTML = '<i class="fas fa-eye"></i> Show Results to Students';
      btn.classList.replace('btn-success', 'btn-outline-light');
    }
  }

  document.getElementById('btn-toggle-results').addEventListener('click', function () {
    if (!activeQid) return;
    fetch(BASE + 'poll/toggle_results/' + activeQid, { method: 'POST' })
      .then(r => r.json())
      .then(d => { if (d.ok) { showResults = d.show_results; updateToggleBtn(); } });
  });

  // ── Sidebar question buttons ───────────────────────────────────────────

  document.querySelectorAll('.q-btn').forEach(btn => {
    btn.addEventListener('click', function () { launchQuestion(parseInt(this.dataset.qid)); });
  });

  // ── Prev / Next navigation ─────────────────────────────────────────────

  function currentIndex() {
    return questions.findIndex(q => q.question_id == activeQid);
  }

  document.getElementById('btn-prev').addEventListener('click', () => {
    const i = currentIndex();
    if (i > 0) launchQuestion(questions[i - 1].question_id);
  });

  document.getElementById('btn-next').addEventListener('click', () => {
    const i = currentIndex();
    if (i < questions.length - 1) launchQuestion(questions[i + 1].question_id);
  });

  // ── Close poll ─────────────────────────────────────────────────────────

  document.getElementById('btn-close-poll').addEventListener('click', function () {
    if (!confirm('Close this poll? Students will no longer be able to answer.')) return;
    fetch(BASE + 'poll/close_poll/' + pollId, { method: 'POST' })
      .then(r => r.json())
      .then(d => { if (d.ok) { stopPolling(); alert('Poll closed.'); window.location = BASE + 'poll/dashboard'; } });
  });

  // ── Init: resume if poll already has an active question ───────────────

  if (activeQid) {
    renderActiveView(activeQid);
    highlightSidebarBtn(activeQid);
    startPolling(activeQid);
  }

})();
</script>

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

  /* Open-ended comment feed */
  .cf-wrap { width: 100%; max-width: 680px; margin: 0 auto; text-align: left;
             background: #0f1a33; border: 1px solid rgba(255,255,255,.08); border-radius: 12px; }
  .cf-head { display: flex; align-items: center; justify-content: space-between; gap: 8px;
             padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,.08); flex-wrap: wrap; }
  .cf-head-title { font-weight: 700; font-size: .95rem; color: #ddd; }
  .cf-head-title i { color: #e94560; margin-right: 6px; }
  .cf-sort { display: flex; gap: 4px; }
  .cf-sort button, .cf-anon { background: transparent; border: 1px solid rgba(255,255,255,.15); color: #aaa;
                              border-radius: 16px; padding: 3px 12px; font-size: .78rem; cursor: pointer; }
  .cf-sort button.on, .cf-anon.on { background: #e94560; border-color: #e94560; color: #fff; }
  #cf-list { max-height: 460px; overflow-y: auto; padding: 8px 4px; }
  #cf-list::-webkit-scrollbar { width: 6px; }
  #cf-list::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 3px; }
  .cf-item { display: flex; gap: 12px; padding: 10px 12px; border-radius: 10px; }
  .cf-item.cf-new { animation: cfIn .45s ease-out; }
  @keyframes cfIn { from { opacity: 0; transform: translateY(-10px); background: rgba(233,69,96,.18); }
                    to   { opacity: 1; transform: none; background: transparent; } }
  .cf-avatar { flex: 0 0 40px; height: 40px; border-radius: 50%; display: flex; align-items: center;
               justify-content: center; font-weight: 700; font-size: .9rem; color: #fff; }
  .cf-body { flex: 1; min-width: 0; }
  .cf-bubble { background: rgba(255,255,255,.07); border-radius: 4px 16px 16px 16px; padding: 8px 14px;
               display: inline-block; max-width: 100%; }
  .cf-name { font-weight: 700; font-size: .85rem; color: #fff; }
  .cf-text { font-size: 1.15rem; color: #e6e6e6; line-height: 1.4; word-wrap: break-word; white-space: pre-wrap; }
  .cf-meta { display: flex; gap: 14px; align-items: center; font-size: .75rem; color: #888;
             margin: 4px 0 0 6px; }
  .cf-likes { color: #e94560; font-weight: 700; }
  .cf-empty { color: #666; text-align: center; padding: 48px 16px; }
  .cf-empty i { display: block; font-size: 2rem; margin-bottom: 10px; }
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
      <a href="<?= base_url('poll/report/' . $poll['poll_id']) ?>" class="btn btn-outline-light btn-sm mr-2">
        <i class="fas fa-chart-bar"></i> Results
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

          <!-- Open-ended comment feed -->
          <div id="oe-cloud-wrap" style="display:none; width:100%">
            <div class="cf-wrap">
              <div class="cf-head">
                <span class="cf-head-title"><i class="fas fa-comments"></i><span id="cf-count">0</span> comments</span>
                <div class="d-flex align-items-center" style="gap:8px">
                  <div class="cf-sort">
                    <button type="button" data-sort="new" class="on">Newest</button>
                    <button type="button" data-sort="top">Top</button>
                  </div>
                  <button type="button" class="cf-anon" id="cf-anon"><i class="fas fa-user-secret"></i> Hide names</button>
                </div>
              </div>
              <div id="cf-list"></div>
            </div>
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

  // ── Comment feed (OE) ──────────────────────────────────────────────────

  let feedData  = [];
  let feedSort  = 'new';
  let feedAnon  = false;
  let seenIds   = new Set();

  function resetFeed() {
    feedData = [];
    seenIds  = new Set();
    renderFeed();
  }

  function timeAgo(sec) {
    sec = Math.max(0, parseInt(sec) || 0);
    if (sec < 10)    return 'just now';
    if (sec < 60)    return sec + 's';
    if (sec < 3600)  return Math.floor(sec / 60) + 'm';
    if (sec < 86400) return Math.floor(sec / 3600) + 'h';
    return Math.floor(sec / 86400) + 'd';
  }

  function avatarColor(seed) {
    let h = 0;
    for (let i = 0; i < seed.length; i++) h = (h * 31 + seed.charCodeAt(i)) | 0;
    return WC_COLORS[Math.abs(h) % WC_COLORS.length];
  }

  function renderFeed() {
    const list = document.getElementById('cf-list');
    document.getElementById('cf-count').textContent = feedData.length;

    if (!feedData.length) {
      list.innerHTML = '<div class="cf-empty"><i class="far fa-comment-dots"></i>Waiting for responses…</div>';
      return;
    }

    // "Likes" = how many students posted the same answer (case/space-insensitive)
    const norm   = t => t.trim().toLowerCase().replace(/\s+/g, ' ');
    const tally  = {};
    feedData.forEach(r => { const k = norm(r.response_text); tally[k] = (tally[k] || 0) + 1; });

    const rows = feedData.map((r, i) => ({ ...r, likes: tally[norm(r.response_text)], idx: i }));
    if (feedSort === 'top') rows.sort((a, b) => b.likes - a.likes || a.idx - b.idx);

    const keepScroll = list.scrollTop;
    list.innerHTML = '';

    rows.forEach(r => {
      const first = (r.firstname || '').trim();
      const last  = (r.lastname  || '').trim();
      const name  = feedAnon ? 'Anonymous' : ((first + ' ' + last).trim() || 'Student');
      const init  = feedAnon ? '' : ((first[0] || '') + (last[0] || '')).toUpperCase() || '?';

      const item = document.createElement('div');
      item.className = 'cf-item' + (seenIds.has(r.response_id) ? '' : ' cf-new');

      const av = document.createElement('div');
      av.className = 'cf-avatar';
      av.style.background = feedAnon ? '#3a3f58' : avatarColor(name);
      if (feedAnon) av.innerHTML = '<i class="fas fa-user"></i>'; else av.textContent = init;

      const body   = document.createElement('div');
      body.className = 'cf-body';
      const bubble = document.createElement('div');
      bubble.className = 'cf-bubble';
      const nm = document.createElement('div');
      nm.className = 'cf-name';
      nm.textContent = name;
      const tx = document.createElement('div');
      tx.className = 'cf-text';
      tx.textContent = r.response_text;
      bubble.append(nm, tx);

      const meta = document.createElement('div');
      meta.className = 'cf-meta';
      meta.innerHTML = '<span>' + timeAgo(r.age_sec) + '</span>' +
        (r.likes > 1
          ? '<span class="cf-likes"><i class="fas fa-heart"></i> ' + r.likes + ' said this</span>'
          : '');

      body.append(bubble, meta);
      item.append(av, body);
      list.appendChild(item);
    });

    feedData.forEach(r => seenIds.add(r.response_id));
    list.scrollTop = keepScroll;
  }

  document.querySelectorAll('.cf-sort button').forEach(btn => {
    btn.addEventListener('click', () => {
      feedSort = btn.dataset.sort;
      document.querySelectorAll('.cf-sort button').forEach(b => b.classList.toggle('on', b === btn));
      renderFeed();
    });
  });

  document.getElementById('cf-anon').addEventListener('click', function () {
    feedAnon = !feedAnon;
    this.classList.toggle('on', feedAnon);
    this.innerHTML = feedAnon
      ? '<i class="fas fa-user"></i> Show names'
      : '<i class="fas fa-user-secret"></i> Hide names';
    renderFeed();
  });

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
          if (qid != activeQid) return; // stale response from a previous question
          feedData = d.feed || [];
          renderFeed();
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
      activeType === 'open_ended' ? 'Open-Ended · Comments' : 'Multiple Choice';

    const mcWrap = document.getElementById('mc-chart-wrap');
    const oeWrap = document.getElementById('oe-cloud-wrap');

    if (activeType === 'open_ended') {
      mcWrap.style.display = 'none';
      oeWrap.style.display = '';
      if (chart) { chart.destroy(); chart = null; }
      resetFeed();
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

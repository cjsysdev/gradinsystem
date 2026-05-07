<?php $this->load->view('header'); ?>
<style>
  body { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); min-height: 100vh; }
  .answer-card { background: #fff; border-radius: 16px; padding: 32px 24px; max-width: 540px; margin: 40px auto; box-shadow: 0 20px 60px rgba(0,0,0,.4); }
  .poll-title { font-size: .85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; color: #e94560; margin-bottom: 4px; }
  .q-text { font-size: 1.4rem; font-weight: 700; color: #1a1a2e; margin-bottom: 24px; line-height: 1.4; }
  .opt-btn { display: block; width: 100%; text-align: left; background: #f4f6f9; border: 2px solid transparent; border-radius: 10px; padding: 14px 18px; margin-bottom: 10px; font-size: 1rem; font-weight: 600; color: #333; cursor: pointer; transition: all .2s; }
  .opt-btn:hover  { border-color: #e94560; background: #fff0f3; }
  .opt-btn.selected { border-color: #e94560; background: #fff0f3; color: #e94560; }
  .opt-btn:disabled { cursor: default; opacity: .8; }
  .opt-btn.winner { border-color: #2ecc71; background: #f0fff4; }
  .result-bar-wrap { position: relative; height: 8px; background: #eee; border-radius: 4px; margin-top: 6px; overflow: hidden; }
  .result-bar { height: 100%; background: #e94560; border-radius: 4px; transition: width .6s ease; }
  .result-pct { font-size: .8rem; color: #999; margin-top: 2px; }
  .status-pill { display: inline-block; border-radius: 20px; padding: 4px 14px; font-size: .8rem; font-weight: 700; letter-spacing: 1px; margin-bottom: 16px; }
  .waiting-pill { background: #fff3cd; color: #856404; }
  .active-pill  { background: #d1e7dd; color: #0a3622; }
  .closed-pill  { background: #f8d7da; color: #842029; }
  .answered-tick { color: #2ecc71; font-size: 1.2rem; margin-left: 8px; }
  .waiting-icon { text-align: center; padding: 40px 0; color: #aaa; }
</style>

<div class="answer-card">
  <p class="poll-title"><?= htmlspecialchars($poll['title']) ?> &nbsp;·&nbsp; PIN: <?= $poll['pin'] ?></p>

  <div id="state-waiting" style="display:none">
    <span class="status-pill waiting-pill"><i class="fas fa-clock"></i> Waiting for question…</span>
    <div class="waiting-icon">
      <i class="fas fa-hourglass-half fa-3x mb-3"></i>
      <p>Your teacher will launch a question soon.</p>
    </div>
  </div>

  <div id="state-active" style="display:none">
    <span class="status-pill active-pill" id="active-pill"><i class="fas fa-circle text-success"></i> Live</span>
    <p class="q-text" id="q-text"></p>
    <div id="options-container"></div>
    <div id="answered-msg" style="display:none" class="text-muted text-center mt-3">
      <i class="fas fa-check-circle text-success"></i> Answer recorded — waiting for results…
    </div>
    <div id="results-section" style="display:none"></div>
  </div>

  <div id="state-closed" style="display:none">
    <span class="status-pill closed-pill"><i class="fas fa-stop-circle"></i> Poll Closed</span>
    <p class="text-muted mt-3 text-center">This poll has ended. Thanks for participating!</p>
  </div>
</div>

<?php $this->load->view('footer'); ?>
<script>
(function () {
  const BASE         = '<?= base_url() ?>';
  const PIN          = '<?= $poll['pin'] ?>';
  let lastQid        = null;
  let answered       = false;
  let selectedOption = null;
  let pollInterval   = null;

  // ── State renderers ────────────────────────────────────────────────────

  function showWaiting() {
    hide(['state-active', 'state-closed']);
    show('state-waiting');
  }

  function showClosed() {
    stopPolling();
    hide(['state-active', 'state-waiting']);
    show('state-closed');
  }

  function showActive(data) {
    hide(['state-waiting', 'state-closed']);
    show('state-active');

    const qChanged = lastQid !== data.question_id;
    if (qChanged) {
      lastQid        = data.question_id;
      answered       = data.answered;
      selectedOption = null;
      renderQuestion(data);
    }

    answered = data.answered;

    // Toggle answered message vs options
    document.getElementById('answered-msg').style.display = answered ? '' : 'none';
    document.getElementById('options-container').style.display = answered ? 'none' : '';

    if (answered && data.results) {
      renderResults(data.results, data.total, selectedOption);
    } else {
      document.getElementById('results-section').style.display = 'none';
    }
  }

  function renderQuestion(data) {
    document.getElementById('q-text').textContent = data.question;

    const cont = document.getElementById('options-container');
    cont.innerHTML = '';
    data.options.forEach(opt => {
      const btn = document.createElement('button');
      btn.className = 'opt-btn';
      btn.textContent = opt.option_text;
      btn.dataset.optionId = opt.option_id;
      btn.addEventListener('click', function () {
        if (answered) return;
        submitAnswer(data.question_id, opt.option_id);
        selectedOption = opt.option_id;
        document.querySelectorAll('.opt-btn').forEach(b => b.classList.remove('selected'));
        this.classList.add('selected');
      });
      cont.appendChild(btn);
    });
  }

  function renderResults(results, total, chosenOptionId) {
    const sec  = document.getElementById('results-section');
    sec.style.display = '';
    const maxVotes = Math.max(...results.map(r => parseInt(r.votes)), 1);
    sec.innerHTML = '<p class="text-muted small mb-2 text-center">Results (' + total + ' responses)</p>' +
      results.map(r => {
        const pct     = total > 0 ? Math.round(parseInt(r.votes) / total * 100) : 0;
        const isChosen = r.option_id == chosenOptionId;
        const isWinner = parseInt(r.votes) === maxVotes && parseInt(r.votes) > 0;
        return `<div class="opt-btn ${isWinner ? 'winner' : ''}" style="cursor:default;opacity:1">
          ${r.option_text} ${isChosen ? '<span class="answered-tick">✓</span>' : ''}
          <div class="result-bar-wrap"><div class="result-bar" style="width:${pct}%"></div></div>
          <span class="result-pct">${r.votes} vote${r.votes != 1 ? 's' : ''} · ${pct}%</span>
        </div>`;
      }).join('');
  }

  // ── Submit ─────────────────────────────────────────────────────────────

  function submitAnswer(questionId, optionId) {
    fetch(BASE + 'poll/submit_answer', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `question_id=${questionId}&option_id=${optionId}`,
    })
    .then(r => r.json())
    .then(d => {
      if (d.ok) { answered = true; fetchState(); }
    });
  }

  // ── State polling ──────────────────────────────────────────────────────

  function fetchState() {
    fetch(BASE + 'poll/student_state/' + PIN)
      .then(r => r.json())
      .then(d => {
        if (!d.ok) return;
        if (d.status === 'closed')   { showClosed();     return; }
        if (d.status === 'waiting')  { showWaiting();    return; }
        if (d.status === 'active')   { showActive(d);    return; }
      })
      .catch(() => {});
  }

  function startPolling() {
    stopPolling();
    pollInterval = setInterval(fetchState, 2000);
    fetchState();
  }

  function stopPolling() {
    if (pollInterval) clearInterval(pollInterval);
    pollInterval = null;
  }

  // ── Helpers ────────────────────────────────────────────────────────────

  function show(id) { document.getElementById(id).style.display = ''; }
  function hide(ids) { ids.forEach(id => document.getElementById(id).style.display = 'none'); }

  // ── Boot ───────────────────────────────────────────────────────────────
  showWaiting();
  startPolling();
})();
</script>

<?php $this->load->view('header'); ?>
<style>
  body { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); min-height: 100vh; }
  .answer-card { background: #fff; border-radius: 16px; padding: 32px 24px; max-width: 540px; margin: 40px auto; box-shadow: 0 20px 60px rgba(0,0,0,.4); }
  .poll-title { font-size: .85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; color: #e94560; margin-bottom: 4px; }
  .q-text { font-size: 1.4rem; font-weight: 700; color: #1a1a2e; margin-bottom: 24px; line-height: 1.4; }
  .q-type-chip { display: inline-block; font-size: .72rem; font-weight: 700; text-transform: uppercase;
                 letter-spacing: 1px; border-radius: 4px; padding: 2px 8px; margin-bottom: 14px; }
  .chip-mc { background: #e8f4fd; color: #0f3460; }
  .chip-oe { background: #fdf3e8; color: #c0392b; }

  /* Multiple choice */
  .opt-btn { display: block; width: 100%; text-align: left; background: #f4f6f9; border: 2px solid transparent;
             border-radius: 10px; padding: 14px 18px; margin-bottom: 10px; font-size: 1rem; font-weight: 600;
             color: #333; cursor: pointer; transition: all .2s; }
  .opt-btn:hover  { border-color: #e94560; background: #fff0f3; }
  .opt-btn.selected { border-color: #e94560; background: #fff0f3; color: #e94560; }
  .opt-btn:disabled { cursor: default; opacity: .8; }
  .opt-btn.winner   { border-color: #2ecc71; background: #f0fff4; }
  .result-bar-wrap  { position: relative; height: 8px; background: #eee; border-radius: 4px; margin-top: 6px; overflow: hidden; }
  .result-bar       { height: 100%; background: #e94560; border-radius: 4px; transition: width .6s ease; }
  .result-pct       { font-size: .8rem; color: #999; margin-top: 2px; }

  /* Open-ended */
  .oe-input { width: 100%; border: 2px solid #dee2e6; border-radius: 10px; padding: 14px 16px;
              font-size: 1rem; resize: none; transition: border-color .2s; font-family: inherit; }
  .oe-input:focus { border-color: #e94560; outline: none; box-shadow: 0 0 0 3px rgba(233,69,96,.15); }
  .char-count { text-align: right; font-size: .8rem; color: #aaa; margin-top: 4px; }
  .btn-submit-oe { background: #e94560; border: none; color: #fff; border-radius: 10px;
                   padding: 13px; font-size: 1rem; font-weight: 700; width: 100%; margin-top: 12px;
                   cursor: pointer; transition: background .2s; }
  .btn-submit-oe:hover:not(:disabled) { background: #c73652; }
  .btn-submit-oe:disabled { opacity: .6; cursor: default; }

  /* OE results word cloud */
  #oe-results-wrap { display: flex; flex-wrap: wrap; gap: 8px 10px; justify-content: center;
                     padding: 12px; background: #f8f9fa; border-radius: 10px; min-height: 60px; }
  .wc-word { border-radius: 6px; padding: 4px 10px; font-weight: 700; cursor: default; }

  /* Status pills */
  .status-pill { display: inline-block; border-radius: 20px; padding: 4px 14px; font-size: .8rem;
                 font-weight: 700; letter-spacing: 1px; margin-bottom: 16px; }
  .waiting-pill { background: #fff3cd; color: #856404; }
  .active-pill  { background: #d1e7dd; color: #0a3622; }
  .closed-pill  { background: #f8d7da; color: #842029; }
  .answered-tick { color: #2ecc71; font-size: 1.2rem; margin-left: 8px; }
  .waiting-icon { text-align: center; padding: 40px 0; color: #aaa; }
</style>

<div class="answer-card">
  <p class="poll-title"><?= htmlspecialchars($poll['title']) ?></p>

  <!-- Waiting state -->
  <div id="state-waiting" style="display:none">
    <span class="status-pill waiting-pill"><i class="fas fa-clock"></i> Waiting for question…</span>
    <div class="waiting-icon">
      <i class="fas fa-hourglass-half fa-3x mb-3"></i>
      <p>Your teacher will launch a question soon.</p>
    </div>
  </div>

  <!-- Active state -->
  <div id="state-active" style="display:none">
    <span class="status-pill active-pill"><i class="fas fa-circle text-success"></i> Live</span>
    <p id="q-type-chip" class="q-type-chip"></p>
    <p class="q-text" id="q-text"></p>

    <!-- Multiple choice options -->
    <div id="mc-options"></div>

    <!-- Open-ended input -->
    <div id="oe-input-wrap" style="display:none">
      <textarea id="oe-input" class="oe-input" rows="3" maxlength="100"
                placeholder="Type your response…"></textarea>
      <p class="char-count"><span id="char-remain">100</span> characters remaining</p>
      <button id="btn-submit-oe" class="btn-submit-oe">
        <i class="fas fa-paper-plane"></i> Submit
      </button>
    </div>

    <!-- After answering -->
    <div id="answered-msg" style="display:none" class="text-muted text-center mt-3">
      <i class="fas fa-check-circle text-success"></i> Answer recorded — waiting for results…
    </div>

    <!-- Results: MC -->
    <div id="mc-results" style="display:none"></div>

    <!-- Results: OE word cloud -->
    <div id="oe-results" style="display:none">
      <p class="text-muted small text-center mb-2">Live responses</p>
      <div id="oe-results-wrap"></div>
    </div>
  </div>

  <!-- Closed state -->
  <div id="state-closed" style="display:none">
    <span class="status-pill closed-pill"><i class="fas fa-stop-circle"></i> Poll Closed</span>
    <p class="text-muted mt-3 text-center">This poll has ended. Thanks for participating!</p>
  </div>
</div>

<?php $this->load->view('footer'); ?>
<script>
(function () {
  const BASE       = '<?= base_url() ?>';
  const PIN        = '<?= $poll['pin'] ?>';
  const WC_COLORS  = ['#e94560','#f39c12','#2ecc71','#3498db','#9b59b6','#1abc9c','#e67e22','#16a085'];
  let lastQid      = null;
  let lastType     = null;
  let answered     = false;
  let chosenOption = null;
  let pollTimer    = null;

  // ── State renderers ──────────────────────────────────────────────────────

  function showWaiting() {
    hide(['state-active','state-closed']);
    show('state-waiting');
  }

  function showClosed() {
    stopPolling();
    hide(['state-active','state-waiting']);
    show('state-closed');
  }

  function showActive(data) {
    hide(['state-waiting','state-closed']);
    show('state-active');

    const qChanged = lastQid !== data.question_id;
    if (qChanged) {
      lastQid      = data.question_id;
      lastType     = data.question_type;
      answered     = data.answered;
      chosenOption = null;
      renderQuestion(data);
    }

    answered = data.answered;

    // Show answered message vs input
    const isOE = lastType === 'open_ended';
    document.getElementById('mc-options').style.display     = (!answered && !isOE) ? '' : 'none';
    document.getElementById('oe-input-wrap').style.display  = (!answered &&  isOE) ? '' : 'none';
    document.getElementById('answered-msg').style.display   = (answered && !data.results) ? '' : 'none';

    if (answered && data.results) {
      document.getElementById('answered-msg').style.display = 'none';
      if (isOE) {
        renderOEResults(data.results);
      } else {
        renderMCResults(data.results, data.total);
      }
    } else {
      document.getElementById('mc-results').style.display = 'none';
      document.getElementById('oe-results').style.display = 'none';
    }
  }

  function renderQuestion(data) {
    document.getElementById('q-text').textContent = data.question;

    const chip = document.getElementById('q-type-chip');
    if (data.question_type === 'open_ended') {
      chip.textContent = 'Open-Ended';
      chip.className   = 'q-type-chip chip-oe';
    } else {
      chip.textContent = 'Multiple Choice';
      chip.className   = 'q-type-chip chip-mc';
    }

    // Reset inputs
    document.getElementById('oe-input').value = '';
    document.getElementById('char-remain').textContent = '100';
    document.getElementById('btn-submit-oe').disabled = false;

    if (data.question_type === 'multiple_choice') {
      const cont = document.getElementById('mc-options');
      cont.innerHTML = '';
      data.options.forEach(opt => {
        const btn = document.createElement('button');
        btn.className       = 'opt-btn';
        btn.textContent     = opt.option_text;
        btn.dataset.optionId = opt.option_id;
        btn.addEventListener('click', function () {
          if (answered) return;
          chosenOption = opt.option_id;
          document.querySelectorAll('.opt-btn').forEach(b => b.classList.remove('selected'));
          this.classList.add('selected');
          submitMC(data.question_id, opt.option_id);
        });
        cont.appendChild(btn);
      });
    }
  }

  function renderMCResults(results, total) {
    document.getElementById('mc-results').style.display = '';
    const maxVotes = Math.max(...results.map(r => parseInt(r.votes)), 1);
    document.getElementById('mc-results').innerHTML =
      '<p class="text-muted small text-center mb-2">Results (' + total + ' responses)</p>' +
      results.map(r => {
        const pct     = total > 0 ? Math.round(parseInt(r.votes) / total * 100) : 0;
        const isChosen = r.option_id == chosenOption;
        const isWinner = parseInt(r.votes) === maxVotes && parseInt(r.votes) > 0;
        return `<div class="opt-btn ${isWinner ? 'winner' : ''}" style="cursor:default;opacity:1">
          ${r.option_text}${isChosen ? '<span class="answered-tick">✓</span>' : ''}
          <div class="result-bar-wrap"><div class="result-bar" style="width:${pct}%"></div></div>
          <span class="result-pct">${r.votes} vote${r.votes != 1 ? 's' : ''} · ${pct}%</span>
        </div>`;
      }).join('');
  }

  function renderOEResults(results) {
    document.getElementById('oe-results').style.display = '';
    const wrap = document.getElementById('oe-results-wrap');
    wrap.innerHTML = '';
    if (!results.length) {
      wrap.innerHTML = '<span style="color:#aaa">No responses yet</span>';
      return;
    }
    const maxCount = Math.max(...results.map(r => parseInt(r.count)));
    results.forEach((r, i) => {
      const ratio = parseInt(r.count) / maxCount;
      const size  = (.85 + ratio * 1.4).toFixed(2);
      const span  = document.createElement('span');
      span.className   = 'wc-word';
      span.textContent = r.response_text;
      span.style.fontSize        = size + 'rem';
      span.style.backgroundColor = WC_COLORS[i % WC_COLORS.length] + '22';
      span.style.color           = WC_COLORS[i % WC_COLORS.length];
      wrap.appendChild(span);
    });
  }

  // ── Submit ───────────────────────────────────────────────────────────────

  function submitMC(questionId, optionId) {
    fetch(BASE + 'poll/submit_answer', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `question_id=${questionId}&option_id=${optionId}`,
    })
    .then(r => r.json())
    .then(d => { if (d.ok) { answered = true; fetchState(); } });
  }

  document.getElementById('btn-submit-oe').addEventListener('click', () => {
    const text = document.getElementById('oe-input').value.trim();
    if (!text || !lastQid) return;
    document.getElementById('btn-submit-oe').disabled = true;
    fetch(BASE + 'poll/submit_answer', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `question_id=${lastQid}&response_text=${encodeURIComponent(text)}`,
    })
    .then(r => r.json())
    .then(d => { if (d.ok) { answered = true; fetchState(); } });
  });

  document.getElementById('oe-input').addEventListener('input', function () {
    document.getElementById('char-remain').textContent = 100 - this.value.length;
  });

  // ── State polling ────────────────────────────────────────────────────────

  function fetchState() {
    fetch(BASE + 'poll/student_state/' + PIN)
      .then(r => r.json())
      .then(d => {
        if (!d.ok) return;
        if (d.status === 'closed')  { showClosed();  return; }
        if (d.status === 'waiting') { showWaiting(); return; }
        if (d.status === 'active')  { showActive(d); return; }
      }).catch(() => {});
  }

  function startPolling() {
    stopPolling();
    pollTimer = setInterval(fetchState, 2000);
    fetchState();
  }

  function stopPolling() {
    if (pollTimer) clearInterval(pollTimer);
    pollTimer = null;
  }

  function show(id) { document.getElementById(id).style.display = ''; }
  function hide(ids) { ids.forEach(id => document.getElementById(id).style.display = 'none'); }

  showWaiting();
  startPolling();
})();
</script>

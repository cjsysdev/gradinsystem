<?php $this->load->view('header') ?>

<link rel="stylesheet" href="<?= base_url('assets/highlights/atom-one-light.min.css') ?>">

<style>
:root {
    --iq-primary:   #04AA6D;
    --iq-dark:      #038a57;
    --iq-correct:   #28a745;
    --iq-incorrect: #dc3545;
}

/* ── Top HUD ─────────────────────────────────────────── */
.iq-topbar {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}
.iq-progress-wrap { flex: 1; }
.iq-progress-wrap .progress { height: 10px; border-radius: 5px; }

.iq-streak-badge {
    display: flex;
    align-items: center;
    gap: 4px;
    background: #fff3cd;
    border: 2px solid #ffc107;
    border-radius: 20px;
    padding: 4px 12px;
    font-weight: 700;
    font-size: 17px;
    min-width: 60px;
    justify-content: center;
}

.iq-score-badge {
    background: #e8f5e9;
    border: 2px solid var(--iq-primary);
    border-radius: 20px;
    padding: 4px 12px;
    font-weight: 600;
    font-size: 14px;
    color: var(--iq-dark);
    white-space: nowrap;
}

/* ── Section card ────────────────────────────────────── */
.iq-section-card {
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,.08);
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 16px;
}

.iq-card-header {
    background: var(--iq-primary);
    color: #fff;
    padding: 12px 16px;
}
.iq-card-header small { opacity: .85; font-size: 12px; display: block; }
.iq-card-header h5    { margin: 4px 0 0; font-size: 16px; }

/* ── Lesson content ──────────────────────────────────── */
.iq-lesson-body {
    padding: 16px;
    font-size: 15px;
    line-height: 1.75;
}
.iq-lesson-body p  { margin-bottom: 10px; }
.iq-lesson-body ul { padding-left: 20px; margin-bottom: 10px; }
.iq-lesson-body pre {
    background: #f8f9fa;
    border-left: 4px solid var(--iq-primary);
    padding: 12px;
    overflow-x: auto;
    border-radius: 4px;
    margin: 10px 0;
}
.iq-lesson-body pre code { background: none; padding: 0; color: inherit; }
.iq-lesson-body code {
    background: #eee;
    padding: 2px 5px;
    border-radius: 3px;
    font-family: Consolas, monospace;
    color: #d63384;
}
.iq-lesson-body table   { border-collapse: collapse; width: 100%; margin-bottom: 10px; }
.iq-lesson-body th      { background: var(--iq-primary); color: #fff; padding: 8px; border: 1px solid #ddd; }
.iq-lesson-body td      { padding: 8px; border: 1px solid #ddd; }
.iq-lesson-body strong  { color: var(--iq-dark); }

/* ── Question cards ──────────────────────────────────── */
.iq-question-card {
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,.07);
    border-radius: 8px;
    margin-bottom: 14px;
}

.iq-question-text { font-size: 15px; margin-bottom: 12px; }

.iq-choice-btn {
    display: block;
    width: 100%;
    text-align: left;
    padding: 11px 16px;
    margin-bottom: 8px;
    border: 2px solid #dee2e6;
    background: #f8f9fa;
    color: #333;
    border-radius: 8px;
    font-size: 14px;
    cursor: pointer;
    transition: border-color .15s, background .15s;
}
.iq-choice-btn:hover:not(:disabled) {
    border-color: var(--iq-primary);
    background: #e8f5e9;
}
.iq-choice-btn.iq-correct {
    border-color: var(--iq-correct);
    background: #d4edda;
    color: #155724;
    font-weight: 600;
}
.iq-choice-btn.iq-incorrect {
    border-color: var(--iq-incorrect);
    background: #f8d7da;
    color: #721c24;
}
.iq-choice-btn:disabled { cursor: not-allowed; opacity: .9; }

.iq-feedback {
    padding: 10px 14px;
    border-radius: 6px;
    margin-top: 6px;
    font-size: 14px;
}
.iq-feedback.correct   { background: #d4edda; border-left: 4px solid var(--iq-correct); }
.iq-feedback.incorrect { background: #f8d7da; border-left: 4px solid var(--iq-incorrect); }
.iq-fb-msg { font-weight: 600; margin-bottom: 4px; }

/* ── Navigation buttons ──────────────────────────────── */
.iq-nav-btn {
    min-width: 150px;
    padding: 11px 24px;
    font-size: 15px;
    border-radius: 8px;
}

/* ── Section enter animation ─────────────────────────── */
@keyframes iqFadeUp {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}
.iq-section-enter { animation: iqFadeUp .3s ease; }

/* ── Congratulations overlay ─────────────────────────── */
#iq-congrats {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: linear-gradient(135deg, #04AA6D 0%, #038a57 100%);
    color: #fff;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 20px;
}
#iq-congrats.active { display: flex; }

.iq-congrats-title { font-size: clamp(26px, 7vw, 44px); font-weight: 700; }
.iq-score-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 10px 0;
}
.iq-score-big  { font-size: clamp(70px, 20vw, 110px); font-weight: 700; line-height: 1; }
.iq-score-sep  { font-size: 38px; opacity: .8; }
.iq-score-tot  { font-size: 42px; opacity: .9; }

.iq-congrats-msg {
    font-size: 20px;
    font-weight: 500;
    margin-top: 10px;
    opacity: 0;
    transform: translateY(10px);
    transition: opacity .4s ease .5s, transform .4s ease .5s;
}
.iq-congrats-msg.visible { opacity: 1; transform: translateY(0); }

.iq-streak-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 10px;
    background: rgba(255,255,255,.18);
    padding: 7px 18px;
    border-radius: 20px;
    font-size: 16px;
    opacity: 0;
    transition: opacity .4s ease .8s;
}
.iq-streak-row.visible { opacity: 1; }

.iq-congrats-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    justify-content: center;
    margin-top: 24px;
}

/* Confetti dots */
.iq-confetti-wrap { position: absolute; inset: 0; pointer-events: none; overflow: hidden; }
.iq-cdot { position: absolute; width: 10px; height: 10px; border-radius: 50%; opacity: 0; }
@keyframes iqBurst {
    0%   { opacity: 1; transform: translate(0,0) scale(1); }
    100% { opacity: 0; transform: translate(var(--tx), var(--ty)) scale(0.3); }
}

/* ── Discussion-view class compatibility ─────────────────── */
.iq-lesson-body .discussion-title { font-size: 1.3rem; font-weight: 700; color: var(--iq-dark); }
.iq-lesson-body .discussion-intro { font-size: 15px; color: #555; margin-bottom: 14px; }
.iq-lesson-body .section { margin-bottom: 20px; }
.iq-lesson-body .note, .iq-lesson-body .key-term-box {
    background: #e8f5e9;
    border-left: 4px solid var(--iq-primary);
    padding: 10px 14px;
    border-radius: 0 4px 4px 0;
    margin: 10px 0;
}
.iq-lesson-body .warning-box {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 10px 14px;
    border-radius: 0 4px 4px 0;
    margin: 10px 0;
}

/* ── Review mode ─────────────────────────────────────────── */
#iq-review-header {
    display: none;
    position: sticky;
    top: 0;
    z-index: 100;
    background: var(--iq-primary);
    color: #fff;
    padding: 10px 16px;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,.15);
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}
#iq-review-header.active { display: flex; }
.iq-review-label { flex: 1; font-weight: 600; font-size: 15px; }
.iq-review-mode .iq-card-header { background: #6c757d; }
.iq-review-answered-note {
    font-size: 13px;
    color: #155724;
    background: #d4edda;
    border-left: 3px solid #28a745;
    padding: 6px 10px;
    border-radius: 0 4px 4px 0;
    margin-bottom: 8px;
}
</style>

<!-- ── Review mode sticky header (hidden until review) ── -->
<div id="iq-review-header">
    <span class="iq-review-label">
        &#128218; Review: <?= htmlspecialchars($title) ?>
    </span>
    <a href="<?= site_url('attendance') ?>" class="btn btn-light btn-sm">
        &larr; Home
    </a>
    <a href="<?= site_url('interactive_quiz/topics') ?>" class="btn btn-outline-light btn-sm">
        Topics
    </a>
</div>

<div class="container mt-3 mb-5" id="iq-app">

    <?php $this->load->view('profile_info') ?>

    <!-- ── HUD: progress / streak / score ── -->
    <div class="iq-topbar mt-2">
        <div class="iq-progress-wrap">
            <div class="progress">
                <div id="iq-bar" class="progress-bar bg-success" role="progressbar"
                     style="width:0%; transition: width .4s ease;"></div>
            </div>
            <small class="text-muted" id="iq-section-label">
                Section 1 of <?= count($sections) ?>
            </small>
        </div>
        <div class="iq-streak-badge">
            <img src="<?= base_url('assets/streak.png') ?>" height="22" alt="streak">
            <span id="iq-streak">0</span>
        </div>
        <div class="iq-score-badge">
            <span id="iq-score">0</span>&nbsp;/&nbsp;<span><?= (int)$total_questions ?></span>
        </div>
    </div>

    <h5 style="color:var(--iq-primary);" class="mb-3">
        <strong><?= htmlspecialchars($title) ?></strong>
    </h5>

    <!-- ── Sections ── -->
    <?php foreach ($sections as $si => $section):
        $q_count = count($section['questions'] ?? []);
    ?>
    <div id="iq-sec-<?= $si ?>" class="iq-section"
         style="<?= $si > 0 ? 'display:none;' : '' ?>">

        <!-- Lesson card -->
        <div class="iq-section-card">
            <div class="iq-card-header">
                <small>Section <?= $si + 1 ?> of <?= count($sections) ?></small>
                <h5><?= htmlspecialchars($section['title']) ?></h5>
            </div>
            <div class="iq-lesson-body">
                <?= str_replace('{ASSETS}', base_url(), $section['lesson']) ?>
            </div>
        </div>

        <!-- Question cards -->
        <?php if (!empty($section['questions'])): ?>
            <?php foreach ($section['questions'] as $qi => $q): ?>
            <div class="card iq-question-card"
                 id="iq-q-<?= $si ?>-<?= $qi ?>"
                 data-answer="<?= htmlspecialchars($q['answer'], ENT_QUOTES) ?>"
                 data-orig-qi="<?= isset($q['_orig_qi']) ? (int)$q['_orig_qi'] : $qi ?>">
                <div class="card-body">
                    <p class="iq-question-text">
                        <strong>Q<?= $qi + 1 ?>:</strong>
                        <?= htmlspecialchars($q['question']) ?>
                    </p>
                    <div id="iq-choices-<?= $si ?>-<?= $qi ?>">
                        <?php foreach ($q['choices'] as $choice): ?>
                        <button type="button"
                                class="iq-choice-btn"
                                data-value="<?= htmlspecialchars($choice, ENT_QUOTES) ?>"
                                onclick="iqAnswer(this, <?= $si ?>, <?= $qi ?>)">
                            <?= htmlspecialchars($choice) ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <div class="iq-feedback" id="iq-fb-<?= $si ?>-<?= $qi ?>"
                         style="display:none;">
                        <div class="iq-fb-msg"></div>
                        <?php if (!empty($q['explanation'])): ?>
                        <small class="text-secondary">
                            <?= htmlspecialchars($q['explanation']) ?>
                        </small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Navigation -->
        <div class="text-center my-4">
            <?php if ($si < count($sections) - 1): ?>
            <button type="button"
                    class="btn btn-success iq-nav-btn"
                    id="iq-next-<?= $si ?>"
                    onclick="iqNextSection(<?= $si ?>)"
                    style="<?= $q_count > 0 ? 'display:none;' : '' ?>">
                Next Section &rarr;
            </button>
            <?php else: ?>
            <button type="button"
                    class="btn btn-primary iq-nav-btn"
                    id="iq-finish"
                    onclick="iqFinish()"
                    style="<?= $q_count > 0 ? 'display:none;' : '' ?>">
                Finish &#10003;
            </button>
            <?php endif; ?>
        </div>

    </div>
    <?php endforeach; ?>

</div><!-- #iq-app -->

<!-- ── Congratulations overlay ── -->
<div id="iq-congrats">
    <div class="iq-confetti-wrap" id="iq-confetti"></div>
    <div class="iq-congrats-title">&#127881; Topic Complete!</div>
    <div class="iq-score-row">
        <span class="iq-score-big" id="iq-final-num">0</span>
        <span class="iq-score-sep">/</span>
        <span class="iq-score-tot"><?= (int)$total_questions ?></span>
    </div>
    <div class="iq-congrats-msg" id="iq-congrats-msg"></div>
    <div class="iq-streak-row" id="iq-streak-row">
        <img src="<?= base_url('assets/streak.png') ?>" height="22" alt="streak">
        <span>Best streak: <strong id="iq-max-streak">0</strong></span>
    </div>
    <div class="iq-congrats-actions">
        <button type="button" class="btn btn-outline-light btn-lg" onclick="iqShowReview()">
            &#128218; Review All Content
        </button>
        <a href="<?= site_url('attendance') ?>" class="btn btn-light btn-lg">
            &larr; Back to Home
        </a>
        <a href="<?= site_url('interactive_quiz/topics') ?>" class="btn btn-outline-light btn-lg">
            More Topics
        </a>
        <?php if (!empty($assessment_id)): ?>
        <form method="post" action="<?= site_url('interactive_quiz/save_result') ?>" id="iq-save-form">
            <input type="hidden" name="assessment_id" value="<?= (int)$assessment_id ?>">
            <input type="hidden" name="score" id="iq-save-score" value="0">
            <button type="submit" class="btn btn-warning btn-lg">Save Score</button>
        </form>
        <?php endif; ?>
    </div>
</div>

<script src="<?= base_url('assets/highlights/11.7.0-highlight.min.js') ?>"></script>
<script>
const IQ = {
    score:      0,
    streak:     0,
    maxStreak:  0,
    total:      <?= (int)$total_questions ?>,
    totalSecs:  <?= (int)count($sections) ?>,
    currentSec: 0,
    topic:      '<?= addslashes($topic) ?>',
    qCounts:    <?= json_encode(array_map(function($s){ return count($s['questions'] ?? []); }, $sections)) ?>,
    sectionTitles: <?= json_encode(array_column($sections, 'title')) ?>
};

function updateHUD() {
    document.getElementById('iq-score').textContent  = IQ.score;
    document.getElementById('iq-streak').textContent = IQ.streak;

    const pct = Math.round((IQ.currentSec / IQ.totalSecs) * 100);
    document.getElementById('iq-bar').style.width = pct + '%';
    document.getElementById('iq-section-label').textContent =
        'Section ' + (IQ.currentSec + 1) + ' of ' + IQ.totalSecs;
}

function iqAnswer(btn, si, qi) {
    const card = document.getElementById('iq-q-' + si + '-' + qi);
    if (card.dataset.answered) return;
    card.dataset.answered = '1';

    const correct   = card.dataset.answer;
    const chosen    = btn.dataset.value;
    const isCorrect = (chosen === correct);

    // Lock all choice buttons and highlight correct/wrong
    card.querySelectorAll('.iq-choice-btn').forEach(function(b) {
        b.disabled = true;
        if (b.dataset.value === correct) b.classList.add('iq-correct');
    });
    if (!isCorrect) btn.classList.add('iq-incorrect');

    // Update counters
    if (isCorrect) {
        IQ.score++;
        IQ.streak++;
        if (IQ.streak > IQ.maxStreak) IQ.maxStreak = IQ.streak;
    } else {
        IQ.streak = 0;
    }

    // Show feedback
    const fb  = document.getElementById('iq-fb-' + si + '-' + qi);
    const msg = fb.querySelector('.iq-fb-msg');
    msg.textContent  = isCorrect ? '✓ Correct!' : '✗ Incorrect';
    msg.style.color  = isCorrect ? '#155724' : '#721c24';
    fb.style.display = 'block';
    fb.className     = 'iq-feedback ' + (isCorrect ? 'correct' : 'incorrect');

    updateHUD();
    checkSectionDone(si);

    // Record attempt for analytics (fire-and-forget)
    var qText = card.querySelector('.iq-question-text').textContent.replace(/^\s*Q\d+:\s*/, '').trim();
    fetch('<?= site_url('interactive_quiz/record_attempt') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            topic:          IQ.topic,
            section_index:  si,
            section_title:  IQ.sectionTitles[si] || '',
            question_index: card.dataset.origQi !== undefined ? card.dataset.origQi : qi,
            question_text:  qText,
            is_correct:     isCorrect ? '1' : '0'
        })
    }).catch(function() {}); // silent fail — never block the quiz UI
}

function checkSectionDone(si) {
    const total = IQ.qCounts[si] || 0;
    if (total === 0) return;

    let answered = 0;
    for (let qi = 0; qi < total; qi++) {
        const card = document.getElementById('iq-q-' + si + '-' + qi);
        if (card && card.dataset.answered) answered++;
    }

    if (answered >= total) {
        const nextBtn   = document.getElementById('iq-next-' + si);
        const finishBtn = document.getElementById('iq-finish');
        if (nextBtn)   nextBtn.style.display   = 'inline-block';
        if (finishBtn) finishBtn.style.display = 'inline-block';
    }
}

function iqNextSection(si) {
    document.getElementById('iq-sec-' + si).style.display = 'none';
    const next = si + 1;
    const el   = document.getElementById('iq-sec-' + next);
    el.style.display = 'block';
    el.classList.remove('iq-section-enter');
    void el.offsetWidth; // force reflow for animation restart
    el.classList.add('iq-section-enter');
    IQ.currentSec = next;
    updateHUD();
    window.scrollTo(0, 0);
}

function iqFinish() {
    const overlay = document.getElementById('iq-congrats');
    overlay.classList.add('active');

    // Set save-score hidden input
    const saveInput = document.getElementById('iq-save-score');
    if (saveInput) saveInput.value = IQ.score;

    // Count-up animation
    const el     = document.getElementById('iq-final-num');
    let   cur    = 0;
    const target = IQ.score;
    const step   = Math.max(1, Math.ceil(target / 25));
    const timer  = setInterval(function() {
        cur = Math.min(cur + step, target);
        el.textContent = cur;
        if (cur >= target) clearInterval(timer);
    }, 40);

    // Message
    const pct = IQ.total > 0 ? IQ.score / IQ.total : 0;
    const msgs = [
        [1,    'Perfect score! Outstanding!'],
        [0.9,  'Excellent work!'],
        [0.75, 'Great job!'],
        [0.5,  'Good effort!'],
        [0,    'Keep practicing!']
    ];
    const msg = msgs.find(function(m) { return pct >= m[0]; })[1];

    setTimeout(function() {
        const el2 = document.getElementById('iq-congrats-msg');
        el2.textContent = msg;
        el2.classList.add('visible');
    }, 500);

    // Streak row
    document.getElementById('iq-max-streak').textContent = IQ.maxStreak;
    setTimeout(function() {
        document.getElementById('iq-streak-row').classList.add('visible');
    }, 800);

    // Confetti
    spawnConfetti();

    // Finish the progress bar
    document.getElementById('iq-bar').style.width = '100%';
    document.getElementById('iq-section-label').textContent =
        'Completed! ' + IQ.totalSecs + ' of ' + IQ.totalSecs + ' sections';
}

function spawnConfetti() {
    const wrap   = document.getElementById('iq-confetti');
    wrap.innerHTML = '';
    const colors = ['#fff','#ffd700','#ff6b6b','#90ee90','#87ceeb','#ffb347'];
    const cx = window.innerWidth / 2;
    const cy = window.innerHeight / 2;

    for (let i = 0; i < 70; i++) {
        const d    = document.createElement('div');
        d.className = 'iq-cdot';
        const ang  = Math.random() * Math.PI * 2;
        const dist = 80 + Math.random() * Math.min(cx, cy) * 0.9;
        d.style.cssText =
            'left:' + cx + 'px;top:' + cy + 'px;' +
            'background:' + colors[i % colors.length] + ';' +
            '--tx:' + (Math.cos(ang) * dist).toFixed(1) + 'px;' +
            '--ty:' + (Math.sin(ang) * dist).toFixed(1) + 'px;' +
            'animation:iqBurst ' + (0.5 + Math.random() * 0.6).toFixed(2) +
            's ease-out ' + (Math.random() * 0.3).toFixed(2) + 's forwards;';
        wrap.appendChild(d);
    }
}

function iqShowReview() {
    // Close congrats overlay
    document.getElementById('iq-congrats').classList.remove('active');

    // Show sticky review header
    document.getElementById('iq-review-header').classList.add('active');

    // Hide the quiz HUD
    var topbar = document.querySelector('.iq-topbar');
    if (topbar) topbar.style.display = 'none';

    // Show all sections in review style; hide nav buttons
    for (var i = 0; i < IQ.totalSecs; i++) {
        var sec = document.getElementById('iq-sec-' + i);
        if (sec) {
            sec.style.display = 'block';
            sec.classList.add('iq-review-mode');
        }
        var nextBtn = document.getElementById('iq-next-' + i);
        if (nextBtn) nextBtn.style.display = 'none';
    }
    var finishBtn = document.getElementById('iq-finish');
    if (finishBtn) finishBtn.style.display = 'none';

    // Reveal correct answers for every question (answered or not)
    for (var si = 0; si < IQ.totalSecs; si++) {
        for (var qi = 0; qi < (IQ.qCounts[si] || 0); qi++) {
            var card = document.getElementById('iq-q-' + si + '-' + qi);
            if (!card) continue;
            var correctVal = card.dataset.answer;

            card.querySelectorAll('.iq-choice-btn').forEach(function(b) {
                b.disabled = true;
                if (b.dataset.value === correctVal) {
                    b.classList.remove('iq-incorrect');
                    b.classList.add('iq-correct');
                }
            });

            // If unanswered, show a "review" feedback note
            if (!card.dataset.answered) {
                var note = document.createElement('div');
                note.className = 'iq-review-answered-note';
                note.textContent = 'Correct answer highlighted above.';
                var fbEl = document.getElementById('iq-fb-' + si + '-' + qi);
                if (fbEl) {
                    var msg = fbEl.querySelector('.iq-fb-msg');
                    if (msg) msg.textContent = '';
                    fbEl.prepend(note);
                    fbEl.style.display = 'block';
                    fbEl.className = 'iq-feedback correct';
                }
            }
        }
    }

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

document.addEventListener('DOMContentLoaded', function() {
    hljs.highlightAll();
    updateHUD();
});
</script>

<!-- Satisfy footer's CodeMirror reference -->
<textarea id="code-editor" style="display:none;"></textarea>

<?php $this->load->view('footer') ?>

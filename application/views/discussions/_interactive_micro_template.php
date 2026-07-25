<?php
/*
 * _interactive_micro_template.php
 * ──────────────────────────────────────────────────────────────
 * Microlearning ("Sololearn-style") Interactive Quiz template.
 *
 * Sibling of _interactive_quiz_template.php, NOT a replacement:
 * that one renders one lesson + one 4-option MCQ per section. This
 * one renders a denser format — each section is a run of 1-2 sentence
 * chunks, every chunk followed by a 2-option micro-check, closed by a
 * section checkpoint that rotates between mcq / arrange / type items,
 * with an objectives screen first and a recap screen last.
 *
 * Rendered by InteractiveQuizController::micro(), which is what the
 * `iq_micro` widget redirects students to.
 *
 * JSON FORMAT  (assets/json/{CLASS}/{topic}.json)
 * ──────────────────────────────────────────────────────────────
 * {
 *   "topic": "html_forms_v3_prod",
 *   "title": "HTML Forms",
 *   "description": "...",
 *   "congratsText": "You mastered HTML Forms!",
 *   "objectives": ["Explain ...", "Configure ..."],
 *   "recap":      ["Forms collect ...", "action says where ..."],
 *   "sections": [
 *     {
 *       "id": 0,
 *       "title": "What Is an HTML Form?",
 *       "callback": false,          // true => this checkpoint tests an earlier section
 *       "refSection": null,         // id (or "1, 2 & 4") of the section being called back to
 *       "chunks": [
 *         { "text": "One idea. <span class=\"highlight\">key term</span> or `inline code`.",
 *           "check": { "question": "...", "options": ["A", "B"], "correct": 0 } }
 *       ],
 *       "lesson": "<div class=\"lesson-title\">Checkpoint: ...</div>...",
 *       "quiz": { "type": "mcq",     "question": "...", "code": "optional",
 *                 "options": ["A","B","C","D"], "correct": 0 }
 *             // or { "type": "arrange", "question": "...",
 *             //      "tokens": [...], "correctOrder": [...] }
 *             // or { "type": "type",    "question": "...", "code": "optional",
 *             //      "acceptedAnswers": ["label", "<label>"] }
 *     }
 *   ]
 * }
 *
 * SCORING
 * ──────────────────────────────────────────────────────────────
 * 1 point per micro-check + 1 point per checkpoint. Objectives and
 * recap screens are not graded. max_score is derived from exactly
 * this count server-side (AdminController::_count_micro_topic_items()),
 * so a perfect run always equals the assessment's max.
 *
 * Unlike _interactive_quiz_template.php, the score here is RECOMPUTED
 * from a per-screen results map rather than incremented in place, so
 * Back-navigation can't double-count — the Back button is therefore
 * always available, not just on retakes.
 * ──────────────────────────────────────────────────────────────
 */

if (empty($topic_data)) {
    $json_path  = FCPATH . 'assets/json/' . ($topic_file ?? 'sample') . '.json';
    $topic_data = json_decode(file_get_contents($json_path), true);
}

$title             = htmlspecialchars($topic_data['title']        ?? 'Interactive Quiz');
$congrats_text     = htmlspecialchars($topic_data['congratsText'] ?? 'You completed this lesson!');
$topic_slug        = $topic_data['topic'] ?? '';
$assessment_id     = isset($assessment_id) ? (int) $assessment_id : 0;
$already_submitted = !empty($already_submitted);
$previous_score    = $previous_score   ?? null;
$previous_answers  = $previous_answers ?? [];

// JSON_HEX_TAG|JSON_HEX_AMP so authored HTML inside lesson/chunk text
// can never close this <script> block — same guard the sibling template uses.
$sections_json   = json_encode($topic_data['sections']   ?? [], JSON_HEX_TAG | JSON_HEX_AMP);
$objectives_json = json_encode($topic_data['objectives'] ?? [], JSON_HEX_TAG | JSON_HEX_AMP);
$recap_json      = json_encode($topic_data['recap']      ?? [], JSON_HEX_TAG | JSON_HEX_AMP);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= $title ?> - Interactive Learning</title>
    <link rel="stylesheet" href="<?= base_url('assets/interactive-quiz-style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/interactive-micro-style.css') ?>">
</head>
<body>
    <div class="container">

        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <button class="header-close" onclick="exitQuiz()">&#x2715;</button>
                <div class="progress-section">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <div class="header-score">
                    <span>&#x2B50;</span>
                    <span id="score">0</span>
                </div>
            </div>
        </div>

        <?php if ($already_submitted): ?>
            <div class="already-submitted-banner">
                Already completed &mdash; recorded score: <strong><?= (int) $previous_score ?></strong>.
                You can retake for practice, but it won't change your recorded score.
            </div>
        <?php endif; ?>

        <!-- Main Content -->
        <div class="content-wrapper" id="contentWrapper">
            <div class="content-scroll">
                <div class="section-container">
                    <div class="lesson-section" id="lessonSection"></div>

                    <div class="quiz-section" id="quizSection">
                        <div class="item-tag" id="itemTag"></div>
                        <div class="question-text" id="questionText"></div>
                        <div id="answerArea"></div>
                        <div class="feedback" id="feedback"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Button Section -->
        <div class="button-section">
            <button class="btn-back" id="backBtn" onclick="previousScreen()">&#x2190; Back</button>
            <button class="btn-submit" id="submitBtn" onclick="submitAnswer()">Submit</button>
        </div>
    </div>

    <!-- Modals -->
    <div class="modal-backdrop" id="backdrop"></div>
    <div class="streak-popup" id="streakPopup">
        <div class="streak-emoji">&#x1F525;</div>
        <div class="streak-text"><span id="streakCount">3</span> in a row!</div>
        <div class="streak-subtext">Keep it up!</div>
    </div>

    <div class="modal-backdrop" id="congratsBackdrop"></div>
    <div class="congrats-modal" id="congratsModal">
        <div class="congrats-emoji">&#x1F389;</div>
        <div class="congrats-title">Congratulations!</div>
        <div class="congrats-text"><?= $congrats_text ?></div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-label">Final Score</div>
                <div class="stat-card-value" id="finalScore">0</div>
            </div>
            <div class="stat-card success">
                <div class="stat-card-label">Best Streak</div>
                <div class="stat-card-value" id="bestStreak">0</div>
            </div>
        </div>

        <button class="congrats-button" onclick="restartQuiz()">Start Over</button>
        <button class="congrats-button" style="background:transparent; border:2px solid #357abd; color:#357abd; margin-top:0.75rem;" onclick="exportMicroImage()">Save as Image</button>
    </div>

    <script src="<?= base_url('assets/html2canvas.min.js') ?>"></script>

    <script>
        // ── Topic data (injected from PHP) ──────────────────────────
        const sections      = <?= $sections_json ?>;
        const OBJECTIVES    = <?= $objectives_json ?>;
        const RECAP         = <?= $recap_json ?>;
        const TOPIC_SLUG    = <?= json_encode($topic_slug) ?>;
        const TOPIC_TITLE   = <?= json_encode($topic_data['title'] ?? '') ?>;
        const BASE_URL      = <?= json_encode(base_url()) ?>;
        const ASSESSMENT_ID = <?= $assessment_id ?>;
        const PREVIOUS_SCORE    = <?= json_encode($previous_score) ?>;
        const ALREADY_SUBMITTED = <?= $already_submitted ? 'true' : 'false' ?>;
        const TOPICS_URL        = <?= json_encode(base_url('classwork')) ?>;

        // ── Flatten sections into a linear screen list ──────────────
        // objectives -> [chunk, chunk, ... , checkpoint] per section -> recap.
        // One question per screen; `graded` marks the ones worth a point.
        const screens = [];
        screens.push({ kind: 'objectives', graded: false, items: OBJECTIVES });
        sections.forEach((s, si) => {
            (s.chunks || []).forEach((c, ci) => screens.push({
                kind: 'micro', graded: true, si: si,
                sectionTitle: s.title || '',
                text: c.text || '', check: c.check || null,
                chunkNum: ci + 1, chunkTotal: (s.chunks || []).length
            }));
            if (s.quiz) {
                screens.push({
                    kind: 'checkpoint', graded: true, si: si,
                    sectionTitle: s.title || '',
                    lesson: s.lesson || '', quiz: s.quiz,
                    callback: !!s.callback, refSection: s.refSection
                });
            }
        });
        screens.push({ kind: 'recap', graded: false, items: RECAP });

        // ── State ───────────────────────────────────────────────────
        // results[screenIndex] is the single source of truth for the score —
        // it is never mutated once written, and score/streak are recomputed
        // from it, so Back-nav and re-renders can't inflate anything.
        let currentScreen = 0;
        let results       = {};
        let shuffleCache  = {}; // screenIndex -> {options, correctIndex}, frozen at first render
        let score         = 0;
        let bestStreak    = 0;
        let streakHighlight = false;
        let congratsShown   = false;

        // Live (unsubmitted) input for the screen being viewed
        let selectedOption = null;
        let builtTokens    = [];
        let usedTokenFlags = [];

        const recordedScreens = new Set(); // record_attempt de-dupe

        // ── Helpers ─────────────────────────────────────────────────
        function escapeHtml(str) {
            return String(str == null ? '' : str)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        // Question/option text is escaped (so `<form>` in a question renders as
        // text instead of vanishing into the DOM) then `backticks` become inline
        // code spans. Lesson/chunk bodies go through fmtRich() instead — those
        // are authored HTML, same trust level as the sibling template's `lesson`.
        function fmt(str) {
            return escapeHtml(str).replace(/`([^`]+)`/g, '<span class="code-inline">$1</span>');
        }

        function fmtRich(str) {
            return String(str == null ? '' : str)
                .replace(/`([^`]+)`/g, '<span class="code-inline">$1</span>');
        }

        function normalize(str) {
            return String(str).trim().toLowerCase().replace(/;+\s*$/, '').replace(/\s+/g, ' ');
        }

        function shuffleArray(array) {
            const shuffled = [...array];
            for (let i = shuffled.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
            }
            return shuffled;
        }

        // Shuffled once per screen and cached — going Back must show the same
        // option order the answer was recorded against.
        function optionsFor(screenIdx, options, correctIndex) {
            if (!shuffleCache[screenIdx]) {
                const tagged   = options.map((text, i) => ({ text, originalIndex: i }));
                const shuffled = shuffleArray(tagged);
                shuffleCache[screenIdx] = {
                    options:      shuffled.map(o => o.text),
                    correctIndex: shuffled.findIndex(o => o.originalIndex === correctIndex)
                };
            }
            return shuffleCache[screenIdx];
        }

        function currentQuestion(s) {
            return s.kind === 'micro' ? s.check : s.quiz;
        }

        function itemTypeOf(s) {
            return s.kind === 'micro' ? 'mcq' : (s.quiz.type || 'mcq');
        }

        // ── Render ──────────────────────────────────────────────────
        function renderScreen() {
            const s = screens[currentScreen];

            selectedOption = null;
            builtTokens    = [];
            usedTokenFlags = [];

            const feedback = document.getElementById('feedback');
            feedback.className   = 'feedback';
            feedback.textContent = '';
            document.getElementById('answerArea').innerHTML  = '';
            document.getElementById('questionText').innerHTML = '';
            document.getElementById('contentWrapper').scrollTop = 0;

            if (s.kind === 'objectives' || s.kind === 'recap') {
                renderListScreen(s);
            } else if (s.kind === 'micro') {
                renderMicroScreen(s);
            } else {
                renderCheckpointScreen(s);
            }

            if (results[currentScreen]) applyRecordedResult(currentScreen);

            updateUI();
        }

        function renderListScreen(s) {
            const isObjectives = s.kind === 'objectives';
            const heading = isObjectives
                ? escapeHtml(TOPIC_TITLE)
                : 'Recap: ' + escapeHtml(TOPIC_TITLE);
            const intro = isObjectives
                ? "By the end of this lesson, you'll be able to:"
                : "Here's what you covered:";
            const items = (s.items || []).map(i => `<li>${fmt(i)}</li>`).join('');

            document.getElementById('lessonSection').innerHTML =
                `<div class="lesson-title">${heading}</div>` +
                `<div class="lesson-text">${intro}</div>` +
                `<ul class="screen-list">${items}</ul>`;
            document.getElementById('itemTag').innerHTML =
                `<span class="item-tag tag-micro">${isObjectives ? 'Objectives' : 'Recap'}</span>`;
        }

        function renderMicroScreen(s) {
            document.getElementById('lessonSection').innerHTML =
                `<div class="chunk-text">${fmtRich(s.text)}</div>`;
            document.getElementById('itemTag').innerHTML =
                `<span class="item-tag tag-micro">${s.chunkNum} / ${s.chunkTotal} &middot; ${escapeHtml(s.sectionTitle)}</span>`;
            document.getElementById('questionText').innerHTML = fmt(s.check.question);

            const shuffled = optionsFor(currentScreen, s.check.options, s.check.correct);
            renderMcqOptions(shuffled.options);
        }

        function renderCheckpointScreen(s) {
            document.getElementById('lessonSection').innerHTML = fmtRich(s.lesson);
            document.getElementById('itemTag').innerHTML = s.callback
                ? `<span class="item-tag tag-callback">Callback &rarr; Section ${escapeHtml(s.refSection)}</span>`
                : `<span class="item-tag tag-checkpoint">Checkpoint</span>`;

            let html = fmt(s.quiz.question);
            if (s.quiz.code) html += `<div class="question-code">${escapeHtml(s.quiz.code)}</div>`;
            document.getElementById('questionText').innerHTML = html;

            if (s.quiz.type === 'arrange') {
                renderArrange(s.quiz);
            } else if (s.quiz.type === 'type') {
                renderTypeInput();
            } else {
                renderMcqOptions(optionsFor(currentScreen, s.quiz.options, s.quiz.correct).options);
            }
        }

        function renderMcqOptions(optionTexts) {
            const area = document.getElementById('answerArea');
            area.innerHTML = '<div class="options" id="optionsContainer"></div>';
            const container = document.getElementById('optionsContainer');
            optionTexts.forEach((text, index) => {
                const opt = document.createElement('div');
                opt.className     = 'option';
                opt.innerHTML     = fmt(text);
                opt.dataset.index = index;
                opt.onclick       = () => selectOption(index);
                container.appendChild(opt);
            });
        }

        function renderArrange(quiz) {
            const tokens = quiz.tokens || [];
            usedTokenFlags = new Array(tokens.length).fill(false);

            document.getElementById('answerArea').innerHTML =
                `<div class="built-row" id="builtRow"></div>` +
                `<div class="token-pool" id="tokenPool"></div>` +
                `<div class="arrange-actions">` +
                `<button class="btn-clear-tokens" id="clearTokensBtn" type="button">Clear</button>` +
                `</div>`;

            const pool = document.getElementById('tokenPool');
            tokens.forEach((tok, idx) => {
                const chip = document.createElement('div');
                chip.className   = 'token-chip';
                chip.textContent = tok;
                chip.dataset.idx = idx;
                chip.onclick     = () => {
                    if (results[currentScreen] || usedTokenFlags[idx]) return;
                    enterFullscreen();
                    usedTokenFlags[idx] = true;
                    chip.classList.add('used');
                    builtTokens.push({ text: tok, idx: idx });
                    renderBuiltRow();
                };
                pool.appendChild(chip);
            });

            document.getElementById('clearTokensBtn').onclick = () => {
                if (results[currentScreen]) return;
                builtTokens = [];
                usedTokenFlags.fill(false);
                document.querySelectorAll('.token-chip').forEach(c => c.classList.remove('used'));
                renderBuiltRow();
            };

            renderBuiltRow();
        }

        // Tapping a placed chip returns it to the pool — without this, one
        // mis-tap forces a full Clear.
        function renderBuiltRow() {
            const row = document.getElementById('builtRow');
            if (!row) return;
            if (!builtTokens.length) {
                row.innerHTML = '<span class="built-placeholder">Tap the tokens below, in order.</span>';
                return;
            }
            row.innerHTML = '';
            builtTokens.forEach((t, pos) => {
                const chip = document.createElement('span');
                chip.className   = 'built-chip';
                chip.textContent = t.text;
                chip.onclick     = () => {
                    if (results[currentScreen]) return;
                    builtTokens.splice(pos, 1);
                    usedTokenFlags[t.idx] = false;
                    const poolChip = document.querySelector(`.token-chip[data-idx="${t.idx}"]`);
                    if (poolChip) poolChip.classList.remove('used');
                    renderBuiltRow();
                };
                row.appendChild(chip);
            });
        }

        function renderTypeInput() {
            document.getElementById('answerArea').innerHTML =
                `<input type="text" class="type-input" id="typeInput" placeholder="Type your answer..." autocomplete="off">`;
            const input = document.getElementById('typeInput');
            input.addEventListener('focus', enterFullscreen);
            input.addEventListener('keydown', e => {
                if (e.key === 'Enter') { e.preventDefault(); submitAnswer(); }
            });
        }

        // ── Interaction ─────────────────────────────────────────────
        function selectOption(index) {
            if (results[currentScreen]) return;
            enterFullscreen();
            document.querySelectorAll('.option').forEach(o => o.classList.remove('selected'));
            const el = document.querySelector(`.option[data-index="${index}"]`);
            if (el) el.classList.add('selected');
            selectedOption = index;
        }

        function submitAnswer() {
            const s = screens[currentScreen];

            // Ungraded screens and already-answered screens are just "Next".
            if (!s.graded || results[currentScreen]) { nextScreen(); return; }

            const type = itemTypeOf(s);
            let correct, chosen, correctAnswer;

            if (type === 'arrange') {
                if (!builtTokens.length) { alert('Arrange the tokens first!'); return; }
                const built   = builtTokens.map(t => t.text);
                const expected = s.quiz.correctOrder || [];
                correct       = JSON.stringify(built) === JSON.stringify(expected);
                chosen        = built.join(' ');
                correctAnswer = expected.join(' ');
            } else if (type === 'type') {
                const val = document.getElementById('typeInput').value;
                if (!val.trim()) { alert('Type your answer first!'); return; }
                const accepted = s.quiz.acceptedAnswers || [];
                correct       = accepted.some(a => normalize(a) === normalize(val));
                chosen        = val.trim();
                correctAnswer = accepted[0] || '';
            } else {
                if (selectedOption === null) { alert('Please select an option!'); return; }
                const cache   = shuffleCache[currentScreen];
                correct       = selectedOption === cache.correctIndex;
                chosen        = cache.options[selectedOption];
                correctAnswer = cache.options[cache.correctIndex];
            }

            const q = currentQuestion(s);
            results[currentScreen] = {
                kind:           s.kind,
                section:        s.si,
                section_title:  s.sectionTitle,
                question:       q.question || '',
                chosen:         chosen,
                correct_answer: correctAnswer,
                is_correct:     correct,
                selected:       selectedOption,
                built:          builtTokens.map(t => t.idx)
            };

            recomputeStats();
            applyRecordedResult(currentScreen);
            recordAttempt(currentScreen);
            updateUI();

            if (correct && currentStreak() > 0 && currentStreak() % 3 === 0) {
                streakHighlight = true;
                showStreakPopup(currentStreak());
            } else if (!correct) {
                streakHighlight = false;
            }
        }

        // Re-paints a screen into its graded state — used both right after
        // submitting and when navigating back to an answered screen.
        function applyRecordedResult(idx) {
            const r = results[idx];
            const s = screens[idx];
            if (!r) return;

            const type     = itemTypeOf(s);
            const feedback = document.getElementById('feedback');

            if (type === 'arrange') {
                const row = document.getElementById('builtRow');
                if (row) {
                    builtTokens    = (r.built || []).map(i => ({ text: (s.quiz.tokens || [])[i], idx: i }));
                    usedTokenFlags = new Array((s.quiz.tokens || []).length).fill(false);
                    r.built.forEach(i => { usedTokenFlags[i] = true; });
                    renderBuiltRow();
                    row.classList.add(r.is_correct ? 'correct' : 'incorrect');
                }
                document.querySelectorAll('.token-chip').forEach(c => {
                    c.classList.toggle('used', usedTokenFlags[parseInt(c.dataset.idx, 10)]);
                    c.style.pointerEvents = 'none';
                });
                const clearBtn = document.getElementById('clearTokensBtn');
                if (clearBtn) clearBtn.style.display = 'none';
            } else if (type === 'type') {
                const input = document.getElementById('typeInput');
                if (input) {
                    input.value    = r.chosen;
                    input.disabled = true;
                    input.classList.add(r.is_correct ? 'correct' : 'incorrect');
                }
            } else {
                const cache = shuffleCache[idx];
                document.querySelectorAll('.option').forEach(o => o.classList.add('disabled'));
                const sel  = document.querySelector(`.option[data-index="${r.selected}"]`);
                const corr = cache ? document.querySelector(`.option[data-index="${cache.correctIndex}"]`) : null;
                if (r.is_correct) {
                    if (sel) sel.classList.add('correct');
                } else {
                    if (sel)  sel.classList.add('incorrect');
                    if (corr) corr.classList.add('correct');
                }
            }

            if (r.is_correct) {
                feedback.className   = 'feedback show correct';
                feedback.textContent = '✓ Correct! +1 point';
            } else {
                feedback.className = 'feedback show incorrect';
                feedback.innerHTML = '✗ Incorrect. The correct answer is: ' + fmt(r.correct_answer);
            }
        }

        // ── Score / streak (always derived, never incremented) ───────
        function gradedOrder() {
            return screens.map((s, i) => i).filter(i => screens[i].graded);
        }

        function recomputeStats() {
            let s = 0, run = 0, best = 0;
            gradedOrder().forEach(i => {
                const r = results[i];
                if (!r) return;
                if (r.is_correct) { s++; run++; best = Math.max(best, run); }
                else { run = 0; }
            });
            score      = s;
            bestStreak = best;
        }

        // Current run length up to and including the latest answered screen.
        function currentStreak() {
            let run = 0;
            gradedOrder().forEach(i => {
                const r = results[i];
                if (!r) return;
                run = r.is_correct ? run + 1 : 0;
            });
            return run;
        }

        // ── Analytics ───────────────────────────────────────────────
        // Only 4-option checkpoint MCQs are reported: the analytics/
        // discussion_results views chart a per-section choice distribution
        // against section.quiz.options, which arrange/type items don't have,
        // and micro-checks are formative rather than assessed.
        function recordAttempt(idx) {
            const s = screens[idx];
            const r = results[idx];
            if (!TOPIC_SLUG || recordedScreens.has(idx)) return;
            if (s.kind !== 'checkpoint' || (s.quiz.type || 'mcq') !== 'mcq') return;
            recordedScreens.add(idx);

            fetch(BASE_URL + 'interactive_quiz/record_attempt', {
                method:  'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body:    new URLSearchParams({
                    topic:          TOPIC_SLUG,
                    section_index:  s.si,
                    section_title:  s.sectionTitle,
                    question_index: 0,
                    question_text:  r.question,
                    is_correct:     r.is_correct ? '1' : '0',
                    chosen_option:  r.chosen
                })
            }).catch(() => {});
        }

        // ── Modals ──────────────────────────────────────────────────
        function showStreakPopup(count) {
            const popup    = document.getElementById('streakPopup');
            const backdrop = document.getElementById('backdrop');
            document.getElementById('streakCount').textContent = count;
            popup.classList.add('show');
            backdrop.classList.add('show');
            setTimeout(() => {
                popup.classList.remove('show');
                backdrop.classList.remove('show');
            }, 1500);
        }

        function showCongratsModal() {
            congratsShown = true;
            document.getElementById('finalScore').textContent = score;
            document.getElementById('bestStreak').textContent = bestStreak;
            document.getElementById('congratsModal').classList.add('show');
            document.getElementById('congratsBackdrop').classList.add('show');

            // save_result() records the FIRST completion only — a retake posts
            // but is refused server-side, so this needs no client-side guard.
            if (ASSESSMENT_ID) {
                fetch(BASE_URL + 'interactive_quiz/save_result', {
                    method: 'POST',
                    headers: {
                        'Content-Type':     'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams({
                        assessment_id: ASSESSMENT_ID,
                        score:         score,
                        answers:       JSON.stringify(orderedResults())
                    })
                }).catch(() => {});
            }
        }

        function orderedResults() {
            return gradedOrder().filter(i => results[i]).map(i => results[i]);
        }

        // ── Navigation ──────────────────────────────────────────────
        function nextScreen() {
            if (currentScreen < screens.length - 1) {
                currentScreen++;
                renderScreen();
            } else {
                showCongratsModal();
            }
        }

        function previousScreen() {
            if (currentScreen > 0) { currentScreen--; renderScreen(); }
        }

        function restartQuiz() {
            currentScreen   = 0;
            results         = {};
            shuffleCache    = {};
            score           = 0;
            bestStreak      = 0;
            streakHighlight = false;
            congratsShown   = false;
            recordedScreens.clear();
            document.getElementById('congratsModal').classList.remove('show');
            document.getElementById('congratsBackdrop').classList.remove('show');
            renderScreen();
        }

        // ── UI sync ─────────────────────────────────────────────────
        function updateUI() {
            document.getElementById('score').textContent = score;

            const fill = document.getElementById('progressFill');
            fill.style.width = `${((currentScreen + 1) / screens.length) * 100}%`;
            fill.classList.toggle('streak-active', streakHighlight);

            document.getElementById('backBtn').disabled = currentScreen === 0;

            const s      = screens[currentScreen];
            const isLast = currentScreen === screens.length - 1;
            const btn    = document.getElementById('submitBtn');

            if (s.kind === 'objectives') {
                btn.textContent = "Let's Begin →";
            } else if (!s.graded || results[currentScreen]) {
                btn.textContent = isLast ? 'Finish' : 'Next →';
            } else {
                btn.textContent = 'Submit';
            }
        }

        // ── Fullscreen / exit ───────────────────────────────────────
        function enterFullscreen() {
            const el  = document.documentElement;
            const req = el.requestFullscreen || el.webkitRequestFullscreen
                     || el.mozRequestFullScreen || el.msRequestFullscreen;
            if (req) req.call(el).catch(() => {});
        }

        function exitQuiz() {
            const inFullscreen = document.fullscreenElement || document.webkitFullscreenElement;
            if (inFullscreen) {
                (document.exitFullscreen || document.webkitExitFullscreen
                    || document.mozCancelFullScreen || document.msExitFullscreen)
                    .call(document)
                    .finally(() => { window.location.href = TOPICS_URL; });
            } else {
                window.location.href = TOPICS_URL;
            }
        }

        // ── Image export (whole lesson + what was picked) ────────────
        function exportMicroImage() {
            const el = document.createElement('div');
            el.style.cssText = 'position:fixed; left:-9999px; top:0; width:700px; background:#fff; padding:24px; font-family:Arial, sans-serif; color:#222;';

            const scoreLine = (PREVIOUS_SCORE !== null && PREVIOUS_SCORE !== undefined)
                ? `Recorded score: ${PREVIOUS_SCORE}`
                : `Score: ${score}`;

            let html = `<h2 style="margin:0 0 4px;">${escapeHtml(TOPIC_TITLE) || 'Microlearning Quiz'}</h2>`;
            html += `<p style="color:#666; margin:0 0 18px;">${scoreLine}</p>`;

            let lastSection = -1;
            gradedOrder().forEach(i => {
                const s = screens[i];
                const r = results[i];

                if (s.si !== lastSection) {
                    lastSection = s.si;
                    html += `<h3 style="margin:18px 0 8px;">${s.si + 1}. ${escapeHtml(s.sectionTitle)}</h3>`;
                }

                html += `<div style="margin-bottom:12px; padding-bottom:10px; border-bottom:1px solid #eee;">`;
                if (s.kind === 'micro') {
                    html += `<div style="margin-bottom:6px; line-height:1.5;">${fmtRich(s.text)}</div>`;
                }
                html += `<p style="font-weight:700; margin:0 0 4px;">Q: ${fmt(currentQuestion(s).question || '')}</p>`;
                if (r) {
                    html += `<p style="margin:0;">Your answer: <span style="color:${r.is_correct ? '#1a7a1a' : '#c0392b'}; font-weight:700;">${escapeHtml(r.chosen)}</span></p>`;
                    if (!r.is_correct) {
                        html += `<p style="margin:0;">Correct answer: <span style="color:#1a7a1a; font-weight:700;">${escapeHtml(r.correct_answer)}</span></p>`;
                    }
                } else {
                    html += `<p style="margin:0; color:#999;">Not answered.</p>`;
                }
                html += `</div>`;
            });

            el.innerHTML = html;
            document.body.appendChild(el);

            html2canvas(el, { scale: 2, useCORS: true }).then(canvas => {
                document.body.removeChild(el);
                const link = document.createElement('a');
                link.download = (TOPIC_SLUG || 'microlearning') + '_review.png';
                link.href = canvas.toDataURL('image/png');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }).catch(() => {
                if (el.parentNode) document.body.removeChild(el);
                alert('Could not generate image. Please try again.');
            });
        }

        window.addEventListener('load', renderScreen);
    </script>
</body>
</html>

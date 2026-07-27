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
 *
 * GROUP MODE
 * ──────────────────────────────────────────────────────────────
 * Set by GroupWorkController::_render_group_iq() for a grouping
 * assessment. Unlike the sibling discussion template (where every
 * member answers independently and races are tolerated), this format
 * has a stricter rule: only ONE member — the "driver" — may answer or
 * navigate at any time. Everyone else watches read-only. The group
 * picks who starts (a picker modal at load), and the driver can hand
 * control to a teammate at any point via "Pass to...". This sidesteps
 * syncing a free-typed `type` answer keystroke-by-keystroke — the
 * driver simply types, and the answer syncs once on Submit, same as
 * every other answer shape here.
 *
 * Shared blob (assessment_live_state.content, whole-blob save_draft path):
 *   {
 *     "v": 12, "by": "<student_id>", "driver": "<student_id>|null",
 *     "currentScreen": 7, "finished": false,
 *     "answers": {
 *       "0:0": { "sel": 1 },              // section 0, chunk 0 (micro-check)
 *       "0:q": { "sel": 0 },              // section 0 checkpoint (mcq)
 *       "1:q": { "built": [1,3,0,2] },    // section 1 checkpoint (arrange) — token indices
 *       "2:q": { "text": "CI_Controller" } // section 2 checkpoint (type)
 *     }
 *   }
 * Keys are "{sectionIndex}:{chunkIndex}" / "{sectionIndex}:q" — stable
 * regardless of the objectives/recap screens the client flattens
 * sections into, and reconstructible server-side by walking
 * topic_data.sections the same way (Iq_topic_model::grade_micro()).
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

// ── Group (shared/synced, single-driver) mode ────────────────────
// Set by GroupWorkController::_render_group_iq(). See docblock above.
$group_mode       = !empty($group_mode);
$group            = $group ?? null;
$group_members    = $group_members ?? [];
$state_content    = $state_content ?? '';
$state_updated_at = $state_updated_at ?? '';
$my_student_id = isset($student_id) ? (string) $student_id : '';
$group_name    = $group['group_name'] ?? '';
$group_member_js = array_map(function ($m) {
    return [
        'id'   => (string) $m['student_id'],
        'name' => trim(($m['firstname'] ?? '') . ' ' . ($m['lastname'] ?? '')),
    ];
}, $group_members);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title><?= $title ?> - Interactive Learning</title>
    <link rel="stylesheet" href="<?= base_url('assets/interactive-quiz-style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/interactive-micro-style.css') ?>">
    <?php if ($group_mode): ?>
    <style>
        .group-bar { display:flex; flex-wrap:wrap; gap:6px; align-items:center; justify-content:center;
            padding:6px 10px; background:rgba(53,122,189,0.10); border-bottom:1px solid rgba(53,122,189,0.2); }
        .group-bar .group-label { font-size:12px; font-weight:700; color:#357abd; margin-right:4px; }
        .group-bar .group-chip { font-size:12px; background:#357abd; color:#fff; border-radius:12px; padding:2px 10px; }
        .group-bar .group-chip-driver { background:#e08a00; }
        .iq-spectator .options .option,
        .iq-spectator .token-pool .token-chip,
        .iq-spectator .built-row .built-chip,
        .iq-spectator .type-input,
        .iq-spectator .arrange-actions .btn-clear-tokens {
            pointer-events: none;
            opacity: 0.55;
        }
        .driver-list { display:flex; flex-direction:column; gap:6px; margin-top:1rem; }
        .driver-list .congrats-button { width:100%; }
        #passBtn, #takeoverBtn { background:transparent; border:2px solid #357abd; color:#357abd; }
    </style>
    <?php endif; ?>
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

        <?php if ($group_mode): ?>
            <div class="group-bar" id="groupBar"></div>
        <?php endif; ?>

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
            <?php if ($group_mode): ?>
            <button class="btn-back" id="passBtn" onclick="openDriverPicker(false)" style="display:none;">Pass to&hellip;</button>
            <button class="btn-back" id="takeoverBtn" onclick="claimTakeover()" style="display:none;">Take over</button>
            <?php endif; ?>
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

    <?php if ($group_mode): ?>
    <div class="modal-backdrop" id="driverBackdrop"></div>
    <div class="congrats-modal" id="driverModal">
        <div class="congrats-title">Who answers first?</div>
        <div class="congrats-text">Pick one teammate to control this quiz. Control can be passed to someone else at any time.</div>
        <div class="driver-list" id="driverList"></div>
    </div>
    <?php endif; ?>

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

        // ── Group mode (shared/synced, single-driver play) ───────────
        const GROUP_MODE    = <?= $group_mode ? 'true' : 'false' ?>;
        const GROUP_NAME    = <?= json_encode($group_name) ?>;
        const MY_STUDENT_ID = <?= json_encode($my_student_id) ?>;
        const GROUP_MEMBERS = <?= json_encode($group_member_js) ?>;
        const GROUP_STATE_INIT = <?= ($group_mode && $state_content !== '' && json_decode($state_content) !== null) ? $state_content : 'null' ?>;

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

        // Reverse lookup: shared-blob answer key -> screen index. Built once —
        // objectives/recap screens have no key (ungraded, never in `answers`).
        const screenIndexByKey = {};
        screens.forEach((s, i) => {
            if (s.kind === 'micro') screenIndexByKey[s.si + ':' + (s.chunkNum - 1)] = i;
            else if (s.kind === 'checkpoint') screenIndexByKey[s.si + ':q'] = i;
        });
        function answerKeyFor(screenIdx) {
            const s = screens[screenIdx];
            return s.kind === 'micro' ? (s.si + ':' + (s.chunkNum - 1)) : (s.si + ':q');
        }
        function screenIndexForKey(key) {
            return screenIndexByKey.hasOwnProperty(key) ? screenIndexByKey[key] : -1;
        }

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

        // ── Group sync state ────────────────────────────────────────
        let groupState = {
            v: 0, driver: null, currentScreen: 0, finished: false, by: MY_STUDENT_ID, answers: {}
        };
        let lastRemoteV      = 0;
        let applyingRemote   = false;
        let groupPollTimer   = null;
        let lastVersion      = <?= json_encode((string) $state_updated_at) ?>;
        let groupPollCount   = 0;
        let pushInFlight     = false;
        let pushDirty        = false;
        let pushRetryDelay   = 2000;
        let lastStateChangeAt = Date.now();

        function isDriver() {
            return GROUP_MODE && !!groupState.driver && groupState.driver === MY_STUDENT_ID;
        }

        // Only the driver may answer or navigate; everyone else watches.
        function canAnswer() {
            return !GROUP_MODE || isDriver();
        }

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
        // option order the answer was recorded against. Group mode keeps a
        // deterministic (unshuffled) order instead — every member must see the
        // same options, and answers are graded server-side against the
        // original array index (Iq_topic_model::grade_micro()).
        function optionsFor(screenIdx, options, correctIndex) {
            if (!shuffleCache[screenIdx]) {
                if (GROUP_MODE) {
                    shuffleCache[screenIdx] = { options: [...options], correctIndex };
                } else {
                    const tagged   = options.map((text, i) => ({ text, originalIndex: i }));
                    const shuffled = shuffleArray(tagged);
                    shuffleCache[screenIdx] = {
                        options:      shuffled.map(o => o.text),
                        correctIndex: shuffled.findIndex(o => o.originalIndex === correctIndex)
                    };
                }
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
                    if (results[currentScreen] || usedTokenFlags[idx] || !canAnswer()) return;
                    enterFullscreen();
                    usedTokenFlags[idx] = true;
                    chip.classList.add('used');
                    builtTokens.push({ text: tok, idx: idx });
                    renderBuiltRow();
                };
                pool.appendChild(chip);
            });

            document.getElementById('clearTokensBtn').onclick = () => {
                if (results[currentScreen] || !canAnswer()) return;
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
                    if (results[currentScreen] || !canAnswer()) return;
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
                `<input type="text" class="type-input" id="typeInput" placeholder="Type your answer..." autocomplete="off"${canAnswer() ? '' : ' readonly'}>`;
            const input = document.getElementById('typeInput');
            input.addEventListener('focus', enterFullscreen);
            input.addEventListener('keydown', e => {
                if (e.key === 'Enter') { e.preventDefault(); submitAnswer(); }
            });
        }

        // ── Interaction ─────────────────────────────────────────────
        function selectOption(index) {
            if (results[currentScreen] || !canAnswer()) return;
            enterFullscreen();
            document.querySelectorAll('.option').forEach(o => o.classList.remove('selected'));
            const el = document.querySelector(`.option[data-index="${index}"]`);
            if (el) el.classList.add('selected');
            selectedOption = index;
        }

        // Builds the raw answer record from whatever the current screen's live
        // input is — shape depends on the checkpoint/micro-check type. Also
        // what gets stored in groupState.answers[key] for group-mode sync.
        function buildRawAnswer(type) {
            if (type === 'arrange') {
                return { built: builtTokens.map(t => t.idx) };
            }
            if (type === 'type') {
                return { text: document.getElementById('typeInput').value };
            }
            return { sel: selectedOption };
        }

        // Grades a raw answer (local or synced from a group teammate) against
        // the topic JSON — single implementation shared by the driver's own
        // submit and by rebuilding results{} from a synced groupState.answers
        // entry, so local and remote answers are scored identically.
        function gradeAnswer(screenIdx, raw) {
            const s    = screens[screenIdx];
            const type = itemTypeOf(s);
            const q    = currentQuestion(s);
            let correct, chosen, correctAnswer, selected = null, built = [];

            if (type === 'arrange') {
                const tokens    = q.tokens || [];
                built           = raw.built || [];
                const builtText = built.map(i => tokens[i]);
                const expected  = q.correctOrder || [];
                correct       = builtText.length > 0 && JSON.stringify(builtText) === JSON.stringify(expected);
                chosen        = builtText.join(' ');
                correctAnswer = expected.join(' ');
            } else if (type === 'type') {
                const val      = raw.text || '';
                const accepted = q.acceptedAnswers || [];
                correct       = accepted.some(a => normalize(a) === normalize(val));
                chosen        = val.trim();
                correctAnswer = accepted[0] || '';
            } else {
                // GROUP_MODE never shuffles (see optionsFor()), so grading can
                // always use the raw authored array + index — no dependency on
                // shuffleCache existing for a screen the viewer hasn't rendered.
                const options    = GROUP_MODE ? (q.options || []) : optionsFor(screenIdx, q.options, q.correct).options;
                const correctIdx = GROUP_MODE ? q.correct : optionsFor(screenIdx, q.options, q.correct).correctIndex;
                selected      = typeof raw.sel === 'number' ? raw.sel : -1;
                correct       = selected >= 0 && selected === correctIdx;
                chosen        = (selected >= 0 && options[selected] !== undefined) ? options[selected] : '';
                correctAnswer = options[correctIdx];
                selected      = selected >= 0 ? selected : null;
            }

            return {
                kind:           s.kind,
                section:        s.si,
                section_title:  s.sectionTitle,
                question:       q.question || '',
                chosen:         chosen,
                correct_answer: correctAnswer,
                is_correct:     correct,
                selected:       selected,
                built:          built
            };
        }

        function submitAnswer() {
            const s = screens[currentScreen];

            // Ungraded screens and already-answered screens are just "Next".
            if (!s.graded || results[currentScreen]) { nextScreen(); return; }
            if (!canAnswer()) return; // spectators can't answer in group mode

            const type = itemTypeOf(s);

            if (type === 'arrange' && !builtTokens.length) { alert('Arrange the tokens first!'); return; }
            if (type === 'type' && !document.getElementById('typeInput').value.trim()) { alert('Type your answer first!'); return; }
            if (type !== 'arrange' && type !== 'type' && selectedOption === null) { alert('Please select an option!'); return; }

            const raw = buildRawAnswer(type);
            recordResult(currentScreen, raw);

            if (GROUP_MODE) {
                groupState.answers[answerKeyFor(currentScreen)] = raw;
                pushGroupState();
            }
        }

        // Applies a graded answer to results{}/score/streak/UI — used both by
        // the driver's own submit and (without the group push) implicitly via
        // applyGroupState()'s results-rebuild loop.
        function recordResult(screenIdx, raw) {
            results[screenIdx] = gradeAnswer(screenIdx, raw);
            recomputeStats();
            applyRecordedResult(screenIdx);
            recordAttempt(screenIdx);
            updateUI();

            const r = results[screenIdx];
            if (r.is_correct && currentStreak() > 0 && currentStreak() % 3 === 0) {
                streakHighlight = true;
                showStreakPopup(currentStreak());
            } else if (!r.is_correct) {
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
        // and micro-checks are formative rather than assessed. Skipped
        // entirely in group mode — only the driver ever answers, so unlike
        // the sibling template there's no multi-member duplicate-firing
        // problem, but a shared group attempt still isn't a meaningful
        // per-student analytics row.
        function recordAttempt(idx) {
            if (GROUP_MODE) return;
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

        // fromRemote finishes (a teammate's finish synced to us) show the modal
        // without re-submitting — isDriver() is false for a spectator here, so
        // the group-mode branch below never double-submits.
        function showCongratsModal() {
            congratsShown = true;
            document.getElementById('finalScore').textContent = score;
            document.getElementById('bestStreak').textContent = bestStreak;
            document.getElementById('congratsModal').classList.add('show');
            document.getElementById('congratsBackdrop').classList.add('show');

            if (GROUP_MODE) {
                if (isDriver()) {
                    // Persist the finished state and grade it in ONE request —
                    // mirrors the sibling template's same-request save+grade,
                    // so an in-flight autosave can't race the submit.
                    groupState.finished = true;
                    gsSyncFromLive();
                    groupState.v = Math.max(groupState.v, lastRemoteV) + 1;
                    lastRemoteV  = groupState.v;
                    submitGroupIq(0);
                }
                return;
            }

            // Save classwork score if this discussion is linked to an assessment
            if (ASSESSMENT_ID) {
                fetch(BASE_URL + 'interactive_quiz/save_result', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
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
        // Only the driver may advance/rewind the shared screen in group mode.
        function nextScreen() {
            if (GROUP_MODE && !isDriver()) return;
            if (currentScreen < screens.length - 1) {
                currentScreen++;
                renderScreen();
                if (GROUP_MODE) pushGroupState();
            } else {
                showCongratsModal();
            }
        }

        function previousScreen() {
            if (GROUP_MODE && !isDriver()) return;
            if (currentScreen > 0) {
                currentScreen--;
                renderScreen();
                if (GROUP_MODE) pushGroupState();
            }
        }

        function restartQuiz() {
            if (GROUP_MODE && !isDriver()) return;
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
            if (GROUP_MODE) {
                groupState.answers  = {};
                groupState.finished = false;
                pushGroupState();
            }
            renderScreen();
        }

        // ── UI sync ─────────────────────────────────────────────────
        function updateUI() {
            document.getElementById('score').textContent = score;

            const fill = document.getElementById('progressFill');
            fill.style.width = `${((currentScreen + 1) / screens.length) * 100}%`;
            fill.classList.toggle('streak-active', streakHighlight);

            const s      = screens[currentScreen];
            const isLast = currentScreen === screens.length - 1;
            const btn    = document.getElementById('submitBtn');
            const backBtn = document.getElementById('backBtn');

            const spectating = GROUP_MODE && !isDriver();
            document.getElementById('contentWrapper').classList.toggle('iq-spectator', spectating);
            backBtn.disabled = currentScreen === 0 || spectating;

            if (GROUP_MODE && !groupState.driver) {
                // Nobody's picked a driver yet — the picker modal is the only
                // way forward, so the button underneath must not act as a
                // bypass around it.
                btn.disabled    = true;
                btn.textContent = 'Choose who answers first…';
            } else if (spectating) {
                btn.disabled    = true;
                btn.textContent = 'Waiting for ' + driverDisplayName() + '…';
            } else {
                btn.disabled = false;
                if (s.kind === 'objectives') {
                    btn.textContent = "Let's Begin →";
                } else if (!s.graded || results[currentScreen]) {
                    btn.textContent = isLast ? 'Finish' : 'Next →';
                } else {
                    btn.textContent = 'Submit';
                }
            }

            if (GROUP_MODE) updateDriverControlsUI();
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

        // ── Group sync (single-driver shared state) ──────────────────
        function gsSyncFromLive() {
            groupState.currentScreen = currentScreen;
            groupState.by            = MY_STUDENT_ID;
        }

        // force=true bypasses the isDriver() gate — needed for claiming/passing
        // control itself, which by definition happens before (or instead of)
        // the caller being the current driver.
        function pushGroupState(force) {
            if (!GROUP_MODE || applyingRemote) return;
            if (!force && !isDriver()) return;
            gsSyncFromLive();
            groupState.v = Math.max(groupState.v, lastRemoteV) + 1;
            lastRemoteV  = groupState.v;
            sendGroupState();
        }

        // One push in flight at a time; a failed push retries with backoff
        // instead of silently losing the answer on a flaky WLAN.
        function sendGroupState() {
            if (pushInFlight) { pushDirty = true; return; }
            pushInFlight = true;
            fetch(BASE_URL + 'GroupWorkController/save_draft/' + ASSESSMENT_ID, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'content=' + encodeURIComponent(JSON.stringify(groupState))
            })
            .then(r => r.json())
            .then(d => {
                pushInFlight = false;
                if (!d || !d.ok) throw new Error('save failed');
                pushRetryDelay = 2000;
                if (d.updated_at) lastVersion = d.updated_at;
                if (pushDirty) { pushDirty = false; sendGroupState(); }
            })
            .catch(() => {
                pushInFlight = false;
                pushDirty    = false; // the retry resends the latest state anyway
                setTimeout(sendGroupState, pushRetryDelay);
                pushRetryDelay = Math.min(pushRetryDelay * 2, 8000);
            });
        }

        // Records the group's score — retried up to 3 times because this is
        // the one request that must not fail silently.
        function submitGroupIq(attempt) {
            fetch(BASE_URL + 'GroupWorkController/submit_group_iq/' + ASSESSMENT_ID, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'content=' + encodeURIComponent(JSON.stringify(groupState))
            })
            .then(r => r.json())
            .then(d => {
                if (!d || !d.success) throw new Error('submit failed');
                if (typeof d.score !== 'undefined') {
                    document.getElementById('finalScore').textContent = d.score;
                }
            })
            .catch(() => {
                if (attempt < 3) {
                    setTimeout(() => submitGroupIq(attempt + 1), 2000 * Math.pow(2, attempt));
                } else {
                    alert('Could not record the group score — check your connection and tell your teacher before closing this page.');
                }
            });
        }

        function pollGroupState() {
            if (!GROUP_MODE) return;
            groupPollCount++;
            let url = BASE_URL + 'GroupWorkController/state/' + ASSESSMENT_ID + '?bare=1';
            if (groupPollCount % 8 !== 0) {
                url += '&since=' + encodeURIComponent(lastVersion);
            }
            fetch(url)
                .then(r => r.json())
                .then(d => {
                    if (!d || !d.ok) return;
                    if (d.updated_at) lastVersion = d.updated_at;
                    if (d.content_changed === false || !d.content) return;
                    let remote;
                    try { remote = JSON.parse(d.content); } catch (e) { return; }
                    if (!remote || typeof remote.v !== 'number') return;
                    if (remote.v > groupState.v) applyGroupState(remote);
                })
                .catch(() => {});
        }

        function applyGroupState(remote) {
            applyingRemote    = true;
            lastRemoteV       = remote.v;
            lastStateChangeAt = Date.now();
            groupState        = remote;
            if (!groupState.answers) groupState.answers = {};

            // Rebuild results{} from every recorded answer so recomputeStats()
            // and applyRecordedResult() work identically for the driver and
            // spectators — one grading implementation, applied to both the
            // local live submit and every synced remote answer.
            Object.keys(groupState.answers).forEach(key => {
                const idx = screenIndexForKey(key);
                if (idx !== -1 && !results[idx]) {
                    results[idx] = gradeAnswer(idx, groupState.answers[key]);
                }
            });
            recomputeStats();

            if (groupState.driver) hideDriverPicker();

            if (groupState.currentScreen !== currentScreen) {
                currentScreen = groupState.currentScreen;
                renderScreen();
            } else {
                if (results[currentScreen]) applyRecordedResult(currentScreen);
                updateUI();
            }

            renderGroupBar();

            if (groupState.finished && !congratsShown) {
                showCongratsModal();
            }
            applyingRemote = false;
        }

        function renderGroupBar() {
            const bar = document.getElementById('groupBar');
            if (!bar) return;
            const chips = GROUP_MEMBERS.map(m => {
                const isD = groupState.driver === m.id;
                return `<span class="group-chip${isD ? ' group-chip-driver' : ''}">${String(m.name || '').replace(/[<>&]/g, '')}${isD ? ' &#9998;' : ''}</span>`;
            }).join('');
            bar.innerHTML = `<span class="group-label">&#128101; ${String(GROUP_NAME).replace(/[<>&]/g, '')}</span>` + chips;
        }

        function driverDisplayName() {
            const m = GROUP_MEMBERS.find(x => x.id === groupState.driver);
            return m ? m.name : 'your teammate';
        }

        // isInitial=true: the group hasn't picked a driver yet (no Cancel —
        // someone must pick). isInitial=false: the current driver is passing
        // control on ("Pass to..." — Cancel returns without changing anything).
        function openDriverPicker(isInitial) {
            const list = document.getElementById('driverList');
            list.innerHTML = '';
            GROUP_MEMBERS.forEach(m => {
                const btn = document.createElement('button');
                btn.className   = 'congrats-button';
                btn.textContent = m.name + (m.id === MY_STUDENT_ID ? ' (you)' : '');
                btn.onclick = () => claimDriver(m.id);
                list.appendChild(btn);
            });
            if (!isInitial) {
                const cancel = document.createElement('button');
                cancel.className   = 'congrats-button';
                cancel.style.cssText = 'background:transparent; border:2px solid #357abd; color:#357abd;';
                cancel.textContent  = 'Cancel';
                cancel.onclick = hideDriverPicker;
                list.appendChild(cancel);
            }
            document.getElementById('driverModal').classList.add('show');
            document.getElementById('driverBackdrop').classList.add('show');
        }

        function hideDriverPicker() {
            document.getElementById('driverModal').classList.remove('show');
            document.getElementById('driverBackdrop').classList.remove('show');
        }

        function claimDriver(studentId) {
            groupState.driver = studentId;
            pushGroupState(true);
            hideDriverPicker();
            renderGroupBar();
            updateUI();
        }

        // Safety valve for a disconnected/absent driver: any teammate can take
        // over once the shared state has sat unchanged for 45s.
        function claimTakeover() {
            groupState.driver = MY_STUDENT_ID;
            pushGroupState(true);
            renderGroupBar();
            updateUI();
        }

        function updateDriverControlsUI() {
            const passBtn     = document.getElementById('passBtn');
            const takeoverBtn = document.getElementById('takeoverBtn');
            if (!passBtn || !takeoverBtn) return;

            passBtn.style.display = isDriver() ? '' : 'none';

            const stale = groupState.driver && !isDriver()
                && (Date.now() - lastStateChangeAt) > 45000;
            takeoverBtn.style.display = stale ? '' : 'none';
        }

        window.addEventListener('load', function () {
            renderScreen();

            if (GROUP_MODE) {
                renderGroupBar();
                // Sync to the group's progress so far (a late joiner lands here).
                if (GROUP_STATE_INIT && typeof GROUP_STATE_INIT.v === 'number') {
                    applyGroupState(GROUP_STATE_INIT);
                }
                if (!groupState.driver) {
                    openDriverPicker(true);
                }
                updateUI();
                groupPollTimer = setInterval(pollGroupState, 1500);
                setInterval(updateDriverControlsUI, 5000);
            }
        });
    </script>
</body>
</html>

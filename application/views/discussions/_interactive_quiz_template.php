<?php
/*
 * _interactive_quiz_template.php
 * ──────────────────────────────────────────────────────────────
 * Reusable Interactive Discussion + Quiz template.
 *
 * HOW TO USE
 * ──────────────────────────────────────────────────────────────
 * 1. Set $topic_file to the JSON filename (without extension)
 *    before including this template, OR load $topic_data yourself.
 *
 *    Option A — load from JSON asset:
 *        $topic_file = '105_mysqli';
 *        include '_interactive_quiz_template.php';
 *
 *    Option B — pass pre-built array from a controller:
 *        $data['topic_data'] = $this->SomeModel->get_topic('mysqli');
 *        $this->load->view('discussions/_interactive_quiz_template', $data);
 *
 * JSON FORMAT  (assets/json/<topic>.json)
 * ──────────────────────────────────────────────────────────────
 * {
 *   "topic":        "105_mysqli",
 *   "title":        "MySQLi Functions",
 *   "description":  "Interactive Learning",
 *   "congratsText": "You mastered MySQLi Functions!",
 *   "sections": [
 *     {
 *       "id":     0,
 *       "title":  "Section Title",
 *       "lesson": "<div class=\"lesson-title\">...</div>...",
 *       "quiz": {
 *         "question": "Question text?",
 *         "options":  ["Option A", "Option B", "Option C", "Option D"],
 *         "correct":  1,
 *         "code":     "optional code snippet shown above options"
 *       }
 *     }
 *   ]
 * }
 * ──────────────────────────────────────────────────────────────
 */

// ── Load topic data ──────────────────────────────────────────
if (empty($topic_data)) {
    $json_path = FCPATH . 'assets/json/' . ($topic_file ?? 'sample') . '.json';
    $topic_data = json_decode(file_get_contents($json_path), true);
}

$title         = htmlspecialchars($topic_data['title']        ?? 'Interactive Quiz');
$congrats_text = htmlspecialchars($topic_data['congratsText'] ?? 'You completed this lesson!');
$section_count = count($topic_data['sections'] ?? []);
$sections_json = json_encode($topic_data['sections'] ?? [], JSON_HEX_TAG | JSON_HEX_AMP);
$topic_slug        = $topic_data['topic'] ?? '';
$assessment_id     = isset($assessment_id) ? (int) $assessment_id : 0;
$already_submitted = !empty($already_submitted);
$previous_score    = $previous_score ?? null;
$previous_answers  = $previous_answers ?? [];

// ── Group (shared/synced) mode ───────────────────────────────
// Set by GroupWorkController::_render_group_iq(). When on, the whole group
// plays one lockstep copy: option order is deterministic (no per-client
// shuffle), section/score/answers sync via the Live_state_model blob, and
// finishing grades server-side + fans out to every member's classworks row.
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
    <?php if ($group_mode): ?>
    <style>
        .group-bar { display:flex; flex-wrap:wrap; gap:6px; align-items:center; justify-content:center;
            padding:6px 10px; background:rgba(53,122,189,0.10); border-bottom:1px solid rgba(53,122,189,0.2); }
        .group-bar .group-label { font-size:12px; font-weight:700; color:#357abd; margin-right:4px; }
        .group-bar .group-chip { font-size:12px; background:#357abd; color:#fff; border-radius:12px; padding:2px 10px; }
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
                <!-- <button class="header-close" onclick="exportDiscussionImage()" title="Save as image for offline review">&#x1F4F7;</button> -->
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
            <div class="already-submitted-banner" id="alreadySubmittedBanner">
                Already completed &mdash; recorded score: <strong><?= (int) $previous_score ?></strong>.
                You can retake for practice, but it won't change your recorded score.
            </div>
        <?php endif; ?>

        <!-- Main Content -->
        <div class="content-wrapper">
            <div class="content-scroll">
                <div class="section-container">
                    <div class="lesson-section" id="lessonSection"></div>

                    <div class="quiz-section">
                        <div class="quiz-label">Question</div>
                        <div class="question-text" id="questionText"></div>
                        <div class="options" id="optionsContainer"></div>
                        <div class="feedback" id="feedback"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Button Section -->
        <div class="button-section">
            <button class="btn-back" id="backBtn" onclick="previousSection()">&#x2190; Back</button>
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
        <button class="congrats-button" style="background:transparent; border:2px solid #357abd; color:#357abd; margin-top:0.75rem;" onclick="exportDiscussionImage()">Save as Image</button>
    </div>

    <script src="<?= base_url('assets/html2canvas.min.js') ?>"></script>

    <script>
        // ── Topic data (injected from PHP) ──────────────────────────
        const sections      = <?= $sections_json ?>;
        const TOPIC_SLUG    = <?= json_encode($topic_slug) ?>;
        const TOPIC_TITLE   = <?= json_encode($topic_data['title'] ?? '') ?>;
        const BASE_URL      = <?= json_encode(base_url()) ?>;
        const ASSESSMENT_ID = <?= $assessment_id ?>;
        const PREVIOUS_SCORE = <?= json_encode($previous_score) ?>;
        // Back button is only safe on retakes (score isn't recorded again — see
        // showCongratsModal/save_result's first-try-only guard). On a first try,
        // going back re-triggers submitAnswer() for an already-answered section,
        // which increments `score` again (only the record_attempt AJAX/quizAnswers
        // push is de-duped via answeredSections) — so it's hidden entirely.
        const ALREADY_SUBMITTED = <?= $already_submitted ? 'true' : 'false' ?>;

        // ── Group mode (shared/synced play) ─────────────────────────
        const GROUP_MODE    = <?= $group_mode ? 'true' : 'false' ?>;
        const GROUP_NAME    = <?= json_encode($group_name) ?>;
        const MY_STUDENT_ID = <?= json_encode($my_student_id) ?>;
        const GROUP_MEMBERS = <?= json_encode($group_member_js) ?>;
        // The shared live-state blob as last saved (null on a fresh group).
        const GROUP_STATE_INIT = <?= ($group_mode && $state_content !== '' && json_decode($state_content) !== null) ? $state_content : 'null' ?>;
        // recorded answers from an earlier completed attempt (see save_result()) —
        // used so the PDF export can show what was actually picked, keyed by section index.
        const prevAnswerBySection = {};
        (<?= json_encode(array_values($previous_answers)) ?>).forEach(a => { prevAnswerBySection[a.section] = a; });

        // ── State ───────────────────────────────────────────────────
        let currentSection = 0;
        let score          = 0;
        let streak         = 0;
        let bestStreak     = 0;
        let selectedOption = null;
        let answered       = false;
        let currentShuffledOptions = [];
        let currentCorrectIndex    = 0;
        let quizAnswers            = []; // per-question record, sent to save_result so it can be reviewed later
        let streakHighlight        = false;
        const answeredSections     = new Set(); // prevent double-recording on back-nav
        let congratsShown          = false;     // guard against re-showing the finish modal

        // ── Group sync state ────────────────────────────────────────
        let groupState = {
            v: 0, currentSection: 0, score: 0, streak: 0, bestStreak: 0,
            finished: false, by: MY_STUDENT_ID, sections: {}
        };
        let lastRemoteV    = 0;      // highest revision seen from the server
        let applyingRemote = false;  // suppress push loops while applying remote state
        let groupPollTimer = null;
        // Version stamp (updated_at) of the shared blob we last saw — echoed as
        // ?since= so the server skips resending the full blob when unchanged.
        let lastVersion    = <?= json_encode((string) $state_updated_at) ?>;
        let groupPollCount = 0;
        let pushInFlight   = false;  // one save_draft in flight at a time
        let pushDirty      = false;  // a newer state arrived while one was in flight
        let pushRetryDelay = 2000;

        // ── Fisher-Yates shuffle ────────────────────────────────────
        function shuffleArray(array) {
            const shuffled = [...array];
            for (let i = shuffled.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [shuffled[i], shuffled[j]] = [shuffled[j], shuffled[i]];
            }
            return shuffled;
        }

        function createShuffledOptions(options, correctIndex) {
            // Group mode keeps a deterministic order so every member sees the
            // same options — selection is synced by index, so order must match.
            if (GROUP_MODE) {
                return { options: [...options], correctIndex };
            }
            const tagged   = options.map((text, i) => ({ text, originalIndex: i }));
            const shuffled = shuffleArray(tagged);
            return {
                options:      shuffled.map(o => o.text),
                correctIndex: shuffled.findIndex(o => o.originalIndex === correctIndex)
            };
        }

        // ── Render ──────────────────────────────────────────────────
        function renderContent() {
            const section  = sections[currentSection];
            const quizSection = document.querySelector('.quiz-section');
            quizSection.style.display = 'block';

            const hasQuiz = section.quiz
                && typeof section.quiz.question === 'string'
                && Array.isArray(section.quiz.options)
                && section.quiz.options.length >= 2;

            if (!hasQuiz) {
                document.getElementById('lessonSection').innerHTML = section.lesson;
                document.getElementById('questionText').innerHTML  = '<div class="no-quiz-message">No quiz for this section.</div>';
                document.getElementById('optionsContainer').innerHTML = '';
                document.getElementById('feedback').className = 'feedback';
                document.getElementById('feedback').textContent = '';
                document.getElementById('submitBtn').textContent = 'Next →';
                answered = true; // skip to next on submit
                updateUI();
                return;
            }

            const shuffled = createShuffledOptions(section.quiz.options, section.quiz.correct);
            currentShuffledOptions = shuffled.options;
            currentCorrectIndex    = shuffled.correctIndex;

            document.getElementById('lessonSection').innerHTML = section.lesson;

            let questionHTML = `<p>${section.quiz.question}</p>`;
            if (section.quiz.code) {
                questionHTML += `<div class="question-code">${section.quiz.code}</div>`;
            }
            document.getElementById('questionText').innerHTML = questionHTML;
            renderOptions();

            const feedback = document.getElementById('feedback');
            feedback.className  = 'feedback';
            feedback.textContent = '';

            answered       = false;
            selectedOption = null;
            updateUI();
        }

        function renderOptions() {
            const container = document.getElementById('optionsContainer');
            container.innerHTML = '';
            currentShuffledOptions.forEach((text, index) => {
                const opt       = document.createElement('div');
                opt.className   = 'option';
                opt.textContent = text;
                opt.dataset.index = index;
                opt.onclick     = () => selectOption(index);
                container.appendChild(opt);
            });
        }

        // ── Interaction ─────────────────────────────────────────────
        function selectOption(index) {
            if (answered) return;
            enterFullscreen();
            document.querySelectorAll('.option').forEach(o => o.classList.remove('selected'));
            document.querySelector(`[data-index="${index}"]`).classList.add('selected');
            selectedOption = index;

            // Share the pick with the group (before submitting) so teammates
            // see the highlighted choice on their screens.
            if (GROUP_MODE && !applyingRemote) {
                groupState.sections[currentSection] = Object.assign(
                    {}, groupState.sections[currentSection], { selected: index, submitted: false }
                );
                pushGroupState();
            }
        }

        function submitAnswer() {
            if (answered) { nextSection(); return; }
            if (selectedOption === null) { alert('Please select an option!'); return; }

            const correct  = selectedOption === currentCorrectIndex;
            const feedback = document.getElementById('feedback');
            const section  = sections[currentSection];

            document.querySelectorAll('.option').forEach(o => o.classList.add('disabled'));
            answered = true;

            if (correct) {
                document.querySelector(`[data-index="${selectedOption}"]`).classList.add('correct');
                feedback.className   = 'feedback show correct';
                feedback.textContent = '✓ Correct! +1 point';
                score++;
                streak++;
                bestStreak = Math.max(bestStreak, streak);
                if (streak > 0 && streak % 3 === 0) { streakHighlight = true; showStreakPopup(streak); }
            } else {
                document.querySelector(`[data-index="${selectedOption}"]`).classList.add('incorrect');
                document.querySelector(`[data-index="${currentCorrectIndex}"]`).classList.add('correct');
                feedback.className   = 'feedback show incorrect';
                feedback.textContent = `✗ Incorrect. The correct answer is option ${currentCorrectIndex + 1}.`;
                streak = 0;
                streakHighlight = false;
            }

            // Record this attempt once per section (ignore back-nav re-submits)
            if (TOPIC_SLUG && !answeredSections.has(currentSection)) {
                answeredSections.add(currentSection);

                quizAnswers.push({
                    section:        currentSection,
                    section_title:  section.title || '',
                    question:       section.quiz.question || '',
                    chosen:         currentShuffledOptions[selectedOption] || '',
                    correct_answer: currentShuffledOptions[currentCorrectIndex] || '',
                    is_correct:     correct
                });

                // Per-student analytics only make sense for solo play — in group
                // mode the shared result is recorded server-side on finish.
                if (!GROUP_MODE) {
                    fetch(BASE_URL + 'interactive_quiz/record_attempt', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            topic:          TOPIC_SLUG,
                            section_index:  currentSection,
                            section_title:  section.title || '',
                            question_index: 0,
                            question_text:  section.quiz.question || '',
                            is_correct:     correct ? '1' : '0',
                            chosen_option:  currentShuffledOptions[selectedOption] || ''
                        })
                    }).catch(() => {});
                }
            }

            // Share the graded result with the group (index-based, deterministic
            // order — so teammates see the same correct/incorrect reveal).
            if (GROUP_MODE && !applyingRemote) {
                groupState.sections[currentSection] = {
                    selected: selectedOption, submitted: true, correct: correct
                };
                pushGroupState();
            }

            updateUI();
            document.getElementById('submitBtn').textContent = 'Next →';
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

        // fromRemote = true when a teammate's finish synced to us — show the
        // modal but don't re-submit (only the finisher records the score).
        function showCongratsModal(fromRemote) {
            congratsShown = true;
            document.getElementById('finalScore').textContent = score;
            document.getElementById('bestStreak').textContent = bestStreak;
            document.getElementById('congratsModal').classList.add('show');
            document.getElementById('congratsBackdrop').classList.add('show');

            if (GROUP_MODE) {
                if (!fromRemote) {
                    // Persist the finished state and grade it in ONE request —
                    // the server saves this exact blob before grading, so an
                    // in-flight autosave can't race the submit and drop the
                    // final answers. Teammates still learn of the finish from
                    // the saved blob's finished flag on their next poll.
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
                        answers:       JSON.stringify(quizAnswers)
                    })
                }).catch(() => {});
            }
        }

        // ── Navigation ──────────────────────────────────────────────
        function nextSection() {
            if (currentSection < sections.length - 1) {
                currentSection++;
                renderContent();
                // Reflect any answer teammates already recorded for this section,
                // then advance the shared pointer so everyone moves together.
                if (GROUP_MODE && !applyingRemote) {
                    reflectSection(currentSection);
                    pushGroupState();
                }
            } else {
                showCongratsModal(false);
            }
        }

        function previousSection() {
            if (!ALREADY_SUBMITTED) return; // no back-nav on a first try — see ALREADY_SUBMITTED note above
            if (currentSection > 0) { currentSection--; renderContent(); }
        }

        function restartQuiz() {
            currentSection = score = streak = bestStreak = 0;
            selectedOption  = null;
            answered        = false;
            streakHighlight = false;
            congratsShown   = false;
            document.getElementById('congratsModal').classList.remove('show');
            document.getElementById('congratsBackdrop').classList.remove('show');
            renderContent();
        }

        function confirmExit() {
            if (score === 0 || confirm('Are you sure? Your progress will be lost.')) {
                window.close();
            }
        }

        // ── UI sync ─────────────────────────────────────────────────
        function updateUI() {
            document.getElementById('score').textContent = score;

            const fill = document.getElementById('progressFill');
            fill.style.width = `${((currentSection + 1) / sections.length) * 100}%`;
            fill.classList.toggle('streak-active', streakHighlight);

            const backBtn = document.getElementById('backBtn');
            backBtn.style.display = ALREADY_SUBMITTED ? '' : 'none';
            backBtn.disabled = currentSection === 0;
            document.getElementById('submitBtn').textContent = answered
                ? (currentSection === sections.length - 1 ? 'Finish' : 'Next →')
                : 'Submit';
        }

        // ── Fullscreen ──────────────────────────────────────────────
        const TOPICS_URL = '<?= base_url('classwork') ?>';

        function enterFullscreen() {
            const el = document.documentElement;
            const req = el.requestFullscreen || el.webkitRequestFullscreen
                     || el.mozRequestFullScreen || el.msRequestFullscreen;
            if (req) req.call(el).catch(() => {});
        }

        function exitQuiz() {
            const done = document.fullscreenElement || document.webkitFullscreenElement;
            if (done) {
                (document.exitFullscreen || document.webkitExitFullscreen
                    || document.mozCancelFullScreen || document.msExitFullscreen)
                    .call(document)
                    .finally(() => { window.location.href = TOPICS_URL; });
            } else {
                window.location.href = TOPICS_URL;
            }
        }

        // ── Image export (topic discussion + question, for offline review) ──
        function answerForSection(i) {
            // Prefer what was picked in this live session (covers a fresh
            // attempt or reaching a question in a retake); fall back to the
            // recorded answer from the original graded attempt, if any.
            const live = quizAnswers.find(a => a.section === i);
            if (live) return live;
            return prevAnswerBySection[i] || null;
        }

        function exportDiscussionImage() {
            const exportEl = document.createElement('div');
            // Full page, however long — no pagination, single tall screenshot.
            exportEl.style.cssText = 'position:fixed; left:-9999px; top:0; width:700px; background:#fff; padding:24px; font-family:Arial, sans-serif; color:#222;';

            const scoreLine = (PREVIOUS_SCORE !== null && PREVIOUS_SCORE !== undefined)
                ? `Recorded score: ${PREVIOUS_SCORE}`
                : `Score so far: ${score}`;

            let html = `<h2 style="margin:0 0 4px;">${TOPIC_TITLE || 'Interactive Discussion'}</h2>`;
            html += `<p style="color:#666; margin:0 0 18px;">${scoreLine}</p>`;

            sections.forEach((section, i) => {
                const hasQuiz = section.quiz
                    && typeof section.quiz.question === 'string'
                    && Array.isArray(section.quiz.options);

                html += `<div style="margin-bottom:20px; padding-bottom:14px; border-bottom:1px solid #ddd;">`;
                html += `<h3 style="margin:0 0 6px;">${i + 1}. ${section.title || ''}</h3>`;
                html += `<div style="margin-bottom:10px; line-height:1.5;">${section.lesson || ''}</div>`;

                if (hasQuiz) {
                    html += `<p style="font-weight:700; margin-bottom:6px;">Q: ${section.quiz.question}</p>`;
                    html += `<ul style="margin:0 0 8px; padding-left:20px;">`;
                    section.quiz.options.forEach((opt, oi) => {
                        const isCorrectOpt = oi === section.quiz.correct;
                        html += `<li style="${isCorrectOpt ? 'color:#1a7a1a; font-weight:700;' : ''}">${opt}${isCorrectOpt ? ' (correct)' : ''}</li>`;
                    });
                    html += `</ul>`;

                    const answer = answerForSection(i);
                    if (answer) {
                        html += `<p style="margin:0;">Your answer: <span style="color:${answer.is_correct ? '#1a7a1a' : '#c0392b'}; font-weight:700;">${answer.chosen}</span></p>`;
                    } else {
                        html += `<p style="margin:0; color:#999;">Not answered yet.</p>`;
                    }
                }

                html += `</div>`;
            });

            exportEl.innerHTML = html;
            document.body.appendChild(exportEl);

            html2canvas(exportEl, { scale: 2, useCORS: true }).then(canvas => {
                document.body.removeChild(exportEl);

                const link = document.createElement('a');
                link.download = (TOPIC_SLUG || 'discussion') + '_review.png';
                link.href = canvas.toDataURL('image/png');
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }).catch(() => {
                if (exportEl.parentNode) document.body.removeChild(exportEl);
                alert('Could not generate image. Please try again.');
            });
        }

        // ── Group sync (lockstep shared state) ──────────────────────
        // Mirror the live quiz vars into the shared blob before every push.
        function gsSyncFromLive() {
            groupState.currentSection = currentSection;
            groupState.score          = score;
            groupState.streak         = streak;
            groupState.bestStreak     = bestStreak;
            groupState.by             = MY_STUDENT_ID;
        }

        function pushGroupState() {
            if (!GROUP_MODE || applyingRemote) return;
            gsSyncFromLive();
            // Monotonic revision so peers apply the newest state and ignore echoes.
            groupState.v  = Math.max(groupState.v, lastRemoteV) + 1;
            lastRemoteV   = groupState.v;
            sendGroupState();
        }

        // One push in flight at a time; a failed push retries with backoff
        // instead of silently losing the answer on a flaky WLAN. The whole
        // latest blob is resent each time, so retrying only the newest state
        // is enough — intermediate pushes are subsumed by it.
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
        // the one request that must not fail silently (it writes everyone's
        // classworks row; the server's first-completion guard makes duplicate
        // attempts safe).
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
            // ?since= lets the server answer with a tiny envelope when nothing
            // changed instead of the full blob every 1.5s; ?bare=1 skips the
            // members/ready queries (the member bar is rendered once at load).
            // updated_at only has one-second resolution, so every 8th poll
            // drops the since gate to self-heal a same-second missed update.
            groupPollCount++;
            let url = BASE_URL + 'GroupWorkController/state/' + ASSESSMENT_ID + '?bare=1';
            if (groupPollCount % 8 !== 0) {
                url += '&since=' + encodeURIComponent(lastVersion);
            }
            fetch(url)
                .then(r => r.json())
                .then(d => {
                    if (!d || !d.ok) return;
                    if (d.members) renderGroupBar(d.members);
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
            applyingRemote = true;
            lastRemoteV = remote.v;
            groupState  = remote;
            if (!groupState.sections) groupState.sections = {};

            score      = remote.score      || 0;
            streak     = remote.streak     || 0;
            bestStreak = remote.bestStreak || 0;

            if (remote.currentSection !== currentSection) {
                currentSection = remote.currentSection;
                renderContent();
            }
            reflectSection(currentSection);
            updateUI();

            if (remote.finished && !congratsShown) {
                showCongratsModal(true);
            }
            applyingRemote = false;
        }

        // Re-apply a section's shared answer to the DOM (highlight the pick and,
        // if submitted, reveal correct/incorrect) — used after render + on sync.
        function reflectSection(idx) {
            const entry = groupState.sections ? groupState.sections[idx] : null;
            if (!entry) return;
            const section = sections[idx];
            const hasQuiz = section.quiz
                && typeof section.quiz.question === 'string'
                && Array.isArray(section.quiz.options)
                && section.quiz.options.length >= 2;
            if (!hasQuiz) return;

            if (entry.selected !== null && entry.selected !== undefined) {
                document.querySelectorAll('.option').forEach(o => o.classList.remove('selected'));
                const el = document.querySelector(`[data-index="${entry.selected}"]`);
                if (el) el.classList.add('selected');
                selectedOption = entry.selected;
            }

            if (entry.submitted) {
                answered = true;
                const correctIdx = section.quiz.correct;
                document.querySelectorAll('.option').forEach(o => o.classList.add('disabled'));
                const sel  = document.querySelector(`[data-index="${entry.selected}"]`);
                const corr = document.querySelector(`[data-index="${correctIdx}"]`);
                const feedback = document.getElementById('feedback');
                if (entry.correct) {
                    if (sel) sel.classList.add('correct');
                    feedback.className   = 'feedback show correct';
                    feedback.textContent = '✓ Correct! +1 point';
                } else {
                    if (sel) sel.classList.add('incorrect');
                    if (corr) corr.classList.add('correct');
                    feedback.className   = 'feedback show incorrect';
                    feedback.textContent = `✗ Incorrect. The correct answer is option ${correctIdx + 1}.`;
                }
                document.getElementById('submitBtn').textContent =
                    (currentSection === sections.length - 1) ? 'Finish' : 'Next →';
            }
        }

        function renderGroupBar(members) {
            const bar = document.getElementById('groupBar');
            if (!bar) return;
            const list = (members && members.length)
                ? members
                : GROUP_MEMBERS.map(m => ({ name: m.name }));
            const chips = list.map(m =>
                `<span class="group-chip">${String(m.name || '').replace(/[<>&]/g, '')}</span>`
            ).join('');
            bar.innerHTML = `<span class="group-label">&#128101; ${String(GROUP_NAME).replace(/[<>&]/g, '')}</span>` + chips;
        }

        window.addEventListener('load', function () {
            renderContent();

            if (GROUP_MODE) {
                renderGroupBar(GROUP_MEMBERS.map(m => ({ name: m.name })));
                // Sync to the group's progress so far (a late joiner lands here).
                if (GROUP_STATE_INIT && typeof GROUP_STATE_INIT.v === 'number') {
                    applyGroupState(GROUP_STATE_INIT);
                }
                groupPollTimer = setInterval(pollGroupState, 1500);
            }
        });
    </script>
</body>
</html>

<?php
// Teacher / instructor results view for an interactive discussion topic.
// Accessed via /interactive_quiz/discussion_results/{topic}
// The teacher goes through the quiz exactly like a student; on submit the
// correct answer AND the live student response distribution are revealed together.
$title         = htmlspecialchars($topic_data['title']        ?? 'Interactive Quiz');
$congrats_text = htmlspecialchars($topic_data['congratsText'] ?? 'Review complete!');
$section_count = count($topic_data['sections'] ?? []);
$sections_json = json_encode($topic_data['sections'] ?? [], JSON_HEX_TAG | JSON_HEX_AMP);
$stats_json    = json_encode($stats ?? [],             JSON_HEX_TAG | JSON_HEX_AMP);
$topic_slug    = htmlspecialchars($topic ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> — Teacher View</title>
    <link rel="stylesheet" href="<?= base_url('assets/interactive-quiz-style.css') ?>">
    <style>
        /* ── Teacher badge (replaces score counter) ─── */
        .teacher-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 20px;
            padding: 3px 12px;
            font-size: 12px;
            font-weight: 700;
            color: #856404;
            white-space: nowrap;
        }

        /* ── Option: wrap text so the dist bar can sit below it ─── */
        .option {
            display: flex;
            flex-direction: column;
            gap: 0;
            padding-bottom: 0.75rem;
        }
        .option-label {
            padding: 0.25rem 0 0.1rem;
            word-wrap: break-word;
        }

        /* ── Distribution area (hidden until reveal) ─── */
        .option-dist {
            display: none;
            margin-top: 8px;
        }
        .dist-track {
            height: 8px;
            background: rgba(0,0,0,.10);
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 4px;
        }
        .dist-bar {
            height: 100%;
            border-radius: 4px;
            background: rgba(0,0,0,.25);
            transition: width .5s ease;
        }
        /* Override bar colour when option is highlighted */
        .option.correct  .dist-bar { background: rgba(21,87,36,.35); }
        .option.incorrect .dist-bar { background: rgba(114,28,36,.30); }

        .dist-info {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            font-weight: 600;
            opacity: .75;
        }

        /* ── "Waiting" hint shown before teacher submits ─── */
        .response-hint {
            font-size: 12px;
            color: #999;
            text-align: center;
            margin-top: 6px;
            font-style: italic;
        }

        /* ── Total-responses line in feedback ─── */
        .feedback-sub {
            font-size: 12px;
            font-weight: 500;
            opacity: .8;
            margin-top: 4px;
        }
    </style>
</head>
<body>
    <div class="container">

        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <button class="header-close" onclick="exitView()">&#x2715;</button>
                <div class="progress-section">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <div class="header-score">
                    <span class="teacher-badge">&#x1F4CA;&nbsp;Teacher</span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content-wrapper">
            <div class="content-scroll">
                <div class="section-container">
                    <div class="lesson-section" id="lessonSection"></div>

                    <div class="quiz-section">
                        <div class="quiz-label">Question</div>
                        <div class="question-text" id="questionText"></div>
                        <div class="options"       id="optionsContainer"></div>
                        <div class="feedback"      id="feedback"></div>
                        <div class="response-hint" id="responseHint"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Buttons -->
        <div class="button-section">
            <button class="btn-back"   id="backBtn"   onclick="previousSection()">&#x2190; Back</button>
            <button class="btn-submit" id="submitBtn" onclick="submitAnswer()">Submit</button>
        </div>
    </div>

    <!-- Congrats modal -->
    <div class="modal-backdrop" id="congratsBackdrop"></div>
    <div class="congrats-modal"  id="congratsModal">
        <div class="congrats-emoji">&#x1F389;</div>
        <div class="congrats-title">Done!</div>
        <div class="congrats-text"><?= $congrats_text ?></div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-label">Your Score</div>
                <div class="stat-card-value" id="finalScore">0</div>
            </div>
            <div class="stat-card success">
                <div class="stat-card-label">Responses</div>
                <div class="stat-card-value" id="totalResponses">0</div>
            </div>
        </div>

        <button class="congrats-button" onclick="restartQuiz()">Review Again</button>
    </div>

    <script>
        // ── Injected data ────────────────────────────────────────────
        const sections   = <?= $sections_json ?>;
        // stats: section_index → question_index → { optionText: count }
        let   stats      = <?= $stats_json ?>;
        const BASE_URL   = <?= json_encode(base_url()) ?>;
        const TOPIC_SLUG = <?= json_encode($topic_slug) ?>;
        const TOPICS_URL = <?= json_encode(site_url('interactive_quiz/topics')) ?>;

        // ── State ────────────────────────────────────────────────────
        let currentSection = 0;
        let teacherScore   = 0;
        let selectedOption = null;
        let answered       = false;
        // Options stay in original JSON order (no shuffle) so teacher knows indices
        let currentOptions      = [];
        let currentCorrectIndex = 0;

        // ── Render ───────────────────────────────────────────────────
        function renderContent() {
            const section = sections[currentSection];
            const quiz    = section.quiz;

            currentOptions      = quiz.options || [];
            currentCorrectIndex = quiz.correct;

            document.getElementById('lessonSection').innerHTML = section.lesson || '';

            let qHTML = `<p>${section.quiz.question}</p>`;
            if (quiz.code) {
                qHTML += `<div class="question-code">${quiz.code}</div>`;
            }
            document.getElementById('questionText').innerHTML = qHTML;

            renderOptions();

            const feedback = document.getElementById('feedback');
            feedback.className  = 'feedback';
            feedback.innerHTML  = '';

            answered       = false;
            selectedOption = null;

            updateResponseHint();
            updateUI();
        }

        function renderOptions() {
            const container = document.getElementById('optionsContainer');
            container.innerHTML = '';
            currentOptions.forEach((text, index) => {
                const opt           = document.createElement('div');
                opt.className       = 'option';
                opt.dataset.index   = index;

                const label         = document.createElement('div');
                label.className     = 'option-label';
                label.textContent   = text;
                opt.appendChild(label);

                // Distribution area — hidden until reveal
                const dist          = document.createElement('div');
                dist.className      = 'option-dist';
                dist.id             = `dist-${index}`;
                opt.appendChild(dist);

                opt.onclick = () => selectOption(index);
                container.appendChild(opt);
            });
        }

        // ── Interaction ──────────────────────────────────────────────
        function selectOption(index) {
            if (answered) return;
            document.querySelectorAll('.option').forEach(o => o.classList.remove('selected'));
            document.querySelector(`[data-index="${index}"]`).classList.add('selected');
            selectedOption = index;
        }

        function submitAnswer() {
            if (answered) { nextSection(); return; }
            if (selectedOption === null) { alert('Please select an option!'); return; }

            answered = true;
            document.querySelectorAll('.option').forEach(o => o.classList.add('disabled'));

            const correct  = selectedOption === currentCorrectIndex;
            if (correct) { teacherScore += 2; }

            // Highlight correct / incorrect immediately — don't wait for stats
            document.querySelector(`[data-index="${currentCorrectIndex}"]`).classList.add('correct');
            if (!correct) {
                document.querySelector(`[data-index="${selectedOption}"]`).classList.add('incorrect');
            }

            // Fetch fresh stats then inject distribution bars
            fetch(BASE_URL + 'interactive_quiz/choice_stats/' + TOPIC_SLUG)
                .then(r => r.json())
                .then(data => {
                    stats = data.sections || {};
                    revealDistribution(correct);
                })
                .catch(() => {
                    // Use cached stats if network fails
                    revealDistribution(correct);
                });

            document.getElementById('submitBtn').textContent = 'Next →';
        }

        // ── Reveal distribution inside each option ───────────────────
        function revealDistribution(correct) {
            const sectionStats = (stats[currentSection] || {})[0] || {};
            const total = currentOptions.reduce(
                (sum, opt) => sum + (sectionStats[opt] || 0), 0
            );

            currentOptions.forEach((optText, idx) => {
                const count  = sectionStats[optText] || 0;
                const pct    = total > 0 ? Math.round(count / total * 100) : 0;
                const distEl = document.getElementById(`dist-${idx}`);

                distEl.innerHTML = `
                    <div class="dist-track">
                        <div class="dist-bar" style="width:${pct}%"></div>
                    </div>
                    <div class="dist-info">
                        <span>${count} student${count !== 1 ? 's' : ''}</span>
                        <span>${pct}%</span>
                    </div>`;
                distEl.style.display = 'block';
            });

            // Feedback line
            const feedback      = document.getElementById('feedback');
            const correctLabel  = correct ? '&#x2713; Correct!' : '&#x2717; Incorrect.';
            const correctClass  = correct ? 'feedback show correct' : 'feedback show incorrect';
            const totalLine     = total > 0
                ? `<div class="feedback-sub">${total} student${total !== 1 ? 's' : ''} responded to this question.</div>`
                : `<div class="feedback-sub">No student responses recorded yet.</div>`;
            feedback.className  = correctClass;
            feedback.innerHTML  = correctLabel + totalLine;

            document.getElementById('responseHint').textContent = '';
            updateUI();
        }

        // ── Navigation ───────────────────────────────────────────────
        function nextSection() {
            if (currentSection < sections.length - 1) {
                currentSection++;
                renderContent();
            } else {
                showCongratsModal();
            }
        }

        function previousSection() {
            if (currentSection > 0) { currentSection--; renderContent(); }
        }

        // ── Modals ───────────────────────────────────────────────────
        function showCongratsModal() {
            // Compute total student responses across all sections
            let totalResponses = 0;
            sections.forEach((_, si) => {
                const sStats = (stats[si] || {})[0] || {};
                totalResponses += Object.values(sStats).reduce((s, c) => s + c, 0);
            });

            document.getElementById('finalScore').textContent    = teacherScore;
            document.getElementById('totalResponses').textContent = totalResponses;
            document.getElementById('congratsModal').classList.add('show');
            document.getElementById('congratsBackdrop').classList.add('show');
        }

        function restartQuiz() {
            currentSection = 0;
            teacherScore   = 0;
            selectedOption = null;
            answered       = false;
            document.getElementById('congratsModal').classList.remove('show');
            document.getElementById('congratsBackdrop').classList.remove('show');
            renderContent();
        }

        // ── UI sync ──────────────────────────────────────────────────
        function updateUI() {
            const fill = document.getElementById('progressFill');
            fill.style.width = `${((currentSection + 1) / sections.length) * 100}%`;

            document.getElementById('backBtn').disabled   = currentSection === 0;
            document.getElementById('submitBtn').textContent = answered
                ? (currentSection === sections.length - 1 ? 'Finish' : 'Next →')
                : 'Submit';
        }

        function updateResponseHint() {
            const sectionStats  = (stats[currentSection] || {})[0] || {};
            const total = currentOptions.reduce(
                (sum, opt) => sum + (sectionStats[opt] || 0), 0
            );
            const hint = document.getElementById('responseHint');
            hint.textContent = total > 0
                ? `${total} student${total !== 1 ? 's' : ''} have already answered this section.`
                : '';
        }

        function exitView() {
            window.location.href = TOPICS_URL;
        }

        // ── Init ─────────────────────────────────────────────────────
        window.addEventListener('load', renderContent);
    </script>
</body>
</html>

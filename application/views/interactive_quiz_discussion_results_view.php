<?php
// Teacher view — shows per-section answer distribution for a discussion topic.
// Accessed via /interactive_quiz/discussion_results/{topic}
$title        = htmlspecialchars($topic_data['title']        ?? 'Results');
$section_count = count($topic_data['sections'] ?? []);
$sections_json = json_encode($topic_data['sections'] ?? [], JSON_HEX_TAG | JSON_HEX_AMP);
$stats_json    = json_encode($stats ?? [], JSON_HEX_TAG | JSON_HEX_AMP);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> — Class Results</title>
    <link rel="stylesheet" href="<?= base_url('assets/interactive-quiz-style.css') ?>">
    <style>
        :root {
            --iq-primary:  #04AA6D;
            --iq-dark:     #038a57;
            --iq-correct:  #28a745;
            --bar-bg:      #e9ecef;
        }

        /* ── Teacher badge ─── */
        .teacher-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 20px;
            padding: 4px 14px;
            font-size: 13px;
            font-weight: 700;
            color: #856404;
        }

        /* ── Stats panel ─── */
        .stats-panel {
            padding: 16px 20px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-top: 14px;
        }
        .stats-panel .panel-title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #666;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .total-responses {
            font-size: 13px;
            color: #888;
            font-weight: 400;
        }

        /* ── Option bar row ─── */
        .option-row {
            margin-bottom: 12px;
        }
        .option-label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 14px;
        }
        .option-label-text {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
            min-width: 0;
        }
        .option-label-text span {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .correct-badge {
            flex-shrink: 0;
            font-size: 11px;
            font-weight: 700;
            background: #d4edda;
            color: var(--iq-correct);
            border: 1px solid #c3e6cb;
            border-radius: 10px;
            padding: 1px 8px;
        }
        .option-count {
            flex-shrink: 0;
            font-weight: 700;
            font-size: 14px;
            min-width: 32px;
            text-align: right;
        }
        .option-pct {
            flex-shrink: 0;
            font-size: 12px;
            color: #888;
            min-width: 38px;
            text-align: right;
        }

        /* ── Bar ─── */
        .bar-track {
            height: 10px;
            background: var(--bar-bg);
            border-radius: 5px;
            overflow: hidden;
        }
        .bar-fill {
            height: 100%;
            border-radius: 5px;
            background: #6c757d;
            transition: width .4s ease;
        }
        .bar-fill.is-correct {
            background: var(--iq-correct);
        }

        /* ── Refresh bar ─── */
        .refresh-bar {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 20px;
            background: #fff;
            border-top: 1px solid #e0e0e0;
            font-size: 13px;
            color: #666;
        }
        .btn-refresh {
            padding: 5px 14px;
            border-radius: 16px;
            border: 2px solid var(--iq-primary);
            background: #fff;
            color: var(--iq-primary);
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            transition: background .15s, color .15s;
        }
        .btn-refresh:hover { background: var(--iq-primary); color: #fff; }
        .btn-refresh:disabled { opacity: .5; cursor: default; }
        .refresh-status { flex: 1; }

        /* ── No responses ─── */
        .no-responses {
            text-align: center;
            padding: 20px;
            color: #aaa;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">

        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <button class="header-close" onclick="window.close()">&#x2715;</button>
                <div class="progress-section">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <div class="header-score">
                    <span class="teacher-badge">&#x1F4CA; Teacher View</span>
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

                        <!-- Answer distribution -->
                        <div class="stats-panel" id="statsPanel">
                            <div class="panel-title">
                                Student Responses
                                <span class="total-responses" id="totalResponses"></span>
                            </div>
                            <div id="distributionBars"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Refresh bar -->
        <div class="refresh-bar">
            <button class="btn-refresh" id="refreshBtn" onclick="refreshStats()">&#x21BB; Refresh</button>
            <span class="refresh-status" id="refreshStatus">Auto-refreshes every 15 s</span>
        </div>

        <!-- Navigation -->
        <div class="button-section">
            <button class="btn-back" id="backBtn" onclick="previousSection()">&#x2190; Back</button>
            <button class="btn-submit" id="nextBtn" onclick="nextSection()">Next &#x2192;</button>
        </div>
    </div>

    <script>
        const sections    = <?= $sections_json ?>;
        const BASE_URL    = <?= json_encode(base_url()) ?>;
        const TOPIC_SLUG  = <?= json_encode($topic ?? '') ?>;

        let currentSection = 0;
        // Keyed by section_index → question_index → { optionText: count }
        let stats = <?= $stats_json ?>;

        // ── Render section content + distribution ───────────────────
        function renderSection() {
            const section = sections[currentSection];

            document.getElementById('lessonSection').innerHTML = section.lesson || '';

            let qHTML = `<p>${section.quiz.question}</p>`;
            if (section.quiz.code) {
                qHTML += `<div class="question-code">${section.quiz.code}</div>`;
            }
            document.getElementById('questionText').innerHTML = qHTML;

            renderDistribution();
            updateNav();
        }

        function renderDistribution() {
            const section   = sections[currentSection];
            const options   = section.quiz.options || [];
            const correct   = section.quiz.correct;
            const sectionStats = (stats[currentSection] || {})[0] || {};
            const bars      = document.getElementById('distributionBars');
            const totalEl   = document.getElementById('totalResponses');

            const total = options.reduce((sum, opt) => sum + (sectionStats[opt] || 0), 0);
            totalEl.textContent = total > 0 ? `${total} response${total !== 1 ? 's' : ''}` : 'No responses yet';

            if (total === 0) {
                bars.innerHTML = '<div class="no-responses">Waiting for students to answer…</div>';
                return;
            }

            bars.innerHTML = options.map((opt, idx) => {
                const count   = sectionStats[opt] || 0;
                const pct     = total > 0 ? Math.round(count / total * 100) : 0;
                const isCorrect = idx === correct;
                return `
                <div class="option-row">
                    <div class="option-label-row">
                        <div class="option-label-text">
                            <span title="${escHtml(opt)}">${escHtml(opt)}</span>
                            ${isCorrect ? '<span class="correct-badge">✓ Correct</span>' : ''}
                        </div>
                        <span class="option-count">${count}</span>
                        <span class="option-pct">${pct}%</span>
                    </div>
                    <div class="bar-track">
                        <div class="bar-fill ${isCorrect ? 'is-correct' : ''}"
                             style="width:${pct}%"></div>
                    </div>
                </div>`;
            }).join('');
        }

        // ── Navigation ───────────────────────────────────────────────
        function nextSection() {
            if (currentSection < sections.length - 1) {
                currentSection++;
                renderSection();
            }
        }

        function previousSection() {
            if (currentSection > 0) {
                currentSection--;
                renderSection();
            }
        }

        function updateNav() {
            const fill = document.getElementById('progressFill');
            fill.style.width = `${((currentSection + 1) / sections.length) * 100}%`;
            document.getElementById('backBtn').disabled = currentSection === 0;
            document.getElementById('nextBtn').disabled = currentSection === sections.length - 1;
            document.getElementById('nextBtn').textContent =
                currentSection === sections.length - 1 ? 'Done' : 'Next →';
        }

        // ── Stats refresh ────────────────────────────────────────────
        function refreshStats() {
            const btn = document.getElementById('refreshBtn');
            const status = document.getElementById('refreshStatus');
            btn.disabled = true;
            status.textContent = 'Refreshing…';

            fetch(BASE_URL + 'interactive_quiz/choice_stats/' + TOPIC_SLUG)
                .then(r => r.json())
                .then(data => {
                    stats = data.sections || {};
                    renderDistribution();
                    const now = new Date();
                    status.textContent = `Updated at ${now.getHours()}:${String(now.getMinutes()).padStart(2,'0')}:${String(now.getSeconds()).padStart(2,'0')} — auto-refreshes every 15 s`;
                })
                .catch(() => { status.textContent = 'Refresh failed. Will retry.'; })
                .finally(() => { btn.disabled = false; });
        }

        // ── Helpers ──────────────────────────────────────────────────
        function escHtml(str) {
            return String(str)
                .replace(/&/g,'&amp;').replace(/</g,'&lt;')
                .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }

        // ── Init ─────────────────────────────────────────────────────
        window.addEventListener('load', () => {
            renderSection();
            setInterval(refreshStats, 15000);
        });
    </script>
</body>
</html>

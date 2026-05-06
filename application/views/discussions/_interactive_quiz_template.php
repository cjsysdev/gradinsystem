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

$title        = htmlspecialchars($topic_data['title']        ?? 'Interactive Quiz');
$congrats_text = htmlspecialchars($topic_data['congratsText'] ?? 'You completed this lesson!');
$section_count = count($topic_data['sections'] ?? []);
$sections_json = json_encode($topic_data['sections'] ?? [], JSON_HEX_TAG | JSON_HEX_AMP);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> - Interactive Learning</title>
    <link rel="stylesheet" href="<?= base_url('assets/interactive-quiz-style.css') ?>">
</head>
<body>
    <div class="container">

        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <div class="header-title"><?= $title ?></div>
                <button class="header-close" onclick="confirmExit()">&#x2715;</button>
            </div>

            <div class="stats-bar">
                <div class="stat-item">
                    <div class="stat-label">Score</div>
                    <div class="stat-value" id="score">0</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Streak &#x1F525;</div>
                    <div class="stat-value" id="streak">0</div>
                </div>
                <div class="stat-item">
                    <div class="stat-label">Progress</div>
                    <div class="stat-value" id="progress">1/<?= $section_count ?></div>
                </div>
            </div>

            <div class="progress-section">
                <div class="progress-fill" id="progressFill"></div>
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
    </div>

    <script>
        // ── Topic data (injected from PHP) ──────────────────────────
        const sections = <?= $sections_json ?>;

        // ── State ───────────────────────────────────────────────────
        let currentSection = 0;
        let score          = 0;
        let streak         = 0;
        let bestStreak     = 0;
        let selectedOption = null;
        let answered       = false;
        let currentShuffledOptions = [];
        let currentCorrectIndex    = 0;

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
            document.querySelectorAll('.option').forEach(o => o.classList.remove('selected'));
            document.querySelector(`[data-index="${index}"]`).classList.add('selected');
            selectedOption = index;
        }

        function submitAnswer() {
            if (answered) { nextSection(); return; }
            if (selectedOption === null) { alert('Please select an option!'); return; }

            const correct  = selectedOption === currentCorrectIndex;
            const feedback = document.getElementById('feedback');

            document.querySelectorAll('.option').forEach(o => o.classList.add('disabled'));
            answered = true;

            if (correct) {
                document.querySelector(`[data-index="${selectedOption}"]`).classList.add('correct');
                feedback.className   = 'feedback show correct';
                feedback.textContent = '✓ Correct! +2 points';
                score++;
                score++;
                streak++;
                bestStreak = Math.max(bestStreak, streak);
                if (streak > 0 && streak % 3 === 0) showStreakPopup(streak);
            } else {
                document.querySelector(`[data-index="${selectedOption}"]`).classList.add('incorrect');
                document.querySelector(`[data-index="${currentCorrectIndex}"]`).classList.add('correct');
                feedback.className   = 'feedback show incorrect';
                feedback.textContent = `✗ Incorrect. The correct answer is option ${currentCorrectIndex + 1}.`;
                streak = 0;
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

        function showCongratsModal() {
            document.getElementById('finalScore').textContent  = score;
            document.getElementById('bestStreak').textContent  = bestStreak;
            document.getElementById('congratsModal').classList.add('show');
            document.getElementById('congratsBackdrop').classList.add('show');
        }

        // ── Navigation ──────────────────────────────────────────────
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

        function restartQuiz() {
            currentSection = score = streak = bestStreak = 0;
            selectedOption = null;
            answered       = false;
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
            document.getElementById('score').textContent    = score;
            document.getElementById('streak').textContent   = streak;
            document.getElementById('progress').textContent = `${currentSection + 1}/${sections.length}`;
            document.getElementById('progressFill').style.width =
                `${((currentSection + 1) / sections.length) * 100}%`;

            document.getElementById('backBtn').disabled = currentSection === 0;
            document.getElementById('submitBtn').textContent = answered
                ? (currentSection === sections.length - 1 ? 'Finish' : 'Next →')
                : 'Submit';
        }

        window.addEventListener('load', renderContent);
    </script>
</body>
</html>

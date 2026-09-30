<?php $this->load->view('header') ?>

<!-- Override viewport to support safe-area-inset for Android nav bar -->
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">

<!-- Highlight.js CSS -->
<link rel="stylesheet" href="<?= base_url('assets/highlights/atom-one-light.min.css') ?>">
<!-- Highlight.js JS -->
<script src="<?= base_url("assets/highlights/11.7.0-highlight.min.js") ?> "></script>

<style>
    .form-check-label {
        display: block;
        padding: 15px;
        margin: 5px 0;
        background-color: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 5px;
        cursor: pointer;
        touch-action: manipulation;
        transition: background-color 0.3s ease;
    }

    .form-check-input {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        position: absolute;
        opacity: 0;
    }

    .form-check-input:checked+.form-check-label {
        background-color: #007bff;
        color: white;
        border-color: #007bff;
    }

    .form-check-label:hover {
        background-color: #e9ecef;
    }

    .btn-block {
        width: 100%;
        padding: 15px;
        font-size: 18px;
    }

    .question-block {
        display: block;
        padding: 5px;
        margin: 5px 0;
        border-radius: 5px;
    }

    .question-group {
        display: none;
    }

    .question-group.active {
        display: block;
    }

    body.locked {
        overflow: hidden;
    }

    .warning-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 0, 0, 0.7);
        color: white;
        text-align: center;
        padding-top: 20%;
        z-index: 1000;
    }

    .blur-effect {
        filter: blur(5px);
        pointer-events: none;
        /* Prevents interaction while blurred */
        transition: filter 0.3s ease;
    }

    .timer {
        font-size: 24px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 20px;
    }

    .text-answer-input {
        width: 100%;
        padding: 10px;
        margin: 5px 0;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 16px;
    }

    .text-answer-input:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
    }

    /* Push buttons above Android/iOS system navigation bar */
    .navigation-buttons {
        padding-bottom: 72px; /* fallback for Android 3-button nav */
        padding-bottom: max(72px, calc(env(safe-area-inset-bottom, 0px) + 16px));
    }
</style>
<div class="container mt-3 mb-5">
    <div class="dashboard">
        <div class="container">
            <div class="row profile-section text-center mt-3">
                <div class="col">
                    <h3 class="m-0"><strong><?= $this->session->lastname, ', ',  $this->session->firstname ?></strong></h3>
                </div>
            </div>
        </div>

        <hr>
        <div class="timer" id="timer">
            <div class="card-header bg-secondary text-white">
                <h2 class="card-title text-center m-0"><span><strong id="time">00:00:00</strong></span></h2>
            </div>
        </div>

        <div class="text-center mb-3">
            <button id="scrollToBottomBtn" class="btn btn-outline-secondary btn-block">Go to Bottom</button>
        </div>
        <?php if (!empty($test_mode)): ?>
            <div class="alert alert-warning text-center mb-3">
                <strong>Test Mode</strong> &mdash; this attempt is not scored or recorded.
            </div>
        <?php endif; ?>
        <div class="card" style="border: none;">
            <div class="card-body p-0">
                <form action="<?= $submit_url ?>" method="post" id="quizForm">
                    <?php
                    $questionsPerPage = 20;
                    $totalQuestions = count($questions);
                    $totalGroups = ceil($totalQuestions / $questionsPerPage);

                    for ($group = 0; $group < $totalGroups; $group++):
                        $start = $group * $questionsPerPage;
                        $end = min(($group + 1) * $questionsPerPage, $totalQuestions);
                    ?>
                        <div class="question-group <?= $group === 0 ? 'active' : '' ?>" data-group="<?= $group ?>">
                            <?php for ($i = $start; $i < $end; $i++): ?>
                                <div class="mb-4">
                                    <div class="question-block">
                                        <strong>Question <?= $i + 1 ?>:</strong> <?= nl2br(htmlspecialchars($questions[$i]['question'])) ?>
                                        <?php if (!empty($questions[$i]['code'])): ?>
                                            <pre class="cm-s-default">
<code class="language-c"><?= htmlspecialchars(ltrim($questions[$i]['code'])) ?></code>
</pre>
                                        <?php endif; ?>
                                    </div>
                                    <?php
                                    // Check if choices are empty or only contain empty strings
                                    $validChoices = array_filter($questions[$i]['choices'], function ($choice) {
                                        return trim($choice) !== '';
                                    });
                                    ?>
                                    <?php if (empty($validChoices)): ?>
                                        <!-- Text Input for questions without choices -->
                                        <div>
                                            <input type="text"
                                                class="text-answer-input"
                                                name="answers[<?= $i ?>]"
                                                id="choice<?= $i ?>_text"
                                                placeholder="Type your answer here..."
                                                data-question="<?= $i ?>"
                                                data-question-type="text">
                                        </div>
                                    <?php else: ?>
                                        <!-- Radio buttons for multiple choice questions -->
                                        <?php foreach ($questions[$i]['choices'] as $choiceIndex => $choice): ?>
                                            <div>
                                                <input class="form-check-input" type="radio"
                                                    name="answers[<?= $i ?>]"
                                                    value="<?= htmlspecialchars($choice) ?>"
                                                    id="choice<?= $i ?>_<?= $choiceIndex ?>"
                                                    data-question="<?= $i ?>"
                                                    data-question-type="radio">
                                                <label class="form-check-label" for="choice<?= $i ?>_<?= $choiceIndex ?>">
                                                    <?= htmlspecialchars($choice) ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <hr>
                            <?php endfor; ?>
                        </div>
                    <?php endfor; ?>

                    <div class="progress my-4" style="height: 30px;">
                        <div id="quizProgressBar" class="progress-bar bg-info" role="progressbar" style="width: 0%; font-size: 18px;">
                            <span id="answeredCount">0</span> / <span id="totalQuestions"><?= $totalQuestions ?></span> Answered
                        </div>
                    </div>

                    <div class="navigation-buttons mt-4">
                        <div class="row">
                            <div class="col-6">
                                <button type="button" class="btn btn-outline-secondary btn-block" id="prevBtn" hidden>Previous</button>
                            </div>
                            <div class="col-6">
                                <button type="button" class="btn btn-secondary btn-block" id="nextBtn">Next</button>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <button type="submit" class="btn btn-success btn-block" id="submitBtn" style="display: none;">Submit</button>
                        </div>
                    </div>

                    <?php // Tab-switch tally, bumped by the blur handler below and read by
                    // SecureQuizController::submit() into classworks.switch_count. Declared
                    // once here rather than appended on every blur, which used to leave the
                    // form carrying one duplicate `blur_count` input per switch. ?>
                    <input type="hidden" name="blur_count" id="blurCountInput" value="0">
                </form>
                <div class="warning-overlay" id="warningOverlay">
                    <h2>Warning!</h2>
                    <p>Please return to the exam. Switching tabs is not allowed.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php // The stray "?>
<script src="<?= base_url('assets/crypto-js.min.js') ?>"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const groups = document.getElementsByClassName('question-group');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');
        const form = document.getElementById('quizForm');
        const warningOverlay = document.getElementById('warningOverlay');
        const timerElement = document.getElementById('time');
        const scrollToBottomBtn = document.getElementById('scrollToBottomBtn');
        const blurCountInput = document.getElementById('blurCountInput');
        let currentGroup = 0;
        let blurCount = 0;
        let quizStarted = false;
        let totalTime = 60 * <?= (int) $max_items ?> * 1.3;

        hljs.highlightAll();

        // Fullscreen mode
        function enterFullscreen() {
            const elem = document.documentElement;
            if (elem.requestFullscreen) {
                elem.requestFullscreen();
            } else if (elem.webkitRequestFullscreen) {
                /* Safari */
                elem.webkitRequestFullscreen();
            } else if (elem.msRequestFullscreen) {
                /* IE11 */
                elem.msRequestFullscreen();
            }
            document.body.classList.add('locked');
            quizStarted = true;
            // console.log('Quiz started, fullscreen mode entered.');
        }

        function updateProgressBar() {
            const totalQuestions = <?= $totalQuestions ?>;
            let answered = 0;

            // Count radio buttons
            answered += document.querySelectorAll('.form-check-input:checked').length;

            // Count text inputs with values
            document.querySelectorAll('input[data-question-type="text"]').forEach(input => {
                if (input.value.trim() !== '') {
                    answered++;
                }
            });

            const percent = Math.round((answered / totalQuestions) * 100);

            const progressBar = document.getElementById('quizProgressBar');
            const answeredCount = document.getElementById('answeredCount');
            const totalQuestionsElem = document.getElementById('totalQuestions');

            progressBar.style.width = percent + '%';
            progressBar.textContent = `${answered} / ${totalQuestions}`;
            answeredCount.textContent = answered;
            totalQuestionsElem.textContent = totalQuestions;
        }

        // Update display
        function updateDisplay() {
            for (let i = 0; i < groups.length; i++) {
                groups[i].classList.remove('active');
            }
            groups[currentGroup].classList.add('active');

            prevBtn.hidden = (currentGroup === 0);
            if (groups.length === 1) {
                nextBtn.style.display = 'none';
                submitBtn.style.display = 'block';
            } else {
                nextBtn.style.display = (currentGroup < groups.length - 1) ? 'block' : 'none';
                submitBtn.style.display = (currentGroup === groups.length - 1) ? 'block' : 'none';
            }
        }

        // Check if all questions are answered
        function checkAllAnswered() {
            const totalQuestions = <?= $totalQuestions ?>;
            let answered = 0;

            // Count radio buttons
            answered += document.querySelectorAll('.form-check-input:checked').length;

            // Count text inputs with values
            document.querySelectorAll('input[data-question-type="text"]').forEach(input => {
                if (input.value.trim() !== '') {
                    answered++;
                }
            });

            const allAnswered = answered === totalQuestions;
            if (allAnswered) {
                submitBtn.style.display = 'block';
            } else {
                submitBtn.style.display = 'none';
            }
        }

        // Save all answers in one object
        let quizAnswers = JSON.parse(localStorage.getItem('quiz_answers')) || {};
        const radioButtons = document.querySelectorAll('.form-check-input');
        const textInputs = document.querySelectorAll('input[data-question-type="text"]');

        // Radio button event listeners
        radioButtons.forEach(radio => {
            radio.addEventListener('change', function() {
                if (!quizStarted) enterFullscreen();
                quizAnswers[this.dataset.question] = this.value;
                localStorage.setItem('quiz_answers', JSON.stringify(quizAnswers));
                checkAllAnswered();
                updateProgressBar();
            });
        });

        // Text input event listeners
        textInputs.forEach(input => {
            input.addEventListener('input', function() {
                if (!quizStarted) enterFullscreen();
                quizAnswers[this.dataset.question] = this.value;
                localStorage.setItem('quiz_answers', JSON.stringify(quizAnswers));
                checkAllAnswered();
                updateProgressBar();
            });
        });

        function loadSavedAnswers() {
            quizAnswers = JSON.parse(localStorage.getItem('quiz_answers')) || {};

            // Load radio button answers
            radioButtons.forEach(radio => {
                const questionIndex = radio.dataset.question;
                if (quizAnswers[questionIndex] && radio.value === quizAnswers[questionIndex]) {
                    radio.checked = true;
                }
            });

            // Load text input answers
            textInputs.forEach(input => {
                const questionIndex = input.dataset.question;
                if (quizAnswers[questionIndex]) {
                    input.value = quizAnswers[questionIndex];
                }
            });
        }

        // Navigation
        prevBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentGroup > 0) {
                currentGroup--;
                updateDisplay();
                window.scrollTo(0, 0); // Instantly jump to top

            }
        });

        nextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentGroup < groups.length - 1) {
                currentGroup++;
                updateDisplay();
                window.scrollTo(0, 0);
            }
        });

        // Scroll to bottom
        scrollToBottomBtn.addEventListener('click', function() {
            window.scrollTo({
                top: document.body.scrollHeight,
                behavior: 'auto'
            });
        });

        // Blur detection
        window.addEventListener('blur', function() {
            if (quizStarted) {
                blurCount++;
                warningOverlay.style.display = 'block';
                // Recorded server-side into classworks.switch_count at submit.
                blurCountInput.value = blurCount;
            }
        });

        window.addEventListener('focus', function() {
            if (quizStarted) {
                setTimeout(function() {
                    warningOverlay.style.display = 'none';
                }, 3000);
                // console.log('Window focused.');
            }
        });

        // Disable copy/paste and right-click
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('copy', e => e.preventDefault());
        document.addEventListener('paste', e => e.preventDefault());

        // Form submission.
        //
        // Two things happen here, in this order and deliberately decoupled:
        //   1. the student gets an encrypted local copy of their answers, so a
        //      network failure between here and the server doesn't lose their
        //      work; then
        //   2. the form actually posts, 3s later, giving the download time to
        //      start.
        // Step 1 must never be able to prevent step 2 — the backup is a safety
        // net, and a safety net that can eat the submission is worse than none.
        // Hence the try/catch: if CryptoJS is missing or Blob/download is
        // blocked, we log it and post anyway.
        let submitting = false;
        let timedOut = false;

        form.addEventListener('submit', function(e) {
            // Always take over the submit; we re-post manually below.
            e.preventDefault();

            // Offline check first. This used to sit *after* the delayed submit
            // was already scheduled, so an offline student got the warning and
            // the form posted into the void 3 seconds later regardless.
            // Skipped when the timer expired: at that point refusing to submit
            // would just strand the student on a dead quiz, and they have their
            // backup file either way.
            if (!navigator.onLine && !timedOut) {
                alert('You are not connected to the internet. Please check your local connection and try again.\n\nYour answers are saved in this browser — do not close this tab.');
                return;
            }

            // Guard against a second click landing inside the 3s window.
            if (submitting) return;
            submitting = true;

            // Prepare the results array in the same format as QuizController::submit
            const questions = <?= json_encode($questions); ?>;
            const answers = {};

            // Get answers from radio buttons (multiple choice)
            document.querySelectorAll('.form-check-input:checked').forEach(input => {
                answers[input.name.replace('answers[', '').replace(']', '')] = input.value;
            });

            // Get answers from text inputs
            document.querySelectorAll('input[data-question-type="text"]').forEach(input => {
                answers[input.name.replace('answers[', '').replace(']', '')] = input.value;
            });

            // NOTE: is_correct here is the *client's* view, for the student's own
            // copy only. The authoritative grade is computed server-side by
            // Widgets_model::grade_quiz() from the session-held question set —
            // this file is a receipt, not a score.
            const results = questions.map((question, index) => {
                const userAnswer = answers[index] !== undefined ? answers[index] : 'No answer';
                // Case-insensitive comparison with whitespace trimming for text inputs
                const userAnswerNormalized = userAnswer.toLowerCase().trim();
                const correctAnswerNormalized = String(question.answer ?? '').toLowerCase().trim();
                const isCorrect = userAnswerNormalized === correctAnswerNormalized;

                return {
                    question: question.question,
                    code: question.code,
                    user_answer: userAnswer,
                    correct_answer: question.answer,
                    is_correct: isCorrect
                };
            });

            // Local backup copy — encrypted so the file isn't an answer key in
            // the student's downloads folder. Best-effort by design.
            try {
                const encrypted = CryptoJS.AES.encrypt(JSON.stringify(results), 'teacherSecret').toString();
                const blob = new Blob([encrypted], {
                    type: "application/json"
                });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = "quiz_submission_<?= $this->session->student_id ?? 'student' ?>.json";
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            } catch (err) {
                // Never block the real submission over a failed backup.
                console.error('Local backup copy could not be created:', err);
            }

            // Submit the form after a short delay (enough for download to start).
            // form.submit() does not re-fire this listener, so there's no loop.
            setTimeout(function() {
                localStorage.removeItem('quiz_answers');
                localStorage.removeItem('remainingTime');
                form.submit();
            }, 3000);
        });

        // Timer
        function startTimer(duration, display) {
            let timer = duration,
                hours, minutes, seconds;
            const interval = setInterval(function() {
                hours = parseInt(timer / 3600, 10);
                minutes = parseInt((timer % 3600) / 60, 10);
                seconds = parseInt(timer % 60, 10);

                hours = hours < 10 ? "0" + hours : hours;
                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                display.textContent = hours + ":" + minutes + ":" + seconds;

                if (--timer < 0) {
                    clearInterval(interval);
                    timedOut = true;
                    // requestSubmit(), not submit(): submit() bypasses the submit
                    // listener, so a student who ran out of time used to be the
                    // one student who got no local backup copy — exactly the case
                    // where a lost POST hurts most. Fall back for old browsers.
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        form.submit();
                    }
                }

                // Save the remaining time to localStorage
                localStorage.setItem('remainingTime', timer);
            }, 1000);
        }

        // Initial setup
        loadSavedAnswers();
        updateDisplay();
        updateProgressBar();

        // Retrieve the remaining time from localStorage
        const savedTime = localStorage.getItem('remainingTime');
        if (savedTime) {
            totalTime = parseInt(savedTime, 10);
        }

        startTimer(totalTime, timerElement);
        checkAllAnswered(); // Initial check to see if all questions are already answered
    });
</script>

<?php $this->load->view('footer') ?>

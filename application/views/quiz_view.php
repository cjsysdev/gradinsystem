<?php $this->load->view('header') ?>

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
        <div class="card" style="border: none;">
            <div class="card-body p-0">
                <?php $assessment_id = (explode('/', uri_string())[1]) ?>
                <form action="<?= site_url('quiz/submit/' . $assessment_id) ?>" method="post" id="quizForm">
                    <?php
                    $questionsPerPage = 10;
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
                                    </div>
                                    <?php foreach ($questions[$i]['choices'] as $choiceIndex => $choice): ?>
                                        <div>
                                            <input class="form-check-input" type="radio"
                                                name="answers[<?= $i ?>]"
                                                value="<?= htmlspecialchars($choice) ?>"
                                                id="choice<?= $i ?>_<?= $choiceIndex ?>"
                                                data-question="<?= $i ?>">
                                            <label class="form-check-label" for="choice<?= $i ?>_<?= $choiceIndex ?>">
                                                <?= htmlspecialchars($choice) ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <hr>
                            <?php endfor; ?>
                        </div>
                    <?php endfor; ?>

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
                </form>
                <div class="warning-overlay" id="warningOverlay">
                    <h2>Warning!</h2>
                    <p>Please return to the exam. Switching tabs is not allowed.</p>
                </div>
            </div>
        </div>
    </div>
</div>
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
        let currentGroup = 0;
        let blurCount = 0;
        let quizStarted = false;
        let totalTime = 60 * 90; // 90 minutes in seconds

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
            console.log('Quiz started, fullscreen mode entered.');
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
            const allAnswered = Array.from(document.querySelectorAll('.form-check-input'))
                .filter(input => input.checked).length === <?= $totalQuestions ?>;
            if (allAnswered) {
                submitBtn.style.display = 'block';
            } else {
                submitBtn.style.display = 'none';
            }
        }

        // Save and load answers (from previous implementation)

        console.log(localStorage);

        const radioButtons = document.querySelectorAll('.form-check-input');
        radioButtons.forEach(radio => {
            radio.addEventListener('change', function() {
                if (!quizStarted) enterFullscreen();
                const questionIndex = this.dataset.question;
                const answer = this.value;
                localStorage.setItem(`quiz_answer_${questionIndex}`, answer);
                checkAllAnswered(); // Check if all questions are answered
            });
        });

        function loadSavedAnswers() {
            radioButtons.forEach(radio => {
                const questionIndex = radio.dataset.question;
                const savedAnswer = localStorage.getItem(`quiz_answer_${questionIndex}`);
                if (savedAnswer && radio.value === savedAnswer) {
                    radio.checked = true;
                }
            });
        }

        // Navigation
        prevBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentGroup > 0) {
                currentGroup--;
                updateDisplay();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                }); // Smooth scroll to the top of the page
            }
        });

        nextBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentGroup < groups.length - 1) {
                currentGroup++;
                updateDisplay();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                }); // Smooth scroll to the top of the page
            }
        });

        // Scroll to bottom
        scrollToBottomBtn.addEventListener('click', function() {
            window.scrollTo({
                top: document.body.scrollHeight,
                behavior: 'smooth'
            });
        });

        // Blur detection
        window.addEventListener('blur', function() {
            if (quizStarted) {
                blurCount++;
                warningOverlay.style.display = 'block';
                console.log('Window blurred. Blur count:', blurCount);
                // Log to hidden input for server-side tracking
                let blurInput = document.createElement('input');
                blurInput.type = 'hidden';
                blurInput.name = 'blur_count';
                blurInput.value = blurCount;
                form.appendChild(blurInput);
            }
        });

        window.addEventListener('focus', function() {
            if (quizStarted) {
                warningOverlay.style.display = 'none';
                console.log('Window focused.');
            }
        });

        // Disable copy/paste and right-click
        document.addEventListener('contextmenu', e => e.preventDefault());
        document.addEventListener('copy', e => e.preventDefault());
        document.addEventListener('paste', e => e.preventDefault());

        // Form submission
        form.addEventListener('submit', function(e) {
            if (!navigator.onLine) {
                e.preventDefault();
                alert('You are not connected to the internet. Please check your local connection and try again.');
                return;
            }
            if (navigator.onLine) {
                for (let i = 0; i < <?= $totalQuestions; ?>; i++) {
                    localStorage.removeItem(`quiz_answer_${i}`);
                }
            }
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
                    form.submit(); // Auto-submit the form when time is up
                }

                // Save the remaining time to localStorage
                localStorage.setItem('remainingTime', timer);
            }, 1000);
        }

        // Initial setup
        loadSavedAnswers();
        updateDisplay();

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
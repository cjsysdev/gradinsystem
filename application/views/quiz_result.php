<?php $this->load->view('header') ?>

<title>Quiz Result</title>

<style>
    .correct {
        color: green;
    }

    .incorrect {
        color: red;
    }

    .btn-block {
        width: 100%;
        padding: 15px;
        font-size: 18px;
    }

    .correct {
        color: green;
    }

    .incorrect {
        color: red;
    }

    .btn-block {
        width: 100%;
        padding: 15px;
        font-size: 18px;
    }

    #scoreOverlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: #0dcaf0;
        color: #fff;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        overflow: hidden;
    }

    #scoreOverlay.active {
        display: flex;
    }

    .score-close-btn {
        position: absolute;
        top: 16px;
        right: 20px;
        background: rgba(255, 255, 255, 0.25);
        border: none;
        color: #fff;
        font-size: 28px;
        line-height: 1;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        cursor: pointer;
    }

    .score-label {
        font-size: 13px;
        opacity: 0.85;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-bottom: 12px;
    }

    .score-numbers {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
        opacity: 0;
        transform: scale(0.4);
        transition: opacity 0.5s ease, transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .score-numbers.visible {
        opacity: 1;
        transform: scale(1);
    }

    .score-main {
        font-size: clamp(80px, 22vw, 140px);
        font-weight: 700;
        line-height: 1;
    }

    .score-separator {
        font-size: 48px;
        opacity: 0.7;
    }

    .score-total {
        font-size: 48px;
        opacity: 0.85;
    }

    .score-msg {
        font-size: 22px;
        margin-top: 20px;
        font-weight: 500;
        letter-spacing: 0.5px;
        opacity: 0;
        transform: translateY(12px);
        transition: opacity 0.4s ease 0.6s, transform 0.4s ease 0.6s;
    }

    .score-msg.visible {
        opacity: 1;
        transform: translateY(0);
    }

    .confetti-wrap {
        position: absolute;
        inset: 0;
        pointer-events: none;
        overflow: hidden;
    }

    .cdot {
        position: absolute;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        opacity: 0;
    }

    @keyframes burst {
        0% {
            opacity: 1;
            transform: translate(0, 0) scale(1);
        }

        100% {
            opacity: 0;
            transform: translate(var(--tx), var(--ty)) scale(0.3);
        }
    }

    .pulsering {
        position: absolute;
        inset: 0;
        border: 4px solid rgba(255, 255, 255, 0.4);
        opacity: 0;
        border-radius: 0;
    }

    @keyframes ringout {
        0% {
            opacity: 0.8;
            transform: scale(0.3);
        }

        100% {
            opacity: 0;
            transform: scale(3);
        }
    }
</style>

<?php
if (!empty($assessment_id)) {
    $query = $this->db->query("
        SELECT student_id, score
        FROM classworks
        WHERE assessment_id = $assessment_id
        ORDER BY score DESC
        LIMIT 10
    ");
    $top_students = $query->result_array();
} else {
    $top_students = [];
}
?>

<!-- Full-screen score overlay -->
<div id="scoreOverlay">
    <div class="confetti-wrap" id="confettiWrap"></div>
    <div class="pulsering" id="ring1"></div>
    <div class="pulsering" id="ring2"></div>

    <button class="score-close-btn" id="closeOverlayBtn" title="Close">&#x2715;</button>

    <div class="score-label">Your Score</div>
    <div class="score-numbers" id="scoreNums">
        <span class="score-main" id="countEl">0</span>
        <span class="score-separator">/</span>
        <span class="score-total"><?= $total ?></span>
    </div>
    <div class="score-msg" id="scoreMsg"></div>
</div>

<div class="container mt-3">
    <?php if (!empty($test_mode)): ?>
        <div class="alert alert-warning text-center">
            <strong>Test Mode</strong> &mdash; this attempt was not scored or recorded.
        </div>
    <?php else: ?>
        <?php $this->load->view('profile_info') ?>
    <?php endif; ?>
    <div class="card" style="border: none">
        <div class="card-body text-center">
            <button id="showScoreBtn" class="btn btn-primary btn-block">Show Score</button>
        </div>
        <div id="scoreSection" style="display: none;">
            <div class="card-header bg-info text-white">
                <h1 class="card-title text-center"><strong><?= $score ?></strong> out of <strong><?= $total ?></strong></h1>
            </div>
            <?php
            $not_cleared = ($this->class_student->where(['is_cleared' => NULL])->as_array()->fields('student_id')->get_all());
            $not_cleared = array_column($not_cleared, 'student_id');
            ?>
            <?php if (!in_array($this->session->student_id, $not_cleared)): ?>
                <!-- Score here -->
            <?php endif; ?>

            <div class="card-body">
                <?php if (!$this->session->exam_term && false): ?>
                    <?php foreach ($results as $index => $result): ?>
                        <div class="mb-4">
                            <p class="fw-bold"><b>Question <?= $index + 1 ?>: </b><?= nl2br(htmlspecialchars($result['question'])) ?></p>
                            <p>Your answer: <span class="<?= $result['is_correct'] ? 'correct' : 'incorrect' ?>"><?= $result['user_answer'] ?></span></p>
                            <p>Correct answer: <?= $result['correct_answer'] ?></p>
                        </div>
                        <hr>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div class="text-center">
                    <a href="<?= site_url('attendance') ?>" class="btn btn-outline-dark btn-block">Exit</a>
                </div>
            </div>
        </div>
        <?php if (!empty($top_students)): ?>
            <div class="mt-4">
                <h3 class="text-center">Top 10 Students</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Student ID</th>
                            <th>Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_students as $index => $student): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($student['student_id']) ?></td>
                                <td><?= htmlspecialchars($student['score']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-center mt-4">No classwork submissions found for this assessment.</p>
        <?php endif; ?>
    </div>
</div>

<script>
    // document.getElementById('showScoreBtn').addEventListener('click', function() {
    //     document.getElementById('scoreSection').style.display = 'block';
    //     this.style.display = 'none';
    // });

    const FINAL_SCORE = <?= (int)$score ?>;
    const TOTAL = <?= (int)$total ?>;

    function getScoreMessage(score, total) {
        const pct = score / total;
        if (pct === 1) return "Perfect score! Outstanding!";
        if (pct >= 0.9) return "Excellent work!";
        if (pct >= 0.75) return "Great job!";
        if (pct >= 0.5) return "Good effort!";
        return "Keep practicing!";
    }

    function spawnConfetti() {
        const wrap = document.getElementById('confettiWrap');
        wrap.innerHTML = '';
        const colors = ['#fff', '#ffd700', '#ff6b6b', '#90ee90', '#87ceeb'];
        const cx = window.innerWidth / 2,
            cy = window.innerHeight / 2;
        for (let i = 0; i < 60; i++) {
            const d = document.createElement('div');
            d.className = 'cdot';
            const angle = Math.random() * Math.PI * 2;
            const dist = 80 + Math.random() * Math.min(cx, cy) * 0.9;
            d.style.cssText =
                `left:${cx}px;top:${cy}px;` +
                `background:${colors[i % colors.length]};` +
                `--tx:${(Math.cos(angle)*dist).toFixed(1)}px;` +
                `--ty:${(Math.sin(angle)*dist).toFixed(1)}px;` +
                `animation:burst ${0.5+Math.random()*0.6}s ease-out ${Math.random()*0.25}s forwards;`;
            wrap.appendChild(d);
        }
    }

    function animateRings() {
        ['ring1', 'ring2'].forEach(function(id, i) {
            const el = document.getElementById(id);
            el.style.animation = 'none';
            void el.offsetWidth;
            el.style.animation = 'ringout 0.8s ease-out ' + (i * 0.18) + 's forwards';
        });
    }

    function countUp(el, from, to, duration) {
        const start = performance.now();
        (function tick(now) {
            const t = Math.min((now - start) / duration, 1);
            const ease = 1 - Math.pow(1 - t, 3);
            el.textContent = Math.round(from + (to - from) * ease);
            if (t < 1) requestAnimationFrame(tick);
            else el.textContent = to;
        })(performance.now());
    }

    function resetOverlay() {
        document.getElementById('scoreNums').classList.remove('visible');
        document.getElementById('scoreMsg').classList.remove('visible');
        document.getElementById('scoreMsg').textContent = '';
        document.getElementById('countEl').textContent = '0';
        document.getElementById('confettiWrap').innerHTML = '';
    }

    document.getElementById('showScoreBtn').addEventListener('click', function() {
        resetOverlay();
        document.getElementById('scoreOverlay').classList.add('active');

        setTimeout(function() {
            document.getElementById('scoreNums').classList.add('visible');
            animateRings();
            spawnConfetti();
        }, 100);

        setTimeout(function() {
            countUp(document.getElementById('countEl'), 0, FINAL_SCORE, 900);
        }, 300);

        setTimeout(function() {
            const msg = document.getElementById('scoreMsg');
            msg.textContent = getScoreMessage(FINAL_SCORE, TOTAL);
            msg.classList.add('visible');
        }, 700);
    });

    document.getElementById('closeOverlayBtn').addEventListener('click', function() {
        document.getElementById('scoreOverlay').classList.remove('active');
    });
</script>

<?php $this->load->view('footer') ?>
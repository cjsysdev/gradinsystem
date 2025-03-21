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
</style>

<div class="container mt-3">
    <?php $this->load->view('profile_info') ?>
    <div class="card" style="border: none">
        <div class="card-body text-center">
            <button id="showScoreBtn" class="btn btn-primary btn-block">Show Score</button>
        </div>
        <div id="scoreSection" style="display: none;">
            <div class="card-header bg-info text-white">
                <h1 class="card-title text-center"><strong><?= $score ?></strong> out of <strong><?= $total ?></strong></h1>
            </div>
            <div class="card-body">
                <?php $midterm = true;
                if (!$midterm): ?>
                    <?php foreach ($results as $index => $result): ?>
                        <div class="mb-4">
                            <p class="fw-bold"><b>Question <?= $index + 1 ?>: </b><?= $result['question'] ?></p>
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
    </div>
</div>

<script>
    document.getElementById('showScoreBtn').addEventListener('click', function() {
        document.getElementById('scoreSection').style.display = 'block';
        this.style.display = 'none';
    });
</script>

<?php $this->load->view('footer') ?>
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

<?php
$query = $this->db->query("
    SELECT student_id, score 
    FROM gradingsystem.classworks 
    WHERE assessment_id = $assessment_id 
    ORDER BY score DESC 
    LIMIT 10
");
$top_students = $query->result_array();
?>


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
                <?php if (!$this->session->exam_term): ?>
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
    document.getElementById('showScoreBtn').addEventListener('click', function() {
        document.getElementById('scoreSection').style.display = 'block';
        this.style.display = 'none';
    });
</script>

<?php $this->load->view('footer') ?>
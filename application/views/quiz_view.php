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
</style>

<div class="container mt-3 mb-5">
    <div class="dashboard">
        <?php $this->load->view('profile_info') ?>
        <div class="card " style="border: none;">
            <div class="card-body p-0">
                <?php $assessment_id = (explode('/', uri_string())[1]) ?>
                <form action="<?= site_url('quiz/submit/' . $assessment_id) ?>" method="post">
                    <?php foreach ($questions as $index => $question): ?>
                        <div class="mb-4">
                            <div class="question-block">
                                <strong>Question <?= $index + 1 ?>:</strong> <?= nl2br(htmlspecialchars($question['question'])) ?>
                            </div>
                            <?php foreach ($question['choices'] as $choiceIndex => $choice): ?>
                                <div>
                                    <input class="form-check-input" type="radio" name="answers[<?= $index ?>]" value="<?= $choice ?>" id="choice<?= $index ?>_<?= $choiceIndex ?>">
                                    <label class="form-check-label" for="choice<?= $index ?>_<?= $choiceIndex ?>">
                                        <?= htmlspecialchars($choice) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <hr>
                    <?php endforeach; ?>
                    <div class="text-center">
                        <button type="submit" class="btn btn-info btn-block">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('footer') ?>
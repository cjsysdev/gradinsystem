<?php $this->load->view('header'); ?>

<div class="container">
    <div class="dashboard">
        <?php $this->load->view('profile_only'); ?>

        <!-- Dropdown to select an assessment -->
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="mb-4">
                    <div class="dropdown">
                        <button class="btn btn-secondary btn-block dropdown-toggle w-100" type="button" id="assessmentDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <?= isset($selected_assessment_id) ? "Selected: Assessment ID $selected_assessment_id" : "Select an Assessment" ?>
                        </button>
                        <ul class="dropdown-menu w-100 shadow-sm" aria-labelledby="assessmentDropdown">
                            <?php foreach ($assessments as $assessment): ?>
                                <li>
                                    <a class="dropdown-item" href="<?= base_url("AdminController/all_submissions/" . $assessment['assessment_id']) ?>">
                                        <?= $assessment['title'] ?> (ID: <?= $assessment['assessment_id'] ?>)
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Display submissions for the selected assessment -->
        <div class="row justify-content-center mt-5">
            <div class="col">
                <?php if (!empty($submissions)): ?>
                    <?php foreach ($submissions as $row): ?>
                        <div class="card mb-3 shadow-sm">
                            <div class="card-body">
                                <h3 class="card-title mb-1"><?= $row['classwork_id'] . " - " . $row['lastname'] . ", " . $row['firstname'] ?></h3>
                                <hr>
                                <p class="card-text mb-3"><?= $row['created_at'] ?></p>

                                <!-- Form to submit score -->
                                <form action="<?= base_url('ClassworkController/add_score') ?>" method="POST">
                                    <input type="hidden" name="classwork_id" value="<?= $row['classwork_id'] ?>">
                                    <input type="hidden" name="student_id" value="<?= $row['trans_no'] ?>">
                                    <input type="hidden" name="assessment_id" value="<?= $selected_assessment_id ?>">
                                    <div class="input-group mb-3">
                                        <input type="number" name="score" class="form-control" placeholder="Enter score" min="0" required>
                                        <button type="submit" class="btn btn-info">Submit</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-warning">No submissions found for the selected assessment.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function redirectToAssessment(assessmentId) {
        if (assessmentId) {
            // Redirect to the selected assessment's URL
            window.location.href = '<?= base_url("AdminController/all_submissions") ?>/' + assessmentId;
        }
    }
</script>

<?php $this->load->view('footer') ?>
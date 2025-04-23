<?php $this->load->view('header'); ?>

<div class="container">
    <div class="dashboard">
        <?php $this->load->view('profile_only'); ?>
        <?php $this->load->view('admin/nav_bar'); ?>

        <!-- Dropdown to select an assessment -->
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="mb-4">
                    <div class="dropdown">
                        <button class="btn btn-secondary btn-block dropdown-toggle w-100" type="button" id="assessmentDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <?= isset($selected_assessment_id) ? "Selected: Assessment ID $selected_assessment_id" : "Select an Assessment" ?>
                        </button>
                        <div class="card mb-3 shadow-sm mt-2">
                            <div class="card-body">
                                <h3 id="student_name" class="card-title">lastname, firstname</h3>
                            </div>
                        </div>
                        <div class="row justify-content-center mt-3">
                            <div class="col-md-6 text-center">
                                <button type="button" class="btn btn-success mb-4" onclick="randomizeStudent()">Randomize Student</button>
                            </div>
                        </div>
                        <div class="row justify-content-center">
                            <div class="col-md-6 text-center">
                                <button type="button" class="btn btn-secondary mb-4" onclick="toggleSubmissions()">Hide Submissions</button>
                            </div>
                        </div>
                        <ul class="dropdown-menu w-100 shadow-sm" aria-labelledby="assessmentDropdown">
                            <?php foreach ($assessments as $assessment): ?>
                                <li>
                                    <a class="dropdown-item" href="<?= base_url("AdminController/all_submissions/" . $assessment['assessment_id']) ?>">
                                        <?= $assessment['title'] ?> (ID: <?= $assessment['assessment_id'] ?>) <?= $assessment['class_schedule']->section ?>
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
            <div id="submissionsContainer" class="col">
                <?php if (!empty($submissions)): ?>
                    <?php foreach ($submissions as $row): ?>
                        <div class="card mb-3 shadow-sm">
                            <div class="card-body">
                                <h3 class="card-title mb-1"><?= $row['classwork_id'] . " - " . $row['lastname'] . ", " . $row['firstname'] ?></h3>
                                <hr>
                                <p class="card-text mb-3"><?= $row['created_at'] ?></p>
                                <!-- Button to open modal -->
                                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#viewSubmissionModal" onclick="loadSubmission(<?= htmlspecialchars(json_encode($row['code']), ENT_QUOTES, 'UTF-8') ?>, '<?= $row['file_upload'] ?>')">
                                    View Submission
                                </button>

                                <!-- Form to submit score -->
                                <form action="<?= base_url('ClassworkController/add_score') ?>" method="POST">
                                    <input type="hidden" name="classwork_id" value="<?= $row['classwork_id'] ?>">
                                    <input type="hidden" name="student_id" value="<?= $row['trans_no'] ?>">
                                    <input type="hidden" name="assessment_id" value="<?= $selected_assessment_id ?>">
                                    <div class="input-group mb-3">
                                        <a href="<?= base_url('add_rand_score/' . $row['classwork_id'] . '/5' . "/$selected_assessment_id") ?>" type="button" class="btn btn-outline-secondary mr-1 ml-1" name="score" value="good">Late</a>
                                        <a href="<?= base_url('add_rand_score/' . $row['classwork_id'] . '/6' . "/$selected_assessment_id") ?>" type="button" class="btn btn-outline-secondary mr-1 ml-1" name="score" value="good">6</a>
                                        <a href="<?= base_url('add_rand_score/' . $row['classwork_id'] . '/7' . "/$selected_assessment_id") ?>" type="button" class="btn btn-outline-secondary mr-1 ml-1" name="score" value="good">7</a>
                                        <a href="<?= base_url('add_rand_score/' . $row['classwork_id'] . '/8' . "/$selected_assessment_id") ?>" type="button" class="btn btn-outline-secondary mr-1 ml-1" name="score" value="good">8</a>
                                        <a href="<?= base_url('add_rand_score/' . $row['classwork_id'] . '/9' . "/$selected_assessment_id") ?>" type="button" class="btn btn-outline-secondary mr-1 ml-1" name="score" value="good">9</a>
                                        <a href="<?= base_url('add_rand_score/' . $row['classwork_id'] . '/10' . "/$selected_assessment_id") ?>" type="button" class="btn btn-outline-secondary mr-1 ml-1" name="score" value="good">10</a>
                                        <input type="number" name="score" class="form-control mr-1 ml-1" placeholder="Enter score" min="0" required>
                                        <button type="submit" class="btn btn-info mr-1 ml-1">Submit</button>
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

<!-- Modal for viewing submission -->
<div class="modal fade" id="viewSubmissionModal" tabindex="-1" aria-labelledby="viewSubmissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewSubmissionModalLabel">Submission</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Content will be dynamically loaded -->
                <div id="submissionContent" class="text-left">
                    <p>Loading submission...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function loadSubmission(code, fileUpload) {
        const submissionContent = document.getElementById('submissionContent');

        if (fileUpload && fileUpload !== 'null') {
            // Display PDF file in an iframe
            submissionContent.innerHTML = `
                <iframe src="<?= base_url('uploads/classworks/') ?>${fileUpload}" width="100%" height="600px" style="border: none;"></iframe>
            `;
        } else if (code && code !== 'null') {
            // Display code in a preformatted block
            submissionContent.innerHTML = `
                <label for="code-viewer" class="form-label">Submitted Code:</label>
                <pre id="code-viewer" style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #ddd;">${code}</pre>
            `;
        } else {
            // No submission available
            submissionContent.innerHTML = `<p class="text-danger">No submission available.</p>`;
        }
    }

    function toggleSubmissions() {
        const submissionsSection = document.querySelector('.row.justify-content-center.mt-5 .col');
        const toggleButton = document.querySelector('.btn-warning');

        if (submissionsSection.style.display === 'none') {
            submissionsSection.style.display = 'block';
            toggleButton.innerText = 'Hide Submissions';
        } else {
            submissionsSection.style.display = 'none';
            toggleButton.innerText = 'Show Submissions';
        }
    }

    function randomizeStudent() {
        const students = <?= json_encode(array_map(function ($row) {
                                return [
                                    'classwork_id' => $row['classwork_id'],
                                    'lastname' => $row['lastname'],
                                    'firstname' => $row['firstname'],
                                    'student_id' => $row['trans_no']
                                ];
                            }, $submissions)) ?>;

        if (students.length > 0) {
            const randomIndex = Math.floor(Math.random() * students.length);
            const randomStudent = students[randomIndex];

            document.getElementById('student_name').innerHTML = `
                    ${randomStudent.classwork_id} - ${randomStudent.lastname}, ${randomStudent.firstname}
                    <a href="<?= base_url('add_rand_score/') ?>${randomStudent.classwork_id}/10/<?= $selected_assessment_id ?>" type="button" class="btn btn-outline-secondary btn-sm ml-2" name="score" value="good">SCORE</a>
                `;
        } else {
            document.getElementById('student_name').innerText = 'No students available for random selection.';
        }
    }

    const assessmentId = <?= json_encode($selected_assessment_id) ?>;

    function checkNewSubmissions() {
        console.log("checking");
        fetch(`<?= base_url('admin/check_new_submissions_by_assessment/') ?>${assessmentId}`)
            .then(response => response.json())
            .then(data => {
                const submissionsContainer = document.getElementById('submissionsContainer');
                submissionsContainer.innerHTML = ''; // Clear the container

                if (data.length > 0) {
                    data.forEach(submission => {
                        const submissionCard = `
                                <div class="card mb-3 shadow-sm">
                                    <div class="card-body">
                                        <h3 class="card-title">${submission.classwork_id} - ${submission.lastname}, ${submission.firstname}</h3>
                                        <p class="card-text">${submission.created_at}</p>
                                        <button type="button" class="btn btn-primary" onclick="viewSubmission(${submission.classwork_id})">View Submission</button>
                                    </div>
                                </div>
                            `;
                        submissionsContainer.innerHTML += submissionCard;
                    });
                } else {
                    submissionsContainer.innerHTML = '<div class="alert alert-warning">No submissions found.</div>';
                }
            })
            .catch(error => console.error('Error fetching submissions:', error));
    }

    // setInterval(checkNewSubmissions, 10000);
</script>

<?php $this->load->view('footer') ?>
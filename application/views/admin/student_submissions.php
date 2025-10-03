<!-- filepath: c:\wamp64\www\gradingSystem\application\views\admin\student_submissions.php -->
<?php $this->load->view('header'); ?>

<div class="container">

    <?php
    $this->load->view('profile_only');
    $this->load->view('admin/nav_bar');
    ?>

    <form id="studentSearchForm" class="mb-4">
        <div class="input-group">
            <input type="text" id="studentSearchInput" class="form-control" placeholder="Search by Firstname or Lastname">
            <button type="submit" class="btn btn-primary">Search</button>
        </div>
        <select id="studentDropdown" name="student_id" class="form-control mt-2" style="display:none;"></select>
    </form>

    <?php if (!empty($submissions)): ?>
        <h2><?= $submissions[0]['firstname'] . ' ' . $submissions[0]['lastname'] . ' - ' . $submissions[0]['student_id'] ?></h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Assessment Title</th>
                    <th>Submission Date</th>
                    <th>Score</th>
                    <th>Max</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $classwork): ?>
                    <tr>
                        <td><?= $classwork['title'] ?></td>
                        <td><?= $classwork['created_at'] ? date('Y-m-d H:i:s', strtotime($classwork['created_at'])) : 'N/A' ?></td>
                        <td><?= $classwork['score'] !== null ? $classwork['score'] : 'Not Graded' ?></td>
                        <td><?= $classwork['max_score'] ?></td>
                        <td><?= $classwork['status'] === 'missing' ? '<span class="text-danger">Missing</span>' : '<span class="text-success">Submitted</span>' ?></td>
                        <td>
                            <?php if ($classwork['score'] === null && $classwork['status'] !== 'missing'): ?>
                                <!-- Form to add score -->
                                <form action="<?= base_url('ClassworkController/add_score') ?>" method="POST" class="d-inline">
                                    <input type="hidden" name="classwork_id" value="<?= $classwork['classwork_id'] ?>">
                                    <input type="hidden" name="assessment_id" value="<?= $classwork['assessment_id'] ?>">
                                    <input type="hidden" name="student_id" value="<?= $classwork['student_id'] ?>">
                                    <input type="number" name="score" class="form-control d-inline w-25" placeholder="Enter score" min="0" required>
                                    <button type="submit" class="btn btn-info btn-sm"><i class="fa fa-plus" aria-hidden="true"></i></button>
                                </form>
                                <!-- Button to view classwork -->
                                <button type="button" class="btn btn-outline-primary btn-sm ml-2" onclick="loadSubmission('<?= htmlspecialchars($classwork['code'] ?? '') ?>', '<?= htmlspecialchars($classwork['file_upload'] ?? '') ?>')" data-toggle="modal" data-target="#viewSubmissionModal">
                                    <i class="fa fa-folder-open" aria-hidden="true"></i>
                                </button>
                            <?php elseif ($classwork['status'] === 'missing'): ?>
                                <span class="text-muted">No actions available</span>
                            <?php else: ?>
                                <span class="text-muted">Graded</span>
                                <!-- Button to view classwork -->
                                <button type="button" class="btn btn-outline-primary btn-sm ml-2" onclick="loadSubmission('<?= htmlspecialchars($classwork['code'] ?? '') ?>', '<?= htmlspecialchars($classwork['file_upload'] ?? '') ?>')" data-toggle="modal" data-target="#viewSubmissionModal">
                                    <i class="fa fa-folder-open" aria-hidden="true"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">No submissions found for this student.</div>
    <?php endif; ?>
</div>

<!-- Modal for viewing submission -->
<div class="modal fade" id="viewSubmissionModal" tabindex="-1" aria-labelledby="viewSubmissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewSubmissionModalLabel">Submission</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
    document.getElementById('studentSearchForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const searchVal = document.getElementById('studentSearchInput').value.trim();
        if (!searchVal) return;

        fetch('<?= base_url('AdminController/search_students?search=') ?>' + encodeURIComponent(searchVal))
            .then(res => res.json())
            .then(students => {
                const dropdown = document.getElementById('studentDropdown');
                dropdown.innerHTML = '';
                if (students.length > 0) {
                    dropdown.style.display = 'block';
                    students.forEach(student => {
                        const option = document.createElement('option');
                        option.value = student.trans_no;
                        option.textContent = `${student.firstname} ${student.lastname} (${student.trans_no})`;
                        dropdown.appendChild(option);
                    });
                    // If only one student, auto-redirect
                    if (students.length === 1) {
                        window.location.href = '<?= base_url('AdminController/student_submissions?student_id=') ?>' + students[0].trans_no;
                    }
                } else {
                    dropdown.style.display = 'none';
                    alert('No students found.');
                }
            });
    });

    // On dropdown change, reload page with selected student
    document.getElementById('studentDropdown').addEventListener('change', function() {
        window.location.href = '<?= base_url('AdminController/student_submissions?student_id=') ?>' + this.value;
    });

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
</script>

<?php $this->load->view('footer'); ?>
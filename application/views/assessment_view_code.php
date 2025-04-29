<?php
if ($classwork['iotype_id'] == '4' || $classwork['iotype_id'] == '3') {
    redirect('quiz/' . $classwork['assessment_id']);
} ?>

<?php $this->load->view('header'); ?>

<div class="container">
    <?php $this->load->view('profile_info'); ?>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Your Work</h5>
            <span class="badge badge-success">Assigned</span>
        </div>
        <div class="card-body">
            <p><?= $classwork['description'] ?></p>

            <form id="submission-form" action="<?= base_url(
                                                    'AssessmentController/submit_classwork'
                                                ) ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="assessment_id" value="<?= $classwork['assessment_id'] ?>">
                <input type="hidden" name="student_id" value="<?= $this->session
                                                                    ->student_id ?>">

                <!-- filepath: c:\xampp\htdocs\gradingsystem\application\views\assessment_view.php -->
                <?php if (!empty($classwork['pdf_file_path'])): ?>
                    <!-- Button to open the PDF in a modal -->
                    <button type="button" class="btn btn-success btn-block mb-3" data-bs-toggle="modal" data-bs-target="#pdfModal">
                        View Given File
                    </button>

                    <!-- Modal to display the PDF -->
                    <div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="pdfModalLabel">Given PDF</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <iframe src="<?= base_url(
                                                        $classwork['pdf_file_path']
                                                    ) ?>" width="100%" height="600px" style="border: none;"></iframe>
                                </div>
                                <div class="modal-footer">
                                    <!-- Download Button -->
                                    <a href="<?= base_url(
                                                    $classwork['pdf_file_path']
                                                ) ?>" class="btn btn-primary" download>
                                        Download
                                    </a>
                                    <!-- Close Button -->
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p>No given PDF file uploaded for this classwork.</p>
                <?php endif; ?>

                <!-- Other assessment details -->

                <!-- Option to choose between code input or file upload -->
                <div class="mb-4">
                    <div class="dropdown">
                        <button class="btn btn-secondary btn-block dropdown-toggle w-100" type="button" id="submissionTypeDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            Submission Type: <span id="selectedSubmissionType">Input Code</span>
                        </button>
                        <ul class="dropdown-menu w-100 shadow-sm" aria-labelledby="submissionTypeDropdown">
                            <li><a class="dropdown-item" href="#" onclick="setSubmissionType('code')">Input Code</a></li>
                            <li><a class="dropdown-item" href="#" onclick="setSubmissionType('file')">Upload File</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Code input section -->
                <div id="code-input-section" class="mb-3">
                    <label for="code-editor" class="form-label">Enter your submission (Code/Text):</label>
                    <textarea id="code-editor" name="code" class="form-control" rows="10"
                        placeholder="Write your code or text here..."></textarea>
                </div>

                <!-- File upload section -->
                <div id="file-upload-section" class="mb-3" style="display: none;">
                    <label for="file-upload" class="form-label">Upload a file:</label>
                    <input type="file" id="file-upload" name="file-upload" class="form-control">
                </div>
                <div class="text-center">
                    <button type="button" class="btn btn-primary btn-block" onclick="confirmSubmission()">Turn
                        In</button>
                </div>
            </form>
        </div>
        <div class="card-footer text-center">
            <p class="text-muted">Work cannot be turned in after the due date.</p>
        </div>
    </div>
</div>

<!-- Modal for Submission Confirmation -->
<div class="modal fade" id="submissionModal" tabindex="-1" aria-labelledby="submissionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="submissionModalLabel">Confirm Submission</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to submit your work? Please ensure that you have either entered code or uploaded a
                file.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitForm()">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
    function setSubmissionType(type) {
        const codeInputSection = document.getElementById('code-input-section');
        const fileUploadSection = document.getElementById('file-upload-section');
        const selectedSubmissionType = document.getElementById('selectedSubmissionType');

        if (type === 'code') {
            codeInputSection.style.display = 'block';
            fileUploadSection.style.display = 'none';
            selectedSubmissionType.textContent = 'Input Code';
        } else if (type === 'file') {
            codeInputSection.style.display = 'none';
            fileUploadSection.style.display = 'block';
            selectedSubmissionType.textContent = 'Upload File';
        }
    }

    function confirmSubmission() {
        const codeEditor = document.getElementById('code-editor').value.trim();
        const fileUpload = document.getElementById('file-upload').value;

        // if (!codeEditor || !fileUpload) {
        //   alert('Please enter code or upload a file before submitting.');
        //   return;
        // }

        const modal = new bootstrap.Modal(document.getElementById('submissionModal'));
        modal.show();
    }

    function submitForm() {
        document.getElementById('submission-form').submit();
    }
</script>

<?php $this->load->view('footer'); ?>
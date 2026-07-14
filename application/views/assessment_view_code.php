<?php
// Assessment Type (Major Exam/Quiz) and Widget are independent settings — a
// widget, if one's assigned, always takes priority. Only fall back to the
// legacy json_file_path quiz flow when there's no widget to render instead.
if (empty($widget) && ($classwork['iotype_id'] == '4' || $classwork['iotype_id'] == '3')) {
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
            <small id="autosave-status" class="text-muted d-block mb-2"></small>
            <p><?= $classwork['description'] ?></p>

            <form id="submission-form" action="<?= base_url(
                                                    'AssessmentController/submit_classwork'
                                                ) ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="assessment_id" value="<?= $classwork['assessment_id'] ?>">
                <input type="hidden" name="student_id" value="<?= $this->session
                                                                    ->student_id ?>">

                <!-- filepath: c:\wamp64\www\gradingSystem\application\views\assessment_view_code.php -->
                <?php if (!empty($classwork['pdf_file_path'])): ?>
                    <!-- Button to open the file in a modal -->
                    <button type="button" class="btn btn-success btn-block mb-3" data-bs-toggle="modal" data-bs-target="#fileModal">
                        View Given File
                    </button>

                    <!-- Modal to display the file -->
                    <div class="modal fade" id="fileModal" tabindex="-1" aria-labelledby="fileModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="fileModalLabel">Given File</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <?php
                                    $file_extension = pathinfo($classwork['pdf_file_path'], PATHINFO_EXTENSION);
                                    if (in_array($file_extension, ['pdf', 'txt', 'c', 'sql', 'php', 'html', 'js', 'css', 'jpg', 'png', 'cpp'])): ?>
                                        <!-- Display file content in an iframe for supported file types -->
                                        <iframe src="<?= base_url($classwork['pdf_file_path']) ?>" width="100%" height="600px" style="border: none;"></iframe>
                                    <?php else: ?>
                                        <!-- Display a message for unsupported file types -->
                                        <p>This file type cannot be previewed. Please download it to view.</p>
                                    <?php endif; ?>
                                </div>
                                <div class="modal-footer">
                                    <!-- Download Button -->
                                    <a href="<?= base_url($classwork['pdf_file_path']) ?>" class="btn btn-primary" download>
                                        Download
                                    </a>
                                    <!-- Close Button -->
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <p style="color: gray;">No given file uploaded for this classwork.</p>
                <?php endif; ?>

                <!-- Other assessment details -->

                <?php if (!empty($widget)): ?>
                    <!-- Widget-driven submission: the widget serializes its state into
                         this hidden field right before the form submits (see submitForm()).
                         Deliberately NOT id="code-editor" — footer.php auto-attaches
                         CodeMirror to that id on every page, which would render an
                         empty editor box next to the widget. -->
                    <input type="hidden" id="widget-code-value" name="code">
                    <?php $this->load->view($widget['input_view'], ['config' => $widget_config, 'readonly' => false, 'existing' => null]); ?>
                <?php else: ?>
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
                <?php endif; ?>
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
        const codeEditorEl = document.getElementById('code-editor');
        const fileUploadEl = document.getElementById('file-upload');
        const codeEditor = codeEditorEl ? codeEditorEl.value.trim() : '';
        const fileUpload = fileUploadEl ? fileUploadEl.value : '';

        // if (!codeEditor || !fileUpload) {
        //   alert('Please enter code or upload a file before submitting.');
        //   return;
        // }

        const modal = new bootstrap.Modal(document.getElementById('submissionModal'));
        modal.show();
    }

    function submitForm() {
        // Widget views (see application/views/widgets/*) define this to copy
        // their state into the hidden #widget-code-value field before the real submit.
        if (typeof window.serializeWidgetBeforeSubmit === 'function') {
            window.serializeWidgetBeforeSubmit();
        } else if (window.editor) {
            // CodeMirror.fromTextArea (footer.php) never writes back into the
            // underlying #code-editor textarea on its own — without this,
            // the "code" POST field is whatever was there on page load (blank).
            window.editor.save();
        }
        clearDraft();
        document.getElementById('submission-form').submit();
    }

    // Autosave: refresh/close/navigate-away used to silently wipe whatever a
    // student had typed, since nothing was persisted before the final "Turn
    // In" click. Mirror the in-progress answer to localStorage and restore it
    // on load. Covers both the plain CodeMirror editor and any widget, via
    // the getWidgetState/setWidgetState contract every widget already
    // implements for group_workspace.php's live-collaboration sync.
    const DRAFT_KEY = 'classwork_draft_' + <?= json_encode($classwork['assessment_id']) ?> +
        '_' + <?= json_encode($this->session->student_id) ?>;

    function getCurrentAnswer() {
        if (typeof window.getWidgetState === 'function') return window.getWidgetState();
        if (window.editor) return window.editor.getValue();
        const el = document.getElementById('code-editor');
        return el ? el.value : '';
    }

    function saveDraft() {
        const value = getCurrentAnswer();
        if (!value) return;
        localStorage.setItem(DRAFT_KEY, value);
        const status = document.getElementById('autosave-status');
        if (status) status.textContent = 'Draft auto-saved at ' + new Date().toLocaleTimeString();
    }

    function clearDraft() {
        localStorage.removeItem(DRAFT_KEY);
    }

    function restoreDraft() {
        const draft = localStorage.getItem(DRAFT_KEY);
        if (!draft) return;
        if (typeof window.setWidgetState === 'function') {
            window.setWidgetState(draft);
        } else if (window.editor) {
            window.editor.setValue(draft);
        } else {
            const el = document.getElementById('code-editor');
            if (el) el.value = draft;
        }
        const status = document.getElementById('autosave-status');
        if (status) status.textContent = 'Draft restored from your last session.';
    }

    document.addEventListener('DOMContentLoaded', function () {
        restoreDraft();
        setInterval(saveDraft, 3000);
    });
    window.addEventListener('beforeunload', saveDraft);
</script>

<?php $this->load->view('footer'); ?>
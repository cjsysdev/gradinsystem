<?php $this->load->view('header') ?>
<?php
// Timed/Secure Quiz backup-file upload — see SecureQuizController::backup().
// Shown while the instructor has this section's backup window open.
?>
<title>Upload Quiz Backup</title>

<div class="container py-4" style="max-width: 560px;">
    <div class="card shadow-sm">
        <div class="card-body">
            <h4 class="card-title mb-1"><i class="fas fa-cloud-upload-alt"></i> Upload your quiz backup</h4>
            <p class="text-muted mb-3"><?= htmlspecialchars($assessment->title ?? '') ?></p>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <p class="mb-2">
                If your submission failed because of the network, your phone downloaded a backup of your answers
                when you pressed Submit. Find the file named
                <strong>quiz_submission_<?= (int) $this->session->student_id ?>.json</strong>
                (usually in your <strong>Downloads</strong> folder) and upload it here.
            </p>
            <p class="small text-muted mb-3">
                Your answers are checked again by the server. You can upload only once.
                <?php if (!empty($until)): ?>
                    Upload is open until <strong><?= date('g:i A', strtotime($until)) ?></strong>.
                <?php endif; ?>
            </p>

            <form method="post" action="<?= $upload_url ?>" enctype="multipart/form-data"
                  onsubmit="this.querySelector('button[type=submit]').disabled = true;">
                <div class="form-group">
                    <?php // No accept= filter: some Android pickers grey out a
                    // downloaded .json they don't recognise, which is exactly
                    // the file we need. The server rejects anything else. ?>
                    <input type="file" name="backup_file" class="form-control-file" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-upload"></i> Upload backup
                </button>
            </form>

            <?php if (!empty($can_continue)): ?>
                <hr>
                <a href="<?= $continue_url ?>" class="btn btn-outline-secondary btn-block">
                    I didn't submit yet &mdash; continue the quiz
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $this->load->view('footer') ?>

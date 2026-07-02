<?php
if ($assessment['iotype_id'] == '4' || $assessment['iotype_id'] == '3') {
    redirect('quiz/' . $assessment['assessment_id']);
} ?>

<?php $this->load->view('header'); ?>

<div class="container">
    <?php $this->load->view('profile_info'); ?>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?= htmlspecialchars($this->session->flashdata('success')) ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($this->session->flashdata('error')) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><?= htmlspecialchars($assessment['title']) ?></h5>
            <span class="badge badge-info">Group: <?= htmlspecialchars($group['group_name']) ?></span>
        </div>
        <div class="card-body">
            <p><?= $assessment['description'] ?></p>

            <?php if (!empty($assessment['pdf_file_path'])): ?>
                <button type="button" class="btn btn-success btn-block mb-3" data-bs-toggle="modal" data-bs-target="#fileModal">
                    View Given File
                </button>
                <div class="modal fade" id="fileModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Given File</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <?php
                                $file_extension = pathinfo($assessment['pdf_file_path'], PATHINFO_EXTENSION);
                                if (in_array($file_extension, ['pdf', 'txt', 'c', 'sql', 'php', 'html', 'js', 'css', 'jpg', 'png', 'cpp'])): ?>
                                    <iframe src="<?= base_url($assessment['pdf_file_path']) ?>" width="100%" height="600px" style="border: none;"></iframe>
                                <?php else: ?>
                                    <p>This file type cannot be previewed. Please download it to view.</p>
                                <?php endif; ?>
                            </div>
                            <div class="modal-footer">
                                <a href="<?= base_url($assessment['pdf_file_path']) ?>" class="btn btn-primary" download>Download</a>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label">
                        Shared draft &mdash; everyone in your group edits this together
                    </label>
                    <textarea id="shared-draft" class="form-control" rows="14"
                        placeholder="Write your group's answer here..."><?= htmlspecialchars($state['content']) ?></textarea>
                    <p class="text-muted small mt-1" id="save-status">&nbsp;</p>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Group members</label>
                    <ul class="list-group mb-3" id="member-list">
                        <?php foreach ($members as $m): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center" data-student="<?= htmlspecialchars($m['student_id']) ?>">
                                <span><?= htmlspecialchars(trim($m['firstname'] . ' ' . $m['lastname'])) ?></span>
                                <?php if ((string) $m['student_id'] === (string) $student_id): ?>
                                    <input type="checkbox" id="ready-toggle" <?= !empty($ready_map[$m['student_id']]) ? 'checked' : '' ?>>
                                <?php else: ?>
                                    <span class="badge <?= !empty($ready_map[$m['student_id']]) ? 'badge-success' : 'badge-secondary' ?> ready-badge">
                                        <?= !empty($ready_map[$m['student_id']]) ? 'Ready' : 'Not ready' ?>
                                    </span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <form method="post" action="<?= base_url('GroupWorkController/submit_group/' . $assessment['assessment_id']) ?>"
                          onsubmit="return confirm('Submit this draft for the whole group?');">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-paper-plane"></i> Submit for Group
                        </button>
                    </form>
                    <p class="text-muted small mt-2">
                        Any member can submit at any time. Marking "ready" is just a signal for your teammates &mdash; it doesn't lock submission.
                    </p>
                </div>
            </div>
        </div>
        <div class="card-footer text-center">
            <p class="text-muted">Work cannot be turned in after the due date.</p>
        </div>
    </div>
</div>

<script>
(function () {
    const BASE = '<?= base_url() ?>';
    const assessmentId = <?= (int) $assessment['assessment_id'] ?>;
    const myStudentId = <?= json_encode((string) $student_id) ?>;

    const draft = document.getElementById('shared-draft');
    const saveStatus = document.getElementById('save-status');
    const readyToggle = document.getElementById('ready-toggle');

    let saveTimer = null;
    let lastSavedContent = draft.value;

    function saveDraft() {
        if (draft.value === lastSavedContent) return;
        lastSavedContent = draft.value;
        saveStatus.textContent = 'Saving...';
        fetch(BASE + 'GroupWorkController/save_draft/' + assessmentId, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'content=' + encodeURIComponent(draft.value),
        })
        .then(r => r.json())
        .then(d => { saveStatus.textContent = d.ok ? 'Saved' : 'Failed to save'; });
    }

    draft.addEventListener('input', () => {
        clearTimeout(saveTimer);
        saveStatus.textContent = 'Typing...';
        saveTimer = setTimeout(saveDraft, 800);
    });

    if (readyToggle) {
        readyToggle.addEventListener('change', () => {
            fetch(BASE + 'GroupWorkController/toggle_ready/' + assessmentId, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'ready=' + (readyToggle.checked ? '1' : ''),
            });
        });
    }

    function pollState() {
        fetch(BASE + 'GroupWorkController/state/' + assessmentId)
            .then(r => r.json())
            .then(d => {
                if (!d.ok) return;

                // Don't clobber the draft while the student is actively typing.
                if (document.activeElement !== draft && d.content !== draft.value) {
                    draft.value = d.content;
                    lastSavedContent = d.content;
                }

                d.members.forEach(m => {
                    const li = document.querySelector('#member-list li[data-student="' + m.student_id + '"]');
                    if (!li) return;
                    if (m.student_id === myStudentId) return;
                    const badge = li.querySelector('.ready-badge');
                    if (!badge) return;
                    badge.textContent = m.ready ? 'Ready' : 'Not ready';
                    badge.classList.toggle('badge-success', m.ready);
                    badge.classList.toggle('badge-secondary', !m.ready);
                });
            })
            .catch(() => {});
    }

    setInterval(pollState, 2000);
})();
</script>

<?php $this->load->view('footer'); ?>

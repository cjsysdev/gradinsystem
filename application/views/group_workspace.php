<?php
// Assessment Type (Major Exam/Quiz) and Widget are independent settings — a
// widget, if one's assigned, always takes priority. Only fall back to the
// legacy json_file_path quiz flow when there's no widget to render instead.
if (empty($widget) && ($assessment['iotype_id'] == '4' || $assessment['iotype_id'] == '3')) {
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
                    <div id="shared-widget-wrap">
                        <?php if (!empty($widget)): ?>
                            <?php $this->load->view($widget['input_view'], [
                                'config'   => $widget_config,
                                'readonly' => false,
                                'existing' => json_decode($state['content'] ?? '', true) ?: null,
                            ]); ?>
                        <?php else: ?>
                            <textarea id="shared-draft" class="form-control" rows="14"
                                placeholder="Write your group's answer here..."><?= htmlspecialchars($state['content']) ?></textarea>
                        <?php endif; ?>
                    </div>
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
                          onsubmit="return window.prepareGroupSubmit();">
                        <input type="hidden" name="content" id="group-submit-content">
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
    const hasWidget = <?= !empty($widget) ? 'true' : 'false' ?>;

    const widgetWrap = document.getElementById('shared-widget-wrap');
    const draft = document.getElementById('shared-draft'); // only present when there's no widget
    const saveStatus = document.getElementById('save-status');
    const readyToggle = document.getElementById('ready-toggle');

    // Generic content contract: a widget view defines window.getWidgetState()/
    // setWidgetState()/isWidgetFocused(); the plain shared textarea (no widget
    // configured) implements the same contract inline as the default case.
    function getCurrentContent() {
        if (hasWidget && typeof window.getWidgetState === 'function') return window.getWidgetState();
        return draft ? draft.value : '';
    }
    function applyRemoteContent(content) {
        if (hasWidget && typeof window.setWidgetState === 'function') {
            window.setWidgetState(content);
        } else if (draft && document.activeElement !== draft) {
            draft.value = content;
        }
    }
    function isEditing() {
        if (hasWidget && typeof window.isWidgetFocused === 'function') return window.isWidgetFocused();
        return draft && document.activeElement === draft;
    }

    let saveTimer = null;
    let lastSavedContent = getCurrentContent();
    // Monotonic revision of the shared content we currently hold. Echoed to the
    // server as ?since= so it skips resending the (possibly large) blob when
    // unchanged. Replaces the old DATETIME stamp, whose 1-second resolution let
    // same-second saves slip through as "unchanged" and drop teammate answers.
    let lastVersion = <?= (int) ($state['revision'] ?? 0) ?>;

    // Called by the submit form's onsubmit, before navigation — captures
    // whatever is actually on screen instead of trusting the debounced
    // autosave to have already landed (confirm() blocks the JS event loop,
    // so a pending save can't fire while the dialog is open, and submitting
    // aborts any in-flight fetch).
    window.prepareGroupSubmit = function () {
        if (!confirm('Submit this draft for the whole group?')) return false;
        clearTimeout(saveTimer);
        document.getElementById('group-submit-content').value = getCurrentContent();
        return true;
    };

    function saveDraft() {
        const content = getCurrentContent();
        if (content === lastSavedContent) return;
        lastSavedContent = content;
        saveStatus.textContent = 'Saving...';
        fetch(BASE + 'GroupWorkController/save_draft/' + assessmentId, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            // merge=1: the server field-merges this save into the shared draft
            // so it fills in our answers without wiping a teammate's.
            body: 'merge=1&content=' + encodeURIComponent(content),
        })
        .then(r => r.json())
        .then(d => {
            saveStatus.textContent = d.ok ? 'Saved' : 'Failed to save';
            // Advance our marker to the revision we just wrote so the next poll
            // doesn't get our own content echoed back to us.
            if (d.ok && typeof d.revision !== 'undefined') lastVersion = d.revision;
        });
    }

    function queueSave() {
        clearTimeout(saveTimer);
        saveStatus.textContent = 'Typing...';
        saveTimer = setTimeout(saveDraft, 800);
    }

    // Event delegation so this works whether the wrap contains the plain
    // textarea or an arbitrary widget's own inputs/buttons.
    widgetWrap.addEventListener('input', queueSave);
    widgetWrap.addEventListener('click', (e) => {
        if (e.target.closest('button')) queueSave();
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
        fetch(BASE + 'GroupWorkController/state/' + assessmentId + '?since=' + encodeURIComponent(lastVersion))
            .then(r => r.json())
            .then(d => {
                if (!d.ok) return;

                // A teammate submitted for the whole group elsewhere — bounce to
                // the workspace route, which redirects on to the classwork list
                // (with the "already submitted" notice) via the server guard.
                if (d.submitted) {
                    stopPolling();
                    window.location = BASE + 'GroupWorkController/workspace/' + assessmentId;
                    return;
                }

                if (d.content_changed && typeof d.content === 'string') {
                    // A newer shared version exists. Don't clobber the widget/draft
                    // while the student is actively editing — and in that case leave
                    // lastVersion stale so the server keeps offering this content and
                    // we adopt it once they stop typing, rather than losing it.
                    if (!isEditing() && d.content !== lastSavedContent) {
                        applyRemoteContent(d.content);
                        lastSavedContent = d.content;
                        lastVersion = d.revision;
                    } else if (!isEditing()) {
                        lastVersion = d.revision; // already matches what we hold
                    }
                } else if (!d.content_changed && typeof d.revision !== 'undefined') {
                    lastVersion = d.revision;
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

    // Poll only while the tab is visible — a backgrounded workspace was still
    // hitting the server every 2s for nothing. Resume (with an immediate catch-up
    // poll) when the student comes back to the tab.
    let pollTimer = null;
    function startPolling() {
        if (pollTimer) return;
        pollTimer = setInterval(pollState, 2000);
    }
    function stopPolling() {
        clearInterval(pollTimer);
        pollTimer = null;
    }
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopPolling();
        } else {
            pollState();
            startPolling();
        }
    });
    if (!document.hidden) startPolling();
})();
</script>

<?php $this->load->view('footer'); ?>

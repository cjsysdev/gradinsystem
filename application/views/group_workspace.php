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
    <?php if (!empty($already_submitted)): ?>
        <div class="alert alert-info">Your group already submitted this — you can keep editing and re-submit until it's graded.</div>
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

<style>
    /* A field a teammate currently has focused — see renderPresence(). */
    .live-presence-editing {
        outline: 2px solid #ffc107 !important;
        box-shadow: 0 0 0 3px rgba(255, 193, 7, .25);
    }
    .presence-note { white-space: nowrap; }
</style>
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
        // The only caller gates on !isEditing(), so applying is always safe
        // here — including into a focused-but-idle textarea.
        if (hasWidget && typeof window.setWidgetState === 'function') {
            window.setWidgetState(content);
        } else if (draft) {
            draft.value = content;
        }
    }
    function isEditing() {
        const focused = (hasWidget && typeof window.isWidgetFocused === 'function')
            ? window.isWidgetFocused()
            : (draft && document.activeElement === draft);
        // Merely parking the cursor in a field shouldn't block sync forever —
        // a student counts as editing only while input is actually recent,
        // so they adopt teammates' newer content once they pause.
        return focused && (Date.now() - lastInputAt) < 4000;
    }

    // ── Flatten / diff (mirror of Live_state_model's PHP set_by_path) ────────
    // We turn the widget's JSON state into a flat map of dotted leaf-path =>
    // scalar value, so a save can send ONLY the leaves that changed (a patch).
    // The server merges the patch per-field, so a teammate's untouched parts
    // survive — that's the whole fix. The heavy field-level merge lives on the
    // server (merge_patch); the client just sends patches and, when idle,
    // adopts the server's already-merged blob wholesale.
    function flatten(obj, prefix, out) {
        out = out || {};
        if (obj !== null && typeof obj === 'object') {
            const keys = Array.isArray(obj) ? obj.map((_, i) => i) : Object.keys(obj);
            keys.forEach(k => {
                const path = prefix === '' ? '' + k : prefix + '.' + k;
                flatten(obj[k], path, out);
            });
        } else {
            out[prefix] = obj; // scalar leaf (string / number / bool / null)
        }
        return out;
    }
    function flatOf(str) {
        try { return flatten(JSON.parse(str || '{}'), '', {}); }
        catch (e) { return {}; }
    }
    function diffFlat(base, cur) {
        const patch = {};
        for (const k in cur) {
            if (!(k in base) || base[k] !== cur[k]) patch[k] = cur[k];
        }
        return patch;
    }

    let saveTimer = null;
    let saveInFlight = false;
    let saveRetryDelay = 2000;
    let lastInputAt = 0;
    let lastSavedContent = getCurrentContent();
    // The flat baseline the server currently holds for our leaves — the diff
    // reference. Kept in sync on every successful save and every adopted poll.
    let baseFlat = hasWidget ? flatOf(lastSavedContent) : {};
    // Integer edit counter of the shared row we currently hold (widget mode).
    // Echoed as ?since_rev= so the server ships the big blob only when it moved.
    let knownRev = <?= (int) ($state['rev'] ?? 0) ?>;
    // Legacy no-widget textarea path still versions on updated_at (?since=),
    // since its whole-blob saves don't bump rev.
    let lastVersion = <?= json_encode((string) $state['updated_at']) ?>;

    // Called by the submit form's onsubmit, before navigation. For widgets we
    // flush a final patch SYNCHRONOUSLY so the server's shared blob has our
    // last edits before submit_group re-reads it as the authoritative,
    // fully-merged group answer (it no longer trusts this screen alone —
    // a teammate's un-polled part must not be dropped). confirm() blocks the
    // event loop, so the debounced autosave can't have fired on its own.
    window.prepareGroupSubmit = function () {
        if (!confirm('Submit this draft for the whole group? Everyone\'s merged answers are submitted — not just what is on your screen.')) return false;
        clearTimeout(saveTimer);
        if (hasWidget) {
            const cur = getCurrentContent();
            const curFlat = flatOf(cur);
            const patch = diffFlat(baseFlat, curFlat);
            if (Object.keys(patch).length > 0) {
                try {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', BASE + 'GroupWorkController/save_draft/' + assessmentId, false); // sync
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.send('patch=' + encodeURIComponent(JSON.stringify(patch)));
                    baseFlat = curFlat;
                } catch (e) { /* fall through — server still has the merged blob */ }
            }
            // The hidden field is now advisory only; the server ignores it for
            // widgets and re-reads the merged blob. Send it anyway for the
            // degraded/no-JS-merge case.
            document.getElementById('group-submit-content').value = cur;
        } else {
            document.getElementById('group-submit-content').value = getCurrentContent();
        }
        return true;
    };

    function saveDraft() {
        if (saveInFlight) {
            // Let the in-flight request settle; its success handler re-queues
            // if the content moved on in the meantime.
            return;
        }
        const content = getCurrentContent();
        if (content === lastSavedContent) { saveStatus.textContent = 'Saved'; return; }

        let body;
        let curFlat = null;
        if (hasWidget) {
            curFlat = flatOf(content);
            const patch = diffFlat(baseFlat, curFlat);
            if (Object.keys(patch).length === 0) {
                // Nothing meaningful changed (e.g. formatting-only) — advance
                // the baseline and stop, don't spam an empty patch.
                lastSavedContent = content;
                baseFlat = curFlat;
                saveStatus.textContent = 'Saved';
                return;
            }
            body = 'patch=' + encodeURIComponent(JSON.stringify(patch));
        } else {
            body = 'content=' + encodeURIComponent(content);
        }

        saveInFlight = true;
        saveStatus.textContent = 'Saving...';
        fetch(BASE + 'GroupWorkController/save_draft/' + assessmentId, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body,
        })
        .then(r => r.json())
        .then(d => {
            saveInFlight = false;
            if (!d || !d.ok) throw new Error('save failed');
            saveRetryDelay = 2000;
            // Only mark saved once the server confirms it — a failed save must
            // stay eligible for retry, not be silently considered saved.
            lastSavedContent = content;
            saveStatus.textContent = 'Saved';
            if (hasWidget) {
                baseFlat = curFlat;                 // server now holds these leaves
                if (typeof d.rev === 'number') knownRev = d.rev;
            } else if (d.skipped) {
                // Server refused to replace real group content with an empty
                // shell — blank our marker so the next poll restores the draft.
                lastVersion = '';
            } else if (d.updated_at) {
                lastVersion = d.updated_at;
            }
            if (getCurrentContent() !== lastSavedContent) queueSave();
        })
        .catch(() => {
            saveInFlight = false;
            saveStatus.textContent = 'Not saved — retrying...';
            clearTimeout(saveTimer);
            saveTimer = setTimeout(saveDraft, saveRetryDelay);
            saveRetryDelay = Math.min(saveRetryDelay * 2, 8000);
        });
    }

    function queueSave() {
        clearTimeout(saveTimer);
        saveStatus.textContent = 'Typing...';
        saveTimer = setTimeout(saveDraft, 800);
    }

    // Event delegation so this works whether the wrap contains the plain
    // textarea or an arbitrary widget's own inputs/buttons.
    widgetWrap.addEventListener('input', () => { lastInputAt = Date.now(); queueSave(); });
    widgetWrap.addEventListener('click', (e) => {
        if (e.target.closest('button')) { lastInputAt = Date.now(); queueSave(); }
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

    // ── Presence: tell teammates which field I'm editing ─────────────────────
    // Returns the flattened path of the focused field when the widget maps it,
    // '*' when a field is focused but unmapped, or null when nothing here is.
    function activeFieldPath() {
        if (!widgetWrap.contains(document.activeElement)) return null;
        if (hasWidget && typeof window.getFocusedFieldPath === 'function') {
            try { return window.getFocusedFieldPath() || '*'; } catch (e) { return '*'; }
        }
        return '*';
    }
    let lastPresenceSent = false;
    function sendPresence(path) {
        fetch(BASE + 'GroupWorkController/presence/' + assessmentId, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'field_path=' + encodeURIComponent(path == null ? '' : path),
        }).catch(() => {});
    }
    widgetWrap.addEventListener('focusin', () => { lastPresenceSent = true; sendPresence(activeFieldPath()); });
    widgetWrap.addEventListener('focusout', () => {
        // Let focus settle — moving between two fields fires focusout then
        // focusin; only report "left" once focus actually leaves the widget.
        setTimeout(() => {
            if (!widgetWrap.contains(document.activeElement)) { lastPresenceSent = false; sendPresence(''); }
        }, 60);
    });
    // Heartbeat so presence stays fresh (server expires it after ~6s).
    setInterval(() => {
        const p = activeFieldPath();
        if (p !== null) { lastPresenceSent = true; sendPresence(p); }
        else if (lastPresenceSent) { lastPresenceSent = false; sendPresence(''); }
    }, 3000);

    function renderPresence(list) {
        document.querySelectorAll('.live-presence-editing').forEach(el => {
            el.classList.remove('live-presence-editing');
            el.removeAttribute('title');
        });
        document.querySelectorAll('.presence-note').forEach(n => n.remove());
        (list || []).forEach(p => {
            if (p.student_id === myStudentId) return;
            let located = false;
            if (p.field_path && p.field_path !== '*' && hasWidget && typeof window.getFieldElement === 'function') {
                let el = null;
                try { el = window.getFieldElement(p.field_path); } catch (e) {}
                if (el) { el.classList.add('live-presence-editing'); el.title = p.name + ' is editing this'; located = true; }
            }
            const li = document.querySelector('#member-list li[data-student="' + p.student_id + '"]');
            if (li) {
                const note = document.createElement('span');
                note.className = 'presence-note text-warning small ml-1';
                note.textContent = located ? '✏️' : '✏️ editing';
                li.appendChild(note);
            }
        });
    }

    function pollState() {
        const query = hasWidget
            ? '?since_rev=' + encodeURIComponent(knownRev)
            : '?since=' + encodeURIComponent(lastVersion);
        fetch(BASE + 'GroupWorkController/state/' + assessmentId + query)
            .then(r => r.json())
            .then(d => {
                if (!d.ok) return;

                // The group's submission has been graded elsewhere — it's now
                // final, so bounce to the workspace route, which redirects on
                // to the classwork list (with the "graded" notice) via the
                // server guard. While merely submitted-but-ungraded, stay put:
                // teammates keep collaborating and the content sync below
                // picks up any teammate's re-submission.
                if (d.graded) {
                    stopPolling();
                    window.location = BASE + 'GroupWorkController/workspace/' + assessmentId;
                    return;
                }

                if (d.content_changed && typeof d.content === 'string') {
                    // A newer server-merged version exists. Don't clobber the
                    // widget/draft while the student is actively editing — leave
                    // our marker stale so the server keeps offering it and we
                    // adopt it once they pause (the merge already happened
                    // server-side, so adopting wholesale is safe & correct).
                    if (!isEditing() && d.content !== lastSavedContent) {
                        applyRemoteContent(d.content);
                        lastSavedContent = getCurrentContent();
                        if (hasWidget) {
                            baseFlat = flatOf(lastSavedContent);
                            if (typeof d.rev === 'number') knownRev = d.rev;
                        } else if (d.updated_at) {
                            lastVersion = d.updated_at;
                        }
                    } else if (!isEditing()) {
                        if (hasWidget) { if (typeof d.rev === 'number') knownRev = d.rev; }
                        else if (d.updated_at) { lastVersion = d.updated_at; }
                    }
                } else if (!d.content_changed) {
                    if (hasWidget) { if (typeof d.rev === 'number') knownRev = d.rev; }
                    else if (d.updated_at) { lastVersion = d.updated_at; }
                }

                (d.members || []).forEach(m => {
                    const li = document.querySelector('#member-list li[data-student="' + m.student_id + '"]');
                    if (!li) return;
                    if (m.student_id === myStudentId) return;
                    const badge = li.querySelector('.ready-badge');
                    if (!badge) return;
                    badge.textContent = m.ready ? 'Ready' : 'Not ready';
                    badge.classList.toggle('badge-success', m.ready);
                    badge.classList.toggle('badge-secondary', !m.ready);
                });

                renderPresence(d.presence);
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

<?php
// File Upload — students attach one or more files (C source, documents,
// text, images, PDFs, ...) plus an optional note. Works for both individual
// and group assessments with no special-casing: in a grouping assessment it
// renders inside group_workspace.php like any other widget, every member can
// add files, and the list syncs through the shared live-state blob. Not
// auto-graded — same manual-score-entry pattern as Worksheet Form.
//
// Files are NOT posted with the Turn In form. Each one is uploaded over AJAX
// to WidgetFileController::upload() the moment it's picked, and only its
// metadata goes into this widget's JSON state — so the hidden `code` field
// and submit_classwork()/submit_group() work unchanged.
//
// $config — [
//   'instructions'       => '...',                       // optional, shown above the drop zone
//   'allowed_extensions' => ['c', 'h', 'txt', 'pdf'],     // optional, [] / omitted = any safe type
//   'max_files'          => int,                          // optional, default 5
//   'max_size_mb'        => number,                       // optional, per file, default 10, capped at 50
//   'note_label'         => '...',                        // optional, default below; "" hides the note box
//   'require_note'       => bool,                         // optional, default false
// ]
// $readonly — bool
// $existing — [
//   'files' => [ '<id>' => ['id', 'name', 'size', 'ext', 'path', 'uploaded_at', 'uploaded_by', 'removed' => 0|1], ... ],
//   'note'  => '...',
// ] or null
//
// `files` is an id-keyed object, not a list, and a removed file is kept as a
// `removed: 1` tombstone rather than deleted: group_workspace.php syncs by
// merging changed leaf paths, so a list index would collide when two members
// upload at once, and a deleted key would never reach teammates.

$readonly      = $readonly ?? false;
$existing      = is_array($existing ?? null) ? $existing : [];
$assessment_id = $assessment_id
    ?? ($classwork['assessment_id'] ?? ($assessment['assessment_id'] ?? null));

$allowed = [];
foreach ((array) ($config['allowed_extensions'] ?? []) as $e) {
    $e = strtolower(ltrim(trim((string) $e), '.'));
    if ($e !== '') $allowed[] = $e;
}
$allowed      = array_values(array_unique($allowed));
$max_files    = max(1, (int) ($config['max_files'] ?? 5));
$max_size_mb  = (float) ($config['max_size_mb'] ?? 10);
$max_size_mb  = $max_size_mb > 0 ? min($max_size_mb, 50) : 10;
$note_label   = array_key_exists('note_label', $config) ? (string) $config['note_label'] : 'Notes for your instructor (optional)';
$require_note = !empty($config['require_note']);

$files = [];
foreach ((array) ($existing['files'] ?? []) as $f) {
    if (is_array($f) && empty($f['removed']) && preg_match('#^\d+/[sg][A-Za-z0-9_-]+/[a-f0-9]{16}$#', (string) ($f['path'] ?? ''))) $files[] = $f;
}
$note = (string) ($existing['note'] ?? '');

$fu_size = function ($bytes) {
    $bytes = (int) $bytes;
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
    if ($bytes >= 1024) return round($bytes / 1024) . ' KB';
    return $bytes . ' B';
};
$fu_previewable = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'pdf',
    'c', 'h', 'cpp', 'hpp', 'cc', 'cs', 'java', 'py', 'js', 'ts', 'html', 'htm', 'css',
    'sql', 'txt', 'md', 'csv', 'json', 'xml', 'yml', 'yaml', 'ini', 'log', 'sh', 'svg'];
?>
<style>
    .fu-widget { text-align: left; }
    .fu-widget .fu-instructions { background: #f6f5f1; border: 1px solid #e3e1da; border-left: 5px solid #357abd; border-radius: 6px; padding: 14px 16px; margin-bottom: 16px; font-size: 14px; }
    .fu-widget .fu-drop { border: 2px dashed #b8c4d0; border-radius: 8px; padding: 22px 16px; text-align: center; background: #fbfcfd; cursor: pointer; transition: background .15s, border-color .15s; }
    .fu-widget .fu-drop:hover, .fu-widget .fu-drop.fu-over { background: #eef5fc; border-color: #357abd; }
    .fu-widget .fu-drop.fu-disabled { cursor: not-allowed; opacity: .6; }
    .fu-widget .fu-drop i { font-size: 28px; color: #357abd; }
    .fu-widget .fu-rules { font-size: 12px; color: #6c757d; margin-top: 6px; }
    .fu-widget .fu-list { margin-top: 14px; }
    .fu-widget .fu-item { border: 1px solid #e3e1da; border-radius: 6px; padding: 8px 12px; margin-bottom: 8px; background: #fff; }
    .fu-widget .fu-item-head { display: flex; align-items: center; gap: 10px; }
    .fu-widget .fu-item-name { font-weight: 600; word-break: break-all; }
    .fu-widget .fu-item-meta { font-size: 12px; color: #6c757d; }
    .fu-widget .fu-item-actions { margin-left: auto; white-space: nowrap; }
    .fu-widget .fu-ext { display: inline-block; min-width: 42px; text-align: center; font-size: 11px; font-weight: bold; text-transform: uppercase; padding: 3px 6px; border-radius: 4px; background: #357abd; color: #fff; }
    .fu-widget .fu-preview { margin-top: 8px; }
    .fu-widget .fu-preview pre { max-height: 420px; overflow: auto; background: #1e1e1e; color: #d4d4d4; padding: 10px 12px; border-radius: 4px; font-size: 13px; margin: 0; white-space: pre; }
    .fu-widget .fu-preview img { max-width: 100%; border: 1px solid #e3e1da; border-radius: 4px; }
    .fu-widget .fu-progress { height: 6px; margin-top: 6px; }
    .fu-widget .fu-answer { white-space: pre-wrap; background: #f6f5f1; border: 1px solid #e3e1da; border-radius: 4px; padding: 8px 10px; min-height: 20px; }
</style>
<div class="fu-widget"<?= $readonly ? '' : ' id="fu-widget"' ?>>
    <?php if (!empty($config['instructions'])): ?>
        <div class="fu-instructions"><?= nl2br(htmlspecialchars($config['instructions'])) ?></div>
    <?php endif; ?>

    <?php if (!$readonly): ?>
        <?php if (!$assessment_id): ?>
            <div class="alert alert-secondary small mb-2">Preview only &mdash; uploading is enabled once students open the assessment.</div>
        <?php endif; ?>
        <div class="fu-drop<?= $assessment_id ? '' : ' fu-disabled' ?>" id="fu-drop" tabindex="0" role="button" aria-label="Choose files to upload">
            <i class="fas fa-cloud-upload-alt"></i>
            <div class="mt-2"><strong>Click to choose files</strong> or drag them here</div>
            <div class="fu-rules">
                <?= $allowed ? 'Accepted: .' . htmlspecialchars(implode(', .', $allowed)) : 'Any file type' ?>
                &middot; up to <?= $max_files ?> file<?= $max_files > 1 ? 's' : '' ?>
                &middot; <?= htmlspecialchars((string) $max_size_mb) ?> MB each
            </div>
            <input type="file" id="fu-input" multiple class="d-none"
                <?= $allowed ? 'accept="' . htmlspecialchars('.' . implode(',.', $allowed)) . '"' : '' ?>>
        </div>
        <div id="fu-errors" class="mt-2"></div>
    <?php endif; ?>

    <div class="fu-list" id="fu-list">
        <?php if ($readonly): ?>
            <?php if (empty($files)): ?>
                <p class="text-muted mb-0">No files submitted.</p>
            <?php endif; ?>
            <?php foreach ($files as $i => $f):
                $ext  = strtolower((string) ($f['ext'] ?? pathinfo($f['name'] ?? '', PATHINFO_EXTENSION)));
                $path = (string) $f['path'];
                $url  = base_url('WidgetFileController/download/' . $path);
            ?>
                <div class="fu-item">
                    <div class="fu-item-head">
                        <span class="fu-ext"><?= htmlspecialchars($ext !== '' ? $ext : 'file') ?></span>
                        <div>
                            <div class="fu-item-name"><?= htmlspecialchars($f['name'] ?? 'file') ?></div>
                            <div class="fu-item-meta">
                                <?= $fu_size($f['size'] ?? 0) ?>
                                <?php if (!empty($f['uploaded_by'])): ?>&middot; by <?= htmlspecialchars($f['uploaded_by']) ?><?php endif; ?>
                                <?php if (!empty($f['uploaded_at'])): ?>&middot; <?= htmlspecialchars($f['uploaded_at']) ?><?php endif; ?>
                            </div>
                        </div>
                        <div class="fu-item-actions">
                            <?php if (in_array($ext, $fu_previewable, true)): ?>
                                <button type="button" class="btn btn-sm btn-outline-secondary fu-preview-btn"
                                    onclick="(window.fuTogglePreview || function (b) { window.open(b.dataset.url, '_blank'); })(this)"
                                    data-url="<?= htmlspecialchars($url . '?inline=1') ?>" data-ext="<?= htmlspecialchars($ext) ?>">
                                    <i class="fas fa-eye"></i> Preview
                                </button>
                            <?php endif; ?>
                            <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($url) ?>"><i class="fas fa-download"></i> Download</a>
                        </div>
                    </div>
                    <div class="fu-preview" style="display:none;"></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($note_label !== ''): ?>
        <div class="form-group mt-3 mb-0">
            <label class="font-weight-bold small mb-1"><?= htmlspecialchars($note_label) ?><?= $require_note ? ' <span class="text-danger">*</span>' : '' ?></label>
            <?php if ($readonly): ?>
                <div class="fu-answer"><?= $note !== '' ? nl2br(htmlspecialchars($note)) : '<span class="text-muted">No note.</span>' ?></div>
            <?php else: ?>
                <textarea class="form-control fu-note" rows="3" placeholder="e.g. How to compile/run it, what's finished, what's not"><?= htmlspecialchars($note) ?></textarea>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<script>
(function () {
    // Preview toggle — fetches through download(), so the same access check
    // applies as a direct download; nothing here reads the file off disk in
    // the view. Buttons call it from an inline onclick because admin pages
    // clone readonly copies of this widget out of a <template> via innerHTML,
    // where this <script> never runs — there, the onclick falls back to
    // opening the inline preview in a new tab.
    if (window.fuTogglePreview) return;
    const IMAGE_EXT = ['png', 'jpg', 'jpeg', 'gif', 'webp'];

    window.fuTogglePreview = function (btn) {
        const box = btn.closest('.fu-item').querySelector('.fu-preview');
        if (box.style.display !== 'none') { box.style.display = 'none'; return; }
        box.style.display = '';
        if (box.dataset.loaded) return;
        box.dataset.loaded = '1';
        const url = btn.dataset.url, ext = btn.dataset.ext;
        if (IMAGE_EXT.indexOf(ext) !== -1) {
            const img = document.createElement('img');
            img.src = url;
            box.appendChild(img);
        } else if (ext === 'pdf') {
            const frame = document.createElement('iframe');
            frame.src = url;
            frame.style.cssText = 'width:100%;height:600px;border:1px solid #e3e1da;border-radius:4px;';
            box.appendChild(frame);
        } else {
            const pre = document.createElement('pre');
            pre.textContent = 'Loading...';
            box.appendChild(pre);
            fetch(url, { credentials: 'same-origin' })
                .then(r => { if (!r.ok) throw new Error(r.status); return r.text(); })
                .then(t => { pre.textContent = t.length > 200000 ? t.slice(0, 200000) + '\n\n... (truncated — download to see the rest)' : t; })
                .catch(() => { pre.textContent = 'Could not load a preview — use Download instead.'; });
        }
    };
})();
</script>

<?php if (!$readonly): ?>
<script>
(function () {
    const BASE = <?= json_encode(base_url()) ?>;
    const ASSESSMENT_ID = <?= json_encode($assessment_id ? (int) $assessment_id : null) ?>;
    const ALLOWED = <?= json_encode($allowed) ?>;
    const MAX_FILES = <?= (int) $max_files ?>;
    const MAX_BYTES = <?= json_encode((int) round($max_size_mb * 1024 * 1024)) ?>;
    const MAX_MB = <?= json_encode($max_size_mb) ?>;
    const REQUIRE_NOTE = <?= $require_note ? 'true' : 'false' ?>;
    const PREVIEWABLE = <?= json_encode($fu_previewable) ?>;
    // Same shape check as the readonly branch above and download()'s own.
    const PATH_RE = /^\d+\/[sg][A-Za-z0-9_-]+\/[a-f0-9]{16}$/;

    const widget = document.getElementById('fu-widget');
    const drop = document.getElementById('fu-drop');
    const input = document.getElementById('fu-input');
    const list = document.getElementById('fu-list');
    const errors = document.getElementById('fu-errors');
    const noteEl = widget.querySelector('.fu-note');

    // id => metadata, tombstones included (see the header comment).
    let files = <?= json_encode((object) array_filter((array) ($existing['files'] ?? []), 'is_array')) ?>;
    let uploading = 0;

    function liveFiles() {
        return Object.keys(files).map(k => files[k]).filter(f => f && !f.removed && PATH_RE.test(f.path || ''));
    }

    function fmtSize(b) {
        b = +b || 0;
        if (b >= 1048576) return (b / 1048576).toFixed(1) + ' MB';
        if (b >= 1024) return Math.round(b / 1024) + ' KB';
        return b + ' B';
    }

    function extOf(name) {
        const m = /\.([^.]+)$/.exec(name || '');
        return m ? m[1].toLowerCase() : '';
    }

    // Tells the host page something changed: group_workspace.php queues a
    // shared-draft save on any 'input' event inside the widget.
    function notifyChanged() {
        widget.dispatchEvent(new Event('input', { bubbles: true }));
    }

    function showError(msg) {
        const div = document.createElement('div');
        div.className = 'alert alert-danger alert-dismissible py-2 mb-2 small';
        div.textContent = msg;
        const x = document.createElement('button');
        x.type = 'button';
        x.className = 'close py-2';
        x.innerHTML = '&times;';
        x.addEventListener('click', () => div.remove());
        div.appendChild(x);
        errors.appendChild(div);
        setTimeout(() => div.remove(), 8000);
    }

    function render() {
        list.querySelectorAll('.fu-item:not(.fu-pending)').forEach(el => el.remove());
        const live = liveFiles().sort((a, b) => String(a.uploaded_at).localeCompare(String(b.uploaded_at)));
        live.forEach(f => {
            const url = BASE + 'WidgetFileController/download/' + f.path;
            const ext = (f.ext || extOf(f.name) || 'file').toLowerCase();
            const item = document.createElement('div');
            item.className = 'fu-item';
            item.innerHTML =
                '<div class="fu-item-head">' +
                    '<span class="fu-ext"></span>' +
                    '<div><div class="fu-item-name"></div><div class="fu-item-meta"></div></div>' +
                    '<div class="fu-item-actions">' +
                        (PREVIEWABLE.indexOf(ext) !== -1
                            ? '<button type="button" class="btn btn-sm btn-outline-secondary fu-preview-btn mr-1"><i class="fas fa-eye"></i></button>'
                            : '') +
                        '<a class="btn btn-sm btn-outline-primary mr-1" title="Download"><i class="fas fa-download"></i></a>' +
                        '<button type="button" class="btn btn-sm btn-outline-danger fu-remove" title="Remove">&times;</button>' +
                    '</div>' +
                '</div>' +
                '<div class="fu-preview" style="display:none;"></div>';
            item.querySelector('.fu-ext').textContent = ext;
            item.querySelector('.fu-item-name').textContent = f.name || 'file';
            item.querySelector('.fu-item-meta').textContent = fmtSize(f.size) +
                (f.uploaded_by ? ' · by ' + f.uploaded_by : '') +
                (f.uploaded_at ? ' · ' + f.uploaded_at : '');
            item.querySelector('a').href = url;
            const pv = item.querySelector('.fu-preview-btn');
            if (pv) { pv.dataset.url = url + '?inline=1'; pv.dataset.ext = ext; pv.addEventListener('click', () => window.fuTogglePreview(pv)); }
            item.querySelector('.fu-remove').addEventListener('click', () => {
                if (!confirm('Remove "' + (f.name || 'this file') + '" from the submission?')) return;
                files[f.id] = Object.assign({}, files[f.id], { removed: 1 });
                render();
                notifyChanged();
            });
            list.insertBefore(item, list.querySelector('.fu-pending'));
        });
        if (drop && ASSESSMENT_ID) {
            drop.classList.toggle('fu-disabled', live.length + uploading >= MAX_FILES);
        }
    }

    function uploadOne(file) {
        const ext = extOf(file.name);
        if (ALLOWED.length && ALLOWED.indexOf(ext) === -1) {
            showError('"' + file.name + '" was skipped — only .' + ALLOWED.join(', .') + ' files are accepted.');
            return;
        }
        if (file.size > MAX_BYTES) {
            showError('"' + file.name + '" is too large — the limit is ' + MAX_MB + ' MB.');
            return;
        }
        if (liveFiles().length + uploading >= MAX_FILES) {
            showError('"' + file.name + '" was skipped — at most ' + MAX_FILES + ' file(s) can be attached.');
            return;
        }

        uploading++;
        const pending = document.createElement('div');
        pending.className = 'fu-item fu-pending';
        pending.innerHTML = '<div class="fu-item-name"></div><div class="progress fu-progress"><div class="progress-bar" style="width:0%"></div></div>';
        pending.querySelector('.fu-item-name').textContent = 'Uploading ' + file.name + '...';
        list.appendChild(pending);
        const bar = pending.querySelector('.progress-bar');

        const fd = new FormData();
        fd.append('file', file);
        const xhr = new XMLHttpRequest();
        xhr.open('POST', BASE + 'WidgetFileController/upload/' + ASSESSMENT_ID);
        xhr.upload.addEventListener('progress', e => {
            if (e.lengthComputable) bar.style.width = Math.round(e.loaded / e.total * 100) + '%';
        });
        xhr.onloadend = function () {
            uploading--;
            pending.remove();
            let res = null;
            try { res = JSON.parse(xhr.responseText); } catch (e) { /* non-JSON = server/login error */ }
            if (xhr.status === 200 && res && res.ok && res.file) {
                files[res.file.id] = Object.assign({ removed: 0 }, res.file);
                render();
                notifyChanged();
            } else {
                showError('"' + file.name + '" failed to upload: ' + ((res && res.error) || 'server error (' + xhr.status + ')'));
                render();
            }
        };
        xhr.send(fd);
    }

    function handleFiles(fileList) {
        if (!ASSESSMENT_ID) return;
        Array.prototype.forEach.call(fileList, uploadOne);
        render();
    }

    if (drop) {
        drop.addEventListener('click', () => {
            if (!ASSESSMENT_ID || drop.classList.contains('fu-disabled')) return;
            input.click();
        });
        drop.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); drop.click(); }
        });
        drop.addEventListener('dragover', e => { e.preventDefault(); drop.classList.add('fu-over'); });
        drop.addEventListener('dragleave', () => drop.classList.remove('fu-over'));
        drop.addEventListener('drop', e => {
            e.preventDefault();
            drop.classList.remove('fu-over');
            if (e.dataTransfer && e.dataTransfer.files) handleFiles(e.dataTransfer.files);
        });
        // Swallow the file input's own input/change so the host page doesn't
        // save before the upload has actually produced metadata.
        input.addEventListener('input', e => e.stopPropagation());
        input.addEventListener('change', e => {
            e.stopPropagation();
            handleFiles(input.files);
            input.value = '';
        });
    }

    window.getWidgetState = function () {
        return JSON.stringify({ files: files, note: noteEl ? noteEl.value : '' });
    };

    window.setWidgetState = function (content) {
        let data;
        try { data = JSON.parse(content || '{}'); } catch (e) { return; }
        if (!data || typeof data !== 'object') return;
        files = (data.files && typeof data.files === 'object' && !Array.isArray(data.files)) ? data.files : {};
        if (noteEl && document.activeElement !== noteEl) noteEl.value = data.note || '';
        render();
    };

    window.isWidgetFocused = function () {
        return widget.contains(document.activeElement);
    };

    window.getFocusedFieldPath = function () {
        return document.activeElement === noteEl ? 'note' : null;
    };

    // Called by the host page right before it submits the form — serializes
    // this widget's state into the hidden #widget-code-value field so the
    // existing AssessmentController::submit_classwork() needs zero changes.
    window.serializeWidgetBeforeSubmit = function () {
        const codeField = document.getElementById('widget-code-value');
        if (codeField) codeField.value = window.getWidgetState();
    };

    // Solo Turn In goes through assessment_view_code.php's confirmation modal;
    // block it there with a clear message rather than submitting nothing.
    // Wrapped on DOMContentLoaded because the host page declares
    // confirmSubmission() in a <script> that comes after this view.
    document.addEventListener('DOMContentLoaded', function () {
        if (!document.getElementById('submission-form')) return;
        const origConfirm = window.confirmSubmission;
        window.confirmSubmission = function () {
            if (uploading > 0) { showError('Please wait for your upload(s) to finish.'); return; }
            if (!liveFiles().length) { showError('Attach at least one file before turning in.'); return; }
            if (REQUIRE_NOTE && noteEl && !noteEl.value.trim()) { showError('Please fill in the note before turning in.'); noteEl.focus(); return; }
            if (typeof origConfirm === 'function') origConfirm();
        };
    });

    render();
})();
</script>
<?php endif; ?>

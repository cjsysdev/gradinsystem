<?php $this->load->view('header') ?>

<style>
:root { --iq-primary: #04AA6D; --iq-dark: #038a57; }

/* ── Drop zone ─────────────────────────────────────── */
.iq-drop-zone {
    border: 2px dashed #ced4da;
    border-radius: 10px;
    padding: 32px 20px;
    text-align: center;
    cursor: pointer;
    transition: border-color .2s, background .2s;
    background: #fafafa;
    position: relative;
}
.iq-drop-zone.drag-over {
    border-color: var(--iq-primary);
    background: #e8f5e9;
}
.iq-drop-zone input[type=file] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
    width: 100%;
    height: 100%;
}
.iq-drop-icon { font-size: 40px; line-height: 1; }
.iq-drop-hint { color: #888; font-size: 13px; margin-top: 6px; }
.iq-filename  { font-weight: 600; color: var(--iq-dark); margin-top: 8px; font-size: 14px; }

/* ── JSON preview panel ────────────────────────────── */
#iq-preview {
    display: none;
    background: #f8f9fa;
    border-left: 4px solid var(--iq-primary);
    border-radius: 6px;
    padding: 14px 16px;
    font-size: 13px;
    margin-top: 14px;
}
#iq-preview .preview-row { display: flex; gap: 8px; margin-bottom: 4px; }
#iq-preview .preview-key  { color: #888; min-width: 90px; }
#iq-preview .preview-val  { font-weight: 600; }
#iq-preview.has-error     { border-color: #dc3545; background: #fff5f5; }
#iq-error-msg             { color: #dc3545; font-size: 13px; font-weight: 500; }

/* ── Overwrite badge ───────────────────────────────── */
.overwrite-warn {
    display: none;
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 6px;
    padding: 10px 14px;
    font-size: 13px;
    margin-top: 10px;
}

/* ── Topic table ───────────────────────────────────── */
.topic-table th { background: var(--iq-primary); color: #fff; border: none; }
.topic-table td { vertical-align: middle; }
.topic-table .badge-section {
    background: #e8f5e9; color: var(--iq-dark);
    border-radius: 12px; padding: 2px 10px; font-size: 12px; font-weight: 600;
}
.topic-table .badge-q {
    background: #e3f2fd; color: #1565c0;
    border-radius: 12px; padding: 2px 10px; font-size: 12px; font-weight: 600;
}
.size-text { color: #888; font-size: 12px; }

/* ── Flash alerts ──────────────────────────────────── */
.alert { border-radius: 8px; }
</style>

<div class="container mt-3 mb-5">
    <?php $this->load->view('admin/nav_bar') ?>

    <div class="d-flex align-items-center my-3">
        <span style="font-size:24px; margin-right:10px;">&#128218;</span>
        <h5 class="mb-0" style="color:var(--iq-primary);"><strong>Manage Topics</strong></h5>
    </div>

    <!-- ── Flash messages ── -->
    <?php if ($flash = $this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?= $flash ?></div>
    <?php endif; ?>
    <?php if ($flash = $this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= $flash ?></div>
    <?php endif; ?>

    <div class="row">

        <!-- ── LEFT: Upload card ── -->
        <div class="col-md-5 mb-4">
            <div class="card" style="border:none; box-shadow:0 2px 10px rgba(0,0,0,.08); border-radius:10px;">
                <div class="card-body p-4">
                    <h6 class="font-weight-bold mb-3" style="color:var(--iq-dark);">
                        Upload New Topic
                    </h6>

                    <form method="post"
                          action="<?= site_url('interactive_quiz/upload_topic') ?>"
                          enctype="multipart/form-data"
                          id="upload-form">

                        <div class="iq-drop-zone" id="drop-zone">
                            <input type="file" name="topic_json" id="topic-file"
                                   accept=".json,application/json">
                            <div class="iq-drop-icon">&#128196;</div>
                            <div>Drop a <strong>.json</strong> file here</div>
                            <div class="iq-drop-hint">or click to browse</div>
                            <div class="iq-filename" id="file-name-display"></div>
                        </div>

                        <!-- Live preview panel -->
                        <div id="iq-preview">
                            <div class="preview-row">
                                <span class="preview-key">Title</span>
                                <span class="preview-val" id="pv-title">—</span>
                            </div>
                            <div class="preview-row">
                                <span class="preview-key">Slug</span>
                                <span class="preview-val" id="pv-slug">—</span>
                            </div>
                            <div class="preview-row">
                                <span class="preview-key">Sections</span>
                                <span class="preview-val" id="pv-sections">—</span>
                            </div>
                            <div class="preview-row">
                                <span class="preview-key">Questions</span>
                                <span class="preview-val" id="pv-questions">—</span>
                            </div>
                            <div id="iq-error-msg"></div>
                        </div>

                        <!-- Overwrite warning -->
                        <div class="overwrite-warn" id="overwrite-warn">
                            &#9888; A topic with this slug already exists.
                            Uploading will <strong>overwrite</strong> it.
                        </div>

                        <button type="submit" class="btn btn-success btn-block mt-3"
                                id="upload-btn" disabled>
                            Upload Topic
                        </button>
                    </form>

                    <hr>
                    <p class="text-muted mb-0" style="font-size:12px;">
                        <strong>Required JSON structure:</strong><br>
                        <code>title</code>, <code>sections[]</code> (each with <code>title</code>,
                        <code>lesson</code>, and optional <code>questions[]</code>).
                        Each question needs <code>question</code>, <code>choices[]</code>,
                        and <code>answer</code> matching a choice.<br><br>
                        Max file size: <strong>5 MB</strong>.
                        The <code>topic</code> field (lowercase, underscores) becomes the filename.
                    </p>
                </div>
            </div>
        </div>

        <!-- ── RIGHT: Current topics ── -->
        <div class="col-md-7 mb-4">
            <div class="card" style="border:none; box-shadow:0 2px 10px rgba(0,0,0,.08); border-radius:10px; overflow:hidden;">
                <div class="card-header" style="background:var(--iq-primary); color:#fff; padding:12px 16px;">
                    <strong>Current Topics</strong>
                    <span class="badge badge-light ml-2"><?= count($topics) ?></span>
                </div>

                <?php if (empty($topics)): ?>
                <div class="card-body text-center text-muted py-5">
                    <p style="font-size:36px;">&#128220;</p>
                    <p>No topics yet. Upload a JSON file to get started.</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table topic-table mb-0">
                        <thead>
                            <tr>
                                <th>Topic</th>
                                <th style="width:80px; text-align:center;">Sections</th>
                                <th style="width:80px; text-align:center;">Questions</th>
                                <th style="width:58px; text-align:right;">Size</th>
                                <th style="width:130px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($topics as $t): ?>
                        <tr>
                            <td>
                                <div class="font-weight-bold" style="font-size:14px;">
                                    <?= htmlspecialchars($t['title']) ?>
                                </div>
                                <code style="font-size:11px; color:#888;"><?= $t['slug'] ?></code>
                                <?php if (!empty($t['desc'])): ?>
                                <div class="text-muted" style="font-size:12px; margin-top:2px;">
                                    <?= htmlspecialchars(mb_substr($t['desc'], 0, 80)) ?>…
                                </div>
                                <?php endif; ?>
                                <div class="size-text">
                                    Modified <?= date('M j, Y', $t['modified']) ?>
                                    <?php if (!empty($t['shuffle'])): ?>
                                    &bull; <span title="Questions &amp; choices are shuffled per attempt"
                                                style="color:#6c757d; font-size:11px;">&#x1F500; shuffle</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge-section"><?= $t['sections'] ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge-q"><?= $t['questions'] ?></span>
                            </td>
                            <td class="text-right size-text">
                                <?= number_format($t['size'] / 1024, 1) ?>&nbsp;KB
                            </td>
                            <td>
                                <div class="d-flex flex-column" style="gap:4px;">
                                    <a href="<?= site_url('interactive_quiz/load/' . $t['slug']) ?>"
                                       class="btn btn-sm btn-outline-success"
                                       target="_blank">Preview</a>
                                    <a href="<?= site_url('interactive_quiz/edit_topic/' . $t['slug']) ?>"
                                       class="btn btn-sm btn-outline-primary">Edit Questions</a>
                                    <a href="<?= site_url('interactive_quiz/analytics/' . $t['slug']) ?>"
                                       class="btn btn-sm btn-outline-secondary">Analytics</a>
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger delete-btn"
                                            data-slug="<?= $t['slug'] ?>"
                                            data-title="<?= htmlspecialchars($t['title'], ENT_QUOTES) ?>"
                                            data-url="<?= site_url('interactive_quiz/delete_topic/' . $t['slug']) ?>">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- .row -->
</div>

<!-- ── Delete confirmation modal ── -->
<div class="modal fade" id="delete-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:10px; border:none;">
            <div class="modal-header" style="border-bottom:1px solid #eee;">
                <h6 class="modal-title font-weight-bold">Delete Topic</h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete
                   <strong id="delete-topic-title"></strong>?</p>
                <p class="text-muted" style="font-size:13px; margin-bottom:0;">
                    This removes the <code id="delete-topic-slug"></code>.json file.
                    Existing analytics data is kept and can be reviewed, but the
                    topic will no longer be playable.
                </p>
            </div>
            <div class="modal-footer" style="border-top:1px solid #eee;">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form method="post" id="delete-form" style="margin:0;">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// ── Existing slugs for overwrite detection ──────────────
var EXISTING_SLUGS = <?= json_encode(array_column($topics, 'slug')) ?>;

// ── Drop zone drag-and-drop highlight ──────────────────
var dropZone = document.getElementById('drop-zone');
['dragenter','dragover'].forEach(function(evt) {
    dropZone.addEventListener(evt, function(e) {
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });
});
['dragleave','drop'].forEach(function(evt) {
    dropZone.addEventListener(evt, function() {
        dropZone.classList.remove('drag-over');
    });
});

// ── File selection → live JSON preview ─────────────────
document.getElementById('topic-file').addEventListener('change', function() {
    var file = this.files[0];
    if (!file) return;

    document.getElementById('file-name-display').textContent = file.name;

    var reader = new FileReader();
    reader.onload = function(e) { parseAndPreview(e.target.result, file.name); };
    reader.readAsText(file);
});

function slugify(str) {
    return str.toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
}

function countQuestions(sections) {
    return (sections || []).reduce(function(acc, s) {
        return acc + ((s.questions || []).length);
    }, 0);
}

function parseAndPreview(text, filename) {
    var preview  = document.getElementById('iq-preview');
    var errorEl  = document.getElementById('iq-error-msg');
    var uploadBtn = document.getElementById('upload-btn');
    var warnEl   = document.getElementById('overwrite-warn');

    errorEl.textContent = '';
    preview.classList.remove('has-error');
    warnEl.style.display = 'none';

    var data;
    try { data = JSON.parse(text); }
    catch(ex) {
        preview.style.display = 'block';
        preview.classList.add('has-error');
        errorEl.textContent = 'Invalid JSON: ' + ex.message;
        uploadBtn.disabled = true;
        return;
    }

    // Basic validation
    var err = '';
    if (!data.title)    err = 'Missing "title" field.';
    else if (!Array.isArray(data.sections) || !data.sections.length)
                        err = 'Missing or empty "sections" array.';
    if (err) {
        preview.style.display = 'block';
        preview.classList.add('has-error');
        errorEl.textContent = err;
        uploadBtn.disabled = true;
        return;
    }

    // Derive slug
    var slug = data.topic
        ? data.topic
        : slugify(filename.replace(/\.json$/i, ''));

    document.getElementById('pv-title').textContent    = data.title;
    document.getElementById('pv-slug').textContent     = slug || '(could not derive)';
    document.getElementById('pv-sections').textContent = data.sections.length;
    document.getElementById('pv-questions').textContent = countQuestions(data.sections);

    preview.style.display = 'block';

    var validSlug = /^[a-z0-9_]{1,100}$/.test(slug);
    if (!validSlug) {
        preview.classList.add('has-error');
        errorEl.textContent = 'Slug "' + slug + '" is invalid. Add a "topic" field to your JSON (lowercase, underscores).';
        uploadBtn.disabled = true;
        return;
    }

    // Check overwrite
    if (EXISTING_SLUGS.indexOf(slug) !== -1) {
        warnEl.style.display = 'block';
    }

    uploadBtn.disabled = false;
}

// ── Delete modal ────────────────────────────────────────
document.querySelectorAll('.delete-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('delete-topic-title').textContent = this.dataset.title;
        document.getElementById('delete-topic-slug').textContent  = this.dataset.slug;
        document.getElementById('delete-form').action             = this.dataset.url;
        $('#delete-modal').modal('show');
    });
});
</script>

<textarea id="code-editor" style="display:none;"></textarea>
<?php $this->load->view('footer') ?>

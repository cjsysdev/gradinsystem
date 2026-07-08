<?php $this->load->view('header'); ?>

<div class="container">
    <div class="dashboard">
        <?php $this->load->view('profile_only'); ?>
        <?php $this->load->view('admin/nav_bar'); ?>
    </div>

    <div class="row mt-3 align-items-center">
        <div class="col">
            <h4>Manage Discussions</h4>
        </div>
        <div class="col-auto">
            <a href="<?= site_url('InteractiveQuizController/analytics') ?>" class="btn btn-outline-secondary mr-2">
                <i class="fas fa-chart-bar"></i> Analytics
            </a>
            <button class="btn btn-primary" onclick="openAdd()">
                <i class="fas fa-plus"></i> Add Discussion
            </button>
        </div>
    </div>

    <?php foreach (['success','error','warning'] as $f): ?>
        <?php if ($msg = $this->session->flashdata($f)): ?>
            <div class="alert alert-<?= $f === 'success' ? 'success' : ($f === 'error' ? 'danger' : 'warning') ?> mt-2"><?= $msg ?></div>
        <?php endif; ?>
    <?php endforeach; ?>

    <?php
    // Build lookup maps
    $class_map = [];
    foreach ($classes as $c) {
        $class_map[$c['class_id']] = $c['class_code'] . ' — ' . $c['class_name'];
    }
    $topic_map = [];
    foreach ($json_topics as $t) {
        $topic_map[$t['slug']] = $t;
    }
    ?>

    <form method="get" action="<?= site_url('AdminController/manage_discussions') ?>" class="form-row align-items-end mt-3">
        <div class="form-group col-sm-3 mb-2">
            <label class="small mb-1">Class</label>
            <select name="class_id" class="form-control form-control-sm">
                <option value="">All classes</option>
                <?php foreach ($classes as $c): ?>
                <option value="<?= $c['class_id'] ?>" <?= (string)$selected_class_id === (string)$c['class_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['class_code'] . ' — ' . $c['class_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group col-sm-2 mb-2">
            <label class="small mb-1">Type</label>
            <select name="type" class="form-control form-control-sm">
                <option value="">All types</option>
                <option value="static" <?= $selected_type === 'static' ? 'selected' : '' ?>>Static</option>
                <option value="interactive" <?= $selected_type === 'interactive' ? 'selected' : '' ?>>Interactive</option>
            </select>
        </div>
        <div class="form-group col-sm-4 mb-2">
            <label class="small mb-1">Search</label>
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Title, description, or link/slug"
                   value="<?= htmlspecialchars($search_q) ?>">
        </div>
        <div class="form-group col-sm-auto mb-2">
            <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-filter"></i> Filter</button>
            <?php if ($selected_class_id !== '' || $selected_type !== '' || $search_q !== ''): ?>
                <a href="<?= site_url('AdminController/manage_discussions') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="table-responsive mt-3">
        <table class="table table-bordered table-hover table-sm">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Class</th>
                    <th>Type</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Link / Topic</th>
                    <th>Display Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($discussions)): ?>
                <tr><td colspan="8" class="text-center text-muted">
                    <?= ($selected_class_id !== '' || $selected_type !== '' || $search_q !== '')
                        ? 'No discussions match your filters.'
                        : 'No discussions yet.' ?>
                </td></tr>
            <?php else: ?>
                <?php foreach ($discussions as $d): ?>
                <?php $slug = $d['link']; $topic = $topic_map[$slug] ?? null; ?>
                <tr>
                    <td><?= $d['id'] ?></td>
                    <td><span class="badge badge-secondary"><?= htmlspecialchars($class_map[$d['class_id']] ?? $d['class_id']) ?></span></td>
                    <td>
                        <?php if ($d['type'] === 'interactive'): ?>
                            <span class="badge badge-info">Interactive</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Static</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($d['title']) ?></td>
                    <td class="text-muted" style="font-size:13px;"><?= htmlspecialchars($d['description']) ?></td>
                    <td style="font-size:13px;">
                        <?php if ($d['type'] === 'interactive' && $topic): ?>
                            <code><?= htmlspecialchars($slug) ?></code>
                            <div class="text-muted" style="font-size:11px;"><?= htmlspecialchars($topic['title']) ?> &bull; <?= $topic['sections'] ?> section<?= $topic['sections'] != 1 ? 's' : '' ?></div>
                        <?php else: ?>
                            <code><?= htmlspecialchars($slug) ?></code>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:13px;"><?= $d['display_date'] ? date('M d, Y', strtotime($d['display_date'])) : '<span class="text-muted">—</span>' ?></td>
                    <td class="text-nowrap">
                        <?php if ($d['type'] === 'interactive' && $slug): ?>
                            <a href="<?= site_url('InteractiveQuizController/discussion/' . urlencode($slug)) ?>"
                               class="btn btn-sm btn-outline-info" target="_blank" title="Preview discussion">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= site_url('InteractiveQuizController/edit_topic/' . urlencode($slug)) ?>"
                               class="btn btn-sm btn-outline-secondary" title="Edit questions">
                                <i class="fas fa-edit"></i>
                            </a>
                        <?php elseif ($d['type'] === 'static' && $slug): ?>
                            <a href="<?= site_url($slug) ?>" class="btn btn-sm btn-outline-info" target="_blank" title="Open link">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        <?php endif; ?>
                        <button class="btn btn-sm btn-outline-primary"
                            onclick="openEdit(<?= htmlspecialchars(json_encode($d), ENT_QUOTES) ?>)">
                            <i class="fas fa-pen"></i>
                        </button>
                        <form method="post" action="<?= site_url('AdminController/delete_discussion/' . $d['id']) ?>"
                              style="display:inline;"
                              onsubmit="return confirm('Delete this discussion?')">
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add / Edit Modal -->
<div class="modal fade" id="discussionModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" action="<?= site_url('AdminController/save_discussion') ?>" id="discussionForm" onsubmit="return syncLinkField()">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Discussion</h5>
                    <button type="button" class="close" onclick="closeDiscussionModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="fieldId">

                    <div class="form-group">
                        <label>Class <span class="text-danger">*</span></label>
                        <select name="class_id" id="fieldClassId" class="form-control" required>
                            <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['class_id'] ?>">
                                <?= htmlspecialchars($c['class_code'] . ' — ' . $c['class_name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Type <span class="text-danger">*</span></label>
                        <select name="type" id="fieldType" class="form-control" onchange="toggleLinkField(this.value)">
                            <option value="static">Static</option>
                            <option value="interactive">Interactive (Discussion + Quiz)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="fieldTitle" class="form-control" required maxlength="128">
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" name="description" id="fieldDesc" class="form-control" maxlength="255">
                    </div>

                    <!-- Static: browse discussions/{folder}/*.php topic files, or a custom URL/path -->
                    <div class="form-group" id="linkStatic">
                        <label>Link Source</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="static_source" id="staticSourceBrowse" value="browse" checked onchange="toggleStaticSource(this.value)">
                            <label class="form-check-label" for="staticSourceBrowse">Browse topic files</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="static_source" id="staticSourceCustom" value="custom" onchange="toggleStaticSource(this.value)">
                            <label class="form-check-label" for="staticSourceCustom">Custom URL / path</label>
                        </div>

                        <div id="staticBrowse" class="mt-2">
                            <select id="fieldLinkSelect" class="form-control">
                                <option value="">— select a topic file —</option>
                                <?php foreach ($static_topics as $folder => $files): ?>
                                <optgroup label="<?= htmlspecialchars($folder) ?>">
                                    <?php foreach ($files as $f): ?>
                                    <option value="<?= htmlspecialchars($f['path']) ?>"><?= htmlspecialchars($f['label']) ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">
                                From <code>application/views/discussions/{folder}/</code>
                            </small>
                        </div>

                        <div id="staticCustom" class="mt-2" style="display:none;">
                            <input type="text" id="fieldLinkCustom" class="form-control" maxlength="255"
                                   placeholder="e.g. assets/pdfjs/web/viewer.html?file=... or uploads/discussions/handout.pdf">
                            <small class="form-text text-muted">Use for PDFs, external URLs, or any path outside the topic-file folders.</small>
                        </div>

                        <input type="hidden" name="link" id="fieldLink">
                    </div>

                    <!-- Interactive: existing topic vs paste-a-new-JSON-template -->
                    <div class="form-group" id="linkInteractive" style="display:none;">
                        <label>Topic Source</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="json_source" id="sourceExisting" value="existing" checked onchange="toggleJsonSource(this.value)">
                            <label class="form-check-label" for="sourceExisting">Use existing topic</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="json_source" id="sourceNew" value="new" onchange="toggleJsonSource(this.value)">
                            <label class="form-check-label" for="sourceNew">Paste new JSON template</label>
                        </div>

                        <div id="topicExisting" class="mt-2">
                            <select name="link" id="fieldSlug" class="form-control">
                                <option value="">— select a topic —</option>
                                <?php foreach ($json_topics as $t): ?>
                                <option value="<?= htmlspecialchars($t['slug']) ?>">
                                    <?= htmlspecialchars($t['title']) ?>
                                    (<?= $t['sections'] ?> section<?= $t['sections'] != 1 ? 's' : '' ?>)
                                    — <?= htmlspecialchars($t['slug']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">
                                Opens via <code>InteractiveQuizController/discussion/{slug}</code>
                            </small>
                        </div>

                        <div id="topicNew" class="mt-2" style="display:none;">
                            <label>Slug <small class="text-muted">(lowercase letters, digits, underscores — becomes assets/json/{slug}.json)</small></label>
                            <input type="text" name="new_slug" id="fieldNewSlug" class="form-control" maxlength="100" placeholder="e.g. css_cascade">

                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <label class="mb-0">JSON Template</label>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertJsonTemplate()">Insert Template</button>
                            </div>
                            <textarea name="json_text" id="fieldJsonText" class="form-control" rows="10" style="font-family:monospace; font-size:12px;" placeholder="Paste JSON here or click &quot;Insert Template&quot;"></textarea>
                            <small class="form-text text-muted">
                                Saved to <code>assets/json/{slug}.json</code> and opens via <code>InteractiveQuizController/discussion/{slug}</code>
                            </small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Display Date <small class="text-muted">(optional — leave blank to show always)</small></label>
                        <input type="datetime-local" name="display_date" id="fieldDisplayDate" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeDiscussionModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function toggleLinkField(type) {
    const isInteractive = type === 'interactive';
    document.getElementById('linkStatic').style.display      = isInteractive ? 'none' : '';
    document.getElementById('linkInteractive').style.display = isInteractive ? '' : 'none';
    // fieldLink (static) and fieldSlug (interactive) both submit as name="link" —
    // only one may be enabled at a time or the form posts duplicate values.
    document.getElementById('fieldLink').disabled = isInteractive;
    document.getElementById('fieldSlug').disabled = !isInteractive;
    if (isInteractive) {
        document.getElementById('sourceExisting').checked = true;
        toggleJsonSource('existing');
    } else {
        document.getElementById('staticSourceBrowse').checked = true;
        toggleStaticSource('browse');
    }
}

function toggleJsonSource(source) {
    const isNew = source === 'new';
    document.getElementById('topicExisting').style.display = isNew ? 'none' : '';
    document.getElementById('topicNew').style.display      = isNew ? '' : 'none';
    document.getElementById('fieldSlug').disabled     = isNew;
    document.getElementById('fieldNewSlug').disabled  = !isNew;
    document.getElementById('fieldJsonText').disabled = !isNew;
}

function toggleStaticSource(source) {
    const isCustom = source === 'custom';
    document.getElementById('staticBrowse').style.display = isCustom ? 'none' : '';
    document.getElementById('staticCustom').style.display = isCustom ? '' : 'none';
    document.getElementById('fieldLinkSelect').disabled = isCustom;
    document.getElementById('fieldLinkCustom').disabled = !isCustom;
}

// Copies whichever static link control is active into the hidden fieldLink
// input before submit (fieldLink itself is never edited directly).
function syncLinkField() {
    if (document.getElementById('fieldType').value === 'static') {
        const source = document.querySelector('input[name="static_source"]:checked').value;
        document.getElementById('fieldLink').value = source === 'custom'
            ? document.getElementById('fieldLinkCustom').value.trim()
            : document.getElementById('fieldLinkSelect').value;
    }
    return true;
}

function insertJsonTemplate() {
    const template = {
        title: "New Topic Title",
        description: "Short description shown in the discussion list.",
        sections: [
            {
                title: "Section 1 Title",
                lesson: "<p>Lesson HTML content goes here.</p>",
                quiz: {
                    question: "Sample question?",
                    options: ["Option A", "Option B", "Option C", "Option D"],
                    correct: 0,
                    code: null
                }
            }
        ]
    };
    document.getElementById('fieldJsonText').value = JSON.stringify(template, null, 2);
}

function getDiscussionModal() {
    // bootstrap.bundle.min.js in assets/ is actually Bootstrap 5 despite the
    // shared filename — use the JS API directly instead of data-toggle/jQuery,
    // neither of which Bootstrap 5 supports for the old Bootstrap 4 markup.
    return bootstrap.Modal.getOrCreateInstance(document.getElementById('discussionModal'));
}

function closeDiscussionModal() {
    getDiscussionModal().hide();
}

function openAdd() {
    document.getElementById('modalTitle').textContent = 'Add Discussion';
    document.getElementById('fieldId').value          = '';
    document.getElementById('fieldClassId').value     = '';
    document.getElementById('fieldType').value        = 'static';
    document.getElementById('fieldTitle').value       = '';
    document.getElementById('fieldDesc').value        = '';
    document.getElementById('fieldLink').value        = '';
    document.getElementById('fieldLinkSelect').value  = '';
    document.getElementById('fieldLinkCustom').value  = '';
    document.getElementById('fieldSlug').value        = '';
    document.getElementById('fieldNewSlug').value     = '';
    document.getElementById('fieldJsonText').value    = '';
    document.getElementById('fieldDisplayDate').value = '';
    toggleLinkField('static');
    getDiscussionModal().show();
}

function openEdit(d) {
    document.getElementById('modalTitle').textContent = 'Edit Discussion';
    document.getElementById('fieldId').value          = d.id;
    document.getElementById('fieldClassId').value     = d.class_id;
    document.getElementById('fieldType').value        = d.type;
    document.getElementById('fieldTitle').value       = d.title;
    document.getElementById('fieldDesc').value        = d.description;
    document.getElementById('fieldNewSlug').value     = '';
    document.getElementById('fieldJsonText').value    = '';
    document.getElementById('fieldLinkSelect').value  = '';
    document.getElementById('fieldLinkCustom').value  = '';
    document.getElementById('fieldDisplayDate').value = d.display_date
        ? d.display_date.replace(' ', 'T').substring(0, 16)
        : '';

    toggleLinkField(d.type);

    if (d.type === 'interactive') {
        document.getElementById('fieldSlug').value = d.link;
    } else {
        const select = document.getElementById('fieldLinkSelect');
        const matchesOption = Array.from(select.options).some(o => o.value === d.link);
        if (matchesOption) {
            select.value = d.link;
        } else {
            document.getElementById('staticSourceCustom').checked = true;
            toggleStaticSource('custom');
            document.getElementById('fieldLinkCustom').value = d.link;
        }
    }

    getDiscussionModal().show();
}

toggleLinkField('static');
</script>

<?php $this->load->view('footer'); ?>

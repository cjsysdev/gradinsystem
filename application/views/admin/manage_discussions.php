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
            <button class="btn btn-primary" data-toggle="modal" data-target="#discussionModal" onclick="openAdd()">
                <i class="fas fa-plus"></i> Add Discussion
            </button>
        </div>
    </div>

    <?php foreach (['success','error','warning'] as $f): ?>
        <?php if ($msg = $this->session->flashdata($f)): ?>
            <div class="alert alert-<?= $f === 'success' ? 'success' : ($f === 'error' ? 'danger' : 'warning') ?> mt-2"><?= $msg ?></div>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="table-responsive mt-3">
        <table class="table table-bordered table-hover table-sm">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Class</th>
                    <th>Type</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Link / Slug</th>
                    <th>Display Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($discussions)): ?>
                <tr><td colspan="8" class="text-center text-muted">No discussions yet.</td></tr>
            <?php else: ?>
                <?php
                $class_map = [];
                foreach ($classes as $c) $class_map[$c['class_id']] = $c['class_code'] . ' — ' . $c['class_name'];
                ?>
                <?php foreach ($discussions as $d): ?>
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
                    <td><code><?= htmlspecialchars($d['link']) ?></code></td>
                    <td style="font-size:13px;"><?= $d['display_date'] ? date('M d, Y', strtotime($d['display_date'])) : '<span class="text-muted">—</span>' ?></td>
                    <td class="text-nowrap">
                        <button class="btn btn-sm btn-outline-primary"
                            onclick="openEdit(<?= htmlspecialchars(json_encode($d), ENT_QUOTES) ?>)">
                            Edit
                        </button>
                        <form method="post" action="<?= site_url('AdminController/delete_discussion/' . $d['id']) ?>"
                              style="display:inline;"
                              onsubmit="return confirm('Delete this discussion?')">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
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
        <form method="post" action="<?= site_url('AdminController/save_discussion') ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Discussion</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
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
                            <option value="interactive">Interactive</option>
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

                    <!-- Static: free-text URL/path -->
                    <div class="form-group" id="linkStatic">
                        <label>Link <small class="text-muted">(URL or internal path)</small></label>
                        <input type="text" name="link" id="fieldLink" class="form-control" maxlength="255">
                    </div>

                    <!-- Interactive: slug dropdown -->
                    <div class="form-group" id="linkInteractive" style="display:none;">
                        <label>Topic Slug <small class="text-muted">(JSON file in assets/json/)</small></label>
                        <select name="link" id="fieldSlug" class="form-control">
                            <option value="">— select a topic —</option>
                            <?php foreach ($json_slugs as $slug): ?>
                            <option value="<?= htmlspecialchars($slug) ?>"><?= htmlspecialchars($slug) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Display Date <small class="text-muted">(optional — leave blank to show always)</small></label>
                        <input type="datetime-local" name="display_date" id="fieldDisplayDate" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
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

    // Toggle required / disabled so only the visible one is submitted
    document.getElementById('fieldLink').disabled  = isInteractive;
    document.getElementById('fieldSlug').disabled  = !isInteractive;
}

function openAdd() {
    document.getElementById('modalTitle').textContent = 'Add Discussion';
    document.getElementById('fieldId').value          = '';
    document.getElementById('fieldClassId').value     = '';
    document.getElementById('fieldType').value        = 'static';
    document.getElementById('fieldTitle').value       = '';
    document.getElementById('fieldDesc').value        = '';
    document.getElementById('fieldLink').value        = '';
    document.getElementById('fieldSlug').value        = '';
    document.getElementById('fieldDisplayDate').value = '';
    toggleLinkField('static');
}

function openEdit(d) {
    document.getElementById('modalTitle').textContent = 'Edit Discussion';
    document.getElementById('fieldId').value          = d.id;
    document.getElementById('fieldClassId').value     = d.class_id;
    document.getElementById('fieldType').value        = d.type;
    document.getElementById('fieldTitle').value       = d.title;
    document.getElementById('fieldDesc').value        = d.description;
    document.getElementById('fieldDisplayDate').value = d.display_date
        ? d.display_date.replace(' ', 'T').substring(0, 16)
        : '';

    if (d.type === 'interactive') {
        document.getElementById('fieldSlug').value = d.link;
    } else {
        document.getElementById('fieldLink').value = d.link;
    }

    toggleLinkField(d.type);
    $('#discussionModal').modal('show');
}

// On load: ensure correct state
toggleLinkField('static');
</script>

<?php $this->load->view('footer'); ?>

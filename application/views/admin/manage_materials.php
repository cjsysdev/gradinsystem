<?php $this->load->view('header'); ?>

<div class="container mb-5">
    <div class="dashboard">
        <?php $this->load->view('profile_only'); ?>
        <?php $this->load->view('admin/nav_bar'); ?>
    </div>

    <h5 class="mb-3"><i class="fa fa-folder-open"></i> Class Materials</h5>
    <p class="small text-muted">
        Upload demo files, handouts and reviewers, then assign them to the sections that
        should see them. Students find them under <strong>Materials</strong> in their nav bar.
    </p>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>

    <?php if (!$table_ready): ?>
        <div class="alert alert-warning">
            <h6 class="font-weight-bold mb-2"><i class="fa fa-triangle-exclamation"></i> Setup required</h6>
            <p class="mb-3 small">
                The <code>class_materials</code> tables do not exist yet. Run the one-time
                installer to create them and prepare <code>assets/materials/</code>.
            </p>
            <form method="get" action="<?= base_url('admin/materials_install') ?>" class="mb-0">
                <button type="submit" class="btn btn-warning btn-sm">
                    <i class="fa fa-wrench"></i> Run installer
                </button>
            </form>
        </div>
    <?php else: ?>

    <?php
        // Group the section dropdown by course so a long list stays scannable.
        $grouped = [];
        foreach ($schedules as $s) {
            $key = $s['class_code'] . ' — ' . $s['class_name'];
            $grouped[$key][] = $s;
        }
        ksort($grouped);

        $icon_for = function ($ext) {
            $map = [
                'pdf'  => 'fa-file-pdf text-danger',
                'doc'  => 'fa-file-word text-primary',   'docx' => 'fa-file-word text-primary',
                'odt'  => 'fa-file-word text-primary',   'rtf'  => 'fa-file-word text-primary',
                'xls'  => 'fa-file-excel text-success',  'xlsx' => 'fa-file-excel text-success',
                'csv'  => 'fa-file-csv text-success',
                'ppt'  => 'fa-file-powerpoint text-warning', 'pptx' => 'fa-file-powerpoint text-warning',
                'zip'  => 'fa-file-zipper text-secondary',  'rar' => 'fa-file-zipper text-secondary',
                '7z'   => 'fa-file-zipper text-secondary',
                'png'  => 'fa-file-image text-info', 'jpg' => 'fa-file-image text-info',
                'jpeg' => 'fa-file-image text-info', 'gif' => 'fa-file-image text-info',
                'webp' => 'fa-file-image text-info',
                'mp4'  => 'fa-file-video text-dark', 'webm' => 'fa-file-video text-dark',
                'mp3'  => 'fa-file-audio text-dark',
                'sql'  => 'fa-database text-secondary',
                'txt'  => 'fa-file-lines text-muted', 'md' => 'fa-file-lines text-muted',
            ];
            return $map[$ext] ?? 'fa-file-code text-muted';
        };

        $human_size = function ($bytes) {
            $bytes = (int) $bytes;
            if ($bytes >= 1048576) { return round($bytes / 1048576, 1) . ' MB'; }
            if ($bytes >= 1024)    { return round($bytes / 1024) . ' KB'; }
            return $bytes . ' B';
        };
    ?>

    <div class="row">

        <!-- ── LEFT: Upload ─────────────────────────────────────────── -->
        <div class="col-lg-5 mb-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="font-weight-bold mb-3"><i class="fa fa-upload"></i> Upload a Material</h6>

                    <form method="post" action="<?= base_url('admin/upload_material') ?>"
                          enctype="multipart/form-data" id="upload-form">

                        <div class="form-group">
                            <label class="small mb-1">File <span class="text-danger">*</span></label>
                            <input type="file" name="material_file" id="material-file"
                                   class="form-control-file" required>
                            <small class="form-text text-muted">
                                Max <?= (int) $max_size_mb ?> MB
                                <span class="text-muted">(server allows
                                    <?= html_escape($php_upload_max) ?> per file,
                                    <?= html_escape($php_post_max) ?> per request)</span>.
                                <br>Allowed: <?= implode(', ', $whitelist) ?>.
                                <br><em>.php demo files are stored as plain text so they can never run on
                                the server; students still download them named <code>.php</code>.
                                .html and .svg stay blocked — they run scripts in the student's session.</em>
                            </small>
                        </div>

                        <div class="form-group">
                            <label class="small mb-1">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="material-title" class="form-control"
                                   maxlength="200" placeholder="e.g. Demo 1 — INSERT query" required>
                        </div>

                        <div class="form-group">
                            <label class="small mb-1">Category <span class="text-muted">(optional)</span></label>
                            <input type="text" name="category" class="form-control" maxlength="64"
                                   list="category-suggestions" placeholder="e.g. Demo 1, Week 3, Finals Reviewer">
                            <datalist id="category-suggestions">
                                <?php
                                $seen = [];
                                foreach ($materials as $m) {
                                    if (!empty($m['category']) && !isset($seen[$m['category']])) {
                                        $seen[$m['category']] = true;
                                        echo '<option value="' . html_escape($m['category']) . '"></option>';
                                    }
                                }
                                ?>
                            </datalist>
                            <small class="form-text text-muted">Used to group the list students see.</small>
                        </div>

                        <div class="form-group">
                            <label class="small mb-1">Description <span class="text-muted">(optional)</span></label>
                            <textarea name="description" class="form-control" rows="2"
                                      placeholder="What this file is, or how to use it."></textarea>
                        </div>

                        <div class="form-group mb-2">
                            <label class="small mb-1">
                                Sections <span class="text-danger">*</span>
                                <a href="#" class="small ml-2" onclick="toggleAll(this, true); return false;">all</a> /
                                <a href="#" class="small" onclick="toggleAll(this, false); return false;">none</a>
                            </label>
                            <div class="border rounded p-2" style="max-height:220px; overflow-y:auto;" id="section-box">
                                <?php if (empty($grouped)): ?>
                                    <span class="text-muted small">No active sections this semester.</span>
                                <?php else: ?>
                                    <?php foreach ($grouped as $course => $rows): ?>
                                        <div class="font-weight-bold small text-muted mt-2"><?= html_escape($course) ?></div>
                                        <?php foreach ($rows as $s): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       name="schedule_ids[]" value="<?= (int) $s['schedule_id'] ?>"
                                                       id="sched-<?= (int) $s['schedule_id'] ?>">
                                                <label class="form-check-label small" for="sched-<?= (int) $s['schedule_id'] ?>">
                                                    <?= html_escape($s['section']) ?>
                                                    <span class="badge badge-light"><?= html_escape($s['type'] ?: 'NA') ?></span>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                            <small class="form-text text-muted">
                                The file is stored once and shared by every section you tick.
                                It lands in <code>assets/materials/{CLASS_CODE}/</code> based on the first one.
                            </small>
                        </div>

                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fa fa-upload"></i> Upload
                        </button>
                    </form>

                    <hr>
                    <p class="small text-muted mb-0">
                        <i class="fa fa-circle-info"></i>
                        Section assignment controls <strong>what each student sees listed</strong>.
                        The file itself sits in a public folder, so treat anything here as
                        shareable — don't put exam keys in it.
                    </p>
                </div>
            </div>
        </div>

        <!-- ── RIGHT: Existing materials ────────────────────────────── -->
        <div class="col-lg-7 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Uploaded Materials</strong>
                    <span class="badge badge-secondary"><?= count($materials) ?></span>
                </div>

                <?php if (empty($materials)): ?>
                    <div class="card-body text-center text-muted py-5">
                        <p class="mb-1" style="font-size:32px;"><i class="fa fa-folder-open"></i></p>
                        <p class="mb-0">Nothing uploaded yet.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>File</th>
                                    <th>Category</th>
                                    <th>Sections</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($materials as $m): ?>
                                <tr>
                                    <td>
                                        <i class="fa <?= $icon_for($m['extension']) ?>"></i>
                                        <strong><?= html_escape($m['title']) ?></strong>
                                        <?php if (!$m['is_active']): ?>
                                            <span class="badge badge-secondary">hidden</span>
                                        <?php endif; ?>
                                        <?php if (!$m['on_disk']): ?>
                                            <span class="badge badge-danger" title="The record exists but the file is missing from disk">file missing</span>
                                        <?php endif; ?>
                                        <br>
                                        <small class="text-muted">
                                            <?= html_escape($m['original_name']) ?>
                                            &middot; <?= $human_size($m['file_size']) ?>
                                            &middot; <?= date('M j, Y', strtotime($m['created_at'])) ?>
                                        </small>
                                        <?php if (!empty($m['description'])): ?>
                                            <br><small class="text-muted"><?= html_escape($m['description']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($m['category'])): ?>
                                            <span class="badge badge-info"><?= html_escape($m['category']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small">
                                        <?= $m['assigned_sections']
                                            ? html_escape($m['assigned_sections'])
                                            : '<span class="text-danger">unassigned — no one can see this</span>' ?>
                                    </td>
                                    <td class="text-right text-nowrap">
                                        <?php if ($m['on_disk']): ?>
                                            <a href="<?= base_url($m['rel_path']) ?>" target="_blank"
                                               class="btn btn-sm btn-outline-secondary" title="Open">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary"
                                                data-toggle="modal" data-target="#edit-<?= (int) $m['material_id'] ?>"
                                                title="Edit">
                                            <i class="fa fa-pen"></i>
                                        </button>
                                        <form method="post" class="d-inline"
                                              action="<?= base_url('admin/delete_material/' . (int) $m['material_id']) ?>"
                                              onsubmit="return confirm('Delete \'<?= html_escape(addslashes($m['title'])) ?>\'? The file will be removed from the server.');">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── Edit modals (metadata + reassignment only; the file is never touched) ── -->
    <?php foreach ($materials as $m): ?>
        <div class="modal fade" id="edit-<?= (int) $m['material_id'] ?>" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <form method="post" action="<?= base_url('admin/update_material') ?>">
                    <input type="hidden" name="material_id" value="<?= (int) $m['material_id'] ?>">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title">Edit &mdash; <?= html_escape($m['original_name']) ?></h6>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <p class="small text-muted">
                                Changes here never touch the uploaded file. To replace the file itself,
                                delete this material and upload the new one.
                            </p>

                            <div class="form-group">
                                <label class="small mb-1">Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" maxlength="200"
                                       value="<?= html_escape($m['title']) ?>" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label class="small mb-1">Category</label>
                                    <input type="text" name="category" class="form-control" maxlength="64"
                                           value="<?= html_escape($m['category']) ?>">
                                </div>
                                <div class="form-group col-md-6 d-flex align-items-end">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                               id="active-<?= (int) $m['material_id'] ?>"
                                               <?= $m['is_active'] ? 'checked' : '' ?>>
                                        <label class="form-check-label small" for="active-<?= (int) $m['material_id'] ?>">
                                            Visible to students
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="small mb-1">Description</label>
                                <textarea name="description" class="form-control" rows="2"><?= html_escape($m['description']) ?></textarea>
                            </div>

                            <div class="form-group mb-0">
                                <label class="small mb-1">Sections <span class="text-danger">*</span></label>
                                <div class="border rounded p-2" style="max-height:220px; overflow-y:auto;">
                                    <?php foreach ($grouped as $course => $rows): ?>
                                        <div class="font-weight-bold small text-muted mt-2"><?= html_escape($course) ?></div>
                                        <?php foreach ($rows as $s): ?>
                                            <?php $cid = 'm' . $m['material_id'] . '-s' . $s['schedule_id']; ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox"
                                                       name="schedule_ids[]" value="<?= (int) $s['schedule_id'] ?>"
                                                       id="<?= $cid ?>"
                                                       <?= in_array((int) $s['schedule_id'], $m['section_ids'], true) ? 'checked' : '' ?>>
                                                <label class="form-check-label small" for="<?= $cid ?>">
                                                    <?= html_escape($s['section']) ?>
                                                    <span class="badge badge-light"><?= html_escape($s['type'] ?: 'NA') ?></span>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </div>
                                <small class="form-text text-muted">
                                    Unticking a section removes access for its students — this replaces
                                    the whole assignment list, it does not merge.
                                </small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm">Save changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    <?php endforeach; ?>

    <?php endif; /* table_ready */ ?>
</div>

<script>
    function toggleAll(link, state) {
        var box = document.getElementById('section-box');
        if (!box) return;
        box.querySelectorAll('input[type=checkbox]').forEach(function (cb) { cb.checked = state; });
    }

    // Prefill the title from the chosen filename, so the common case is one click.
    (function () {
        var file  = document.getElementById('material-file');
        var title = document.getElementById('material-title');
        if (!file || !title) return;

        file.addEventListener('change', function () {
            if (title.value.trim() !== '' || !file.files.length) return;
            var name = file.files[0].name.replace(/\.[^.]+$/, '');
            title.value = name.replace(/[_-]+/g, ' ').trim();
        });
    })();
</script>

<?php $this->load->view('footer'); ?>

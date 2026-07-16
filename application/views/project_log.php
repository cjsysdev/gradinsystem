<?php $this->load->view('header'); ?>

<div class="container mb-5">
    <?php $this->load->view('profile_info'); ?>

    <h5 class="mb-3"><i class="fa fa-diagram-project"></i> Project Progress Log</h5>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>

    <?php if (empty($courses)): ?>
        <p class="text-muted">You have no enrolled courses this semester, so there is nothing to log yet.</p>
        </div><?php $this->load->view('footer'); return; ?>
    <?php endif; ?>

    <?php
        // status label -> Bootstrap badge class
        $status_badge = ['planned' => 'secondary', 'in-progress' => 'warning', 'done' => 'success'];
    ?>

    <?php if (count($courses) > 1): ?>
        <form method="get" action="" class="form-inline mb-3" id="course-picker">
            <label class="mr-2 font-weight-bold">Course:</label>
            <select class="form-control" onchange="location.href='<?= base_url('project_log') ?>/' + this.value;">
                <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['class_id'] ?>" <?= ((int)$c['class_id'] === (int)$selected_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['class_code'] . ' — ' . $c['class_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    <?php else: ?>
        <p class="text-muted mb-3">
            <strong><?= htmlspecialchars($selected['class_code'] . ' — ' . $selected['class_name']) ?></strong>
        </p>
    <?php endif; ?>

    <!-- ── Team banner (group mode only) ─────────────────────────── -->
    <?php if ($mode === 'group'): ?>
        <div class="alert alert-info">
            <strong><i class="fa fa-people-group"></i> <?= htmlspecialchars($group['group_name']) ?></strong>
            — shared team log. Members:
            <?php foreach ($members as $m): ?>
                <span class="badge badge-light border"><?= htmlspecialchars($m['firstname'] . ' ' . $m['lastname']) ?></span>
            <?php endforeach; ?>
        </div>
    <?php elseif ($mode === 'ungrouped'): ?>
        <div class="alert alert-warning">
            Your instructor set up teams for this course, but you're not on a team yet — ask them to add you.
        </div>
    <?php endif; ?>

    <!-- ── Entry list ─────────────────────────────────────────────── -->
    <?php if (!empty($entries)): ?>
        <div class="mb-4">
            <?php foreach ($entries as $e): ?>
                <div class="card mb-3">
                    <div class="card-body py-2 px-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong><?= htmlspecialchars($e['title']) ?></strong>
                                <span class="badge badge-<?= $status_badge[$e['status']] ?? 'secondary' ?> ml-1">
                                    <?= htmlspecialchars(ucfirst($e['status'])) ?>
                                </span>
                                <div class="small text-muted">
                                    <?php if ($mode === 'group' && !empty($e['firstname'])): ?>
                                        <span class="font-weight-bold"><?= htmlspecialchars($e['firstname'] . ' ' . $e['lastname']) ?></span>
                                        &nbsp;·&nbsp;
                                    <?php endif; ?>
                                    <?= date('M d, Y g:i A', strtotime($e['created_at'])) ?>
                                    <?php if (!empty($e['updated_at'])): ?>
                                        &nbsp;·&nbsp;edited <?= date('M d, Y g:i A', strtotime($e['updated_at'])) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ((int)$e['student_id'] === (int)$this->session->student_id): ?>
                                <div class="d-flex" style="gap: 6px;">
                                    <button type="button" class="btn btn-sm btn-outline-primary js-edit"
                                        data-id="<?= $e['log_id'] ?>"
                                        data-title="<?= htmlspecialchars($e['title'], ENT_QUOTES) ?>"
                                        data-status="<?= htmlspecialchars($e['status'], ENT_QUOTES) ?>"
                                        data-description="<?= htmlspecialchars((string)$e['description'], ENT_QUOTES) ?>"
                                        data-link="<?= htmlspecialchars((string)$e['link'], ENT_QUOTES) ?>"
                                        data-code="<?= htmlspecialchars((string)$e['code'], ENT_QUOTES) ?>"
                                        title="Edit"><i class="fa fa-pen"></i></button>
                                    <form method="post" action="<?= base_url('project_log/delete/' . $e['log_id']) ?>"
                                          onsubmit="return confirm('Remove this entry?');" class="m-0">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Remove">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($e['description'])): ?>
                            <p class="mb-1 mt-2"><?= nl2br(htmlspecialchars($e['description'])) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($e['code'])): ?>
                            <pre class="mt-2 mb-1"><code><?= htmlspecialchars($e['code']) ?></code></pre>
                        <?php endif; ?>

                        <div class="small">
                            <?php if (!empty($e['link'])): ?>
                                <a href="<?= htmlspecialchars($e['link']) ?>" target="_blank" rel="noopener">
                                    <i class="fa fa-link"></i> <?= htmlspecialchars($e['link']) ?>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($e['file_upload'])): ?>
                                &nbsp;<a href="<?= base_url('uploads/project_logs/' . $e['file_upload']) ?>" target="_blank" rel="noopener">
                                    <i class="fa fa-paperclip"></i> Attachment
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-muted">No progress entries yet for this course. Add your first one below.</p>
    <?php endif; ?>

    <!-- ── Add / edit form ────────────────────────────────────────── -->
    <?php if ($mode !== 'ungrouped'): ?>
    <hr>
    <h6 class="mb-3" id="form-heading">Add Progress Entry</h6>

    <form id="log-form" action="<?= base_url('project_log/save') ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="class_id" value="<?= (int)$selected_id ?>">

        <div class="form-row">
            <div class="form-group col-md-8">
                <label class="form-label">Title / Milestone <span class="text-danger">*</span></label>
                <input type="text" name="title" id="f-title" class="form-control" placeholder="e.g. Finished login page" required>
            </div>
            <div class="form-group col-md-4">
                <label class="form-label">Status</label>
                <select name="status" id="f-status" class="form-control">
                    <option value="planned">Planned</option>
                    <option value="in-progress">In Progress</option>
                    <option value="done">Done</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Description / Notes</label>
            <textarea name="description" id="f-description" class="form-control" rows="3" placeholder="What did you work on?"></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Code (optional)</label>
            <textarea name="code" id="code-editor"></textarea>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label class="form-label">Link (repo / demo URL)</label>
                <input type="url" name="link" id="f-link" class="form-control" placeholder="https://github.com/...">
            </div>
            <div class="form-group col-md-6">
                <label class="form-label">Attachment (optional)</label>
                <input type="file" name="file_upload" class="form-control-file">
            </div>
        </div>

        <button type="submit" class="btn btn-info" id="form-submit">Add Entry</button>
        <button type="button" class="btn btn-outline-secondary d-none" id="cancel-edit">Cancel</button>
    </form>
    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Syntax-highlight the rendered code blocks in the entry list.
        if (window.hljs) {
            document.querySelectorAll('pre code').forEach(function (block) {
                hljs.highlightElement(block);
            });
        }

        var form      = document.getElementById('log-form');
        var heading    = document.getElementById('form-heading');
        var submitBtn = document.getElementById('form-submit');
        var cancelBtn = document.getElementById('cancel-edit');
        var addAction = '<?= base_url('project_log/save') ?>';
        var updateBase = '<?= base_url('project_log/update') ?>/';

        function setCode(val) {
            // window.editor is the CodeMirror instance created in footer.php.
            if (window.editor) { window.editor.setValue(val || ''); }
            else { document.getElementById('code-editor').value = val || ''; }
        }

        function resetToAdd() {
            form.action = addAction;
            heading.textContent = 'Add Progress Entry';
            submitBtn.textContent = 'Add Entry';
            document.getElementById('f-title').value = '';
            document.getElementById('f-status').value = 'planned';
            document.getElementById('f-description').value = '';
            document.getElementById('f-link').value = '';
            setCode('');
            cancelBtn.classList.add('d-none');
        }

        document.querySelectorAll('.js-edit').forEach(function (btn) {
            btn.addEventListener('click', function () {
                form.action = updateBase + btn.dataset.id;
                heading.textContent = 'Edit Progress Entry';
                submitBtn.textContent = 'Save Changes';
                document.getElementById('f-title').value = btn.dataset.title;
                document.getElementById('f-status').value = btn.dataset.status || 'planned';
                document.getElementById('f-description').value = btn.dataset.description;
                document.getElementById('f-link').value = btn.dataset.link;
                setCode(btn.dataset.code);
                cancelBtn.classList.remove('d-none');
                heading.scrollIntoView({ behavior: 'smooth' });
            });
        });

        cancelBtn.addEventListener('click', resetToAdd);
    });
</script>

<?php $this->load->view('footer'); ?>

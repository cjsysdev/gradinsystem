<?php $this->load->view('header'); ?>

<div class="container mb-5">
    <div class="dashboard">
        <?php $this->load->view('profile_only'); ?>
        <?php $this->load->view('admin/nav_bar'); ?>
    </div>
    <h5 class="mb-3"><i class="fa fa-diagram-project"></i> Project Progress Logs</h5>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>

    <?php $status_badge = ['planned' => 'secondary', 'in-progress' => 'warning', 'done' => 'success']; ?>

    <!-- ── Group designation panel ───────────────────────────────── -->
    <div class="card mb-4">
        <div class="card-body">
            <h6 class="mb-3"><i class="fa fa-people-group"></i> Team Project Logs</h6>
            <p class="small text-muted">
                Designate a grouping set for a course to make its project log shared per team
                instead of per student. Clear the selection to go back to individual logs.
            </p>
            <form method="post" action="<?= base_url('admin/save_project_log_groupings') ?>" class="form-row align-items-end">
                <div class="form-group col-md-4">
                    <label class="form-label small mb-1">Course</label>
                    <select name="class_id" class="form-control" id="designation-course" onchange="showDesignationSets(this.value)">
                        <option value="">Select a course…</option>
                        <?php foreach ($designations as $d): ?>
                            <option value="<?= $d['course']['class_id'] ?>">
                                <?= htmlspecialchars($d['course']['class_code'] . ' — ' . $d['course']['class_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group col-md-6">
                    <label class="form-label small mb-1">Grouping Set(s)</label>
                    <?php foreach ($designations as $d): ?>
                        <select name="set_id[]" multiple disabled
                                class="form-control designation-sets"
                                id="designation-sets-<?= $d['course']['class_id'] ?>"
                                style="display:none;">
                            <?php foreach ($d['available_sets'] as $s): ?>
                                <option value="<?= $s['set_id'] ?>" <?= in_array((int)$s['set_id'], $d['set_ids'], true) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['name'] . ' (' . $s['section_id'] . ')') ?>
                                </option>
                            <?php endforeach; ?>
                            <?php if (empty($d['available_sets'])): ?>
                                <option value="" disabled>No grouping sets for this course's sections yet</option>
                            <?php endif; ?>
                        </select>
                    <?php endforeach; ?>
                    <small class="form-text text-muted">Ctrl/Cmd-click to select multiple; select none to clear.</small>
                </div>
                <div class="form-group col-md-2">
                    <button type="submit" class="btn btn-info btn-block"><i class="fa fa-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Filters -->
    <form method="get" action="<?= base_url('admin/project_logs') ?>" class="form-row align-items-end mb-4">
        <div class="form-group col-md-4">
            <label class="form-label small mb-1">Course</label>
            <select name="class_id" class="form-control">
                <option value="">All courses</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['class_id'] ?>" <?= ((int)$class_id === (int)$c['class_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['class_code'] . ' — ' . $c['class_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group col-md-4">
            <label class="form-label small mb-1">Section</label>
            <select name="section" class="form-control">
                <option value="">All sections</option>
                <?php foreach ($sections as $s): ?>
                    <option value="<?= htmlspecialchars($s['section']) ?>" <?= ($section === $s['section']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['section']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group col-md-4">
            <button type="submit" class="btn btn-outline-secondary"><i class="fa fa-filter"></i> Filter</button>
            <a href="<?= base_url('admin/project_logs') ?>" class="btn btn-link">Reset</a>
        </div>
    </form>

    <?php if (empty($logs)): ?>
        <p class="text-muted">No project log entries found for the selected filter.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead class="thead-light">
                    <tr>
                        <th>Student</th>
                        <th>Section</th>
                        <th>Course</th>
                        <th>Team</th>
                        <th>Title / Milestone</th>
                        <th>Status</th>
                        <th>Notes</th>
                        <th>Links</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $l): ?>
                        <tr>
                            <td><?= htmlspecialchars(trim(($l['lastname'] ?? '') . ', ' . ($l['firstname'] ?? ''), ', ')) ?></td>
                            <td><?= htmlspecialchars((string)$l['section']) ?></td>
                            <td><?= htmlspecialchars((string)$l['class_code']) ?></td>
                            <td><?= !empty($l['group_name']) ? htmlspecialchars($l['group_name']) : '<span class="text-muted">—</span>' ?></td>
                            <td><?= htmlspecialchars($l['title']) ?></td>
                            <td>
                                <span class="badge badge-<?= $status_badge[$l['status']] ?? 'secondary' ?>">
                                    <?= htmlspecialchars(ucfirst($l['status'])) ?>
                                </span>
                            </td>
                            <td style="max-width:280px;">
                                <?php if (!empty($l['description'])): ?>
                                    <small><?= nl2br(htmlspecialchars($l['description'])) ?></small>
                                <?php endif; ?>
                                <?php if (!empty($l['code'])): ?>
                                    <pre class="mb-0 mt-1" style="max-height:160px;overflow:auto;"><code><?= htmlspecialchars($l['code']) ?></code></pre>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($l['link'])): ?>
                                    <a href="<?= htmlspecialchars($l['link']) ?>" target="_blank" rel="noopener" title="<?= htmlspecialchars($l['link']) ?>"><i class="fa fa-link"></i></a>
                                <?php endif; ?>
                                <?php if (!empty($l['file_upload'])): ?>
                                    <a href="<?= base_url('uploads/project_logs/' . $l['file_upload']) ?>" target="_blank" rel="noopener" title="Attachment"><i class="fa fa-paperclip"></i></a>
                                <?php endif; ?>
                            </td>
                            <td class="text-nowrap"><small><?= date('M d, Y', strtotime($l['created_at'])) ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script>
    function showDesignationSets(classId) {
        document.querySelectorAll('.designation-sets').forEach(function (sel) {
            var isTarget = sel.id === 'designation-sets-' + classId;
            sel.style.display = isTarget ? '' : 'none';
            sel.disabled = !isTarget;
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (window.hljs) {
            document.querySelectorAll('pre code').forEach(function (block) {
                hljs.highlightElement(block);
            });
        }

        var courseSelect = document.getElementById('designation-course');
        if (courseSelect && courseSelect.value) {
            showDesignationSets(courseSelect.value);
        }
    });
</script>

<?php $this->load->view('footer'); ?>

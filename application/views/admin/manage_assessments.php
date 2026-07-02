<?php $this->load->view('header'); ?>

<div class="container">
    <div class="dashboard">
        <?php $this->load->view('profile_only'); ?>
        <?php $this->load->view('admin/nav_bar'); ?>
    </div>

    <div class="row mt-3 align-items-center">
        <div class="col">
            <h4>Assessments</h4>
        </div>
        <div class="col-auto">
            <button class="btn btn-primary" data-toggle="modal" data-target="#assessmentModal" onclick="openAddModal()">
                <i class="fas fa-plus"></i> Add Assessment
            </button>
        </div>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success mt-2"><?= $this->session->flashdata('success') ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger mt-2"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>

    <form method="get" action="<?= base_url('manage_assessments') ?>" class="form-inline mt-3 mb-3">
        <label class="mr-2">Section:</label>
        <select name="schedule_id" class="form-control mr-2" onchange="this.form.submit()">
            <option value="">All Sections</option>
            <?php foreach ($schedules as $s): ?>
                <option value="<?= $s['schedule_id'] ?>" <?= $selected_schedule == $s['schedule_id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($s['section']) ?> &mdash; <?= htmlspecialchars($s['class_code']) ?> (<?= $s['type'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Section</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Term</th>
                    <th>Max Score</th>
                    <th>Due</th>
                    <th>Submissions</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($assessments)): ?>
                    <?php foreach ($assessments as $a): ?>
                        <tr>
                            <td><?= $a['assessment_id'] ?></td>
                            <td><span class="badge badge-secondary"><?= htmlspecialchars($a['section']) ?></span></td>
                            <td><?= htmlspecialchars($a['title']) ?></td>
                            <td><?= htmlspecialchars($a['iotype']) ?></td>
                            <td>
                                <?php
                                $termLabels = ['midterm' => 'Midterm', 'tentative-final' => 'Tentative Final', 'final' => 'Final'];
                                echo $termLabels[$a['term']] ?? $a['term'];
                                ?>
                            </td>
                            <td><?= $a['max_score'] ?></td>
                            <td><?= date('M d, Y H:i', strtotime($a['due'])) ?></td>
                            <td><span class="badge badge-info"><?= $a['submission_count'] ?></span></td>
                            <td>
                                <?php $statusValue = is_numeric($a['status']) ? (int)$a['status'] : ($a['status'] === 'open' ? 1 : 0); ?>
                                <select class="form-control form-control-sm"
                                        data-id="<?= $a['assessment_id'] ?>"
                                        onchange="updateStatus(this)">
                                    <option value="1" <?= $statusValue === 1 ? 'selected' : '' ?>>Open</option>
                                    <option value="0" <?= $statusValue === 0 ? 'selected' : '' ?>>Closed</option>
                                </select>
                            </td>
                            <td class="text-nowrap">
                                <button class="btn btn-sm btn-outline-primary"
                                        data-toggle="modal"
                                        data-target="#assessmentModal"
                                        onclick='openEditModal(<?= htmlspecialchars(json_encode($a), ENT_QUOTES) ?>)'>
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="<?= base_url('all_submissions/' . $a['assessment_id']) ?>"
                                   class="btn btn-sm btn-outline-secondary"
                                   title="View Submissions">
                                    <i class="fas fa-list"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="text-center text-muted">No assessments found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add / Edit Modal -->
<div class="modal fade" id="assessmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" action="<?= base_url('save_assessment') ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Assessment</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="assessment_id" id="modal_assessment_id">
                    <input type="hidden" name="schedule_id_filter" value="<?= $selected_schedule ?>">

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Section <span class="text-danger">*</span></label>
                            <select name="schedule_id" id="modal_schedule_id" class="form-control" required>
                                <option value="">Select section...</option>
                                <?php foreach ($schedules as $s): ?>
                                    <option value="<?= $s['schedule_id'] ?>">
                                        <?= htmlspecialchars($s['section']) ?> &mdash; <?= htmlspecialchars($s['class_code']) ?> (<?= $s['type'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Assessment Type <span class="text-danger">*</span></label>
                            <select name="iotype_id" id="modal_iotype_id" class="form-control" required>
                                <option value="">Select type...</option>
                                <?php foreach ($io_types as $t): ?>
                                    <option value="<?= $t['iotype_id'] ?>"><?= htmlspecialchars($t['type']) ?> (<?= $t['percentage'] ?>%)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="modal_title" class="form-control" required maxlength="64">
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="modal_description" class="form-control" rows="3"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Max Score <span class="text-danger">*</span></label>
                            <input type="number" name="max_score" id="modal_max_score" class="form-control" min="1" required>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Term <span class="text-danger">*</span></label>
                            <select name="term" id="modal_term" class="form-control" required>
                                <option value="midterm">Midterm</option>
                                <option value="tentative-final">Tentative Final</option>
                                <option value="final">Final</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Status</label>
                            <select name="status" id="modal_status" class="form-control">
                                <option value="1">Open</option>
                                <option value="0">Closed</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Due Date &amp; Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="due" id="modal_due" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" name="is_groupings" id="modal_is_groupings" class="form-check-input" value="1">
                        <label class="form-check-label" for="modal_is_groupings">Group Submission</label>
                    </div>
                    <div class="form-group mt-2" id="modal_grouping_set_wrap" style="display:none">
                        <label>Grouping Set</label>
                        <select name="grouping_set_id" id="modal_grouping_set_id" class="form-control">
                            <option value="">Select grouping set...</option>
                        </select>
                        <small class="form-text text-muted">
                            Sets are managed under <a href="<?= base_url('Groupings') ?>" target="_blank">Groupings</a>.
                        </small>
                    </div>

                    <div class="form-group mt-3">
                        <label>Widget (optional)</label>
                        <select name="widget_id" id="modal_widget_id" class="form-control">
                            <option value="">None &mdash; plain code/file submission</option>
                            <?php foreach ($widgets as $w): ?>
                                <option value="<?= $w['widget_id'] ?>"><?= htmlspecialchars($w['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" id="modal_given_wrap" style="display:none">
                        <label>Widget Config (JSON)</label>
                        <textarea name="given" id="modal_given" class="form-control" rows="5"
                                  placeholder='{"columns": ["Column A", "Column B"], "min_rows": 3, "allow_add_rows": true}'></textarea>
                        <small class="form-text text-muted">
                            See root/docs/paperless-midterm-plan.md for each widget's config schema.
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="modal_submit_btn">Add Assessment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// schedule_id -> section, used to filter grouping sets to the assessment's section
const scheduleSections = {
    <?php foreach ($schedules as $s): ?>
        <?= (int) $s['schedule_id'] ?>: <?= json_encode($s['section']) ?>,
    <?php endforeach; ?>
};

// section -> [{set_id, name}], used to populate the grouping-set dropdown
const setsBySection = {};
<?php foreach ($grouping_sets as $gs): ?>
    if (!setsBySection[<?= json_encode($gs['section_id']) ?>]) setsBySection[<?= json_encode($gs['section_id']) ?>] = [];
    setsBySection[<?= json_encode($gs['section_id']) ?>].push({ set_id: <?= (int) $gs['set_id'] ?>, name: <?= json_encode($gs['name']) ?> });
<?php endforeach; ?>

function refreshGroupingSetOptions(selectedSetId) {
    const scheduleId = document.getElementById('modal_schedule_id').value;
    const section = scheduleSections[scheduleId];
    const select = document.getElementById('modal_grouping_set_id');
    const sets = (section && setsBySection[section]) || [];

    select.innerHTML = '<option value="">Select grouping set...</option>';
    sets.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.set_id;
        opt.textContent = s.name;
        select.appendChild(opt);
    });
    select.value = selectedSetId || '';
}

function toggleGroupingSetWrap() {
    document.getElementById('modal_grouping_set_wrap').style.display =
        document.getElementById('modal_is_groupings').checked ? '' : 'none';
}

function toggleGivenWrap() {
    document.getElementById('modal_given_wrap').style.display =
        document.getElementById('modal_widget_id').value ? '' : 'none';
}

document.getElementById('modal_schedule_id').addEventListener('change', () => refreshGroupingSetOptions());
document.getElementById('modal_is_groupings').addEventListener('change', toggleGroupingSetWrap);
document.getElementById('modal_widget_id').addEventListener('change', toggleGivenWrap);

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Assessment';
    document.getElementById('modal_assessment_id').value = '';
    document.getElementById('modal_schedule_id').value = '<?= $selected_schedule ?: '' ?>';
    document.getElementById('modal_iotype_id').value = '';
    document.getElementById('modal_title').value = '';
    document.getElementById('modal_description').value = '';
    document.getElementById('modal_max_score').value = '';
    document.getElementById('modal_term').value = 'final';
    document.getElementById('modal_status').value = '0';
    document.getElementById('modal_due').value = '';
    document.getElementById('modal_is_groupings').checked = false;
    refreshGroupingSetOptions();
    toggleGroupingSetWrap();
    document.getElementById('modal_widget_id').value = '';
    document.getElementById('modal_given').value = '';
    toggleGivenWrap();
    document.getElementById('modal_submit_btn').textContent = 'Add Assessment';
    if (typeof $ !== 'undefined') $('#assessmentModal').modal('show');
}

function openEditModal(a) {
    document.getElementById('modalTitle').textContent = 'Edit Assessment';
    document.getElementById('modal_assessment_id').value = a.assessment_id;
    document.getElementById('modal_schedule_id').value = a.schedule_id;
    document.getElementById('modal_iotype_id').value = a.iotype_id;
    document.getElementById('modal_title').value = a.title;
    document.getElementById('modal_description').value = a.description || '';
    document.getElementById('modal_max_score').value = a.max_score;
    document.getElementById('modal_term').value = a.term;
    document.getElementById('modal_status').value = (a.status === 'open' || a.status === 1 || a.status === '1') ? '1' : '0';
    document.getElementById('modal_due').value = a.due ? a.due.replace(' ', 'T').substring(0, 16) : '';
    document.getElementById('modal_is_groupings').checked = parseInt(a.is_groupings) === 1;
    refreshGroupingSetOptions(a.grouping_set_id);
    toggleGroupingSetWrap();
    document.getElementById('modal_widget_id').value = a.widget_id || '';
    document.getElementById('modal_given').value = a.given || '';
    toggleGivenWrap();
    document.getElementById('modal_submit_btn').textContent = 'Update Assessment';
    if (typeof $ !== 'undefined') $('#assessmentModal').modal('show');
}

function updateStatus(select) {
    const assessment_id = select.dataset.id;
    const status = select.value;
    const original = status === '1' ? '0' : '1';

    fetch('<?= base_url('update_assessment_status') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'assessment_id=' + encodeURIComponent(assessment_id) + '&status=' + encodeURIComponent(status)
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            alert('Failed to update status.');
            select.value = original;
        }
    })
    .catch(() => {
        alert('Request failed.');
        select.value = original;
    });
}
</script>

<?php $this->load->view('footer'); ?>

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

                    <div class="form-group" id="modal_apply_mode_wrap">
                        <label>Apply To</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="apply_mode" id="modal_apply_mode_section" value="section" checked>
                            <label class="form-check-label" for="modal_apply_mode_section">One Section</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="apply_mode" id="modal_apply_mode_class" value="class">
                            <label class="form-check-label" for="modal_apply_mode_class">Entire Class (all sections, this semester)</label>
                        </div>
                        <small class="form-text text-muted">
                            "Entire Class" creates one copy of this assessment per active section of the chosen class
                            this semester, instead of repeating this form per section. Only available when adding a
                            new assessment &mdash; editing always applies to the one section it's already on.
                        </small>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6" id="modal_schedule_wrap">
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
                        <div class="form-group col-md-6" id="modal_class_wrap" style="display:none">
                            <label>Class <span class="text-danger">*</span></label>
                            <select name="class_id" id="modal_class_id" class="form-control">
                                <option value="">Select class...</option>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?= $c['class_id'] ?>">
                                        <?= htmlspecialchars($c['class_code']) ?> &mdash; <?= htmlspecialchars($c['class_name']) ?>
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
                            <small class="form-text text-muted">
                                Only affects grade weighting &mdash; independent of the submission interface below.
                                "Major Exam"/"Quiz" only trigger the legacy JSON-upload quiz if no Widget is selected.
                            </small>
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

                    <div id="modal_groupings_section_wrap">
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
                    </div>
                    <small class="form-text text-muted" id="modal_groupings_class_note" style="display:none">
                        Group Submission isn't available for "Entire Class" &mdash; grouping sets are per-section.
                        Create the assessment per-section instead if you need groups.
                    </small>

                    <div class="form-group mt-3">
                        <label>Widget (optional)</label>
                        <select name="widget_id" id="modal_widget_id" class="form-control">
                            <option value="">None &mdash; plain code/file submission</option>
                            <?php foreach ($widgets as $w): ?>
                                <option value="<?= $w['widget_id'] ?>" data-key="<?= htmlspecialchars($w['widget_key']) ?>"><?= htmlspecialchars($w['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">
                            Controls the actual submission interface, e.g. pick "Multiple Choice Quiz" here for an
                            auto-graded quiz regardless of Assessment Type above &mdash; no JSON file upload needed.
                        </small>
                    </div>
                    <div class="form-group" id="modal_given_wrap" style="display:none">
                        <label>Widget Config (JSON)</label>
                        <textarea name="given" id="modal_given" class="form-control" rows="6"></textarea>
                        <small class="form-text text-muted" id="modal_given_hint">
                            Select a widget above to see its example config.
                        </small>
                    </div>
                    <div class="form-group" id="modal_widget_preview_wrap" style="display:none">
                        <label>Preview <small class="text-muted">(how students will see it)</small></label>
                        <div id="modal_widget_preview" class="border rounded p-3 bg-light"></div>
                    </div>

                    <div class="form-check mt-3">
                        <input type="checkbox" name="auto_create_submissions" id="modal_auto_create_submissions" class="form-check-input" value="1">
                        <label class="form-check-label" for="modal_auto_create_submissions">
                            Participation: auto-create a blank submission for every enrolled student in the section
                        </label>
                        <small class="form-text text-muted">
                            For assessments where students don't submit anything (e.g. class participation) &mdash;
                            creates one ungraded slot per enrolled student on save, so you can score them directly
                            from All Submissions / the randomizer instead of waiting for uploads. Safe to check again
                            later (e.g. after new students enroll) &mdash; only missing students get a slot.
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

function toggleApplyMode() {
    const isClassMode = document.getElementById('modal_apply_mode_class').checked;

    document.getElementById('modal_schedule_wrap').style.display = isClassMode ? 'none' : '';
    document.getElementById('modal_class_wrap').style.display = isClassMode ? '' : 'none';
    document.getElementById('modal_schedule_id').required = !isClassMode;
    document.getElementById('modal_class_id').required = isClassMode;

    // Grouping sets are per-section, so Group Submission isn't offered when
    // creating across a whole class at once (server also forces it off).
    document.getElementById('modal_groupings_section_wrap').style.display = isClassMode ? 'none' : '';
    document.getElementById('modal_groupings_class_note').style.display = isClassMode ? '' : 'none';
    if (isClassMode) {
        document.getElementById('modal_is_groupings').checked = false;
        toggleGroupingSetWrap();
    }
}

// Example config JSON per widget_key — shown as the textarea's placeholder
// so the admin doesn't have to go dig through root/docs/paperless-midterm-plan.md
// every time. Keep in sync with each widgets/*.php view's expected $config shape.
const widgetExamples = {
    worksheet: {
        hint: 'Table-style form. "min_rows" pre-fills that many blank rows; "allow_add_rows" lets the student add more.',
        example: {
            columns: ['Technology', 'Problem Solved', 'Why It Succeeded'],
            min_rows: 3,
            allow_add_rows: true
        }
    },
    quiz: {
        hint: 'Auto-graded. Empty/omitted "choices" = free-text question (case-insensitive match).',
        example: {
            questions: [
                { question: '2 + 2 = ?', choices: ['3', '4', '5'], answer: '4' },
                { question: 'Capital of France?', choices: [], answer: 'Paris' }
            ]
        }
    },
    card_sort: {
        hint: '"require_justification" adds a text box per placed item.',
        example: {
            bins: ['Incremental', 'Disruptive'],
            items: ['Android OS', 'Netflix', 'ChatGPT', 'LED Bulbs'],
            require_justification: true
        }
    },
    diagram: {
        hint: 'Fixed sequence of labeled boxes — student fills in the text inside each one.',
        example: {
            nodes: ['Sense', 'Transmit', 'Store', 'Act']
        }
    },
    decision_matrix: {
        hint: 'Fixed rows; each column is typed ("text" or "select" with "options").',
        example: {
            rows: ['Smart irrigation', 'Fish tank monitor', 'Offshore fishing boat'],
            columns: [
                { name: 'Cost', type: 'text' },
                { name: 'Best Fit', type: 'select', options: ['WiFi', 'Bluetooth', 'LoRa', 'Cellular', 'Satellite'] }
            ]
        }
    },
    calculator: {
        hint: '"formula" can use +, -, *, /, parentheses and each input\'s "key" as a variable name.',
        example: {
            inputs: [
                { label: 'Equipment Cost (₱)', key: 'cost' },
                { label: 'Monthly Savings (₱)', key: 'savings' }
            ],
            formula: 'cost / savings',
            result_label: 'Months to Break Even'
        }
    },
    brainstorm: {
        hint: 'Shared class-wide board, not per-student — "max_votes_per_student" limits dot-voting.',
        example: {
            prompt: 'How could IS help Maria the farmer?',
            max_votes_per_student: 3
        }
    }
};

// Tracks the last example JSON we auto-filled into the textarea, so switching
// widgets can safely replace it — but real config (typed by hand, loaded from
// an existing assessment, or edited from the example) is never clobbered.
let lastAutoFilledExample = null;

function toggleGivenWrap() {
    const select = document.getElementById('modal_widget_id');
    document.getElementById('modal_given_wrap').style.display = select.value ? '' : 'none';

    const key = select.options[select.selectedIndex] ? select.options[select.selectedIndex].dataset.key : null;
    const info = key && widgetExamples[key] ? widgetExamples[key] : null;
    const textarea = document.getElementById('modal_given');
    const hint = document.getElementById('modal_given_hint');

    if (info) {
        const exampleJson = JSON.stringify(info.example, null, 2);
        if (!textarea.value.trim() || textarea.value === lastAutoFilledExample) {
            textarea.value = exampleJson;
            lastAutoFilledExample = exampleJson;
        }
        hint.textContent = info.hint + ' Edit the example below to fit your assessment.';
    } else {
        hint.textContent = 'Select a widget above to see its example config.';
    }

    fetchWidgetPreview();
}

// <script> tags inserted via innerHTML don't execute — re-create them so the
// widget's own interactivity (Add Row, live calculator, etc.) works in the preview.
function runScriptsIn(container) {
    container.querySelectorAll('script').forEach(oldScript => {
        const newScript = document.createElement('script');
        Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
        newScript.textContent = oldScript.textContent;
        oldScript.replaceWith(newScript);
    });
}

function fetchWidgetPreview() {
    const widgetId = document.getElementById('modal_widget_id').value;
    const wrap = document.getElementById('modal_widget_preview_wrap');
    const box = document.getElementById('modal_widget_preview');

    if (!widgetId) {
        wrap.style.display = 'none';
        box.innerHTML = '';
        return;
    }

    wrap.style.display = '';
    const given = document.getElementById('modal_given').value;

    fetch('<?= base_url('AdminController/preview_widget') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'widget_id=' + encodeURIComponent(widgetId) + '&given=' + encodeURIComponent(given)
    })
        .then(r => r.text())
        .then(html => {
            box.innerHTML = html;
            runScriptsIn(box);
        })
        .catch(() => {
            box.innerHTML = '<p class="text-danger mb-0">Preview failed to load.</p>';
        });
}

let widgetPreviewTimer = null;
function refreshWidgetPreviewDebounced() {
    clearTimeout(widgetPreviewTimer);
    widgetPreviewTimer = setTimeout(fetchWidgetPreview, 400);
}

document.getElementById('modal_schedule_id').addEventListener('change', () => refreshGroupingSetOptions());
document.getElementById('modal_is_groupings').addEventListener('change', toggleGroupingSetWrap);
document.getElementById('modal_widget_id').addEventListener('change', toggleGivenWrap);
document.getElementById('modal_given').addEventListener('input', refreshWidgetPreviewDebounced);
document.getElementById('modal_apply_mode_section').addEventListener('change', toggleApplyMode);
document.getElementById('modal_apply_mode_class').addEventListener('change', toggleApplyMode);

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Assessment';
    document.getElementById('modal_assessment_id').value = '';
    document.getElementById('modal_apply_mode_wrap').style.display = '';
    document.getElementById('modal_apply_mode_section').checked = true;
    document.getElementById('modal_schedule_id').value = '<?= $selected_schedule ?: '' ?>';
    document.getElementById('modal_class_id').value = '';
    toggleApplyMode();
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
    lastAutoFilledExample = null;
    toggleGivenWrap();
    document.getElementById('modal_auto_create_submissions').checked = false;
    document.getElementById('modal_submit_btn').textContent = 'Add Assessment';
    if (typeof $ !== 'undefined') $('#assessmentModal').modal('show');
}

function openEditModal(a) {
    document.getElementById('modalTitle').textContent = 'Edit Assessment';
    document.getElementById('modal_assessment_id').value = a.assessment_id;
    // Editing always applies to the one section the assessment is already on.
    document.getElementById('modal_apply_mode_wrap').style.display = 'none';
    document.getElementById('modal_apply_mode_section').checked = true;
    document.getElementById('modal_class_id').value = '';
    toggleApplyMode();
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
    lastAutoFilledExample = null;
    toggleGivenWrap();
    document.getElementById('modal_auto_create_submissions').checked = false;
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

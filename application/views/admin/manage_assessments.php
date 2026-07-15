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
            <button class="btn btn-outline-success" onclick="bulkUpdateStatus(1)">
                <i class="fas fa-lock-open"></i> Open All
            </button>
            <button class="btn btn-outline-secondary" onclick="bulkUpdateStatus(0)">
                <i class="fas fa-lock"></i> Close All
            </button>
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
                            <td>
                                <span class="badge badge-info"><?= $a['submission_count'] ?></span>
                                <?php $missing_count = max(0, (int) $a['enrolled_count'] - (int) $a['submitted_student_count']); ?>
                                <?php if ($missing_count > 0): ?>
                                    <span class="badge badge-danger" title="Enrolled students who haven't submitted"><?= $missing_count ?></span>
                                <?php endif; ?>
                                <?php if ((int) $a['unscored_count'] > 0): ?>
                                    <span class="badge badge-warning" title="Submitted but not yet scored"><?= (int) $a['unscored_count'] ?></span>
                                <?php endif; ?>
                            </td>
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
                                    <option value="<?= $s['schedule_id'] ?>" data-class-code="<?= htmlspecialchars($s['class_code']) ?>">
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
                                    <option value="<?= $c['class_id'] ?>" data-class-code="<?= htmlspecialchars($c['class_code']) ?>">
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
                            <small class="form-text text-muted" id="modal_max_score_hint" style="display:none">
                                Auto-set to the topic's question count (1 point each).
                            </small>
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
                    <div class="form-group" id="modal_iq_topic_wrap" style="display:none">
                        <label>Topic</label>
                        <select id="modal_iq_topic" class="form-control">
                            <option value="">Select a topic...</option>
                            <?php foreach ($iq_topics as $slug => $topic_title): ?>
                                <option value="<?= htmlspecialchars($slug) ?>" data-class-code="<?= htmlspecialchars($iq_topic_classes[$slug] ?? '') ?>"><?= htmlspecialchars($topic_title) ?> (<?= htmlspecialchars($slug) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">
                            Only lesson+quiz topics (uploaded under Interactive Quiz &rarr; Manage Topics) for the
                            section/class selected above are listed here (plus any legacy unfiled topics). Students
                            are redirected straight to this topic; their score is recorded on first completion only.
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

// topic slug -> question count, used to auto-fill Max Score for Interactive
// Discussion/Quiz (one point per question — see AdminController::save_assessment()
// for the server-side derivation this mirrors).
const iqTopicQuestionCounts = <?= json_encode($iq_topic_question_counts) ?>;

// topic slug -> class_code ('' for legacy/unfiled topics available to every
// class), used to filter the Topic dropdown to the section/class selected above.
const iqTopicClasses = <?= json_encode($iq_topic_classes) ?>;

// topic slug -> {title, description}, used to auto-fill the assessment's
// Title/Description fields from the topic JSON when a topic is picked.
const iqTopicMeta = <?= json_encode($iq_topic_meta) ?>;

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

// Class code of whichever section/class select currently governs this
// assessment (depends on Apply To mode), read off the selected option's
// data-class-code attribute.
function currentSelectedClassCode() {
    const isClassMode = document.getElementById('modal_apply_mode_class').checked;
    const select = document.getElementById(isClassMode ? 'modal_class_id' : 'modal_schedule_id');
    const opt = select.options[select.selectedIndex];
    return opt ? (opt.dataset.classCode || '') : '';
}

// Interactive Discussion/Quiz topics live under assets/json/{CLASS_CODE}/
// (see AdminController::_topic_class_code_from_path()), so only topics
// belonging to the section/class currently selected above (plus any legacy
// unfiled topics) make sense to offer. Uses "hidden" rather than "disabled"
// so a value can still be programmatically assigned (e.g. openEditModal()
// loading an existing assessment) even before filtering settles.
function refreshIqTopicOptions() {
    const select = document.getElementById('modal_iq_topic');
    const classCode = currentSelectedClassCode();
    let selectedStillVisible = !select.value;

    Array.from(select.options).forEach(opt => {
        if (!opt.value) return; // keep the placeholder
        const topicClass = iqTopicClasses[opt.value] || '';
        const visible = !classCode || !topicClass || topicClass === classCode;
        opt.hidden = !visible;
        if (opt.value === select.value && visible) selectedStillVisible = true;
    });

    if (!selectedStillVisible) {
        select.value = '';
        syncIqTopicToGiven();
    }
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
    refreshIqTopicOptions();
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
    },
    iq_discussion: {
        hint: 'Pick the topic from the dropdown below — not per-student either, students are redirected to the topic.',
        example: null
    },
    lab_worksheet: {
        hint: 'Fixed sequence of experiments (instructions + Predict/Observe/Explain prompts). Not auto-graded — score it manually like Worksheet Form.',
        example: {
            intro: '<p>Optional objectives/timeline HTML shown above the experiments.</p>',
            experiments: [
                {
                    title: 'Experiment 1.1 — Declare an array and print the first element',
                    instructions: '<p>Inside <code>main()</code>, type:</p><pre><code>int scores[5] = {85, 90, 78, 92, 88};\n\nprintf("%d\\n", scores[0]);</code></pre>',
                    warning: false,
                    prompts: [
                        { tag: 'predict', label: 'PREDICT', text: 'What number will print?' },
                        { tag: 'observe', label: 'OBSERVE', text: 'Compile. Run. What actually printed?' },
                        { tag: 'explain', label: 'EXPLAIN', text: 'Why does scores[0] give the first value?' }
                    ]
                },
                {
                    title: 'Experiment 1.2 — Go out of bounds',
                    instructions: '<p>Change the printf line to print <code>scores[5]</code>.</p>',
                    warning: true,
                    prompts: [
                        { tag: 'predict', label: 'PREDICT', text: 'Error, crash, or a number?' },
                        { tag: 'observe', label: 'OBSERVE', text: 'What actually happened?' },
                        { tag: 'explain', label: 'EXPLAIN', text: 'Why did C let you ask for a index that does not exist?' }
                    ],
                    note: 'Fix it back: change scores[5] back to scores[4] before continuing.'
                }
            ],
            exit_question: 'In one or two sentences: what surprised you the most today, and why?'
        }
    },
    case_study: {
        hint: 'Narrative story panel + fixed sections of heterogeneous questions (text/list/choice-with-rationale/toggle-grid) for case-study activities. Not auto-graded. This example is the full "Meet Maria" Session 1.2 worksheet — ready to use as-is, or adapt the story/sections for a different case study.',
        example: {
            story: {
                eyebrow: 'Session 1.2 · Field Notebook',
                title: "Innovation in Bohol: Maria's Calamansi Farm",
                intro: '<p>Maria grows calamansi on a small farm just outside Tagbilaran. Three things are working against her every season:</p>',
                stats: [
                    { label: 'NO FERTILIZER CREDIT', text: 'She has to pay full price upfront for fertilizer — or skip it and get a smaller harvest.' },
                    { label: "CAN'T PREDICT YIELD", text: 'No data on rainfall, pests, or demand — she plants the same amount every year and hopes.' },
                    { label: 'MIDDLEMEN TAKE ~70%', text: 'She sells at the farm gate to a consolidator, who resells in Tagbilaran and Cebu markets for far more.' }
                ]
            },
            sections: [
                {
                    label: 'Meet Maria',
                    timing: '3–15 min · Problem Intro',
                    questions: [
                        { type: 'text', badge: 'core', prompt: "In ONE sentence, state Maria's core problem — not a solution yet, just the problem.", rows: 2, placeholder: "Maria's problem is..." },
                        { type: 'list', badge: 'core', prompt: "Maria's situation is actually 3 separate problems bundled together. Name each one in a few words.", lines: 3, placeholders: ['1. ...', '2. ...', '3. ...'] },
                        {
                            type: 'choice', badge: 'core', prompt: 'Which of the 3 is hardest to solve with technology ALONE (no policy or lending changes)?',
                            options: [
                                { text: 'Fertilizer credit', note: "Credit is often a policy/finance problem before it's a tech problem — an app can't fix a bank's risk appetite." },
                                { text: 'Yield prediction', note: 'Actually the most solvable by tech alone — sensors, weather data, and simple forecasting apps directly attack this.' },
                                { text: 'Middlemen / market access', note: 'Partly tech (an app connecting farmers to buyers), but mostly about trust, logistics, and getting enough farmers to switch at once.' }
                            ]
                        }
                    ]
                },
                {
                    label: 'Innovation Ideation Mural',
                    timing: '15–40 min · Hands-On',
                    questions: [
                        { type: 'list', badge: 'core', prompt: 'Brainstorm (10 min). Write down every idea that could help Maria — quantity over judgment, no discussing yet.', lines: 6, placeholders: ['Idea 1', 'Idea 2', 'Idea 3', 'Idea 4', 'Idea 5', 'Idea 6'] },
                        { type: 'text', badge: 'core', prompt: 'Cluster (5 min). Sort your ideas into 2–3 categories — e.g. "Data/Prediction tools," "Financing tools," "Market access tools."', rows: 3, placeholder: 'Category A: ...\nCategory B: ...\nCategory C: ...' },
                        { type: 'list', badge: 'core', prompt: 'Vote (5 min). As a group, agree on your TOP 3 ideas, ranked.', lines: 3, placeholders: ['#1 (top pick)', '#2', '#3'] }
                    ]
                },
                {
                    label: 'Gallery Walk & Discussion',
                    timing: '40–55 min · Peer Feedback',
                    questions: [
                        { type: 'text', badge: 'core', prompt: 'For your #1 idea: roughly, what would it cost to build and run?', rows: 2, placeholder: 'Cost estimate + what drives that cost...' },
                        { type: 'text', badge: 'core', prompt: 'Who actually uses it day-to-day? Maria herself? Her buyer? A co-op officer?', rows: 2, placeholder: 'Name the specific person/role...' },
                        {
                            type: 'choice', badge: 'core', prompt: "For your top idea — what's the harder part?",
                            options: [
                                { text: 'Building the tech', note: "Fair — some of these ideas need sensors, connectivity, or apps that don't exist cheaply yet in rural Bohol." },
                                { text: 'Getting people to adopt it', note: 'This is the most common answer in real Philippine agri-tech cases — the tech usually exists, but getting farmers, buyers, and middlemen to actually change behavior is the hard part.' },
                                { text: 'Both, equally', note: "Also valid — and it's exactly what the Innovation Triangle (Week 2) is built to explain: tech, people, and business model all have to work together." }
                            ]
                        }
                    ]
                },
                {
                    label: 'Stress-Test Your Idea',
                    timing: 'Optional · Go Deeper',
                    questions: [
                        {
                            type: 'toggle_grid', badge: 'bonus', prompt: 'Tap each side of the triangle below to mark it a STRENGTH for your top idea. Leave the weak ones untapped.',
                            items: [
                                { title: 'TECH', text: 'Does the technology actually exist and work reliably?' },
                                { title: 'PEOPLE', text: 'Will Maria, the buyer, and the co-op actually use it?' },
                                { title: 'BUSINESS', text: 'Is there a way to pay for it that makes sense?' }
                            ]
                        },
                        { type: 'text', badge: 'bonus', prompt: 'MASIFAGCA is a real calamansi farmer group (Nueva Ecija) that faced almost this exact middleman problem. If you can look them up — what did they actually do?', rows: 2, placeholder: 'What you found...' },
                        { type: 'text', badge: 'bonus', prompt: 'Name one other Bohol industry (fishing, tourism, transport, weaving) with a similar "farmer\'s dilemma" — no data, no credit, middlemen. What would need to change?', rows: 2, placeholder: 'Industry + what would change...' }
                    ]
                },
                {
                    label: 'Reflection',
                    timing: '55–60 min · Wrap-Up',
                    questions: [
                        { type: 'text', badge: 'core', prompt: 'In one sentence — what makes something an innovation, not just an invention? Bring this answer to Session 2.1.', rows: 2, placeholder: 'Your answer here...' }
                    ]
                }
            ]
        }
    },
    case_dossier: {
        hint: 'Hook question + read-only framework explainer + multiple parallel case dossiers (each rated 1-5 per factor with a cited-evidence text field) + reflection questions. Not auto-graded. This example is the full Session 2.1 "Innovation Triangle" worksheet (GCash/Kodak/Friendster) — ready to use as-is.',
        example: {
            meta: {
                eyebrow: 'Session 2.1 · Field Notebook',
                title: 'Why Inventions Fail: The Innovation Triangle',
                sub: 'IS Innovations & New Technologies · Carmen Municipal College · Week 2'
            },
            hook: {
                label: 'The Best Widget Nobody Bought',
                timing: '0–5 min · Hook Question',
                intro: "<p>Imagine you invent the best widget ever. It's better than anything on the market. But almost nobody buys it.</p>",
                questions: [
                    { type: 'list', badge: 'core', prompt: 'Give 3 reasons the best widget ever could still fail to sell. (Don\'t just say "it broke" — think beyond the tech itself.)', lines: 3, placeholders: ['1. ...', '2. ...', '3. ...'] }
                ]
            },
            framework: {
                label: 'The Innovation Triangle',
                timing: '5–12 min · Mini-Lecture',
                intro: '<p>An innovation only succeeds if three things ALL line up:</p>',
                factors: [
                    { title: 'TECH', text: 'Does the technology actually work and solve the stated problem?' },
                    { title: 'PEOPLE', text: 'Do real users adopt it — is it trusted, accessible, and easy enough to use?' },
                    { title: 'BUSINESS', text: 'Is there a viable model to fund, distribute, and sustain it at scale?' }
                ],
                anchor: 'Tech alone is not enough.'
            },
            groups: [
                {
                    name: 'GCash',
                    accent: 'mango',
                    dossier: {
                        title: 'Case Dossier — GCash',
                        facts: [
                            "Launched October 2004 as an SMS-based money-transfer service — Globe Telecom's answer to Smart Padala, not a technological breakthrough.",
                            "As of Dec. 31, 2025: ~90 million registered users and 39.1 million monthly active users — nearly half the Philippines' adult population.",
                            '78% of active users are OUTSIDE Metro Manila, and 92% belong to lower-income segments — real financial inclusion, not just a Manila app.',
                            'Processed ₱17 trillion in gross transaction value in 2025 (56.7M transactions/day average); parent company Mynt filed in June 2026 for a ₱92.3-billion IPO — set to be the largest in Philippine stock market history.'
                        ],
                        source: 'Sources: Wikipedia "GCash"; BusinessWorld/Philstar/Inquirer, Mynt IPO filing coverage (June 2026).'
                    },
                    factors: [
                        { title: 'TECH', question: 'Did the technology work?' },
                        { title: 'PEOPLE', question: 'Did people actually adopt it?' },
                        { title: 'BUSINESS', question: 'Was there a model to profit & scale?' }
                    ]
                },
                {
                    name: 'Kodak',
                    accent: 'teal',
                    dossier: {
                        title: 'Case Dossier — Kodak',
                        facts: [
                            "Kodak engineer Steven Sasson invented the first digital camera in 1975 — inside Kodak's own labs.",
                            'Kodak shelved the digital camera to protect its dominant, highly profitable film business rather than bring it to market.',
                            'Despite inventing the core technology nearly two decades before digital cameras went mainstream, Kodak filed for bankruptcy in January 2012.'
                        ],
                        source: 'Sources: The Guardian, "Kodak\'s Digital Moment" (2012).'
                    },
                    factors: [
                        { title: 'TECH', question: 'Did the technology work?' },
                        { title: 'PEOPLE', question: 'Did people actually adopt it?' },
                        { title: 'BUSINESS', question: 'Was there a model to profit & scale?' }
                    ]
                },
                {
                    name: 'Friendster (Philippines)',
                    accent: 'purple',
                    dossier: {
                        title: 'Case Dossier — Friendster in the Philippines',
                        facts: [
                            "Launched March 2002 and became the Philippines' first mass social network, spread through internet cafés nationwide — by 2008, the Philippines accounted for 39% of ALL Friendster traffic worldwide, its single largest market anywhere.",
                            "In 2003, Friendster turned down a $30 million buyout offer from Google — later called one of Silicon Valley's biggest blunders.",
                            'Chronic server crashes and slow load times, worsened by the disproportionate volume of Philippine traffic, drove users away; Facebook overtook Friendster in the Philippines by 2009.',
                            'Friendster actually relaunched in the Philippines in April 2026 as a stripped-down, ad-free app — a nostalgia-driven comeback, 11 years after it shut down.'
                        ],
                        source: 'Sources: Wikipedia "Friendster"; GMA News Online; M2 Comms (2026).'
                    },
                    factors: [
                        { title: 'TECH', question: 'Did the technology work?' },
                        { title: 'PEOPLE', question: 'Did people actually adopt it?' },
                        { title: 'BUSINESS', question: 'Was there a model to profit & scale?' }
                    ]
                }
            ],
            reflection: {
                label: 'Reflection',
                timing: '40–60 min · Applied Work & Wrap-Up',
                questions: [
                    { type: 'text', badge: 'core', prompt: 'If Kodak had launched the digital camera in 1975, what business change would have had to happen alongside it? Write 2–3 sentences.', rows: 3, placeholder: 'Kodak would have had to...' },
                    {
                        type: 'choice', badge: 'core', prompt: 'Which corner of the Innovation Triangle did Kodak fail hardest on?',
                        options: [
                            { text: 'Tech', note: "Worth reconsidering — Kodak's technology was genuinely ahead of its time. Rating Tech as the failure confuses invention with commercialization." },
                            { text: 'People', note: "Defensible, but downstream — nobody adopted the digital camera because it was never released to them. That's a real gap, but it's a symptom of a deeper failure." },
                            { text: 'Business', note: 'The strongest answer. The core failure was a strategic business decision — protecting film profits and refusing to commercialize a threat to the existing model.' }
                        ]
                    },
                    { type: 'text', badge: 'bonus', prompt: 'Name one other company or product you know of that had great technology but still failed. What corner did it miss?', rows: 2, placeholder: 'Company/product + which corner it missed...' }
                ]
            }
        }
    }
};

// Tracks the last example JSON we auto-filled into the textarea, so switching
// widgets can safely replace it — but real config (typed by hand, loaded from
// an existing assessment, or edited from the example) is never clobbered.
let lastAutoFilledExample = null;

// Same "don't clobber typed content" tracking as lastAutoFilledExample, but
// for the Title/Description fields auto-filled from the topic JSON below.
let lastAutoFilledTitle = null;
let lastAutoFilledDescription = null;

// Interactive Discussion/Quiz doesn't take free-form JSON — it's driven by
// the topic <select> below, which writes {"topic": slug} into the (hidden)
// given textarea so save_assessment/preview_widget don't need special-casing.
function syncIqTopicToGiven() {
    const topic = document.getElementById('modal_iq_topic').value;
    document.getElementById('modal_given').value = topic ? JSON.stringify({ topic: topic }) : '';
    applyIqMaxScoreLock(true);
    autofillIqTopicMeta(topic);
    fetchWidgetPreview();
}

// Auto-fills Title/Description from the topic JSON's own "title"/"description"
// keys when a topic is picked — only overwrites a field that's still blank or
// holds our own previous auto-fill, so anything the admin actually typed is
// never clobbered.
function autofillIqTopicMeta(topic) {
    const titleInput = document.getElementById('modal_title');
    const descInput = document.getElementById('modal_description');
    const info = topic ? iqTopicMeta[topic] : null;

    if (!titleInput.value.trim() || titleInput.value === lastAutoFilledTitle) {
        titleInput.value = info ? info.title : '';
        lastAutoFilledTitle = titleInput.value;
    }
    if (!descInput.value.trim() || descInput.value === lastAutoFilledDescription) {
        descInput.value = info ? info.description : '';
        lastAutoFilledDescription = descInput.value;
    }
}

// Max Score isn't hand-entered for this widget — it's the topic's question
// count (1 point per question, matching the +1-per-correct-answer scoring in
// _interactive_quiz_template.php). Locked read-only here purely so the admin
// isn't misled into typing a value that save_assessment() will overwrite
// server-side anyway (see AdminController::save_assessment()).
function applyIqMaxScoreLock(isIqDiscussion) {
    const input = document.getElementById('modal_max_score');
    const hint = document.getElementById('modal_max_score_hint');
    input.readOnly = isIqDiscussion;
    hint.style.display = isIqDiscussion ? '' : 'none';
    if (isIqDiscussion) {
        const topic = document.getElementById('modal_iq_topic').value;
        input.value = topic && iqTopicQuestionCounts[topic] !== undefined ? iqTopicQuestionCounts[topic] : '';
    }
}

function toggleGivenWrap() {
    const select = document.getElementById('modal_widget_id');
    const key = select.options[select.selectedIndex] ? select.options[select.selectedIndex].dataset.key : null;
    const isIqDiscussion = key === 'iq_discussion';

    document.getElementById('modal_given_wrap').style.display = (select.value && !isIqDiscussion) ? '' : 'none';
    document.getElementById('modal_iq_topic_wrap').style.display = isIqDiscussion ? '' : 'none';
    applyIqMaxScoreLock(isIqDiscussion);

    if (isIqDiscussion) {
        fetchWidgetPreview();
        return;
    }

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

document.getElementById('modal_schedule_id').addEventListener('change', () => { refreshGroupingSetOptions(); refreshIqTopicOptions(); });
document.getElementById('modal_class_id').addEventListener('change', refreshIqTopicOptions);
document.getElementById('modal_is_groupings').addEventListener('change', toggleGroupingSetWrap);
document.getElementById('modal_widget_id').addEventListener('change', toggleGivenWrap);
document.getElementById('modal_given').addEventListener('input', refreshWidgetPreviewDebounced);
document.getElementById('modal_iq_topic').addEventListener('change', syncIqTopicToGiven);
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
    document.getElementById('modal_term').value = 'midterm';
    document.getElementById('modal_status').value = '0';
    document.getElementById('modal_due').value = '';
    document.getElementById('modal_is_groupings').checked = false;
    refreshGroupingSetOptions();
    toggleGroupingSetWrap();
    document.getElementById('modal_widget_id').value = '';
    document.getElementById('modal_given').value = '';
    document.getElementById('modal_iq_topic').value = '';
    lastAutoFilledExample = null;
    lastAutoFilledTitle = null;
    lastAutoFilledDescription = null;
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
    lastAutoFilledTitle = null;
    lastAutoFilledDescription = null;
    let givenTopic = '';
    if (a.given) {
        try { givenTopic = JSON.parse(a.given).topic || ''; } catch (e) {}
    }
    refreshIqTopicOptions();
    document.getElementById('modal_iq_topic').value = givenTopic;
    toggleGivenWrap();
    document.getElementById('modal_auto_create_submissions').checked = false;
    document.getElementById('modal_submit_btn').textContent = 'Update Assessment';
    if (typeof $ !== 'undefined') $('#assessmentModal').modal('show');
}

// Applies to every assessment currently shown in the table (i.e. respecting
// the Section filter above), not the whole database.
function bulkUpdateStatus(status) {
    const selects = Array.from(document.querySelectorAll('select[data-id]'));
    const targets = selects.filter(s => s.value !== String(status));
    if (!targets.length) return;

    const label = status === 1 ? 'Open' : 'Close';
    if (!confirm(`${label} all ${selects.length} assessment(s) shown?`)) return;

    const ids = targets.map(s => s.dataset.id);
    const body = new URLSearchParams();
    ids.forEach(id => body.append('assessment_ids[]', id));
    body.append('status', status);

    fetch('<?= base_url('AdminController/bulk_update_assessment_status') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString()
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            targets.forEach(s => s.value = String(status));
        } else {
            alert('Failed to update status.');
        }
    })
    .catch(() => alert('Request failed.'));
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

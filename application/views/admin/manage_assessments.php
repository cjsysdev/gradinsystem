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
            <button class="btn btn-outline-primary" data-toggle="modal" data-target="#assignModal" onclick="openAssignModal()">
                <i class="fas fa-link"></i> Assign to Section
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

    <?php $has_filters = ($search_q !== '' || $selected_iotype || $selected_term || $selected_submission || ($selected_status !== '')); ?>
    <form method="get" action="<?= base_url('manage_assessments') ?>" class="form-row align-items-end mt-3 mb-3">
        <div class="form-group col-sm-3 mb-2">
            <label class="small mb-1">Section</label>
            <select name="schedule_id" class="form-control form-control-sm">
                <option value="">All Sections</option>
                <?php foreach ($schedules as $s): ?>
                    <option value="<?= $s['schedule_id'] ?>" <?= (string)$selected_schedule === (string)$s['schedule_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['section']) ?> &mdash; <?= htmlspecialchars($s['class_code']) ?> (<?= $s['type'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group col-sm-2 mb-2">
            <label class="small mb-1">Submissions</label>
            <select name="submission" class="form-control form-control-sm">
                <option value="">Any</option>
                <option value="none"     <?= $selected_submission === 'none'     ? 'selected' : '' ?>>No submissions</option>
                <option value="has"      <?= $selected_submission === 'has'      ? 'selected' : '' ?>>Has submissions</option>
                <option value="unscored" <?= $selected_submission === 'unscored' ? 'selected' : '' ?>>Has unscored</option>
                <option value="missing"  <?= $selected_submission === 'missing'  ? 'selected' : '' ?>>Has missing submitters</option>
            </select>
        </div>
        <div class="form-group col-sm-2 mb-2">
            <label class="small mb-1">Type</label>
            <select name="iotype_id" class="form-control form-control-sm">
                <option value="">All types</option>
                <?php foreach ($io_types as $t): ?>
                    <option value="<?= $t['iotype_id'] ?>" <?= (string)$selected_iotype === (string)$t['iotype_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['type']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group col-sm-2 mb-2">
            <label class="small mb-1">Term</label>
            <select name="term" class="form-control form-control-sm">
                <option value="">All terms</option>
                <option value="midterm"         <?= $selected_term === 'midterm'         ? 'selected' : '' ?>>Midterm</option>
                <option value="tentative-final" <?= $selected_term === 'tentative-final' ? 'selected' : '' ?>>Tentative Final</option>
                <option value="final"           <?= $selected_term === 'final'           ? 'selected' : '' ?>>Final</option>
            </select>
        </div>
        <div class="form-group col-sm-1 mb-2">
            <label class="small mb-1">Status</label>
            <select name="status" class="form-control form-control-sm">
                <option value="">All</option>
                <option value="1" <?= $selected_status === '1' ? 'selected' : '' ?>>Open</option>
                <option value="0" <?= $selected_status === '0' ? 'selected' : '' ?>>Closed</option>
            </select>
        </div>
        <div class="form-group col-sm-2 mb-2">
            <label class="small mb-1">Search</label>
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Title or section" value="<?= htmlspecialchars($search_q) ?>">
        </div>
        <div class="form-group col-sm-auto mb-2">
            <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-filter"></i> Filter</button>
            <?php if ($has_filters): ?>
                <a href="<?= base_url('manage_assessments') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
            <?php endif; ?>
        </div>
    </form>

    <?php if ($total > 0): ?>
        <p class="text-muted mb-2">
            Showing <?= $offset + 1 ?>–<?= min($offset + $per_page, $total) ?> of <?= $total ?> assessment<?= $total != 1 ? 's' : '' ?>
        </p>
    <?php endif; ?>

    <style>
        /* Rows sharing one assessment across multiple sections (see
           get_all_for_admin()'s sibling_count/rowspan grouping) get a faint
           tint so the group reads as one entry at a glance. */
        tr.table-shared-group { background-color: rgba(0, 123, 255, 0.035); }
    </style>
    <div class="table-responsive">
        <table class="table table-bordered table-hover table-sm">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Section</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Widget</th>
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
                        <?php $is_group_head = isset($a['_rowspan']); ?>
                        <tr class="<?= (int) $a['sibling_count'] > 1 ? 'table-shared-group' : '' ?>">
                            <td><?= $a['assessment_id'] ?></td>
                            <td><span class="badge badge-secondary"><?= htmlspecialchars($a['section']) ?></span></td>
                            <?php if ($is_group_head): ?>
                                <td rowspan="<?= (int) $a['_rowspan'] ?>">
                                    <?= htmlspecialchars($a['title']) ?>
                                    <?php if ((int) $a['sibling_count'] > 1): ?>
                                        <br><small class="text-muted" title="This assessment is shared across these sections — editing content here applies to all of them.">
                                            <i class="fas fa-link"></i> Shared: <?= htmlspecialchars($a['sibling_sections_csv']) ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td rowspan="<?= (int) $a['_rowspan'] ?>"><?= htmlspecialchars($a['iotype']) ?></td>
                                <td rowspan="<?= (int) $a['_rowspan'] ?>">
                                    <?php if (!empty($a['widget_name'])): ?>
                                        <span class="badge badge-primary"><?= htmlspecialchars($a['widget_name']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td rowspan="<?= (int) $a['_rowspan'] ?>">
                                    <?php
                                    $termLabels = ['midterm' => 'Midterm', 'tentative-final' => 'Tentative Final', 'final' => 'Final'];
                                    echo $termLabels[$a['term']] ?? $a['term'];
                                    ?>
                                </td>
                                <td rowspan="<?= (int) $a['_rowspan'] ?>"><?= $a['max_score'] ?></td>
                            <?php endif; ?>
                            <td><?= date('M j, y, D', strtotime($a['due'])) ?></td>
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
                                <?php if (!empty($a['class_id'])): ?>
                                    <a href="<?= base_url('class_assessments?class_id=' . (int) $a['class_id']) ?>"
                                       class="btn btn-sm btn-outline-secondary"
                                       title="View all assessments for this class">
                                        <i class="fas fa-layer-group"></i>
                                    </a>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-outline-danger"
                                        onclick="deleteAssessment(<?= (int) $a['assessment_id'] ?>, <?= htmlspecialchars(json_encode($a['title']), ENT_QUOTES) ?>)"
                                        title="Delete">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11" class="text-center text-muted">No assessments found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pagination): ?>
        <nav aria-label="Page navigation" class="mb-5">
            <?= $pagination ?>
        </nav>
    <?php endif; ?>
</div>

<!-- Add / Edit Modal -->
<div class="modal fade" id="assessmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" action="<?= base_url('save_assessment') ?>" id="assessmentForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Assessment</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="assessment_id" id="modal_assessment_id">
                    <input type="hidden" name="schedule_id_filter" value="<?= $selected_schedule ?>">

                    <div class="form-group" id="modal_copy_from_wrap">
                        <label>Copy from existing assessment <small class="text-muted">(optional)</small></label>
                        <select id="modal_copy_from" class="form-control">
                            <option value="">Start blank &mdash; don't copy</option>
                            <?php foreach ($copyable_assessments as $ca): ?>
                                <option value="<?= $ca['assessment_id'] ?>" data-class-code="<?= htmlspecialchars($ca['class_code']) ?>">
                                    <?= htmlspecialchars($ca['section']) ?> &mdash; <?= htmlspecialchars($ca['title']) ?> (<?= htmlspecialchars($ca['class_code']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="form-text text-muted">
                            Pre-fills the form from another assessment (filtered to the selected section's class),
                            so you can drop an existing assessment onto a different section. Only the target
                            section, status, and grouping set stay yours to choose.
                        </small>
                    </div>

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
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="iq_source" id="modal_iq_source_existing" value="existing" checked>
                            <label class="form-check-label" for="modal_iq_source_existing">Reuse existing topic</label>
                        </div>
                        <div class="form-check form-check-inline mb-2">
                            <input class="form-check-input" type="radio" name="iq_source" id="modal_iq_source_new" value="new">
                            <label class="form-check-label" for="modal_iq_source_new">Paste new JSON</label>
                        </div>

                        <div id="modal_iq_existing_wrap">
                            <select id="modal_iq_topic" class="form-control">
                                <option value="">Select a topic...</option>
                                <?php foreach ($iq_topics as $slug => $topic_title): ?>
                                    <option value="<?= htmlspecialchars($slug) ?>" data-class-code="<?= htmlspecialchars($iq_topic_classes[$slug] ?? '') ?>"><?= htmlspecialchars($topic_title) ?> (<?= htmlspecialchars($slug) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted">
                                Only topics (uploaded under Interactive Quiz &rarr; Manage Topics) matching the selected
                                widget's format &mdash; lesson+quiz for Interactive Discussion/Quiz, chunks+micro-checks
                                for Microlearning Quiz &mdash; and the section/class selected above are listed here
                                (plus any legacy unfiled topics). Students are redirected straight to this topic; their
                                score is recorded on first completion only.
                            </small>
                        </div>

                        <div id="modal_iq_new_wrap" style="display:none">
                            <input type="text" name="iq_new_slug" id="modal_iq_new_slug" class="form-control mb-2" maxlength="100"
                                   pattern="[a-z0-9_]+" placeholder="new_topic_slug (lowercase letters, digits, underscores)">
                            <button type="button" class="btn btn-sm btn-outline-secondary mb-1"
                                    onclick="document.getElementById('modal_iq_new_json_file').click()">
                                <i class="fas fa-file-import"></i> Load from .json file
                            </button>
                            <input type="file" id="modal_iq_new_json_file" accept=".json,application/json" class="d-none">
                            <textarea name="iq_new_json" id="modal_iq_new_json" class="form-control" rows="10"
                                      placeholder='{"title": "...", "sections": [...]}'></textarea>
                            <small class="form-text text-muted">
                                Saved as a new file under <code>assets/json/{class code}/{slug}.json</code> so it
                                becomes a reusable topic like any other &mdash; the slug must not already exist
                                anywhere in the topic library. Use the microlearning format (sections with
                                <code>chunks</code>) for Microlearning Quiz, or plain lesson+quiz sections for
                                Interactive Discussion/Quiz.
                            </small>
                        </div>
                    </div>
                    <div class="form-group" id="modal_given_wrap" style="display:none">
                        <label>Widget Config</label>
                        <ul class="nav nav-tabs mb-2" id="widget_config_tabs">
                            <li class="nav-item" id="widget_tab_builder_li">
                                <a class="nav-link active" href="#" id="widget_tab_builder"
                                   onclick="switchWidgetConfigTab('builder'); return false;">
                                    <i class="fas fa-sliders-h"></i> Visual Builder
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#" id="widget_tab_raw"
                                   onclick="switchWidgetConfigTab('raw'); return false;">
                                    <i class="fas fa-code"></i> Raw JSON
                                </a>
                            </li>
                        </ul>
                        <div class="alert alert-warning py-2 px-3 small" id="modal_builder_warning" style="display:none"></div>
                        <div id="widget_builder_pane">
                            <div id="modal_builder_pane"></div>
                        </div>
                        <div id="widget_raw_pane" style="display:none">
                            <button type="button" class="btn btn-sm btn-outline-secondary mb-1"
                                    onclick="document.getElementById('modal_given_file').click()">
                                <i class="fas fa-file-import"></i> Load from .json file
                            </button>
                            <input type="file" id="modal_given_file" accept=".json,application/json" class="d-none">
                            <textarea name="given" id="modal_given" class="form-control" rows="6"></textarea>
                            <small class="form-text text-muted" id="modal_given_hint">
                                Select a widget above to see its example config.
                            </small>
                        </div>
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

<!-- Assign to Section Modal -->
<div class="modal fade" id="assignModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="<?= base_url('AdminController/assign_master') ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Existing Assessment to a Section</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small">
                        Attaches an assessment that already exists to ANOTHER section, sharing its content
                        (title/description/max score/widget config) &mdash; editing it later updates every section
                        it's assigned to. For a similar-but-separate assessment, use "Copy from existing assessment"
                        in the Add Assessment form instead.
                    </p>
                    <div class="form-group">
                        <label>Assessment <span class="text-danger">*</span></label>
                        <select id="assign_master_id" class="form-control" required>
                            <option value="">Select assessment...</option>
                            <?php foreach ($assignable_masters as $m): ?>
                                <option value="<?= (int) $m['master_id'] ?>"
                                        data-class-id="<?= (int) $m['class_id'] ?>"
                                        data-assigned="<?= htmlspecialchars($m['assigned_schedule_ids']) ?>">
                                    <?= htmlspecialchars($m['class_code']) ?> &mdash; <?= htmlspecialchars($m['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Target Section <span class="text-danger">*</span></label>
                        <select name="schedule_id" id="assign_schedule_id" class="form-control" required disabled>
                            <option value="">Select an assessment first...</option>
                        </select>
                        <small class="form-text text-muted">Only sections of the same class, not already assigned, are shown.</small>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Status</label>
                            <select name="status" id="assign_status" class="form-control">
                                <option value="1">Open</option>
                                <option value="0" selected>Closed</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Due Date &amp; Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="due" id="assign_due" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_groupings" id="assign_is_groupings" class="form-check-input" value="1">
                        <label class="form-check-label" for="assign_is_groupings">Group Submission</label>
                    </div>
                    <div class="form-group mt-2" id="assign_grouping_set_wrap" style="display:none">
                        <label>Grouping Set</label>
                        <select name="grouping_set_id" id="assign_grouping_set_id" class="form-control">
                            <option value="">Select grouping set...</option>
                        </select>
                    </div>
                    <div class="form-check mt-2">
                        <input type="checkbox" name="auto_create_submissions" id="assign_auto_create_submissions" class="form-check-input" value="1">
                        <label class="form-check-label" for="assign_auto_create_submissions">
                            Participation: auto-create a blank submission for every enrolled student in the section
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Visual widget config builder (schema-driven form over the #modal_given textarea) -->
<script src="<?= base_url('assets/js/widget-schemas.js') ?>"></script>
<script src="<?= base_url('assets/js/widget-builder.js') ?>"></script>
<!-- Widget config example JSON per widget_key — shared with class_assessments.php -->
<script src="<?= base_url('assets/js/widget-examples.js') ?>"></script>

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

// topic slug -> 'discussion' | 'micro'. The two topic-file widgets share one
// Topic dropdown, so it's also filtered to the format the selected widget's
// renderer can handle (save_assessment() rejects a mismatch server-side too).
const iqTopicFormats = <?= json_encode($iq_topic_formats) ?>;
const WIDGET_TOPIC_FORMATS = { iq_discussion: 'discussion', iq_micro: 'micro' };

function selectedWidgetKey() {
    const select = document.getElementById('modal_widget_id');
    const opt = select.options[select.selectedIndex];
    return opt ? (opt.dataset.key || '') : '';
}

// '' when the selected widget isn't one of the topic-file widgets.
function selectedTopicFormat() {
    return WIDGET_TOPIC_FORMATS[selectedWidgetKey()] || '';
}

// topic slug -> {title, description}, used to auto-fill the assessment's
// Title/Description fields from the topic JSON when a topic is picked.
const iqTopicMeta = <?= json_encode($iq_topic_meta) ?>;

// assessment_id -> assessment fields, used by the "Copy from existing
// assessment" picker to pre-fill the Add modal from another assessment.
const copyableAssessments = <?= json_encode(array_column($copyable_assessments, null, 'assessment_id')) ?>;

// Every active section, used by the Assign modal to populate the target
// Section dropdown once a master is picked (filtered to that master's class
// and to sections it isn't already assigned to).
const allSchedules = <?= json_encode(array_map(function ($s) {
    return [
        'schedule_id' => (int) $s['schedule_id'],
        'section'     => $s['section'],
        'class_id'    => (int) $s['class_id'],
        'class_code'  => $s['class_code'],
        'type'        => $s['type'],
    ];
}, $schedules)) ?>;

// master_id -> {class_id, assigned: [schedule_id, ...]}, used by the Assign
// modal to filter the Section dropdown to the picked master's class, minus
// sections it's already on.
const assignableMasters = {};
<?php foreach ($assignable_masters as $m): ?>
    assignableMasters[<?= (int) $m['master_id'] ?>] = {
        class_id: <?= (int) $m['class_id'] ?>,
        assigned: <?= json_encode(array_map('intval', explode(',', $m['assigned_schedule_ids']))) ?>
    };
<?php endforeach; ?>

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
    const wantFormat = selectedTopicFormat();
    let selectedStillVisible = !select.value;

    Array.from(select.options).forEach(opt => {
        if (!opt.value) return; // keep the placeholder
        const topicClass = iqTopicClasses[opt.value] || '';
        const topicFormat = iqTopicFormats[opt.value] || 'discussion';
        const visible = (!classCode || !topicClass || topicClass === classCode)
            && (!wantFormat || topicFormat === wantFormat);
        opt.hidden = !visible;
        if (opt.value === select.value && visible) selectedStillVisible = true;
    });

    if (!selectedStillVisible) {
        select.value = '';
        syncIqTopicToGiven();
    }
}

// Same class-filtering approach as refreshIqTopicOptions(), but for the "Copy
// from existing assessment" picker — only offer assessments belonging to the
// section/class currently selected above.
function refreshCopyFromOptions() {
    const select = document.getElementById('modal_copy_from');
    const classCode = currentSelectedClassCode();
    let selectedStillVisible = !select.value;

    Array.from(select.options).forEach(opt => {
        if (!opt.value) return; // keep the placeholder
        const optClass = opt.dataset.classCode || '';
        const visible = !classCode || !optClass || optClass === classCode;
        opt.hidden = !visible;
        if (opt.value === select.value && visible) selectedStillVisible = true;
    });

    if (!selectedStillVisible) {
        select.value = '';
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
    refreshCopyFromOptions();
}

// Tracks the last example JSON we auto-filled into the textarea, so switching
// widgets can safely replace it — but real config (typed by hand, loaded from
// an existing assessment, or edited from the example) is never clobbered.
let lastAutoFilledExample = null;

// Same "don't clobber typed content" tracking as lastAutoFilledExample, but
// for the Title/Description fields auto-filled from the topic JSON below.
let lastAutoFilledTitle = null;
let lastAutoFilledDescription = null;
let lastAutoFilledSlug = null;
let lastAutoFilledMaxScore = null;

// Turns a pasted topic's "title" into a slug candidate matching the server's
// ^[a-z0-9_]{1,100}$ requirement (_save_pasted_topic_json()).
function slugifyTopicTitle(title) {
    return (title || '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '_')
        .replace(/^_+|_+$/g, '')
        .substring(0, 100);
}

// The topic-file widgets (Interactive Discussion/Quiz, Microlearning Quiz)
// don't take free-form JSON — they're driven either by the topic <select>
// below (writes {"topic": slug} into the hidden given textarea, as always)
// or, when "Paste new JSON" is picked, by iq_new_slug/iq_new_json — the
// server writes the file and derives "given" itself on save
// (AdminController::_resolve_iq_paste()), so there's nothing to post here yet.
function syncIqTopicToGiven() {
    if (document.getElementById('modal_iq_source_new').checked) {
        document.getElementById('modal_given').value = '';
        applyIqMaxScoreLock(true);
        fetchWidgetPreview();
        return;
    }
    const topic = document.getElementById('modal_iq_topic').value;
    document.getElementById('modal_given').value = topic ? JSON.stringify({ topic: topic }) : '';
    applyIqMaxScoreLock(true);
    autofillIqTopicMeta(topic);
    fetchWidgetPreview();
}

// Shows the matching Reuse/Paste sub-wrap and re-derives given/Max Score for
// the newly-active source. Called on radio change and from toggleGivenWrap().
function toggleIqSource() {
    const isNew = document.getElementById('modal_iq_source_new').checked;
    document.getElementById('modal_iq_existing_wrap').style.display = isNew ? 'none' : '';
    document.getElementById('modal_iq_new_wrap').style.display = isNew ? '' : 'none';
    syncIqTopicToGiven();
}

// Client-side mirror of AdminController::_count_iq_topic_questions() /
// _count_micro_topic_items() — only used to keep the (locked) Max Score field
// non-blank for a not-yet-saved pasted topic; save_assessment() recomputes
// the real value server-side once the file exists.
function countIqItemsFromJson(text, format) {
    let data;
    try { data = JSON.parse(text); } catch (e) { return 1; }
    const sections = (data && Array.isArray(data.sections)) ? data.sections : [];
    let count = 0;
    sections.forEach(s => {
        if (format === 'micro') {
            count += Array.isArray(s.chunks) ? s.chunks.length : 0;
            if (s.quiz) count++;
        } else if (s.quiz) {
            count++;
        }
    });
    return count > 0 ? count : 1;
}

// Same "don't clobber typed content" autofill as autofillIqTopicMeta(), but
// reading the pasted JSON's own title/description directly instead of the
// topic-library lookup (the file doesn't exist server-side yet to look up).
// Also proposes a slug from the title — still just a starting point, the
// admin can freely overwrite it before saving.
function autofillIqMetaFromJson(text) {
    let data;
    try { data = JSON.parse(text); } catch (e) { data = null; }
    const titleInput = document.getElementById('modal_title');
    const descInput = document.getElementById('modal_description');
    const slugInput = document.getElementById('modal_iq_new_slug');
    if (!titleInput.value.trim() || titleInput.value === lastAutoFilledTitle) {
        titleInput.value = (data && data.title) ? data.title : '';
        lastAutoFilledTitle = titleInput.value;
    }
    if (!descInput.value.trim() || descInput.value === lastAutoFilledDescription) {
        descInput.value = (data && data.description) ? data.description : '';
        lastAutoFilledDescription = descInput.value;
    }
    if (!slugInput.value.trim() || slugInput.value === lastAutoFilledSlug) {
        slugInput.value = (data && data.title) ? slugifyTopicTitle(data.title) : '';
        lastAutoFilledSlug = slugInput.value;
    }
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

// Reads one of the given key paths ('a.b' walks into nested objects) out of a
// parsed config, flattened to plain text — client-side twin of the $pick()
// closure in AdminController::_widget_config_meta().
function pickConfigMeta(data, paths) {
    for (const path of paths) {
        let value = data;
        let found = true;
        for (const key of path.split('.')) {
            if (!value || typeof value !== 'object' || value[key] === undefined) {
                found = false;
                break;
            }
            value = value[key];
        }
        if (!found || (typeof value !== 'string' && typeof value !== 'number')) continue;

        const text = String(value).replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
        if (text) return text;
    }
    return '';
}

// Fills Title/Description/Max Score from a widget config JSON that names
// itself — configs written by the generator skills carry their own title,
// description, and (for quiz-shaped ones) score, so there's nothing to retype.
// Mirrors AdminController::_widget_config_meta() + _fill_blank_fields(), which
// does the same on save for configs that never passed through this modal.
// Same "don't clobber typed content" guard as autofillIqMetaFromJson(), except
// a key the config doesn't carry leaves its field alone instead of blanking it
// — this runs on every keystroke in the textarea, where the JSON is usually
// half-typed.
function autofillMetaFromWidgetConfig(text) {
    let data;
    try { data = JSON.parse(text); } catch (e) { return; }
    if (!data || typeof data !== 'object') return;

    const title = pickConfigMeta(data, ['title', 'meta.title', 'story.title']).substring(0, 64);
    const description = pickConfigMeta(data, ['description', 'subtitle', 'meta.sub', 'prompt']);
    const maxScore = parseInt(pickConfigMeta(data, ['max_score', 'total_points', 'points']), 10);

    const titleInput = document.getElementById('modal_title');
    const descInput = document.getElementById('modal_description');
    const scoreInput = document.getElementById('modal_max_score');

    if (title && (!titleInput.value.trim() || titleInput.value === lastAutoFilledTitle)) {
        titleInput.value = title;
        lastAutoFilledTitle = title;
    }
    if (description && (!descInput.value.trim() || descInput.value === lastAutoFilledDescription)) {
        descInput.value = description;
        lastAutoFilledDescription = description;
    }
    // Never touches Max Score for the topic-file widgets — applyIqMaxScoreLock()
    // owns that field there (read-only, derived from the topic's item count).
    if (maxScore > 0 && !selectedTopicFormat()
        && (!scoreInput.value.trim() || scoreInput.value === lastAutoFilledMaxScore)) {
        scoreInput.value = maxScore;
        lastAutoFilledMaxScore = String(maxScore);
    }
}

// Max Score isn't hand-entered for the topic-file widgets — it's the topic's
// own item count (1 point per question for Interactive Discussion/Quiz, 1 per
// micro-check + 1 per checkpoint for Microlearning Quiz, matching the
// +1-per-correct-answer scoring in each template). Locked read-only here purely
// so the admin isn't misled into typing a value that save_assessment() will
// overwrite server-side anyway (see AdminController::save_assessment()).
function applyIqMaxScoreLock(isTopicWidget) {
    const input = document.getElementById('modal_max_score');
    const hint = document.getElementById('modal_max_score_hint');
    input.readOnly = isTopicWidget;
    hint.style.display = isTopicWidget ? '' : 'none';
    if (!isTopicWidget) return;

    if (document.getElementById('modal_iq_source_new').checked) {
        input.value = countIqItemsFromJson(document.getElementById('modal_iq_new_json').value, selectedTopicFormat());
        return;
    }
    const topic = document.getElementById('modal_iq_topic').value;
    input.value = topic && iqTopicQuestionCounts[topic] !== undefined ? iqTopicQuestionCounts[topic] : '';
}

function toggleGivenWrap() {
    const select = document.getElementById('modal_widget_id');
    const isTopicWidget = !!selectedTopicFormat();

    document.getElementById('modal_given_wrap').style.display = (select.value && !isTopicWidget) ? '' : 'none';
    document.getElementById('modal_iq_topic_wrap').style.display = isTopicWidget ? '' : 'none';
    // The two topic widgets read different topic formats, so re-filter the
    // shared dropdown (and drop a now-invalid selection) on every switch.
    refreshIqTopicOptions();

    if (isTopicWidget) {
        // Shows the right Reuse/Paste sub-wrap and derives given + Max Score
        // + preview for whichever source is active.
        toggleIqSource();
        return;
    }
    applyIqMaxScoreLock(false);

    const key = selectedWidgetKey();

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

    // Show/hide the Visual Builder tab based on whether this widget has a schema
    // (widget-schemas.js) and, when it does, render the builder from the current
    // config JSON. Falls back to raw-JSON-only for widgets without a schema.
    if (typeof initWidgetConfigUI === 'function') initWidgetConfigUI();

    autofillMetaFromWidgetConfig(textarea.value);
    fetchWidgetPreview();
}

// Pre-fills the Add modal's content fields from another assessment (picked
// via the "Copy from existing assessment" select), so an assessment created
// for one section can be dropped onto a different section without re-typing
// title/type/description/widget/config by hand. Target section, status, and
// grouping set are deliberately NOT copied — grouping sets are per-section
// (see refreshGroupingSetOptions()) and status defaults fresh like any new row.
function applyCopyFrom() {
    const src = copyableAssessments[document.getElementById('modal_copy_from').value];
    if (!src) return;

    document.getElementById('modal_iotype_id').value = src.iotype_id || '';
    document.getElementById('modal_title').value = src.title || '';
    document.getElementById('modal_description').value = src.description || '';
    document.getElementById('modal_max_score').value = src.max_score || '';
    document.getElementById('modal_term').value = src.term || 'midterm';
    document.getElementById('modal_due').value = src.due ? src.due.replace(' ', 'T').substring(0, 16) : '';
    document.getElementById('modal_is_groupings').checked = parseInt(src.is_groupings) === 1;
    refreshGroupingSetOptions();
    toggleGroupingSetWrap();

    document.getElementById('modal_widget_id').value = src.widget_id || '';
    document.getElementById('modal_given').value = src.given || '';
    lastAutoFilledExample = null;
    lastAutoFilledTitle = null;
    lastAutoFilledDescription = null;
    lastAutoFilledSlug = null;
    lastAutoFilledMaxScore = null;

    let givenTopic = '';
    if (src.given) {
        try { givenTopic = JSON.parse(src.given).topic || ''; } catch (e) {}
    }
    // The copied-from assessment already points at a saved topic file — reuse it.
    document.getElementById('modal_iq_source_existing').checked = true;
    document.getElementById('modal_iq_new_slug').value = '';
    document.getElementById('modal_iq_new_json').value = '';
    refreshIqTopicOptions();
    document.getElementById('modal_iq_topic').value = givenTopic;
    toggleGivenWrap();
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

    // Nothing to preview for a not-yet-saved pasted topic — the given JSON
    // the preview endpoint needs is only derived once the file exists on save.
    if (selectedTopicFormat() && document.getElementById('modal_iq_source_new').checked) {
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

function openAssignModal() {
    document.getElementById('assign_master_id').value = '';
    document.getElementById('assign_schedule_id').innerHTML = '<option value="">Select an assessment first...</option>';
    document.getElementById('assign_schedule_id').disabled = true;
    document.getElementById('assign_status').value = '0';
    document.getElementById('assign_due').value = '';
    document.getElementById('assign_is_groupings').checked = false;
    toggleAssignGroupingSetWrap();
    document.getElementById('assign_auto_create_submissions').checked = false;
    if (typeof $ !== 'undefined') $('#assignModal').modal('show');
}

// Populates the Target Section dropdown once a master is picked — only
// sections belonging to the same class, and not already assigned to this
// master (see assignableMasters, built from get_assignable_masters()).
function refreshAssignSections() {
    const masterId = document.getElementById('assign_master_id').value;
    const select = document.getElementById('assign_schedule_id');
    const info = assignableMasters[masterId];

    select.innerHTML = '';
    if (!info) {
        select.innerHTML = '<option value="">Select an assessment first...</option>';
        select.disabled = true;
        return;
    }

    const candidates = allSchedules.filter(s => s.class_id === info.class_id && info.assigned.indexOf(s.schedule_id) === -1);
    if (!candidates.length) {
        select.innerHTML = '<option value="">No unassigned sections left for this class</option>';
        select.disabled = true;
        return;
    }

    select.disabled = false;
    select.innerHTML = '<option value="">Select section...</option>' + candidates.map(s =>
        `<option value="${s.schedule_id}">${s.section} — ${s.class_code} (${s.type})</option>`
    ).join('');
    refreshAssignGroupingSetOptions();
}

function refreshAssignGroupingSetOptions() {
    const scheduleId = document.getElementById('assign_schedule_id').value;
    const section = scheduleSections[scheduleId];
    const select = document.getElementById('assign_grouping_set_id');
    const sets = (section && setsBySection[section]) || [];

    select.innerHTML = '<option value="">Select grouping set...</option>';
    sets.forEach(s => {
        const opt = document.createElement('option');
        opt.value = s.set_id;
        opt.textContent = s.name;
        select.appendChild(opt);
    });
}

function toggleAssignGroupingSetWrap() {
    document.getElementById('assign_grouping_set_wrap').style.display =
        document.getElementById('assign_is_groupings').checked ? '' : 'none';
}

document.getElementById('assign_master_id').addEventListener('change', refreshAssignSections);
document.getElementById('assign_schedule_id').addEventListener('change', refreshAssignGroupingSetOptions);
document.getElementById('assign_is_groupings').addEventListener('change', toggleAssignGroupingSetWrap);

// "Load from .json file" — reads a local file into the config textarea, so
// big configs can be authored in an editor but still live in the DB (given
// column is the standard store; files are only for the shared topic library).
document.getElementById('modal_given_file').addEventListener('change', function () {
    const file = this.files[0];
    this.value = ''; // so picking the same file again still fires 'change'
    if (!file) return;

    const reader = new FileReader();
    reader.onload = () => {
        let parsed;
        try {
            parsed = JSON.parse(reader.result);
        } catch (e) {
            alert('That file is not valid JSON: ' + e.message);
            return;
        }
        const textarea = document.getElementById('modal_given');
        textarea.value = JSON.stringify(parsed, null, 2);
        lastAutoFilledExample = null; // real config now — switching widgets must not clobber it
        autofillMetaFromWidgetConfig(textarea.value);
        fetchWidgetPreview();
    };
    reader.readAsText(file);
});

// Same "load a local file into the textarea" helper, for the "Paste new
// JSON" topic-widget sub-wrap.
document.getElementById('modal_iq_new_json_file').addEventListener('change', function () {
    const file = this.files[0];
    this.value = '';
    if (!file) return;

    const reader = new FileReader();
    reader.onload = () => {
        let parsed;
        try {
            parsed = JSON.parse(reader.result);
        } catch (e) {
            alert('That file is not valid JSON: ' + e.message);
            return;
        }
        const textarea = document.getElementById('modal_iq_new_json');
        textarea.value = JSON.stringify(parsed, null, 2);
        applyIqMaxScoreLock(true);
        autofillIqMetaFromJson(textarea.value);
    };
    reader.readAsText(file);
});

document.getElementById('modal_iq_new_json').addEventListener('input', function () {
    applyIqMaxScoreLock(true);
    autofillIqMetaFromJson(this.value);
});

// Client-side twin of save_assessment()'s given-JSON check — blocks the
// submit up front so the typed config isn't lost to a server-side redirect.
document.getElementById('assessmentForm').addEventListener('submit', function (e) {
    const select = document.getElementById('modal_widget_id');
    if (!select.value) return;

    const topicFormat = selectedTopicFormat();
    if (topicFormat) {
        // Reusing an existing topic has no hand-written config to check —
        // syncIqTopicToGiven() already wrote {"topic": slug} for it.
        if (!document.getElementById('modal_iq_source_new').checked) return;

        const slug = document.getElementById('modal_iq_new_slug').value.trim();
        const raw = document.getElementById('modal_iq_new_json').value.trim();
        let problem = '';
        if (!/^[a-z0-9_]{1,100}$/.test(slug)) {
            problem = 'the slug is required and may only contain lowercase letters, digits, and underscores';
        } else if (!raw) {
            problem = 'the pasted JSON is empty';
        } else {
            try {
                const parsed = JSON.parse(raw);
                if (typeof parsed !== 'object' || parsed === null || !Array.isArray(parsed.sections) || !parsed.sections.length) {
                    problem = 'the pasted JSON must be an object with a non-empty "sections" array';
                }
            } catch (err) {
                problem = 'the pasted JSON is not valid JSON — ' + err.message;
            }
        }
        if (problem) {
            e.preventDefault();
            alert('New topic not saved: ' + problem + '.');
        }
        return;
    }

    const raw = document.getElementById('modal_given').value.trim();
    let problem = '';
    if (!raw) {
        problem = 'the config is empty';
    } else {
        try {
            const parsed = JSON.parse(raw);
            if (typeof parsed !== 'object' || parsed === null) problem = 'the config must be a JSON object';
        } catch (err) {
            problem = 'the config is not valid JSON — ' + err.message;
        }
    }
    if (problem) {
        e.preventDefault();
        alert('Widget config not saved: ' + problem + '.');
    }
});

document.getElementById('modal_schedule_id').addEventListener('change', () => { refreshGroupingSetOptions(); refreshIqTopicOptions(); refreshCopyFromOptions(); });
document.getElementById('modal_class_id').addEventListener('change', () => { refreshIqTopicOptions(); refreshCopyFromOptions(); });
document.getElementById('modal_is_groupings').addEventListener('change', toggleGroupingSetWrap);
document.getElementById('modal_widget_id').addEventListener('change', toggleGivenWrap);
document.getElementById('modal_given').addEventListener('input', function () {
    autofillMetaFromWidgetConfig(this.value);
    refreshWidgetPreviewDebounced();
});
document.getElementById('modal_iq_topic').addEventListener('change', syncIqTopicToGiven);
document.getElementById('modal_iq_source_existing').addEventListener('change', toggleIqSource);
document.getElementById('modal_iq_source_new').addEventListener('change', toggleIqSource);
document.getElementById('modal_apply_mode_section').addEventListener('change', toggleApplyMode);
document.getElementById('modal_apply_mode_class').addEventListener('change', toggleApplyMode);
document.getElementById('modal_copy_from').addEventListener('change', applyCopyFrom);

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Assessment';
    document.getElementById('modal_assessment_id').value = '';
    document.getElementById('modal_copy_from_wrap').style.display = '';
    document.getElementById('modal_copy_from').value = '';
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
    document.getElementById('modal_iq_source_existing').checked = true;
    document.getElementById('modal_iq_new_slug').value = '';
    document.getElementById('modal_iq_new_json').value = '';
    lastAutoFilledExample = null;
    lastAutoFilledTitle = null;
    lastAutoFilledDescription = null;
    lastAutoFilledSlug = null;
    lastAutoFilledMaxScore = null;
    toggleGivenWrap();
    document.getElementById('modal_auto_create_submissions').checked = false;
    document.getElementById('modal_submit_btn').textContent = 'Add Assessment';
    if (typeof $ !== 'undefined') $('#assessmentModal').modal('show');
}

function openEditModal(a) {
    document.getElementById('modalTitle').textContent = 'Edit Assessment';
    document.getElementById('modal_assessment_id').value = a.assessment_id;
    // Copy-from is an add-only helper; editing always applies to the one
    // section the assessment is already on.
    document.getElementById('modal_copy_from_wrap').style.display = 'none';
    document.getElementById('modal_copy_from').value = '';
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
    lastAutoFilledSlug = null;
    lastAutoFilledMaxScore = null;
    let givenTopic = '';
    if (a.given) {
        try { givenTopic = JSON.parse(a.given).topic || ''; } catch (e) {}
    }
    // An assessment being edited already points at a saved topic file — reuse it.
    document.getElementById('modal_iq_source_existing').checked = true;
    document.getElementById('modal_iq_new_slug').value = '';
    document.getElementById('modal_iq_new_json').value = '';
    refreshIqTopicOptions();
    document.getElementById('modal_iq_topic').value = givenTopic;
    toggleGivenWrap();
    document.getElementById('modal_auto_create_submissions').checked = false;
    document.getElementById('modal_submit_btn').textContent = 'Update Assessment';
    if (typeof $ !== 'undefined') $('#assessmentModal').modal('show');
}

// Applies to every assessment matching the current Section filter, across
// every page (not just the ones currently rendered in the table).
const allAssessmentIds = <?= json_encode(array_values($all_assessment_ids)) ?>;

function bulkUpdateStatus(status) {
    if (!allAssessmentIds.length) return;

    const label = status === 1 ? 'Open' : 'Close';
    if (!confirm(`${label} all ${allAssessmentIds.length} assessment(s) matching this filter?`)) return;

    const body = new URLSearchParams();
    allAssessmentIds.forEach(id => body.append('assessment_ids[]', id));
    body.append('status', status);

    fetch('<?= base_url('AdminController/bulk_update_assessment_status') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString()
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to update status.');
        }
    })
    .catch(() => alert('Request failed.'));
}

// Delete button on each row. Always confirms once up front; the server then
// checks for existing student submissions itself (see
// AdminController::delete_assessment()) rather than trusting the
// submission_count already rendered in this row, which can be stale by the
// time the admin clicks. If submissions are found, deletion is refused
// outright — submitted classwork must be removed/reassigned first, there is
// no force/cascade option.
function deleteAssessment(id, title) {
    if (!confirm(`Delete assessment "${title}"? This cannot be undone.`)) return;

    fetch('<?= base_url('AdminController/delete_assessment/') ?>' + id, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: ''
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else if (data.blocked) {
            const n = data.submission_count;
            alert(`Cannot delete "${title}": ${n} student submission(s) already exist for this assessment. Remove the submissions first if you need to delete it.`);
        } else {
            alert(data.error || 'Failed to delete assessment.');
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

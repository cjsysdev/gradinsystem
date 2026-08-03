<?php $this->load->view('header'); ?>

<div class="container">
    <div class="dashboard">
        <?php $this->load->view('profile_only'); ?>
        <?php $this->load->view('admin/nav_bar'); ?>
    </div>

    <div class="row mt-3 align-items-center">
        <div class="col">
            <h4>Class Assessments</h4>
            <p class="text-muted small mb-0">
                Every assessment for a class, including drafts not yet assigned to any section.
                For per-section due dates/status, or assessments not yet linked to a class, use
                <a href="<?= base_url('manage_assessments') ?>">Assessments</a> instead.
            </p>
        </div>
        <?php if ($class_id): ?>
            <div class="col-auto">
                <button class="btn btn-primary" data-toggle="modal" data-target="#assessmentModal" onclick="openAddModal()">
                    <i class="fas fa-plus"></i> Add Assessment
                </button>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success mt-2"><?= $this->session->flashdata('success') ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger mt-2"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>

    <form method="get" action="<?= base_url('class_assessments') ?>" class="form-row align-items-end mt-3 mb-3">
        <div class="form-group col-sm-4 mb-2">
            <label class="small mb-1">Class</label>
            <select name="class_id" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">Select a class...</option>
                <?php foreach ($all_classes as $c): ?>
                    <option value="<?= $c['class_id'] ?>" <?= (string)$class_id === (string)$c['class_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['class_code']) ?> &mdash; <?= htmlspecialchars($c['class_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group col-sm-4 mb-2">
            <label class="small mb-1">Semester</label>
            <select name="semester_id" class="form-control form-control-sm" onchange="this.form.submit()">
                <?php foreach ($semesters as $sem): ?>
                    <option value="<?= $sem['trans_no'] ?>" <?= (string)$selected_semester_id === (string)$sem['trans_no'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sem['description']) ?><?= $sem['is_active'] ? ' (active)' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <!-- <small class="form-text text-muted">Which semester's sections count as "assigned" below. Drafts always show.</small> -->
        </div>
    </form>

    <?php if (!$class_id): ?>
        <p class="text-muted">Pick a class above to see its assessments.</p>
    <?php elseif (!$selected_class): ?>
        <div class="alert alert-warning">That class could not be found.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm">
                <thead class="thead-light">
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Widget</th>
                        <th>Term</th>
                        <th>Max Score</th>
                        <!-- <th>Status</th> -->
                        <th>Sections</th>
                        <th>Submissions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($assessments)): ?>
                        <?php foreach ($assessments as $a): ?>
                            <?php
                                $section_pairs = [];
                                if (!empty($a['sections_csv'])) {
                                    foreach (explode('|', $a['sections_csv']) as $pair) {
                                        [$sid, $scode] = array_pad(explode(':', $pair, 2), 2, null);
                                        if ($sid !== null) $section_pairs[] = ['id' => (int) $sid, 'code' => $scode];
                                    }
                                }
                                $assigned_ids = array_column($section_pairs, 'id');
                                $is_draft = (int) $a['section_count'] === 0;
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($a['title']) ?></td>
                                <td>
                                    <?php
                                    $iotypeBadges = [1 => 'badge-info', 2 => 'badge-primary', 3 => 'badge-danger', 4 => 'badge-warning'];
                                    $iotypeBadge = $iotypeBadges[(int) $a['iotype_id']] ?? 'badge-secondary';
                                    ?>
                                    <span class="badge <?= $iotypeBadge ?>"><?= htmlspecialchars($a['iotype'] ?? '') ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($a['widget_name'])): ?>
                                        <?php $widgetHue = abs(crc32($a['widget_name'])) % 360; ?>
                                        <span class="badge text-white" style="background-color: hsl(<?= $widgetHue ?>, 60%, 40%);"><?= htmlspecialchars($a['widget_name']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">&mdash;</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $termLabels = ['midterm' => 'Midterm', 'tentative-final' => 'Tentative Final', 'final' => 'Final'];
                                    echo $termLabels[$a['term']] ?? $a['term'];
                                    ?>
                                </td>
                                <td><?= $a['max_score'] ?></td>
                                <!-- <td>
                                    <?php if ($is_draft): ?>
                                        <span class="badge badge-warning">Unassigned draft</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Assigned to <?= (int) $a['section_count'] ?> section<?= (int) $a['section_count'] != 1 ? 's' : '' ?></span>
                                    <?php endif; ?>
                                </td> -->
                                <td>
                                    <?php if (!$section_pairs): ?>
                                        <span class="text-muted">&mdash;</span>
                                    <?php else: ?>
                                        <?php foreach ($section_pairs as $sp): ?>
                                            <?php $sectionHue = abs(crc32($sp['code'])) % 360; ?>
                                            <a href="<?= base_url('manage_assessments?schedule_id=' . $sp['id']) ?>"
                                               class="badge badge-secondary text-white" title="Manage this section's due date/status in Assessments">
                                                <?= htmlspecialchars($sp['code']) ?>
                                            </a>
                                            <a href="<?= base_url('all_submissions/' . $sp['id']) ?>" title="View submissions">
                                                <i class="fas fa-list text-muted"></i>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge badge-info"><?= (int) $a['submission_count'] ?></span>
                                    <?php if ((int) $a['unscored_count'] > 0): ?>
                                        <span class="badge badge-warning" title="Submitted but not yet scored"><?= (int) $a['unscored_count'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-nowrap">
                                    <button class="btn btn-sm btn-outline-primary"
                                            data-toggle="modal"
                                            data-target="#assessmentModal"
                                            onclick='openEditModal(<?= htmlspecialchars(json_encode($a), ENT_QUOTES) ?>)'
                                            title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary"
                                            data-toggle="modal"
                                            data-target="#assignModal"
                                            onclick='openAssignModal(<?= (int) $a["master_id"] ?>, <?= htmlspecialchars(json_encode($a["title"]), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($assigned_ids), ENT_QUOTES) ?>)'
                                            title="Assign to a section">
                                        <i class="fas fa-link"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger"
                                            onclick="deleteClassAssessment(<?= (int) $a['master_id'] ?>, <?= htmlspecialchars(json_encode($a['title']), ENT_QUOTES) ?>)"
                                            title="Delete">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">No assessments for this class yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <form method="post" action="<?= base_url('AdminController/backfill_assessment_class_id') ?>" class="mt-2">
            <input type="hidden" name="class_id" value="<?= (int) $class_id ?>">
            <button type="submit" class="btn btn-link btn-sm text-muted p-0">
                Missing an older assessment here? Backfill class links for assessments created before this page existed.
            </button>
        </form>
    <?php endif; ?>
</div>

<?php if ($class_id && $selected_class): ?>
<!-- Add / Edit Modal -->
<div class="modal fade" id="assessmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post" id="assessmentForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Assessment</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="master_id" id="modal_master_id">
                    <input type="hidden" name="class_id" value="<?= (int) $class_id ?>">
                    <input type="hidden" name="semester_id" value="<?= (int) $selected_semester_id ?>">
                    <input type="hidden" name="return_class_id" value="<?= (int) $class_id ?>">

                    <div class="form-group" id="modal_copy_from_wrap">
                        <label>Copy from existing assessment <small class="text-muted">(optional, this class only)</small></label>
                        <select id="modal_copy_from" class="form-control">
                            <option value="">Start blank &mdash; don't copy</option>
                            <?php foreach ($copyable_assessments as $ca): ?>
                                <option value="<?= $ca['assessment_id'] ?>">
                                    <?= htmlspecialchars($ca['section']) ?> &mdash; <?= htmlspecialchars($ca['title']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group" id="modal_apply_mode_wrap">
                        <label>Apply To</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="apply_mode" id="modal_apply_mode_draft" value="draft" checked>
                            <label class="form-check-label" for="modal_apply_mode_draft">Draft (no section yet)</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="apply_mode" id="modal_apply_mode_section" value="section">
                            <label class="form-check-label" for="modal_apply_mode_section">One Section</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="apply_mode" id="modal_apply_mode_class" value="class">
                            <label class="form-check-label" for="modal_apply_mode_class">Entire Class (all sections this semester)</label>
                        </div>
                        <?php
                            $selected_semester_label = '';
                            foreach ($semesters as $sem) {
                                if ((string) $sem['trans_no'] === (string) $selected_semester_id) {
                                    $selected_semester_label = $sem['description'];
                                    break;
                                }
                            }
                        ?>
                        <small class="form-text text-muted">
                            "Draft" saves the assessment for <?= htmlspecialchars($selected_class['class_code']) ?> without assigning it
                            to any section yet — assign it later with the <i class="fas fa-link"></i> button. "One Section" and "Entire
                            Class" both act within <strong><?= htmlspecialchars($selected_semester_label) ?></strong> (the semester
                            selected above). Assigning is always available regardless of which mode you start with.
                        </small>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6" id="modal_schedule_wrap" style="display:none">
                            <label>Section <span class="text-danger">*</span></label>
                            <select name="schedule_id" id="modal_schedule_id" class="form-control">
                                <option value="">Select section...</option>
                                <?php foreach ($sections as $s): ?>
                                    <option value="<?= $s['schedule_id'] ?>">
                                        <?= htmlspecialchars($s['section']) ?> (<?= htmlspecialchars($s['type']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($sections)): ?>
                                <small class="form-text text-danger">No sections for this class in that semester.</small>
                            <?php endif; ?>
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
                        <div class="form-group col-md-3" id="modal_status_wrap" style="display:none">
                            <label>Status</label>
                            <select name="status" id="modal_status" class="form-control">
                                <option value="1">Open</option>
                                <option value="0">Closed</option>
                            </select>
                        </div>
                        <div class="form-group col-md-3" id="modal_due_wrap" style="display:none">
                            <label>Due Date &amp; Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" name="due" id="modal_due" class="form-control">
                        </div>
                    </div>

                    <div id="modal_groupings_section_wrap" style="display:none">
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
                    </small>
                    <small class="form-text text-muted" id="modal_groupings_draft_note" style="display:none">
                        Group Submission is set when you assign this draft to a section.
                    </small>

                    <div class="form-group mt-3">
                        <label>Widget (optional)</label>
                        <select name="widget_id" id="modal_widget_id" class="form-control">
                            <option value="">None &mdash; plain code/file submission</option>
                            <?php foreach ($widgets as $w): ?>
                                <option value="<?= $w['widget_id'] ?>" data-key="<?= htmlspecialchars($w['widget_key']) ?>"><?= htmlspecialchars($w['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
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
                                    <option value="<?= htmlspecialchars($slug) ?>"><?= htmlspecialchars($topic_title) ?> (<?= htmlspecialchars($slug) ?>)</option>
                                <?php endforeach; ?>
                            </select>
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
                                Saved as a new file under <code>assets/json/<?= htmlspecialchars($selected_class['class_code'] ?? '') ?>/{slug}.json</code>
                                so it becomes a reusable topic like any other &mdash; the slug must not already exist
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

                    <div class="form-check mt-3" id="modal_auto_create_wrap" style="display:none">
                        <input type="checkbox" name="auto_create_submissions" id="modal_auto_create_submissions" class="form-check-input" value="1">
                        <label class="form-check-label" for="modal_auto_create_submissions">
                            Participation: auto-create a blank submission for every enrolled student in the section
                        </label>
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
                    <h5 class="modal-title">Assign &ldquo;<span id="assign_master_title"></span>&rdquo; to a Section</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="master_id" id="assign_master_id">
                    <input type="hidden" name="return_class_id" value="<?= (int) $class_id ?>">
                    <div class="form-group">
                        <label>Target Section <span class="text-danger">*</span></label>
                        <select name="schedule_id" id="assign_schedule_id" class="form-control" required>
                            <option value="">Select section...</option>
                        </select>
                        <small class="form-text text-muted">Only <?= htmlspecialchars($selected_class['class_code']) ?> sections not already assigned to this assessment are shown.</small>
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
<script src="<?= base_url('assets/js/widget-examples.js') ?>"></script>

<script>
const SAVE_ASSESSMENT_URL = <?= json_encode(base_url('save_assessment')) ?>;
const UPDATE_MASTER_URL = <?= json_encode(base_url('AdminController/update_class_assessment_master')) ?>;

// This class's active sections only — schedule_id -> section code, used to
// filter the grouping-set dropdown to the section picked in either modal.
const scheduleSections = {
    <?php foreach ($sections as $s): ?>
        <?= (int) $s['schedule_id'] ?>: <?= json_encode($s['section']) ?>,
    <?php endforeach; ?>
};
const allSections = <?= json_encode(array_map(function ($s) {
    return ['schedule_id' => (int) $s['schedule_id'], 'section' => $s['section'], 'type' => $s['type']];
}, $sections)) ?>;

const iqTopicQuestionCounts = <?= json_encode($iq_topic_question_counts) ?>;
const iqTopicMeta = <?= json_encode($iq_topic_meta) ?>;

// topic slug -> 'discussion' | 'micro'. The two topic-file widgets share one
// Topic dropdown, so it's filtered to the format the selected widget's renderer
// can handle (save_master_content() rejects a mismatch server-side too).
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

// Hides topics the selected widget can't render, and drops the current pick
// when it becomes invalid. "hidden" rather than "disabled" so openEditModal()
// can still assign a value before filtering settles.
function refreshIqTopicOptions() {
    const select = document.getElementById('modal_iq_topic');
    const wantFormat = selectedTopicFormat();
    let selectedStillVisible = !select.value;

    Array.from(select.options).forEach(opt => {
        if (!opt.value) return; // keep the placeholder
        const visible = !wantFormat || (iqTopicFormats[opt.value] || 'discussion') === wantFormat;
        opt.hidden = !visible;
        if (opt.value === select.value && visible) selectedStillVisible = true;
    });

    if (!selectedStillVisible) {
        select.value = '';
        syncIqTopicToGiven();
    }
}
const copyableAssessments = <?= json_encode(array_column($copyable_assessments, null, 'assessment_id')) ?>;

// section code -> [{set_id, name}]
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
    const mode = document.querySelector('input[name="apply_mode"]:checked').value;
    const isDraft = mode === 'draft';
    const isSection = mode === 'section';
    const isClass = mode === 'class';

    document.getElementById('modal_schedule_wrap').style.display = isSection ? '' : 'none';
    document.getElementById('modal_schedule_id').required = isSection;

    document.getElementById('modal_due_wrap').style.display = isDraft ? 'none' : '';
    document.getElementById('modal_due').required = !isDraft;
    document.getElementById('modal_status_wrap').style.display = isDraft ? 'none' : '';

    document.getElementById('modal_groupings_section_wrap').style.display = isSection ? '' : 'none';
    document.getElementById('modal_groupings_class_note').style.display = isClass ? '' : 'none';
    document.getElementById('modal_groupings_draft_note').style.display = isDraft ? '' : 'none';
    if (!isSection) {
        document.getElementById('modal_is_groupings').checked = false;
        toggleGroupingSetWrap();
    }

    document.getElementById('modal_auto_create_wrap').style.display = isDraft ? 'none' : '';
}

function applyIqMaxScoreLock(isIqDiscussion) { // true for either topic-file widget
    const input = document.getElementById('modal_max_score');
    const hint = document.getElementById('modal_max_score_hint');
    input.readOnly = isIqDiscussion;
    hint.style.display = isIqDiscussion ? '' : 'none';
    if (!isIqDiscussion) return;

    if (document.getElementById('modal_iq_source_new').checked) {
        input.value = countIqItemsFromJson(document.getElementById('modal_iq_new_json').value, selectedTopicFormat());
        return;
    }
    const topic = document.getElementById('modal_iq_topic').value;
    input.value = topic && iqTopicQuestionCounts[topic] !== undefined ? iqTopicQuestionCounts[topic] : '';
}

let lastAutoFilledExample = null;
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
// non-blank for a not-yet-saved pasted topic; save runs the real derivation
// server-side once the file exists.
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

// Twin of manage_assessments.php's config-meta autofill (see the fuller
// comments there) and of AdminController::_widget_config_meta(): a widget
// config JSON that names itself fills the Title/Description/Max Score fields
// the admin left blank, and only those.
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
    // Max Score belongs to applyIqMaxScoreLock() for the topic-file widgets.
    if (maxScore > 0 && !selectedTopicFormat()
        && (!scoreInput.value.trim() || scoreInput.value === lastAutoFilledMaxScore)) {
        scoreInput.value = maxScore;
        lastAutoFilledMaxScore = String(maxScore);
    }
}

function toggleGivenWrap() {
    const select = document.getElementById('modal_widget_id');
    const key = selectedWidgetKey();
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

    if (typeof initWidgetConfigUI === 'function') initWidgetConfigUI();
    autofillMetaFromWidgetConfig(textarea.value);
    fetchWidgetPreview();
}

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
    document.getElementById('modal_iq_topic').value = givenTopic;
    toggleGivenWrap();
}

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

document.getElementById('modal_given_file').addEventListener('change', function () {
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
        const textarea = document.getElementById('modal_given');
        textarea.value = JSON.stringify(parsed, null, 2);
        lastAutoFilledExample = null;
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

document.getElementById('modal_schedule_id').addEventListener('change', refreshGroupingSetOptions);
document.getElementById('modal_is_groupings').addEventListener('change', toggleGroupingSetWrap);
document.getElementById('modal_widget_id').addEventListener('change', toggleGivenWrap);
document.getElementById('modal_given').addEventListener('input', function () {
    autofillMetaFromWidgetConfig(this.value);
    refreshWidgetPreviewDebounced();
});
document.getElementById('modal_iq_topic').addEventListener('change', syncIqTopicToGiven);
document.getElementById('modal_iq_source_existing').addEventListener('change', toggleIqSource);
document.getElementById('modal_iq_source_new').addEventListener('change', toggleIqSource);
document.getElementById('modal_apply_mode_draft').addEventListener('change', toggleApplyMode);
document.getElementById('modal_apply_mode_section').addEventListener('change', toggleApplyMode);
document.getElementById('modal_apply_mode_class').addEventListener('change', toggleApplyMode);
document.getElementById('modal_copy_from').addEventListener('change', applyCopyFrom);

function resetModalCommon() {
    document.getElementById('modal_iotype_id').value = '';
    document.getElementById('modal_title').value = '';
    document.getElementById('modal_description').value = '';
    document.getElementById('modal_max_score').value = '';
    document.getElementById('modal_term').value = 'midterm';
    document.getElementById('modal_status').value = '0';
    document.getElementById('modal_due').value = '';
    document.getElementById('modal_is_groupings').checked = false;
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
}

function openAddModal() {
    document.getElementById('assessmentForm').action = SAVE_ASSESSMENT_URL;
    document.getElementById('modalTitle').textContent = 'Add Assessment';
    document.getElementById('modal_master_id').value = '';
    document.getElementById('modal_copy_from_wrap').style.display = '';
    document.getElementById('modal_copy_from').value = '';
    document.getElementById('modal_apply_mode_wrap').style.display = '';
    document.getElementById('modal_apply_mode_draft').checked = true;
    document.getElementById('modal_schedule_id').value = '';
    toggleApplyMode();
    resetModalCommon();
    document.getElementById('modal_auto_create_submissions').checked = false;
    document.getElementById('modal_submit_btn').textContent = 'Add Assessment';
    if (typeof $ !== 'undefined') $('#assessmentModal').modal('show');
}

function openEditModal(a) {
    document.getElementById('assessmentForm').action = UPDATE_MASTER_URL;
    document.getElementById('modalTitle').textContent = 'Edit Assessment';
    document.getElementById('modal_master_id').value = a.master_id;
    document.getElementById('modal_copy_from_wrap').style.display = 'none';
    document.getElementById('modal_apply_mode_wrap').style.display = 'none';
    document.getElementById('modal_schedule_wrap').style.display = 'none';
    document.getElementById('modal_schedule_id').required = false;
    document.getElementById('modal_due_wrap').style.display = 'none';
    document.getElementById('modal_due').required = false;
    document.getElementById('modal_status_wrap').style.display = 'none';
    document.getElementById('modal_groupings_section_wrap').style.display = 'none';
    document.getElementById('modal_groupings_class_note').style.display = 'none';
    document.getElementById('modal_groupings_draft_note').style.display = 'none';
    document.getElementById('modal_auto_create_wrap').style.display = 'none';

    document.getElementById('modal_iotype_id').value = a.iotype_id;
    document.getElementById('modal_title').value = a.title;
    document.getElementById('modal_description').value = a.description || '';
    document.getElementById('modal_max_score').value = a.max_score;
    document.getElementById('modal_term').value = a.term;
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
    document.getElementById('modal_iq_topic').value = givenTopic;
    toggleGivenWrap();
    document.getElementById('modal_submit_btn').textContent = 'Update Assessment';
    if (typeof $ !== 'undefined') $('#assessmentModal').modal('show');
}

function toggleAssignGroupingSetWrap() {
    document.getElementById('assign_grouping_set_wrap').style.display =
        document.getElementById('assign_is_groupings').checked ? '' : 'none';
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

function openAssignModal(masterId, title, assignedIds) {
    document.getElementById('assign_master_id').value = masterId;
    document.getElementById('assign_master_title').textContent = title;

    const select = document.getElementById('assign_schedule_id');
    const candidates = allSections.filter(s => assignedIds.indexOf(s.schedule_id) === -1);
    if (!candidates.length) {
        select.innerHTML = '<option value="">No unassigned sections left</option>';
    } else {
        select.innerHTML = '<option value="">Select section...</option>' + candidates.map(s =>
            `<option value="${s.schedule_id}">${s.section} (${s.type})</option>`
        ).join('');
    }

    document.getElementById('assign_status').value = '0';
    document.getElementById('assign_due').value = '';
    document.getElementById('assign_is_groupings').checked = false;
    toggleAssignGroupingSetWrap();
    document.getElementById('assign_auto_create_submissions').checked = false;
    if (typeof $ !== 'undefined') $('#assignModal').modal('show');
}

document.getElementById('assign_schedule_id').addEventListener('change', refreshAssignGroupingSetOptions);
document.getElementById('assign_is_groupings').addEventListener('change', toggleAssignGroupingSetWrap);

// Delete button — same two-step force-confirm pattern as manage_assessments.php,
// against delete_class_assessment() (removes the whole master across every
// section it's on) instead of delete_assessment() (one section only).
function deleteClassAssessment(masterId, title) {
    if (!confirm(`Delete assessment "${title}"? This removes it from every section it's assigned to. This cannot be undone.`)) return;
    sendDeleteClassAssessment(masterId, false);
}

function sendDeleteClassAssessment(masterId, force) {
    const body = new URLSearchParams();
    if (force) body.append('force', '1');

    fetch('<?= base_url('AdminController/delete_class_assessment/') ?>' + masterId, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString()
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else if (data.blocked) {
            const n = data.submission_count;
            if (confirm(`This assessment already has ${n} student submission(s) across its section(s). Deleting it will permanently remove those submissions and their scores too. This cannot be undone. Delete anyway?`)) {
                sendDeleteClassAssessment(masterId, true);
            }
        } else {
            alert(data.error || 'Failed to delete assessment.');
        }
    })
    .catch(() => alert('Request failed.'));
}
</script>
<?php endif; ?>

<?php $this->load->view('footer'); ?>

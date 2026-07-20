<?php $this->load->view('header'); ?>

<div class="container">
    <div class="dashboard">
        <?php $this->load->view('profile_only'); ?>
        <?php $this->load->view('admin/nav_bar'); ?>

        <?php
        // get_for_schedule() returns a flat array (a.*, cs.*, iot.type AS iotype),
        // filtered here to group-enabled assessments only, so section/type/term
        // are top-level keys.
        $selected_assessment = null;
        if (!empty($selected_assessment_id)) {
            foreach ($assessments as $a) {
                if ((int) $a['assessment_id'] === (int) $selected_assessment_id) {
                    $selected_assessment = $a;
                    break;
                }
            }
        }
        ?>

        <div class="row justify-content-center mt-4">
            <div class="col-md-8 text-center">
                <h2 class="mb-1"><i class="fa fa-people-group"></i> Group Submissions</h2>
                <p class="text-muted small">
                    One card per group — grade a group's shared submission once and the
                    score applies to every member.
                    <?php if (!empty($selected_assessment_id)): ?>
                        <a href="<?= base_url('all_submissions/' . $selected_assessment_id) ?>">Switch to per-student view &rarr;</a>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- Dropdown to select a group assessment -->
        <div class="row justify-content-center mt-3">
            <div class="col-md-6">
                <div class="mb-4">
                    <div class="dropdown">
                        <button class="btn btn-secondary btn-block dropdown-toggle w-100" type="button" id="assessmentDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php if ($selected_assessment): ?>
                                <?= htmlspecialchars($selected_assessment['title']) ?>
                                <small>(ID: <?= $selected_assessment['assessment_id'] ?><?= !empty($selected_assessment['section']) ? ' · Sec ' . htmlspecialchars($selected_assessment['section']) : '' ?>)</small>
                            <?php elseif (isset($selected_assessment_id)): ?>
                                Selected: Assessment ID <?= $selected_assessment_id ?>
                            <?php else: ?>
                                Select a Group Assessment
                            <?php endif; ?>
                        </button>
                        <div class="dropdown-menu w-100 shadow-sm p-2" aria-labelledby="assessmentDropdown">
                            <input type="text" class="form-control mb-2" id="assessmentSearchInput"
                                placeholder="Search assessments by title, ID, or section..."
                                onkeyup="filterAssessments()" onclick="event.stopPropagation()">
                            <ul class="list-unstyled mb-0" id="assessmentList" style="max-height: 320px; overflow-y: auto;">
                                <?php if (empty($assessments)): ?>
                                    <li class="text-muted px-3 py-2">No group assessments scheduled for today.</li>
                                <?php endif; ?>
                                <?php foreach ($assessments as $assessment): ?>
                                    <?php
                                    $searchBits = [
                                        $assessment['title'],
                                        $assessment['assessment_id'],
                                        $assessment['section'] ?? '',
                                        $assessment['type'] ?? '',
                                        $assessment['iotype'] ?? '',
                                        $assessment['term'] ?? '',
                                    ];
                                    ?>
                                    <li data-search-text="<?= htmlspecialchars(strtolower(implode(' ', $searchBits))) ?>">
                                        <a class="dropdown-item" href="<?= base_url("group_submissions/" . $assessment['assessment_id']) ?>">
                                            <div><?= htmlspecialchars($assessment['title']) ?> <small class="text-muted">(ID: <?= $assessment['assessment_id'] ?>)</small></div>
                                            <div class="small text-muted">
                                                <?php if (!empty($assessment['section'])): ?><span class="badge badge-secondary mr-1">Sec <?= htmlspecialchars($assessment['section']) ?></span><?php endif; ?>
                                                <?php if (!empty($assessment['type'])): ?><span class="badge badge-light border mr-1"><?= htmlspecialchars($assessment['type']) ?></span><?php endif; ?>
                                                <?php if (!empty($assessment['iotype'])): ?><span class="badge badge-info mr-1"><?= htmlspecialchars($assessment['iotype']) ?></span><?php endif; ?>
                                                <?php if (!empty($assessment['term'])): ?><span class="badge badge-dark mr-1"><?= htmlspecialchars(ucwords(str_replace('-', ' ', $assessment['term']))) ?></span><?php endif; ?>
                                            </div>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                                <li id="assessmentNoResults" class="text-muted px-3 py-2" style="display:none;">No matching assessments.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($selected_assessment_id && !$is_group_assessment): ?>
            <!-- Selected assessment has no grouping set linked -->
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="alert alert-warning">
                        <i class="fa fa-triangle-exclamation"></i>
                        This assessment isn't set up for group submission (no grouping set is
                        linked to it). Grade it in the
                        <a href="<?= base_url('all_submissions/' . $selected_assessment_id) ?>">per-student submissions view</a>,
                        or link a grouping set to it in
                        <a href="<?= base_url('manage_assessments') ?>">Manage Assessments</a>.
                    </div>
                </div>
            </div>
        <?php elseif ($is_group_assessment): ?>

            <!-- Summary badges + search -->
            <div class="row justify-content-center">
                <div class="col-md-6 text-center">
                    <div class="mb-3">
                        <?php
                        $groups_with_sub = 0;
                        foreach ($groups as $g) {
                            if ($g['submitted_count'] > 0) $groups_with_sub++;
                        }
                        ?>
                        <span class="badge badge-primary p-2 mr-2">Groups: <?= count($groups) ?></span>
                        <span class="badge badge-success p-2 mr-2">Submitted: <?= $groups_with_sub ?></span>
                        <?php if (!empty($missing_students)): ?>
                            <a href="#" class="badge badge-danger p-2" data-bs-toggle="modal" data-bs-target="#missingStudentsModal">Missing students: <?= count($missing_students) ?></a>
                        <?php else: ?>
                            <span class="badge badge-secondary p-2">Missing students: 0</span>
                        <?php endif; ?>
                    </div>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="groupSearchInput"
                            placeholder="Search by group name or member..."
                            onkeyup="filterGroups()">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary" onclick="clearGroupSearch()">&times;</button>
                        </div>
                    </div>
                    <div class="btn-group mb-3" role="group">
                        <button type="button" class="btn btn-primary active" id="filterAll" onclick="filterGroups('all')">Show All</button>
                        <button type="button" class="btn btn-outline-primary" id="filterNoScore" onclick="filterGroups('no_score')">No Score Only</button>
                    </div>
                    <div id="groupSearchCount" class="text-muted small"></div>
                </div>
            </div>

            <!-- Group cards -->
            <div class="row justify-content-center mt-2">
                <div id="groupsContainer" class="col">
                    <?php if (!empty($groups)): ?>
                        <?php foreach ($groups as $g): ?>
                            <?php
                            $sub          = $g['submission'];              // shared row or null
                            $has_score    = ($g['score'] !== null);
                            $member_names = implode(' ', array_map(function ($m) {
                                return strtolower($m['lastname'] . ' ' . $m['firstname']);
                            }, $g['members']));
                            ?>
                            <div class="card mb-3 shadow-sm group-card"
                                data-has-score="<?= $has_score ? 'true' : 'false' ?>"
                                data-group-name="<?= htmlspecialchars(strtolower($g['group_name']), ENT_QUOTES, 'UTF-8') ?>"
                                data-member-names="<?= htmlspecialchars($member_names, ENT_QUOTES, 'UTF-8') ?>"
                                data-group-id="<?= $g['group_id'] ?>"
                                data-max-score="<?= htmlspecialchars($g['max_score'], ENT_QUOTES, 'UTF-8') ?>">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap">
                                        <h3 class="card-title mb-1"><i class="fa fa-users text-muted"></i> <?= htmlspecialchars($g['group_name']) ?></h3>
                                        <?php if ($g['submitted_count'] > 0): ?>
                                            <span class="badge badge-success p-2">Submitted <?= $g['submitted_count'] ?>/<?= $g['member_count'] ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary p-2">No submission</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mb-2">
                                        <?php foreach ($g['members'] as $m): ?>
                                            <span class="badge <?= $m['submitted'] ? 'badge-light border' : 'badge-light border text-muted' ?> mr-1 mb-1 p-2">
                                                <?php if ($m['submitted']): ?><i class="fa fa-check text-success"></i> <?php else: ?><i class="fa fa-minus text-muted"></i> <?php endif; ?>
                                                <?= htmlspecialchars($m['lastname'] . ', ' . $m['firstname']) ?>
                                            </span>
                                        <?php endforeach; ?>
                                        <?php if ($g['member_count'] === 0): ?>
                                            <span class="text-muted small">No members in this group.</span>
                                        <?php endif; ?>
                                    </div>
                                    <hr>

                                    <p class="card-text mb-3">
                                        <?php if ($sub): ?>
                                            <span class="text-muted small"><?= htmlspecialchars($sub['created_at']) ?></span>
                                            <?php if (!empty($sub['file_upload'])): ?> · <?= htmlspecialchars($sub['file_upload']) ?><?php endif; ?>
                                            — Score: <span class="current-score font-weight-bold"><?= $has_score ? $g['score'] : 'No score yet' ?></span>
                                            <span class="text-muted">/ <?= htmlspecialchars($g['max_score']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">This group hasn't submitted yet.</span>
                                        <?php endif; ?>
                                    </p>

                                    <?php if ($sub): ?>
                                        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#viewSubmissionModal"
                                            onclick="loadSubmission(<?= htmlspecialchars(json_encode($sub['code']), ENT_QUOTES, 'UTF-8') ?>, '<?= htmlspecialchars($sub['file_upload'] ?? '', ENT_QUOTES) ?>', <?= (int)($sub['iotype_id'] ?? 0) ?>, <?= (int) $g['group_id'] ?>)">
                                            View Submission
                                        </button>

                                        <?php if ($widget): ?>
                                            <template class="widget-submission-html" data-group-id="<?= $g['group_id'] ?>">
                                                <?php $this->load->view($widget['input_view'], [
                                                    'config'   => $widget_config,
                                                    'readonly' => true,
                                                    'existing' => json_decode($sub['code'] ?? '', true) ?: [],
                                                ]); ?>
                                            </template>
                                        <?php endif; ?>

                                        <div class="score-entry" data-group-id="<?= $g['group_id'] ?>" data-max-score="<?= htmlspecialchars($g['max_score'], ENT_QUOTES, 'UTF-8') ?>">
                                            <div class="input-group mb-2">
                                                <button type="button" class="btn btn-outline-secondary mr-1 ml-1" onclick="addGroupScore(<?= $g['group_id'] ?>, 5)">Late</button>
                                                <input type="number" step="any" class="form-control mr-1 ml-1 manual-score-input" placeholder="Enter group score" min="0" value="<?= $has_score ? htmlspecialchars($g['score']) : '' ?>">
                                                <button type="button" class="btn btn-outline-secondary mr-1 ml-1" onclick="addGroupScore(<?= $g['group_id'] ?>, <?= htmlspecialchars($g['max_score']) ?>)"><?= htmlspecialchars($g['max_score']) ?></button>
                                                <button type="button" class="btn btn-info mr-1 ml-1" onclick="submitManualGroupScore(this)">Submit to group</button>
                                            </div>
                                            <small class="text-muted">Applies to all <?= $g['submitted_count'] ?> member submission<?= $g['submitted_count'] !== 1 ? 's' : '' ?>.</small>
                                        </div>

                                        <?php if (!empty($sub['file_upload']) && !str_contains($sub['file_upload'], 'zip')): ?>
                                            <iframe src="<?= base_url("uploads/classworks/{$sub['file_upload']}") ?>" width="100%" height="500px" style="border: none;"></iframe>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-warning">No groups exist for this assessment's grouping set yet. Create groups in <a href="<?= base_url('Groupings') ?>">Groupings</a>.</div>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="alert alert-info">Select a group assessment above to view its group submissions.</div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal listing enrolled students who have not submitted -->
<div class="modal fade" id="missingStudentsModal" tabindex="-1" aria-labelledby="missingStudentsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="missingStudentsModalLabel">Students Who Have Not Submitted</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php if (!empty($missing_students)): ?>
                    <ul class="list-group">
                        <?php foreach ($missing_students as $student): ?>
                            <li class="list-group-item"><?= htmlspecialchars($student['lastname'] . ', ' . $student['firstname']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted mb-0">Everyone has submitted.</p>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for viewing a group's shared submission -->
<div class="modal fade" id="viewSubmissionModal" tabindex="-1" aria-labelledby="viewSubmissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewSubmissionModalLabel">Group Submission</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="submissionContent" class="text-left">
                    <p>Loading submission...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Renders a group's shared submission into the modal. Mirrors the per-student
    // all_submission.php loadSubmission(), keyed on group_id instead of
    // classwork_id (widget readonly render → quiz results → file → raw code).
    function loadSubmission(code, fileUpload, iotypeId, groupId) {
        const submissionContent = document.getElementById('submissionContent');

        const widgetTemplate = document.querySelector('.widget-submission-html[data-group-id="' + groupId + '"]');
        if (widgetTemplate) {
            submissionContent.innerHTML = widgetTemplate.innerHTML;
            return;
        }

        if ((iotypeId === 3 || iotypeId === 4) && code && code !== 'null') {
            try {
                const results = JSON.parse(code);
                const wrong = results.filter(q => q.is_correct === false);
                if (wrong.length === 0) {
                    submissionContent.innerHTML = '<p class="text-success font-weight-bold"><i class="fa fa-check-circle"></i> All answers correct!</p>';
                    return;
                }
                let html = `<p class="text-danger font-weight-bold mb-3"><i class="fa fa-times-circle"></i> ${wrong.length} incorrect answer${wrong.length !== 1 ? 's' : ''}:</p>`;
                wrong.forEach((q, i) => {
                    html += `
                        <div class="card mb-2 border-danger">
                            <div class="card-body py-2">
                                <p class="mb-1"><strong>#${i + 1}:</strong> ${q.question ?? ''}</p>
                                ${q.code ? `<pre class="bg-light p-2 rounded" style="font-size:.82rem;">${q.code}</pre>` : ''}
                                <p class="mb-1 text-danger"><i class="fa fa-times"></i> Group answered: <strong>${q.user_answer ?? ''}</strong></p>
                                <p class="mb-0 text-success"><i class="fa fa-check"></i> Correct answer: <strong>${q.correct_answer ?? ''}</strong></p>
                            </div>
                        </div>`;
                });
                submissionContent.innerHTML = html;
            } catch (e) {
                submissionContent.innerHTML = '<p class="text-danger">Could not parse submission data.</p>';
            }
            return;
        }

        if (fileUpload && fileUpload !== 'null' && fileUpload !== '') {
            submissionContent.innerHTML = `<iframe src="<?= base_url('uploads/classworks/') ?>${fileUpload}" width="100%" height="600px" style="border:none;"></iframe>`;
        } else if (code && code !== 'null') {
            submissionContent.innerHTML = `<label class="form-label">Submitted Content:</label><pre style="background:#f8f9fa;padding:15px;border-radius:5px;border:1px solid #ddd;">${code}</pre>`;
        } else {
            submissionContent.innerHTML = `<p class="text-danger">No submission available.</p>`;
        }
    }

    // Search box for the assessment dropdown (filters by title, ID, section).
    function filterAssessments() {
        const query = document.getElementById('assessmentSearchInput').value.toLowerCase().trim();
        let visibleCount = 0;
        document.querySelectorAll('#assessmentList > li[data-search-text]').forEach(li => {
            const match = li.dataset.searchText.includes(query);
            li.style.display = match ? '' : 'none';
            if (match) visibleCount++;
        });
        const noRes = document.getElementById('assessmentNoResults');
        if (noRes) noRes.style.display = visibleCount === 0 ? '' : 'none';
    }

    document.getElementById('assessmentDropdown').addEventListener('shown.bs.dropdown', () => {
        const input = document.getElementById('assessmentSearchInput');
        input.value = '';
        filterAssessments();
        input.focus();
    });

    let currentFilterMode = 'all';

    // Combines the score filter (all / no score) with the group/member search.
    function filterGroups(mode) {
        if (typeof mode === 'string') currentFilterMode = mode;
        const searchInput = document.getElementById('groupSearchInput');
        const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
        let visible = 0;

        document.querySelectorAll('.group-card').forEach(card => {
            const matchesScore = !(currentFilterMode === 'no_score' && card.dataset.hasScore === 'true');
            const matchesSearch = !searchTerm
                || card.dataset.groupName.includes(searchTerm)
                || card.dataset.memberNames.includes(searchTerm);
            const show = matchesScore && matchesSearch;
            card.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        const allBtn = document.getElementById('filterAll');
        const noScoreBtn = document.getElementById('filterNoScore');
        if (allBtn) allBtn.className = currentFilterMode === 'all' ? 'btn btn-primary active' : 'btn btn-outline-primary';
        if (noScoreBtn) noScoreBtn.className = currentFilterMode === 'no_score' ? 'btn btn-primary active' : 'btn btn-outline-primary';

        const countEl = document.getElementById('groupSearchCount');
        if (countEl) countEl.textContent = searchTerm ? `${visible} match${visible !== 1 ? 'es' : ''}` : '';
    }

    function clearGroupSearch() {
        const input = document.getElementById('groupSearchInput');
        if (input) input.value = '';
        filterGroups(currentFilterMode);
    }

    // Shared success/error toast (same style as all_submission.php).
    function showScoreAlert(success, message) {
        const oldAlert = document.getElementById('score-alert');
        if (oldAlert) oldAlert.remove();

        const alertDiv = document.createElement('div');
        alertDiv.id = 'score-alert';
        alertDiv.className = 'alert ' + (success ? 'alert-success' : 'alert-danger') + ' alert-dismissible fade show position-fixed';
        alertDiv.style.top = '20px';
        alertDiv.style.right = '20px';
        alertDiv.style.zIndex = '9999';
        alertDiv.innerHTML = `
            <strong>${message}</strong>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        `;
        document.body.appendChild(alertDiv);
        setTimeout(() => { $(alertDiv).alert('close'); }, 2500);
    }

    const assessmentId = <?= json_encode($selected_assessment_id) ?>;

    // Writes one score to the whole group (server fans it out to every member's
    // classworks row via classworks::set_score()).
    function addGroupScore(groupId, score) {
        fetch('<?= base_url('AdminController/add_group_score/') ?>' + assessmentId + '/' + groupId + '/' + score, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    showScoreAlert(false, data.notice || 'Failed to save group score.');
                    return;
                }

                const card = document.querySelector('.group-card[data-group-id="' + groupId + '"]');
                if (card) {
                    card.dataset.hasScore = 'true';
                    const currentScoreEl = card.querySelector('.current-score');
                    if (currentScoreEl) currentScoreEl.textContent = data.score;
                    const manualInput = card.querySelector('.manual-score-input');
                    if (manualInput) manualInput.value = data.score;
                }

                let msg = `Group scored ${data.score} (${data.updated_count} member${data.updated_count !== 1 ? 's' : ''})`;
                if (data.notice) msg += ' — ' + data.notice;
                showScoreAlert(true, msg);
                filterGroups(currentFilterMode);
            })
            .catch(() => showScoreAlert(false, 'Error saving group score.'));
    }

    function submitManualGroupScore(button) {
        const wrap = button.closest('.score-entry');
        const groupId = wrap.dataset.groupId;
        const input = wrap.querySelector('.manual-score-input');
        const score = input.value.trim();

        if (score === '' || isNaN(score) || Number(score) < 0) {
            showScoreAlert(false, 'Enter a valid score.');
            return;
        }
        addGroupScore(groupId, score);
    }
</script>

<?php $this->load->view('footer') ?>

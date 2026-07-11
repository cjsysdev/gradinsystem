<?php $this->load->view('header'); ?>

<div class="container">
    <div class="dashboard">
        <?php $this->load->view('profile_only'); ?>
        <?php $this->load->view('admin/nav_bar'); ?>

        <?php
        // get_for_schedule() returns a flat array (a.*, cs.*, iot.type AS iotype),
        // so section/type/term/due are top-level keys, not a ->class_schedule relation.
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
        <!-- Dropdown to select an assessment -->
        <div class="row justify-content-center mt-5">
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
                                Select an Assessment
                            <?php endif; ?>
                        </button>
                        <div class="card mb-3 shadow-sm mt-2">
                            <div class="card-body">
                                <h3 id="student_name" class="card-title text-center">lastname, firstname</h3>
                            </div>
                        </div>
                        <div class="row justify-content-center mt-3">
                            <div class="col text-center">
                                <button type="button" class="btn btn-secondary mb-4 mr-2" onclick="randomizeStudent()"><i class="fa fa-shuffle" aria-hidden="true"></i></button>
                                <button type="button" class="btn btn-secondary mb-4 mr-2" onclick="openFullscreenRandomizer()">&#x26F6;</button>
                                <button type="button" class="btn btn-secondary mb-4" onclick="toggleSubmissions()"><i class="fa fa-eye" aria-hidden="true"></i></button>
                            </div>
                        </div>
                        <div class="dropdown-menu w-100 shadow-sm p-2" aria-labelledby="assessmentDropdown">
                            <input type="text" class="form-control mb-2" id="assessmentSearchInput"
                                placeholder="Search assessments by title, ID, or section..."
                                onkeyup="filterAssessments()" onclick="event.stopPropagation()">
                            <ul class="list-unstyled mb-0" id="assessmentList" style="max-height: 320px; overflow-y: auto;">
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
                                        <a class="dropdown-item" href="<?= base_url("AdminController/all_submissions/" . $assessment['assessment_id']) ?>">
                                            <div><?= htmlspecialchars($assessment['title']) ?> <small class="text-muted">(ID: <?= $assessment['assessment_id'] ?>)</small></div>
                                            <div class="small text-muted">
                                                <?php if (!empty($assessment['section'])): ?><span class="badge badge-secondary mr-1">Sec <?= htmlspecialchars($assessment['section']) ?></span><?php endif; ?>
                                                <?php if (!empty($assessment['type'])): ?><span class="badge badge-light border mr-1"><?= htmlspecialchars($assessment['type']) ?></span><?php endif; ?>
                                                <?php if (!empty($assessment['iotype'])): ?><span class="badge badge-info mr-1"><?= htmlspecialchars($assessment['iotype']) ?></span><?php endif; ?>
                                                <?php if (!empty($assessment['term'])): ?><span class="badge badge-dark mr-1"><?= htmlspecialchars(ucwords(str_replace('-', ' ', $assessment['term']))) ?></span><?php endif; ?>
                                                <?php if (!empty($assessment['due'])): ?><span>Due <?= date('M j, Y', strtotime($assessment['due'])) ?></span><?php endif; ?>
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

        <!-- Filter buttons -->
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <?php if ($selected_assessment_id): ?>
                    <div class="mb-3">
                        <span class="badge badge-success p-2 mr-2">Submitted: <?= count($submissions) ?></span>
                        <?php if (!empty($missing_students)): ?>
                            <a href="#" class="badge badge-danger p-2" data-bs-toggle="modal" data-bs-target="#missingStudentsModal">Missing: <?= count($missing_students) ?></a>
                        <?php else: ?>
                            <span class="badge badge-secondary p-2">Missing: 0</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="studentSearchInput"
                        placeholder="Search student by name or classwork ID..."
                        onkeyup="handleStudentSearch(event)">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-outline-secondary" id="clearStudentSearch" onclick="clearStudentSearch()">&times;</button>
                    </div>
                </div>
                <div class="btn-group mb-3" role="group">
                    <button type="button" class="btn btn-primary active" id="filterAll" onclick="filterSubmissions('all')">Show All</button>
                    <button type="button" class="btn btn-outline-primary" id="filterNoScore" onclick="filterSubmissions('no_score')">No Score Only</button>
                    <button type="button" class="btn btn-outline-secondary" id="toggleBulkScore" onclick="toggleBulkScore()">All</button>
                </div>
                <div class="input-group mb-3" id="bulkScoreGroup" style="display:none;">
                    <input type="number" step="any" class="form-control" id="bulkScoreInput"
                        placeholder="Score to apply (blank = max score)" min="0">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-outline-success" id="bulkScoreBtn" onclick="addScoreToAll()">Add</button>
                    </div>
                </div>
                <div id="submissionSearchCount" class="text-muted small"></div>
            </div>
        </div>

        <!-- Display submissions for the selected assessment -->
        <div class="row justify-content-center mt-2">
            <div id="submissionsContainer" class="col">
                <?php if (!empty($submissions)): ?>
                    <?php foreach ($submissions as $row): ?>
                        <div class="card mb-3 shadow-sm submission-card"
                            data-has-score="<?= isset($row['score']) && $row['score'] !== null ? 'true' : 'false' ?>"
                            data-student-name="<?= htmlspecialchars(strtolower($row['lastname'] . ' ' . $row['firstname']), ENT_QUOTES, 'UTF-8') ?>"
                            data-classwork-id="<?= $row['classwork_id'] ?>"
                            data-max-score="<?= htmlspecialchars($row['max_score'], ENT_QUOTES, 'UTF-8') ?>">
                            <div class="card-body">
                                <h3 class="card-title mb-1"><?= $row['classwork_id'] . " - " . $row['lastname'] . ", " . $row['firstname'] ?></h3>
                                <hr>
                                <p class="card-text mb-3"><?= $row['created_at'] , " " , $row['file_upload'], " - " ?><span class="current-score"><?= isset($row['score']) ? $row['score'] : 'No score yet' ?></span></p>
                                <!-- Button to open modal -->
                                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#viewSubmissionModal" onclick="loadSubmission(<?= htmlspecialchars(json_encode($row['code']), ENT_QUOTES, 'UTF-8') ?>, '<?= $row['file_upload'] ?>', <?= (int)($row['iotype_id'] ?? 0) ?>, <?= (int) $row['classwork_id'] ?>)">
                                    View Submission
                                </button>
                                <?php if ($widget): ?>
                                    <template class="widget-submission-html" data-classwork-id="<?= $row['classwork_id'] ?>">
                                        <?php $this->load->view($widget['input_view'], [
                                            'config'   => $widget_config,
                                            'readonly' => true,
                                            'existing' => json_decode($row['code'] ?? '', true) ?: [],
                                        ]); ?>
                                    </template>
                                <?php endif; ?>
                                <div class="score-entry" data-classwork-id="<?= $row['classwork_id'] ?>">
                                    <div class="input-group mb-3">
                                        <button type="button" class="btn btn-outline-secondary mr-1 ml-1" onclick="addScore(<?= $row['classwork_id'] ?>, 5)">Late</button>
                                        <input type="number" step="any" class="form-control mr-1 ml-1 manual-score-input" placeholder="Enter score" min="0" value="<?= isset($row['score']) ? $row['score'] : '' ?>">
                                        <button type="button" class="btn btn-outline-secondary mr-1 ml-1" onclick="addScore(<?= $row['classwork_id'] ?>, 3)">3</button>
                                        <button type="button" class="btn btn-outline-secondary mr-1 ml-1" onclick="addScore(<?= $row['classwork_id'] ?>, 2)">2</button>
                                        <button type="button" class="btn btn-outline-secondary mr-1 ml-1" onclick="addScore(<?= $row['classwork_id'] ?>, <?= $row['max_score'] ?>)"><?= $row['max_score'] ?></button>
                                        <button type="button" class="btn btn-info mr-1 ml-1" onclick="submitManualScore(this)">Submit</button>
                                    </div>
                                </div>
                                <?php if (isset($row['file_upload']) && !str_contains($row['file_upload'], 'zip')): ?>
                                    <iframe src="<?= base_url("uploads/classworks/{$row['file_upload']}") ?>" width="100%" height="600px" style="border: none;"></iframe>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-warning">No submissions found for the selected assessment.</div>
                <?php endif; ?>
            </div>
        </div>
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

<!-- Modal for viewing submission -->
<div class="modal fade" id="viewSubmissionModal" tabindex="-1" aria-labelledby="viewSubmissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewSubmissionModalLabel">Submission</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Content will be dynamically loaded -->
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

<!-- Fullscreen Randomizer Overlay -->
<div id="randomizerOverlay" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;z-index:10000;flex-direction:column;align-items:center;justify-content:center;background:linear-gradient(135deg,#0d1b2a 0%,#1b2838 50%,#0d1b2a 100%);">
    <canvas id="confettiCanvas" style="position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;"></canvas>

    <button onclick="closeFullscreenRandomizer()" style="position:absolute;top:24px;right:28px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.25);color:#fff;border-radius:8px;padding:8px 20px;cursor:pointer;font-size:1rem;transition:background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.18)'" onmouseout="this.style.background='rgba(255,255,255,0.08)'">&#x2715; Close</button>

    <div id="fsEligibleCount" style="color:rgba(255,255,255,0.38);font-size:0.9rem;letter-spacing:2px;text-transform:uppercase;margin-bottom:28px;"></div>

    <div id="fsNameDisplay" style="font-size:clamp(2.5rem,9vw,6.5rem);font-weight:900;color:#fff;text-align:center;letter-spacing:3px;text-shadow:0 0 60px rgba(99,179,237,0.7),0 2px 8px rgba(0,0,0,0.5);min-height:1.2em;display:flex;align-items:center;justify-content:center;padding:0 40px;">
        Ready
    </div>

    <div id="fsBadgeArea" style="min-height:72px;display:flex;align-items:center;justify-content:center;margin-top:28px;"></div>

    <button id="fsRandomizeBtn" onclick="randomizeStudent(true)" style="margin-top:36px;background:linear-gradient(135deg,#276749,#38a169);border:none;color:#fff;border-radius:50px;padding:18px 64px;font-size:1.5rem;font-weight:700;cursor:pointer;box-shadow:0 8px 32px rgba(56,161,105,0.4);letter-spacing:1px;transition:transform 0.15s,box-shadow 0.15s;" onmouseover="this.style.transform='scale(1.06)';this.style.boxShadow='0 12px 40px rgba(56,161,105,0.55)'" onmouseout="this.style.transform='scale(1)';this.style.boxShadow='0 8px 32px rgba(56,161,105,0.4)'">
        🎲 Randomize
    </button>
</div>

<style>
@keyframes fsFlash {
    0%,100% { opacity:.3; filter:blur(4px); transform:scale(.94) translateY(-6px); }
    50%      { opacity:1;  filter:blur(0);   transform:scale(1)    translateY(0); }
}
@keyframes fsReveal {
    0%  { opacity:0; transform:scale(.4) translateY(30px); filter:blur(14px); }
    65% { opacity:1; transform:scale(1.07) translateY(-4px); filter:blur(0); }
    100%{ opacity:1; transform:scale(1) translateY(0); }
}
</style>

<script>
    // Module-level student data so all functions share it
    const allStudents = <?= json_encode(array_map(function ($row) {
        return [
            'classwork_id' => $row['classwork_id'],
            'lastname'     => $row['lastname'],
            'firstname'    => $row['firstname'],
            'student_id'   => $row['trans_no'],
            'score'        => $row['score'],
            'max_score'    => $row['max_score']
        ];
    }, $submissions)) ?>;

    function loadSubmission(code, fileUpload, iotypeId, classworkId) {
        const submissionContent = document.getElementById('submissionContent');

        const widgetTemplate = document.querySelector('.widget-submission-html[data-classwork-id="' + classworkId + '"]');
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
                                <p class="mb-1 text-danger"><i class="fa fa-times"></i> Student answered: <strong>${q.user_answer ?? ''}</strong></p>
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

        if (fileUpload && fileUpload !== 'null') {
            submissionContent.innerHTML = `<iframe src="<?= base_url('uploads/classworks/') ?>${fileUpload}" width="100%" height="600px" style="border:none;"></iframe>`;
        } else if (code && code !== 'null') {
            submissionContent.innerHTML = `<label class="form-label">Submitted Code:</label><pre style="background:#f8f9fa;padding:15px;border-radius:5px;border:1px solid #ddd;">${code}</pre>`;
        } else {
            submissionContent.innerHTML = `<p class="text-danger">No submission available.</p>`;
        }
    }

    function toggleSubmissions() {
        const section = document.getElementById('submissionsContainer');
        const btn = document.querySelector('.btn-secondary[onclick="toggleSubmissions()"]');
        const hidden = section.style.display === 'none';
        section.style.display = hidden ? 'block' : 'none';
        btn.innerHTML = hidden ? '<i class="fa fa-eye" aria-hidden="true"></i>' : '<i class="fa fa-eye-slash" aria-hidden="true"></i>';
    }

    // Search box for the assessment dropdown (filters by title, ID, section)
    function filterAssessments() {
        const query = document.getElementById('assessmentSearchInput').value.toLowerCase().trim();
        let visibleCount = 0;
        document.querySelectorAll('#assessmentList > li[data-search-text]').forEach(li => {
            const match = li.dataset.searchText.includes(query);
            li.style.display = match ? '' : 'none';
            if (match) visibleCount++;
        });
        document.getElementById('assessmentNoResults').style.display = visibleCount === 0 ? '' : 'none';
    }

    document.getElementById('assessmentDropdown').addEventListener('shown.bs.dropdown', () => {
        const input = document.getElementById('assessmentSearchInput');
        input.value = '';
        filterAssessments();
        input.focus();
    });

    let currentFilterMode = 'all';

    // Combines the score filter (all / no score) with the live student-name search
    function filterSubmissions(mode) {
        currentFilterMode = mode;
        const searchTerm = document.getElementById('studentSearchInput').value.toLowerCase().trim();
        let visibleCards = [];

        document.querySelectorAll('.submission-card').forEach(card => {
            const matchesScoreFilter = !(mode === 'no_score' && card.dataset.hasScore === 'true');
            const matchesSearch = !searchTerm
                || card.dataset.studentName.includes(searchTerm)
                || card.dataset.classworkId.includes(searchTerm);
            const visible = matchesScoreFilter && matchesSearch;
            card.style.display = visible ? '' : 'none';
            if (visible) visibleCards.push(card);
        });

        document.getElementById('filterAll').className      = mode === 'all'      ? 'btn btn-primary active' : 'btn btn-outline-primary';
        document.getElementById('filterNoScore').className  = mode === 'no_score' ? 'btn btn-primary active' : 'btn btn-outline-primary';

        const countEl = document.getElementById('submissionSearchCount');
        countEl.textContent = searchTerm ? `${visibleCards.length} match${visibleCards.length !== 1 ? 'es' : ''}` : '';

        return visibleCards;
    }

    function handleStudentSearch(event) {
        const visibleCards = filterSubmissions(currentFilterMode);

        // Enter jumps straight to the first match's score input for faster submission
        if (event.key === 'Enter' && visibleCards.length >= 1) {
            const scoreInput = visibleCards[0].querySelector('.manual-score-input');
            if (scoreInput) {
                scoreInput.focus();
                scoreInput.select();
            }
            visibleCards[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function clearStudentSearch() {
        document.getElementById('studentSearchInput').value = '';
        filterSubmissions(currentFilterMode);
        document.getElementById('studentSearchInput').focus();
    }

    function openFullscreenRandomizer() {
        const overlay = document.getElementById('randomizerOverlay');
        overlay.style.display = 'flex';
        const eligible = allStudents.filter(s => s.score === null || parseFloat(s.score) < parseFloat(s.max_score));
        document.getElementById('fsEligibleCount').textContent = `${eligible.length} student${eligible.length !== 1 ? 's' : ''} eligible`;
        document.getElementById('fsBadgeArea').innerHTML = '';
        const fsName = document.getElementById('fsNameDisplay');
        fsName.style.animation = 'none';
        fsName.style.color = '#fff';
        fsName.style.textShadow = '0 0 60px rgba(99,179,237,0.7),0 2px 8px rgba(0,0,0,0.5)';
        fsName.textContent = 'Ready';
    }

    function closeFullscreenRandomizer() {
        document.getElementById('randomizerOverlay').style.display = 'none';
    }

    function randomizeStudent(isFullscreen = false) {
        const students = allStudents.filter(s =>
            s.score === null || parseFloat(s.score) < parseFloat(s.max_score)
        );

        const nameElem  = isFullscreen ? document.getElementById('fsNameDisplay')  : document.getElementById('student_name');
        const badgeArea = isFullscreen ? document.getElementById('fsBadgeArea')     : null;
        const btn       = isFullscreen ? document.getElementById('fsRandomizeBtn')  : null;

        if (students.length === 0) {
            nameElem.style.color = isFullscreen ? '#fc8181' : '';
            nameElem.textContent = 'No eligible students.';
            return;
        }

        if (btn) { btn.disabled = true; }
        if (badgeArea) badgeArea.innerHTML = '';

        let animationCount = 30, currentFrame = 0, interval = 40;

        function animate() {
            const s = students[Math.floor(Math.random() * students.length)];

            if (isFullscreen) {
                nameElem.style.animation = 'none';
                nameElem.offsetHeight; // force reflow to restart animation
                nameElem.style.color      = '#93c5fd';
                nameElem.style.textShadow = '0 0 40px rgba(147,197,253,0.6)';
                nameElem.style.animation  = 'fsFlash 0.15s ease-in-out';
                nameElem.textContent = `${s.lastname}, ${s.firstname}`;
            } else {
                nameElem.innerHTML = `<span style="opacity:0.7">${s.lastname}, ${s.firstname}</span>`;
            }

            currentFrame++;
            if (currentFrame > animationCount - 8) interval += 40;

            if (currentFrame < animationCount) {
                setTimeout(animate, interval);
            } else {
                const pick = students[Math.floor(Math.random() * students.length)];
                showFinalPick(pick, nameElem, badgeArea, isFullscreen);
                if (btn) btn.disabled = false;
            }
        }
        animate();
    }

    function showFinalPick(student, nameElem, badgeArea, isFullscreen) {
        if (isFullscreen) {
            nameElem.style.animation = 'none';
            nameElem.offsetHeight;
            nameElem.style.color      = '#68d391';
            nameElem.style.textShadow = '0 0 80px rgba(104,211,145,0.9),0 2px 8px rgba(0,0,0,0.5)';
            nameElem.style.animation  = 'fsReveal 0.55s cubic-bezier(0.34,1.56,0.64,1) forwards';
            nameElem.textContent = `${student.lastname}, ${student.firstname}`;
            badgeArea.innerHTML = `
                <a href="#" onclick="addRandScoreIncremental(${student.classwork_id}, 2); return false;"
                   style="display:inline-block;background:linear-gradient(135deg,#276749,#38a169);color:#fff;font-size:2rem;font-weight:800;padding:14px 52px;border-radius:50px;text-decoration:none;box-shadow:0 4px 24px rgba(56,161,105,0.5);letter-spacing:2px;transition:transform 0.15s,box-shadow 0.15s;"
                   onmouseover="this.style.transform='scale(1.08)';this.style.boxShadow='0 8px 32px rgba(56,161,105,0.65)'"
                   onmouseout="this.style.transform='scale(1)';this.style.boxShadow='0 4px 24px rgba(56,161,105,0.5)'">
                    II
                </a>
                <a href="#" onclick="addRandScoreIncremental(${student.classwork_id}, 1); return false;"
                   style="display:inline-block;background:linear-gradient(135deg,#2b6cb0,#4299e1);color:#fff;font-size:1.4rem;font-weight:800;padding:14px 36px;border-radius:50px;text-decoration:none;box-shadow:0 4px 24px rgba(66,153,225,0.5);letter-spacing:2px;margin-left:16px;transition:transform 0.15s,box-shadow 0.15s;"
                   onmouseover="this.style.transform='scale(1.08)';this.style.boxShadow='0 8px 32px rgba(66,153,225,0.65)'"
                   onmouseout="this.style.transform='scale(1)';this.style.boxShadow='0 4px 24px rgba(66,153,225,0.5)'">
                    I
                </a>`;
            launchConfetti();
        } else {
            nameElem.innerHTML = `
                <b>${student.lastname}, ${student.firstname}</b>
                <a class="badge bg-success m-2 text-white" href="#" onclick="addRandScoreIncremental(${student.classwork_id}, 2); return false;">II</a>
                <a class="badge bg-primary m-2 text-white" href="#" onclick="addRandScoreIncremental(${student.classwork_id}, 1); return false;">I</a>`;
        }
    }

    function launchConfetti() {
        const canvas = document.getElementById('confettiCanvas');
        canvas.width  = window.innerWidth;
        canvas.height = window.innerHeight;
        const ctx    = canvas.getContext('2d');
        const colors = ['#ff6b6b','#ffd93d','#6bcb77','#4d96ff','#ff922b','#cc5de8','#f06595'];
        const particles = Array.from({length: 130}, () => ({
            x: canvas.width / 2, y: canvas.height * 0.45,
            vx: (Math.random() - 0.5) * 26,
            vy: (Math.random() - 0.88) * 22,
            color: colors[Math.floor(Math.random() * colors.length)],
            w: Math.random() * 10 + 5, h: Math.random() * 6 + 3,
            alpha: 1, rot: Math.random() * 360,
            rotV: (Math.random() - 0.5) * 14
        }));
        function draw() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            let alive = false;
            particles.forEach(p => {
                p.x += p.vx; p.y += p.vy; p.vy += 0.5;
                p.alpha -= 0.013; p.rot += p.rotV;
                if (p.alpha <= 0) return;
                alive = true;
                ctx.save();
                ctx.translate(p.x, p.y);
                ctx.rotate(p.rot * Math.PI / 180);
                ctx.globalAlpha = Math.max(0, p.alpha);
                ctx.fillStyle = p.color;
                ctx.fillRect(-p.w / 2, -p.h / 2, p.w, p.h);
                ctx.restore();
            });
            if (alive) requestAnimationFrame(draw);
        }
        draw();
    }

    // Shared success/error toast for every score-saving action on this page.
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
        setTimeout(() => { $(alertDiv).alert('close'); }, 2000);
    }

    function addRandScoreIncremental(classwork_id, points = 2) {
        fetch('<?= base_url('AdminController/add_rand_score_incremental/') ?>' + classwork_id + '/' + points, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) { showScoreAlert(false, 'Failed to add points.'); return; }
                showScoreAlert(true, `+${points} point${points !== 1 ? 's' : ''} added!`);
            })
            .catch(() => showScoreAlert(false, 'Error adding score.'));
    }

    // AJAX call to add/update a score via classworks->add_score — works
    // whether the classwork already has a score or not, since add_score()
    // is an unconditional UPDATE, not an insert-if-missing.
    function addScore(classwork_id, score) {
        fetch('<?= base_url('AdminController/add_score/') ?>' + classwork_id + '/' + score, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) { showScoreAlert(false, 'Failed to save score.'); return; }

                // Reflect the new score in the card + randomizer data without
                // a full page reload.
                const card = document.querySelector('.submission-card[data-classwork-id="' + classwork_id + '"]');
                if (card) {
                    card.dataset.hasScore = 'true';
                    const currentScoreEl = card.querySelector('.current-score');
                    if (currentScoreEl) currentScoreEl.textContent = score;
                    const manualInput = card.querySelector('.manual-score-input');
                    if (manualInput) manualInput.value = score;
                }
                const student = allStudents.find(s => String(s.classwork_id) === String(classwork_id));
                if (student) student.score = score;

                showScoreAlert(true, 'Score saved: ' + score);
                filterSubmissions(currentFilterMode);
            })
            .catch(() => showScoreAlert(false, 'Error saving score.'));
    }

    function toggleBulkScore() {
        const group = document.getElementById('bulkScoreGroup');
        const hidden = group.style.display === 'none';
        group.style.display = hidden ? '' : 'none';
        if (hidden) document.getElementById('bulkScoreInput').focus();
    }

    // Applies a single score to every currently visible submission card
    // (respects the Show All / No Score Only filter and the name search),
    // so filtering to "No Score Only" first + leaving the input blank is
    // the normal "give everyone who hasn't submitted their max score" flow.
    function addScoreToAll() {
        const input = document.getElementById('bulkScoreInput');
        const override = input.value.trim();

        if (override !== '' && (isNaN(override) || Number(override) < 0)) {
            showScoreAlert(false, 'Enter a valid score.');
            return;
        }

        const visibleCards = Array.from(document.querySelectorAll('.submission-card'))
            .filter(card => card.style.display !== 'none');

        if (visibleCards.length === 0) {
            showScoreAlert(false, 'No visible submissions to score.');
            return;
        }

        const label = override !== '' ? override : 'each submission\'s max score';
        if (!confirm(`Apply ${label} to ${visibleCards.length} visible submission${visibleCards.length !== 1 ? 's' : ''}?`)) {
            return;
        }

        visibleCards.forEach(card => {
            const score = override !== '' ? override : card.dataset.maxScore;
            addScore(card.dataset.classworkId, score);
        });
    }

    function submitManualScore(button) {
        const wrap = button.closest('.score-entry');
        const classworkId = wrap.dataset.classworkId;
        const input = wrap.querySelector('.manual-score-input');
        const score = input.value.trim();

        if (score === '' || isNaN(score) || Number(score) < 0) {
            showScoreAlert(false, 'Enter a valid score.');
            return;
        }
        addScore(classworkId, score);
    }

    const assessmentId = <?= json_encode($selected_assessment_id) ?>;

    function checkNewSubmissions() {
        console.log("checking");
        fetch(`<?= base_url('admin/check_new_submissions_by_assessment/') ?>${assessmentId}`)
            .then(response => response.json())
            .then(data => {
                const submissionsContainer = document.getElementById('submissionsContainer');
                submissionsContainer.innerHTML = ''; // Clear the container

                if (data.length > 0) {
                    data.forEach(submission => {
                        const submissionCard = `
                                <div class="card mb-3 shadow-sm">
                                    <div class="card-body">
                                        <h3 class="card-title">${submission.classwork_id} - ${submission.lastname}, ${submission.firstname}</h3>
                                        <p class="card-text">${submission.created_at} <span class="badge badge-secondary">${submission.score}</span></p>
                                    </div>
                                </div>
                            `;
                        submissionsContainer.innerHTML += submissionCard;
                    });
                } else {
                    submissionsContainer.innerHTML = '<div class="alert alert-warning">No submissions found.</div>';
                }
            })
            .catch(error => console.error('Error fetching submissions:', error));
    }
    // setInterval(checkNewSubmissions, 10000);
</script>

<?php $this->load->view('footer') ?>
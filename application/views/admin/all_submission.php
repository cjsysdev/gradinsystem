<?php $this->load->view('header'); ?>

<div class="container">
    <div class="dashboard">
        <?php $this->load->view('profile_only'); ?>
        <?php $this->load->view('admin/nav_bar'); ?>

        <!-- Dropdown to select an assessment -->
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="mb-4">
                    <div class="dropdown">
                        <button class="btn btn-secondary btn-block dropdown-toggle w-100" type="button" id="assessmentDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <?= isset($selected_assessment_id) ? "Selected: Assessment ID $selected_assessment_id" : "Select an Assessment" ?>
                        </button>
                        <div class="card mb-3 shadow-sm mt-2">
                            <div class="card-body">
                                <h3 id="student_name" class="card-title text-center">lastname, firstname</h3>
                            </div>
                        </div>
                        <div class="row justify-content-center mt-3">
                            <div class="col text-center">
                                <button type="button" class="btn btn-success mb-4 mr-2" onclick="randomizeStudent()"><i class="fa fa-shuffle" aria-hidden="true"></i></button>
                                <button type="button" class="btn btn-dark mb-4 mr-2" onclick="openFullscreenRandomizer()">&#x26F6;</button>
                                <button type="button" class="btn btn-secondary mb-4" onclick="toggleSubmissions()"><i class="fa fa-eye" aria-hidden="true"></i></button>
                            </div>
                        </div>
                        <ul class="dropdown-menu w-100 shadow-sm" aria-labelledby="assessmentDropdown">
                            <?php foreach ($assessments as $assessment): ?>
                                <li>
                                    <a class="dropdown-item" href="<?= base_url("AdminController/all_submissions/" . $assessment['assessment_id']) ?>">
                                        <?= $assessment['title'] ?> (ID: <?= $assessment['assessment_id'] ?>) <?= $assessment['class_schedule']->section ?? "" ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter buttons -->
        <div class="row justify-content-center">
            <div class="col-md-6 text-center">
                <div class="btn-group mb-3" role="group">
                    <button type="button" class="btn btn-primary active" id="filterAll" onclick="filterSubmissions('all')">Show All</button>
                    <button type="button" class="btn btn-outline-primary" id="filterNoScore" onclick="filterSubmissions('no_score')">No Score Only</button>
                </div>
            </div>
        </div>

        <!-- Display submissions for the selected assessment -->
        <div class="row justify-content-center mt-2">
            <div id="submissionsContainer" class="col">
                <?php if (!empty($submissions)): ?>
                    <?php foreach ($submissions as $row): ?>
                        <div class="card mb-3 shadow-sm submission-card" data-has-score="<?= isset($row['score']) && $row['score'] !== null ? 'true' : 'false' ?>">
                            <div class="card-body">
                                <h3 class="card-title mb-1"><?= $row['classwork_id'] . " - " . $row['lastname'] . ", " . $row['firstname'] ?></h3>
                                <hr>
                                <p class="card-text mb-3"><?= $row['created_at'] , " " , $row['file_upload'], " - ", $row['score'] ?></p>
                                <!-- Button to open modal -->
                                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#viewSubmissionModal" onclick="loadSubmission(<?= htmlspecialchars(json_encode($row['code']), ENT_QUOTES, 'UTF-8') ?>, '<?= $row['file_upload'] ?>')">
                                    View Submission
                                </button>
                                <?php if (!isset($row['score'])): ?>
                                    <form action="<?= base_url('ClassworkController/add_score') ?>" method="POST">
                                        <input type="hidden" name="classwork_id" value="<?= $row['classwork_id'] ?>">
                                        <input type="hidden" name="student_id" value="<?= $row['trans_no'] ?>">
                                        <input type="hidden" name="assessment_id" value="<?= $selected_assessment_id ?>">
                                        <div class="input-group mb-3">
                                            <a href="<?= base_url('add_rand_score/' . $row['classwork_id'] . '/5' . "/$selected_assessment_id") ?>" type="button" class="btn btn-outline-secondary mr-1 ml-1" name="score" value="good">Late</a>
                                            <input type="decimal" name="score" class="form-control mr-1 ml-1" placeholder="Enter score" min="0" required>
                                            <a href="<?= base_url('add_rand_score/' . $row['classwork_id'] . "/{$row['max_score']}" . "/$selected_assessment_id") ?>" type="button" class="btn btn-outline-secondary mr-1 ml-1" name="score" value="good"><?= $row['max_score'] ?></a><button type="submit" class="btn btn-info mr-1 ml-1">Submit</button>
                                        </div>
                                        <?php if (isset($row['file_upload']) && !str_contains($row['file_upload'], 'zip')): ?>
                                            <iframe src="<?= base_url("uploads/classworks/{$row['file_upload']}") ?>" width="100%" height="600px" style="border: none;"></iframe>
                                        <?php endif; ?>
                                    </form>
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

    function loadSubmission(code, fileUpload) {
        const submissionContent = document.getElementById('submissionContent');
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

    function filterSubmissions(mode) {
        document.querySelectorAll('.submission-card').forEach(card => {
            card.style.display = (mode === 'no_score' && card.dataset.hasScore === 'true') ? 'none' : '';
        });
        document.getElementById('filterAll').className      = mode === 'all'      ? 'btn btn-primary active' : 'btn btn-outline-primary';
        document.getElementById('filterNoScore').className  = mode === 'no_score' ? 'btn btn-primary active' : 'btn btn-outline-primary';
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
                <a href="#" onclick="addRandScoreIncremental(${student.classwork_id}); return false;"
                   style="display:inline-block;background:linear-gradient(135deg,#276749,#38a169);color:#fff;font-size:2rem;font-weight:800;padding:14px 52px;border-radius:50px;text-decoration:none;box-shadow:0 4px 24px rgba(56,161,105,0.5);letter-spacing:2px;transition:transform 0.15s,box-shadow 0.15s;"
                   onmouseover="this.style.transform='scale(1.08)';this.style.boxShadow='0 8px 32px rgba(56,161,105,0.65)'"
                   onmouseout="this.style.transform='scale(1)';this.style.boxShadow='0 4px 24px rgba(56,161,105,0.5)'">
                    +2
                </a>`;
            launchConfetti();
        } else {
            nameElem.innerHTML = `
                <b>${student.lastname}, ${student.firstname}</b>
                <a class="badge bg-success m-2 text-white" href="#" onclick="addRandScoreIncremental(${student.classwork_id}); return false;">+2</a>`;
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

    function addRandScoreIncremental(classwork_id) {
        const oldAlert = document.getElementById('score-alert');
        if (oldAlert) oldAlert.remove();

        fetch('<?= base_url('AdminController/add_rand_score_incremental/') ?>' + classwork_id, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                const alertDiv = document.createElement('div');
                alertDiv.id = 'score-alert';
                alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
                alertDiv.style.top = '20px';
                alertDiv.style.right = '20px';
                alertDiv.style.zIndex = '9999';
                alertDiv.innerHTML = `
            <strong>+2 points added!</strong>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        `;
                document.body.appendChild(alertDiv);
                setTimeout(() => { $(alertDiv).alert('close'); }, 2000);
            })
            .catch(() => {
                const alertDiv = document.createElement('div');
                alertDiv.id = 'score-alert';
                alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed';
                alertDiv.style.top = '20px';
                alertDiv.style.right = '20px';
                alertDiv.style.zIndex = '9999';
                alertDiv.innerHTML = `
            <strong>Error adding score.</strong>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        `;
                document.body.appendChild(alertDiv);
                setTimeout(() => { $(alertDiv).alert('close'); }, 2000);
            });
    }

    // AJAX call to add score using classworks->add_score
    function addScore(classwork_id, score) {
        // Remove any existing alert
        const oldAlert = document.getElementById('score-alert');
        if (oldAlert) oldAlert.remove();

        fetch('<?= base_url('AdminController/add_score/') ?>' + classwork_id + '/' + score, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                // Bootstrap 4 alert
                const alertDiv = document.createElement('div');
                alertDiv.id = 'score-alert';
                alertDiv.className = 'alert alert-success alert-dismissible fade show position-fixed';
                alertDiv.style.top = '20px';
                alertDiv.style.right = '20px';
                alertDiv.style.zIndex = '9999';
                alertDiv.innerHTML = `
            <strong>Score added!</strong>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        `;
                document.body.appendChild(alertDiv);

                // Auto-dismiss after 2 seconds
                setTimeout(() => {
                    $(alertDiv).alert('close');
                }, 2000);
            })
            .catch(error => {
                const alertDiv = document.createElement('div');
                alertDiv.id = 'score-alert';
                alertDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed';
                alertDiv.style.top = '20px';
                alertDiv.style.right = '20px';
                alertDiv.style.zIndex = '9999';
                alertDiv.innerHTML = `
            <strong>Error adding score.</strong>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        `;
                document.body.appendChild(alertDiv);

                setTimeout(() => {
                    $(alertDiv).alert('close');
                }, 2000);
            });
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
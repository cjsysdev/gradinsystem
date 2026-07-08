<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Natural Language Processing (NLP) with Orange Data Mining</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="<?= base_url('assets/bootstrap.4.5.2.min.css') ?>">

    <!-- Discussion Style -->
    <link rel="stylesheet" href="<?= base_url('assets/discussion-style.css') ?>">

    <!-- Highlight.js -->
    <link rel="stylesheet" href="<?= base_url('assets/highlights/atom-one-light.min.css') ?>">
    <script src="<?= base_url('assets/highlights/11.7.0-highlight.min.js') ?>"></script>
    <script>
        hljs.highlightAll();
    </script>
</head>

<body>

    <header>
        <h1>Natural Language Processing (NLP) with Orange Data Mining</h1>
        <p>Turning text (reviews, comments, messages) into meaningful insights — no heavy coding required</p>
    </header>

    <div class="container">

        <div class="section">
            <h2>📱 Interactive Quiz: Scenario → Database Design</h2>
            <p class="text-muted mb-3">
                Answer step-by-step. You’ll practice identifying entities, keys, relationships, and SQL tables.
            </p>

            <!-- Scenario Card -->
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title mb-2">Scenario</h5>
                    <p id="scenarioText" class="mb-0">
                        A small <strong>Clinic</strong> records patient visits. Each <strong>Patient</strong> can have many <strong>Visits</strong>.
                        Each visit is handled by one <strong>Doctor</strong>. A doctor can handle many visits.
                        A visit includes a date and diagnosis.
                    </p>
                    <small class="text-muted d-block mt-2">Tip: Think nouns (entities) + how they connect (relationships).</small>
                </div>
            </div>

            <!-- Progress -->
            <div class="d-flex align-items-center mb-2">
                <small class="text-muted mr-2">Progress</small>
                <div class="progress flex-grow-1" style="height:10px;">
                    <div id="progressBar" class="progress-bar" role="progressbar" style="width:0%"></div>
                </div>
            </div>

            <!-- Quiz Card -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge badge-primary" id="qTag">Step 1</span>
                        <small class="text-muted" id="qCount">1 / 6</small>
                    </div>

                    <h5 class="mt-3" id="qText">Question text...</h5>

                    <div id="choices" class="mt-3"></div>

                    <div id="feedback" class="alert mt-3 d-none" role="alert"></div>

                    <div class="d-flex justify-content-between mt-3">
                        <button id="btnPrev" class="btn btn-outline-secondary btn-sm" disabled>Back</button>
                        <button id="btnNext" class="btn btn-primary btn-sm" disabled>Next</button>
                    </div>
                </div>
            </div>

            <!-- Results -->
            <div id="resultBox" class="card mt-3 d-none">
                <div class="card-body">
                    <h5 class="card-title">✅ Results</h5>
                    <p class="mb-2">Score: <strong id="scoreText"></strong></p>

                    <div class="alert alert-info mb-3">
                        <strong>Your next task (mini-build):</strong>
                        <ol class="mb-0">
                            <li>Draw the ERD: Patient — Visit — Doctor</li>
                            <li>Write SQL: CREATE DATABASE + CREATE TABLE for Patient, Doctor, Visit</li>
                            <li>Make sure Visit has foreign keys to Patient and Doctor</li>
                        </ol>
                    </div>

                    <button id="btnRestart" class="btn btn-outline-primary btn-sm">Restart Quiz</button>
                </div>
            </div>
        </div>


    </div>

    <style>
        /* Mobile-friendly tap targets */
        .choice-btn {
            width: 100%;
            text-align: left;
            padding: 12px 14px;
            border-radius: 12px;
            margin-bottom: 10px;
            border: 1px solid #dee2e6;
            background: #fff;
            transition: transform .05s ease;
        }

        .choice-btn:active {
            transform: scale(0.99);
        }

        .choice-btn.correct {
            border-color: #28a745;
        }

        .choice-btn.wrong {
            border-color: #dc3545;
        }

        .mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        }
    </style>

    <script>
        // ✅ You can edit this scenario + questions anytime
        const quiz = [{
                tag: "Step 1: Entities",
                q: "Which set best represents the main entities in the scenario?",
                type: "single",
                choices: [
                    "Patient, Doctor, Visit",
                    "Date, Diagnosis, Clinic",
                    "PatientName, DoctorName, VisitDate",
                    "Room, Nurse, Medicine"
                ],
                answer: 0,
                explain: "Entities are the main nouns we store data about. Here: Patient, Doctor, and Visit."
            },
            {
                tag: "Step 2: Primary Keys",
                q: "Which primary key design is most appropriate?",
                type: "single",
                choices: [
                    "PatientName as PK, DoctorName as PK, VisitDate as PK",
                    "PatientID, DoctorID, VisitID",
                    "Diagnosis as PK, Date as PK",
                    "ClinicName as PK for all tables"
                ],
                answer: 1,
                explain: "Use stable, unique identifiers like PatientID/DoctorID/VisitID instead of names or dates."
            },
            {
                tag: "Step 3: Relationships",
                q: "Which relationship is correct?",
                type: "single",
                choices: [
                    "Patient (1) — (1) Visit",
                    "Patient (1) — (Many) Visit",
                    "Doctor (Many) — (Many) Visit",
                    "Doctor (1) — (1) Patient"
                ],
                answer: 1,
                explain: "A patient can have many visits; each visit belongs to one patient."
            },
            {
                tag: "Step 4: Foreign Keys",
                q: "Which foreign keys should be in the Visit table?",
                type: "multi",
                choices: [
                    "PatientID",
                    "DoctorID",
                    "DiagnosisID",
                    "ClinicPassword"
                ],
                answerMulti: [0, 1],
                explain: "Visit should reference Patient and Doctor using PatientID and DoctorID."
            },
            {
                tag: "Step 5: Attributes",
                q: "Which list best matches Visit attributes from the scenario?",
                type: "single",
                choices: [
                    "VisitID, VisitDate, Diagnosis, PatientID, DoctorID",
                    "VisitID, PatientName, DoctorSpecialty, ClinicBudget",
                    "VisitDate only",
                    "DoctorID, DoctorName only"
                ],
                answer: 0,
                explain: "Visit needs its own ID + date + diagnosis + links to patient and doctor."
            },
            {
                tag: "Step 6: SQL Table Mapping",
                q: "Which SQL snippet correctly creates the Visit table structure?",
                type: "single",
                choices: [
                    `CREATE TABLE Visit (
  VisitID INT PRIMARY KEY,
  VisitDate DATE,
  Diagnosis VARCHAR(255),
  PatientID INT,
  DoctorID INT,
  FOREIGN KEY (PatientID) REFERENCES Patient(PatientID),
  FOREIGN KEY (DoctorID) REFERENCES Doctor(DoctorID)
);`,
                    `CREATE TABLE Visit (
  PatientName VARCHAR(100) PRIMARY KEY,
  DoctorName VARCHAR(100),
  Diagnosis TEXT
);`,
                    `CREATE TABLE Visit (
  VisitID INT,
  Anything TEXT
);`,
                    `CREATE TABLE Visit (
  VisitDate DATE PRIMARY KEY
);`
                ],
                answer: 0,
                explain: "Correct PK + columns + two foreign keys referencing Patient and Doctor."
            }
        ];

        let current = 0;
        let score = 0;
        let selected = {}; // qIndex -> selected index OR array
        const $ = (id) => document.getElementById(id);

        function render() {
            const total = quiz.length;
            const q = quiz[current];

            $("qTag").textContent = q.tag;
            $("qCount").textContent = `${current + 1} / ${total}`;
            $("qText").textContent = q.q;

            // progress
            const pct = Math.round((current / total) * 100);
            $("progressBar").style.width = pct + "%";

            // buttons
            $("btnPrev").disabled = current === 0;
            $("btnNext").disabled = (selected[current] == null || (Array.isArray(selected[current]) && selected[current].length === 0));

            // feedback hidden
            $("feedback").classList.add("d-none");
            $("feedback").innerHTML = "";

            // choices
            const wrap = $("choices");
            wrap.innerHTML = "";

            q.choices.forEach((c, idx) => {
                const btn = document.createElement("button");
                btn.type = "button";
                btn.className = "choice-btn";
                btn.setAttribute("aria-label", `choice ${idx + 1}`);

                // preserve code formatting if multiline
                if (c.includes("\n")) {
                    btn.innerHTML = `<div class="mono" style="white-space:pre-wrap;">${escapeHtml(c)}</div>`;
                } else {
                    btn.textContent = c;
                }

                // restore selection visual
                if (q.type === "single" && selected[current] === idx) {
                    btn.style.borderWidth = "2px";
                }
                if (q.type === "multi" && Array.isArray(selected[current]) && selected[current].includes(idx)) {
                    btn.style.borderWidth = "2px";
                }

                btn.addEventListener("click", () => onPick(idx));
                wrap.appendChild(btn);
            });

            // show result if last already answered and moved
            $("resultBox").classList.add("d-none");
        }

        function onPick(idx) {
            const q = quiz[current];

            if (q.type === "single") {
                selected[current] = idx;
            } else {
                // multi select toggle
                if (!Array.isArray(selected[current])) selected[current] = [];
                const i = selected[current].indexOf(idx);
                if (i >= 0) selected[current].splice(i, 1);
                else selected[current].push(idx);
                selected[current].sort((a, b) => a - b);
            }

            $("btnNext").disabled = (selected[current] == null || (Array.isArray(selected[current]) && selected[current].length === 0));

            // instant feedback
            showFeedback();
            // re-render for selection outline
            render();
            showFeedback(true); // keep feedback visible after re-render
        }

        function showFeedback(keepOpen = false) {
            const q = quiz[current];
            const fb = $("feedback");

            let correct = false;
            if (q.type === "single") {
                correct = selected[current] === q.answer;
            } else {
                const sel = selected[current] || [];
                const ans = q.answerMulti || [];
                correct = sel.length === ans.length && sel.every((v, i) => v === ans[i]);
            }

            fb.classList.remove("d-none");
            fb.classList.toggle("alert-success", correct);
            fb.classList.toggle("alert-danger", !correct);

            const msg = correct ? "Correct ✅" : "Not quite ❌";
            fb.innerHTML = `<strong>${msg}</strong><br><small>${q.explain}</small>`;

            // decorate choices
            const buttons = $("choices").querySelectorAll("button.choice-btn");
            buttons.forEach((b, idx) => {
                b.classList.remove("correct", "wrong");
                // mark correct answer(s)
                if (q.type === "single") {
                    if (idx === q.answer) b.classList.add("correct");
                    if (selected[current] === idx && idx !== q.answer) b.classList.add("wrong");
                } else {
                    const ans = q.answerMulti || [];
                    if (ans.includes(idx)) b.classList.add("correct");
                    if ((selected[current] || []).includes(idx) && !ans.includes(idx)) b.classList.add("wrong");
                }
            });

            if (!keepOpen) {
                // no-op; kept for potential future behavior
            }
        }

        function computeScore() {
            let s = 0;
            quiz.forEach((q, i) => {
                if (q.type === "single") {
                    if (selected[i] === q.answer) s++;
                } else {
                    const sel = selected[i] || [];
                    const ans = q.answerMulti || [];
                    const ok = sel.length === ans.length && sel.every((v, idx) => v === ans[idx]);
                    if (ok) s++;
                }
            });
            return s;
        }

        function finish() {
            const total = quiz.length;
            score = computeScore();
            $("progressBar").style.width = "100%";
            $("scoreText").textContent = `${score} / ${total}`;

            $("resultBox").classList.remove("d-none");
            window.scrollTo({
                top: document.body.scrollHeight,
                behavior: "smooth"
            });
        }

        function escapeHtml(str) {
            return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }

        // nav buttons
        $("btnPrev").addEventListener("click", () => {
            if (current > 0) {
                current--;
                render();
            }
        });

        $("btnNext").addEventListener("click", () => {
            // On next, allow moving; if last, finish
            if (current < quiz.length - 1) {
                current++;
                render();
            } else {
                finish();
            }
        });

        $("btnRestart").addEventListener("click", () => {
            current = 0;
            selected = {};
            score = 0;
            $("resultBox").classList.add("d-none");
            render();
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        });

        // initial render
        render();
    </script>
</body>

</html>
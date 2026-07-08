<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Interactive NLP Activity (Orange Data Mining Focus)</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="<?= base_url('assets/bootstrap.4.5.2.min.css') ?>">

    <!-- Discussion Style -->
    <link rel="stylesheet" href="<?= base_url('assets/discussion-style.css') ?>">

    <!-- Highlight.js -->
    <link rel="stylesheet" href="<?= base_url('assets/highlights/atom-one-light.min.css') ?>">
    <script src="<?= base_url('assets/highlights/11.7.0-highlight.min.js') ?>"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            hljs.highlightAll();
        });
    </script>

    <style>
        /* Activity UI helpers (safe, minimal, responsive) */
        .activity-card {
            border: 1px solid rgba(0, 0, 0, .08);
            border-radius: 10px;
        }

        .badge-soft {
            background: rgba(0, 0, 0, .06);
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .4rem .6rem;
            border-radius: 999px;
            border: 1px solid rgba(0, 0, 0, .12);
            background: #fff;
            cursor: grab;
            user-select: none;
        }

        .pill:active {
            cursor: grabbing;
        }

        .dropzone {
            min-height: 64px;
            border: 2px dashed rgba(0, 0, 0, .18);
            border-radius: 10px;
            padding: .5rem;
            background: rgba(0, 0, 0, .02);
        }

        .dropzone.dragover {
            border-color: rgba(0, 0, 0, .35);
            background: rgba(0, 0, 0, .04);
        }

        .tiny {
            font-size: .92rem;
        }

        .mono {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        }

        .result-good {
            border-left: 5px solid #28a745;
        }

        .result-warn {
            border-left: 5px solid #ffc107;
        }

        .result-bad {
            border-left: 5px solid #dc3545;
        }

        .sticky-score {
            position: sticky;
            top: .75rem;
            z-index: 2;
            border: 1px solid rgba(0, 0, 0, .08);
            border-radius: 10px;
            background: #fff;
        }

        .kbd {
            border: 1px solid rgba(0, 0, 0, .15);
            border-bottom-width: 2px;
            border-radius: 6px;
            padding: 0 .35rem;
            background: rgba(0, 0, 0, .03);
            font-size: .9em;
        }
    </style>
</head>

<body>

    <header>
        <h1>Interactive NLP Activity</h1>
        <p>Learn NLP fundamentals + practice an Orange Data Mining workflow (sentiment analysis focus)</p>
    </header>

    <div class="container">

        <!-- Overview -->
        <div class="section">
            <h2>Learning Targets</h2>
            <ul>
                <li>Explain why text needs preprocessing (case, punctuation, stopwords, word forms).</li>
                <li>Identify the correct widget sequence for sentiment analysis in <strong>Orange</strong>.</li>
                <li>Practice labeling sentiment + interpreting common NLP pitfalls (negation, sarcasm, mixed sentiment).</li>
            </ul>

            <div class="alert alert-info mb-0">
                <strong>Orange Reminder:</strong>
                A typical sentiment pipeline looks like:
                <span class="mono">File → Corpus/Preprocess Text → TF-IDF → Learner → Test &amp; Score → Confusion Matrix</span>
            </div>
        </div>

        <!-- Score / Progress -->
        <div class="section">
            <div class="row">
                <div class="col-lg-4 mb-3">
                    <div class="sticky-score p-3 activity-card">
                        <h3 class="mb-2">Progress</h3>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="tiny text-muted">Sentiment Quiz Score</span>
                            <span class="badge badge-pill badge-dark" id="scoreBadge">0 / 0</span>
                        </div>
                        <div class="progress mt-2" style="height: 10px;">
                            <div class="progress-bar" role="progressbar" id="scoreBar" style="width: 0%"></div>
                        </div>

                        <hr>

                        <div class="tiny">
                            <div class="mb-2"><strong>Quick Tips</strong></div>
                            <ul class="mb-0 pl-3">
                                <li><span class="kbd">Tap</span> pills to move (mobile-friendly)</li>
                                <li>Watch out for <strong>not</strong> (negation flips meaning)</li>
                                <li>Sarcasm can trick bag-of-words models</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <!-- Task 1: Preprocessing Playground -->
                    <div class="activity-card p-3 mb-3">
                        <h2 class="mb-1">Task 1 — Preprocessing Playground</h2>
                        <p class="text-muted mb-3">Toggle common preprocessing steps and see how the text changes.</p>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="tiny text-muted mb-1">Input Text</label>
                                <textarea class="form-control" id="rawText" rows="5">NOT satisfied!!! Delivery was late, and the item is not good.</textarea>

                                <div class="mt-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="optLower" checked>
                                        <label class="custom-control-label" for="optLower">Lowercase</label>
                                    </div>

                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="optPunct" checked>
                                        <label class="custom-control-label" for="optPunct">Remove punctuation</label>
                                    </div>

                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="optStop" checked>
                                        <label class="custom-control-label" for="optStop">Remove stopwords (simple)</label>
                                    </div>

                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="optKeepNot" checked>
                                        <label class="custom-control-label" for="optKeepNot">
                                            Keep negation words (<span class="mono">not, no, never</span>)
                                        </label>
                                    </div>
                                </div>

                                <button class="btn btn-sm btn-primary mt-3" id="btnApplyPrep">Apply</button>
                                <button class="btn btn-sm btn-outline-secondary mt-3" id="btnResetPrep">Reset</button>
                            </div>

                            <div class="col-md-6">
                                <label class="tiny text-muted mb-1">Output Tokens (what models often see)</label>
                                <div class="p-2 dropzone" style="border-style: solid;" aria-label="Output Tokens">
                                    <div class="mono tiny" id="tokenOutput">(Click Apply)</div>
                                </div>

                                <div class="alert alert-warning mt-3 mb-0">
                                    <strong>Think:</strong> If <span class="mono">not</span> gets removed, what happens to sentiment?
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Task 2: Orange Pipeline Builder -->
                    <div class="activity-card p-3 mb-3">
                        <h2 class="mb-1">Task 2 — Build the Orange NLP Pipeline</h2>
                        <p class="text-muted mb-3">
                            Arrange the widgets in the correct order for <strong>sentiment analysis</strong>.
                            You can <strong>tap</strong> on mobile or <strong>drag &amp; drop</strong> on desktop.
                        </p>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h3 class="h6">Widget Bank</h3>
                                <div class="dropzone" id="bankZone">
                                    <!-- Pills injected by JS -->
                                </div>
                                <div class="tiny text-muted mt-2">
                                    Tip: The goal is a supervised workflow (needs labels).
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <h3 class="h6">Your Pipeline</h3>
                                <div class="dropzone" id="pipeZone"></div>

                                <div class="d-flex gap-2 mt-3">
                                    <button class="btn btn-sm btn-success mr-2" id="btnCheckPipeline">Check</button>
                                    <button class="btn btn-sm btn-outline-secondary" id="btnResetPipeline">Reset</button>
                                </div>

                                <div class="mt-3" id="pipelineFeedback"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Task 3: Sentiment Mini-Quiz -->
                    <div class="activity-card p-3">
                        <h2 class="mb-1">Task 3 — Sentiment Labeling Mini-Quiz</h2>
                        <p class="text-muted mb-3">Answer and get instant feedback. Aim for strong reasoning, not just guessing.</p>

                        <div id="quizContainer"></div>

                        <div class="d-flex flex-wrap mt-3">
                            <button class="btn btn-primary mr-2 mb-2" id="btnSubmitQuiz">Submit Quiz</button>
                            <button class="btn btn-outline-secondary mb-2" id="btnResetQuiz">Reset Quiz</button>
                        </div>

                        <div class="mt-3" id="quizSummary"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Task 4: Real Orange Execution (Classroom) -->
        <div class="section">
            <h2>Task 4 — Do it in Orange (Required Output)</h2>
            <p>
                Now reproduce what you learned in Orange Data Mining.
                Use a labeled dataset (<span class="mono">review</span> + <span class="mono">sentiment</span>).
            </p>

            <div class="table-responsive">
                <table class="table table-sm table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Widget</th>
                            <th>What to Do</th>
                            <th>Evidence to Submit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>File</strong></td>
                            <td>Load <span class="mono">reviews.csv</span></td>
                            <td>Screenshot showing columns + target</td>
                        </tr>
                        <tr>
                            <td><strong>Preprocess Text</strong></td>
                            <td>Lowercase, Tokenize, Stopwords, (optional) Lemmatize; be careful with <span class="mono">not</span></td>
                            <td>Screenshot of settings</td>
                        </tr>
                        <tr>
                            <td><strong>TF-IDF</strong></td>
                            <td>Transform corpus into numeric features</td>
                            <td>Screenshot showing number of features</td>
                        </tr>
                        <tr>
                            <td><strong>Naive Bayes</strong> + <strong>Logistic Regression</strong></td>
                            <td>Train 2 models</td>
                            <td>Screenshot of connected learners</td>
                        </tr>
                        <tr>
                            <td><strong>Test &amp; Score</strong></td>
                            <td>Use 10-fold cross-validation</td>
                            <td>Screenshot of scores</td>
                        </tr>
                        <tr>
                            <td><strong>Confusion Matrix</strong></td>
                            <td>Inspect which class is misclassified more</td>
                            <td>Screenshot + 2–3 sentence interpretation</td>
                        </tr>
                        <tr>
                            <td><strong>Word Cloud</strong></td>
                            <td>Compare common words per sentiment</td>
                            <td>Screenshot + top 5 keywords per class</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="alert alert-info mb-0">
                <strong>Submission:</strong> Workflow screenshot + metrics + 1 misclassification explanation (negation/mixed/sarcasm).
            </div>
        </div>

        <!-- Reflection -->
        <div class="section">
            <h2>Reflection (Short Answer)</h2>
            <div class="activity-card p-3">
                <div class="form-group">
                    <label><strong>1)</strong> Why can removing <span class="mono">not</span> harm sentiment accuracy?</label>
                    <textarea class="form-control" rows="2" placeholder="Write 1–3 sentences..."></textarea>
                </div>
                <div class="form-group">
                    <label><strong>2)</strong> Give one example of a “mixed sentiment” review and how you would label it.</label>
                    <textarea class="form-control" rows="2" placeholder="Write 1–3 sentences..."></textarea>
                </div>
                <div class="form-group mb-0">
                    <label><strong>3)</strong> What is one limitation of bag-of-words/TF-IDF for sentiment analysis?</label>
                    <textarea class="form-control" rows="2" placeholder="Write 1–3 sentences..."></textarea>
                </div>
            </div>
        </div>

        <footer class="mt-5">
            <p class="text-center text-muted mb-0">
                Interactive NLP Activity — Orange Data Mining Focus
            </p>
        </footer>

    </div>

    <script>
        // -----------------------------
        // Task 1: Preprocessing Playground
        // -----------------------------
        const STOPWORDS = new Set([
            "the", "is", "am", "are", "was", "were", "and", "or", "but", "a", "an", "to", "of", "for", "in", "on", "at", "with",
            "this", "that", "it", "as", "i", "you", "we", "they", "he", "she", "them", "my", "your", "our", "their", "so", "very"
        ]);
        const NEGATIONS = new Set(["not", "no", "never"]);

        function tokenize(text) {
            return text.split(/\s+/).filter(Boolean);
        }

        function preprocessText(raw, opts) {
            let t = raw;

            if (opts.lowercase) t = t.toLowerCase();

            if (opts.removePunct) {
                // keep apostrophes inside words, remove most punctuation
                t = t.replace(/[^\w\s']/g, " ");
            }

            let tokens = tokenize(t);

            if (opts.removeStopwords) {
                tokens = tokens.filter(w => {
                    if (opts.keepNegations && NEGATIONS.has(w)) return true;
                    return !STOPWORDS.has(w);
                });
            }

            // very light "lemma-like" normalization (demo only)
            // (Orange can do proper lemmatization; here we just trim common suffixes)
            tokens = tokens.map(w => w.replace(/(ing|ed|s)$/g, (m) => (w.length > 4 ? "" : m)));

            return tokens;
        }

        const rawTextEl = document.getElementById("rawText");
        const tokenOutputEl = document.getElementById("tokenOutput");

        document.getElementById("btnApplyPrep").addEventListener("click", () => {
            const opts = {
                lowercase: document.getElementById("optLower").checked,
                removePunct: document.getElementById("optPunct").checked,
                removeStopwords: document.getElementById("optStop").checked,
                keepNegations: document.getElementById("optKeepNot").checked
            };
            const tokens = preprocessText(rawTextEl.value, opts);
            tokenOutputEl.textContent = tokens.length ? tokens.join("  |  ") : "(no tokens)";
        });

        document.getElementById("btnResetPrep").addEventListener("click", () => {
            rawTextEl.value = "NOT satisfied!!! Delivery was late, and the item is not good.";
            document.getElementById("optLower").checked = true;
            document.getElementById("optPunct").checked = true;
            document.getElementById("optStop").checked = true;
            document.getElementById("optKeepNot").checked = true;
            tokenOutputEl.textContent = "(Click Apply)";
        });

        // -----------------------------
        // Task 2: Pipeline Builder (tap + drag/drop)
        // -----------------------------
        const bankZone = document.getElementById("bankZone");
        const pipeZone = document.getElementById("pipeZone");
        const pipelineFeedback = document.getElementById("pipelineFeedback");

        const WIDGETS = [
            "File",
            "Corpus",
            "Preprocess Text",
            "TF-IDF",
            "Naive Bayes",
            "Logistic Regression",
            "Test & Score",
            "Confusion Matrix",
            "Word Cloud"
        ];

        // One valid supervised sentiment flow (allow Corpus OR direct Preprocess Text; allow either learner order)
        const ACCEPTED_CORE_ORDER = ["File", "Preprocess Text", "TF-IDF", "Learner", "Test & Score", "Confusion Matrix"];

        function makePill(label) {
            const el = document.createElement("div");
            el.className = "pill mb-2 mr-2";
            el.draggable = true;
            el.dataset.label = label;
            el.innerHTML = `<span class="badge badge-pill badge-soft">${label}</span> <span class="text-muted tiny">tap / drag</span>`;

            // Tap to move (mobile)
            el.addEventListener("click", () => {
                const parent = el.parentElement.id;
                if (parent === "bankZone") pipeZone.appendChild(el);
                else bankZone.appendChild(el);
            });

            // Drag events
            el.addEventListener("dragstart", (e) => {
                e.dataTransfer.setData("text/plain", label);
                e.dataTransfer.effectAllowed = "move";
                el.classList.add("border");
            });
            el.addEventListener("dragend", () => el.classList.remove("border"));

            return el;
        }

        function resetPipeline() {
            bankZone.innerHTML = "";
            pipeZone.innerHTML = "";
            pipelineFeedback.innerHTML = "";
            WIDGETS.forEach(w => bankZone.appendChild(makePill(w)));
        }

        function enableDropzone(zone) {
            zone.addEventListener("dragover", (e) => {
                e.preventDefault();
                zone.classList.add("dragover");
            });
            zone.addEventListener("dragleave", () => zone.classList.remove("dragover"));
            zone.addEventListener("drop", (e) => {
                e.preventDefault();
                zone.classList.remove("dragover");

                // Move the actual pill by label
                const label = e.dataTransfer.getData("text/plain");
                const allPills = [...document.querySelectorAll(".pill")];
                const pill = allPills.find(p => p.dataset.label === label);
                if (pill) zone.appendChild(pill);
            });
        }

        enableDropzone(bankZone);
        enableDropzone(pipeZone);
        resetPipeline();

        function getPipelineLabels() {
            return [...pipeZone.querySelectorAll(".pill")].map(p => p.dataset.label);
        }

        function checkPipeline() {
            const labels = getPipelineLabels();

            // Basic checks
            const hasFile = labels.includes("File");
            const hasPrep = labels.includes("Preprocess Text");
            const hasTfidf = labels.includes("TF-IDF");
            const hasTest = labels.includes("Test & Score");
            const hasCM = labels.includes("Confusion Matrix");
            const hasLearner = labels.includes("Naive Bayes") || labels.includes("Logistic Regression");

            if (!hasFile || !hasPrep || !hasTfidf || !hasLearner || !hasTest || !hasCM) {
                pipelineFeedback.innerHTML = `
          <div class="alert alert-danger result-bad mb-0">
            <strong>Not complete yet.</strong><br>
            Required: <span class="mono">File, Preprocess Text, TF-IDF, (at least 1 Learner), Test &amp; Score, Confusion Matrix</span>
          </div>
        `;
                return;
            }

            // Order check (flexible with extra widgets)
            // Reduce to a "core sequence" representation
            const core = [];
            labels.forEach(l => {
                if (l === "File") core.push("File");
                if (l === "Preprocess Text") core.push("Preprocess Text");
                if (l === "TF-IDF") core.push("TF-IDF");
                if (l === "Naive Bayes" || l === "Logistic Regression") core.push("Learner");
                if (l === "Test & Score") core.push("Test & Score");
                if (l === "Confusion Matrix") core.push("Confusion Matrix");
            });

            // Remove duplicate learners in core for comparison
            const coreDedup = core.filter((v, i, arr) => !(v === "Learner" && arr[i - 1] === "Learner"));

            const isInOrder = ACCEPTED_CORE_ORDER.every((step, idx) => coreDedup[idx] === step);

            if (isInOrder) {
                pipelineFeedback.innerHTML = `
          <div class="alert alert-success result-good mb-0">
            <strong>Correct core order!</strong><br>
            Nice — this matches a supervised sentiment workflow in Orange.<br>
            <span class="tiny text-muted">Optional extras: Corpus / Word Cloud can be placed where appropriate.</span>
          </div>
        `;
            } else {
                pipelineFeedback.innerHTML = `
          <div class="alert alert-warning result-warn mb-0">
            <strong>Almost.</strong><br>
            Your core widgets are present, but the order looks off.<br>
            Hint: <span class="mono">Preprocess Text</span> should come before <span class="mono">TF-IDF</span>, and evaluation comes after the learner.
          </div>
        `;
            }
        }

        document.getElementById("btnCheckPipeline").addEventListener("click", checkPipeline);
        document.getElementById("btnResetPipeline").addEventListener("click", resetPipeline);

        // -----------------------------
        // Task 3: Sentiment Quiz (instant feedback on submit)
        // -----------------------------
        const quizContainer = document.getElementById("quizContainer");
        const quizSummary = document.getElementById("quizSummary");
        const scoreBadge = document.getElementById("scoreBadge");
        const scoreBar = document.getElementById("scoreBar");

        const QUIZ = [{
                id: "q1",
                text: "Review: “Fast delivery and excellent packaging.”",
                choices: ["Positive", "Negative", "Neutral", "Mixed"],
                answer: "Positive",
                tip: "Words like “excellent” often indicate positive sentiment."
            },
            {
                id: "q2",
                text: "Review: “The item stopped working after one day.”",
                choices: ["Positive", "Negative", "Neutral", "Mixed"],
                answer: "Negative",
                tip: "Product failure is typically negative."
            },
            {
                id: "q3",
                text: "Review: “The delivery was fast, but the quality is disappointing.” (Choose Positive/Negative)",
                choices: ["Positive", "Negative", "Neutral", "Mixed"],
                answer: "Negative",
                tip: "When forced to pick, the stronger complaint often drives the label."
            },
            {
                id: "q4",
                text: "Review: “Not good. Not satisfied.” What’s the key token to keep during preprocessing?",
                choices: ["not", "good", "satisfied", "the"],
                answer: "not",
                tip: "Removing negation can flip meaning."
            },
            {
                id: "q5",
                text: "Review: “Great, just great... it broke immediately.” Why can models fail here?",
                choices: ["Sarcasm", "Spelling", "Too short", "Stopwords"],
                answer: "Sarcasm",
                tip: "Bag-of-words sees “great” and may miss sarcastic context."
            }
        ];

        function renderQuiz() {
            quizContainer.innerHTML = "";
            QUIZ.forEach((q, idx) => {
                const card = document.createElement("div");
                card.className = "card mb-3";
                card.innerHTML = `
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
              <h3 class="h6 mb-2">Q${idx + 1}. ${q.text}</h3>
              <span class="badge badge-pill badge-light">Sentiment</span>
            </div>
            <div class="mt-2" role="group" aria-label="Question ${idx + 1}">
              ${q.choices.map((c, i) => `
                <div class="custom-control custom-radio">
                  <input type="radio" id="${q.id}_${i}" name="${q.id}" class="custom-control-input" value="${c}">
                  <label class="custom-control-label" for="${q.id}_${i}">${c}</label>
                </div>
              `).join("")}
            </div>
            <div class="mt-3 d-none" id="${q.id}_fb"></div>
          </div>
        `;
                quizContainer.appendChild(card);
            });

            quizSummary.innerHTML = "";
            updateScore(0, QUIZ.length);
        }

        function getSelected(name) {
            const el = document.querySelector(`input[name="${name}"]:checked`);
            return el ? el.value : null;
        }

        function updateScore(score, total) {
            scoreBadge.textContent = `${score} / ${total}`;
            const pct = total ? Math.round((score / total) * 100) : 0;
            scoreBar.style.width = pct + "%";
            scoreBar.setAttribute("aria-valuenow", pct);
        }

        function submitQuiz() {
            let score = 0;

            QUIZ.forEach((q) => {
                const sel = getSelected(q.id);
                const fb = document.getElementById(`${q.id}_fb`);
                fb.classList.remove("d-none");

                if (!sel) {
                    fb.className = "alert alert-warning mb-0";
                    fb.innerHTML = `<strong>No answer selected.</strong> <span class="tiny text-muted">Tip: ${q.tip}</span>`;
                    return;
                }

                if (sel === q.answer) {
                    score += 1;
                    fb.className = "alert alert-success mb-0";
                    fb.innerHTML = `<strong>Correct.</strong> <span class="tiny text-muted">${q.tip}</span>`;
                } else {
                    fb.className = "alert alert-danger mb-0";
                    fb.innerHTML = `<strong>Incorrect.</strong> Correct answer: <span class="mono">${q.answer}</span><br><span class="tiny text-muted">${q.tip}</span>`;
                }
            });

            updateScore(score, QUIZ.length);

            quizSummary.innerHTML = `
        <div class="alert alert-info mb-0">
          <strong>Quiz Complete:</strong> You scored <span class="mono">${score}/${QUIZ.length}</span>.<br>
          <span class="tiny text-muted">Now apply the same thinking in Orange: preprocessing → TF-IDF → learners → evaluation.</span>
        </div>
      `;
        }

        function resetQuiz() {
            renderQuiz();
        }

        document.getElementById("btnSubmitQuiz").addEventListener("click", submitQuiz);
        document.getElementById("btnResetQuiz").addEventListener("click", resetQuiz);

        renderQuiz();
    </script>

</body>

</html>
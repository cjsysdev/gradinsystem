<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Introduction to Business Intelligence</title>

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
    <header class="py-4">
        <div class="container">
            <h1 class="mb-1">Natural Language Processing (NLP) Using Orange</h1>
            <p class="mb-0">A hands-on, no-code workflow for text cleaning, vectorization, modeling, and insights</p>
        </div>
    </header>

    <div class="content mt-4 mb-5">
        <div class="container">

            <!-- Section: Overview -->
            <div class="section card p-4 mb-4">
                <h2>Overview</h2>
                <p>
                    Natural Language Processing (NLP) is the field of working with human language using computers—turning raw text
                    (reviews, comments, essays, messages) into information you can analyze. In Orange Data Mining, you can build
                    an NLP pipeline by connecting visual widgets: load text, clean it, convert it to numbers, and apply analytics
                    like classification, clustering, topic modeling, and sentiment analysis.
                </p>

                <div class="alert alert-info mb-0">
                    <strong>Big idea:</strong> Machines cannot “understand” raw text directly. We must convert text into numeric
                    features (vectors) before most models can learn patterns.
                </div>
            </div>

            <!-- Section: Learning Objectives -->
            <div class="section card p-4 mb-4">
                <h2>Learning Objectives</h2>
                <ul class="mb-0">
                    <li>Explain what NLP is and why text must be converted into numerical representations.</li>
                    <li>Build an end-to-end NLP workflow in Orange (load → preprocess → vectorize → model → evaluate).</li>
                    <li>Apply multiple text analysis tasks: topic modeling, clustering, and text classification.</li>
                    <li>Interpret results using visual widgets (word clouds, topic terms, confusion matrix, evaluation scores).</li>
                    <li>Troubleshoot common Orange NLP issues (missing add-ons, “no internet” embedding errors, memory limits).</li>
                </ul>
            </div>

            <!-- Section: Key Concepts -->
            <div class="section card p-4 mb-4">
                <h2>Key Concepts You Must Know</h2>

                <h5 class="mt-3">1) Corpus</h5>
                <p>
                    A <strong>corpus</strong> is a collection of text documents. In Orange, many NLP widgets output a “Corpus”
                    object rather than a simple table.
                </p>

                <h5 class="mt-3">2) Preprocessing</h5>
                <p>
                    <strong>Text preprocessing</strong> prepares text for analysis: lowercasing, removing punctuation, removing
                    stopwords (common words like “the”, “is”), stemming/lemmatization, and filtering rare/common terms.
                </p>

                <h5 class="mt-3">3) Vectorization</h5>
                <p>
                    <strong>Vectorization</strong> converts text to numbers. Common approaches:
                </p>
                <ul>
                    <li><strong>Bag of Words (BoW):</strong> counts of words per document</li>
                    <li><strong>TF-IDF:</strong> weighs words higher if they’re important in a document but not common overall</li>
                    <li><strong>Embeddings:</strong> dense vectors capturing semantic meaning (often requires downloading models)</li>
                </ul>

                <h5 class="mt-3">4) Tasks</h5>
                <ul class="mb-0">
                    <li><strong>Topic Modeling:</strong> find themes (topics) in documents</li>
                    <li><strong>Clustering:</strong> group similar documents</li>
                    <li><strong>Classification:</strong> predict labels (e.g., pass/fail, sentiment)</li>
                    <li><strong>Similarity Search:</strong> find texts that look alike</li>
                </ul>
            </div>

            <!-- Section: What You Need -->
            <div class="section card p-4 mb-4">
                <h2>What You Need Before You Start</h2>
                <ol class="mb-0">
                    <li>
                        <strong>Orange Data Mining</strong> installed on your PC.
                    </li>
                    <li>
                        <strong>Orange Text add-on</strong> installed:
                        <div class="alert alert-warning mt-2 mb-0">
                            In Orange: <strong>Options</strong> → <strong>Add-ons</strong> → search for <strong>Orange3-Text</strong> → Install → Restart.
                        </div>
                    </li>
                    <li>
                        A text dataset (CSV/Excel) with at least:
                        <ul>
                            <li>One column containing the text (e.g., <code>comment</code>, <code>review</code>, <code>essay</code>)</li>
                            <li>Optional label column (e.g., <code>sentiment</code>, <code>category</code>) if doing classification</li>
                        </ul>
                    </li>
                </ol>
            </div>

            <!-- Section: Recommended Dataset Structure -->
            <div class="section card p-4 mb-4">
                <h2>Recommended Dataset Structure</h2>
                <p>Example CSV columns:</p>
                <pre><code class="language-none">id, text, label
1, "The instructor explained clearly and the quiz was easy.", positive
2, "I got lost during the lecture and the exam felt unfair.", negative
3, "The activity helped me understand linked lists.", positive</code></pre>

                <div class="alert alert-info mb-0">
                    If you don’t have labels, that’s fine—Orange can still do topic modeling and clustering.
                </div>
            </div>

            <!-- Section: Core Workflow -->
            <div class="section card p-4 mb-4">
                <h2>Core NLP Workflow in Orange (Recommended)</h2>
                <p>Think of this as your “default pipeline”:</p>

                <div class="alert alert-secondary">
                    <strong>File</strong> → <strong>Select Columns</strong> → <strong>Corpus</strong> → <strong>Preprocess Text</strong> →
                    <strong>Bag of Words / TF-IDF</strong> → (Choose your task widgets)
                </div>

                <h5 class="mt-3">Widget Purposes</h5>
                <ul class="mb-0">
                    <li><strong>File:</strong> load your CSV/Excel</li>
                    <li><strong>Select Columns:</strong> choose which column is text and which is label/metadata</li>
                    <li><strong>Corpus:</strong> convert table rows into a text corpus (documents)</li>
                    <li><strong>Preprocess Text:</strong> clean and normalize the text</li>
                    <li><strong>Bag of Words / TF-IDF:</strong> transform corpus into numeric features</li>
                </ul>
            </div>

            <!-- Section: Preprocess Text Settings -->
            <div class="section card p-4 mb-4">
                <h2>Preprocess Text: Suggested Settings</h2>
                <p>
                    These settings usually work well for student feedback, reviews, and short comments.
                </p>

                <ul>
                    <li><strong>Lowercase</strong> ✅</li>
                    <li><strong>Remove punctuation</strong> ✅</li>
                    <li><strong>Remove stopwords</strong> ✅ (English; add Filipino/Cebuano custom stopwords if needed)</li>
                    <li><strong>Lemmatization</strong> ✅ (prefer over stemming when available)</li>
                    <li><strong>Filter tokens by length</strong> ✅ (e.g., min 2)</li>
                    <li><strong>Keep only alphabetic tokens</strong> ✅ (optional; may remove useful items like “SQL”)</li>
                </ul>

                <div class="alert alert-warning mb-0">
                    <strong>Tip:</strong> If your domain uses abbreviations (e.g., SQL, C++, AI), avoid filters that remove tokens with symbols.
                </div>
            </div>

            <!-- Section: Task 1 - Topic Modeling -->
            <div class="section card p-4 mb-4">
                <h2>Task 1: Topic Modeling (Discover Themes)</h2>
                <p>
                    Topic modeling finds hidden themes in text (e.g., “teaching style”, “difficulty”, “activities”, “attendance reasons”).
                    In Orange, you can use topic modeling widgets (commonly based on LDA) and visualize top words per topic.
                </p>

                <h5 class="mt-3">Suggested Widget Flow</h5>
                <div class="alert alert-secondary">
                    File → Select Columns → Corpus → Preprocess Text → TF-IDF (or BoW) → Topic Modeling → Word Cloud / Data Table
                </div>

                <h5 class="mt-3">How to Interpret Topics</h5>
                <ul class="mb-0">
                    <li>A topic is usually shown as a list of top terms (words) with weights.</li>
                    <li>Your job is to <strong>name the topic</strong> based on its top terms (human interpretation is required).</li>
                    <li>Use Word Cloud to quickly “see” dominant terms per topic.</li>
                </ul>
            </div>

            <!-- Section: Task 2 - Clustering -->
            <div class="section card p-4 mb-4">
                <h2>Task 2: Clustering (Group Similar Documents)</h2>
                <p>
                    Clustering groups similar texts without labels. This is useful when you want to group student comments into
                    categories automatically (e.g., “complaints”, “praise”, “requests”).
                </p>

                <h5 class="mt-3">Suggested Widget Flow</h5>
                <div class="alert alert-secondary">
                    File → Select Columns → Corpus → Preprocess Text → TF-IDF → Distance → Hierarchical Clustering (or k-Means) → Scatter Plot / Data Table
                </div>

                <h5 class="mt-3">Practical Tips</h5>
                <ul class="mb-0">
                    <li>Try <strong>Hierarchical Clustering</strong> to see a dendrogram (tree) and choose cut levels.</li>
                    <li>Try <strong>k-Means</strong> if you already have a guess on the number of clusters.</li>
                    <li>After clustering, inspect example texts from each cluster in a Data Table.</li>
                </ul>
            </div>

            <!-- Section: Task 3 - Text Classification -->
            <div class="section card p-4 mb-4">
                <h2>Task 3: Text Classification (Predict a Label)</h2>
                <p>
                    Classification predicts a known label (e.g., sentiment positive/negative, “pass/fail”, or “topic category”).
                    This requires a label column.
                </p>

                <h5 class="mt-3">Suggested Widget Flow</h5>
                <div class="alert alert-secondary">
                    File → Select Columns → Corpus → Preprocess Text → TF-IDF → (Learners) → Test & Score → Confusion Matrix
                </div>

                <h5 class="mt-3">Recommended Learners to Try</h5>
                <ul>
                    <li><strong>Logistic Regression</strong> (strong baseline)</li>
                    <li><strong>Naive Bayes</strong> (fast; often good for text)</li>
                    <li><strong>SVM</strong> (can perform very well with TF-IDF)</li>
                    <li><strong>Random Forest</strong> (sometimes weaker on sparse text; still worth testing)</li>
                </ul>

                <h5 class="mt-3">Evaluation Metrics (What to Look At)</h5>
                <ul class="mb-0">
                    <li><strong>Accuracy:</strong> overall correctness (good if classes are balanced)</li>
                    <li><strong>Precision:</strong> when the model predicts a class, how often it’s right</li>
                    <li><strong>Recall:</strong> how many real items of a class it found</li>
                    <li><strong>F1-score:</strong> balance between precision and recall</li>
                </ul>

                <div class="alert alert-info mt-3 mb-0">
                    If your classes are imbalanced (e.g., many “present” but few “absent”), rely more on <strong>F1</strong> and the <strong>Confusion Matrix</strong>.
                </div>
            </div>

            <!-- Section: Hands-on Activity -->
            <div class="section card p-4 mb-4">
                <h2>Hands-on Activity</h2>
                <p>
                    Build a complete Orange NLP workflow and report results with screenshots of widget connections and outputs.
                </p>

                <h5 class="mt-3">Part A: Prepare Your Dataset</h5>
                <ol>
                    <li>Create or obtain a CSV/Excel file with at least one text column (e.g., <code>text</code>).</li>
                    <li>(Optional) Add a label column (e.g., <code>sentiment</code>, <code>category</code>, <code>result</code>).</li>
                </ol>

                <h5 class="mt-3">Part B: Build the Pipeline (Required)</h5>
                <ol>
                    <li>Connect: <strong>File → Select Columns → Corpus → Preprocess Text</strong></li>
                    <li>Add <strong>TF-IDF</strong> after preprocessing.</li>
                    <li>Show one visualization output (choose at least one): <strong>Word Cloud</strong> or <strong>Data Table</strong>.</li>
                </ol>

                <h5 class="mt-3">Part C: Choose ONE Analysis Track</h5>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 p-3">
                            <h6 class="mb-2"><strong>Track 1: Topic Modeling</strong></h6>
                            <ul class="mb-0">
                                <li>Add <strong>Topic Modeling</strong></li>
                                <li>List topics + top terms</li>
                                <li>Name each topic</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 p-3">
                            <h6 class="mb-2"><strong>Track 2: Clustering</strong></h6>
                            <ul class="mb-0">
                                <li>Add <strong>Distance</strong></li>
                                <li>Add <strong>Hierarchical Clustering</strong></li>
                                <li>Explain 2–3 clusters</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 p-3">
                            <h6 class="mb-2"><strong>Track 3: Classification</strong></h6>
                            <ul class="mb-0">
                                <li>Add 2 learners</li>
                                <li>Add <strong>Test & Score</strong></li>
                                <li>Add <strong>Confusion Matrix</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <h5 class="mt-3">Part D: Submit These Outputs</h5>
                <ul class="mb-0">
                    <li>Screenshot of your Orange workflow (widgets connected).</li>
                    <li>Screenshot of preprocessing settings.</li>
                    <li>Results screenshot (topics/clusters/evaluation table).</li>
                    <li>Short interpretation (5–10 sentences) explaining insights.</li>
                </ul>
            </div>

            <!-- Section: Example Output (Guide) -->
            <div class="section card p-4 mb-4">
                <h2>Output Example Guide (What a Good Answer Looks Like)</h2>
                <p><strong>If Topic Modeling:</strong></p>
                <ul>
                    <li><strong>Topic 1 (Assessment Difficulty):</strong> exam, quiz, hard, score, time</li>
                    <li><strong>Topic 2 (Teaching Style):</strong> explain, clear, examples, instructor, steps</li>
                    <li><strong>Topic 3 (Activities & Practice):</strong> activity, hands-on, practice, lab, group</li>
                </ul>

                <p class="mb-0"><strong>Interpretation sample:</strong> “Most comments focus on assessments and teaching clarity. Students who mention
                    ‘time’ and ‘hard’ often describe difficulty during exams, while those mentioning ‘examples’ and ‘steps’ describe clear explanations.
                    This suggests improving review materials and providing practice tests could address the biggest pain points.”
                </p>
            </div>

            <!-- Section: Troubleshooting -->
            <div class="section card p-4 mb-4">
                <h2>Troubleshooting Common Issues in Orange NLP</h2>

                <h5 class="mt-3">1) “I can’t find Corpus / Preprocess Text widgets”</h5>
                <div class="alert alert-warning">
                    Install the <strong>Orange Text</strong> add-on: Options → Add-ons → <strong>Orange3-Text</strong> → Install → Restart.
                </div>

                <h5 class="mt-3">2) “No internet connection. Please establish a connection or use another vectorizer.”</h5>
                <p>
                    This usually happens when using <strong>Document Embedding</strong> or embedding-based widgets that need to download a model
                    (or connect to a service). Even if your internet is working, Orange may be blocked by firewall/proxy or the model download fails.
                </p>
                <ul>
                    <li><strong>Quick fix:</strong> Use <strong>TF-IDF</strong> instead of embeddings (works fully offline).</li>
                    <li><strong>Network fix:</strong> Allow Orange/Python through firewall; try a different network (hotspot).</li>
                    <li><strong>Practical fix:</strong> Download models ahead of time (if the widget supports cached models).</li>
                    <li><strong>Stability fix:</strong> If it stops at a certain percent (e.g., 82%), it’s commonly a download/caching issue.</li>
                </ul>
                <div class="alert alert-info mb-0">
                    For most class projects, <strong>TF-IDF + Logistic Regression/Naive Bayes/SVM</strong> is already a strong and accepted baseline.
                </div>

                <h5 class="mt-4">3) “My workflow is slow / Orange freezes”</h5>
                <ul class="mb-0">
                    <li>Reduce dataset size (sample 1,000–5,000 rows first).</li>
                    <li>Limit vocabulary in TF-IDF (e.g., remove extremely rare and extremely common terms).</li>
                    <li>Disable heavy preprocessing steps you don’t need.</li>
                </ul>
            </div>

            <!-- Section: Reflection Questions -->
            <div class="section card p-4 mb-4">
                <h2>Reflection Questions</h2>
                <ol class="mb-0">
                    <li>Why do we need preprocessing before TF-IDF or modeling?</li>
                    <li>Which preprocessing step made the biggest difference in your results, and why?</li>
                    <li>Compare TF-IDF and Embeddings: what is one strength and one weakness of each?</li>
                    <li>If your topics/clusters were messy, what would you change in your pipeline?</li>
                    <li>How can your findings be used to improve teaching, assessments, or student support?</li>
                </ol>
            </div>

            <!-- Section: Quick Checklist -->
            <div class="section card p-4">
                <h2>Quick Checklist</h2>
                <ul class="mb-0">
                    <li>✅ Orange Text add-on installed</li>
                    <li>✅ Text column selected correctly</li>
                    <li>✅ Preprocess Text configured</li>
                    <li>✅ TF-IDF (or BoW) connected</li>
                    <li>✅ At least one analysis track completed</li>
                    <li>✅ Screenshots + short interpretation written</li>
                </ul>
            </div>

        </div>
    </div>

    <?php $this->load->view('web_to_image'); ?>

    <!-- Scripts -->
    <script src="<?php echo base_url('assets/bootstrap-4.5.2/js/jquery-3.5.1.min.js'); ?>"></script>
    <script src="<?php echo base_url('assets/bootstrap-4.5.2/js/bootstrap.bundle.min.js'); ?>"></script>

    <script>
        hljs.highlightAll();
    </script>
</body>

</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Model Comparison – Evaluating Machine Learning Models</title>


    <!-- Bootstrap 4.5.2 -->
    <link rel="stylesheet" href="<?php echo base_url('assets/bootstrap.min.css'); ?>">
    <!-- Custom Style -->
    <link rel="stylesheet" href="<?php echo base_url('assets/discussion-style.css'); ?>">
    <!-- Highlight.js -->
    <link rel="stylesheet" href="<?= base_url('assets/highlights/atom-one-light.min.css') ?>">
    <script src="<?= base_url('assets/highlights/11.7.0-highlight.min.js') ?>"></script>
    <script>
        hljs.highlightAll();
    </script>

    <style>
        /* ── Base ── */
        body {
            font-family: 'Georgia', serif;
            background: #f4f6fb;
            color: #2d3142;
        }

        /* ── Header ── */
        .discussion-header {
            background: linear-gradient(135deg, #1a3a5c 0%, #2874a6 100%);
            color: #fff;
            padding: 3rem 2rem 2.5rem;
            text-align: center;
        }

        .discussion-header h1 {
            font-size: 2.2rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .discussion-header .subtitle {
            font-size: 1.1rem;
            opacity: 0.85;
            font-style: italic;
        }

        /* ── Content wrapper ── */
        .content {
            max-width: 860px;
            margin: 0 auto;
        }

        /* ── Section cards ── */
        .section {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.07);
            padding: 1.8rem 2rem;
            margin-bottom: 1.5rem;
        }

        .section h2 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #1a3a5c;
            border-left: 5px solid #2874a6;
            padding-left: 0.75rem;
            margin-bottom: 1rem;
        }

        .section p,
        .section li {
            font-size: 0.97rem;
            line-height: 1.75;
            color: #3b4252;
        }

        .section ul {
            padding-left: 1.4rem;
        }

        .section ul li {
            margin-bottom: 0.4rem;
        }

        /* ── Objectives list ── */
        .objectives-list li {
            padding: 0.35rem 0;
            border-bottom: 1px dashed #e0e4ef;
        }

        .objectives-list li:last-child {
            border-bottom: none;
        }

        /* ── Formula box ── */
        .formula-box {
            background: #eaf3fb;
            border-left: 4px solid #2874a6;
            border-radius: 6px;
            padding: 0.7rem 1rem;
            font-family: 'Courier New', monospace;
            font-size: 0.95rem;
            color: #1a3a5c;
            margin: 0.8rem 0;
        }

        /* ── Metric cards ── */
        .metric-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .metric-card {
            background: #f0f5ff;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            border-top: 4px solid #2874a6;
        }

        .metric-card .metric-name {
            font-weight: 700;
            font-size: 1rem;
            color: #1a3a5c;
            margin-bottom: 0.4rem;
        }

        .metric-card .metric-desc {
            font-size: 0.82rem;
            color: #555;
        }

        /* ── Comparison table ── */
        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            font-size: 0.93rem;
        }

        .comparison-table th {
            background: #1a3a5c;
            color: #fff;
            padding: 0.7rem 1rem;
            text-align: center;
        }

        .comparison-table td {
            padding: 0.65rem 1rem;
            text-align: center;
            border-bottom: 1px solid #e0e4ef;
        }

        .comparison-table tr:nth-child(even) td {
            background: #f4f6fb;
        }

        .highlight-row td {
            font-weight: 600;
            color: #1a6a3a;
        }

        /* ── Decision table ── */
        .decision-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            font-size: 0.93rem;
        }

        .decision-table th {
            background: #2874a6;
            color: #fff;
            padding: 0.65rem 1rem;
        }

        .decision-table td {
            padding: 0.65rem 1rem;
            border-bottom: 1px solid #e0e4ef;
        }

        .decision-table tr:hover td {
            background: #eef4fb;
        }

        /* ── Alert ── */
        .alert-tip {
            background: #fff8e1;
            border-left: 5px solid #f9a825;
            border-radius: 6px;
            padding: 0.9rem 1.2rem;
            font-size: 0.93rem;
            color: #5d4037;
        }

        .alert-tip strong {
            color: #e65100;
        }

        /* ── Reflection ── */
        .reflection-box {
            background: #e8f5e9;
            border-left: 5px solid #388e3c;
            border-radius: 6px;
            padding: 1rem 1.2rem;
        }

        .reflection-box p {
            margin-bottom: 0.5rem;
            font-style: italic;
        }

        /* ── Footer ── */
        footer {
            text-align: center;
            font-size: 0.82rem;
            color: #888;
            padding: 1.5rem 0 2rem;
        }
    </style>
</head>

<body>

    <!-- ══ HEADER ══ -->
    <div class="discussion-header">
        <h1>📊 Model Comparison</h1>
        <p class="subtitle">Evaluating Machine Learning Models Using Metrics</p>
    </div>

    <!-- ══ CONTENT ══ -->
    <div class="content mt-4 mb-5 px-3">

        <!-- Learning Objectives -->
        <div class="section">
            <h2>🎯 Learning Objectives</h2>
            <ul class="objectives-list">
                <li>Define model comparison and explain its role in machine learning.</li>
                <li>Identify the importance of evaluating models before deployment.</li>
                <li>Describe how cross-validation works and list its common types.</li>
                <li>Compute and interpret Accuracy, Precision, Recall, and F1 Score.</li>
                <li>Compare model performance using a metrics table and justify model selection.</li>
                <li>Apply the correct metric based on the problem context and data characteristics.</li>
            </ul>
        </div>

        <!-- What is Model Comparison? -->
        <div class="section">
            <h2>📖 What is Model Comparison?</h2>
            <p>
                <strong>Model comparison</strong> is the process of evaluating different machine learning models to determine which one performs best for a given problem. Instead of choosing a model arbitrarily, data scientists use systematic measurement techniques to objectively determine which model is most accurate and reliable.
            </p>
            <p>
                Think of it like choosing the best basketball player for your team — you don't just guess; you compare their stats: shooting percentage, assists, rebounds. Model comparison works the same way, but with numbers from your data.
            </p>
        </div>

        <!-- Why It Matters -->
        <div class="section">
            <h2>💡 Why Is Model Comparison Important?</h2>
            <p>Comparing models before deployment is a critical step in any machine learning pipeline. Here's why:</p>
            <ul>
                <li><strong>Ensures best model selection</strong> — Not all models fit all data equally well.</li>
                <li><strong>Improves prediction accuracy</strong> — Picking a well-evaluated model means better real-world results.</li>
                <li><strong>Avoids overfitting or underfitting</strong> — A model that works on training data must also work on unseen data.</li>
                <li><strong>Reveals model strengths and weaknesses</strong> — Different models excel under different conditions.</li>
            </ul>
        </div>

        <!-- Cross-Validation -->
        <div class="section">
            <h2>🔁 Cross-Validation</h2>
            <p>
                <strong>Cross-validation</strong> is a technique used to evaluate model performance by splitting data into multiple parts. Instead of training and testing on the same data (which gives an overly optimistic result), the model is trained and tested multiple times on different data splits to ensure reliable performance estimates.
            </p>

            <h2 style="border-left-color:#e67e22; font-size:1.1rem; margin-top:1.2rem;">Types of Cross-Validation</h2>
            <ul>
                <li>
                    <strong>K-Fold Cross-Validation</strong> — The dataset is divided into <em>k</em> equal parts. The model trains on <em>k-1</em> parts and tests on the remaining 1 part, repeated <em>k</em> times.
                </li>
                <li>
                    <strong>Stratified K-Fold</strong> — Like K-Fold, but ensures each fold has a proportional representation of each class — especially useful for imbalanced datasets.
                </li>
                <li>
                    <strong>Leave-One-Out Cross-Validation (LOOCV)</strong> — An extreme form of K-Fold where each data point is used as the test set once. Best for small datasets.
                </li>
                <li>
                    <strong>Train-Test Split</strong> — The simplest method: split data into a training set (e.g., 80%) and a test set (20%). Quick but less reliable.
                </li>
            </ul>
        </div>

        <!-- Common Metrics -->
        <div class="section">
            <h2>📏 Common Evaluation Metrics</h2>
            <p>Four key metrics are used to measure how well a classification model performs:</p>

            <div class="metric-cards">
                <div class="metric-card">
                    <div class="metric-name">Accuracy</div>
                    <div class="metric-desc">Overall correctness of predictions</div>
                </div>
                <div class="metric-card">
                    <div class="metric-name">Precision</div>
                    <div class="metric-desc">Quality of positive predictions</div>
                </div>
                <div class="metric-card">
                    <div class="metric-name">Recall</div>
                    <div class="metric-desc">Coverage of actual positives</div>
                </div>
                <div class="metric-card">
                    <div class="metric-name">F1 Score</div>
                    <div class="metric-desc">Balanced precision &amp; recall</div>
                </div>
            </div>

            <!-- Accuracy -->
            <h2 style="border-left-color:#27ae60; font-size:1.1rem; margin-top:1.5rem;">✅ Accuracy</h2>
            <p>Accuracy measures how many predictions are correct out of all predictions made.</p>
            <div class="formula-box">
                Accuracy = (Correct Predictions / Total Predictions) × 100%
            </div>
            <p><em>Example:</em> If a model makes 100 predictions and gets 90 right → Accuracy = <strong>90%</strong></p>

            <!-- Precision -->
            <h2 style="border-left-color:#2980b9; font-size:1.1rem; margin-top:1.5rem;">🎯 Precision</h2>
            <p>Precision measures how many of the <em>predicted positives</em> are actually correct. Use this when false positives are costly (e.g., spam filtering — you don't want to mark real emails as spam).</p>
            <div class="formula-box">
                Precision = True Positives / (True Positives + False Positives)
            </div>

            <!-- Recall -->
            <h2 style="border-left-color:#8e44ad; font-size:1.1rem; margin-top:1.5rem;">🔍 Recall</h2>
            <p>Recall measures how many of the <em>actual positives</em> are correctly identified. Use this when missing a positive is dangerous (e.g., cancer diagnosis — you don't want to miss a sick patient).</p>
            <div class="formula-box">
                Recall = True Positives / (True Positives + False Negatives)
            </div>

            <!-- F1 Score -->
            <h2 style="border-left-color:#e74c3c; font-size:1.1rem; margin-top:1.5rem;">⚖️ F1 Score</h2>
            <p>The F1 Score is the harmonic mean of Precision and Recall. It provides a single balanced metric when both false positives and false negatives matter equally.</p>
            <div class="formula-box">
                F1 Score = 2 × (Precision × Recall) / (Precision + Recall)
            </div>
        </div>

        <!-- Example Comparison -->
        <div class="section">
            <h2>📊 Example: Model A vs. Model B</h2>
            <p>Consider two models evaluated on the same dataset:</p>

            <table class="comparison-table">
                <thead>
                    <tr>
                        <th>Model</th>
                        <th>Accuracy</th>
                        <th>Precision</th>
                        <th>Recall</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="highlight-row">
                        <td>Model A</td>
                        <td>90%</td>
                        <td>85%</td>
                        <td>88%</td>
                    </tr>
                    <tr>
                        <td>Model B</td>
                        <td>88%</td>
                        <td>92%</td>
                        <td>80%</td>
                    </tr>
                </tbody>
            </table>

            <div class="alert-tip mt-3">
                <strong>📌 Conclusion:</strong> Model A is more <em>balanced</em> overall, while Model B is more <em>precise</em> — but misses more actual positives (lower recall). Choose the model based on your problem's priorities.
            </div>
        </div>

        <!-- Decision Making -->
        <div class="section">
            <h2>🧭 When to Use Which Metric</h2>
            <table class="decision-table">
                <thead>
                    <tr>
                        <th>Metric</th>
                        <th>Best Used When...</th>
                        <th>Example Scenario</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Accuracy</strong></td>
                        <td>Dataset is balanced (equal classes)</td>
                        <td>Digit recognition</td>
                    </tr>
                    <tr>
                        <td><strong>Precision</strong></td>
                        <td>False positives are costly</td>
                        <td>Spam detection, fraud alerts</td>
                    </tr>
                    <tr>
                        <td><strong>Recall</strong></td>
                        <td>Missing positives is risky</td>
                        <td>Disease/cancer detection</td>
                    </tr>
                    <tr>
                        <td><strong>F1 Score</strong></td>
                        <td>Balance between precision &amp; recall is needed</td>
                        <td>Imbalanced datasets</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Alert -->
        <div class="alert-tip mb-4">
            <strong>⚠️ Common Mistake:</strong> Using accuracy alone for imbalanced datasets (e.g., 95% accuracy when 95% of data is one class) can be misleading. Always check precision, recall, and F1 Score for a complete picture.
        </div>

        <!-- Conclusion -->
        <div class="section">
            <h2>✅ Conclusion</h2>
            <p>
                Model comparison is an essential step in machine learning that ensures you select the best-performing model using structured techniques such as cross-validation and evaluation metrics. Accuracy, Precision, Recall, and F1 Score each serve a different purpose, and the best metric to use depends on the specific requirements of your problem.
            </p>
            <p>
                A well-compared model leads to more reliable, accurate, and trustworthy machine learning results — whether in health, business, or technology applications.
            </p>
        </div>

        <!-- Reflection -->
        <div class="section">
            <h2>🪞 Reflection Questions</h2>
            <div class="reflection-box">
                <p>1. Why is it not enough to just use accuracy as the only evaluation metric?</p>
                <p>2. In a cancer detection system, which metric is more important — Precision or Recall? Defend your answer.</p>
                <p>3. Given the table comparing Model A and Model B, which would you choose for a fraud detection system? Why?</p>
                <p>4. How does K-Fold Cross-Validation give a more reliable result than a simple train-test split?</p>
            </div>
        </div>

    </div><!-- /content -->

    <footer>
        CMC · Model Comparison Discussion · Machine Learning Evaluation Metrics
    </footer>

    <!-- Bootstrap JS -->
    <script src="<?= base_url('assets/jquery.slim.min.js') ?>"></script>
    <script src="<?= base_url('assets/bootstrap.bundle.min.js') ?>"></script>

    <!-- Highlight.js init -->
    <script>
        hljs.highlightAll();
    </script>
</body>

</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Random Forest Algorithm</title>

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
    
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
            color: #2d3748;
        }

        header {
            background: linear-gradient(135deg, #1a5276, #2e86c1);
            color: white;
            padding: 48px 24px 40px;
            text-align: center;
        }

        header h1 {
            font-size: 2.4rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        header p.subtitle {
            font-size: 1.1rem;
            opacity: 0.88;
            margin-bottom: 4px;
        }

        header p.meta {
            font-size: 0.9rem;
            opacity: 0.7;
        }

        .content {
            max-width: 860px;
            margin: 0 auto;
        }

        .section {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.07);
            padding: 28px 32px;
            margin-bottom: 28px;
        }

        .section h2 {
            font-size: 1.35rem;
            font-weight: 700;
            color: #1a5276;
            border-left: 4px solid #2e86c1;
            padding-left: 12px;
            margin-bottom: 16px;
        }

        .section h3 {
            font-size: 1.05rem;
            font-weight: 600;
            color: #2c3e50;
            margin-top: 18px;
            margin-bottom: 8px;
        }

        .step-item {
            display: flex;
            align-items: flex-start;
            gap: 16px;
            margin-bottom: 16px;
        }

        .step-num {
            background: #2e86c1;
            color: white;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            min-width: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .step-body strong {
            display: block;
            color: #1a5276;
            margin-bottom: 2px;
        }

        .concept-card {
            background: #eaf4fb;
            border-left: 4px solid #2e86c1;
            border-radius: 6px;
            padding: 16px 20px;
            margin-bottom: 14px;
        }

        .concept-card strong {
            color: #1a5276;
        }

        .app-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 14px;
            margin-top: 8px;
        }

        .app-card {
            background: #f0f7ff;
            border-radius: 8px;
            padding: 14px 16px;
            border: 1px solid #d0e8f8;
        }

        .app-card .icon {
            font-size: 1.5rem;
            margin-bottom: 6px;
        }

        .app-card strong {
            display: block;
            color: #1a5276;
            font-size: 0.95rem;
            margin-bottom: 4px;
        }

        .app-card p {
            margin: 0;
            font-size: 0.87rem;
            color: #4a5568;
        }

        .param-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 10px;
        }

        .param-card {
            background: #f7fbff;
            border: 1px solid #d0e8f8;
            border-radius: 8px;
            padding: 12px 16px;
        }

        .param-card strong {
            display: block;
            color: #1a5276;
            font-size: 0.92rem;
            margin-bottom: 4px;
        }

        .param-card p {
            margin: 0;
            font-size: 0.85rem;
            color: #4a5568;
        }

        .workflow-step {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .wf-badge {
            background: #1a5276;
            color: white;
            font-weight: 700;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            min-width: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
        }

        .alert-info-custom {
            background: #eaf4fb;
            border-left: 4px solid #2e86c1;
            border-radius: 6px;
            padding: 14px 18px;
            margin-top: 12px;
            font-size: 0.95rem;
            color: #2c3e50;
        }

        .summary-list li {
            margin-bottom: 8px;
        }

        .quote-block {
            text-align: center;
            font-size: 1.2rem;
            font-style: italic;
            color: #1a5276;
            padding: 20px;
            border-top: 2px dashed #d0e8f8;
            margin-top: 16px;
        }

        .model-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 12px;
        }

        .model-badge {
            background: #eaf4fb;
            border: 1px solid #2e86c1;
            border-radius: 20px;
            padding: 6px 16px;
            font-size: 0.9rem;
            color: #1a5276;
            font-weight: 600;
        }

        @media (max-width: 576px) {
            .param-grid {
                grid-template-columns: 1fr;
            }

            .section {
                padding: 20px 18px;
            }
        }
    </style>
</head>

<body>

    <header>
        <h1>🌲 Random Forest Algorithm</h1>
        <p class="subtitle">An Ensemble Learning Method for Classification &amp; Regression</p>
        <p class="meta">BSIS 3-C &nbsp;|&nbsp; Group 5</p>
    </header>

    <div class="content mt-4 mb-5 px-3">

        <!-- Objectives -->
        <div class="section">
            <h2>🎯 Learning Objectives</h2>
            <p>By the end of this discussion, you should be able to:</p>
            <ul>
                <li>Explain what the Random Forest algorithm is and how it differs from a single Decision Tree.</li>
                <li>Describe the role of Bootstrap Sampling and Random Feature Selection.</li>
                <li>Identify the key parameters that control a Random Forest model.</li>
                <li>Recognize real-world domains where Random Forest is applied.</li>
            </ul>
        </div>

        <!-- What is Random Forest -->
        <div class="section">
            <h2>🌳 What is Random Forest?</h2>
            <p>
                <strong>Random Forest</strong> is an <em>ensemble learning</em> method used for classification, regression, and other machine learning tasks. Instead of relying on a single decision tree, it builds <strong>many trees</strong> and combines their results to produce a more accurate and stable prediction.
            </p>
            <p>
                It was first proposed by <strong>Tin Kam Ho</strong>, then further developed and formalized by <strong>Leo Breiman</strong> (2001) and <strong>Adele Cutler</strong>.
            </p>
            <div class="row mt-3">
                <div class="col-md-4 mb-3">
                    <div class="concept-card">
                        <strong>🌳 Ensemble of Trees</strong>
                        <p class="mt-1 mb-0 small">Builds multiple decision trees and combines their predictions for a final result.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="concept-card">
                        <strong>🎲 Bootstrap Sampling</strong>
                        <p class="mt-1 mb-0 small">Each tree is trained on a random sample drawn with replacement from the training data.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="concept-card">
                        <strong>🗳️ Majority Vote</strong>
                        <p class="mt-1 mb-0 small">The final prediction is based on the majority vote (classification) or average (regression) of all trees.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- How It Works -->
        <div class="section">
            <h2>⚙️ How Random Forest Works</h2>
            <p>The Random Forest algorithm follows these five steps:</p>

            <div class="step-item">
                <div class="step-num">1</div>
                <div class="step-body">
                    <strong>Bootstrap Sampling</strong>
                    Randomly pick rows from the dataset <em>with replacement</em> to create a new training set for each tree. Some rows may appear more than once; others may not appear at all.
                </div>
            </div>

            <div class="step-item">
                <div class="step-num">2</div>
                <div class="step-body">
                    <strong>Random Feature Selection</strong>
                    At each split in the tree, only a <em>random subset of features</em> (columns) is considered — not all of them. This introduces diversity among the trees.
                </div>
            </div>

            <div class="step-item">
                <div class="step-num">3</div>
                <div class="step-body">
                    <strong>Build Decision Trees</strong>
                    Grow each tree fully using its sampled data and selected features. Each tree may look different from the others.
                </div>
            </div>

            <div class="step-item">
                <div class="step-num">4</div>
                <div class="step-body">
                    <strong>Make Predictions</strong>
                    Feed new data into all trees. Each tree outputs its own individual prediction.
                </div>
            </div>

            <div class="step-item">
                <div class="step-num">5</div>
                <div class="step-body">
                    <strong>Aggregate Results</strong>
                    Combine all tree predictions: use <strong>majority vote</strong> for classification tasks, or <strong>average</strong> for regression tasks.
                </div>
            </div>
        </div>

        <!-- Key Concepts -->
        <div class="section">
            <h2>🔑 Key Concepts</h2>

            <h3>Bootstrap Sampling</h3>
            <p>
                Each tree is trained on a random sample of rows drawn <em>with replacement</em>. This means some rows may appear twice and others not at all. The rows <strong>not</strong> selected form the <strong>Out-of-Bag (OOB)</strong> set, which is used to test that tree's accuracy without needing extra validation data.
            </p>

            <h3>Random Feature Selection</h3>
            <p>
                At every decision split, only a random subset of features is considered. For classification, the default is <strong>√n features</strong> (square root of total features). This forces each tree to be structurally different, preventing them from all making the same mistakes.
            </p>

            <div class="alert-info-custom">
                💡 <strong>Why these matter:</strong> Together, Bootstrap Sampling and Random Feature Selection <em>reduce overfitting</em> and <em>improve generalization</em> — the forest performs better on unseen data than any single tree would.
            </div>
        </div>

        <!-- Key Parameters -->
        <div class="section">
            <h2>🛠️ Key Parameters</h2>
            <p>Understanding these parameters helps you tune a Random Forest effectively:</p>

            <h3>Basic Properties</h3>
            <div class="param-grid">
                <div class="param-card">
                    <strong>Number of Trees</strong>
                    <p>How many decision trees are included in the forest. More trees generally improve accuracy up to a point.</p>
                </div>
                <div class="param-card">
                    <strong>Attributes at Each Split</strong>
                    <p>How many features are randomly drawn per node. Default is √n for classification.</p>
                </div>
                <div class="param-card">
                    <strong>Replicable Training (Random Seed)</strong>
                    <p>Fix the random seed for reproducible results — useful for debugging and comparison.</p>
                </div>
                <div class="param-card">
                    <strong>Balance Class Distribution</strong>
                    <p>Weighs classes inversely to their frequency, helping with imbalanced datasets.</p>
                </div>
            </div>

            <h3>Growth Control</h3>
            <div class="param-grid">
                <div class="param-card">
                    <strong>Limit Depth of Trees</strong>
                    <p>Pre-prune trees for faster training. Breiman's original implementation did not prune trees.</p>
                </div>
                <div class="param-card">
                    <strong>Minimum Subset Size</strong>
                    <p>Do not split subsets smaller than this threshold — a form of regularization.</p>
                </div>
            </div>
        </div>

        <!-- Classification Demo -->
        <div class="section">
            <h2>🎯 Classification — Iris Dataset</h2>
            <p><strong>Goal:</strong> Compare predictions from Random Forest vs. a single Decision Tree on the classic Iris dataset.</p>

            <h3>Workflow Steps (Orange Tool / Similar ML Tool)</h3>
            <div class="workflow-step">
                <div class="wf-badge">A</div>
                <div><strong>Load Data</strong> — Open the Iris dataset using the File widget.</div>
            </div>
            <div class="workflow-step">
                <div class="wf-badge">B</div>
                <div><strong>Connect Models</strong> — Connect File → Random Forest and File → Tree widget separately.</div>
            </div>
            <div class="workflow-step">
                <div class="wf-badge">C</div>
                <div><strong>Predictions Widget</strong> — Connect both models to the Predictions widget.</div>
            </div>
            <div class="workflow-step">
                <div class="wf-badge">D</div>
                <div><strong>Observe</strong> — Compare predictions side-by-side for both models and note differences.</div>
            </div>

            <div class="alert-info-custom">
                📝 This hands-on comparison helps you see how the ensemble (Random Forest) tends to produce more reliable predictions than a single tree, especially on ambiguous instances.
            </div>
        </div>

        <!-- Regression Demo -->
        <div class="section">
            <h2>📈 Regression — Housing Data</h2>
            <p><strong>Goal:</strong> Compare model performance using a Test &amp; Score widget on housing regression data.</p>

            <p>Three models are compared:</p>
            <div class="model-row">
                <div class="model-badge">🌲 Random Forest</div>
                <div class="model-badge">📉 Linear Regression</div>
                <div class="model-badge">📊 Constant Model (Baseline)</div>
            </div>

            <div class="alert-info-custom mt-3">
                <strong>Random Forest</strong> is an ensemble of trees that typically achieves the best generalization. <strong>Linear Regression</strong> assumes a linear relationship between features and the target. The <strong>Constant Model</strong> is a baseline that always predicts the mean — useful for benchmarking.
            </div>

            <p class="mt-3">All three models are connected to the <strong>Test &amp; Score</strong> widget for performance comparison using metrics such as RMSE or R².</p>
        </div>

        <!-- Real-World Applications -->
        <div class="section">
            <h2>🌍 Real-Life Applications</h2>
            <p>Random Forest is widely used across many industries:</p>
            <div class="app-grid">
                <div class="app-card">
                    <div class="icon">🏥</div>
                    <strong>Healthcare</strong>
                    <p>Predicting diseases like cancer or diabetes from patient data.</p>
                </div>
                <div class="app-card">
                    <div class="icon">💳</div>
                    <strong>Finance</strong>
                    <p>Detecting credit card fraud and predicting stock movements.</p>
                </div>
                <div class="app-card">
                    <div class="icon">🛒</div>
                    <strong>E-Commerce</strong>
                    <p>Recommending products and predicting customer churn.</p>
                </div>
                <div class="app-card">
                    <div class="icon">🌾</div>
                    <strong>Agriculture</strong>
                    <p>Classifying crop types and predicting harvest yields.</p>
                </div>
                <div class="app-card">
                    <div class="icon">📧</div>
                    <strong>Spam Filtering</strong>
                    <p>Classifying emails as spam or legitimate messages.</p>
                </div>
                <div class="app-card">
                    <div class="icon">🚗</div>
                    <strong>Self-Driving Cars</strong>
                    <p>Object detection and scene classification from sensor data.</p>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="section">
            <h2>📝 Summary</h2>
            <ul class="summary-list">
                <li>Random Forest = many Decision Trees working together as an ensemble.</li>
                <li>Uses <strong>Bootstrap Sampling</strong> &amp; <strong>Random Feature Selection</strong> to create diversity among trees.</li>
                <li>Final output = <strong>majority vote</strong> (classification) or <strong>average</strong> (regression).</li>
                <li>Highly accurate, handles noisy data, and avoids overfitting better than a single tree.</li>
                <li>Applied across healthcare, finance, agriculture, e-commerce, spam filtering, and more.</li>
            </ul>
            <div class="quote-block">"More trees, more accuracy." 🌲</div>
        </div>

        <!-- Reflection -->
        <div class="section">
            <h2>💭 Reflection Questions</h2>
            <ol>
                <li>Why is it better to use many trees instead of one? What problem does this solve?</li>
                <li>What is the purpose of using Out-of-Bag (OOB) samples?</li>
                <li>In what situation would you choose Random Forest over Linear Regression?</li>
                <li>Can you think of another real-world application of Random Forest not listed above?</li>
            </ol>
        </div>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
    <script>
        hljs.highlightAll();
    </script>
</body>

</html>
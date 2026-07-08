<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Intelligence Overview</title>

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
            background-color: #f8f9fa;
        }

        .section {
            background: #fff;
            /* border-radius: 10px; */
            padding: 15px;
            /* margin-bottom: 20px; */
            /* box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); */
        }

        h2 {
            border-left: 5px solid #007bff;
            padding-left: 10px;
        }

        .reveal-btn {
            margin-top: 10px;
        }

        .answer {
            display: none;
            margin-top: 10px;
        }
    </style>
</head>

<body>

    <header>
        <h1>Orange Data Preprocessing Reviewer</h1>
        <p>Machine Learning Data Preparation using Orange</p>
    </header>

    <div class="container content mt-4 mb-5">

        <!-- Learning Objectives -->
        <div class="section">
            <h2>Learning Objectives</h2>
            <ul>
                <li>Understand the role of preprocessing in machine learning</li>
                <li>Identify key preprocessing techniques in Orange</li>
                <li>Apply preprocessing steps such as normalization and imputation</li>
                <li>Analyze the effect of preprocessing on model performance</li>
            </ul>
        </div>

        <!-- Concept Explanation -->
        <div class="section">
            <h2>What is Data Preprocessing?</h2>
            <p>
                Data preprocessing is the process of preparing raw data before applying machine learning algorithms.
                In Orange, this is done using the <strong>Preprocess widget</strong>.
            </p>

            <div class="alert alert-info">
                Preprocessing improves data quality, making models more accurate and reliable.
            </div>
        </div>

        <!-- Imputation -->
        <div class="section">
            <h2>Handling Missing Data (Imputation)</h2>
            <p>
                Missing values are common in datasets. Imputation replaces missing values with estimated ones.
            </p>

            <ul>
                <li><strong>Mean</strong> – average (numerical)</li>
                <li><strong>Median</strong> – middle value</li>
                <li><strong>Mode</strong> – most frequent (categorical)</li>
            </ul>

            <div class="alert alert-warning">
                Always handle missing data BEFORE applying other preprocessing steps.
            </div>

            <h5>Example:</h5>
            <pre><code>[5, ?, 15] → Mean = 10 → [5, 10, 15]</code></pre>
        </div>

        <!-- Normalization -->
        <div class="section">
            <h2>Normalization (Scaling Data)</h2>
            <p>
                Normalization adjusts values to a common scale so that no feature dominates others.
            </p>

            <h5>Common Methods:</h5>
            <ul>
                <li><strong>Min-Max Scaling</strong> (0 to 1)</li>
                <li><strong>Z-score</strong> (mean = 0, std = 1)</li>
            </ul>

            <h5>Formula (Min-Max):</h5>
            <pre><code>(x - min) / (max - min)</code></pre>

            <div class="alert alert-info">
                Useful when features have different units (e.g., age vs salary).
            </div>

            <h5>Example:</h5>
            <pre><code>[10, 20, 30] → 20 becomes 0.5</code></pre>
        </div>

        <!-- Discretization -->
        <div class="section">
            <h2>Discretization</h2>
            <p>
                Discretization converts continuous values into categories.
            </p>

            <h5>Example:</h5>
            <pre><code>Age → Young | Middle | Old</code></pre>

            <div class="alert alert-success">
                Helps simplify models and improve interpretability.
            </div>
        </div>

        <!-- Feature Selection -->
        <div class="section">
            <h2>Feature Selection</h2>
            <p>
                Removes irrelevant or redundant features to improve performance.
            </p>

            <ul>
                <li>Remove constant features</li>
                <li>Remove noisy data</li>
            </ul>

            <div class="alert alert-danger">
                Keeping useless features can reduce model accuracy.
            </div>
        </div>

        <!-- Workflow -->
        <div class="section">
            <h2>Typical Orange Workflow</h2>
            <pre><code>File → Preprocess → Model → Test & Score</code></pre>

            <div class="alert alert-primary">
                Preprocess is always placed BEFORE modeling.
            </div>
        </div>

        <!-- <div class="section">
            <h2>Bloom’s Taxonomy Guide</h2>
            <ul>
                <li><strong>Remembering:</strong> Define preprocessing steps</li>
                <li><strong>Understanding:</strong> Explain why normalization is needed</li>
                <li><strong>Applying:</strong> Perform normalization/imputation</li>
                <li><strong>Analyzing:</strong> Evaluate preprocessing impact</li>
            </ul>
        </div> -->

        <div class="section">
            <h2>Reflection Questions</h2>
            <ol>
                <li>Why is preprocessing important before training a model?</li>
                <li>What happens if you skip normalization?</li>
                <li>When should you use discretization instead of normalization?</li>
                <li>How does removing features affect model performance?</li>
            </ol>
        </div>

    </div>

    <script>
        function toggleAnswer(id) {
            const el = document.getElementById(id);
            el.style.display = el.style.display === "block" ? "none" : "block";
        }
    </script>

</body>

</html>
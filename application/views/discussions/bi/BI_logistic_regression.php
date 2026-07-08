<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logistic Regression using Orange</title>

    <!-- Bootstrap 4.5.2 -->
    <link rel="stylesheet" href="<?php echo base_url('assets/bootstrap.min.css'); ?>">

    <!-- Custom Style -->
    <link rel="stylesheet" href="<?php echo base_url('assets/discussion-style.css'); ?>">

    <!-- Highlight.js -->
    <script src="<?= base_url('assets/highlights/11.7.0-highlight.min.js') ?>"></script>
    <script>
        hljs.highlightAll();
    </script>
</head>

<body>

    <header class="text-center py-4">
        <h1>Logistic Regression</h1>
        <p>Using Orange Data Mining</p>
    </header>

    <div class="content mt-4 mb-5">

        <!-- Learning Objectives -->
        <div class="section card p-4 mb-4">
            <h2>Learning Objectives</h2>
            <ul>
                <li>Understand Logistic Regression in a simple way</li>
                <li>Explain the role of the sigmoid function</li>
                <li>Apply Logistic Regression in real-world scenarios</li>
                <li>Build and interpret a model using Orange</li>
            </ul>
        </div>

        <!-- Concept Explanation -->
        <div class="section card p-4 mb-4">
            <h2>Concept Explanation</h2>
            <p>
                Logistic Regression is used to answer Yes/No questions by estimating probability.
                Instead of directly saying "Yes" or "No", it first calculates how likely the outcome is.
            </p>

            <div class="alert alert-info">
                <strong>Main Idea:</strong> Score → Probability → Decision
            </div>
        </div>

        <!-- Sigmoid Section -->
        <div class="section card p-4 mb-4">
            <h2>Sigmoid Function (The Core of Logistic Regression)</h2>

            <p>
                The sigmoid function is what makes Logistic Regression special.
                It transforms any value into a number between 0 and 1.
            </p>

            <pre><code>p = 1 / (1 + e^-z)</code></pre>

            <p>
                Why is this important?
            </p>
            <ul>
                <li>Ensures outputs are valid probabilities</li>
                <li>Smoothly converts low scores to low probability</li>
                <li>Converts high scores to high probability</li>
            </ul>

            <div class="alert alert-success">
                Think of sigmoid as a "probability converter".
            </div>

            <p>
                Behavior of sigmoid:
            </p>
            <ul>
                <li>Very negative score → close to 0</li>
                <li>Score near 0 → around 0.5</li>
                <li>Very positive score → close to 1</li>
            </ul>
        </div>

        <!-- Real World Example -->
        <div class="section card p-4 mb-4">
            <h2>Real-World Examples</h2>

            <h5>1. Student Passing Prediction</h5>
            <p>
                A teacher wants to predict if a student will pass.
            </p>
            <ul>
                <li>Inputs: Study Hours, Attendance</li>
                <li>Output: Probability of passing</li>
            </ul>
            <p>
                Example results:
            </p>
            <ul>
                <li>0.85 → Pass</li>
                <li>0.40 → Fail</li>
            </ul>

            <hr>

            <h5>2. Loan Approval (Banking)</h5>
            <p>
                A bank decides whether to approve a loan.
            </p>
            <ul>
                <li>Inputs: Income, Debt, Credit Score</li>
                <li>Output: Probability of repayment</li>
            </ul>

            <ul>
                <li>0.90 → Approve</li>
                <li>0.25 → Reject</li>
            </ul>

            <hr>

            <h5>3. Email Spam Detection</h5>
            <p>
                Email systems classify messages as spam or not.
            </p>
            <ul>
                <li>Inputs: Keywords, sender, frequency</li>
                <li>Output: Probability of being spam</li>
            </ul>

            <ul>
                <li>0.95 → Spam</li>
                <li>0.10 → Not Spam</li>
            </ul>

        </div>

        <!-- Orange Workflow -->
        <div class="section card p-4 mb-4">
            <h2>Orange Workflow (Step-by-Step)</h2>
            <ol>
                <li><strong>File</strong> → Load dataset</li>
                <li><strong>Logistic Regression</strong> → Build model</li>
                <li><strong>Test & Score</strong> → Evaluate accuracy</li>
                <li><strong>Predictions</strong> → View results</li>
            </ol>

            <div class="alert alert-warning">
                No coding needed—just connect widgets visually.
            </div>
        </div>

        <!-- Key Notes -->
        <div class="section card p-4 mb-4">
            <h2>Key Notes</h2>
            <ul>
                <li>Used for binary classification</li>
                <li>Outputs probability (not exact values)</li>
                <li>Sigmoid ensures results stay between 0 and 1</li>
                <li>Threshold converts probability to decision</li>
            </ul>
        </div>

        <!-- Reflection -->
        <div class="section card p-4 mb-4">
            <h2>Reflection Questions</h2>
            <ul>
                <li>Why is sigmoid important in Logistic Regression?</li>
                <li>What happens if the threshold changes from 0.5 to 0.7?</li>
                <li>Can you think of another Yes/No problem?</li>
            </ul>
        </div>

    </div>

</body>

</html>
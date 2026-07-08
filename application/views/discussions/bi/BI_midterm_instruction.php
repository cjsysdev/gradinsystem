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

    <header class="text-center py-4">
        <h1>Business Intelligence Midterm Project</h1>
        <p class="lead">Text Mining, Analytics, and NLP Using Orange Data Mining</p>
    </header>

    <div class="content mt-4 mb-5">
        <div class="container">

            <!-- Project Overview -->
            <div class="section card p-4 mb-4">
                <h2>Project Overview</h2>
                <p>
                    In this midterm project, you will act as a <strong>Business Intelligence Analyst</strong>.
                    Your task is to collect a real-world textual dataset and apply
                    <strong>Text Mining, NLP, Topic Modeling, and Analytics</strong>
                    using <strong>Orange Data Mining Software</strong>.
                </p>
                <p>
                    The goal is not only to analyze text data but to transform it into
                    meaningful business insights and actionable recommendations.
                </p>
            </div>

            <!-- Dataset Selection -->
            <div class="section card p-4 mb-4">
                <h2>Part 1 – Dataset Information</h2>

                <h5>Step 1: Find a Dataset</h5>
                <ul>
                    <li>Kaggle</li>
                    <li>UCI Machine Learning Repository</li>
                    <li>Government Open Data</li>
                    <li>Any public dataset with textual data</li>
                </ul>

                <h5>Step 2: Provide Dataset Introduction</h5>
                <ul>
                    <li>Dataset Title</li>
                    <li>Source Link</li>
                    <li>Number of Records</li>
                    <li>Main Attributes</li>
                    <li>Type of Text Data</li>
                    <li>Business Context</li>
                </ul>
            </div>

            <!-- Orange Workflow -->
            <div class="section card p-4 mb-4">
                <h2>Part 2 – Orange Data Mining Analysis</h2>

                <p>Create a complete NLP workflow using Orange.</p>

                <p><strong>Minimum Required Widgets:</strong></p>

                <pre><code>
File
 → Corpus
 → Preprocess Text
 → Word Cloud
 → Sentiment Analysis
 → Topic Modeling
 → Data Table
 → Visualization (Scatter Plot / Cluster)
</code></pre>

                <p><strong>Submission Requirement:</strong></p>
                <ul>
                    <li>Screenshot of widget connections</li>
                    <li>Screenshot of analysis outputs</li>
                    <li>Clear and readable workflow</li>
                </ul>
            </div>

            <!-- Results and Analysis -->
            <div class="section card p-4 mb-4">
                <h2>Part 3 – Results and Analysis</h2>

                <h5>A. Text Mining Results</h5>
                <ul>
                    <li>Most frequent words</li>
                    <li>Important keywords</li>
                    <li>Observed patterns</li>
                </ul>

                <h5>B. Sentiment Analysis</h5>
                <ul>
                    <li>Percentage of Positive, Neutral, Negative</li>
                    <li>Interpretation of results</li>
                </ul>

                <h5>C. Topic Modeling</h5>
                <ul>
                    <li>Number of topics discovered</li>
                    <li>Keywords per topic</li>
                    <li>Meaning of each topic</li>
                </ul>

                <div class="alert alert-info">
                    Do NOT just show screenshots. You must explain and interpret the results.
                </div>
            </div>

            <!-- Business Intelligence Insight -->
            <div class="section card p-4 mb-4">
                <h2>Part 4 – Business Intelligence Insight</h2>

                <p>Answer the following:</p>
                <ul>
                    <li>What problems were discovered?</li>
                    <li>What opportunities were identified?</li>
                    <li>What hidden patterns were revealed?</li>
                </ul>
            </div>

            <!-- Action Plan -->
            <div class="section card p-4 mb-4">
                <h2>Part 5 – Action Plan (Decision Making)</h2>

                <p>Create a table similar to the format below:</p>

                <pre><code>
| Finding | Evidence from Data | Recommended Action |
|---------|--------------------|--------------------|
| Example | 40% negative reviews | Improve customer support |
</code></pre>

                <div class="alert alert-warning">
                    This section carries high weight. Your recommendations must be based on your analysis.
                </div>
            </div>

            <!-- Submission -->
            <div class="section card p-4 mb-4">
                <h2>Submission Requirements</h2>

                <ul>
                    <li>Project Report (PDF or DOCX)</li>
                    <li>Dataset source link included</li>
                    <li>Orange workflow screenshots</li>
                    <li>Results and interpretation</li>
                    <li>Business insights and action plan</li>
                </ul>
            </div>

            <!-- Grading Rubric -->
            <div class="section card p-4 mb-4">
                <h2>Grading Rubric</h2>

                <ul>
                    <li>Dataset Introduction – 15%</li>
                    <li>Correct Orange Workflow – 25%</li>
                    <li>Analysis Interpretation – 30%</li>
                    <li>BI Insight & Critical Thinking – 20%</li>
                    <li>Actionable Recommendations – 10%</li>
                </ul>
            </div>

            <!-- Reflection -->
            <div class="section card p-4 mb-4">
                <h2>Reflection Questions</h2>

                <ol>
                    <li>What was the most surprising insight from your analysis?</li>
                    <li>How can text mining improve business decisions?</li>
                    <li>What challenges did you encounter using Orange?</li>
                </ol>
            </div>

        </div>
    </div>

    <?php $this->load->view('web_to_image'); ?>


</body>

</html>
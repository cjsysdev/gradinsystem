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

    <header class="bg-primary text-white text-center py-4">
        <h1>Decision Tree Modeling and Visualization</h1>
        <p>Using Tree and Tree Viewer Widgets in Orange Data Mining</p>
    </header>

    <div class="container content mt-4 mb-5">

        <!-- Learning Objectives -->
        <div class="section card p-4 mb-4">
            <h2>Learning Objectives</h2>
            <ul>
                <li>Understand how the Tree widget builds decision tree models</li>
                <li>Explain how Tree Viewer visualizes classification and regression trees</li>
                <li>Identify key parameters affecting tree construction</li>
                <li>Analyze model outputs using interactive visualization</li>
            </ul>
        </div>

        <!-- Concept Explanation -->
        <div class="section card p-4 mb-4">
            <h2>Concept Explanation</h2>

            <h4>1. Tree Widget (Model)</h4>
            <p>
                The Tree widget is used to create a <strong>decision tree model</strong>.
                It splits data into branches based on feature values to improve prediction accuracy.
            </p>

            <div class="alert alert-info">
                A decision tree divides data based on <strong>class purity</strong> using:
                <ul>
                    <li>Information Gain (classification)</li>
                    <li>Mean Squared Error (regression)</li>
                </ul>
            </div>

            <p>
                It supports both <strong>classification and regression</strong> tasks and produces:
            </p>
            <ul>
                <li><strong>Learner</strong> – the algorithm</li>
                <li><strong>Model</strong> – the trained decision tree</li>
            </ul>

            <h5>Key Parameters:</h5>
            <ul>
                <li>Binary tree splitting</li>
                <li>Minimum instances per leaf</li>
                <li>Maximum tree depth</li>
                <li>Stopping criteria (majority threshold)</li>
            </ul>

            <hr>

            <h4>2. Tree Viewer Widget (Visualization)</h4>
            <p>
                The Tree Viewer displays the generated decision tree in a <strong>2D visual format</strong>.
            </p>

            <div class="alert alert-success">
                It allows users to interactively explore the model by clicking nodes to inspect data.
            </div>

            <h5>Key Features:</h5>
            <ul>
                <li>Zoom in/out and adjust tree depth</li>
                <li>Node selection to extract subsets of data</li>
                <li>Color nodes based on class or statistical values</li>
                <li>Adjust edge width based on data proportions</li>
            </ul>

            <p>
                Selecting a node outputs the corresponding dataset, enabling
                <strong>exploratory data analysis</strong>.
            </p>
        </div>

        <!-- Workflow Example -->
        <div class="section card p-4 mb-4">
            <h2>Workflow Example</h2>

            <pre><code class="language-text">
File → Tree → Tree Viewer → Data Table / Scatter Plot
</code></pre>

            <ul>
                <li>Load dataset using File widget</li>
                <li>Connect to Tree to build model</li>
                <li>Visualize using Tree Viewer</li>
                <li>Select nodes to analyze subsets</li>
            </ul>
        </div>

        <!-- Key Insights -->
        <div class="section card p-4 mb-4">
            <h2>Key Insights</h2>
            <ul>
                <li>Tree builds the model, Tree Viewer explains it</li>
                <li>Tree depth controls complexity (overfitting vs simplicity)</li>
                <li>Visualization helps interpret model decisions</li>
                <li>Node selection enables interactive data exploration</li>
            </ul>
        </div>

        <!-- Reflection -->
        <div class="section card p-4 mb-4">
            <h2>Reflection Questions</h2>
            <ol>
                <li>Why is controlling tree depth important?</li>
                <li>How does Tree Viewer help in understanding predictions?</li>
                <li>What happens if the tree is too deep?</li>
                <li>How can node selection support decision-making?</li>
            </ol>
        </div>

    </div>

    <script>
        hljs.highlightAll();
    </script>
</body>

</html>
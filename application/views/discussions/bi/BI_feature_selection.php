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
        <h1>Feature Selection in Predictive Analytics</h1>
        <p class="lead">Improving Model Performance through Relevant Data</p>
    </header>

    <div class="content mt-4 mb-5 container">

        <!-- Learning Objectives -->
        <div class="section card p-4 mb-4">
            <h2>Learning Objectives</h2>
            <ul>
                <li>Understand the concept and purpose of feature selection</li>
                <li>Identify benefits and risks of selecting features</li>
                <li>Use Orange tools (Rank widget) for feature selection</li>
                <li>Analyze the impact of features on model performance</li>
            </ul>
        </div>

        <!-- Concept Explanation -->
        <div class="section card p-4 mb-4">
            <h2>Concept Explanation</h2>
            <p>
                Feature selection is the process of choosing the most relevant variables (features)
                from a dataset to use in building a predictive model. Instead of using all available data,
                we focus only on the features that significantly contribute to predicting the target variable.
            </p>

            <p>
                Including irrelevant or redundant features can introduce noise, reduce model accuracy,
                and increase computational cost. By selecting only the important features, we can build
                simpler, faster, and more accurate models.
            </p>
        </div>

        <!-- Key Concepts -->
        <div class="section card p-4 mb-4">
            <h2>Key Concepts from the Quiz</h2>
            <ul>
                <li><strong>Goal:</strong> Select the most relevant variables for prediction</li>
                <li><strong>Benefits:</strong> Improved accuracy, faster computation, simpler models</li>
                <li><strong>Risks:</strong> Losing important information if too many features are removed</li>
                <li><strong>Common Issue:</strong> Too many irrelevant features reduce model performance</li>
            </ul>
        </div>

        <!-- Orange Workflow -->
        <div class="section card p-4 mb-4">
            <h2>Feature Selection in Orange</h2>

            <p>Typical workflow:</p>

            <pre><code class="language-plaintext">
File → Impute → Rank → Select Columns → Model
</code></pre>

            <ul>
                <li><strong>File:</strong> Load dataset</li>
                <li><strong>Impute:</strong> Handle missing values</li>
                <li><strong>Rank:</strong> Evaluate feature importance</li>
                <li><strong>Select Columns:</strong> Keep only important features</li>
            </ul>
        </div>

        <!-- Example -->
        <div class="section card p-4 mb-4">
            <h2>Example Scenario</h2>
            <p>
                A school wants to predict student performance. The dataset includes:
            </p>
            <ul>
                <li>Study hours</li>
                <li>Attendance</li>
                <li>Favorite color</li>
                <li>Previous grades</li>
            </ul>

            <p>
                Using feature selection, "Favorite color" may be removed because it does not affect
                academic performance, while "Study hours" and "Previous grades" are retained.
            </p>
        </div>

        <!-- Important Notes -->
        <div class="section card p-4 mb-4 border-left border-warning">
            <h2>Important Notes</h2>
            <ul>
                <li>Do not remove features blindly—always analyze importance</li>
                <li>Too many features → Overfitting risk</li>
                <li>Too few features → Underfitting risk</li>
            </ul>
        </div>

        <!-- Reflection Questions -->
        <div class="section card p-4 mb-4">
            <h2>Reflection Questions</h2>
            <ol>
                <li>Why is feature selection important in predictive analytics?</li>
                <li>What might happen if irrelevant features are included?</li>
                <li>How does the Rank widget help in decision-making?</li>
                <li>Can removing too many features harm the model? Explain.</li>
                <li>Give a real-life example where feature selection is useful.</li>
            </ol>
        </div>

        <!-- Checklist -->
        <div class="section card p-4 mb-4">
            <h2>Student Checklist</h2>
            <ul>
                <li>I understand what feature selection is</li>
                <li>I can identify relevant vs irrelevant features</li>
                <li>I can use the Rank widget in Orange</li>
                <li>I understand the risks of overfitting and underfitting</li>
            </ul>
        </div>

    </div>

</body>

</html>
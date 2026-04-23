<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logistic Regression using Orange</title>

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

    <header class="bg-success text-white text-center py-4">
        <h1>k-Nearest Neighbors (kNN)</h1>
        <p class="mb-0">Interactive Learning Module for Model-Based Prediction</p>
    </header>

    <div class="content container mt-4 mb-5">

        <!-- Learning Objectives -->
        <div class="section-card">
            <h2>Learning Objectives</h2>
            <ul>
                <li>Understand the concept of kNN algorithm</li>
                <li>Configure kNN widget in Orange Data Mining</li>
                <li>Interpret distance metrics and weights</li>
                <li>Apply kNN in real-world scenarios</li>
            </ul>
        </div>

        <!-- Introduction -->
        <div class="section-card">
            <h2>What is kNN?</h2>
            <p>
                The k-Nearest Neighbors (kNN) algorithm is a <strong>lazy learning method</strong> that predicts outcomes
                based on the closest data points in the dataset.
            </p>
            <p>
                In simple terms: <em>"Your output is influenced by your nearest neighbors."</em>
            </p>
        </div>

        <!-- How kNN Works -->
        <div class="section-card">
            <h2>How kNN Works in Orange</h2>

            <ol>
                <li>Select number of neighbors (k)</li>
                <li>Choose a distance metric</li>
                <li>Set weighting method</li>
                <li>Compute nearest instances</li>
                <li>Predict based on majority vote or average</li>
            </ol>

            <pre><code class="language-text">
Example Workflow:
File → Preprocess → kNN → Test & Score → Predictions
        </code></pre>
        </div>

        <!-- Parameters -->
        <div class="section-card">
            <h2>Key Parameters of kNN Widget</h2>

            <h5>1. Number of Neighbors (k)</h5>
            <p>Controls how many nearby instances influence prediction.</p>

            <h5>2. Distance Metrics</h5>
            <ul>
                <li>Euclidean Distance</li>
                <li>Manhattan Distance</li>
                <li>Mahalanobis Distance</li>
            </ul>

            <h5>3. Weights</h5>
            <ul>
                <li>Uniform: equal influence</li>
                <li>Distance-based: closer neighbors matter more</li>
            </ul>
        </div>

        <!-- Real World Example -->
        <div class="section-card">
            <h2>Real-World Example</h2>
            <p><strong>Student Performance Prediction</strong></p>

            <ul>
                <li>Inputs: study hours, attendance, quiz scores</li>
                <li>kNN finds similar students</li>
                <li>Prediction based on majority outcome</li>
            </ul>
        </div>

        <!-- Key Insight -->
        <div class="section-card">
            <h2>Key Insight</h2>
            <p>
                kNN does not build a model in advance. Instead, it memorizes the dataset and compares new inputs to existing examples.
            </p>
        </div>

        <!-- Concept 1 -->
        <div class="section-card">
            <h2>Understanding “k” in kNN</h2>
            <p>
                In kNN, the value <strong>k</strong> determines how many nearby data points are considered when making a prediction.
                Instead of relying on a single neighbor, the algorithm looks at multiple neighbors to make a more balanced decision.
            </p>
            <p>
                The main idea behind kNN is simple: data points that are close to each other are likely to be similar.
                When a new input is given, the algorithm finds the closest known data points and uses them to predict the result.
            </p>

        </div>


        <!-- Concept 3 -->
        <div class="section-card">
            <h2>Distance Metrics Matter</h2>
            <p>
                kNN relies on distance to determine similarity. The most commonly used metric is
                <strong>Euclidean distance</strong>, which measures straight-line distance between two points.
            </p>
            <p>
                When k is very small (e.g., k = 1), the model becomes highly sensitive to individual data points.
                This may lead to <strong>overfitting</strong>, where the model performs well on training data but poorly on new data.
            </p>

        </div>

        <!-- Concept 5 -->
        <div class="section-card">
            <h2>Importance of Normalization</h2>
            <p>
                Features must be scaled properly before applying kNN. Without normalization,
                features with larger values can dominate distance calculations, leading to incorrect results.
            </p>
            <p>
                Not all neighbors need to contribute equally. In distance-based weighting,
                closer neighbors have more influence on the prediction than those farther away.
            </p>

        </div>

        <!-- Concept 7 -->
        <div class="section-card">
            <h2>Type of Learning in kNN</h2>
            <p>
                kNN is a <strong>lazy learning</strong> algorithm. It does not build a model in advance but instead
                waits until a prediction is needed before computing results.
            </p>
            <p>
                When k is too large, the model becomes too generalized and may ignore important patterns.
                This leads to <strong>underfitting</strong>.
            </p>
        </div>

        <!-- Concept 9 -->
        <div class="section-card">
            <h2>How kNN Makes Predictions</h2>
            <p>
                kNN does not train a neural network or build complex models. Instead, it:
                computes distances, finds nearest neighbors, and performs majority voting (classification)
                or averaging (regression).
            </p>
            <p>
                In classification, kNN predicts the most common class among neighbors.
                In regression, it predicts the average value of the neighbors.
            </p>

        </div>

        <!-- Summary -->
        <div class="section-card">
            <h2>Summary</h2>
            <ul>
                <li>kNN is based on similarity and distance</li>
                <li>Choice of k affects performance</li>
                <li>Normalization is critical</li>
                <li>Used for both classification and regression</li>
                <li>kNN is a lazy learning algorithm</li>
                <li>Uses distance-based similarity</li>
                <li>Simple but powerful for classification and regression</li>
            </ul>
        </div>

    </div>

</body>

</html>
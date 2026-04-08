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
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
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
        <h1>Guided Discussion: Data Preparation & Imputation</h1>
        <p class="text">From Questions to Deep Understanding</p>
    </header>

    <div class="container mt-4 mb-5">

        <!-- Section 1 -->
        <div class="section">
            <h2>What is the purpose of Orange?</h2>
            <p>
                Think about how machine learning is usually done. Most tools require coding.
                Orange is different.
            </p>

            <div class="alert alert-secondary">
                🤔 How do you think a beginner would benefit from a drag-and-drop ML tool?
            </div>

            <button class="btn btn-outline-primary reveal-btn" onclick="toggleAnswer('a1')">Reveal Explanation</button>
            <div id="a1" class="answer alert alert-info">
                Orange allows users to build machine learning workflows visually using widgets.
                This removes the need for heavy coding and helps beginners focus on concepts.
            </div>
        </div>

        <!-- Section 2 -->
        <div class="section">
            <h2>What is Imputation?</h2>
            <p>
                Real-world datasets are messy. Sometimes values are missing.
            </p>

            <div class="alert alert-warning">
                🤔 If a dataset has missing values, what do you think will happen during training?
            </div>

            <button class="btn btn-outline-primary reveal-btn" onclick="toggleAnswer('a2')">Reveal Explanation</button>
            <div id="a2" class="answer alert alert-info">
                Imputation is the process of replacing missing values with estimated ones.
                Without it, many machine learning algorithms will fail or perform poorly.
            </div>
        </div>

        <!-- Section 3 -->
        <div class="section">
            <h2>Why use Mean or Most Frequent?</h2>
            <p>
                One simple way to fill missing data is by using averages or common values.
            </p>

            <div class="alert alert-secondary">
                🤔 When would using the average be a good idea?
            </div>

            <button class="btn btn-outline-primary reveal-btn" onclick="toggleAnswer('a3')">Reveal Explanation</button>
            <div id="a3" class="answer alert alert-info">
                Mean is useful for numerical data because it represents a central value.
                Most frequent works for categorical data like labels or categories.
            </div>
        </div>

        <!-- Section 4 -->
        <div class="section">
            <h2>What is Model-Based Imputation?</h2>
            <p>
                Instead of guessing, we can predict missing values using other data.
            </p>

            <div class="alert alert-secondary">
                🤔 How is predicting missing data better than using averages?
            </div>

            <button class="btn btn-outline-primary reveal-btn" onclick="toggleAnswer('a4')">Reveal Explanation</button>
            <div id="a4" class="answer alert alert-info">
                Model-based imputation uses algorithms (like k-NN) to predict values,
                making it more accurate because it considers relationships in data.
            </div>
        </div>

        <!-- Section 5 -->
        <div class="section">
            <h2>What happens if data is not cleaned?</h2>
            <p>
                Machine learning models rely heavily on data quality.
            </p>

            <div class="alert alert-danger">
                🤔 What do you think happens if we train a model with messy data?
            </div>

            <button class="btn btn-outline-primary reveal-btn" onclick="toggleAnswer('a5')">Reveal Explanation</button>
            <div id="a5" class="answer alert alert-info">
                Poor data leads to poor predictions. This is known as
                <strong>"Garbage in, garbage out."</strong>
            </div>
        </div>

        <!-- Section 6 -->
        <div class="section">
            <h2>When should we remove rows?</h2>
            <p>
                Sometimes instead of filling missing values, we delete them.
            </p>

            <div class="alert alert-warning">
                🤔 Is it always safe to remove data?
            </div>

            <button class="btn btn-outline-primary reveal-btn" onclick="toggleAnswer('a6')">Reveal Explanation</button>
            <div id="a6" class="answer alert alert-info">
                Removing rows is useful when only a few records are missing values.
                But removing too much data can reduce dataset quality and size.
            </div>
        </div>

        <!-- Section 7 -->
        <div class="section">
            <h2>Why avoid imputing class labels?</h2>
            <p>
                The class label is what the model tries to predict.
            </p>

            <div class="alert alert-danger">
                🤔 What happens if we "guess" the correct answer during training?
            </div>

            <button class="btn btn-outline-primary reveal-btn" onclick="toggleAnswer('a7')">Reveal Explanation</button>
            <div id="a7" class="answer alert alert-info">
                Imputing class labels can introduce bias and make the model learn incorrect patterns.
            </div>
        </div>

        <!-- Section 8 -->
        <div class="section">
            <h2>What is the risk of improper preprocessing?</h2>
            <p>
                Preprocessing must be done carefully, especially during validation.
            </p>

            <div class="alert alert-danger">
                🤔 What is data leakage and why is it dangerous?
            </div>

            <button class="btn btn-outline-primary reveal-btn" onclick="toggleAnswer('a8')">Reveal Explanation</button>
            <div id="a8" class="answer alert alert-info">
                Data leakage happens when information from test data is used during training.
                This leads to overly optimistic and misleading results.
            </div>
        </div>

        <!-- Section 9 -->
        <div class="section">
            <h2>How does 1-Nearest Neighbor (1-NN) help in imputation?</h2>
            <p>
                Orange uses 1-NN as a default for model-based imputation.
            </p>
            <div class="alert alert-secondary">
                🤔 Why would using a nearby data point be useful to fill missing values?
            </div>
            <button class="btn btn-outline-primary reveal-btn" onclick="toggleAnswer('a9')">Reveal Explanation</button>
            <div id="a9" class="answer alert alert-info">
                1-NN finds the closest record to the missing value and uses its value to fill in,
                maintaining consistency in the dataset.
            </div>
        </div>

        <!-- Section 10 -->
        <div class="section">
            <h2> What is the difference between numeric and categorical imputation?</h2>
            <p>
                Not all data types can be treated the same way.
            </p>
            <div class="alert alert-warning">
                🤔 How would you handle missing numbers vs missing categories differently?
            </div>
            <button class="btn btn-outline-primary reveal-btn" onclick="toggleAnswer('a10')">Reveal Explanation</button>
            <div id="a10" class="answer alert alert-info">
                Numeric data can use mean or median, while categorical data often uses the most frequent value.
                Using the wrong method can distort the data.
            </div>
        </div>

        <!-- Section 11 -->
        <div class="section">
            <h2> Why is data preprocessing considered more important than algorithms?</h2>
            <p>
                A model is only as good as the data it sees.
            </p>
            <div class="alert alert-secondary">
                🤔 Can a complex algorithm fix poor-quality data?
            </div>
            <button class="btn btn-outline-primary reveal-btn" onclick="toggleAnswer('a11')">Reveal Explanation</button>
            <div id="a11" class="answer alert alert-info">
                Even the best algorithm fails if data is messy.
                Preprocessing ensures the algorithm receives clean and meaningful inputs.
            </div>
        </div>

        <!-- Section 12 -->
        <div class="section">
            <h2> When should random imputation be used?</h2>
            <p>
                Sometimes we want to maintain variability.
            </p>
            <div class="alert alert-warning">
                🤔 What might happen if we always use the same value to fill missing data?
            </div>
            <button class="btn btn-outline-primary reveal-btn" onclick="toggleAnswer('a12')">Reveal Explanation</button>
            <div id="a12" class="answer alert alert-info">
                Random imputation preserves variability and prevents artificially uniform data,
                which may improve model robustness.
            </div>
        </div>

        <!-- Section 13 -->
        <div class="section">
            <h2> How does imputation affect model evaluation?</h2>
            <p>
                Filling missing values impacts the final metrics.
            </p>
            <div class="alert alert-danger">
                🤔 Why might an improper imputation give misleading accuracy scores?
            </div>
            <button class="btn btn-outline-primary reveal-btn" onclick="toggleAnswer('a13')">Reveal Explanation</button>
            <div id="a13" class="answer alert alert-info">
                If the imputation introduces bias or uses information from test data, metrics like accuracy
                may appear better than they really are, leading to data leakage.
            </div>
        </div>

        <!-- Section 14 -->
        <div class="section">
            <h2> Why is normalization or scaling important after imputation?</h2>
            <p>
                Imputation fixes missing values, but features may still differ in scale.
            </p>
            <div class="alert alert-warning">
                🤔 How could differences in feature scales affect model performance?
            </div>
            <button class="btn btn-outline-primary reveal-btn" onclick="toggleAnswer('a14')">Reveal Explanation</button>
            <div id="a14" class="answer alert alert-info">
                Features on different scales can dominate model training, e.g., larger numbers overpower smaller ones.
                Normalization ensures all features contribute fairly.
            </div>
        </div>

        <!-- Section 15 -->
        <div class="section">
            <h2> What is the final takeaway about missing data handling in Orange?</h2>
            <p>
                Handling missing data correctly is critical for real-world applications.
            </p>
            <div class="alert alert-info">
                🤔 Think about what you would do if you encountered a dataset with 20% missing values.
            </div>
            <button class="btn btn-outline-primary reveal-btn" onclick="toggleAnswer('a15')">Reveal Explanation</button>
            <div id="a15" class="answer alert alert-info">
                Proper imputation or removal strategies are essential.
                Choose methods based on data type, missing value proportion, and downstream tasks.
                Orange provides flexible tools to experiment and visualize the impact.
            </div>
        </div>

        <!-- Reflection -->
        <div class="section">
            <h2>🧠 Final Reflection</h2>
            <ul>
                <li>Which imputation method would you trust most? Why?</li>
                <li>When is deleting data better than filling it?</li>
                <li>How can poor preprocessing affect real-world systems?</li>
            </ul>
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
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Introduction to Predictive Modeling</title>

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

    <header>
        <h1>Introduction to Predictive Modeling</h1>
        <p>Learn how historical data is used to forecast future outcomes in Business Intelligence.</p>
    </header>

    <div class="content mt-4 mb-5">

        <h2 class="discussion-title">Introduction to Predictive Modeling</h2>

        <p class="discussion-intro">
            Predictive modeling is one of the core components of modern analytics.
            It allows organizations to move beyond understanding what happened,
            and instead forecast what is likely to happen in the future.
            This module introduces the foundations, process, and business applications of predictive modeling.
        </p>

        <hr>

        <h4>What is Predictive Modeling?</h4>

        <p>
            <b>Predictive Modeling</b> is a statistical and machine learning approach used to
            analyze historical data and predict future outcomes.
        </p>

        <p>
            It identifies patterns in past data and uses those patterns to estimate
            future events or behaviors.
        </p>

        <div class="alert alert-info">
            <b>Key idea:</b> Predictive modeling answers the question —
            <i>“What is likely to happen next?”</i>
        </div>

        <hr>

        <h4>From Descriptive to Predictive Analytics</h4>

        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Type of Analytics</th>
                    <th>Question Answered</th>
                    <th>Example</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><b>Descriptive</b></td>
                    <td>What happened?</td>
                    <td>Monthly sales report</td>
                </tr>
                <tr>
                    <td><b>Diagnostic</b></td>
                    <td>Why did it happen?</td>
                    <td>Sales dropped due to low inventory</td>
                </tr>
                <tr>
                    <td><b>Predictive</b></td>
                    <td>What will happen?</td>
                    <td>Forecast next month’s sales</td>
                </tr>
                <tr>
                    <td><b>Prescriptive</b></td>
                    <td>What should we do?</td>
                    <td>Increase marketing budget by 15%</td>
                </tr>
            </tbody>
        </table>

        <hr>

        <h4>Types of Predictive Models</h4>

        <h5>1. Regression (Predicting Numbers)</h5>
        <ul>
            <li>Sales forecasting</li>
            <li>Price prediction</li>
            <li>Demand estimation</li>
        </ul>

        <h5>2. Classification (Predicting Categories)</h5>
        <ul>
            <li>Customer churn (Yes/No)</li>
            <li>Loan approval (Approve/Reject)</li>
            <li>Spam detection</li>
        </ul>

        <h5>3. Time Series Forecasting</h5>
        <ul>
            <li>Enrollment forecasting</li>
            <li>Monthly revenue prediction</li>
        </ul>

        <div class="alert alert-success">
            <b>Key idea:</b> The type of model depends on whether the output is a number,
            a category, or time-based data.
        </div>

        <hr>

        <h4>The Predictive Modeling Process</h4>

        <ol>
            <li><b>Define the Problem</b></li>
            <li><b>Collect Historical Data</b></li>
            <li><b>Clean and Prepare Data</b></li>
            <li><b>Split Data (Training and Testing)</b></li>
            <li><b>Train the Model</b></li>
            <li><b>Evaluate the Model</b></li>
            <li><b>Deploy and Monitor</b></li>
        </ol>

        <div class="alert alert-warning">
            <b>Important:</b> A model must perform well on new data, not just on the training data.
        </div>

        <hr>

        <h4>Simple Concept Example (Linear Regression)</h4>

        <p>Suppose we want to predict sales based on advertising budget:</p>

<pre><code class="language-python">
sales = 5000 + (20 * advertising_budget)
</code></pre>

        <p>
            If advertising_budget = 1000  
            Predicted sales = 5000 + (20 × 1000) = 25,000
        </p>

        <hr>

        <h4>How Do We Evaluate a Model?</h4>

        <p>Different metrics are used depending on the model type:</p>

        <ul>
            <li><b>Accuracy</b> – Overall correctness (Classification)</li>
            <li><b>Precision & Recall</b> – Quality of positive predictions</li>
            <li><b>RMSE</b> – Measures prediction error (Regression)</li>
            <li><b>R²</b> – Explains variance in regression</li>
        </ul>

        <div class="alert alert-info">
            <b>Tip:</b> Always compare your model with a simple baseline prediction.
        </div>

        <hr>

        <h4>Business Applications of Predictive Modeling</h4>

        <ul>
            <li>Customer churn prediction</li>
            <li>Fraud detection</li>
            <li>Inventory demand forecasting</li>
            <li>University enrollment forecasting</li>
            <li>Agricultural yield prediction</li>
        </ul>

        <div class="alert alert-secondary">
            <b>Key idea:</b> Predictive modeling transforms BI dashboards into forward-looking decision tools.
        </div>

        <hr>

        <h4>Quick Reflection Questions</h4>

        <ol>
            <li>What is the difference between regression and classification?</li>
            <li>Why do we split data into training and testing sets?</li>
            <li>Give one example of predictive modeling in an educational institution.</li>
        </ol>

    </div>

</body>

</html>

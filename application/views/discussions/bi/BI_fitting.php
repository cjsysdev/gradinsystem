<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Overfitting vs Underfitting – Machine Learning</title>
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
    body {
      background-color: #f4f6f9;
      font-family: 'Segoe UI', sans-serif;
      color: #2d2d2d;
    }
    header {
      background: linear-gradient(135deg, #1a3c6e, #2e6da4);
      color: white;
      padding: 2.5rem 1.5rem;
      text-align: center;
    }
    header h1 { font-size: 2rem; font-weight: 700; margin-bottom: 0.3rem; }
    header p  { font-size: 1.05rem; opacity: 0.88; margin: 0; }
    .badge-topic {
      display: inline-block;
      background: rgba(255,255,255,0.2);
      border: 1px solid rgba(255,255,255,0.45);
      border-radius: 20px;
      padding: 3px 14px;
      font-size: 0.8rem;
      margin-top: 0.8rem;
      letter-spacing: 0.5px;
    }
    .content { max-width: 900px; margin: 0 auto; }
    .section {
      background: #fff;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.07);
      padding: 1.8rem 2rem;
      margin-bottom: 1.8rem;
    }
    .section h2 {
      font-size: 1.25rem;
      font-weight: 700;
      color: #1a3c6e;
      border-left: 5px solid #2e6da4;
      padding-left: 0.75rem;
      margin-bottom: 1.1rem;
    }
    .section h3 {
      font-size: 1rem;
      font-weight: 700;
      color: #2e6da4;
      margin-top: 1.2rem;
      margin-bottom: 0.4rem;
    }
    /* Objective list */
    .obj-list { list-style: none; padding: 0; margin: 0; }
    .obj-list li { padding: 0.4rem 0 0.4rem 1.8rem; position: relative; }
    .obj-list li::before {
      content: "✓";
      position: absolute; left: 0;
      color: #2e6da4; font-weight: 700;
    }
    /* Model cards */
    .model-card {
      border-radius: 8px;
      padding: 1.2rem 1.4rem;
      height: 100%;
    }
    .card-underfit  { background: #fff3cd; border-left: 5px solid #ffc107; }
    .card-overfit   { background: #f8d7da; border-left: 5px solid #dc3545; }
    .card-goodfit   { background: #d4edda; border-left: 5px solid #28a745; }
    .model-card h4  { font-size: 1rem; font-weight: 700; margin-bottom: 0.5rem; }
    .model-card ul  { padding-left: 1.2rem; margin: 0; font-size: 0.92rem; }
    .model-card ul li { margin-bottom: 0.25rem; }
    /* Metaphor box */
    .metaphor-box {
      background: #eef4fb;
      border-left: 4px solid #2e6da4;
      border-radius: 6px;
      padding: 0.85rem 1.1rem;
      font-size: 0.92rem;
      margin-top: 0.8rem;
      font-style: italic;
      color: #444;
    }
    /* Comparison table */
    .table thead th {
      background: #1a3c6e;
      color: white;
      font-size: 0.88rem;
      vertical-align: middle;
    }
    .table td { font-size: 0.9rem; vertical-align: middle; }
    .badge-underfit { background: #ffc107; color: #333; }
    .badge-overfit  { background: #dc3545; color: #fff; }
    .badge-goodfit  { background: #28a745; color: #fff; }
    /* Metrics cards */
    .metric-card {
      background: #f0f4ff;
      border-radius: 8px;
      padding: 0.9rem 1rem;
      text-align: center;
      height: 100%;
    }
    .metric-card .metric-name  { font-weight: 700; font-size: 0.95rem; color: #1a3c6e; }
    .metric-card .metric-desc  { font-size: 0.82rem; color: #555; margin-top: 0.25rem; }
    /* Accuracy result boxes */
    .result-box {
      border-radius: 8px;
      padding: 1rem 1.2rem;
      margin-bottom: 1rem;
    }
    .result-box .label { font-weight: 700; font-size: 0.88rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .result-box .scores { font-size: 1.3rem; font-weight: 700; margin: 0.3rem 0; }
    .result-box .verdict { font-size: 0.85rem; }
    .result-underfit { background: #fff3cd; border-left: 4px solid #ffc107; }
    .result-overfit  { background: #f8d7da; border-left: 4px solid #dc3545; }
    .result-goodfit  { background: #d4edda; border-left: 4px solid #28a745; }
    /* Sample results table */
    .results-table thead th { background: #1a3c6e; color: white; font-size: 0.85rem; }
    .results-table td { font-size: 0.88rem; }
    .verdict-over { color: #dc3545; font-weight: 700; }
    .verdict-good { color: #28a745; font-weight: 700; }
    .verdict-under{ color: #856404; font-weight: 700; }
    /* Alert boxes */
    .alert-concept {
      border-radius: 8px;
      padding: 1rem 1.2rem;
      font-size: 0.92rem;
    }
    /* Reflection */
    .reflect-item {
      background: #f8f9fa;
      border-radius: 8px;
      padding: 0.8rem 1rem 0.8rem 1.2rem;
      margin-bottom: 0.7rem;
      border-left: 4px solid #6c757d;
      font-size: 0.92rem;
    }
    footer {
      text-align: center;
      font-size: 0.8rem;
      color: #888;
      padding: 2rem 1rem;
    }
  </style>
</head>
<body>

<header>
  <h1>Overfitting vs. Underfitting</h1>
  <p>Understanding Model Performance in Machine Learning</p>
  <div class="badge-topic">Machine Learning · Test &amp; Score · Model Validation</div>
</header>

<div class="content mt-4 mb-5 px-3">

  <!-- OBJECTIVES -->
  <div class="section">
    <h2>🎯 Learning Objectives</h2>
    <ul class="obj-list">
      <li>Define overfitting and underfitting in the context of machine learning.</li>
      <li>Describe the characteristics of a good fit model.</li>
      <li>Compare training accuracy vs. testing accuracy across model types.</li>
      <li>Interpret Test &amp; Score results using Orange to identify model behavior.</li>
      <li>Apply appropriate solutions to address overfitting and underfitting.</li>
    </ul>
  </div>

  <!-- THE CORE PROBLEM -->
  <div class="section">
    <h2>🧩 The Core Problem</h2>
    <p>
      When training a machine learning model, the goal is not just to perform well on the training data — 
      the model must also <strong>generalize</strong> to new, unseen data. Two common problems can prevent this:
    </p>
    <div class="row mt-3">
      <div class="col-md-6 mb-3">
        <div class="model-card card-underfit">
          <h4>📉 Underfitting</h4>
          <p class="mb-1" style="font-size:0.9rem;">The model is <strong>too simple</strong> — it cannot capture patterns even in training data.</p>
          <ul>
            <li>Low training accuracy</li>
            <li>Low testing accuracy</li>
            <li>Unreliable and useless predictions</li>
          </ul>
          <div class="metaphor-box">📚 Like a student who barely studied — they can't answer even basic exam questions.</div>
        </div>
      </div>
      <div class="col-md-6 mb-3">
        <div class="model-card card-overfit">
          <h4>📈 Overfitting</h4>
          <p class="mb-1" style="font-size:0.9rem;">The model is <strong>too complex</strong> — it memorizes training data, including noise.</p>
          <ul>
            <li>Very high training accuracy</li>
            <li>Low testing accuracy</li>
            <li>Fails on real-world unseen data</li>
          </ul>
          <div class="metaphor-box">📚 Like a student who memorized answers word-for-word — they fail when questions are rephrased.</div>
        </div>
      </div>
    </div>
    <div class="row mt-2">
      <div class="col-md-8 offset-md-2 mb-3">
        <div class="model-card card-goodfit">
          <h4>✅ Good Fit</h4>
          <p class="mb-1" style="font-size:0.9rem;">The model finds the <strong>right balance</strong> — it learns real patterns and generalizes well.</p>
          <ul>
            <li>High training accuracy</li>
            <li>High testing accuracy</li>
            <li>Reliable and consistent predictions</li>
          </ul>
          <div class="metaphor-box">📚 Like a student who truly understands the lesson — they answer familiar and new questions with confidence.</div>
        </div>
      </div>
    </div>
  </div>

  <!-- COMPARISON TABLE -->
  <div class="section">
    <h2>📊 Comparison of Model Behavior</h2>
    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead>
          <tr>
            <th>Model Type</th>
            <th>Description</th>
            <th>Train Accuracy</th>
            <th>Test Accuracy</th>
            <th>Reliability</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><span class="badge badge-warning badge-underfit px-2 py-1">Underfitting</span></td>
            <td>Too simple to learn patterns</td>
            <td>Low</td>
            <td>Low</td>
            <td>Very Low</td>
          </tr>
          <tr>
            <td><span class="badge badge-danger badge-overfit px-2 py-1">Overfitting</span></td>
            <td>Memorizes training data</td>
            <td>High</td>
            <td>Low</td>
            <td>Low</td>
          </tr>
          <tr>
            <td><span class="badge badge-success badge-goodfit px-2 py-1">Good Fit ✓</span></td>
            <td>Learns true patterns</td>
            <td>High</td>
            <td>High</td>
            <td>High</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- WHY IT MATTERS -->
  <div class="section">
    <h2>⚠️ Why It Matters</h2>
    <div class="alert alert-warning alert-concept" role="alert">
      <strong>High training accuracy alone is NOT enough</strong> to validate a model's real-world usefulness.
    </div>
    <div class="row mt-3">
      <div class="col-md-6 mb-3">
        <h3>🎯 Real-World Reliability</h3>
        <p style="font-size:0.92rem;">A model that only works on training data is useless in practice. Validation confirms it will perform on new, real-world inputs.</p>
      </div>
      <div class="col-md-6 mb-3">
        <h3>🚨 Avoid False Confidence</h3>
        <p style="font-size:0.92rem;">High training accuracy can be misleading. Without testing validation, we may deploy a flawed model and not even know it.</p>
      </div>
      <div class="col-md-6 mb-3">
        <h3>📐 Model Selection</h3>
        <p style="font-size:0.92rem;">Comparing overfitting vs. underfitting helps us choose the right model complexity and tune hyperparameters correctly.</p>
      </div>
      <div class="col-md-6 mb-3">
        <h3>🔄 Continuous Improvement</h3>
        <p style="font-size:0.92rem;">Understanding model behavior allows iterative improvement using techniques like cross-validation and regularization.</p>
      </div>
    </div>
  </div>

  <!-- USING ORANGE -->
  <div class="section">
    <h2>🔧 Using Orange: Test &amp; Score + Validation</h2>
    <p style="font-size:0.92rem;">
      <strong>Orange</strong> is a visual machine learning tool that evaluates model performance without writing code. 
      It provides clear metrics for comparing training vs. testing performance.
    </p>
    <h3>Key Workflow</h3>
    <ol style="font-size:0.92rem;">
      <li>Load dataset into Orange</li>
      <li>Add a model (e.g., kNN, Naive Bayes)</li>
      <li>Connect to the <strong>Test &amp; Score</strong> widget</li>
      <li>Select a validation method (e.g., cross-validation)</li>
      <li>Read and interpret the output metrics</li>
    </ol>
    <h3>Key Metrics</h3>
    <div class="row mt-2">
      <div class="col-6 col-md-3 mb-3">
        <div class="metric-card">
          <div class="metric-name">CA</div>
          <div class="metric-desc">Classification Accuracy — overall correct predictions</div>
        </div>
      </div>
      <div class="col-6 col-md-3 mb-3">
        <div class="metric-card">
          <div class="metric-name">AUC</div>
          <div class="metric-desc">Area Under ROC Curve — measures classification quality</div>
        </div>
      </div>
      <div class="col-6 col-md-3 mb-3">
        <div class="metric-card">
          <div class="metric-name">Precision</div>
          <div class="metric-desc">Of predicted positives, how many are actually positive</div>
        </div>
      </div>
      <div class="col-6 col-md-3 mb-3">
        <div class="metric-card">
          <div class="metric-name">Recall</div>
          <div class="metric-desc">Of actual positives, how many were correctly identified</div>
        </div>
      </div>
    </div>
  </div>

  <!-- INTERPRETING RESULTS -->
  <div class="section">
    <h2>🔍 Interpreting Test &amp; Score Results</h2>
    <div class="row">
      <div class="col-md-4">
        <div class="result-box result-underfit">
          <div class="label">Case 1 — Underfitting</div>
          <div class="scores">Train: 45% &nbsp;|&nbsp; Test: 42%</div>
          <div class="verdict">Low + Low → Model too simple<br/>
            <strong>Fix:</strong> Increase complexity or add features
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="result-box result-overfit">
          <div class="label">Case 2 — Overfitting</div>
          <div class="scores">Train: 97% &nbsp;|&nbsp; Test: 54%</div>
          <div class="verdict">High + Low → Memorized data<br/>
            <strong>Fix:</strong> Reduce complexity, add regularization or data
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="result-box result-goodfit">
          <div class="label">Case 3 — Good Fit</div>
          <div class="scores">Train: 91% &nbsp;|&nbsp; Test: 88%</div>
          <div class="verdict">High + High → Balanced model<br/>
            <strong>Fix:</strong> None — ready for deployment
          </div>
        </div>
      </div>
    </div>

    <h3 class="mt-3">Sample Model Results</h3>
    <div class="table-responsive">
      <table class="table table-bordered results-table">
        <thead>
          <tr>
            <th>Model</th>
            <th>CA</th>
            <th>AUC</th>
            <th>Precision</th>
            <th>Recall</th>
            <th>Verdict</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>kNN (k=1)</td>
            <td>0.97</td>
            <td>0.99</td>
            <td>0.97</td>
            <td>0.97</td>
            <td class="verdict-over">Overfitting</td>
          </tr>
          <tr>
            <td>kNN (k=5)</td>
            <td>0.92</td>
            <td>0.97</td>
            <td>0.91</td>
            <td>0.92</td>
            <td class="verdict-good">Good Fit ✓</td>
          </tr>
          <tr>
            <td>Naive Bayes</td>
            <td>0.88</td>
            <td>0.96</td>
            <td>0.88</td>
            <td>0.87</td>
            <td class="verdict-good">Good Fit ✓</td>
          </tr>
          <tr>
            <td>Decision Tree</td>
            <td>0.65</td>
            <td>0.72</td>
            <td>0.63</td>
            <td>0.64</td>
            <td class="verdict-under">Underfitting</td>
          </tr>
        </tbody>
      </table>
    </div>
    <p style="font-size:0.88rem; color:#555;">
      kNN (k=5) and Naive Bayes show balanced, consistent scores between training and testing, confirming a good fit.
      kNN (k=1) shows signs of overfitting due to high training but lower generalization. Decision Tree underperforms across all metrics.
    </p>
  </div>

  <!-- SOLUTIONS -->
  <div class="section">
    <h2>🛠️ Solutions at a Glance</h2>
    <div class="row">
      <div class="col-md-6 mb-3">
        <div class="alert alert-warning" role="alert">
          <strong>📉 Fix Underfitting</strong>
          <ul class="mb-0 mt-2" style="font-size:0.9rem;">
            <li>Use a more complex model</li>
            <li>Add more relevant features</li>
            <li>Increase training time / iterations</li>
            <li>Reduce regularization constraints</li>
          </ul>
        </div>
      </div>
      <div class="col-md-6 mb-3">
        <div class="alert alert-danger" role="alert">
          <strong>📈 Fix Overfitting</strong>
          <ul class="mb-0 mt-2" style="font-size:0.9rem;">
            <li>Simplify the model (reduce parameters)</li>
            <li>Apply regularization (L1/L2)</li>
            <li>Collect more training data</li>
            <li>Use cross-validation techniques</li>
            <li>Apply dropout (for neural networks)</li>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- REFLECTION -->
  <div class="section">
    <h2>💬 Reflection Questions</h2>
    <div class="reflect-item">1. A model has 99% training accuracy and 55% test accuracy. What is likely happening, and how would you fix it?</div>
    <div class="reflect-item">2. Why is a model that achieves only 45% on both training and testing considered a failure even on training data?</div>
    <div class="reflect-item">3. In the Orange results table, what difference between kNN (k=1) and kNN (k=5) explains the change in model behavior?</div>
    <div class="reflect-item">4. If you were deploying a model for a hospital diagnosis tool, would you prioritize Precision or Recall? Why?</div>
    <div class="reflect-item">5. What is the purpose of the Test &amp; Score widget in Orange, and why can't we rely on training accuracy alone?</div>
  </div>

</div><!-- /content -->

<footer>
  <p>Machine Learning · Overfitting vs Underfitting · Test &amp; Score + Validation</p>
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
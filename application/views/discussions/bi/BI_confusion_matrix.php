<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Confusion Matrix Decoded</title>
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
    :root {
      --teal:    #2a9d8f;
      --coral:   #e76f51;
      --dark:    #264653;
      --light-bg:#f4f8f8;
      --card-bg: #ffffff;
      --border:  #dee2e6;
      --accent:  #e9f5f3;
    }

    body {
      background: var(--light-bg);
      font-family: 'Segoe UI', sans-serif;
      color: #333;
      font-size: 15px;
    }

    /* ── Header ── */
    header {
      background: var(--dark);
      color: #fff;
      padding: 2.2rem 1.5rem 1.8rem;
      text-align: center;
    }
    header h1 {
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: .4rem;
    }
    header p {
      font-size: 1rem;
      opacity: .85;
      margin: 0;
    }
    header .badge-topic {
      display: inline-block;
      background: var(--teal);
      color: #fff;
      border-radius: 20px;
      padding: 3px 14px;
      font-size: .78rem;
      margin-bottom: .8rem;
      letter-spacing: .5px;
      text-transform: uppercase;
    }

    /* ── Section cards ── */
    .section {
      background: var(--card-bg);
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,.07);
      padding: 1.6rem 1.8rem;
      margin-bottom: 1.6rem;
    }
    .section h2 {
      font-size: 1.2rem;
      font-weight: 700;
      color: var(--dark);
      border-left: 4px solid var(--teal);
      padding-left: .75rem;
      margin-bottom: 1rem;
    }
    .section h3 {
      font-size: 1rem;
      font-weight: 600;
      color: var(--teal);
      margin: 1rem 0 .4rem;
    }

    /* ── Objectives list ── */
    .obj-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    .obj-list li {
      padding: .4rem 0 .4rem 1.6rem;
      position: relative;
    }
    .obj-list li::before {
      content: "✓";
      color: var(--teal);
      font-weight: 700;
      position: absolute;
      left: 0;
    }

    /* ── Matrix grid ── */
    .matrix-wrap {
      overflow-x: auto;
    }
    .conf-matrix {
      display: grid;
      grid-template-columns: 120px 1fr 1fr;
      grid-template-rows: auto 1fr 1fr;
      gap: 4px;
      max-width: 440px;
      margin: 1rem auto;
      font-size: .85rem;
      font-weight: 600;
      text-align: center;
    }
    .conf-matrix .corner   { background: transparent; }
    .conf-matrix .col-head { background: var(--dark); color: #fff; padding: 8px; border-radius: 6px 6px 0 0; }
    .conf-matrix .row-head { background: var(--dark); color: #fff; padding: 8px; border-radius: 6px 0 0 6px; writing-mode: horizontal-tb; display: flex; align-items: center; justify-content: center; }
    .conf-matrix .tp { background: var(--teal); color: #fff; padding: 18px 10px; border-radius: 6px; }
    .conf-matrix .fn { background: var(--coral); color: #fff; padding: 18px 10px; border-radius: 6px; }
    .conf-matrix .fp { background: var(--coral); color: #fff; padding: 18px 10px; border-radius: 6px; }
    .conf-matrix .tn { background: var(--teal); color: #fff; padding: 18px 10px; border-radius: 6px; }
    .conf-matrix .cell-label { font-size: .7rem; font-weight: 400; display: block; margin-top: 4px; opacity: .9; }

    /* ── Quadrant cards ── */
    .quad-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
    }
    .quad-card {
      border-radius: 8px;
      padding: 1rem;
      color: #fff;
    }
    .quad-card.teal  { background: var(--teal); }
    .quad-card.coral { background: var(--coral); }
    .quad-card strong { display: block; font-size: .95rem; margin-bottom: .3rem; }
    .quad-card span { font-size: .82rem; opacity: .92; }

    /* ── Formula pills ── */
    .formula-box {
      background: var(--accent);
      border: 1px solid #b2dfdb;
      border-radius: 8px;
      padding: .8rem 1.1rem;
      font-family: 'Courier New', monospace;
      font-size: .9rem;
      color: var(--dark);
      margin: .6rem 0;
    }

    /* ── Metric table ── */
    .metric-table th { background: var(--dark); color: #fff; }
    .metric-table td, .metric-table th { vertical-align: middle; font-size: .85rem; }
    .pill-tp { background: var(--teal); color: #fff; border-radius: 4px; padding: 1px 6px; }
    .pill-fp { background: var(--coral); color: #fff; border-radius: 4px; padding: 1px 6px; }
    .pill-fn { background: var(--coral); color: #fff; border-radius: 4px; padding: 1px 6px; }
    .pill-tn { background: var(--teal); color: #fff; border-radius: 4px; padding: 1px 6px; }

    /* ── Alerts ── */
    .alert-tip  { background: #e9f5f3; border-left: 4px solid var(--teal);  color: #1b5e50; }
    .alert-warn { background: #fef3ee; border-left: 4px solid var(--coral); color: #7b2d14; }
    .alert-info { background: #e8f4fd; border-left: 4px solid #3498db;     color: #1a4a6b; }
    .alert-tip, .alert-warn, .alert-info { padding: .75rem 1rem; border-radius: 6px; margin: .7rem 0; font-size: .88rem; }

    /* ── Playbook cards ── */
    .playbook-item {
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: .9rem 1.1rem;
      margin-bottom: .7rem;
    }
    .playbook-item .pb-goal   { font-weight: 700; color: var(--coral); }
    .playbook-item .pb-metric { font-weight: 700; color: var(--teal); font-size: 1rem; }
    .playbook-item .pb-ex     { font-size: .82rem; color: #666; margin-top: .25rem; }

    /* ── Reflection ── */
    .reflection-box {
      background: #fff8f0;
      border: 1px dashed var(--coral);
      border-radius: 8px;
      padding: 1rem 1.2rem;
    }
    .reflection-box ol { padding-left: 1.3rem; margin: 0; }
    .reflection-box li { margin-bottom: .5rem; }

    @media (max-width: 576px) {
      header h1 { font-size: 1.4rem; }
      .quad-grid { grid-template-columns: 1fr; }
      .conf-matrix { font-size: .75rem; }
    }
  </style>
</head>
<body>

<header>
  <div class="badge-topic">Machine Learning · Model Evaluation</div>
  <h1>The Confusion Matrix Decoded</h1>
  <p>Understanding diagnostic metrics and measuring what actually matters</p>
</header>

<div class="content mt-4 mb-5">
  <div class="container">

    <!-- ① OBJECTIVES -->
    <div class="section">
      <h2>Learning Objectives</h2>
      <p>By the end of this discussion, you should be able to:</p>
      <ul class="obj-list">
        <li>Explain why accuracy alone can be a misleading metric</li>
        <li>Identify and define the four quadrants of the confusion matrix (TP, FP, FN, TN)</li>
        <li>Compute Precision, Recall, Specificity, and F1 Score from a given matrix</li>
        <li>Distinguish Type I (False Positive) from Type II (False Negative) errors</li>
        <li>Decide which metric to optimize based on real-world context</li>
        <li>Read and interpret a multi-class confusion matrix</li>
      </ul>
    </div>

    <!-- ② CONCEPT: ACCURACY PARADOX -->
    <div class="section">
      <h2>The Accuracy Paradox</h2>
      <p>
        Accuracy sounds like everything you need: if a model gets 60 out of 100 predictions right, it's 60% accurate.
        Simple, right? Unfortunately, a single percentage <strong>masks the anatomy of failure</strong>. It cannot tell you:
      </p>
      <ul>
        <li>Did the model miss things that were really there? (False Negatives)</li>
        <li>Did the model hallucinate things that weren't there? (False Positives)</li>
      </ul>
      <div class="alert-warn">
        ⚠️ <strong>The Paradox:</strong> A model that always predicts "Negative" on a dataset with 95% negative cases achieves
        95% accuracy — yet it has detected exactly zero actual positives. High accuracy, zero usefulness.
      </div>
      <div class="formula-box">Accuracy = (TP + TN) / Total &nbsp;→&nbsp; hides the <em>type</em> of errors made</div>
    </div>

    <!-- ③ CONCEPT: ANATOMY OF MATRIX -->
    <div class="section">
      <h2>The Anatomy of the Matrix</h2>
      <p>
        The <strong>Confusion Matrix</strong> (also called the Error Matrix) is a contingency table that pits
        <em>actual labels</em> against <em>predicted labels</em>. Correct predictions sit on the main diagonal
        ("Zone of Truth"); everything off-diagonal is a specific type of mistake ("Zone of Confusion").
      </p>

      <h3>The Four Quadrants</h3>
      <div class="quad-grid">
        <div class="quad-card teal">
          <strong>✔ True Positive (TP)</strong>
          <span>The Hit. Condition is real — model correctly detected it.</span>
        </div>
        <div class="quad-card coral">
          <strong>🔔 False Positive (FP)</strong>
          <span>The False Alarm. Condition is absent — model wrongly claimed it was present. (Type I Error)</span>
        </div>
        <div class="quad-card coral">
          <strong>🔍 False Negative (FN)</strong>
          <span>The Miss. Condition is real — model completely missed it. (Type II Error)</span>
        </div>
        <div class="quad-card teal">
          <strong>🛡 True Negative (TN)</strong>
          <span>The Correct Rejection. Condition is absent — model correctly ignored it.</span>
        </div>
      </div>

      <h3>Visual Layout</h3>
      <div class="matrix-wrap">
        <div class="conf-matrix">
          <div class="corner"></div>
          <div class="col-head">Predicted Positive</div>
          <div class="col-head">Predicted Negative</div>

          <div class="row-head">Actual Positive</div>
          <div class="tp">TP<span class="cell-label">Correct Detection</span></div>
          <div class="fn">FN<span class="cell-label">Missed (Type II)</span></div>

          <div class="row-head">Actual Negative</div>
          <div class="fp">FP<span class="cell-label">False Alarm (Type I)</span></div>
          <div class="tn">TN<span class="cell-label">Correct Rejection</span></div>
        </div>
      </div>
    </div>

    <!-- ④ CONCEPT: COST OF ERRORS -->
    <div class="section">
      <h2>The Cost of Being Wrong</h2>
      <p>Not all errors carry the same weight. The real-world consequences differ drastically:</p>
      <div class="row">
        <div class="col-md-6">
          <div style="border:2px solid var(--coral); border-radius:8px; padding:1rem;">
            <strong style="color:var(--coral);">Type I Error — False Positive</strong>
            <p class="mb-1 mt-2" style="font-size:.88rem;">
              <em>Example:</em> Diagnosing a <strong>healthy</strong> person with COVID.<br>
              <em>Cost:</em> Unnecessary quarantine, anxiety, wasted test resources.
            </p>
          </div>
        </div>
        <div class="col-md-6 mt-3 mt-md-0">
          <div style="border:2px solid var(--teal); border-radius:8px; padding:1rem;">
            <strong style="color:var(--dark);">Type II Error — False Negative</strong>
            <p class="mb-1 mt-2" style="font-size:.88rem;">
              <em>Example:</em> Clearing a patient who <strong>actually has</strong> COVID.<br>
              <em>Cost:</em> Sick person unknowingly spreads the virus to the public.
            </p>
          </div>
        </div>
      </div>
      <div class="alert-warn mt-3">
        ⚖️ In medical screening, an FN is <strong>far more dangerous</strong> than an FP — a missed case can trigger
        an outbreak, while a false alarm merely causes inconvenience.
      </div>
    </div>

    <!-- ⑤ CONCEPT: METRICS -->
    <div class="section">
      <h2>The Four Key Metrics</h2>

      <h3>1. Precision (Positive Predictive Value)</h3>
      <p><em>Plain English:</em> "Out of all the times the model cried wolf, how many times was there actually a wolf?"</p>
      <div class="formula-box">Precision = TP / (TP + FP)</div>
      <div class="alert-tip">📌 <strong>Covid example:</strong> 45 positive results from 1,000 tests; only 30 truly had COVID. Precision = 30/45 = <strong>66.7%</strong>. One-third of alarms were false.</div>

      <h3>2. Recall (Sensitivity / True Positive Rate)</h3>
      <p><em>Plain English:</em> "Out of all the actual positives in reality, how many did the model successfully find?"</p>
      <div class="formula-box">Recall = TP / (TP + FN)</div>
      <div class="alert-tip">📌 <strong>Covid example:</strong> 35 people with COVID in the population; test flagged 30. Recall = 30/35 = <strong>85.7%</strong>. Five sick patients were missed entirely.</div>

      <h3>3. Specificity (True Negative Rate)</h3>
      <p><em>Plain English:</em> "Out of all the actual negatives in reality, how many did the model correctly reject?"</p>
      <div class="formula-box">Specificity = TN / (TN + FP)</div>
      <div class="alert-tip">📌 A high-specificity test excels at correctly identifying healthy people, making a positive result highly trustworthy for confirming a condition.</div>

      <h3>4. F1 Score (Harmonic Mean)</h3>
      <p>
        When Precision and Recall pull in opposite directions (improving one worsens the other), the
        <strong>F1 Score</strong> balances both into a single number. Unlike simple accuracy, F1 severely
        punishes models that sacrifice one metric to inflate the other.
      </p>
      <div class="formula-box">F1 = 2 × (Precision × Recall) / (Precision + Recall)</div>
      <div class="alert-tip">📌 <strong>Covid example:</strong> Precision = 0.667, Recall = 0.857 → F1 = <strong>0.75</strong></div>
    </div>

    <!-- ⑥ EXAMPLE: CATS vs DOGS -->
    <div class="section">
      <h2>Worked Example — Cats vs. Dogs Classifier</h2>
      <p>
        A classifier processes <strong>12 images</strong> (8 Cats, 4 Dogs). It makes <strong>9 correct</strong>
        predictions and misses 3.
      </p>
      <div class="matrix-wrap">
        <table class="table table-bordered metric-table text-center" style="max-width:400px; margin:auto;">
          <thead>
            <tr>
              <th></th>
              <th>Predicted: Cat</th>
              <th>Predicted: Dog</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <th>Actual: Cat</th>
              <td style="background:#e9f5f3;"><strong>6</strong><br><small>Cats correctly identified</small></td>
              <td style="background:#fef3ee;"><strong>2</strong><br><small>Cats wrongly called Dogs</small></td>
            </tr>
            <tr>
              <th>Actual: Dog</th>
              <td style="background:#fef3ee;"><strong>1</strong><br><small>Dog wrongly called a Cat</small></td>
              <td style="background:#e9f5f3;"><strong>3</strong><br><small>Dogs correctly identified</small></td>
            </tr>
          </tbody>
        </table>
      </div>
      <h3 class="mt-3">Calculations (treating "Cat" as Positive)</h3>
      <div class="formula-box">Precision = 6 / (6+2) = 6/8 = <strong>75%</strong></div>
      <div class="formula-box">Recall    = 6 / (6+1) = 6/7 ≈ <strong>85.7%</strong></div>
      <div class="formula-box">F1        = 2 × (0.75 × 0.857) / (0.75 + 0.857) ≈ <strong>0.80</strong></div>
      <div class="alert-info">📐 The bold diagonal (6, 3) = correct predictions. Everything off-diagonal = specific classification errors.</div>
    </div>

    <!-- ⑦ CONCEPT: ERROR TRADE-OFF -->
    <div class="section">
      <h2>The Error Trade-off Spectrum</h2>
      <p>
        There is <strong>no perfect model</strong>. You cannot eliminate all errors — you can only choose
        <em>which errors</em> you are willing to tolerate. Moving the decision threshold shifts the balance:
      </p>
      <div class="row text-center mt-3">
        <div class="col-md-6">
          <div style="background:var(--accent); border-radius:8px; padding:1rem;">
            <strong>High Sensitivity / Low Specificity</strong>
            <p style="font-size:.85rem; margin:.5rem 0 0;">
              Catches almost all positives (few FN) but generates many false alarms (many FP).
              Good when <em>missing a case is catastrophic</em>.
            </p>
          </div>
        </div>
        <div class="col-md-6 mt-3 mt-md-0">
          <div style="background:#fef3ee; border-radius:8px; padding:1rem;">
            <strong>Low Sensitivity / High Specificity</strong>
            <p style="font-size:.85rem; margin:.5rem 0 0;">
              Rarely triggers false alarms (few FP) but misses many positives (many FN).
              Good when <em>false alarms are costly or harmful</em>.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- ⑧ CONCEPT: PRACTITIONER'S PLAYBOOK -->
    <div class="section">
      <h2>The Practitioner's Playbook: What to Optimize?</h2>
      <p>The best metric depends on your real-world goal and the <strong>asymmetry of error costs</strong>:</p>

      <div class="playbook-item">
        <span class="pb-goal">Goal: Minimize False Negatives (misses are fatal)</span>
        <div class="pb-metric">→ Optimize for RECALL</div>
        <div class="pb-ex">Examples: Medical screening, Fraud detection, Safety systems</div>
      </div>
      <div class="playbook-item">
        <span class="pb-goal">Goal: Minimize False Positives (false alarms are costly)</span>
        <div class="pb-metric">→ Optimize for PRECISION</div>
        <div class="pb-ex">Examples: Spam filters, YouTube recommendations, Arrest warrants</div>
      </div>
      <div class="playbook-item">
        <span class="pb-goal">Goal: Balance both equally</span>
        <div class="pb-metric">→ Optimize for F1 SCORE</div>
        <div class="pb-ex">Examples: General search algorithms, Automated content tagging</div>
      </div>
    </div>

    <!-- ⑨ CONCEPT: METRIC SUMMARY TABLE -->
    <div class="section">
      <h2>The Metric Rosetta Stone</h2>
      <div class="table-responsive">
        <table class="table table-bordered metric-table">
          <thead>
            <tr>
              <th>Metric</th>
              <th>Alias</th>
              <th>Formula</th>
              <th>Plain-English Question</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Accuracy</td>
              <td>—</td>
              <td>(<span class="pill-tp">TP</span> + <span class="pill-tn">TN</span>) / Total</td>
              <td>How often is the model right overall?</td>
            </tr>
            <tr>
              <td>Precision</td>
              <td>PPV</td>
              <td><span class="pill-tp">TP</span> / (<span class="pill-tp">TP</span> + <span class="pill-fp">FP</span>)</td>
              <td>When it cries wolf, is there a wolf?</td>
            </tr>
            <tr>
              <td>Recall</td>
              <td>Sensitivity, TPR</td>
              <td><span class="pill-tp">TP</span> / (<span class="pill-tp">TP</span> + <span class="pill-fn">FN</span>)</td>
              <td>Did we find all the actual wolves?</td>
            </tr>
            <tr>
              <td>Specificity</td>
              <td>TNR</td>
              <td><span class="pill-tn">TN</span> / (<span class="pill-tn">TN</span> + <span class="pill-fp">FP</span>)</td>
              <td>Did we correctly ignore the sheep?</td>
            </tr>
            <tr>
              <td>F1 Score</td>
              <td>Harmonic Mean</td>
              <td>2×(P×R)/(P+R)</td>
              <td>Is the model balanced without major blind spots?</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ⑩ REFLECTION -->
    <div class="section">
      <h2>Reflection Questions</h2>
      <div class="reflection-box">
        <ol>
          <li>A COVID rapid test reports 99% accuracy on a population where only 1% are infected. Why might this be completely useless?</li>
          <li>A spam filter blocks 100% of spam but also blocks 40% of legitimate emails. Which metric is perfect, and which one is terrible?</li>
          <li>A self-driving car's pedestrian detector misses 1 in 10 pedestrians but never triggers a false stop. Should the engineers be satisfied? Why or why not?</li>
          <li>In the Cats vs. Dogs example, which class would you treat as "Positive" if you were a dog shelter trying to screen stray animals? Does the choice change your Precision and Recall values?</li>
          <li>When would you prefer a model with F1 = 0.70 over one with Accuracy = 0.95?</li>
        </ol>
      </div>
    </div>

  </div><!-- /container -->
</div><!-- /content -->

    <!-- Bootstrap JS -->
    <script src="<?= base_url('assets/jquery.slim.min.js') ?>"></script>
    <script src="<?= base_url('assets/bootstrap.bundle.min.js') ?>"></script>

    <!-- Highlight.js init -->
    <script>
        hljs.highlightAll();
    </script>
</body>
</html>
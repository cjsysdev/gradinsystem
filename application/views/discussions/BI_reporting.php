<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BI Reporting Hub - Orange Data Mining 2026</title>
    <style>
        :root {
            --orange-main: #ff8c00;
            --navy: #1e293b;
            --slate: #64748b;
            --bg: #f1f5f9;
        }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--bg);
            color: #1e293b;
            margin: 0;
            padding: 40px 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        header {
            text-align: center;
            margin-bottom: 50px;
        }

        h1 {
            color: var(--navy);
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 25px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            border-top: 4px solid var(--orange-main);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }

        .tag {
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--orange-main);
            letter-spacing: 0.05em;
        }

        h3 {
            margin: 10px 0;
            color: var(--navy);
            font-size: 1.3rem;
        }

        p {
            font-size: 0.95rem;
            color: #475569;
            flex-grow: 1;
            line-height: 1.5;
        }

        .links {
            margin-top: 20px;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
        }

        .btn {
            display: block;
            background: var(--navy);
            color: white;
            text-align: center;
            padding: 10px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 10px;
            transition: 0.2s;
        }

        .btn:hover {
            background: var(--orange-main);
        }

        .sim {
            font-size: 0.85rem;
            color: var(--slate);
        }

        .sim a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
        }

        footer {
            text-align: center;
            margin-top: 60px;
            color: var(--slate);
            font-size: 0.85rem;
        }
    </style>
</head>

<body>

    <div class="container">
        <header>
            <h1>BI & Data Mining Reporting</h1>
            <p style="color:var(--slate)">Comprehensive Workflow Guide for Orange Data Mining</p>
        </header>

        <div class="grid">
            <div class="card">
                <span class="tag">Impute Widget</span>
                <h3>1. Handling Missing Data</h3>
                <p>Ensure dataset integrity by replacing gaps using averages, constant values, or model-based predictions (1-NN).</p>
                <div class="links">
                    <a href="https://orangedatamining.com/widget-catalog/transform/impute/" class="btn" target="_blank">Impute Documentation</a>
                    <div class="sim">Similar: <a href="https://orangedatamining.com/blog/2022/2022-04-12-handling-missing-values/">Missing Data Strategies</a></div>
                </div>
            </div>

            <div class="card">
                <span class="tag">Preprocess Widget</span>
                <h3>2. Data Normalization</h3>
                <p>Standardize feature scales to prevent high-magnitude numbers (like revenue) from biasing distance-based algorithms.</p>
                <div class="links">
                    <a href="https://orangedatamining.com/widget-catalog/transform/preprocess/" class="btn" target="_blank">Normalization Guide</a>
                    <div class="sim">Similar: <a href="https://orange3.readthedocs.io/en/latest/widgets/data/preprocess.html">Preprocessing Deep Dive</a></div>
                </div>
            </div>

            <div class="card">
                <span class="tag">Rank Widget</span>
                <h3>3. Feature Selection</h3>
                <p>Use Information Gain or Chi-Square to identify which business attributes most impact your target KPI.</p>
                <div class="links">
                    <a href="https://orangedatamining.com/widget-catalog/data/rank/" class="btn" target="_blank">Ranking Documentation</a>
                    <div class="sim">Similar: <a href="https://orangedatamining.com/blog/2021/2021-09-08-feature-selection/">Feature Subset Selection</a></div>
                </div>
            </div>

            <div class="card">
                <span class="tag">Tree Viewer Widget</span>
                <h3>4. Decision Tree Model</h3>
                <p>Generate transparent "If-Then" rules that allow business managers to see the logic behind every prediction.</p>
                <div class="links">
                    <a href="https://orangedatamining.com/widget-catalog/visualize/treeviewer/" class="btn" target="_blank">Tree Viewer Documentation</a>
                    <div class="sim">Similar: <a href="https://orangedatamining.com/blog/2021/2021-03-24-decision-trees-and-logic/">Visualizing Logic</a></div>
                </div>
            </div>

            <div class="card">
                <span class="tag">Test & Score Widget</span>
                <h3>5. Model Comparison</h3>
                <p>Contrast multiple algorithms (Random Forest vs LogReg) using cross-validation to select the best performer.</p>
                <div class="links">
                    <a href="https://orangedatamining.com/widget-catalog/evaluate/testandscore/" class="btn" target="_blank">Testing Documentation</a>
                    <div class="sim">Similar: <a href="https://orangedatamining.com/blog/2022/2022-01-05-how-to-evaluate-models/">Evaluation Metrics 101</a></div>
                </div>
            </div>

            <div class="card">
                <span class="tag">Confusion Matrix Widget</span>
                <h3>6. Confusion Matrix</h3>
                <p>Analyze specific prediction errors to calculate the financial impact of False Positives vs False Negatives.</p>
                <div class="links">
                    <a href="https://orangedatamining.com/widget-catalog/evaluate/confusionmatrix/" class="btn" target="_blank">Matrix Documentation</a>
                    <div class="sim">Similar: <a href="https://orangedatamining.com/blog/2020/2020-04-01-understanding-confusion-matrix/">Error Analysis Blog</a></div>
                </div>
            </div>

            <div class="card">
                <span class="tag">Scatter Plot / Box Plot</span>
                <h3>7. Data Visualization</h3>
                <p>Create visual stories to spot trends. Use the 'Find Informative Projections' feature to automate insight discovery.</p>
                <div class="links">
                    <a href="https://orangedatamining.com/widget-catalog/visualize/scatterplot/" class="btn" target="_blank">Visual Catalog</a>
                    <div class="sim">Similar: <a href="https://orangedatamining.com/blog/2022/2022-03-02-visualization-tricks/">Advanced Viz Tricks</a></div>
                </div>
            </div>

            <div class="card">
                <span class="tag">Select Data / Interactivity</span>
                <h3>8. Dashboard Design</h3>
                <p>Link widgets together so that clicking a point in one chart filters a table in another—perfect for live BI demos.</p>
                <div class="links">
                    <a href="https://orangedatamining.com/blog/2020/2020-06-23-interactivity-in-orange/" class="btn" target="_blank">Interactive Design</a>
                    <div class="sim">Similar: <a href="https://orangedatamining.com/getting-started/">Workflow Templates</a></div>
                </div>
            </div>

            <div class="card">
                <span class="tag">Scoring Sheet / Explainers</span>
                <h3>9. Decision Making</h3>
                <p>Translate model scores into a final "Yes/No" decision sheet. Use 'Explain Model' to defend your strategy to stakeholders.</p>
                <div class="links">
                    <a href="https://orangedatamining.com/blog/explain-scoring-sheet/" class="btn" target="_blank">Explainable AI (XAI)</a>
                    <div class="sim">Similar: <a href="https://www.tableau.com/learn/articles/business-intelligence/decision-making">BI Strategy Theory</a></div>
                </div>
            </div>

            <div class="card">
                <span class="tag">Workflow Integration</span>
                <h3>10. BI Workflow</h3>
                <p>Combine data ingestion, cleaning, modeling, and reporting into a single, automated, and repeatable system.</p>
                <div class="links">
                    <a href="https://orangedatamining.com/workflows/" class="btn" target="_blank">Workflow Library</a>
                    <div class="sim">Similar: <a href="https://www.youtube.com/playlist?list=PLmNPvQr9nsh-M9_z6_oY8Y_3j5fS_A6V-">YouTube: Beginner to Pro</a></div>
                </div>
            </div>
        </div>

        <footer>
            &copy; 2026 BI Team | Powered by Orange Data Mining
        </footer>
    </div>

</body>

</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Fundamentals</title>

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
        <h1>Data Fundamentals</h1>
        <p>Understanding the types, sources, and nature of data used in Business Intelligence.</p>
    </header>

    <div class="content mt-4 mb-5">

        <h2 class="discussion-title">Module 2: Data Fundamentals</h2>

        <p class="discussion-intro">
            Data is the foundation of Business Intelligence. Before data can be analyzed or visualized,
            it is important to understand what data is, how it differs from information and knowledge,
            and the different forms data can take in modern information systems.
        </p>

        <hr>

        <h4>1. Data, Information, and Knowledge</h4>

        <p>
            <b>Data</b> refers to raw facts and figures without context.
            <br><b>Information</b> is processed data that has meaning.
            <br><b>Knowledge</b> is the understanding gained from analyzing information.
        </p>

        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Concept</th>
                    <th>Description</th>
                    <th>Example</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><b>Data</b></td>
                    <td>Raw, unprocessed facts</td>
                    <td>100, 200, 300</td>
                </tr>
                <tr>
                    <td><b>Information</b></td>
                    <td>Processed and meaningful data</td>
                    <td>Daily sales = ₱300</td>
                </tr>
                <tr>
                    <td><b>Knowledge</b></td>
                    <td>Insights used for decisions</td>
                    <td>Sales are increasing</td>
                </tr>
            </tbody>
        </table>

        <div class="alert alert-info">
            <b>Key idea:</b> BI focuses on converting data into information and knowledge.
        </div>

        <hr>

        <h4>2. Types of Data by Structure</h4>

        <p>Data can be classified based on how it is organized:</p>

        <ul>
            <li><b>Structured Data:</b> Organized in tables with rows and columns (e.g., databases).</li>
            <li><b>Semi-Structured Data:</b> Has structure but not in table form (e.g., JSON, XML).</li>
            <li><b>Unstructured Data:</b> No predefined structure (e.g., text, images, videos).</li>
        </ul>

        <div class="alert alert-success">
            <b>Key idea:</b> BI systems must handle different data structures.
        </div>

        <hr>

        <h4>3. Types of Data by Business Use</h4>

        <ul>
            <li><b>Transactional Data:</b> Records daily operations (sales, payments).</li>
            <li><b>Behavioral Data:</b> Tracks user actions (clicks, views).</li>
            <li><b>Demographic Data:</b> Describes users (age, location).</li>
        </ul>

        <p>
            These data types are commonly used together in BI analysis.
        </p>

        <hr>

        <h4>4. Data Sources</h4>

        <p>BI systems collect data from multiple sources:</p>

        <ul>
            <li><b>Internal Sources:</b> Sales systems, HR systems, inventory databases</li>
            <li><b>External Sources:</b> Social media, market data, third-party providers</li>
        </ul>

        <div class="alert alert-warning">
            <b>Key idea:</b> Combining internal and external data improves insights.
        </div>

        <hr>

        <h4>5. Data Quality Issues</h4>

        <p>Good BI depends on high-quality data. Common data quality problems include:</p>

        <ul>
            <li>Missing values</li>
            <li>Duplicate records</li>
            <li>Incorrect or outdated data</li>
            <li>Inconsistent formats</li>
        </ul>

        <div class="alert alert-danger">
            <b>Key idea:</b> Poor data quality leads to poor decisions.
        </div>

        <hr>

        <h4>6. Data Fundamentals in Daily Life</h4>

        <p>
            Everyday platforms use different data types:
        </p>

        <ul>
            <li>YouTube uses behavioral data (watch time)</li>
            <li>Social media uses engagement data</li>
            <li>Online stores use transactional data</li>
        </ul>

        <hr>

        <h4>7. Quick Activity: Identify the Data Type</h4>

        <p>Identify the type of data used in each
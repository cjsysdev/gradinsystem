<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data, Information, and Metadata</title>

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

<body >

    <header>
        <h1>Data, Information, and Metadata</h1>
        <p>Learn how the difference between data, information, and metadata works in computing systems.</p>
    </header>


    <div class="content mt-4 mb-5">

        <h2 class="discussion-title">Data, Information, and Metadata</h2>

        <p class="discussion-intro">
            In computing and information systems, the terms <b>data</b>, <b>information</b>, and <b>metadata</b>
            are closely related but not the same. Understanding their differences is important when working with
            databases, information systems, and data analysis.
        </p>

        <hr>

        <h4>1. What is Data?</h4>

        <p>
            <b>Data</b> refers to raw, unprocessed facts and figures. By themselves, data may not have meaning
            until they are organized or interpreted.
        </p>

        <p>Examples of data:</p>
        <ul>
            <li>Numbers: <code>85, 90, 72</code></li>
            <li>Text: <code>"Juan", "Maria"</code></li>
            <li>Dates: <code>2025-01-15</code></li>
            <li>Sensor readings: <code>36.5°C</code></li>
        </ul>

        <div class="alert alert-info">
            <b>Key idea:</b> Data is raw and does not explain itself.
        </div>

        <hr>

        <h4>2. What is Information?</h4>

        <p>
            <b>Information</b> is data that has been processed, organized, or summarized so that it becomes
            meaningful and useful.
        </p>

        <p>Examples of information:</p>
        <ul>
            <li>"The average score of the class is 82."</li>
            <li>"Maria scored the highest grade in the exam."</li>
            <li>"The temperature today is higher than yesterday."</li>
        </ul>

        <div class="alert alert-success">
            <b>Key idea:</b> Information answers questions like <i>what happened?</i> or <i>what does it mean?</i>
        </div>

        <hr>

        <h4>3. What is Metadata?</h4>

        <p>
            <b>Metadata</b> is often described as <i>data about data</i>. It provides additional details that
            describe, explain, or give context to data.
        </p>

        <p>Examples of metadata:</p>
        <ul>
            <li>Date a file was created or modified</li>
            <li>File size and file type (e.g., PDF, JPG)</li>
            <li>Author of a document</li>
            <li>Column names and data types in a database table</li>
        </ul>

        <div class="alert alert-warning">
            <b>Key idea:</b> Metadata helps us understand, manage, and organize data.
        </div>

        <hr>

        <h4>4. Simple Comparison</h4>

        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Term</th>
                    <th>Description</th>
                    <th>Example</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><b>Data</b></td>
                    <td>Raw facts and figures</td>
                    <td>85, 90, 72</td>
                </tr>
                <tr>
                    <td><b>Information</b></td>
                    <td>Processed and meaningful data</td>
                    <td>Average score is 82</td>
                </tr>
                <tr>
                    <td><b>Metadata</b></td>
                    <td>Data about data</td>
                    <td>Exam date, student ID</td>
                </tr>
            </tbody>
        </table>

        <hr>

        <h4>5. Activity: Data, Information, or Metadata?</h4>

        <p>
            Identify whether each item below is <b>Data</b>, <b>Information</b>, or <b>Metadata</b>.
            Write your answer beside each number.
        </p>

        <ol>
            <li>Student name: <code>"Carlos Dela Cruz"</code></li>
            <li>"The total number of students who passed is 45."</li>
            <li>Date when the report was created</li>
            <li>Scores: <code>78, 85, 91, 88</code></li>
            <li>File size of a document: <code>2.3 MB</code></li>
            <li>"The highest temperature recorded today is 34°C."</li>
            <li>Column name in a database table: <code>birth_date</code></li>
        </ol>

        <div class="alert alert-secondary">
            <b>Tip:</b>
            <ul>
                <li>If it is raw → <b>Data</b></li>
                <li>If it explains or summarizes → <b>Information</b></li>
                <li>If it describes other data → <b>Metadata</b></li>
            </ul>
        </div>

    </div>

</body>

</html>
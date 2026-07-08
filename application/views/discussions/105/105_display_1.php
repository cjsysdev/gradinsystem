<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Displaying SQL Results with var_dump</title>

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
</head>

<body>

    <header>
        <h1>Displaying SQL Results with <code>var_dump</code></h1>
        <p>Quick PHP Output — No HTML Table Needed</p>
    </header>

    <div class="content mt-4 mb-5">

        <!-- Objectives -->
        <div class="section">
            <h2>Learning Objectives</h2>
            <ul>
                <li>Fetch SQL query results into a PHP variable</li>
                <li>Use <code class="inline-code">var_dump()</code> to inspect data directly</li>
                <li>Understand what the output tells you about each row</li>
            </ul>
        </div>

        <!-- Concept -->
        <div class="section">
            <h2>Why var_dump?</h2>
            <p>
                When learning or testing, you don't always need a fancy HTML table.
                <code class="inline-code">var_dump()</code> prints the <strong>raw structure and values</strong> of a variable —
                perfect for checking if your query is actually returning data.
            </p>
            <div class="alert alert-info">
                Think of it as "peek inside the box" before you decide how to display it nicely.
            </div>
        </div>

        <!-- Step 1 -->
        <div class="section">
            <h2>Step 1 — Query and Fetch All Rows</h2>
            <p>Use <code class="inline-code">mysqli_fetch_all()</code> to grab every row at once into an array.</p>
            <pre><code class="language-php">&lt;?php
$conn = mysqli_connect("localhost", "root", "", "school_db");

$sql    = "SELECT id, name, course FROM students";
$result = mysqli_query($conn, $sql);

$rows = mysqli_fetch_all($result, MYSQLI_ASSOC);

var_dump($rows);
?&gt;</code></pre>

            <div class="alert alert-warning mt-2">
                <code class="inline-code">MYSQLI_ASSOC</code> tells PHP to use column names as keys (same as <code class="inline-code">fetch_assoc</code>).
                Without it, you get numeric indexes.
            </div>
        </div>

        <!-- Sample Output -->
        <div class="section">
            <h2>Sample Output</h2>
            <p>This is what <code class="inline-code">var_dump($rows)</code> prints in the browser:</p>
            <pre><code class="language-php">array(2) {
  [0]=>
  array(3) {
    ["id"]     => string(1) "1"
    ["name"]   => string(14) "Juan Dela Cruz"
    ["course"] => string(4) "BSIT"
  }
  [1]=>
  array(3) {
    ["id"]     => string(1) "2"
    ["name"]   => string(12) "Maria Santos"
    ["course"] => string(4) "BSEd"
  }
}</code></pre>

            <!-- Fetch One Row -->
            <div class="section">
                <h2>Fetching a Single Row</h2>
                <p>If you only need one row, use <code class="inline-code">mysqli_fetch_assoc()</code> once:</p>
                <pre><code class="language-php">&lt;?php
$sql    = "SELECT id, name, course FROM students WHERE id = 1";
$result = mysqli_query($conn, $sql);

$row = mysqli_fetch_assoc($result);

var_dump($row);
?&gt;</code></pre>
                <div class="alert alert-success mt-2">
                    This returns a single associative array — no loop needed.
                </div>
            </div>

            <!-- Complete Example -->
            <div class="section">
                <h2>Complete Example</h2>
                <pre><code class="language-php">&lt;?php
$conn = mysqli_connect("localhost", "root", "", "school_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql    = "SELECT id, name, course FROM students";
$result = mysqli_query($conn, $sql);
$rows   = mysqli_fetch_all($result, MYSQLI_ASSOC);

var_dump($rows);

mysqli_close($conn);
?&gt;</code></pre>
            </div>

            <!-- Reflection -->
            <div class="section">
                <h2>Reflection</h2>
                <div class="reflection">
                    <code>var_dump()</code> is like opening the box to see exactly what's inside before wrapping it.
                    Once you confirm the data is there and correct, that's when you move on to displaying it properly in a table.
                </div>
            </div>

        </div>

        <script>
            hljs.highlightAll();
        </script>
</body>

</html>
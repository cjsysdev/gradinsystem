<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Introduction to Connecting Database to PHP</title>

    <!-- Bootstrap 4.5.2 -->
    <link rel="stylesheet" href="<?php echo base_url('assets/bootstrap.min.css'); ?>">
    <!-- Custom Style -->
    <link rel="stylesheet" href="<?php echo base_url('assets/discussion-style.css'); ?>">
    <!-- Highlight.js -->
    <link rel="stylesheet" href="<?php echo base_url('assets/atom-one-light.min.css'); ?>">
</head>

<body>

    <header class="text-center py-4">
        <h1>Introduction to Connecting Database to PHP</h1>
        <p class="lead">Understanding how PHP communicates with MySQL databases</p>
    </header>

    <div class="container content mt-4 mb-5">

        <!-- Learning Objectives -->
        <div class="section">
            <h2>Learning Objectives</h2>
            <ul>
                <li>Understand the role of PHP in database-driven applications.</li>
                <li>Identify the prerequisites for connecting PHP to MySQL.</li>
                <li>Learn how to establish a database connection using PHP.</li>
                <li>Differentiate between MySQLi and PDO.</li>
            </ul>
        </div>

        <!-- Introduction -->
        <div class="section">
            <h2>What is Database Connection in PHP?</h2>
            <p>
                Connecting a database to PHP means allowing your PHP program to communicate with a database
                (such as MySQL) so it can store, retrieve, update, and delete data.
            </p>
            <p>
                This connection is essential for building dynamic web applications like login systems,
                online stores, and student information systems.
            </p>

            <div class="alert alert-info">
                💡 Example: When a user logs in, PHP checks the username and password stored in the database.
            </div>
        </div>

        <!-- Prerequisites -->
        <div class="section">
            <h2>Prerequisites</h2>
            <ul>
                <li>Basic knowledge of PHP and HTML</li>
                <li>A running web server (e.g., Apache via XAMPP/WAMP)</li>
                <li>MySQL database installed and running</li>
                <li>PHP MySQL extension enabled</li>
                <li>A database with at least one table</li>
            </ul>

            <div class="alert alert-warning">
                ⚠️ PHP cannot directly access a database without a server environment.
            </div>
        </div>

        <!-- Key Components -->
        <div class="section">
            <h2>Required Connection Information</h2>
            <p>To connect PHP to a MySQL database, you need the following:</p>
            <ul>
                <li><strong>Server Name</strong> (e.g., localhost)</li>
                <li><strong>Username</strong></li>
                <li><strong>Password</strong></li>
                <li><strong>Database Name</strong></li>
            </ul>

            <div class="alert alert-info">
                💡 These credentials act like a "key" to access your database.
            </div>
        </div>

        <!-- MySQLi Example -->
        <div class="section">
            <h2>Example: Connecting using MySQLi</h2>

            <pre><code class="language-php">
&lt;?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "my_database";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "Connected successfully";
?&gt;
</code></pre>

            <p>
                The <code>mysqli_connect()</code> function connects PHP to MySQL using server credentials.
                If the connection fails, it returns false. :contentReference[oaicite:0]{index=0}
            </p>
        </div>

        <!-- PDO Example -->
        <div class="section">
            <h2>Example: Connecting using PDO</h2>

            <pre><code class="language-php">
&lt;?php
$host = "localhost";
$dbname = "my_database";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully";
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?&gt;
</code></pre>

            <p>
                PDO (PHP Data Objects) is more flexible because it can work with multiple database systems
                and supports better error handling. :contentReference[oaicite:1]{index=1}
            </p>
        </div>

        <!-- Comparison -->
        <div class="section">
            <h2>MySQLi vs PDO</h2>
            <ul>
                <li><strong>MySQLi</strong> – works only with MySQL</li>
                <li><strong>PDO</strong> – supports multiple databases</li>
                <li>Both support prepared statements for security</li>
            </ul>

            <div class="alert alert-success">
                ✅ Best Practice: Use PDO for flexibility and security.
            </div>
        </div>

        <!-- Real-world Example -->
        <div class="section">
            <h2>Real-World Example</h2>
            <p>
                Imagine a <strong>Student Grading System</strong>:
            </p>
            <ul>
                <li>Students enter their ID</li>
                <li>PHP connects to the database</li>
                <li>The system retrieves grades from MySQL</li>
                <li>Results are displayed on the webpage</li>
            </ul>

            <div class="alert alert-info">
                💡 Without database connection, dynamic systems like this cannot function.
            </div>
        </div>

        <!-- Process Flow -->
        <div class="section">
            <h2>Basic Flow of Connection</h2>
            <ol>
                <li>User interacts with a web page</li>
                <li>PHP script runs on the server</li>
                <li>PHP connects to MySQL</li>
                <li>Query is executed</li>
                <li>Results are returned and displayed</li>
            </ol>
        </div>

        <!-- Reflection -->
        <div class="section">
            <h2>Reflection Questions</h2>
            <ol>
                <li>Why is a database connection important in web development?</li>
                <li>What happens if the connection fails?</li>
                <li>When would you prefer PDO over MySQLi?</li>
                <li>What are the risks of incorrect database credentials?</li>
            </ol>
        </div>

    </div>

    <!-- Scripts -->
    <script src="<?php echo base_url('assets/highlight.min.js'); ?>"></script>
    <script>
        hljs.highlightAll();
    </script>

</body>

</html>
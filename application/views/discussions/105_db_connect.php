<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Connecting SQL to the Web</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="<?= base_url('assets/bootstrap.4.5.2.min.css') ?>">

    <!-- Discussion Style -->
    <!-- <link rel="stylesheet" href="<?= base_url('assets/discussion-style.css') ?>"> -->

    <!-- Highlight.js -->
    <link rel="stylesheet" href="<?= base_url('assets/highlights/atom-one-light.min.css') ?>">
    <script src="<?= base_url('assets/highlights/11.7.0-highlight.min.js') ?>"></script>
    <script>
        hljs.highlightAll();
    </script>

    <style>
        /* ── Base ── */
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #2d3748;
            font-size: 0.97rem;
        }

        /* ── Header ── */
        header {
            background: linear-gradient(135deg, #1a365d 0%, #2b6cb0 100%);
            color: #fff;
            padding: 2.5rem 0 2rem;
            margin-bottom: 2rem;
        }

        header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
        }

        header p.subtitle {
            font-size: 1rem;
            opacity: 0.85;
            margin: 0;
        }

        /* ── Content wrapper ── */
        .content {
            max-width: 860px;
            margin: 0 auto;
        }

        /* ── Section cards ── */
        .section {
            background: #ffffff;
            border-radius: 10px;
            padding: 1.6rem 1.8rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
        }

        /* ── Section headings with left border ── */
        .section h2 {
            font-size: 1.15rem;
            font-weight: 700;
            color: #1a365d;
            border-left: 4px solid #2b6cb0;
            padding-left: 0.75rem;
            margin-bottom: 1rem;
        }

        /* ── Objectives list ── */
        .objectives-list {
            padding-left: 1.2rem;
            margin: 0;
        }

        .objectives-list li {
            margin-bottom: 0.45rem;
        }

        /* ── Concept flow ── */
        .flow-diagram {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.4rem;
            margin: 1rem 0;
        }

        .flow-step {
            background: #ebf4ff;
            border: 1.5px solid #bee3f8;
            border-radius: 6px;
            padding: 0.4rem 0.8rem;
            font-size: 0.88rem;
            font-weight: 600;
            color: #2b6cb0;
        }

        .flow-arrow {
            color: #a0aec0;
            font-size: 1.1rem;
        }

        /* ── Code blocks ── */
        pre {
            border-radius: 8px;
            font-size: 0.88rem;
            margin-top: 0.75rem;
            margin-bottom: 0.75rem;
        }

        pre code {
            border-radius: 8px;
        }

        .code-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #718096;
            margin-bottom: 0.2rem;
        }

        /* ── Alerts ── */
        .alert-tip {
            background: #f0fff4;
            border-left: 4px solid #38a169;
            border-radius: 6px;
            padding: 0.9rem 1.1rem;
            margin-bottom: 0.9rem;
        }

        .alert-tip .alert-title {
            font-weight: 700;
            color: #276749;
            margin-bottom: 0.2rem;
        }

        .alert-warning {
            background: #fffaf0;
            border-left: 4px solid #dd6b20;
            border-radius: 6px;
            padding: 0.9rem 1.1rem;
            margin-bottom: 0.9rem;
        }

        .alert-warning .alert-title {
            font-weight: 700;
            color: #9c4221;
            margin-bottom: 0.2rem;
        }

        .alert-info {
            background: #ebf8ff;
            border-left: 4px solid #3182ce;
            border-radius: 6px;
            padding: 0.9rem 1.1rem;
            margin-bottom: 0.9rem;
        }

        .alert-info .alert-title {
            font-weight: 700;
            color: #2c5282;
            margin-bottom: 0.2rem;
        }

        /* ── Reflection box ── */
        .reflection-box {
            background: #faf5ff;
            border: 1.5px dashed #9f7aea;
            border-radius: 8px;
            padding: 1rem 1.2rem;
        }

        .reflection-box ol {
            margin: 0.5rem 0 0;
            padding-left: 1.3rem;
        }

        .reflection-box li {
            margin-bottom: 0.5rem;
            color: #44337a;
        }

        /* ── Table ── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0.7rem;
        }

        th {
            background: #2b6cb0;
            color: #fff;
            padding: 0.55rem 0.8rem;
            font-size: 0.88rem;
            text-align: left;
        }

        td {
            padding: 0.5rem 0.8rem;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.88rem;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:nth-child(even) td {
            background: #f7fafc;
        }

        /* ── Badge ── */
        .badge-section {
            display: inline-block;
            background: #2b6cb0;
            color: #fff;
            font-size: 0.72rem;
            font-weight: 700;
            border-radius: 20px;
            padding: 0.15rem 0.65rem;
            margin-bottom: 0.6rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        /* ── Mobile ── */
        @media (max-width: 600px) {
            header h1 {
                font-size: 1.5rem;
            }

            .section {
                padding: 1.1rem 1rem;
            }

            .flow-diagram {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>

    <!-- ══ HEADER ══ -->
    <header>
        <div class="container">
            <h1>Connecting SQL to the Web</h1>
            <p class="subtitle">Introductory Discussion &nbsp;·&nbsp; PHP &amp; MySQL &nbsp;·&nbsp; Web-Based Data Display</p>
        </div>
    </header>

    <!-- ══ BODY ══ -->
    <div class="container content mt-4 mb-5">

        <!-- 1. OBJECTIVES -->
        <div class="section">
            <span class="badge-section">Objectives</span>
            <h2>Learning Objectives</h2>
            <p>By the end of this discussion, you should be able to:</p>
            <ol class="objectives-list">
                <li>Connect a PHP script to a MySQL database using <code>mysqli</code>.</li>
                <li>Execute a basic <code>SELECT</code> query from PHP.</li>
                <li>Display query results as an HTML table on a web page.</li>
                <li>Handle connection errors gracefully.</li>
                <li>Understand the flow of data from the database to the browser.</li>
            </ol>
        </div>

        <!-- 2. CONCEPT -->
        <div class="section">
            <span class="badge-section">Concept</span>
            <h2>How Does It All Connect?</h2>

            <p>Think of your database like a <strong>library stockroom</strong> — full of organized information but hidden from visitors. PHP is the <strong>librarian</strong> who goes into the stockroom, fetches what you need, and brings it out to display on the <strong>reading table</strong> (your web page).</p>

            <div class="flow-diagram">
                <div class="flow-step">🌐 Browser</div>
                <div class="flow-arrow">→</div>
                <div class="flow-step">📄 PHP Script</div>
                <div class="flow-arrow">→</div>
                <div class="flow-step">🔗 DB Connection</div>
                <div class="flow-arrow">→</div>
                <div class="flow-step">🗄️ MySQL Server</div>
                <div class="flow-arrow">→</div>
                <div class="flow-step">📦 Result Set</div>
                <div class="flow-arrow">→</div>
                <div class="flow-step">🌐 HTML Output</div>
            </div>

            <p class="mt-2">The three key steps are always:</p>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Step</th>
                        <th>What Happens</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td><strong>Connect</strong></td>
                        <td>PHP opens a connection to the MySQL server using credentials.</td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td><strong>Query</strong></td>
                        <td>PHP sends a SQL statement and receives a result set.</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td><strong>Display</strong></td>
                        <td>PHP loops through the rows and renders them as HTML.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- 3. EXAMPLES -->
        <div class="section">
            <span class="badge-section">Examples</span>
            <h2>Step-by-Step Code Examples</h2>

            <!-- Step 1 -->
            <p><strong>Step 1 — Set up the database (run this in phpMyAdmin or MySQL CLI)</strong></p>
            <div class="code-label">SQL</div>
            <pre><code class="language-sql">CREATE DATABASE school;
USE school;

CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    grade VARCHAR(10),
    score FLOAT
);

INSERT INTO students (name, grade, score) VALUES
('Ana Reyes',   'Grade 10', 92.5),
('Ben Cruz',    'Grade 10', 85.0),
('Carla Diaz',  'Grade 11', 78.3),
('Dan Santos',  'Grade 11', 95.1);</code></pre>

            <!-- Step 2 -->
            <p class="mt-3"><strong>Step 2 — Connect to the database in PHP</strong></p>
            <div class="code-label">PHP — connect.php</div>
            <pre><code class="language-php">&lt;?php
// Database credentials
$host     = "localhost";
$username = "root";
$password = "";        // Default WAMPServer password is empty
$database = "school";

// Open connection
$conn = new mysqli($host, $username, $password, $database);

// Check for errors
if ($conn-&gt;connect_error) {
    die("Connection failed: " . $conn-&gt;connect_error);
}

echo "Connected successfully!";
?&gt;</code></pre>

            <div class="alert-info">
                <div class="alert-title">ℹ️ WAMPServer Default Credentials</div>
                On a fresh WAMPServer install: host = <code>localhost</code>, username = <code>root</code>, password = <em>(empty string)</em>.
            </div>

            <!-- Step 3 -->
            <p class="mt-3"><strong>Step 3 — Query and display results as an HTML table</strong></p>
            <div class="code-label">PHP — students.php</div>
            <pre><code class="language-php">&lt;?php
$host = "localhost";
$username = "root";
$password = "";
$database = "school";

$conn = new mysqli($host, $username, $password, $database);

if ($conn-&gt;connect_error) {
    die("Connection failed: " . $conn-&gt;connect_error);
}

// Run the SELECT query
$sql    = "SELECT id, name, grade, score FROM students ORDER BY score DESC";
$result = $conn-&gt;query($sql);
?&gt;

&lt;!DOCTYPE html&gt;
&lt;html&gt;
&lt;head&gt;
    &lt;title&gt;Student List&lt;/title&gt;
    &lt;style&gt;
        table { border-collapse: collapse; width: 60%; }
        th, td { border: 1px solid #ccc; padding: 8px 12px; }
        th { background: #2b6cb0; color: white; }
        tr:nth-child(even) { background: #f0f4f8; }
    &lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;

&lt;h2&gt;Student Scores&lt;/h2&gt;

&lt;?php if ($result-&gt;num_rows &gt; 0): ?&gt;
    &lt;table&gt;
        &lt;tr&gt;
            &lt;th&gt;ID&lt;/th&gt;
            &lt;th&gt;Name&lt;/th&gt;
            &lt;th&gt;Grade&lt;/th&gt;
            &lt;th&gt;Score&lt;/th&gt;
        &lt;/tr&gt;

        &lt;?php while ($row = $result-&gt;fetch_assoc()): ?&gt;
        &lt;tr&gt;
            &lt;td&gt;&lt;?= $row['id'] ?&gt;&lt;/td&gt;
            &lt;td&gt;&lt;?= $row['name'] ?&gt;&lt;/td&gt;
            &lt;td&gt;&lt;?= $row['grade'] ?&gt;&lt;/td&gt;
            &lt;td&gt;&lt;?= $row['score'] ?&gt;&lt;/td&gt;
        &lt;/tr&gt;
        &lt;?php endwhile; ?&gt;

    &lt;/table&gt;
&lt;?php else: ?&gt;
    &lt;p&gt;No students found.&lt;/p&gt;
&lt;?php endif; ?&gt;

&lt;/body&gt;
&lt;/html&gt;

&lt;?php $conn-&gt;close(); ?&gt;</code></pre>

            <div class="alert-tip">
                <div class="alert-title">✅ Key Pattern to Remember</div>
                <code>$result-&gt;fetch_assoc()</code> returns one row at a time as an associative array. Loop with <code>while</code> until all rows are consumed.
            </div>
        </div>

        <!-- 4. ALERTS / COMMON MISTAKES -->
        <div class="section">
            <span class="badge-section">Watch Out</span>
            <h2>Common Mistakes &amp; Reminders</h2>

            <div class="alert-warning">
                <div class="alert-title">⚠️ Wrong Database Name or Credentials</div>
                Double-check your <code>$database</code> variable. A typo here causes a silent failure or a "Connection failed" message. Use phpMyAdmin to confirm your DB name.
            </div>

            <div class="alert-warning">
                <div class="alert-title">⚠️ Forgetting to Check <code>num_rows</code></div>
                Always check <code>$result-&gt;num_rows &gt; 0</code> before looping. Otherwise, you may loop over an empty result and get unexpected output.
            </div>

            <div class="alert-tip">
                <div class="alert-title">✅ Close Your Connection</div>
                Always call <code>$conn-&gt;close()</code> at the end of your script to free up server resources. It's a good habit even on small projects.
            </div>

            <div class="alert-info">
                <div class="alert-title">ℹ️ WAMPServer "Site Can't Be Reached"?</div>
                If you get this error, try running <code>ipconfig /flushdns</code> in the Windows Command Prompt, then reload the page.
            </div>
        </div>

        <!-- 5. REFLECTION -->
        <div class="section">
            <span class="badge-section">Reflection</span>
            <h2>Think About It</h2>
            <div class="reflection-box">
                <p>Answer these questions in your notebook or share in class discussion:</p>
                <ol>
                    <li>What would happen if you forgot the <code>while</code> loop and just called <code>fetch_assoc()</code> once?</li>
                    <li>What changes if you want to show only students from <strong>Grade 11</strong>? Which part of the SQL would you modify?</li>
                    <li>How is fetching rows from a database similar to reading lines from a file one by one?</li>
                    <li>In your own words, describe the role PHP plays between the browser and the database.</li>
                </ol>
            </div>
        </div>

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
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SQL UPDATE Statement</title>

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
        <h1>SQL UPDATE Statement</h1>
        <p>Modifying existing records safely using conditions and best practices</p>
    </header>

    <main class="container my-4">

        <!-- LEARNING OBJECTIVES -->
        <div class="section">
            <h2>Learning Objectives</h2>
            <ul>
                <li>Explain what the <code>UPDATE</code> statement does and when to use it.</li>
                <li>Write safe <code>UPDATE</code> queries using <code>WHERE</code> conditions.</li>
                <li>Update one or multiple columns in a single statement.</li>
                <li>Use <code>LIMIT</code> and <code>ORDER BY</code> (MySQL) for controlled updates.</li>
                <li>Apply best practices to avoid accidental mass updates.</li>
            </ul>
        </div>

        <!-- CONCEPT OVERVIEW -->
        <div class="section">
            <h2>Concept Overview</h2>
            <p>
                The <code>UPDATE</code> statement modifies existing rows in a table.
                Unlike <code>INSERT</code> (which adds new records), <code>UPDATE</code> changes values already stored.
                Because it can affect many rows at once, using a <strong>correct WHERE condition</strong> is critical.
            </p>

            <div class="alert alert-warning">
                <strong>Warning:</strong> Running <code>UPDATE</code> without <code>WHERE</code> updates <u>all rows</u> in the table.
            </div>
        </div>

        <!-- BASIC SYNTAX -->
        <div class="section">
            <h2>Basic Syntax</h2>
            <pre><code class="language-sql">UPDATE table_name
SET column1 = value1,
    column2 = value2,
    ...
WHERE condition;</code></pre>

            <ul>
                <li><code>table_name</code> = the table you want to modify</li>
                <li><code>SET</code> = specifies which column(s) to change</li>
                <li><code>WHERE</code> = filters which row(s) should be updated</li>
            </ul>
        </div>

        <!-- EXAMPLES -->
        <div class="section">
            <h2>Examples</h2>

            <h3 class="mt-3">1) Update a Single Column</h3>
            <p>Scenario: Update the email of user with <code>UserID = 101</code>.</p>
            <pre><code class="language-sql">UPDATE Users
SET Email = 'maria.santos@email.com'
WHERE UserID = 101;</code></pre>

            <h3 class="mt-3">2) Update Multiple Columns</h3>
            <p>Scenario: Change both phone number and status for a specific user.</p>
            <pre><code class="language-sql">UPDATE Users
SET Phone = '0917-123-4567',
    Status = 'Active'
WHERE UserID = 101;</code></pre>

            <h3 class="mt-3">3) Update Multiple Rows Using a Condition</h3>
            <p>Scenario: Mark all students in section <code>BSIT-2A</code> as <code>Enrolled</code>.</p>
            <pre><code class="language-sql">UPDATE Students
SET EnrollmentStatus = 'Enrolled'
WHERE Section = 'BSIT-2A';</code></pre>

            <h3 class="mt-3">4) Update Using a Computation</h3>
            <p>Scenario: Increase all product prices in category <code>'Snacks'</code> by 10%.</p>
            <pre><code class="language-sql">UPDATE Products
SET Price = Price * 1.10
WHERE Category = 'Snacks';</code></pre>

            <h3 class="mt-3">5) MySQL Controlled Updates (ORDER BY + LIMIT)</h3>
            <p>Scenario: Update only the latest 1 pending order (use with care).</p>
            <pre><code class="language-sql">UPDATE Orders
SET Status = 'Processing'
WHERE Status = 'Pending'
ORDER BY OrderDate DESC
LIMIT 1;</code></pre>

            <div class="alert alert-info mt-3">
                <strong>Note:</strong> <code>ORDER BY</code> and <code>LIMIT</code> in <code>UPDATE</code> are common in MySQL,
                but not supported the same way in all SQL databases.
            </div>
        </div>

        <!-- BEST PRACTICES -->
        <div class="section">
            <h2>Best Practices for Safe UPDATE</h2>
            <ol>
                <li>
                    <strong>Preview rows first</strong> using a matching <code>SELECT</code>.
                    <pre><code class="language-sql">SELECT *
FROM Users
WHERE UserID = 101;</code></pre>
                </li>
                <li>
                    <strong>Use primary keys</strong> when possible (e.g., <code>UserID</code>) to target exactly one row.
                </li>
                <li>
                    <strong>Update only the columns you need</strong> (avoid changing unrelated fields).
                </li>
                <li>
                    <strong>Use transactions</strong> when doing risky updates (if your DB supports it).
                    <pre><code class="language-sql">START TRANSACTION;

UPDATE Products
SET Price = Price * 1.10
WHERE Category = 'Snacks';

-- If correct:
COMMIT;

-- If wrong:
-- ROLLBACK;</code></pre>
                </li>
                <li>
                    <strong>Avoid mass updates accidentally</strong> — never forget <code>WHERE</code> unless you truly mean “all rows”.
                </li>
            </ol>
        </div>

        <!-- COMMON MISTAKES -->
        <div class="section">
            <h2>Common Mistakes</h2>

            <h3 class="mt-3">❌ Mistake 1: Missing WHERE</h3>
            <pre><code class="language-sql">UPDATE Students
SET EnrollmentStatus = 'Enrolled';</code></pre>
            <p class="text-danger">
                This updates <strong>every student</strong>.
            </p>

            <h3 class="mt-3">❌ Mistake 2: Wrong Condition</h3>
            <pre><code class="language-sql">UPDATE Users
SET Status = 'Inactive'
WHERE Status = 'Active';</code></pre>
            <p>
                This might be valid—but only if you truly intend to change <u>all active users</u>.
                Always confirm with a <code>SELECT</code> first.
            </p>

            <h3 class="mt-3">❌ Mistake 3: Quoting Numbers Incorrectly (Context Matters)</h3>
            <p>
                Many databases allow quotes around numbers, but it can cause confusion.
                Keep types consistent with your schema (INTEGER vs VARCHAR).
            </p>
        </div>

        <!-- MINI CHECKPOINT -->
        <div class="section">
            <h2>Quick Check (Try This)</h2>
            <ol>
                <li>Write an <code>UPDATE</code> query that changes a student’s <code>Section</code> to <code>BSIT-2B</code> for <code>StudentID = 20230012</code>.</li>
                <li>Write an <code>UPDATE</code> query that increases all prices by 5% for products where <code>Stock &lt; 10</code>.</li>
                <li>Why is it safer to run a <code>SELECT</code> first before an <code>UPDATE</code>?</li>
            </ol>
        </div>

        <!-- HANDS-ON LAB -->
        <div class="section">
            <h2>Hands-on Lab Activity: Safe Updates in a Student Enrollment Table</h2>
            <p>
                Goal: Practice updating records using a realistic table and verifying changes before committing them.
            </p>

            <h3 class="mt-3">Step 1: Create the Table</h3>
            <pre><code class="language-sql">CREATE TABLE Students (
    StudentID INT PRIMARY KEY,
    FullName VARCHAR(100) NOT NULL,
    Section VARCHAR(20) NOT NULL,
    EnrollmentStatus VARCHAR(20) NOT NULL DEFAULT 'Pending'
);</code></pre>

            <h3 class="mt-3">Step 2: Insert Sample Data</h3>
            <pre><code class="language-sql">INSERT INTO Students (StudentID, FullName, Section, EnrollmentStatus) VALUES
(20230001, 'Maria Santos', 'BSIT-2A', 'Pending'),
(20230002, 'Juan Dela Cruz', 'BSIT-2A', 'Pending'),
(20230003, 'Ana Reyes', 'BSIT-2B', 'Enrolled'),
(20230004, 'Paolo Lim', 'BSIT-2A', 'Pending');</code></pre>

            <h3 class="mt-3">Step 3: Preview Before Updating</h3>
            <pre><code class="language-sql">SELECT * FROM Students
WHERE Section = 'BSIT-2A' AND EnrollmentStatus = 'Pending';</code></pre>

            <h3 class="mt-3">Step 4: Perform the UPDATE</h3>
            <pre><code class="language-sql">UPDATE Students
SET EnrollmentStatus = 'Enrolled'
WHERE Section = 'BSIT-2A' AND EnrollmentStatus = 'Pending';</code></pre>

            <h3 class="mt-3">Step 5: Verify the Result</h3>
            <pre><code class="language-sql">SELECT * FROM Students
ORDER BY StudentID;</code></pre>

            <h3 class="mt-4">Lab Questions</h3>
            <ol>
                <li>How many rows were updated? (Check the affected rows message)</li>
                <li>Which part of the query ensured only BSIT-2A pending students were updated?</li>
                <li>What would happen if the <code>WHERE</code> clause was removed?</li>
            </ol>
        </div>

        <!-- SUMMARY -->
        <div class="section">
            <h2>Summary</h2>
            <ul>
                <li><code>UPDATE</code> modifies existing records (not adding new ones).</li>
                <li><code>WHERE</code> is the safety filter—use it to target specific rows.</li>
                <li>Preview with <code>SELECT</code> and verify after updating.</li>
                <li>For risky updates, use transactions (<code>COMMIT</code>/<code>ROLLBACK</code>).</li>
            </ul>
        </div>

    </main>

    <footer class="text-center my-4">
        <small>Prepared for SQL Fundamentals • Practice safe updates ✅</small>
    </footer>

    <?php $this->load->view('web_to_image'); ?>


</body>

</html>
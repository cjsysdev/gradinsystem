<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SQL SELECT Statement</title>

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
        <h1>SQL SELECT Statement</h1>
        <p>Retrieving data from tables (columns, rows, filtering, sorting, and limiting results)</p>
    </header>

    <main class="container">

        <!-- Learning Objectives -->
        <section class="section">
            <h2>Learning Objectives</h2>
            <ul>
                <li>Explain what the <code>SELECT</code> statement does and when it is used</li>
                <li>Retrieve specific columns and all columns using <code>*</code></li>
                <li>Filter records using <code>WHERE</code> with operators and patterns</li>
                <li>Sort results using <code>ORDER BY</code> and limit outputs using <code>LIMIT</code></li>
                <li>Use <code>DISTINCT</code> and basic aggregate functions with <code>GROUP BY</code></li>
            </ul>
        </section>

        <!-- Concept Overview -->
        <section class="section">
            <h2>Concept Overview</h2>
            <p>
                The <strong>SQL SELECT statement</strong> is used to <strong>read/retrieve data</strong> from one or more tables.
                Unlike <code>INSERT</code>, <code>UPDATE</code>, and <code>DELETE</code> (which modify data),
                <code>SELECT</code> is mainly used to <strong>query</strong> and display results.
            </p>

            <div class="note">
                <strong>Key idea:</strong> SQL queries describe <em>what</em> you want, not step-by-step instructions on <em>how</em> to get it.
            </div>
        </section>

        <!-- Sample Table -->
        <section class="section">
            <h2>Sample Data</h2>
            <p>We will use a simple table named <code>students</code> for examples:</p>

            <pre><code class="language-sql">CREATE TABLE students (
  student_id INT PRIMARY KEY,
  full_name  VARCHAR(100),
  course     VARCHAR(50),
  year_level INT,
  age        INT
);

INSERT INTO students (student_id, full_name, course, year_level, age) VALUES
(101, 'Maria Santos', 'BSIT', 1, 18),
(102, 'John Dela Cruz', 'BSCS', 2, 19),
(103, 'Ana Reyes', 'BSIT', 2, 20),
(104, 'Paolo Garcia', 'BSIS', 1, 18),
(105, 'Liza Mendoza', 'BSCS', 3, 21);</code></pre>
        </section>

        <!-- Basic SELECT -->
        <section class="section">
            <h2>1) Basic SELECT</h2>

            <h3>A. Select all columns</h3>
            <pre><code class="language-sql">SELECT * FROM students;</code></pre>
            <p>
                <code>*</code> means “all columns”. This is useful for quick checking, but in real systems,
                selecting only the needed columns is better (faster + cleaner output).
            </p>

            <h3>B. Select specific columns</h3>
            <pre><code class="language-sql">SELECT full_name, course FROM students;</code></pre>
        </section>

        <!-- WHERE clause -->
        <section class="section">
            <h2>2) Filtering with WHERE</h2>
            <p>
                Use <code>WHERE</code> to retrieve only the rows that match a condition.
            </p>

            <h3>A. Comparison operators</h3>
            <pre><code class="language-sql">SELECT * 
FROM students
WHERE year_level = 2;</code></pre>

            <pre><code class="language-sql">SELECT full_name, age
FROM students
WHERE age &gt;= 20;</code></pre>

            <h3>B. AND / OR / NOT</h3>
            <pre><code class="language-sql">SELECT * 
FROM students
WHERE course = 'BSCS' AND year_level &gt;= 2;</code></pre>

            <pre><code class="language-sql">SELECT * 
FROM students
WHERE course = 'BSIT' OR course = 'BSIS';</code></pre>

            <h3>C. IN (multiple matches)</h3>
            <pre><code class="language-sql">SELECT * 
FROM students
WHERE course IN ('BSIT', 'BSCS');</code></pre>

            <h3>D. BETWEEN (range)</h3>
            <pre><code class="language-sql">SELECT * 
FROM students
WHERE age BETWEEN 18 AND 20;</code></pre>

            <h3>E. LIKE (pattern matching)</h3>
            <pre><code class="language-sql">SELECT * 
FROM students
WHERE full_name LIKE 'A%';</code></pre>
            <ul>
                <li><code>%</code> = any number of characters</li>
                <li><code>_</code> = exactly one character</li>
            </ul>
        </section>

        <!-- DISTINCT -->
        <section class="section">
            <h2>3) DISTINCT (Unique values)</h2>
            <p>
                <code>DISTINCT</code> removes duplicate rows from the selected columns.
            </p>
            <pre><code class="language-sql">SELECT DISTINCT course
FROM students;</code></pre>
        </section>

        <!-- ORDER BY + LIMIT -->
        <section class="section">
            <h2>4) Sorting and Limiting Results</h2>

            <h3>A. ORDER BY</h3>
            <pre><code class="language-sql">SELECT * 
FROM students
ORDER BY full_name ASC;</code></pre>

            <pre><code class="language-sql">SELECT * 
FROM students
ORDER BY age DESC;</code></pre>

            <h3>B. LIMIT (MySQL)</h3>
            <pre><code class="language-sql">SELECT * 
FROM students
ORDER BY age DESC
LIMIT 3;</code></pre>

            <div class="note">
                <strong>Tip:</strong> When using <code>LIMIT</code>, always combine it with <code>ORDER BY</code> if you want meaningful “top results”.
            </div>
        </section>

        <!-- Aggregates + GROUP BY -->
        <section class="section">
            <h2>5) Aggregates and GROUP BY</h2>

            <p>
                Aggregate functions summarize data:
                <code>COUNT()</code>, <code>SUM()</code>, <code>AVG()</code>, <code>MIN()</code>, <code>MAX()</code>.
            </p>

            <h3>A. COUNT all students</h3>
            <pre><code class="language-sql">SELECT COUNT(*) AS total_students
FROM students;</code></pre>

            <h3>B. Count students per course (GROUP BY)</h3>
            <pre><code class="language-sql">SELECT course, COUNT(*) AS total
FROM students
GROUP BY course;</code></pre>

            <h3>C. Filter grouped results (HAVING)</h3>
            <pre><code class="language-sql">SELECT course, COUNT(*) AS total
FROM students
GROUP BY course
HAVING COUNT(*) &gt;= 2;</code></pre>

            <div class="note">
                <strong>WHERE vs HAVING:</strong>
                <ul class="mb-0">
                    <li><code>WHERE</code> filters <strong>rows</strong> before grouping</li>
                    <li><code>HAVING</code> filters <strong>groups</strong> after <code>GROUP BY</code></li>
                </ul>
            </div>
        </section>

        <!-- Common Mistakes -->
        <section class="section">
            <h2>Common Mistakes to Avoid</h2>
            <ul>
                <li>Using <code>=</code> with <code>NULL</code> (use <code>IS NULL</code> / <code>IS NOT NULL</code>)</li>
                <li>Forgetting quotes for strings (e.g., <code>'BSIT'</code>)</li>
                <li>Using <code>HAVING</code> without grouping when you really need <code>WHERE</code></li>
                <li>Using <code>SELECT *</code> in reports when only a few columns are needed</li>
            </ul>

            <pre><code class="language-sql">-- NULL checks
SELECT * FROM students WHERE course IS NULL;
SELECT * FROM students WHERE course IS NOT NULL;</code></pre>
        </section>

        <!-- Mini Practice -->
        <section class="section">
            <h2>Quick Practice (Try It!)</h2>
            <ol>
                <li>Display all students who are in <strong>BSCS</strong>.</li>
                <li>Show only <code>full_name</code> and <code>year_level</code> of students who are <strong>2nd year</strong>.</li>
                <li>List students sorted by <code>age</code> from oldest to youngest.</li>
                <li>Show the <strong>top 2 oldest</strong> students.</li>
                <li>Count how many students are enrolled in each <code>course</code>.</li>
            </ol>

            <div class="note">
                <strong>Challenge:</strong> Modify a query so it returns only students whose name starts with <strong>“M”</strong>.
            </div>
        </section>

        <!-- Hands-on Lab -->
        <section class="section">
            <h2>Hands-on Lab Activity (15–25 mins)</h2>
            <p><strong>Scenario:</strong> You are building a “Student Lookup” feature for a registrar system.</p>

            <h3>Task A — Create the table and insert sample records</h3>
            <ul>
                <li>Create the <code>students</code> table (use the sample above).</li>
                <li>Insert at least <strong>8 students</strong> (mix courses and year levels).</li>
            </ul>

            <h3>Task B — Write queries for the lookup feature</h3>
            <ol>
                <li>Show all students (all columns).</li>
                <li>Show only <code>student_id</code>, <code>full_name</code>, and <code>course</code>.</li>
                <li>Filter only <strong>1st year</strong> students.</li>
                <li>Filter students where <code>age</code> is between 18 and 20.</li>
                <li>Search students whose name contains “<strong>del</strong>” (case depends on collation).</li>
                <li>Sort by <code>course</code> (A–Z), then by <code>full_name</code> (A–Z).</li>
                <li>Return only the first 5 results (use <code>LIMIT</code>).</li>
                <li>Count students per course and show only courses with 2 or more students.</li>
            </ol>

            <div class="note">
                <strong>Submission:</strong> Copy/paste your SQL queries and the result screenshots (or output table).
            </div>
        </section>

        <!-- Summary -->
        <section class="section">
            <h2>Summary</h2>
            <ul class="mb-0">
                <li><code>SELECT</code> retrieves data from tables</li>
                <li><code>WHERE</code> filters rows, <code>ORDER BY</code> sorts results, <code>LIMIT</code> reduces output</li>
                <li><code>DISTINCT</code> removes duplicates</li>
                <li><code>GROUP BY</code> + aggregates summarize data, <code>HAVING</code> filters groups</li>
            </ul>
        </section>

    </main>

    <footer class="text-center mt-4 mb-5">
        <small>Prepared for SQL Fundamentals • Use with MySQL / MariaDB</small>
    </footer>

    <?php $this->load->view('web_to_image'); ?>


</body>

</html>
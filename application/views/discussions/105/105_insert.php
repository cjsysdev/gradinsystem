<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL INSERT Statement</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="<?= base_url('assets/bootstrap.4.5.2.min.css') ?>">

    <!-- Discussion Style -->
    <link rel="stylesheet" href="<?= base_url('assets/discussion-style.css') ?>">
</head>

<body>
    <header>
        <h1>SQL INSERT Statement</h1>
        <p>
            Adding new records into a table safely and correctly
        </p>
    </header>

    <div class="content my-4">

        <div class="section">
            <h2>Learning Objectives</h2>
            <ul>
                <li>Explain the purpose of the <code>INSERT</code> statement</li>
                <li>Write <code>INSERT</code> queries for single-row and multi-row inserts</li>
                <li>Insert values while controlling columns (including <code>NULL</code> and <code>DEFAULT</code>)</li>
                <li>Use <code>INSERT ... SELECT</code> to copy data from one table to another</li>
                <li>Identify common insert errors (data type, constraints, foreign keys)</li>
            </ul>
        </div>

        <div class="section">
            <h2>What Is INSERT?</h2>
            <p>
                The <strong>INSERT</strong> statement is used to add new rows (records) into a table.
                Each inserted row must follow the table structure: correct columns, correct data types,
                and valid constraint rules (primary key, foreign key, NOT NULL, etc.).
            </p>
        </div>

        <div class="section">
            <h2>Basic Syntax</h2>
            <p>
                The safest approach is to specify the column list explicitly.
            </p>

            <pre><code class="sql">
INSERT INTO table_name (column1, column2, column3)
VALUES (value1, value2, value3);
            </code></pre>

            <ul>
                <li>Columns and values must match in count and order.</li>
                <li>Text values usually use single quotes: <code>'Maria'</code></li>
                <li>Dates commonly use: <code>'YYYY-MM-DD'</code></li>
            </ul>
        </div>

        <div class="section">
            <h2>Example Tables</h2>
            <p class="text-muted">Sample structure used in examples below</p>

            <pre><code class="sql">
CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    course VARCHAR(50) NOT NULL,
    year_level INT NOT NULL,
    email VARCHAR(120) UNIQUE
);
            </code></pre>
        </div>

        <div class="section">
            <h2>1) Insert a Single Row</h2>
            <pre><code class="sql">
INSERT INTO students (full_name, course, year_level, email)
VALUES ('Juan Dela Cruz', 'BSIT', 1, 'juan.delacruz@email.com');
            </code></pre>

            <p>
                Notice we did not insert <code>student_id</code> because it is <strong>AUTO_INCREMENT</strong>
                (MySQL will generate it automatically).
            </p>
        </div>

        <div class="section">
            <h2>2) Insert Multiple Rows</h2>
            <p>
                You can insert many records in one statement (faster and cleaner).
            </p>

            <pre><code class="sql">
INSERT INTO students (full_name, course, year_level, email)
VALUES
('Maria Santos', 'BSCS', 2, 'maria.santos@email.com'),
('Paolo Reyes', 'BSIT', 1, 'paolo.reyes@email.com'),
('Anne Lopez', 'BSIS', 3, 'anne.lopez@email.com');
            </code></pre>
        </div>

        <div class="section">
            <h2>3) Insert With All Columns</h2>
            <p class="text-muted">
                Only use this when you are sure of the exact table column order.
            </p>

            <pre><code class="sql">
INSERT INTO students
VALUES (NULL, 'Kevin Tan', 'BSIT', 2, 'kevin.tan@email.com');
            </code></pre>

            <ul>
                <li><code>NULL</code> here allows MySQL to auto-generate the <code>student_id</code>.</li>
                <li>This style breaks easily if the table structure changes—prefer specifying columns.</li>
            </ul>
        </div>

        <div class="section">
            <h2>4) NULL and DEFAULT Values</h2>
            <p>
                Use <code>NULL</code> when a column allows it. Use <code>DEFAULT</code> to use the column's default value.
            </p>

            <pre><code class="sql">
-- If email is optional (allows NULL)
INSERT INTO students (full_name, course, year_level, email)
VALUES ('Liza Cruz', 'BSCS', 1, NULL);

-- If a column has a default (example)
-- year_level INT NOT NULL DEFAULT 1
INSERT INTO students (full_name, course, year_level, email)
VALUES ('Noel Ramos', 'BSIT', DEFAULT, 'noel.ramos@email.com');
            </code></pre>
        </div>

        <div class="section">
            <h2>5) INSERT ... SELECT (Copy Data)</h2>
            <p>
                Instead of manually typing values, you can insert data coming from another table or query result.
            </p>

            <pre><code class="sql">
-- Example: copy 1st year BSIT students into a new table for orientation list
CREATE TABLE orientation_list (
    full_name VARCHAR(100),
    course VARCHAR(50)
);

INSERT INTO orientation_list (full_name, course)
SELECT full_name, course
FROM students
WHERE course = 'BSIT' AND year_level = 1;
            </code></pre>
        </div>

        <div class="section">
            <h2>Common INSERT Errors and Causes</h2>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>Error / Problem</th>
                            <th>Common Cause</th>
                            <th>Fix</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Column count doesn't match value count</td>
                            <td>Number of values is different from number of columns</td>
                            <td>Make sure columns and values align</td>
                        </tr>
                        <tr>
                            <td>Data truncated / incorrect type</td>
                            <td>Wrong data type (text into INT, invalid date, etc.)</td>
                            <td>Check column types and value formats</td>
                        </tr>
                        <tr>
                            <td>Duplicate entry</td>
                            <td>Violates UNIQUE or PRIMARY KEY constraint</td>
                            <td>Use a unique value or change table rules</td>
                        </tr>
                        <tr>
                            <td>Cannot be NULL</td>
                            <td>Trying to insert NULL into NOT NULL column</td>
                            <td>Provide a value or update the table definition</td>
                        </tr>
                        <tr>
                            <td>Foreign key constraint fails</td>
                            <td>Referenced record does not exist in parent table</td>
                            <td>Insert parent record first, then child record</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="section">
            <h2>Good Practices</h2>
            <ul>
                <li>Always specify column names in <code>INSERT</code> statements</li>
                <li>Insert parent rows first when using foreign keys</li>
                <li>Validate formats (especially dates and numbers)</li>
                <li>Use multi-row inserts for better performance</li>
                <li>Test with <code>SELECT</code> after inserting to confirm results</li>
            </ul>
        </div>

        <div class="section">
            <h2>Key Takeaways</h2>
            <ul>
                <li><code>INSERT</code> adds new rows into a table</li>
                <li>Columns and values must match in count and order</li>
                <li>Multi-row insert reduces repeated statements</li>
                <li><code>INSERT ... SELECT</code> is useful for copying/query-based inserts</li>
                <li>Constraints (PK/UK/FK/NOT NULL) control what inserts are allowed</li>
            </ul>
        </div>

        <div class="section">
            <h2>Laboratory Objectives</h2>
            <ul>
                <li>Understand the purpose of inserting records into a table</li>
                <li>Insert one record correctly into a table</li>
                <li>Insert multiple records using one statement</li>
                <li>Explain why multi-row insert is more efficient</li>
                <li>Verify inserted data using SELECT</li>
            </ul>
        </div>

        <div class="section">
            <h2>Given Table Structure</h2>
            <p>
                Create the table <strong>students</strong> with the following columns:
            </p>
            <ul>
                <li>student_id (Auto Increment, Primary Key)</li>
                <li>full_name (Required)</li>
                <li>course (Required)</li>
                <li>year_level (Required)</li>
                <li>email (Unique, Optional)</li>
            </ul>
        </div>

        <div class="section">
            <h2>Single Row Insert</h2>
            <p class="text-muted">
                Goal: Insert only one student record into the table.
            </p>

            <h5 class="mt-3">Instructions</h5>
            <ol>
                <li>Insert one new student into the <strong>students</strong> table.</li>
                <li>Provide values for:
                    <ul>
                        <li>full_name</li>
                        <li>course</li>
                        <li>year_level</li>
                        <li>email</li>
                    </ul>
                </li>
                <li>Do NOT manually insert a value for <strong>student_id</strong>.</li>
                <li>After inserting, verify your record using a SELECT statement.</li>
            </ol>

            <div class="alert alert-warning mt-3">
                <strong>Guide Questions:</strong>
                <ul>
                    <li>Why did you not include the student_id?</li>
                    <li>What automatically happens to the primary key?</li>
                </ul>
            </div>
        </div>

        <div class="section">
            <h2>Multiple Row Insert</h2>
            <p class="text-muted">
                Goal: Insert multiple student records using a single INSERT statement.
            </p>

            <h5 class="mt-3">Instructions</h5>
            <ol>
                <li>Insert at least three (3) new students at the same time.</li>
                <li>Use only ONE INSERT statement.</li>
                <li>Each student must have:
                    <ul>
                        <li>Different full_name</li>
                        <li>Different email</li>
                    </ul>
                </li>
                <li>Do NOT insert student_id manually.</li>
                <li>Verify all inserted records using SELECT.</li>
            </ol>

            <div class="alert alert-info mt-3">
                <strong>Performance Reflection:</strong>
                Why is inserting multiple rows in one statement better than writing three separate INSERT statements?
            </div>
        </div>
    </div>

    <?php $this->load->view('web_to_image'); ?>

</body>

</html>
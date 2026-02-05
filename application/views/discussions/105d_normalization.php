<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relational Database Normalization</title>

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
        <h1>Relational Database Normalization</h1>
        <p>Designing efficient, consistent, and scalable databases</p>
    </header>

    <div class="content mt-4 mb-5">

        <h2 class="discussion-title">Module: Database Design Fundamentals</h2>

        <p class="discussion-intro">
            Relational database normalization is a systematic approach to organizing data in a
            database to minimize redundancy and improve data integrity. The goal is to store data
            once, in the correct place, while preserving logical relationships among tables.
        </p>

        <div class="alert alert-info">
            <b>In simple terms:</b> Normalization means structuring your tables so that each fact is stored only once.
        </div>

        <hr>

        <h4>Why Normalization Is Important</h4>
        <p>
            Poorly designed databases often suffer from duplicated data, inconsistent values, and
            unexpected data loss. Normalization helps eliminate these problems by organizing tables
            based on functional dependencies.
        </p>
        <ul>
            <li>Reduces data redundancy</li>
            <li>Prevents update, insertion, and deletion anomalies</li>
            <li>Improves data integrity and consistency</li>
            <li>Supports scalability and easier maintenance</li>
        </ul>

        <div class="alert alert-success">
            <b>Key idea:</b> Correct structure first, performance tuning later.
        </div>

        <hr>

        <h4>Data Redundancy and Anomalies</h4>
        <p>
            Data redundancy occurs when the same information is stored in multiple rows or tables.
            This often leads to three common anomalies:
        </p>
        <ul>
            <li><b>Update anomaly:</b> Changing data in one row but forgetting others</li>
            <li><b>Insertion anomaly:</b> Inability to add data without unrelated information</li>
            <li><b>Deletion anomaly:</b> Loss of important data when removing records</li>
        </ul>

        <div class="alert alert-warning">
            <b>Example:</b> Deleting the last student enrolled in a course may also remove the course details.
        </div>

        <hr>

        <h4>First Normal Form (1NF)</h4>
        <p>A table is in <b>First Normal Form (1NF)</b> if:</p>
        <ul>
            <li>Each field contains atomic (indivisible) values</li>
            <li>No repeating groups or multi-valued attributes exist</li>
            <li>Each record can be uniquely identified</li>
        </ul>

        <pre><code class="sql">-- Not in 1NF
StudentID | Courses
101       | IT101, IT102

-- In 1NF
StudentID | CourseID
101       | IT101
101       | IT102</code></pre>

        <div class="alert alert-info">
            <b>Key idea:</b> One cell, one value.
        </div>

        <hr>

        <h4>Second Normal Form (2NF)</h4>
        <p>A table is in <b>Second Normal Form (2NF)</b> if:</p>
        <ul>
            <li>It is already in 1NF</li>
            <li>All non-key attributes depend on the entire primary key</li>
        </ul>
        <p>This rule applies mainly to tables with <b>composite primary keys</b>.</p>

        <pre><code class="sql">-- Partial dependency problem
(StudentID, CourseID) → StudentName

-- Decomposed tables
Students(StudentID, StudentName)
Enrollments(StudentID, CourseID)</code></pre>

        <div class="alert alert-success">
            <b>Key idea:</b> No partial dependency on a composite key.
        </div>

        <hr>

        <h4>Third Normal Form (3NF)</h4>
        <p>A table is in <b>Third Normal Form (3NF)</b> if:</p>
        <ul>
            <li>It is already in 2NF</li>
            <li>No transitive dependencies exist</li>
        </ul>
        <p>
            A transitive dependency occurs when a non-key attribute depends on another non-key
            attribute rather than the primary key.
        </p>

        <pre><code class="sql">-- Transitive dependency
CourseID → Instructor → InstructorPhone

-- Decomposed tables
Courses(CourseID, InstructorID)
Instructors(InstructorID, InstructorPhone)</code></pre>

        <div class="alert alert-warning">
            <b>Key idea:</b> Non-key attributes should depend only on the primary key.
        </div>

        <hr>

        <h4>Summary of Normal Forms</h4>
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Normal Form</th>
                    <th>Main Purpose</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1NF</td>
                    <td>Removes repeating and multi-valued fields</td>
                </tr>
                <tr>
                    <td>2NF</td>
                    <td>Eliminates partial dependency</td>
                </tr>
                <tr>
                    <td>3NF</td>
                    <td>Eliminates transitive dependency</td>
                </tr>
            </tbody>
        </table>

        <hr>

        <h4>Normalization in Practice</h4>
        <p>
            Most transactional systems (OLTP) use databases normalized up to <b>Third Normal Form</b>
            to ensure data accuracy and consistency. In reporting or analytical systems (OLAP),
            controlled denormalization may be used to improve performance.
        </p>

        <div class="alert alert-danger">
            <b>Design rule:</b> Normalize for correctness, denormalize only when performance requires it.
        </div>

        <hr>

        <h4>Final Takeaway</h4>
        <p>
            Normalization is a foundational concept in relational database design. A well-normalized
            database leads to cleaner data, fewer errors, and more reliable applications.
        </p>

        <div class="alert alert-info">
            <b>Remember:</b> Good structure today prevents problems tomorrow.
        </div>

    </div>

</body>

</html>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprehensive SQL Constraints</title>

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
        <div class="container">
            <h1>Database Design Guide: Choosing the Right Columns</h1>
            <p>A practical discussion on how to decide what columns to add (and what to avoid) in your tables.</p>
        </div>
    </header>

    <div class="content container mt-4 mb-5">

        <!-- Learning Objectives -->
        <div class="section card mb-4">
            <div class="card-body">
                <span class="badge-soft">Learning Objectives</span>
                <h2>What you should be able to do after this lesson</h2>
                <ul class="mb-0">
                    <li>Decide whether a piece of information should be a column, computed value, or a separate table.</li>
                    <li>Avoid common column design mistakes (e.g., <code>full_name</code> only, storing <code>age</code>).</li>
                    <li>Design searchable, consistent, and flexible columns using real-world examples.</li>
                </ul>
            </div>
        </div>

        <!-- Core Idea -->
        <div class="section card mb-4">
            <div class="card-body">
                <span class="badge-soft">Concept Explanation</span>
                <h2>The main idea: columns should be stable, searchable, and meaningful</h2>
                <p>
                    A <strong>column</strong> should store a single, clear type of data that your system can reliably use for
                    searching, sorting, filtering, reporting, and validation.
                </p>

                <div class="callout callout-info">
                    <strong>Rule of Thumb:</strong>
                    If the value <em>changes often</em>, can be <em>calculated</em>, or contains <em>multiple pieces of data</em>,
                    then it might be the wrong thing to store as one column.
                </div>
            </div>
        </div>

        <!-- Guidelines -->
        <div class="section card mb-4">
            <div class="card-body">
                <span class="badge-soft">Guidelines</span>
                <h2>How to decide what columns to add</h2>

                <ol>
                    <li class="mb-3">
                        <strong>Store atomic data (one column = one idea)</strong>
                        <div class="callout callout-warn mt-2">
                            <strong>Avoid:</strong> <code>full_name</code> as the only name column.<br>
                            <strong>Prefer:</strong> <code>first_name</code>, <code>middle_name</code> (optional), <code>last_name</code>, and maybe <code>suffix</code>.
                            <div class="mt-2">
                                Why? Searching “Garcia” (last name) or sorting by last name becomes hard if everything is stored as one string.
                            </div>
                        </div>
                    </li>

                    <li class="mb-3">
                        <strong>Store stable facts, not changing snapshots</strong>
                        <div class="callout callout-warn mt-2">
                            <strong>Avoid:</strong> <code>age</code><br>
                            <strong>Prefer:</strong> <code>birthdate</code>
                            <div class="mt-2">
                                Why? Age changes every year. Birthdate stays the same, and age can be computed when needed.
                            </div>
                        </div>
                    </li>

                    <li class="mb-3">
                        <strong>Choose columns based on how the system will use the data</strong>
                        <div class="callout callout-info mt-2">
                            Ask:
                            <ul class="mb-0">
                                <li>Will users search/filter by this?</li>
                                <li>Will the system validate it (format/range)?</li>
                                <li>Will we report or sort by it?</li>
                                <li>Does it have a standard format (date, number, code)?</li>
                            </ul>
                        </div>
                    </li>

                    <li class="mb-3">
                        <strong>Don’t store computed values unless you have a strong reason</strong>
                        <div class="callout callout-warn mt-2">
                            Examples of computed values:
                            <ul class="mb-0">
                                <li><code>total_price</code> if it always equals <code>qty * unit_price</code></li>
                                <li><code>age</code> from <code>birthdate</code></li>
                                <li><code>full_name</code> from first/middle/last</li>
                            </ul>
                            <div class="mt-2">
                                Computed values can become inconsistent if one part changes and the computed column is not updated.
                            </div>
                        </div>
                    </li>

                    <li class="mb-3">
                        <strong>Avoid “multi-value” columns (comma-separated lists)</strong>
                        <div class="callout callout-warn mt-2">
                            <strong>Avoid:</strong> <code>skills = "Java,SQL,Python"</code><br>
                            <strong>Prefer:</strong> a separate table (e.g., <code>student_skills</code>) where each skill is one row.
                            <div class="mt-2">
                                Why? Searching/filtering/reporting becomes painful and inaccurate with lists inside one column.
                            </div>
                        </div>
                    </li>

                    <li class="mb-3">
                        <strong>Use IDs for relationships, not names</strong>
                        <div class="callout callout-good mt-2">
                            <strong>Prefer:</strong> <code>department_id</code> instead of storing <code>department_name</code> repeatedly.
                            <div class="mt-2">
                                Why? Names can change; IDs stay consistent. This reduces duplication and avoids mismatch spelling.
                            </div>
                        </div>
                    </li>

                    <li class="mb-0">
                        <strong>Plan for missing/optional information</strong>
                        <div class="callout callout-info mt-2">
                            Some fields may be unknown at registration time (e.g., middle name, suffix, contact number).
                            Your design should allow <code>NULL</code> where appropriate, rather than forcing fake values like “N/A”.
                        </div>
                    </li>
                </ol>
            </div>
        </div>

        <!-- Examples -->
        <div class="section card mb-4">
            <div class="card-body">
                <span class="badge-soft">Examples</span>
                <h2>Good vs. poor column choices (Student profile example)</h2>

                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead>
                            <tr>
                                <th style="width: 35%;">Bad Design</th>
                                <th style="width: 65%;">Better Design</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>full_name</code></td>
                                <td><code>first_name</code>, <code>middle_name</code> (nullable), <code>last_name</code>, <code>suffix</code> (nullable)</td>
                            </tr>
                            <tr>
                                <td><code>age</code></td>
                                <td><code>birthdate</code> (then compute age)</td>
                            </tr>
                            <tr>
                                <td><code>address</code> (one big text)</td>
                                <td>
                                    <code>address_line</code>, <code>barangay</code>, <code>city</code>, <code>province</code>, <code>zip_code</code>
                                    <small class="d-block text-muted mt-1">Tip: split only as much as your system needs for searching/filtering.</small>
                                </td>
                            </tr>
                            <tr>
                                <td><code>subjects = "CC104, WS101"</code></td>
                                <td>Enrollment table: <code>student_id</code> + <code>subject_id</code> (one row per subject)</td>
                            </tr>
                            <tr>
                                <td><code>department_name</code> repeated</td>
                                <td><code>department_id</code> (FK) referencing a <code>departments</code> table</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        <!-- Practical Column Decision Checklist -->
        <div class="section card mb-4">
            <div class="card-body">
                <span class="badge-soft">Checklist</span>
                <h2>Column Decision Checklist (use this before adding a field)</h2>

                <div class="callout callout-info">
                    For each proposed column, answer:
                    <ul class="mb-0">
                        <li><strong>What question does this answer?</strong> (Search, report, validation, display?)</li>
                        <li><strong>Is it atomic?</strong> (One idea only?)</li>
                        <li><strong>Is it stable?</strong> (Won’t change frequently?)</li>
                        <li><strong>Can it be computed?</strong> If yes, store the source instead.</li>
                        <li><strong>Is it multi-valued?</strong> If yes, use another table.</li>
                        <li><strong>Is it a relationship?</strong> If yes, store an ID (foreign key).</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Mini Hands-on -->
        <div class="section card mb-4">
            <div class="card-body">
                <span class="badge-soft">Hands-on Activity</span>
                <h2>Activity: Fix the columns</h2>

                <p class="mb-2">A student table has these columns:</p>
                <pre><code class="language-sql">student(
  student_id,
  full_name,
  age,
  course_name,
  subjects,
  contact
)</code></pre>

                <p class="mb-2"><strong>Task:</strong> Redesign it using the guidelines above.</p>

                <div class="callout callout-good">
                    <strong>One possible improved answer:</strong>
                    <pre class="mb-0"><code class="language-sql">students(
  student_id,
  first_name,
  middle_name,
  last_name,
  suffix,
  birthdate,
  course_id,
  contact_number
)

courses(
  course_id,
  course_name
)

enrollments(
  enrollment_id,
  student_id,
  subject_id,
  term,
  school_year
)

subjects(
  subject_id,
  subject_code,
  subject_title
)</code></pre>
                </div>

                <small class="text-muted d-block mt-2">
                    Note: Some systems keep <code>full_name</code> as a generated/display field, but still store first/last separately for search and sorting.
                </small>
            </div>
        </div>

        <!-- Reflection -->
        <div class="section card mb-4">
            <div class="card-body">
                <span class="badge-soft">Reflection Questions</span>
                <h2>Check your understanding</h2>
                <ol class="mb-0">
                    <li>Why is storing <code>age</code> less reliable than storing <code>birthdate</code>?</li>
                    <li>What problems happen when you store comma-separated values in one column?</li>
                    <li>If the user wants to search by last name quickly, what columns should exist?</li>
                    <li>Give one example of a value that should be stored as an ID instead of text.</li>
                    <li>For “address”, how would you decide whether to split it into multiple columns or keep it as one?</li>
                </ol>
            </div>
        </div>

        <!-- Quick Summary -->
        <div class="section card">
            <div class="card-body">
                <span class="badge-soft">Summary</span>
                <h2>Key takeaways</h2>
                <ul class="mb-0">
                    <li>Design columns for <strong>searching, sorting, and consistency</strong>.</li>
                    <li>Store <strong>stable facts</strong> (birthdate) instead of <strong>changing values</strong> (age).</li>
                    <li>Keep columns <strong>atomic</strong> (first/last name separate, avoid combined strings).</li>
                    <li>Use <strong>separate tables</strong> for multi-valued data and relationships.</li>
                </ul>
            </div>
        </div>

    </div>
</body>

</html>
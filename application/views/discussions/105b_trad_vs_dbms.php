<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Introduction to Queues in C (Array Implementation)</title>

    <link rel="stylesheet" href="<?= base_url('assets/bootstrap.4.5.2.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/discussion-style.css') ?>">

    <link rel="stylesheet" href="<?= base_url('assets/highlights/atom-one-light.min.css') ?>">
    <script src="<?= base_url('assets/highlights/11.7.0-highlight.min.js') ?>"></script>
    <script>
        hljs.highlightAll();
    </script>
</head>

<body>

    <header>
        <h1>Traditional vs Database Approach</h1>
        <p>Learn how the difference between traditional file processing and database approaches works in computing systems.</p>
    </header>

    <div class="content mt-4 mb-5">

        <h2 class="discussion-title">Traditional File Processing vs. Database Approach</h2>

        <p class="discussion-intro">
            Before modern database systems existed, organizations stored and managed data using
            <b>traditional file processing systems</b>. As data volume and complexity increased,
            the <b>database approach</b> was introduced to overcome the limitations of file-based systems.
        </p>

        <hr>

        <h4>1. Traditional File Processing System</h4>

        <p>
            In a <b>traditional file processing system</b>, each application program manages its own data files.
            The data is stored in separate files, usually designed specifically for a particular application.
        </p>

        <p>Characteristics:</p>
        <ul>
            <li>Data is stored in flat files (text files, spreadsheets, etc.)</li>
            <li>Each application has its own files</li>
            <li>Programs and data are tightly coupled</li>
            <li>Data access logic is written directly into the program</li>
        </ul>

        <div class="alert alert-danger">
            <b>Common Problems:</b>
            <ul>
                <li>Data redundancy (duplicate data)</li>
                <li>Data inconsistency</li>
                <li>Difficult data sharing</li>
                <li>High maintenance cost</li>
            </ul>
        </div>

        <hr>

        <h4>2. Database Approach</h4>

        <p>
            The <b>database approach</b> uses a centralized database managed by a
            <b>Database Management System (DBMS)</b>. Multiple applications can access the same
            data without duplication.
        </p>

        <p>Characteristics:</p>
        <ul>
            <li>Data is stored in a centralized database</li>
            <li>Multiple applications share the same data</li>
            <li>Data independence between programs and data</li>
            <li>DBMS controls access, security, and integrity</li>
        </ul>

        <div class="alert alert-success">
            <b>Advantages:</b>
            <ul>
                <li>Reduced data redundancy</li>
                <li>Improved data consistency</li>
                <li>Better data security</li>
                <li>Easier data sharing and reporting</li>
            </ul>
        </div>

        <hr>

        <h4>3. Simple Illustration</h4>

        <p><b>Traditional File Processing:</b></p>
        <pre><code class="plaintext">
Payroll Program  → payroll.txt
Enrollment App   → students.txt
Library System   → members.txt
    </code></pre>

        <p><b>Database Approach:</b></p>
        <pre><code class="plaintext">
Payroll Program
Enrollment App   → Central Database (DBMS)
Library System
    </code></pre>

        <hr>

        <h4>4. Comparison Table</h4>

        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Aspect</th>
                    <th>File Processing</th>
                    <th>Database Approach</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Data Storage</td>
                    <td>Separate files</td>
                    <td>Centralized database</td>
                </tr>
                <tr>
                    <td>Redundancy</td>
                    <td>High</td>
                    <td>Low</td>
                </tr>
                <tr>
                    <td>Data Sharing</td>
                    <td>Difficult</td>
                    <td>Easy</td>
                </tr>
                <tr>
                    <td>Security</td>
                    <td>Limited</td>
                    <td>Controlled by DBMS</td>
                </tr>
                <tr>
                    <td>Maintenance</td>
                    <td>Hard to maintain</td>
                    <td>Easier to maintain</td>
                </tr>
            </tbody>
        </table>

        <hr>

        <h4>5. Activity: File Processing or Database Approach?</h4>

        <p>
            Identify whether each situation describes a <b>Traditional File Processing System</b>
            or a <b>Database Approach</b>.
        </p>

        <ol>
            <li>Each department keeps its own Excel file of employee records.</li>
            <li>A school system uses one centralized database accessed by enrollment, grading, and billing modules.</li>
            <li>The same student data appears in multiple files across different programs.</li>
            <li>User access and permissions are controlled by a DBMS.</li>
            <li>Updating a data format requires modifying several programs.</li>
            <li>Multiple applications retrieve data using SQL queries.</li>
        </ol>

        <div class="alert alert-secondary">
            <b>Guide:</b>
            <ul>
                <li>Separate files per program → <b>File Processing</b></li>
                <li>Centralized data with DBMS → <b>Database Approach</b></li>
            </ul>
        </div>

    </div>

    <?php $this->load->view('web_to_image'); ?>

</body>

</html>
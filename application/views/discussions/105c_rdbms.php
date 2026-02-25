<?php
// CC105 - Overview of Databases and RDBMS
// Web-based discussion format similar to 105a_data.php
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>CC105 | Overview of Databases and RDBMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">


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
        <h1>Overview of Databases and RDBMS</h1>
        <p>Understand the fundamentals of databases and relational database management systems.</p>
    </header>

    <div class="content my-5">

        <!-- Section 1 -->
        <div class="discussion-card">
            <h3 class="section-title">What is a Database?</h3>
            <p>
                A <strong>database</strong> is an organized collection of structured data stored electronically in a computer system.
                It is designed to allow efficient storage, retrieval, and management of large volumes of data.
            </p>
            <p>
                Databases are essential in modern applications such as enrollment systems, payroll systems,
                social media platforms, and online shopping applications.
            </p>
        </div>

        <!-- Section 2 -->
        <div class="discussion-card">
            <h3 class="section-title">Relational Database Management System (RDBMS)</h3>
            <p>
                A <strong>Relational Database Management System (RDBMS)</strong> is a type of DBMS that stores data
                in a structured format using <em>tables</em>, also known as <em>relations, entities, data collections, and database objects</em>.
            </p>
            <p>
                RDBMS ensures data integrity, supports <strong>SQL (Structured Query Language)</strong>,
                and allows relationships to be created between tables.
            </p>

            <h5 class="mt-3">Key Features of RDBMS</h5>
            <ul>
                <li>Data is stored in tables (relations)</li>
                <li>Supports ACID properties (Atomicity, Consistency, Isolation, Durability)</li>
                <li>Provides data integrity using primary and foreign keys</li>
                <li>Allows complex data queries using SQL</li>
            </ul>
        </div>

        <!-- Section 3 -->
        <div class="discussion-card">
            <h3 class="section-title">Relational Model Concepts</h3>
            <p>
                The <strong>relational model</strong> is the foundation of RDBMS. It represents data in the form of tables
                and defines how data is stored, accessed, and manipulated.
            </p>

            <h5>Tables</h5>
            <p>
                A table is a collection of related data organized into rows and columns.
            </p>
            <ul>
                <li><strong>Rows (Tuples, Records)</strong> – represent individual records</li>
                <li><strong>Columns (Attributes, Fields)</strong> – represent specific properties of data</li>
            </ul>

            <div class="example-box">
                <strong>Example: Employee Table</strong>
                <pre><code class="language-sql">EmployeeID | Name       | Department | Salary
--------------------------------------------
101        | John Doe   | HR         | 50000
102        | Jane Smith | IT         | 60000</code></pre>
            </div>
        </div>

        <!-- Section 4 -->
        <div class="discussion-card">
            <h3 class="section-title">Keys in a Database</h3>
            <p>
                <strong>Keys</strong> are used to uniquely identify records and to establish relationships between tables.
            </p>

            <h5>Primary Key</h5>
            <p>
                A <strong>primary key</strong> uniquely identifies each record in a table and cannot contain null values.
            </p>
            <p><em>Example:</em> EmployeeID in the Employee table.</p>

            <h5>Foreign Key</h5>
            <p>
                A <strong>foreign key</strong> is a field in one table that refers to the primary key of another table.
                It is used to create relationships between tables.
            </p>
        </div>

        <!-- Section 5 -->
        <div class="discussion-card">
            <h3 class="section-title">Table Relationships</h3>
            <p>
                Relationships define how tables are connected with each other in a database.
            </p>
            <ul>
                <li><strong>One-to-One (1:1)</strong> – one record relates to one record</li>
                <li><strong>One-to-Many (1:N)</strong> – one record relates to multiple records</li>
                <li><strong>Many-to-Many (M:N)</strong> – multiple records relate to multiple records</li>
            </ul>
        </div>

        <!-- Section 6 -->
        <div class="discussion-card">
            <h3 class="section-title">Entity-Relationship (ER) Diagrams</h3>
            <p>
                <strong>ER Diagrams</strong> visually represent the database structure, including entities,
                attributes, and relationships.
            </p>

            <ul>
                <li><strong>Entities</strong> – real-world objects (e.g., Employee, Department)</li>
                <li><strong>Attributes</strong> – properties of entities (e.g., EmployeeID, Name)</li>
                <li><strong>Relationships</strong> – connections between entities</li>
            </ul>

            <h5>Common ER Diagram Symbols</h5>
            <ul>
                <li>Rectangle – Entity</li>
                <li>Ellipse – Attribute</li>
                <li>Diamond – Relationship</li>
                <li>Lines – Connections</li>
            </ul>
        </div>

        <!-- Section 7 -->
        <div class="discussion-card">
            <h3 class="section-title">Key Takeaway</h3>
            <p>
                The relational model and proper database design principles are fundamental
                to creating efficient, reliable, and scalable database systems.
            </p>
        </div>

    </div>
    <?php $this->load->view('web_to_image'); ?>

</body>


</html>
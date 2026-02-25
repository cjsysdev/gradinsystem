<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Introduction to SQL</title>

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
        <h1>Introduction to SQL</h1>
        <p>Understand how Structured Query Language (SQL) is used to manage and interact with databases.</p>
    </header>

    <div class="content mt-4 mb-5">

        <h2 class="discussion-title">Introduction to SQL</h2>

        <p class="discussion-intro">
            <b>SQL (Structured Query Language)</b> is the standard language used to communicate with relational
            databases. It allows users to create databases, define tables, store data, retrieve information,
            and manage database security.
        </p>

        <hr>

        <h4>Learning Objectives</h4>
        <ul>
            <li>Understand what SQL is and why it is important</li>
            <li>Identify common SQL concepts and terminology</li>
            <li>Recognize different SQL environments and tools</li>
            <li>Set up a basic SQL working environment</li>
            <li>Create a database and tables using SQL</li>
            <li>Identify appropriate data types for table columns</li>
        </ul>

        <hr>

        <h4>What is SQL?</h4>
        <p>
            SQL is a language designed for managing data stored in a <b>relational database</b>. Instead of
            navigating files manually, SQL allows users to issue commands that tell the database what to do.
        </p>

        <p>Common tasks performed using SQL include:</p>
        <ul>
            <li>Creating databases and tables</li>
            <li>Inserting, updating, and deleting records</li>
            <li>Retrieving data using queries</li>
            <li>Controlling access and permissions</li>
        </ul>

        <div class="alert alert-info">
            <b>Key idea:</b> SQL focuses on <i>what data you want</i>, not how the database internally finds it.
        </div>

        <hr>

        <h4>Basic SQL Concepts</h4>
        <ul>
            <li><b>Database</b> – A collection of organized data</li>
            <li><b>Table</b> – A structure that stores data in rows and columns</li>
            <li><b>Row (Record)</b> – A single entry in a table</li>
            <li><b>Column (Field)</b> – A specific attribute of the data</li>
            <li><b>Primary Key</b> – A unique identifier for each record</li>
        </ul>

        <hr>

        <h4>SQL Environment</h4>
        <p>
            An SQL environment consists of a <b>Database Management System (DBMS)</b> and a tool or interface
            used to write and execute SQL commands.
        </p>

        <p>Common DBMS examples:</p>
        <ul>
            <li>MySQL</li>
            <li>MariaDB</li>
            <li>PostgreSQL</li>
            <li>SQLite</li>
            <li>Microsoft SQL Server</li>
        </ul>

        <hr>

        <h4>SQL Interface</h4>
        <p>
            SQL commands can be executed using different interfaces such as:
        </p>
        <ul>
            <li>Command-line tools</li>
            <li>Graphical tools (e.g., phpMyAdmin, MySQL Workbench)</li>
            <li>Web-based database management tools</li>
        </ul>

        <div class="alert alert-warning">
            <b>Note:</b> Regardless of the interface, the SQL syntax remains mostly the same.
        </div>

        <hr>

        <h4>Setting Up the SQL Environment</h4>
        <p>To start using SQL, the following are typically required:</p>
        <ol>
            <li>Install a DBMS (e.g., MySQL or MariaDB)</li>
            <li>Install a database interface or management tool</li>
            <li>Start the database service</li>
            <li>Log in using valid credentials</li>
        </ol>

        <hr>

        <h4>Creating a Database</h4>
        <p>The following SQL statement creates a new database:</p>

        <pre><code class="language-sql">CREATE DATABASE school_db;</code></pre>

        <p>To select and use the database:</p>
        <pre><code class="language-sql">USE school_db;</code></pre>

        <hr>

        <h4>Creating a Table</h4>
        <p>
            Tables store actual data inside a database. Each column must be defined with a name and data type.
        </p>

        <pre><code class="language-sql">CREATE TABLE students (
    student_id INT PRIMARY KEY,
    full_name VARCHAR(100),
    age INT,
    birth_date DATE
);</code></pre>

        <hr>

        <h4>Identifying SQL Data Types</h4>
        <p>Choosing the correct data type is important for data accuracy and efficiency.</p>

        <ul>
            <li><b>INT</b> – Whole numbers</li>
            <li><b>VARCHAR(n)</b> – Text with variable length</li>
            <li><b>DATE</b> – Date values</li>
            <li><b>DECIMAL(p,s)</b> – Exact numeric values with decimals</li>
            <li><b>BOOLEAN</b> – True or false values</li>
        </ul>

        <div class="alert alert-success">
            <b>Tip:</b> Always select the smallest data type that can correctly store your data.
        </div>

        <h4>Adding Constraints (Primary Key and Foreign Key)</h4>
        <p>
            <b>Constraints</b> are rules applied to table columns to enforce data integrity and accuracy.
            Two of the most important constraints in relational databases are <b>Primary Key</b> and <b>Foreign Key</b>.
        </p>


        <h5>Primary Key</h5>
        <p>
            A <b>Primary Key</b> uniquely identifies each record in a table. It ensures that the value is
            <b>unique</b> and <b>not null</b>.
        </p>
        <ul>
            <li>Each table should have only one primary key</li>
            <li>Often applied to ID columns</li>
        </ul>


        <pre><code class="language-sql">student_id INT PRIMARY KEY</code></pre>


        <h5>Foreign Key</h5>
        <p>
            A <b>Foreign Key</b> is used to link two tables together. It references the primary key of another table,
            ensuring that relationships between tables remain valid.
        </p>
        <ul>
            <li>Prevents invalid data from being inserted</li>
            <li>Maintains relationships between tables</li>
        </ul>


        <pre><code class="language-sql">FOREIGN KEY (student_id) REFERENCES students(student_id)</code></pre>


        <div class="alert alert-info">
            <b>Key idea:</b> Primary keys identify records, while foreign keys connect related tables.
        </div>

        <hr>

        <h4>Summary</h4>
        <p>
            SQL is a powerful and essential tool for managing relational databases. By understanding its
            environment, syntax, and core concepts, users can efficiently design databases and interact with
            stored data.
        </p>

    </div>
    <?php $this->load->view('web_to_image'); ?>

</body>

</html>
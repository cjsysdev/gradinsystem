<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Storage Engines</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="<?= base_url('assets/bootstrap.4.5.2.min.css') ?>">

    <!-- Discussion Style -->
    <link rel="stylesheet" href="<?= base_url('assets/discussion-style.css') ?>">
</head>

<body>
    <header>
        <h1>SQL Storage Engines</h1>
        <p>
            Understanding how databases store, retrieve, and manage data internally
        </p>
    </header>

    <div class="content my-4">

        <div class="section">
            <h2>Learning Objectives</h2>
            <ul>
                <li>Define what a storage engine is in a relational database system</li>
                <li>Explain why different storage engines exist</li>
                <li>Compare common MySQL storage engines</li>
                <li>Select an appropriate storage engine for a given use case</li>
            </ul>
        </div>

        <div class="section">
            <h2>What Is a Storage Engine?</h2>
            <p>
                A <strong>storage engine</strong> is the component of a database management system (DBMS)
                responsible for how data is stored, indexed, retrieved, and managed on disk or in memory.
            </p>

            <p>
                In MySQL, the SQL layer (queries, joins, functions) is separated from the
                storage engine layer. This means the same SQL query can work across different
                storage engines, but performance and features may vary.
            </p>
        </div>

        <div class="section">
            <h2>Why Are Storage Engines Important?</h2>
            <p>
                Different applications have different needs. Some require:
            </p>
            <ul>
                <li>High transaction reliability</li>
                <li>Fast read performance</li>
                <li>Minimal storage overhead</li>
                <li>Support for foreign keys and constraints</li>
            </ul>

            <p>
                Storage engines allow database designers to choose how data behaves
                behind the scenes without changing SQL syntax.
            </p>
        </div>

        <div class="section">
            <h2>Common MySQL Storage Engines</h2>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>Storage Engine</th>
                            <th>Description</th>
                            <th>Key Features</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>InnoDB</strong></td>
                            <td>Default and most widely used storage engine</td>
                            <td>
                                Transactions, foreign keys, row-level locking,
                                crash recovery
                            </td>
                        </tr>
                        <tr>
                            <td><strong>MyISAM</strong></td>
                            <td>Older engine focused on fast read operations</td>
                            <td>
                                Table-level locking, no transactions,
                                faster reads
                            </td>
                        </tr>
                        <tr>
                            <td><strong>MEMORY</strong></td>
                            <td>Stores data in RAM instead of disk</td>
                            <td>
                                Extremely fast access, data lost on restart
                            </td>
                        </tr>
                        <tr>
                            <td><strong>CSV</strong></td>
                            <td>Stores data as CSV text files</td>
                            <td>
                                Simple format, easy data exchange,
                                limited performance
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="section">
            <h2>InnoDB vs MyISAM (Quick Comparison)</h2>

            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <tr>
                        <th>Feature</th>
                        <th>InnoDB</th>
                        <th>MyISAM</th>
                    </tr>
                    <tr>
                        <td>Transactions</td>
                        <td>✔ Supported</td>
                        <td>✖ Not Supported</td>
                    </tr>
                    <tr>
                        <td>Foreign Keys</td>
                        <td>✔ Supported</td>
                        <td>✖ Not Supported</td>
                    </tr>
                    <tr>
                        <td>Locking</td>
                        <td>Row-level</td>
                        <td>Table-level</td>
                    </tr>
                    <tr>
                        <td>Crash Recovery</td>
                        <td>✔ Yes</td>
                        <td>✖ No</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="section">
            <h2>Specifying a Storage Engine</h2>
            <p>
                A storage engine can be specified when creating a table.
            </p>

            <pre><code class="sql">
CREATE TABLE students (
    student_id INT PRIMARY KEY,
    name VARCHAR(100),
    course VARCHAR(50)
) ENGINE = InnoDB;
        </code></pre>

            <p>
                If no engine is specified, MySQL uses the default storage engine
                (usually InnoDB).
            </p>
        </div>

        <div class="section">
            <h2>When to Use Which Engine?</h2>
            <ul>
                <li><strong>InnoDB</strong> – Best for most applications, especially transactional systems</li>
                <li><strong>MyISAM</strong> – Suitable for read-heavy, non-transactional data</li>
                <li><strong>MEMORY</strong> – Temporary tables or fast lookups</li>
                <li><strong>CSV</strong> – Simple data exchange or reporting</li>
            </ul>
        </div>

        <div class="section">
            <h2>Key Takeaways</h2>
            <ul>
                <li>Storage engines control how data is stored and managed</li>
                <li>Different engines offer different features and performance</li>
                <li>InnoDB is the modern default and recommended engine</li>
                <li>Choosing the right engine improves reliability and efficiency</li>
            </ul>
        </div>

        <!-- ================= LAB ACTIVITY ================= -->
        <!-- <div class="section">
            <h2>Hands-On Lab Activity: Storage Engines in Practice</h2>
            <p class="text-muted">
                ERD → SQL Tables → Storage Engine Behavior
            </p>
        </div>

        <div class="section">
            <h3>Lab Objectives</h3>
            <ul>
                <li>Translate an ERD into SQL tables</li>
                <li>Create tables using different storage engines</li>
                <li>Inspect table properties using <code>SHOW TABLE STATUS</code></li>
                <li>Compare how storage engines affect table behavior</li>
            </ul>
        </div>

        <div class="section">
            <h3>Scenario</h3>
            <p>
                A small <strong>Library Management System</strong> is being designed.
                You have already created an ERD showing the following entities:
            </p>

            <ul>
                <li><strong>Book</strong> (BookID, Title, Author)</li>
                <li><strong>Borrower</strong> (BorrowerID, Name)</li>
                <li><strong>Loan</strong> (LoanID, BookID, BorrowerID, LoanDate)</li>
            </ul>

            <p>
                The ERD shows that:
            </p>
            <ul>
                <li>A borrower can loan many books</li>
                <li>A book can be loaned many times over time</li>
                <li>Loan connects Book and Borrower (foreign keys)</li>
            </ul>
        </div>

        <div class="section">
            <h3>Step 1: Create the Database</h3>

            <pre><code class="sql">
CREATE DATABASE library_db;
USE library_db;
    </code></pre>
        </div>

        <div class="section">
            <h3>Step 2: Create Tables Based on ERD</h3>
            <p>
                The tables below directly implement the ERD.
                Note the use of <strong>InnoDB</strong> to support relationships.
            </p>

            <pre><code class="sql">
CREATE TABLE Book (
    BookID INT PRIMARY KEY,
    Title VARCHAR(100),
    Author VARCHAR(100)
) ENGINE = InnoDB;

CREATE TABLE Borrower (
    BorrowerID INT PRIMARY KEY,
    Name VARCHAR(100)
) ENGINE = InnoDB;

CREATE TABLE Loan (
    LoanID INT PRIMARY KEY,
    BookID INT,
    BorrowerID INT,
    LoanDate DATE,
    FOREIGN KEY (BookID) REFERENCES Book(BookID),
    FOREIGN KEY (BorrowerID) REFERENCES Borrower(BorrowerID)
) ENGINE = InnoDB;
    </code></pre>
        </div>

        <div class="section">
            <h3>Step 3: Create a Table Using a Different Storage Engine</h3>
            <p>
                Create a simple reporting table using <strong>MyISAM</strong>.
                This table does not require relationships or transactions.
            </p>

            <pre><code class="sql">
CREATE TABLE LibraryReport (
    ReportID INT PRIMARY KEY,
    ReportName VARCHAR(100)
) ENGINE = MyISAM;
    </code></pre>
        </div>

        <div class="section">
            <h3>Step 4: Inspect Storage Engine Information</h3>
            <p>
                Use the following command to view table properties:
            </p>

            <pre><code class="sql">
SHOW TABLE STATUS FROM library_db;
    </code></pre>

            <p>
                Focus on the following columns in the output:
            </p>
            <ul>
                <li><strong>Name</strong></li>
                <li><strong>Engine</strong></li>
                <li><strong>Rows</strong></li>
                <li><strong>Data_length</strong></li>
                <li><strong>Index_length</strong></li>
            </ul>
        </div>

        <div class="section">
            <h3>Step 5: Analysis Questions (Write Your Answers)</h3>
            <ol>
                <li>Which tables use the InnoDB storage engine?</li>
                <li>Which table uses MyISAM?</li>
                <li>Why is InnoDB required for the Loan table?</li>
                <li>Which engine would be unsafe for transactional data? Why?</li>
            </ol>
        </div>

        <div class="section">
            <h3>ERD → SQL → Storage Engine Mapping</h3>

            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <tr>
                        <th>Design Stage</th>
                        <th>Description</th>
                    </tr>
                    <tr>
                        <td>ERD</td>
                        <td>Defines entities, attributes, and relationships</td>
                    </tr>
                    <tr>
                        <td>SQL Logical Design</td>
                        <td>Creates tables, primary keys, and foreign keys</td>
                    </tr>
                    <tr>
                        <td>Physical Design</td>
                        <td>Selects storage engines and optimizes performance</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="section">
            <h3>Reflection</h3>
            <p>
                Storage engines are part of <strong>physical database design</strong>.
                While ERDs focus on structure and relationships, storage engines determine
                how reliably and efficiently that design works in real systems.
            </p>
        </div>

        <div class="section">
            <h3>Optional Challenge</h3>
            <ul>
                <li>Change the <code>LibraryReport</code> table to use InnoDB</li>
                <li>Observe any differences using <code>SHOW TABLE STATUS</code></li>
                <li>Explain when this change would be necessary</li>
            </ul>
        </div> -->


    </div>
</body>

</html>
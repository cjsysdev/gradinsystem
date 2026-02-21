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
        <h1>SQL Constraints</h1>
        <p>Learn how constraints enforce data integrity, prevent invalid entries, and maintain reliable relationships between tables.</p>
    </header>

    <div class="content mt-4 mb-5">

        <div class="container">

            <div class="section">
                <h2>Learning Objectives</h2>
                <ul>
                    <li>Explain what SQL constraints are and why they matter.</li>
                    <li>Differentiate column-level and table-level constraints.</li>
                    <li>Use PRIMARY KEY, FOREIGN KEY, UNIQUE, NOT NULL, CHECK, and DEFAULT correctly.</li>
                    <li>Apply referential actions (ON DELETE / ON UPDATE) to control relationship behavior.</li>
                    <li>Identify common constraint errors and fix them.</li>
                </ul>
            </div>

            <div class="section">
                <h2>What Are SQL Constraints?</h2>
                <p>
                    <strong>SQL constraints</strong> are rules enforced by the database to ensure the data stored is valid, consistent, and meaningful.
                    Instead of relying on users or application code to behave correctly, constraints act like the database’s “automatic guardrails.”
                </p>

                <div class="alert alert-info">
                    <strong>Key idea:</strong> Constraints protect your database even if the application has bugs, or data is inserted manually.
                </div>
            </div>

            <div class="section">
                <h2>Why Constraints Matter</h2>
                <ul>
                    <li><strong>Accuracy:</strong> prevents impossible values (e.g., negative quantity).</li>
                    <li><strong>Consistency:</strong> ensures unique identifiers and non-empty required fields.</li>
                    <li><strong>Referential integrity:</strong> stops “orphan” records (e.g., enrollment referencing a non-existent student).</li>
                    <li><strong>Cleaner queries:</strong> less defensive filtering because data is already valid.</li>
                </ul>
            </div>

            <div class="section">
                <h2>Where Constraints Can Be Written</h2>
                <p>Constraints can be defined in two main ways:</p>

                <div class="row">
                    <div class="col-md-6">
                        <div class="alert alert-secondary">
                            <strong>Column-level constraint</strong>
                            <p class="mb-0">Written beside a column definition. Best for rules that apply to one column only.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="alert alert-secondary">
                            <strong>Table-level constraint</strong>
                            <p class="mb-0">Written after all columns. Best for multi-column rules (composite keys, combined checks).</p>
                        </div>
                    </div>
                </div>

                <pre><code class="language-sql">CREATE TABLE Example (
  id INT PRIMARY KEY,              -- column-level
  a  INT,
  b  INT,
  CONSTRAINT uq_a_b UNIQUE (a, b)   -- table-level (multi-column)
);</code></pre>
            </div>

            <div class="section">
                <h2>Core SQL Constraints (Most Common)</h2>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Constraint</th>
                                <th>Purpose</th>
                                <th>Typical Use</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>NOT NULL</strong></td>
                                <td>Disallows missing (NULL) values</td>
                                <td>Required fields (name, date, price)</td>
                                <td>Does not prevent empty strings ('')</td>
                            </tr>
                            <tr>
                                <td><strong>UNIQUE</strong></td>
                                <td>Prevents duplicate values</td>
                                <td>Emails, usernames, student numbers</td>
                                <td>Many DBs allow multiple NULLs in UNIQUE</td>
                            </tr>
                            <tr>
                                <td><strong>PRIMARY KEY</strong></td>
                                <td>Uniquely identifies each row</td>
                                <td>id columns, composite identifiers</td>
                                <td>Implies UNIQUE + NOT NULL</td>
                            </tr>
                            <tr>
                                <td><strong>FOREIGN KEY</strong></td>
                                <td>Enforces valid relationships</td>
                                <td>Orders → Customers, Enrollments → Students</td>
                                <td>Supports ON DELETE/UPDATE actions</td>
                            </tr>
                            <tr>
                                <td><strong>CHECK</strong></td>
                                <td>Enforces a logical rule</td>
                                <td>age &gt;= 0, qty &gt; 0, status IN (...)</td>
                                <td>Support varies (older MySQL ignored it)</td>
                            </tr>
                            <tr>
                                <td><strong>DEFAULT</strong></td>
                                <td>Assigns a value when none is provided</td>
                                <td>created_at, is_active, country</td>
                                <td>Does not apply if NULL is explicitly inserted</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-warning">
                    <strong>Reminder:</strong> Constraints enforce rules at the database level. Validation in the UI/app is still useful, but constraints are the final “truth enforcer.”
                </div>
            </div>

            <div class="section">
                <h2>NOT NULL</h2>
                <p><strong>NOT NULL</strong> ensures a column must always have a value.</p>
                <pre><code class="language-sql">CREATE TABLE Students (
  StudentID INT PRIMARY KEY,
  FullName  VARCHAR(100) NOT NULL,
  BirthDate DATE NOT NULL
);</code></pre>

                <div class="alert alert-info">
                    <strong>Common confusion:</strong> NOT NULL does not stop empty strings like '' (for text). If you must prevent blanks, combine with CHECK where supported.
                </div>

                <pre><code class="language-sql">-- Prevent empty names (works in many DBs that support CHECK)
FullName VARCHAR(100) NOT NULL,
CONSTRAINT ck_fullname_not_blank CHECK (TRIM(FullName) &lt;&gt; '')</code></pre>
            </div>

            <div class="section">
                <h2>UNIQUE</h2>
                <p><strong>UNIQUE</strong> prevents duplicate values in a column (or set of columns).</p>

                <pre><code class="language-sql">CREATE TABLE Users (
  UserID INT PRIMARY KEY,
  Email  VARCHAR(255) NOT NULL UNIQUE
);</code></pre>

                <p><strong>Composite UNIQUE (multi-column):</strong> useful when a combination must be unique.</p>
                <pre><code class="language-sql">CREATE TABLE Sections (
  SectionID INT PRIMARY KEY,
  CourseCode VARCHAR(20) NOT NULL,
  Term      VARCHAR(10) NOT NULL,
  SectionNo INT NOT NULL,
  CONSTRAINT uq_course_term_section UNIQUE (CourseCode, Term, SectionNo)
);</code></pre>

                <div class="alert alert-secondary">
                    <strong>Note on NULLs:</strong> Many databases allow multiple NULLs in a UNIQUE column because NULL means “unknown.” Behavior can differ by DB.
                </div>
            </div>

            <div class="section">
                <h2>PRIMARY KEY</h2>
                <p>
                    A <strong>PRIMARY KEY</strong> uniquely identifies a row. Each table should have one primary key.
                    It can be <strong>single-column</strong> or <strong>composite</strong>.
                </p>

                <h3 class="mt-3">Single-column Primary Key</h3>
                <pre><code class="language-sql">CREATE TABLE Products (
  ProductID INT PRIMARY KEY,
  ProductName VARCHAR(120) NOT NULL
);</code></pre>

                <!-- <h3 class="mt-3">Composite Primary Key</h3>
                <p>Often used in linking tables (many-to-many), like Enrollment.</p>
                <pre><code class="language-sql">CREATE TABLE Enrollments (
  StudentID INT NOT NULL,
  SubjectID INT NOT NULL,
  EnrollDate DATE NOT NULL,
  CONSTRAINT pk_enrollments PRIMARY KEY (StudentID, SubjectID)
);</code></pre>

                <div class="alert alert-info">
                    <strong>Tip:</strong> Use composite PK when the “identity” of the row is naturally defined by multiple columns (e.g., StudentID + SubjectID).
                </div> -->
            </div>

            <div class="section">
                <h2>FOREIGN KEY (Referential Integrity)</h2>
                <p>
                    A <strong>FOREIGN KEY</strong> ensures that a value in one table must exist in another table’s referenced key (usually a primary key).
                    This prevents orphan records.
                </p>

                <pre><code class="language-sql">CREATE TABLE Students (
  StudentID INT PRIMARY KEY,
  FullName  VARCHAR(100) NOT NULL
);

CREATE TABLE Enrollments (
  EnrollmentID INT PRIMARY KEY,
  StudentID INT NOT NULL,
  SubjectCode VARCHAR(20) NOT NULL,
  CONSTRAINT fk_enroll_student
    FOREIGN KEY (StudentID) REFERENCES Students(StudentID)
);</code></pre>

                <h3 class="mt-3">ON DELETE / ON UPDATE Actions</h3>
                <p>These actions control what happens to child rows when a parent row changes or is deleted.</p>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Meaning</th>
                                <th>Example Use</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>RESTRICT / NO ACTION</strong></td>
                                <td>Block delete/update if dependent rows exist</td>
                                <td>Don’t delete a Student if enrollments exist</td>
                            </tr>
                            <tr>
                                <td><strong>CASCADE</strong></td>
                                <td>Automatically delete/update dependent rows</td>
                                <td>Delete all enrollment rows when student is deleted</td>
                            </tr>
                            <tr>
                                <td><strong>SET NULL</strong></td>
                                <td>Set foreign key value to NULL</td>
                                <td>Keep record but remove relationship</td>
                            </tr>
                            <tr>
                                <td><strong>SET DEFAULT</strong></td>
                                <td>Set foreign key to its DEFAULT</td>
                                <td>Move rows to “Unassigned” category</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <pre><code class="language-sql">CREATE TABLE Enrollments (
  EnrollmentID INT PRIMARY KEY,
  StudentID INT,
  SubjectCode VARCHAR(20) NOT NULL,
  CONSTRAINT fk_enroll_student
    FOREIGN KEY (StudentID) REFERENCES Students(StudentID)
    ON DELETE SET NULL
    ON UPDATE CASCADE
);</code></pre>

                <div class="alert alert-warning">
                    <strong>Be careful with CASCADE:</strong> It can delete lots of records quickly. Use it only when that behavior is truly intended.
                </div>
            </div>

            <div class="section">
                <h2>CHECK</h2>
                <p>
                    <strong>CHECK</strong> enforces a logical condition. If the condition is false, the insert/update is rejected.
                </p>

                <pre><code class="language-sql">CREATE TABLE Payroll (
  EmployeeID INT PRIMARY KEY,
  Salary DECIMAL(12,2) NOT NULL,
  CONSTRAINT ck_salary_positive CHECK (Salary &gt;= 0)
);</code></pre>

                <p><strong>Common pattern:</strong> limiting allowed values (like status).</p>
                <pre><code class="language-sql">CREATE TABLE Tickets (
  TicketID INT PRIMARY KEY,
  Status VARCHAR(20) NOT NULL,
  CONSTRAINT ck_ticket_status CHECK (Status IN ('OPEN','IN_PROGRESS','RESOLVED','CLOSED'))
);</code></pre>

                <div class="alert alert-secondary">
                    <strong>DB note:</strong> CHECK support differs by database/version. If your DB doesn’t enforce CHECK, you may need triggers or application validation.
                </div>
            </div>

            <div class="section">
                <h2>DEFAULT</h2>
                <p>
                    <strong>DEFAULT</strong> supplies a value automatically when a column is not provided in an INSERT statement.
                </p>

                <pre><code class="language-sql">CREATE TABLE Accounts (
  AccountID INT PRIMARY KEY,
  IsActive  BOOLEAN DEFAULT TRUE,
  CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);</code></pre>

                <div class="alert alert-info">
                    <strong>Important:</strong> DEFAULT applies only when the column is omitted. If you explicitly insert NULL, DEFAULT usually won’t apply (unless combined with DB-specific features).
                </div>
            </div>

            <div class="section">
                <h2>Naming Constraints (Best Practice)</h2>
                <p>
                    You can (and should) <strong>name</strong> constraints for clearer error messages and easier maintenance.
                </p>

                <pre><code class="language-sql">CREATE TABLE Users (
  UserID INT,
  Email  VARCHAR(255) NOT NULL,
  CONSTRAINT pk_users PRIMARY KEY (UserID),
  CONSTRAINT uq_users_email UNIQUE (Email)
);</code></pre>

                <div class="alert alert-success">
                    <strong>Why naming helps:</strong> When an insert fails, the DB error often shows the constraint name—making it easier to identify the exact rule that was violated.
                </div>
            </div>

            <div class="section">
                <h2>Adding, Dropping, and Modifying Constraints</h2>
                <p>Most DBs support <strong>ALTER TABLE</strong> to manage constraints after table creation.</p>

                <pre><code class="language-sql">-- Add a constraint
ALTER TABLE Users
ADD CONSTRAINT uq_users_username UNIQUE (Username);

-- Drop a constraint (syntax varies by DB)
ALTER TABLE Users
DROP CONSTRAINT uq_users_username;</code></pre>

                <div class="alert alert-warning">
                    <strong>Note:</strong> The exact DROP syntax differs (e.g., MySQL often uses DROP INDEX for UNIQUE). Always check your DB flavor.
                </div>
            </div>

            <div class="section">
                <h2>Common Constraint Errors (and What They Mean)</h2>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Error Type</th>
                                <th>Typical Cause</th>
                                <th>Fix</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>NOT NULL violation</td>
                                <td>Inserted NULL into required field</td>
                                <td>Provide a value or allow NULL (if acceptable)</td>
                            </tr>
                            <tr>
                                <td>UNIQUE violation</td>
                                <td>Duplicate value inserted</td>
                                <td>Use a different value, or redesign uniqueness rule</td>
                            </tr>
                            <tr>
                                <td>PRIMARY KEY violation</td>
                                <td>Duplicate key or missing key</td>
                                <td>Ensure unique + not null key values</td>
                            </tr>
                            <tr>
                                <td>FOREIGN KEY violation</td>
                                <td>Child references non-existent parent</td>
                                <td>Insert parent first, or correct the FK value</td>
                            </tr>
                            <tr>
                                <td>CHECK violation</td>
                                <td>Value breaks a logical rule</td>
                                <td>Fix input data or adjust rule if wrong</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="section">
                <h2>Mini Demonstration Scenario</h2>
                <p>
                    Below is a realistic academic mini-database using constraints to enforce clean data:
                    students must have names, emails must be unique, and enrollments must reference existing students.
                </p>

                <pre><code class="language-sql">CREATE TABLE Students (
  StudentID INT PRIMARY KEY,
  FullName  VARCHAR(100) NOT NULL,
  Email     VARCHAR(255) NOT NULL,
  YearLevel INT NOT NULL,
  CONSTRAINT uq_students_email UNIQUE (Email),
  CONSTRAINT ck_students_yearlevel CHECK (YearLevel BETWEEN 1 AND 5)
);

CREATE TABLE Subjects (
  SubjectID INT PRIMARY KEY,
  SubjectCode VARCHAR(20) NOT NULL UNIQUE,
  Title VARCHAR(120) NOT NULL,
  Units INT NOT NULL,
  CONSTRAINT ck_subjects_units CHECK (Units BETWEEN 1 AND 6)
);

CREATE TABLE Enrollments (
  StudentID INT NOT NULL,
  SubjectID INT NOT NULL,
  EnrollDate DATE NOT NULL DEFAULT CURRENT_DATE,
  CONSTRAINT pk_enroll PRIMARY KEY (StudentID, SubjectID),
  CONSTRAINT fk_enroll_student FOREIGN KEY (StudentID)
    REFERENCES Students(StudentID)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT fk_enroll_subject FOREIGN KEY (SubjectID)
    REFERENCES Subjects(SubjectID)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
);</code></pre>
            </div>

            <div class="section">
                <h2>Hands-on Activity</h2>
                <div class="alert alert-primary">
                    <strong>Task:</strong> Improve a small “Library Borrowing” database using constraints.
                </div>

                <ol>
                    <li>Create <strong>Members</strong> with:
                        <ul>
                            <li>MemberID as PRIMARY KEY</li>
                            <li>Email as UNIQUE + NOT NULL</li>
                            <li>FullName as NOT NULL</li>
                        </ul>
                    </li>
                    <li>Create <strong>Books</strong> with:
                        <ul>
                            <li>BookID as PRIMARY KEY</li>
                            <li>ISBN as UNIQUE</li>
                            <li>Status with CHECK (e.g., 'AVAILABLE', 'BORROWED')</li>
                        </ul>
                    </li>
                    <li>Create <strong>Borrowings</strong> with:
                        <ul>
                            <li>BorrowID as PRIMARY KEY</li>
                            <li>MemberID as FOREIGN KEY → Members(MemberID)</li>
                            <li>BookID as FOREIGN KEY → Books(BookID)</li>
                            <li>BorrowDate DEFAULT current date</li>
                            <li>ReturnDate CHECK (ReturnDate is NULL or ReturnDate >= BorrowDate)</li>
                        </ul>
                    </li>
                </ol>

                <div class="alert alert-secondary">
                    <strong>Challenge upgrade:</strong> Add a composite UNIQUE constraint to prevent the same member from borrowing the same book multiple times without returning.
                    (Example: UNIQUE(MemberID, BookID, BorrowDate) or a design you justify.)
                </div>
            </div>

            <div class="section">
                <h2>Quick Self-Check Questions</h2>
                <ol>
                    <li>Why does PRIMARY KEY automatically imply NOT NULL and UNIQUE?</li>
                    <li>What problem does FOREIGN KEY solve?</li>
                    <li>When should you prefer a composite primary key over a single ID?</li>
                    <li>Give a real example where ON DELETE CASCADE is appropriate, and one where it is dangerous.</li>
                    <li>How is DEFAULT different from NOT NULL?</li>
                </ol>
            </div>

            <div class="section">
                <h2>Key Takeaways</h2>
                <ul>
                    <li>Constraints are database-level rules that protect data integrity.</li>
                    <li>Use NOT NULL for required fields, UNIQUE for non-duplicate fields, and CHECK for logical rules.</li>
                    <li>PRIMARY KEY identifies rows; FOREIGN KEY protects relationships.</li>
                    <li>Name constraints for easier debugging and maintenance.</li>
                    <li>Be intentional with ON DELETE/UPDATE actions—especially CASCADE.</li>
                </ul>
            </div>

        </div>
    </div>


    <div class="container text-center mb-5">
        <button id="download-img" class="btn btn-primary mt-4">Download Discussion as Image</button>
    </div>

    <!-- html2canvas only -->
    <script src="<?= base_url('assets/html2canvas.min.js') ?>"></script>
    <script>
        document.getElementById('download-img').addEventListener('click', function () {
            const content = document.querySelector('.content');
            html2canvas(content, { scale: 2 }).then(canvas => {
                const link = document.createElement('a');
                link.href = canvas.toDataURL('image/png');
                link.download = 'SQL_Constraints_Discussion.png';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
        });
    </script>

</body>

</html>

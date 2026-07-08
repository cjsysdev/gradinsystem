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

        <!-- ================= SCENARIO-BASED ACTIVITY ================= -->
        <div class="section">
            <h2>Scenario-Based Activity: Choosing the Right Storage Engine</h2>
            <p class="text-muted">
                Applying ERD concepts to SQL and physical database design
            </p>
        </div>

        <div class="section">
            <h3>Activity Objectives</h3>
            <ul>
                <li>Analyze real-world system requirements</li>
                <li>Identify entities and relationships from a scenario</li>
                <li>Decide the most appropriate storage engine</li>
                <li>Justify design decisions</li>
            </ul>
        </div>

        <div class="section">
            <h3>Scenario</h3>
            <p>
                A <strong>College Enrollment System</strong> is being developed.
                The system will store information about students, courses, and enrollments.
            </p>

            <p>
                The system has the following requirements:
            </p>

            <ul>
                <li>Each student can enroll in many courses</li>
                <li>Each course can have many students</li>
                <li>Enrollment records must be accurate and reliable</li>
                <li>If an error occurs during enrollment, changes must be undone</li>
                <li>The system will also generate read-only summary reports</li>
            </ul>
        </div>

        <div class="section">
            <h3>Part A: ERD Identification</h3>
            <p>
                Based on the scenario, identify the following:
            </p>

            <ol>
                <li>List the entities involved</li>
                <li>Identify the primary key for each entity</li>
                <li>Determine the relationship between Student and Course</li>
                <li>Identify the associative (junction) entity</li>
            </ol>

            <p class="text-muted">
                (Write your answers on paper or in a document.)
            </p>
        </div>

        <div class="section">
            <h3>Part B: Logical Design (SQL Tables)</h3>
            <p>
                Translate the ERD into SQL table structures.
            </p>

            <p>
                Identify:
            </p>
            <ul>
                <li>Which tables require foreign keys</li>
                <li>Which table represents a many-to-many relationship</li>
            </ul>

            <p class="text-muted">
                (Do not write full SQL yet—focus on structure.)
            </p>
        </div>

        <div class="section">
            <h3>Part C: Storage Engine Selection</h3>
            <p>
                Decide which storage engine should be used for each table.
            </p>

            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <tr>
                        <th>Table</th>
                        <th>Recommended Storage Engine</th>
                        <th>Reason</th>
                    </tr>
                    <tr>
                        <td>Student</td>
                        <td>__________</td>
                        <td>________________________________</td>
                    </tr>
                    <tr>
                        <td>Course</td>
                        <td>__________</td>
                        <td>________________________________</td>
                    </tr>
                    <tr>
                        <td>Enrollment</td>
                        <td>__________</td>
                        <td>________________________________</td>
                    </tr>
                    <tr>
                        <td>EnrollmentReport</td>
                        <td>__________</td>
                        <td>________________________________</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="section">
            <h3>Part D: Decision Justification</h3>
            <p>
                Answer the following questions in 1–2 sentences each:
            </p>

            <ol>
                <li>Why is InnoDB required for the Enrollment table?</li>
                <li>Why might a read-only report table use a different storage engine?</li>
                <li>What risks occur if MyISAM is used for enrollment records?</li>
            </ol>
        </div>

        <div class="section">
            <h3>Reflection</h3>
            <p>
                This activity demonstrates that database design does not end with ERDs or SQL syntax.
                Storage engine selection is part of <strong>physical design</strong> and directly affects
                data integrity, performance, and reliability.
            </p>
        </div>

        <div class="section">
            <h3>Optional Challenge</h3>
            <ul>
                <li>Suggest another system where MyISAM or MEMORY would be appropriate</li>
                <li>Explain why InnoDB would be unnecessary in that case</li>
            </ul>
        </div>

        <?php $this->load->view('web_to_image'); ?>

    </div>
</body>

</html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CARMEN MUNICIPAL COLLEGE - GRADE SUBMISSION FORM (FINALS)</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 18px;
        }

        .header p {
            margin: 5px 0 0 0;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        .form-title {
            font-weight: bold;
            text-align: center;
            font-size: 16px;
        }

        /* Highlight rows with INC grades */
        table tbody tr.highlight-inc {
            background-color: rgb(250, 235, 236) !important;
            /* Light red */
        }

        /* Ensure background colors are printed */
        @media print {
            table tbody tr.highlight-inc {
                background-color: rgb(250, 235, 236) !important;
                /* Light red */
                -webkit-print-color-adjust: exact;
                /* For WebKit browsers */
                print-color-adjust: exact;
                /* For other browsers */
            }
        }
    </style>
</head>

<div class="header">
    <h1>CARMEN MUNICIPAL COLLEGE</h1>
    <p>Poblacion Norte, Carmen, Bohol</p>
</div>

<table>
    <tr>
        <td>Form No.:</td>
        <td>GSF-CS-2023-002</td>
    </tr>
    <tr>
        <td>Revision No.:</td>
        <td>Rev. 1</td>
    </tr>
    <tr>
        <td>Effective Date:</td>
        <td>3-Jul-24</td>
    </tr>
    <tr>
        <td>Related Process:</td>
        <td></td>
    </tr>
    <tr>
        <td colspan="2" class="form-title">GRADE SUBMISSION FORM (FINALS)</td>
    </tr>
    <tr>
        <td>Term:</td>
        <td>Final</td>
    </tr>
    <tr>
        <td>Year and Semester</td>
        <td>2nd Semester, S.Y 2024 - 2025</td>
    </tr>
    <tr>
        <td>Course Code : </td>
        <td><?= $class_code ?></td>
    </tr>
    <tr>
        <td>Course Title : </td>
        <td><?= $class_name ?></td>
    </tr>
    <tr>
        <td>Section : </td>
        <td><?= strtoupper($section) ?></td>
    </tr>
</table>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Student ID</th>
            <th>Lastname</th>
            <th>Firstname</th>
            <th>Midterm</th>
            <th>Tentative</th>
            <th>Final</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($studentsGrades)): ?>
            <?php foreach ($studentsGrades as $student): ?>
                <tr class="<?= $student['final_grade'] === 'INC' ? 'highlight-inc' : '' ?>">
                    <td><?= $student['student_id'] ?></td>
                    <td><?= $student['lastname'] ?></td>
                    <td><?= $student['firstname'] ?></td>
                    <td><?= is_numeric($student['midterm_grade']) ? number_format(convertPercentageToGradePoint(floor($student['midterm_grade'] * 10) / 10), 1) : $student['midterm_grade'] ?></td>
                    <td><?= is_numeric($student['tentative_final_grade']) ? number_format(convertPercentageToGradePoint(floor($student['tentative_final_grade'] * 10) / 10), 1) : $student['tentative_final_grade'] ?></td>
                    <td><?= is_numeric($student['final_grade']) ? number_format(floor($student['final_grade'] * 10) / 10, 1) : $student['final_grade'] ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="text-center">No grades available for this section.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
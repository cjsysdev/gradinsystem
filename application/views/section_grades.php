<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CARMEN MUNICIPAL COLLEGE - GRADE SUBMISSION FORM</title>
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
    </style>
</head>

<div class="header">
    <h1>CARMEN MUNICIPAL COLLEGE</h1>
    <p>Poblacion Norte, Carmen, Bohol</p>
</div>

<table>
    <tr>
        <td>Form No.:</td>
        <td>GSF-CS-2023-001</td>
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
        <td colspan="2" class="form-title">GRADE SUBMISSION FORM</td>
    </tr>
    <tr>
        <td>Term:</td>
        <td><?= ucfirst($term) ?></td>

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
        <td><?= $section ?></td>
    </tr>
</table>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Student ID</th>
            <th>Firstname</th>
            <th>Lastname</th>
            <th>Grade Point</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($studentsGrades)): ?>
            <?php foreach ($studentsGrades as $student): ?>
                <tr>
                    <td><?= $student['student_id'] ?></td>
                    <td><?= $student['firstname'] ?></td>
                    <td><?= $student['lastname'] ?></td>
                    <td><?= is_numeric($student['grade_point']) ? number_format($student['grade_point'], 2) : $student['grade_point'] ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="text-center">No grades available for this section.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
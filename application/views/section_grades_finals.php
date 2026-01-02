<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CARMEN MUNICIPAL COLLEGE - GRADE SUBMISSION FORM (FINALS)</title>
    <style>
        body {
            font-family: Calibri, sans-serif;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: 11px;
        }

        .header p {
            margin: 5px 0 0 0;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 3px;
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

<div style="margin-top: 20px; margin-bottom: 0; font-size:16px;">
    <b>Course Code:</b> <?= $class_code ?><br>
    <b>Course Title:</b> <?= $class_name ?><br>
    <b>Section:</b> <?= strtoupper($section) ?><br>
    <?php if (isset($schedule)): ?>
        <b>Schedule:</b> <?= $schedule ?><br>
    <?php endif; ?>
</div>

<table class="inc-table" style="margin-top: 10px;">
    <thead>
        <tr>
            <th style="width:50px;">ID No.</th>
            <th style="width:350px;">Student Name <span style="font-weight:normal;">(Lastname, First name M.I)</span></th>
            <!-- <th style="width:80px;">Midterm Grade</th> -->
            <!-- <th style="width:80px;">Tentative Final Grade</th> -->
            <th style="width:80px;">Grade</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $i = 1;
        if (!empty($studentsGrades)):
            foreach ($studentsGrades as $student): ?>
                <tr<?php if (strtoupper($student['final_grade']) === 'INC' || (float)$student['final_grade'] > 3.00) echo ' class="highlight-inc"'; ?>>
                    <td><?= $i++ ?></td>
                    <td style="text-align:left;">
                        <?= strtoupper($student['lastname']) ?>, <?= strtoupper($student['firstname']) ?>
                        <?php if (!empty($student['middlename'])): ?>
                            <?= strtoupper(substr($student['middlename'], 0, 1)) ?>.
                        <?php endif; ?>
                    </td>
                    <!-- <td>
                        <?php
                        // Always show one decimal place for numeric grades
                        if (is_numeric($student['midterm_grade'])) {
                            echo convertPercentageToGradePoint(number_format($student['midterm_grade'], 1));
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        // Always show one decimal place for numeric grades
                        if (is_numeric($student['tentative_final_grade'])) {
                            echo convertPercentageToGradePoint(number_format($student['tentative_final_grade'], 1));
                        }
                        ?>
                    </td> -->
                    <td style="text-align:left;">
                        <?php
                        // Always show one decimal place for numeric grades
                        if (is_numeric($student['final_grade'])) {
                            echo number_format($student['final_grade'], 1);
                        } else {
                            echo $student['final_grade'];
                        }
                        ?>
                    </td>
                    </tr>
                <?php endforeach;
        else: ?>
                <tr>
                    <td colspan="3" class="text-center">No student grades available for this section.</td>
                </tr>
            <?php endif; ?>
    </tbody>
</table>

<div class="prepared">
    Prepared By:<br><br>
    <span class="faculty" style="font-weight:bold;">Criscel Jay F. Nayve</span><br>
    Faculty
</div>
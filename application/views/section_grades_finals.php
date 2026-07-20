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

<!-- <div style="margin-top: 20px; margin-bottom: 0; font-size:16px;">
    <b>Course Code:</b> <?= $class_code ?><br>
    <b>Course Title:</b> <?= $class_name ?><br>
    <b>Section:</b> <?= strtoupper($section) ?><br>
    <?php if (isset($schedule)): ?>
        <b>Schedule:</b> <?= $schedule ?><br>
    <?php endif; ?>
</div> -->

<table class="inc-table" style="margin-top: 10px;">
    <thead>
        <tr>
            <!-- <th style="width:50px;">ID No.</th> -->
            <th style="width:80px;">Student ID</th>
            <th style="width:80px;">Last Name</th>
            <th style="width:80px;">First Name</th>
            <th style="width:80px;">Midterm Grade</th>
            <th style="width:80px;">Final Grade</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $i = 1;
        if (!empty($studentsGrades)):
            foreach ($studentsGrades as $student): ?>
                <?php // Grades arrive display-ready from Grade_calculator. This view
                      // no longer converts a percentage to a grade point at render
                      // time, nor applies its own INC cutoff. ?>
                <tr<?php if ($student['is_inc']) echo ' class="highlight-inc"'; ?>>
                    <td style="text-align:left;">
                        <?= strtoupper(htmlspecialchars($student['student_no'])) ?>
                    </td>
                    <td style="text-align:left;">
                        <?= strtoupper(htmlspecialchars($student['lastname'])) ?>
                    </td>
                    <td style="text-align:left;">
                        <?= strtoupper(htmlspecialchars($student['firstname'])) ?>
                    </td>
                    <td>
                        <?= htmlspecialchars($student['midterm_grade']) ?>
                    </td>
                    <td style="text-align:left;">
                        <?php if (is_numeric($student['overall_grade'])): ?>
                            <b><?= htmlspecialchars($student['overall_grade']) ?></b>
                        <?php else: ?>
                            <?= htmlspecialchars($student['overall_grade']) ?>
                        <?php endif; ?>
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
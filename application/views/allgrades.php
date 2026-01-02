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

<img src="<?= base_url('./assets/finalgrades_header.jpg') ?>" alt="finalgrades_header" style="width:100%; max-width:800px;">

<?php
// Normalize incoming data (supports either $studentsGrades or $studentsGrades['studentsGrades'])
$studentsList = [];
if (!empty($studentsGrades)) {
    $studentsList = isset($studentsGrades['studentsGrades']) ? $studentsGrades['studentsGrades'] : $studentsGrades;
}

// Group students by section
$sections = [];
foreach ($studentsList as $s) {
    $sec = isset($s['section']) ? $s['section'] : 'UNKNOWN';
    $sections[$sec][] = $s;
}

if (!empty($sections)):
    foreach ($sections as $sectionName => $students):
        // use first student entry for course-level info
        $first = reset($students);
        $class_code = isset($first['class_code']) ? $first['class_code'] : '';
        $class_name = isset($first['class_name']) ? $first['class_name'] : '';
        $schedule = isset($first['schedule']) ? $first['schedule'] : null;
?>
        <div style="margin-top: 20px; margin-bottom: 0; font-size:16px;">
            <b>Course Code:</b> <?= htmlspecialchars($class_code) ?><br>
            <b>Course Title:</b> <?= htmlspecialchars($class_name) ?><br>
            <b>Section:</b> <?= strtoupper(htmlspecialchars($sectionName)) ?><br>
            <?php if (!empty($schedule)): ?>
                <b>Schedule:</b> <?= htmlspecialchars($schedule) ?><br>
            <?php endif; ?>
        </div>

        <table class="inc-table" style="margin-top: 10px;">
            <thead>
                <tr>
                    <th style="width:50px;">ID No.</th>
                    <th style="width:350px;">Student Name <span style="font-weight:normal;">(Lastname, First name M.I)</span></th>
                    <th style="width:80px;">Grade</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 1;
                foreach ($students as $student): ?>
                    <tr<?php if (strtoupper((string)$student['final_grade']) === 'INC' || (is_numeric($student['final_grade']) && (float)$student['final_grade'] >= 3.1)) echo ' class="highlight-inc"'; ?>>
                        <td><?= $i++ ?></td>
                        <td style="text-align:left;">
                            <?= strtoupper(htmlspecialchars($student['lastname'])) ?>, <?= strtoupper(htmlspecialchars($student['firstname'])) ?>
                            <?php if (!empty($student['middlename'])): ?>
                                <?= strtoupper(htmlspecialchars(substr($student['middlename'], 0, 1))) ?>.
                            <?php endif; ?>
                        </td>
                        <td style="text-align:left;">
                            <?php
                            if (is_numeric($student['final_grade'])) {
                                echo number_format((float)$student['final_grade'], 1);
                            } else {
                                echo htmlspecialchars($student['final_grade']);
                            }
                            ?>
                        </td>
                        </tr>
                    <?php endforeach; ?>
            </tbody>
        </table>
    <?php
    endforeach;
else: ?>
    <div style="margin-top: 20px; margin-bottom: 0; font-size:16px;">
        No student grades available.
    </div>
<?php endif; ?>

<img src="<?= base_url('./assets/finalgrades_footer.jpg') ?>" alt="finalgrades_footer" style="width:100%; max-width:800px;">

<!-- <div class="prepared">
    Prepared By:<br><br>
    <span class="faculty" style="font-weight:bold;">Criscel Jay F. Nayve</span><br>
    Faculty
</div> -->
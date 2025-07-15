<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>CARMEN MUNICIPAL COLLEGE - STUDENTS WITH SPECIAL MARKS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .header-table,
        .info-table,
        .inc-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .header-table td,
        .header-table th,
        .info-table td,
        .info-table th,
        .inc-table td,
        .inc-table th {
            border: 1px solid #000;
            padding: 6px;
        }

        .header-table {
            margin-bottom: 0;
        }

        .header-table td {
            font-size: 14px;
        }

        .header-title {
            font-size: 18px;
            font-weight: bold;
            text-align: left;
        }

        .header-sub {
            font-size: 14px;
        }

        .form-title {
            font-weight: bold;
            text-align: center;
            font-size: 16px;
        }

        .inc-table th {
            background: #f2f2f2;
            text-align: center;
        }

        .inc-table td {
            text-align: center;
        }

        .prepared {
            margin-top: 40px;
        }

        .faculty {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <table class="header-table">
        <tr>
            <td rowspan="6" style="width:110px;text-align:center;padding:8px 4px;">
                <img src="<?= base_url('assets/cmc-logo.png') ?>" alt="CMC Logo" style="width:100px;">
            </td>
            <td rowspan="6" style="text-align:left;vertical-align:middle;padding-left:10px;">
                <span style="font-size:20px;font-weight:bold;">CARMEN MUNICIPAL COLLEGE</span><br>
                <span style="font-size:15px;">Poblacion Norte, Carmen, Bohol</span>
            </td>
            <td style="width:140px;">Form No.:</td>
            <td style="width:200px;font-weight:bold;">LOSWMS-CS-2025-001</td>
        </tr>
        <tr>
            <td>Revision No.:</td>
            <td style="font-weight:bold;">Rev. 1</td>
        </tr>
        <tr>
            <td>Effective Date:</td>
            <td style="font-weight:bold;">July 1, 2025</td>
        </tr>
        <tr>
            <td>Related Process:</td>
            <td></td>
        </tr>
        <tr>
            <td style="font-weight:bold;">STUDENTS WITH SPECIAL MARKS</td>
            <td></td>
        </tr>
        <tr>
            <td>Term:</td>
            <td style="font-weight:bold;">Finals</td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td>Year and Semester</td>
            <td style="font-weight:bold;">2024-2025 - 2<sup>nd</sup> SEM.</td>
        </tr>
    </table>

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
                <th style="width:80px;">Grade</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $incStudents = array_filter($studentsGrades, function ($s) {
                return isset($s['final_grade']) && $s['final_grade'] === 'INC';
            });
            $i = 1;
            if (!empty($incStudents)):
                foreach ($incStudents as $student): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td style="text-align:left;font-weight:bold;">
                            <?= strtoupper($student['lastname']) ?>, <?= strtoupper($student['firstname']) ?>
                            <?php if (!empty($student['middlename'])): ?>
                                <?= strtoupper(substr($student['middlename'], 0, 1)) ?>.
                            <?php endif; ?>
                        </td>
                        <td style="font-weight:bold;">INC</td>
                    </tr>
                <?php endforeach;
            else: ?>
                <tr>
                    <td colspan="3" class="text-center">No students with INC grade for this section.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="prepared">
        Prepared By:<br><br>
        <span class="faculty">&lt;Faculty Name&gt;</span><br>
        Faculty
    </div>
</body>

</html>
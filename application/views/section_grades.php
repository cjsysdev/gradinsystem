<h4>Section Grades for <?= $section ?> (<?= ucfirst($term) ?> Term)</h4>

<table class="table table-bordered" style="border: 1px solid black; border-collapse: collapse; width: 100%;">
    <thead>
        <tr>
            <th style="border: 1px solid black;">Student ID</th>
            <th style="border: 1px solid black;">Lastname</th>
            <th style="border: 1px solid black;">Firstname</th>
            <th style="border: 1px solid black;">Midterm Total Grade</th>
            <th style="border: 1px solid black;">Grade Point</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($studentsGrades)): ?>
            <?php foreach ($studentsGrades as $student): ?>
                <tr>
                    <td style="border: 1px solid black;"><?= $student['student_id'] ?></td>
                    <td style="border: 1px solid black;"><?= $student['lastname'] ?></td>
                    <td style="border: 1px solid black;"><?= $student['firstname'] ?></td>
                    <td style="border: 1px solid black;"><?= round($student['midterm_total_grade'], 2) ?>%</td>
                    <td style="border: 1px solid black;"><?= number_format($student['grade_point'], 2) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="text-center" style="border: 1px solid black;">No grades available for this section.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
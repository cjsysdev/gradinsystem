<?php $this->load->view('header') ?>

<div class="container">
    <div class="dashboard">
        <?php $this->load->view('profile_info') ?>

        <div class="category-btns">
            <h4 class="text-center">Midterm Grades</h4>
            <?php if (!empty($midtermGrades)): ?>
                <?php foreach ($midtermGrades as $grade): ?>
                    <div class="alert alert-secondary block text-center">
                        <?= $grade['iotype_name'] ?? 'N/A' ?> (<?= $grade['iotype_percentage'] ?? '0' ?>%)<br>
                        <div class="progress m-2">
                            <div class="progress-bar bg-info" role="progressbar" style="width: <?= $grade['percentage'] ?>%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <?= round($grade['total_score'] ?? 0, 1) . '/' .  round($grade['total_max_score'] ?? 0, 1) ?>
                        (<?= number_format(floor($grade['grade_point'] * 10) / 10, 1) ?>)
                    </div>
                <?php endforeach; ?>
                <div class="total-section mt-3">
                    <div class="alert alert-info block text-center">
                        Total Midterm Grade:
                        <div class="progress m-2">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $midtermTotalGrade ?? '0' ?>%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        </div> ( <?= number_format(convertPercentageToGradePoint($midtermTotalGrade ?? 0), 1) ?> )
                    </div>
                </div>
            <?php else: ?>
                <p class="text-center">No grades available for Midterm.</p>
            <?php endif; ?>
        </div>

        <div class="category-btns mt-4">
            <?php if (!empty($finalGrades)): ?>
                <h4 class="text-center">Final Term Grades</h4>
                <?php foreach ($finalGrades as $grade): ?>
                    <div class="alert alert-secondary block text-center">
                        <?= $grade['iotype_name'] ?? 'N/A' ?> (<?= $grade['iotype_percentage'] ?? '0' ?>%)<br>
                        <div class="progress m-2">
                            <div class="progress-bar bg-info" role="progressbar" style="width: <?= $grade['percentage'] ?>%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <?= round($grade['total_score'] ?? 0, 1) . '/' .  round($grade['total_max_score'] ?? 0, 1) ?>
                        (<?= number_format($grade['grade_point'] ?? 0, 1) ?>)
                    </div>
                <?php endforeach; ?>
                <div class="total-section mt-3">
                    <div class="alert alert-info block text-center">
                        Total Tentative-Final Grade:
                        <div class="progress m-2">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $midtermTotalGrade ?? '0' ?>%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                        </div> ( <?= number_format(convertPercentageToGradePoint($finalTotalGrade ?? 0), 1) ?> )
                    </div>
                </div>
            <?php else: ?>
                <p style="text-align: center;">No grades available for Final Term.</p>
            <?php endif; ?>
        </div>

        <?php if (!empty($finalGrades)): ?>
            <div class="total-section mt-4">
                <div class="alert alert-primary alert-total alert-block mb-5 text-center">
                    Overall Final Grade: <?= number_format(convertPercentageToGradePoint($overallFinalGrade) ?? 0, 1) ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $this->load->view('footer') ?>
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
                        <?= round($grade['total_score'] ?? 0, 1) . '/' .  round($grade['total_max_score'] ?? 0, 1) ?>
                        (<?= number_format($grade['grade_point'] ?? 0, 1) ?>)
                    </div>
                <?php endforeach; ?>
                <div class="total-section mt-3">
                    <div class="alert alert-info block text-center">
                        Total Midterm Grade: <br> <?= $midtermTotalGrade ?? '0' ?>% ( <?= number_format(convertPercentageToGradePoint($midtermTotalGrade ?? 0), 1) ?> )
                    </div>
                </div>
            <?php else: ?>
                <p>No grades available for Midterm.</p>
            <?php endif; ?>
        </div>

        <div class="category-btns mt-4">
            <?php if (!empty($finalGrades)): ?>
                <h4 class="text-center">Final Term Grades</h4>
                <?php foreach ($finalGrades as $grade): ?>
                    <button class="alert alert-light block">
                        <?= $grade['iotype_name'] ?? 'N/A' ?> (<?= $grade['iotype_percentage'] ?? '0' ?>%)<br>
                        <?= round($grade['total_score'] ?? 0, 1) . '/' .  round($grade['total_max_score'] ?? 0, 1) ?>
                        (<?= number_format($grade['grade_point'] ?? 0, 1) ?>)
                    </button>
                <?php endforeach; ?>
                <div class="total-section mt-3">
                    <button class="btn btn-secondary btn-total btn-block">
                        Total Final Term Grade: <?= $finalTotalGrade ?? '0' ?>% ( <?= number_format(convertPercentageToGradePoint($finalTotalGrade ?? 0), 1) ?> )
                    </button>
                </div>
            <?php else: ?>
                <p style="text-align: center;">No grades available for Final Term.</p>
            <?php endif; ?>
        </div>

        <?php if (!empty($finalGrades)): ?>
            <div class="total-section mt-4">
                <button class="btn btn-primary btn-total btn-block">
                    Overall Final Grade: <?= number_format($overallFinalGrade ?? 0, 1) ?>%
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $this->load->view('footer') ?>
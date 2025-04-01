<?php $this->load->view('header') ?>


<div class="container">
    <div class="dashboard">
        <?php $this->load->view('profile_info') ?>

        <div class="category-btns">
            <h4 class="text-center">Midterm Grades</h4>
            <?php if (!empty($midtermGrades)): ?>
                <?php foreach ($midtermGrades as $grade): ?>
                    <div class="alert alert-secondary block text-center">
                        <?= $grade['iotype_name'] ?> (<?= $grade['iotype_percentage'] ?>%)<br>
                        <?= round($grade['percentage'], 1) . '%' ?>
                        (<?= round($grade['grade_point'], 1) ?>)
                    </div>
                <?php endforeach; ?>
                <div class="total-section mt-3">
                    <div class="alert alert-info block text-center">
                        Total Midterm Grade: <br> <?= $midtermTotalGrade ?>% ( <?= convertPercentageToGradePoint($midtermTotalGrade) ?> )
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
                        <?= $grade['iotype_name'] ?> (<?= $grade['iotype_percentage'] ?>%)<br>
                        <?= round($grade['percentage'], 1) . '%' ?>
                        (<?= round($grade['grade_point'], 1) ?>)
                    </button>
                <?php endforeach; ?>
                <div class="total-section mt-3">
                    <button class="btn btn-secondary btn-total btn-block">
                        Total Final Term Grade: <?= $finalTotalGrade ?>% ( <?= convertPercentageToGradePoint($finalTotalGrade) ?> )
                    </button>
                </div>
            <?php else: ?>
                <p style=" text-align: center;">No grades available for Final Term.</p>
            <?php endif; ?>
        </div>
        <!-- 
        <div class="total-section mt-4">
            <button class="btn btn-primary btn-total btn-block">
                Overall Final Grade: <?= $overallFinalGrade ?>%
            </button>
        </div> -->
    </div>
</div>

<?php $this->load->view('footer') ?>
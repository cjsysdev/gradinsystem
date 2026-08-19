<?php $this->load->view('header') ?>

<link rel="stylesheet" href="<?= base_url('assets/grade-estimator.css') ?>">

<?php
/**
 * Student grade dashboard.
 *
 * All numbers arrive precomputed from Grade_calculator via GradesController.
 * This view does no arithmetic and never calls a transmutation function —
 * it previously floored the midterm grade point but rounded the final one,
 * and called convertPercentageToGradePoint() on already-weighted percentages.
 *
 * Per-component percentages are always shown, because each is valid on its
 * own. Only the term total is withheld when the term is INC.
 *
 * Every real value lives inside a .gc-actual wrapper and every estimator mount
 * point is an empty .gc-estimate next to it. assets/js/grade-estimator.js
 * swaps their visibility; with JS off, .gc-estimate stays empty and hidden and
 * the page is exactly what it was before the estimator existed.
 */
$render_term = function ($term, $label) {
    if (($term['status'] ?? '') === 'ok') {
        $pct = number_format($term['percentage'], 2);
        $gp  = number_format($term['grade_point'], 2);
        return ['pct' => $pct, 'gp' => $gp, 'inc' => false, 'width' => (float) $term['percentage']];
    }
    return ['pct' => '—', 'gp' => 'INC', 'inc' => true, 'width' => 0];
};
?>

<div class="container">
    <div class="dashboard">
        <?php $this->load->view('profile_info') ?>

        <?php if (!empty($no_enrollment)): ?>
            <div class="alert alert-warning text-center mt-3">
                You are not currently enrolled in an active class, so no grades can be shown.
            </div>
        <?php else: ?>

            <?php if (!empty($estimator)): ?>
                <div class="gc-estimate-controls">
                    <button type="button" id="gc-estimate-toggle"
                            class="btn btn-outline-primary" aria-pressed="false">
                        Estimate my grade
                    </button>
                    <button type="button" id="gc-estimate-reset"
                            class="btn btn-outline-secondary" hidden>
                        Reset to actual
                    </button>
                </div>
                <div class="alert alert-warning text-center" id="gc-estimate-banner" hidden>
                    <strong>Estimate only.</strong> These are "what if" numbers you are
                    adjusting yourself — they are not your official grades and nothing here
                    is saved or sent to your instructor.
                </div>
            <?php endif; ?>

            <?php foreach ([['Midterm', $midtermGrades, $midterm, 'midterm'], ['Final Term', $finalGrades, $final, 'final']] as $block): ?>
                <?php
                list($label, $components, $term, $term_key) = $block;
                $t = $render_term($term, $label);
                ?>
                <div class="category-btns mt-4">
                    <h4 class="text-center"><?= $label ?> Grades</h4>

                    <?php if (!empty($components)): ?>
                        <?php foreach ($components as $grade): ?>
                            <div class="alert alert-secondary block text-center gc-component"
                                 data-gc-term="<?= $term_key ?>"
                                 data-gc-iotype="<?= (int) $grade['iotype_id'] ?>">
                                <div class="gc-actual">
                                    <?= htmlspecialchars($grade['iotype_name']) ?>
                                    (<?= $grade['iotype_percentage'] ?>%)<br>

                                    <?php if (empty($grade['n_assessments'])): ?>
                                        <span class="text-muted">Not yet recorded</span>
                                    <?php else: ?>
                                        <div class="progress m-2">
                                            <div class="progress-bar bg-info" role="progressbar"
                                                 style="width: <?= (float) $grade['percentage'] ?>%"
                                                 aria-valuenow="<?= (float) $grade['percentage'] ?>"
                                                 aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <?= number_format((float) $grade['percentage'], 1) ?>%
                                        (<?= $grade['grade_point'] === null ? 'N/A' : number_format($grade['grade_point'], 2) ?>)
                                        <?php if (!empty($grade['n_ungraded'])): ?>
                                            <br><small class="text-muted">
                                                <?= (int) $grade['n_ungraded'] ?> item(s) awaiting grading — counted as 0 for now
                                            </small>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="gc-estimate" hidden></div>
                            </div>
                        <?php endforeach; ?>

                        <div class="total-section mt-3">
                            <div class="alert alert-info block text-center gc-term-total"
                                 data-gc-term="<?= $term_key ?>">
                                <div class="gc-actual">
                                    Total <?= $label ?> Grade:
                                    <?php if ($t['inc']): ?>
                                        <br><strong>INC</strong>
                                        <br><small><?= htmlspecialchars($term['reason'] === 'missing_components'
                                            ? 'Provisional — not every component has been recorded yet.'
                                            : 'Incomplete.') ?></small>
                                    <?php else: ?>
                                        <div class="progress m-2">
                                            <div class="progress-bar bg-primary" role="progressbar"
                                                 style="width: <?= $t['width'] ?>%"
                                                 aria-valuenow="<?= $t['width'] ?>"
                                                 aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <?= $t['pct'] ?>% ( <?= $t['gp'] ?> )
                                    <?php endif; ?>
                                </div>
                                <div class="gc-estimate" hidden></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-center">No grades available for <?= $label ?>.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="total-section mt-4">
                <div class="alert alert-primary alert-total alert-block mb-5 text-center gc-overall">
                    <div class="gc-actual">
                        Overall Final Grade:
                        <?php if (($overall['status'] ?? '') === 'ok'): ?>
                            <strong><?= number_format($overall['grade_point'], 2) ?></strong>
                            (<?= number_format($overall['percentage'], 2) ?>%)
                        <?php else: ?>
                            <strong>INC</strong>
                            <br><small>Available once both the midterm and final term are complete.</small>
                        <?php endif; ?>
                    </div>
                    <div class="gc-estimate" hidden></div>
                </div>
            </div>

        <?php endif; ?>

        <?php if (!empty($recommendations)): ?>
            <div class="category-btns mt-4 mb-5">
                <h4 class="text-center">Recommendations</h4>
                <?php foreach ($recommendations as $rec): ?>
                    <div class="alert alert-<?= htmlspecialchars($rec['type']) ?>">
                        <?= htmlspecialchars($rec['message']) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($estimator)): ?>
    <?php
    /**
     * The estimator's entire input. Every rule it applies comes from here, so
     * the browser never carries a grading constant of its own. HEX_APOS/HEX_QUOT
     * keep the JSON safe inside a <script> block.
     */
    ?>
    <script type="application/json" id="gc-estimator-payload">
        <?= json_encode($estimator, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
    </script>
    <script src="<?= base_url('assets/js/grade-estimator.js') ?>"></script>
<?php endif; ?>

<?php $this->load->view('footer') ?>

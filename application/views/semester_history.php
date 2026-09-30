<?php
/**
 * Academic history: one block per semester, one row per course.
 * Expects $history from Grade_calculator::history_for_student() and
 * $history_link_base (URL prefix; ?sem=<id> is appended for each semester).
 */
$gc = $this->Grade_calculator;
if (empty($history)): ?>
    <div class="alert alert-info py-2">No enrolment history found.</div>
<?php return; endif;
foreach ($history as $sem_id => $block):
    $sem = $block['semester'];
    $label = $sem ? ($sem['description'] ?: $sem['semcode']) : 'Semester #' . (int) $sem_id;
?>
    <div class="card shadow-sm mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong><?= htmlspecialchars($label) ?><?= !empty($sem['is_active']) ? ' <span class="badge badge-success">active</span>' : '' ?></strong>
            <?php if (!empty($history_link_base)): ?>
                <a class="btn btn-sm btn-outline-primary" href="<?= htmlspecialchars($history_link_base) ?>?sem=<?= (int) $sem_id ?>">Open records</a>
            <?php endif; ?>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead class="thead-light">
                    <tr><th>Course</th><th>Section</th><th class="text-center">Midterm</th><th class="text-center">Final</th><th class="text-center">Overall</th></tr>
                </thead>
                <tbody>
                <?php foreach ($block['courses'] as $c): $g = $c['grade']; ?>
                    <tr>
                        <td><?= htmlspecialchars($c['class_code'] . ' — ' . $c['class_name']) ?> <small class="text-muted"><?= htmlspecialchars($c['type']) ?></small></td>
                        <td><?= htmlspecialchars($c['section']) ?></td>
                        <?php if ($g): ?>
                            <td class="text-center"><?= $gc->display_grade_point($g['midterm'], 1) ?></td>
                            <td class="text-center"><?= $gc->display_grade_point($g['final'], 1) ?></td>
                            <td class="text-center font-weight-bold"><?= $gc->display_grade_point($g['overall'], 1) ?></td>
                        <?php else: ?>
                            <td colspan="3" class="text-center text-muted">No grade data</td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endforeach; ?>

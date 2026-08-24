<?php
/**
 * Clearance roster for ONE section, ONE semester, ONE term.
 *
 * The whole roster is listed (uncleared first) rather than only the uncleared,
 * so a mistaken clearance can be revoked without hunting for the student — the
 * clearance row exists per semester + term, so revoking here touches only this
 * term.
 *
 * @var array  $students    student_id, student_no, names, is_cleared, cleared_at, cleared_by
 * @var string $section
 * @var string $term        midterm|final
 * @var array  $terms       term key => label
 * @var int    $semester_id
 * @var array  $semester    the semester_master row in scope (or null)
 * @var array  $semesters   every semester, newest first
 * @var bool   $table_ready student_clearance exists yet
 */
$this->load->view('header');
$scope_q   = '?semester=' . (int) $semester_id . '&term=' . urlencode($term);
$section_u = rawurlencode($section);

$uncleared = 0;
foreach ($students as $s) {
    if (empty($s['is_cleared'])) {
        $uncleared++;
    }
}
?>

<div class="container">
    <?php $this->load->view('profile_only'); ?>
    <?php $this->load->view('admin/nav_bar'); ?>

    <div class="d-flex align-items-center flex-wrap mt-4 mb-2">
        <a href="<?= base_url('uncleared_students') . $scope_q ?>" class="btn btn-outline-secondary btn-sm mr-3">
            <i class="fa fa-arrow-left"></i> All Sections
        </a>
        <h4 class="mb-0">
            Clearance &mdash; Section: <span class="text-primary"><?= htmlspecialchars($section) ?></span>
        </h4>
        <span class="badge <?= $uncleared ? 'badge-danger' : 'badge-success' ?> ml-2" style="font-size:.85rem;">
            <?= $uncleared ? $uncleared . ' uncleared' : 'All cleared' ?>
        </span>
    </div>

    <p class="text-muted">
        <?= $semester ? htmlspecialchars($semester['description']) : 'No semester selected' ?>
        &middot; <strong><?= htmlspecialchars($terms[$term]) ?></strong> term
    </p>

    <!-- Scope: semester + term. Both are part of the clearance key, so
         switching either one shows a different set of clearances. -->
    <div class="card mb-3">
        <div class="card-body py-3">
            <form method="get" action="<?= base_url('uncleared_students/' . $section_u) ?>" class="form-row align-items-end">
                <input type="hidden" name="term" value="<?= htmlspecialchars($term) ?>">
                <div class="col-12 col-md-6 mb-2 mb-md-0">
                    <label for="semester" class="small text-muted mb-1">Semester</label>
                    <select name="semester" id="semester" class="form-control form-control-sm"
                            onchange="this.form.submit()">
                        <?php foreach ($semesters as $sem): ?>
                            <option value="<?= (int) $sem['trans_no'] ?>"
                                <?= (int) $sem['trans_no'] === (int) $semester_id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sem['description']) ?><?= !empty($sem['is_active']) ? ' (active)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <span class="small text-muted d-block mb-1">Term</span>
                    <div class="btn-group btn-group-sm" role="group">
                        <?php foreach ($terms as $key => $label): ?>
                            <a href="<?= base_url('uncleared_students/' . $section_u) ?>?semester=<?= (int) $semester_id ?>&term=<?= urlencode($key) ?>"
                               class="btn <?= $term === $key ? 'btn-primary' : 'btn-outline-primary' ?>">
                                <?= htmlspecialchars($label) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <noscript><button type="submit" class="btn btn-sm btn-secondary ml-2">Go</button></noscript>
            </form>
        </div>
    </div>

    <?php if (!$table_ready): ?>
        <div class="alert alert-warning">
            <strong>Clearance table not installed yet.</strong>
            Run the one-time setup from the
            <a href="<?= base_url('uncleared_students') ?>">clearance overview</a>.
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-info">
                <tr class="text-center">
                    <th>Student No.</th>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($students)): ?>
                    <?php foreach ($students as $student): ?>
                        <?php
                        $is_cleared = !empty($student['is_cleared']);
                        $full_name  = $student['firstname'] . ' ' . $student['lastname'];
                        ?>
                        <tr class="<?= $is_cleared ? '' : 'table-warning' ?>">
                            <td><?= htmlspecialchars((string) $student['student_no']) ?></td>
                            <td><?= htmlspecialchars($student['lastname']) ?></td>
                            <td><?= htmlspecialchars($student['firstname']) ?></td>
                            <td class="text-center">
                                <?php if ($is_cleared): ?>
                                    <span class="badge badge-success">Cleared</span>
                                    <?php if (!empty($student['cleared_at'])): ?>
                                        <small class="d-block text-muted">
                                            <?= date('M j, Y', strtotime($student['cleared_at'])) ?>
                                            <?= !empty($student['cleared_by']) ? ' &middot; ' . htmlspecialchars($student['cleared_by']) : '' ?>
                                        </small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge badge-danger">Uncleared</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($is_cleared): ?>
                                    <a href="<?= base_url('uncleared_students/unclear/' . (int) $student['student_id'] . '/' . $section_u) . $scope_q ?>"
                                       class="btn btn-outline-danger btn-sm"
                                       title="Revoke this term's clearance"
                                       onclick="return confirm('Revoke <?= htmlspecialchars(addslashes($full_name)) ?>&#39;s <?= htmlspecialchars(strtolower($terms[$term])) ?> clearance?')">
                                        <i class="fa fa-undo"></i> Unclear
                                    </a>
                                <?php else: ?>
                                    <a href="<?= base_url('uncleared_students/clear/' . (int) $student['student_id'] . '/' . $section_u) . $scope_q ?>"
                                       class="btn btn-success btn-sm"
                                       title="Mark as Cleared"
                                       onclick="return confirm('Mark <?= htmlspecialchars(addslashes($full_name)) ?> as cleared for the <?= htmlspecialchars(strtolower($terms[$term])) ?>?')">
                                        <i class="fa fa-check"></i> Clear
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-3">
                            No enrolled students in this section for the selected semester.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $this->load->view('footer'); ?>

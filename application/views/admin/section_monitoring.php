<?php $this->load->view('header'); ?>

<div class="container">
    <?php
    $this->load->view('profile_only');
    $this->load->view('admin/nav_bar');
    ?>

    <div class="row mt-3">
        <div class="col text-center">
            <h4 class="mb-0">Section Monitoring</h4>
            <small class="text-muted">Grades and attendance for one section, in roster order.</small>
        </div>
    </div>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger mt-3"><?= htmlspecialchars($this->session->flashdata('error')) ?></div>
    <?php endif; ?>

    <form method="GET" action="<?= base_url('view_attendance') ?>" class="form-row align-items-end mt-3 mb-3">
        <!-- Marks the query string as admin-submitted, so an unticked checkbox
             stays unticked instead of falling back to its default-on. See
             AdminController::_monitoring_filters(). -->
        <input type="hidden" name="filters_applied" value="1">

        <div class="col-md-3 mb-2">
            <label for="schedule_id" class="mb-1">Section</label>
            <select name="schedule_id" id="schedule_id" class="form-control">
                <option value="">-- Select Section --</option>
                <?php foreach ($schedules as $s): ?>
                    <option value="<?= (int) $s['schedule_id'] ?>" <?= (int) $schedule_id === (int) $s['schedule_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['section']) ?> &mdash; <?= htmlspecialchars($s['class_code']) ?> (<?= htmlspecialchars($s['type']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-4 mb-2">
            <label class="mb-1 d-block">Show</label>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="show_grades" value="1" id="show_grades" <?= $show_grades ? 'checked' : '' ?>>
                <label class="form-check-label" for="show_grades">Grades</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="show_attendance" value="1" id="show_attendance" <?= $show_attendance ? 'checked' : '' ?>>
                <label class="form-check-label" for="show_attendance">Attendance</label>
            </div>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="show_missing" value="1" id="show_missing" <?= $show_missing ? 'checked' : '' ?>>
                <label class="form-check-label" for="show_missing"
                       title="Count of classwork with nothing handed in, per component — due date ignored">Missing</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="only_with_values" value="1" id="only_with_values" <?= $only_with_values ? 'checked' : '' ?>>
                <label class="form-check-label small" for="only_with_values"
                       title="Hides students whose Missing and Absent tallies are all zero">
                    Only rows with missing / absences
                </label>
            </div>
        </div>

        <div class="col-md-3 mb-2">
            <label for="grade_mode" class="mb-1">Incomplete grades</label>
            <select name="grade_mode" id="grade_mode" class="form-control">
                <option value="inc" <?= $grade_mode === 'current' ? '' : 'selected' ?>>Show as INC</option>
                <option value="current" <?= $grade_mode === 'current' ? 'selected' : '' ?>>Show current grade</option>
            </select>
        </div>

        <div class="col-md-2 mb-2">
            <button type="submit" class="btn btn-primary">Filter</button>
            <?php if ($schedule_id && !empty($rows)): ?>
                <a href="<?= base_url('admin/export_section_monitoring?' . http_build_query($export_query)) ?>"
                   class="btn btn-outline-secondary">
                    <i class="fas fa-file-excel"></i> Export
                </a>
                <!-- No grade_mode on this link: printed slips are always the
                     official grade, whatever the screen is currently showing.
                     The term is picked on the print page itself. -->
                <a href="<?= base_url('admin/print_slips?schedule_id=' . (int) $schedule_id) ?>"
                   target="_blank" class="btn btn-outline-secondary" title="Printable half-sheet slips, 2 per page">
                    <i class="fas fa-print"></i> Slips
                </a>
            <?php endif; ?>
        </div>
    </form>

    <?php if (!$schedule_id): ?>
        <div class="alert alert-info">Pick a section to see its monitoring sheet.</div>
    <?php elseif (empty($rows) && $only_with_values && $total_rows > 0): ?>
        <div class="alert alert-success">
            All <?= (int) $total_rows ?> student<?= $total_rows === 1 ? '' : 's' ?> on this section are clear —
            nothing missing and no absences. Untick <em>Only rows with missing / absences</em> to see the full sheet.
        </div>
    <?php elseif (empty($rows)): ?>
        <div class="alert alert-warning">No students enrolled on this section for the active semester.</div>
    <?php else: ?>
        <?php if (!$show_grades && !$show_attendance && !$show_missing): ?>
            <div class="alert alert-secondary">Tick Grades, Attendance or Missing to show more columns.</div>
        <?php endif; ?>

        <?php if ($only_with_values && !$show_missing && !$show_attendance): ?>
            <div class="alert alert-secondary">
                <em>Only rows with missing / absences</em> has nothing to filter on while both
                <strong>Missing</strong> and <strong>Attendance</strong> are hidden, so the whole
                roster is shown. A row is only ever hidden on a tally the sheet is displaying.
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-bordered table-sm table-hover">
                <thead class="thead-light">
                    <tr>
                        <?php foreach ($columns as $c): ?>
                            <th<?= isset($c['label_long']) ? ' title="' . htmlspecialchars($c['label_long']) . '"' : '' ?>><?= htmlspecialchars($c['label']) ?></th>
                        <?php endforeach; ?>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <?php foreach ($columns as $c): ?>
                                <?php
                                // A provisional cell is not the official grade, so it never
                                // takes the red INC styling — it gets its own muted italic,
                                // explained by the legend below the table.
                                $provisional = !empty($row[$c['key'] . '_provisional']);
                                $kind = isset($c['kind']) ? $c['kind'] : '';
                                if ($kind === 'missing') {
                                    // A zero here is the good case, so it stays
                                    // quiet and only a real backlog draws the eye.
                                    $cell_class = $row[$c['key']] > 0
                                        ? 'text-danger font-weight-bold'
                                        : 'text-muted';
                                } elseif ($provisional) {
                                    $cell_class = 'font-italic text-info';
                                } elseif ($c['key'] === 'overall' && $row['is_inc']) {
                                    $cell_class = 'text-danger font-weight-bold';
                                } else {
                                    $cell_class = '';
                                }
                                ?>
                                <td<?= $cell_class ? ' class="' . $cell_class . '"' : '' ?>><?= htmlspecialchars((string) $row[$c['key']]) ?><?= $provisional ? '*' : '' ?></td>
                            <?php endforeach; ?>
                            <td class="text-nowrap">
                                <a href="<?= base_url('admin/student_summary/' . (int) $row['student_id']) ?>"
                                   class="btn btn-sm btn-outline-info" title="Attendance, classwork, violations and contacts for this student">
                                    <i class="fas fa-id-card"></i>
                                </a>
                                <a href="<?= base_url('admin/student_attendance/' . (int) $row['student_id']) ?>"
                                   class="btn btn-sm btn-outline-primary"> <i class="fas fa-edit"></i></a>
                                <a href="<?= base_url('admin/print_slips?schedule_id=' . (int) $schedule_id . '&student_id=' . (int) $row['student_id']) ?>"
                                   target="_blank" class="btn btn-sm btn-outline-secondary" title="Print this student's slip">
                                    <i class="fas fa-print"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($show_missing): ?>
            <p class="small text-muted mb-1">
                <strong>Missing</strong> columns count past-due classwork with
                <em>nothing handed in</em>, per component:
                <?php
                $legend = [];
                foreach ($columns as $c) {
                    if (isset($c['kind']) && $c['kind'] === 'missing') {
                        $legend[] = '<strong>' . htmlspecialchars($c['label']) . '</strong> = '
                            . htmlspecialchars(preg_replace('/^Missing /', '', $c['label_long']));
                    }
                }
                echo implode(', ', $legend);
                ?>.
                The due date is not consulted, so work that is still ahead of its
                deadline is counted too — read these as <em>outstanding</em>, not
                <em>late</em>. A submitted-but-ungraded item is not missing.
            </p>
        <?php endif; ?>

        <?php if ($show_grades && $grade_mode !== 'current'): ?>
            <p class="small text-muted mb-1">
                A grade reads <strong>INC</strong> when the term is incomplete
                <em>or</em> when the student has a Performance Task or Major Exam
                with nothing handed in — due date not considered. Switch
                <em>Incomplete grades</em> to <em>Show current grade</em> to see the
                standing behind an INC.
            </p>
        <?php endif; ?>

        <?php if ($show_grades && $grade_mode === 'current'): ?>
            <p class="small text-muted mb-1">
                <span class="font-italic text-info">Italic *</span> values are
                <strong>provisional</strong>: the term is not complete, so the grade shown is
                computed from the components recorded so far and rescaled to their weight.
                It is not the official grade and will change as the remaining components are
                added. Plain values are complete terms. Switch <em>Incomplete grades</em> back
                to <em>Show as INC</em> for the official view.
            </p>
        <?php endif; ?>

        <p class="text-muted small">
            <?php if ($only_with_values && count($rows) < $total_rows): ?>
                <?= count($rows) ?> of <?= (int) $total_rows ?> students shown —
                the rest have nothing missing and no absences. The <strong>#</strong>
                column keeps each student's place on the full roster, so it skips.
            <?php else: ?>
                <?= count($rows) ?> student<?= count($rows) === 1 ? '' : 's' ?>
            <?php endif; ?>
        </p>
    <?php endif; ?>
</div>

<?php $this->load->view('footer'); ?>

<?php $this->load->view('header'); ?>

<?php
$reasons = [
    'open' => 'Still open — the student can still hand it in',
];
// Where the grant editor's forms send the admin back to: this same preview.
$self = 'admin/auto_submit_missing_activities?' . http_build_query([
    'schedule_id' => (int) $sched['schedule_id'],
    'sem'         => (int) $sem,
]);
$n_eligible = 0;
$n_override = 0;
$grants     = []; // request_id => grant row, for the out-of-table edit forms
foreach ($students as $st) {
    foreach ($st['items'] as $it) {
        $n_eligible += $it['eligible'] ? 1 : 0;
        if ($it['reason'] === 'grant') {
            $n_override++;
            $grants[(int) $it['grant']['request_id']] = $it['grant'];
        }
    }
}
?>

<div class="container">
    <?php
    $this->load->view('profile_only');
    $this->load->view('admin/nav_bar');
    ?>

    <div class="row mt-3">
        <div class="col text-center">
            <h4 class="mb-0">Auto-submit Missing Activities</h4>
            <small class="text-muted">
                <?= htmlspecialchars($sched['section']) ?> &mdash; <?= htmlspecialchars($sched['class_code']) ?> (<?= htmlspecialchars($sched['type']) ?>)
            </small>
        </div>
    </div>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger mt-3"><?= htmlspecialchars($this->session->flashdata('error')) ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success mt-3"><?= htmlspecialchars($this->session->flashdata('success')) ?></div>
    <?php endif; ?>

    <div class="alert alert-info small mt-3">
        Each ticked activity gets a <strong>blank, unscored submission</strong> so you can enter a late
        score on it from the submissions page. Until you score it, it still counts as 0 &mdash; it just
        stops showing as missing. Untick anything you don't want submitted.
        <?php if ($n_override): ?>
            <br>Activities with an <strong>approved late submission</strong> are left unticked, since the
            student was told they can still hand them in. You can change or end that late submission
            here, or tick it to override &mdash; overriding ends the late submission.
        <?php endif; ?>
    </div>

    <?php if (empty($students)): ?>
        <div class="alert alert-success">Nobody on this section is missing an activity.</div>
        <a href="<?= base_url($back) ?>" class="btn btn-outline-secondary">Back to Section Monitoring</a>
    <?php else: ?>
        <form method="POST" action="<?= base_url('admin/auto_submit_missing_activities') ?>" id="auto-submit-form">
            <input type="hidden" name="schedule_id" value="<?= (int) $sched['schedule_id'] ?>">
            <input type="hidden" name="sem" value="<?= (int) $sem ?>">

            <div class="d-flex flex-wrap align-items-center mb-2">
                <div class="form-check mr-3">
                    <input class="form-check-input" type="checkbox" id="check-all" <?= $n_eligible ? 'checked' : 'disabled' ?>>
                    <label class="form-check-label font-weight-bold" for="check-all">Select all</label>
                </div>
                <span class="text-muted small"><span id="selected-count"><?= $n_eligible ?></span> of <?= $n_eligible + $n_override ?> selected</span>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th style="width: 30%">Student</th>
                            <th>Missing activities</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $st): ?>
                            <?php $has_eligible = (bool) array_filter(array_column($st['items'], 'eligible')); ?>
                            <tr>
                                <td>
                                    <div class="form-check">
                                        <input class="form-check-input student-toggle" type="checkbox"
                                               id="stu-<?= (int) $st['student_id'] ?>"
                                               data-student="<?= (int) $st['student_id'] ?>"
                                               <?= $has_eligible ? 'checked' : 'disabled' ?>>
                                        <label class="form-check-label font-weight-bold" for="stu-<?= (int) $st['student_id'] ?>">
                                            <?= htmlspecialchars($st['name']) ?>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <?php foreach ($st['items'] as $it): ?>
                                        <?php $pid = 'p-' . (int) $st['student_id'] . '-' . (int) $it['assessment_id']; ?>
                                        <?php $is_grant = $it['reason'] === 'grant'; ?>
                                        <div class="form-check<?= $is_grant ? ' mb-2' : '' ?>">
                                            <?php if ($it['eligible'] || $is_grant): ?>
                                                <!-- An override (approved late submission) is never
                                                     pre-ticked, and Select all / the student box leave
                                                     it alone: it has to be ticked on purpose. -->
                                                <input class="form-check-input pair<?= $is_grant ? ' override' : '' ?>" type="checkbox" name="pairs[]"
                                                       id="<?= $pid ?>"
                                                       data-student="<?= (int) $st['student_id'] ?>"
                                                       value="<?= (int) $st['student_id'] . ':' . (int) $it['assessment_id'] ?>"
                                                       <?= $is_grant ? '' : 'checked' ?>>
                                            <?php else: ?>
                                                <input class="form-check-input" type="checkbox" id="<?= $pid ?>" disabled>
                                            <?php endif; ?>
                                            <label class="form-check-label<?= ($it['eligible'] || $is_grant) ? '' : ' text-muted' ?>" for="<?= $pid ?>">
                                                <?= htmlspecialchars($it['title']) ?>
                                                <small class="text-muted">
                                                    &middot; <?= htmlspecialchars(ucfirst($it['term'])) ?>
                                                    <?php if (!empty($it['due'])): ?>
                                                        &middot; due <?= htmlspecialchars(date('M d, Y g:i A', strtotime($it['due']))) ?>
                                                    <?php endif; ?>
                                                    &middot; /<?= htmlspecialchars((string) (float) $it['max_score']) ?>
                                                </small>
                                                <?php if ($it['reason'] === 'open'): ?>
                                                    <span class="badge badge-light border"><?= htmlspecialchars($reasons['open']) ?></span>
                                                <?php elseif ($is_grant): ?>
                                                    <span class="badge badge-info">
                                                        Late submission approved until <?= htmlspecialchars(date('M j, Y g:i A', strtotime($it['grant']['granted_until']))) ?>
                                                    </span>
                                                    <small class="text-info">tick to override</small>
                                                <?php endif; ?>
                                            </label>
                                            <?php if ($is_grant): ?>
                                                <?php $fid = 'grant-' . (int) $it['grant']['request_id']; ?>
                                                <!-- These inputs belong to a form rendered after the main
                                                     one (form="..."), since forms can't nest. -->
                                                <div class="form-inline mt-1">
                                                    <label class="small text-muted mr-1" for="<?= $fid ?>-until">Reopen until</label>
                                                    <input type="datetime-local" name="granted_until" id="<?= $fid ?>-until" form="<?= $fid ?>"
                                                           class="form-control form-control-sm mr-1"
                                                           value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime($it['grant']['granted_until']))) ?>">
                                                    <button type="submit" form="<?= $fid ?>" name="action" value="save"
                                                            class="btn btn-sm btn-outline-primary mr-1">Save</button>
                                                    <button type="submit" form="<?= $fid ?>" name="action" value="end"
                                                            class="btn btn-sm btn-outline-danger"
                                                            data-confirm="End this late submission now? The activity locks again for this student, and it becomes eligible for auto-submit.">End now</button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mb-4">
                <button type="submit" class="btn btn-warning" id="submit-btn" <?= $n_eligible ? '' : 'disabled' ?>>
                    <i class="fas fa-inbox"></i> Auto-submit selected
                </button>
                <a href="<?= base_url($back) ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>

        <?php foreach ($grants as $request_id => $g): ?>
            <form method="POST" action="<?= base_url('admin/update_late_grant') ?>" id="grant-<?= (int) $request_id ?>" class="d-none">
                <input type="hidden" name="request_id" value="<?= (int) $request_id ?>">
                <input type="hidden" name="return" value="<?= htmlspecialchars($self) ?>">
            </form>
        <?php endforeach; ?>

        <script>
        (function () {
            var form   = document.getElementById('auto-submit-form');
            var all    = document.getElementById('check-all');
            var count  = document.getElementById('selected-count');
            var btn    = document.getElementById('submit-btn');
            // Bulk toggles only ever touch the normal pairs; overrides are
            // ticked one at a time. `ticked()` counts both.
            var pairs  = function (sid) {
                var sel = 'input.pair:not(.override)' + (sid ? '[data-student="' + sid + '"]' : '');
                return Array.prototype.slice.call(form.querySelectorAll(sel));
            };
            var ticked = function () {
                return form.querySelectorAll('input.pair:checked').length;
            };

            // Each student box reflects its own activities: ticked when all are,
            // indeterminate when some are. Same for Select all over everything.
            function sync() {
                var total = pairs(), on_total = total.filter(function (p) { return p.checked; }).length;
                form.querySelectorAll('input.student-toggle:not([disabled])').forEach(function (t) {
                    var own = pairs(t.dataset.student), on = own.filter(function (p) { return p.checked; }).length;
                    t.checked = on === own.length;
                    t.indeterminate = on > 0 && on < own.length;
                });
                all.checked = total.length > 0 && on_total === total.length;
                all.indeterminate = on_total > 0 && on_total < total.length;
                count.textContent = ticked();
                btn.disabled = ticked() === 0;
            }

            form.addEventListener('change', function (e) {
                var t = e.target;
                if (t === all) {
                    pairs().forEach(function (p) { p.checked = all.checked; });
                } else if (t.classList.contains('student-toggle')) {
                    pairs(t.dataset.student).forEach(function (p) { p.checked = t.checked; });
                }
                sync();
            });

            form.addEventListener('submit', function (e) {
                var n = ticked(), o = form.querySelectorAll('input.pair.override:checked').length;
                var msg = 'Create ' + n + ' blank submission' + (n === 1 ? '' : 's') + '?';
                if (o) {
                    msg += '\n\n' + o + ' of them override an approved late submission; '
                        + (o === 1 ? 'that late submission' : 'those late submissions') + ' will be ended.';
                }
                if (!confirm(msg)) {
                    e.preventDefault();
                }
            });

            // End now: the button lives outside its form (form="..."), so the
            // confirm hangs off the click rather than that form's submit.
            document.querySelectorAll('button[data-confirm]').forEach(function (b) {
                b.addEventListener('click', function (e) {
                    if (!confirm(b.dataset.confirm)) {
                        e.preventDefault();
                    }
                });
            });

            sync();
        })();
        </script>
    <?php endif; ?>
</div>

<?php $this->load->view('footer'); ?>

<?php
/**
 * Read-only semester picker + archived banner.
 * Expects: $semester_options (list of semester_master rows), $viewed_semester
 * (row), $viewing_archived (bool). Submits GET ?sem=<trans_no> to the current
 * URL; never changes the active semester.
 */
if (empty($semester_options) || empty($viewed_semester)) return;
?>
<form method="get" class="form-inline mt-3">
    <label class="mr-2 font-weight-bold" for="sem-switch"><i class="fa fa-calendar-alt"></i> Semester</label>
    <select id="sem-switch" name="sem" class="custom-select custom-select-sm mr-2" onchange="this.form.submit()">
        <?php foreach ($semester_options as $opt): ?>
            <option value="<?= (int) $opt['trans_no'] ?>"
                <?= (int) $opt['trans_no'] === (int) $viewed_semester['trans_no'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($opt['description'] ?: $opt['semcode']) ?><?= !empty($opt['is_active']) ? ' (active)' : '' ?>
            </option>
        <?php endforeach; ?>
    </select>
    <noscript><button class="btn btn-sm btn-primary">View</button></noscript>
</form>
<?php if (!empty($viewing_archived)): ?>
    <div class="alert alert-warning py-2 mt-2 mb-0">
        <i class="fa fa-archive"></i>
        Viewing <strong><?= htmlspecialchars($viewed_semester['description'] ?: $viewed_semester['semcode']) ?></strong>
        (archived, read-only).
        <a href="?" class="alert-link ml-2">Back to active semester</a>
    </div>
<?php endif; ?>

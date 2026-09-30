<?php $this->load->view('header'); ?>

<div class="container">
    <div class="dashboard">
        <?php $this->load->view('profile_only'); ?>
        <?php $this->load->view('admin/nav_bar'); ?>

        <div class="d-flex justify-content-between align-items-center mt-4 mb-3">
            <div>
                <h4 class="mb-0"><i class="fa fa-clock"></i> Batches &amp; Timer</h4>
                <div class="text-muted"><?= htmlspecialchars($assessment['title']) ?> &middot; Section <?= htmlspecialchars($section ?? '') ?></div>
            </div>
            <a href="<?= base_url('all_submissions/' . $section_id) ?>" class="btn btn-outline-secondary btn-sm">&larr; Back to submissions</a>
        </div>

        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i>
            Split this section into batches (e.g. one PC-limited lab shared across two turns).
            Each batch gets its own start/end time. A student sees the editor only during their
            own batch's window; when it ends, whatever they last autosaved is submitted
            automatically and marked <strong>TIME'S UP</strong>. Leave every batch field blank
            and save, or use "Remove timer" below, to go back to an untimed assessment.
        </div>

        <form method="post" action="<?= base_url('AdminAssessmentController/snippet_batches/' . $section_id) ?>" id="batchesForm">
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Batches</strong>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addBatchRow()"><i class="fa fa-plus"></i> Add batch</button>
                </div>
                <div class="card-body">
                    <table class="table table-sm" id="batchesTable">
                        <thead>
                            <tr>
                                <th style="width:5%">#</th>
                                <th>Start</th>
                                <th>End</th>
                                <th style="width:15%"></th>
                            </tr>
                        </thead>
                        <tbody id="batchesBody"></tbody>
                    </table>
                    <?php if (!empty($batches)): ?>
                        <button type="submit" name="clear_timer" value="1" class="btn btn-outline-danger btn-sm mt-2"
                                onclick="return confirm('Remove the timer? This assessment goes back to untimed — students can submit any time (subject to the normal due date).');">
                            <i class="fa fa-trash"></i> Remove timer
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Roster &mdash; assign each student to a batch</strong>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="autoSplit()"><i class="fa fa-shuffle"></i> Auto-split A&ndash;Z</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearAssignments()">Clear all</button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0" id="rosterTable">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th id="batchColHeaders" colspan="99">Batch</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($roster as $s): ?>
                                <?php $current_batch = (int) ($members[(string) (int) $s['student_id']] ?? 0); ?>
                                <tr class="roster-row" data-student-id="<?= (int) $s['student_id'] ?>">
                                    <td><?= htmlspecialchars($s['lastname'] . ', ' . $s['firstname']) ?></td>
                                    <td class="roster-batch-cells" colspan="99">
                                        <?php // Radio inputs for each batch are injected by JS (renderBatchOptions()),
                                        // so the set of batches always matches the editor above, even before save. ?>
                                        <input type="hidden" class="roster-current-batch" value="<?= $current_batch ?>">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($roster)): ?>
                                <tr><td colspan="2" class="text-muted text-center py-3">No enrolled students found for this section.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save batches &amp; assignments</button>
        </form>
    </div>
</div>

<script>
(function () {
    const existingBatches = <?= json_encode(array_values($batches)) ?>;

    function toLocalInputValue(mysqlDatetime) {
        // "2026-09-30 13:00:00" -> "2026-09-30T13:00" for <input type="datetime-local">.
        if (!mysqlDatetime) return '';
        return mysqlDatetime.replace(' ', 'T').slice(0, 16);
    }

    function batchRowHtml(n, start, end) {
        return '<tr data-n="' + n + '">' +
            '<td>' + n + '</td>' +
            '<td><input type="datetime-local" class="form-control form-control-sm" name="batch_start_' + n + '" value="' + toLocalInputValue(start) + '" required></td>' +
            '<td><input type="datetime-local" class="form-control form-control-sm" name="batch_end_' + n + '" value="' + toLocalInputValue(end) + '" required></td>' +
            '<td>' +
                (n > 1 ? '<button type="button" class="btn btn-sm btn-outline-secondary" onclick="continueFromPrevious(' + n + ')" title="Start where the previous batch ends, same length">Continue &rarr;</button> ' : '') +
                '<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeBatchRow(' + n + ')" title="Remove this batch"><i class="fa fa-times"></i></button>' +
            '</td>' +
        '</tr>';
    }

    function batchCount() {
        return document.querySelectorAll('#batchesBody tr').length;
    }

    window.addBatchRow = function (start, end) {
        const n = batchCount() + 1;
        document.getElementById('batchesBody').insertAdjacentHTML('beforeend', batchRowHtml(n, start || '', end || ''));
        renderBatchOptions();
    };

    window.removeBatchRow = function (n) {
        // Renumber every row after a removal so batch numbers stay 1..N with
        // no gaps (student assignments referencing a removed number are
        // dropped — clearAssignments() on just that column, done below).
        const rows = Array.from(document.querySelectorAll('#batchesBody tr'));
        rows.forEach(function (row) {
            if (parseInt(row.dataset.n, 10) === n) row.remove();
        });
        const remaining = Array.from(document.querySelectorAll('#batchesBody tr'));
        remaining.forEach(function (row, idx) {
            const newN = idx + 1;
            row.dataset.n = newN;
            row.querySelector('td').textContent = newN;
            row.querySelector('input[name^="batch_start_"]').name = 'batch_start_' + newN;
            row.querySelector('input[name^="batch_end_"]').name = 'batch_end_' + newN;
        });
        document.querySelectorAll('.roster-current-batch').forEach(function (el) {
            if (parseInt(el.value, 10) > remaining.length) el.value = '0';
        });
        renderBatchOptions();
    };

    window.continueFromPrevious = function (n) {
        const prevRow = document.querySelector('#batchesBody tr[data-n="' + (n - 1) + '"]');
        const row = document.querySelector('#batchesBody tr[data-n="' + n + '"]');
        if (!prevRow || !row) return;
        const prevStart = prevRow.querySelector('input[name^="batch_start_"]').value;
        const prevEnd = prevRow.querySelector('input[name^="batch_end_"]').value;
        if (!prevStart || !prevEnd) return;
        const durMs = new Date(prevEnd) - new Date(prevStart);
        const newStart = new Date(prevEnd);
        const newEnd = new Date(newStart.getTime() + durMs);
        const pad = n => String(n).padStart(2, '0');
        const fmt = d => d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
        row.querySelector('input[name^="batch_start_"]').value = fmt(newStart);
        row.querySelector('input[name^="batch_end_"]').value = fmt(newEnd);
    };

    // Renders one radio button per current batch, per roster row — kept in
    // sync with the batches table above so the roster never offers a batch
    // number that doesn't exist (or misses one that was just added).
    function renderBatchOptions() {
        const n = batchCount();
        document.querySelectorAll('.roster-row').forEach(function (row) {
            const studentId = row.dataset.studentId;
            const cell = row.querySelector('.roster-batch-cells');
            // Prefer whatever's actually checked right now (an admin edit made
            // before adding/removing a batch) over the original server value —
            // rebuilding this cell's HTML must not silently discard that.
            const checkedRadio = cell.querySelector('input[type="radio"]:checked');
            const current = checkedRadio ? checkedRadio.value : row.querySelector('.roster-current-batch').value;
            let html = '<input type="hidden" class="roster-current-batch" value="' + current + '">';
            html += '<label class="mr-2 mb-0"><input type="radio" name="student_batch[' + studentId + ']" value="" ' + (current === '' || current === '0' ? 'checked' : '') + '> <span class="text-muted">Unassigned</span></label>';
            for (let b = 1; b <= n; b++) {
                html += ' <label class="mr-2 mb-0"><input type="radio" name="student_batch[' + studentId + ']" value="' + b + '" ' + (String(current) === String(b) ? 'checked' : '') + '> Batch ' + b + '</label>';
            }
            cell.innerHTML = html;
        });
    }

    window.autoSplit = function () {
        const n = batchCount();
        if (n < 1) { alert('Add at least one batch first.'); return; }
        const rows = Array.from(document.querySelectorAll('.roster-row'));
        const perBatch = Math.ceil(rows.length / n);
        rows.forEach(function (row, idx) {
            const batch = Math.min(n, Math.floor(idx / perBatch) + 1);
            const radio = row.querySelector('input[name^="student_batch"][value="' + batch + '"]');
            if (radio) radio.checked = true;
        });
    };

    window.clearAssignments = function () {
        document.querySelectorAll('.roster-row input[value=""]').forEach(function (r) { r.checked = true; });
    };

    // Seed the batches table from the saved config, or one blank batch to start.
    if (existingBatches.length) {
        existingBatches.forEach(function (b) { window.addBatchRow(b.start, b.end); });
    } else {
        window.addBatchRow();
        window.addBatchRow();
    }
})();
</script>

<?php $this->load->view('footer'); ?>

<?php
// Widget B — Worksheet Form (root/docs/paperless-midterm-plan.md #4).
// Shared by both input mode (student filling it out) and readonly display
// mode (admin grading, or student_submission.php), toggled by $readonly.
//
// $config   — ['columns' => [...], 'min_rows' => int, 'allow_add_rows' => bool]
// $readonly — bool
// $existing — ['rows' => [ [col => value, ...], ... ]] or null

$readonly       = $readonly ?? false;
$existing       = $existing ?? null;
$columns        = $config['columns'] ?? [];
$min_rows       = max(1, (int) ($config['min_rows'] ?? 1));
$allow_add_rows = !empty($config['allow_add_rows']);
$rows           = $existing['rows'] ?? [];

if (!$readonly && empty($rows)) {
    for ($i = 0; $i < $min_rows; $i++) {
        $rows[] = array_fill_keys($columns, '');
    }
}
?>
<div id="worksheet-widget">
    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <?php foreach ($columns as $col): ?>
                        <th><?= htmlspecialchars($col) ?></th>
                    <?php endforeach; ?>
                    <?php if (!$readonly): ?><th></th><?php endif; ?>
                </tr>
            </thead>
            <tbody id="worksheet-body">
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <?php foreach ($columns as $col): ?>
                            <td>
                                <?php if ($readonly): ?>
                                    <?= nl2br(htmlspecialchars($row[$col] ?? '')) ?>
                                <?php else: ?>
                                    <input type="text" class="form-control form-control-sm ws-cell" data-col="<?= htmlspecialchars($col) ?>" value="<?= htmlspecialchars($row[$col] ?? '') ?>">
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                        <?php if (!$readonly): ?>
                            <td><button type="button" class="btn btn-sm btn-outline-danger ws-remove-row">&times;</button></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                <?php if ($readonly && empty($rows)): ?>
                    <tr><td colspan="<?= count($columns) ?>" class="text-muted text-center">No submission.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if (!$readonly && $allow_add_rows): ?>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="worksheet-add-row">
            <i class="fas fa-plus"></i> Add Row
        </button>
    <?php endif; ?>
</div>

<?php if (!$readonly): ?>
<script>
(function () {
    const columns = <?= json_encode($columns) ?>;
    const tbody = document.getElementById('worksheet-body');

    function bindRemove(btn) {
        btn.addEventListener('click', () => btn.closest('tr').remove());
    }

    function addRow(values) {
        values = values || {};
        const tr = document.createElement('tr');
        columns.forEach(col => {
            const td = document.createElement('td');
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control form-control-sm ws-cell';
            input.dataset.col = col;
            input.value = values[col] || '';
            td.appendChild(input);
            tr.appendChild(td);
        });
        const tdBtn = document.createElement('td');
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-sm btn-outline-danger ws-remove-row';
        removeBtn.innerHTML = '&times;';
        bindRemove(removeBtn);
        tdBtn.appendChild(removeBtn);
        tr.appendChild(tdBtn);
        tbody.appendChild(tr);
    }

    tbody.querySelectorAll('.ws-remove-row').forEach(bindRemove);

    const addBtn = document.getElementById('worksheet-add-row');
    if (addBtn) addBtn.addEventListener('click', () => addRow());

    // Called by the host page right before it submits the form — serializes
    // this widget's state into the hidden #code-editor field so the existing
    // AssessmentController::submit_classwork() needs zero changes.
    window.serializeWidgetBeforeSubmit = function () {
        const rows = [];
        tbody.querySelectorAll('tr').forEach(tr => {
            const row = {};
            tr.querySelectorAll('.ws-cell').forEach(input => { row[input.dataset.col] = input.value; });
            if (Object.keys(row).length) rows.push(row);
        });
        const codeField = document.getElementById('code-editor');
        if (codeField) codeField.value = JSON.stringify({ rows });
    };
})();
</script>
<?php endif; ?>

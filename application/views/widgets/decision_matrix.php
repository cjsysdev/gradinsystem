<?php
// Widget F — Decision Matrix (root/docs/paperless-midterm-plan.md #4).
// Essentially Widget B (Worksheet) with fixed rows and typed columns
// (text vs. select) instead of free-form add/remove rows.
//
// $config   — ['rows' => [...], 'columns' => [ ['name' => '...', 'type' => 'text'|'select', 'options' => [...]], ... ]]
// $readonly — bool
// $existing — ['cells' => { row: { col_name: value, ... }, ... }] or null

$readonly = $readonly ?? false;
$existing = $existing ?? null;
$rows = $config['rows'] ?? [];
$columns = $config['columns'] ?? [];
$cells = $existing['cells'] ?? [];
?>
<div id="decision-matrix-widget">
    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th></th>
                    <?php foreach ($columns as $col): ?>
                        <th><?= htmlspecialchars($col['name'] ?? '') ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr data-row="<?= htmlspecialchars($row) ?>">
                        <th class="align-middle"><?= htmlspecialchars($row) ?></th>
                        <?php foreach ($columns as $col): ?>
                            <?php
                            $col_name = $col['name'] ?? '';
                            $col_type = $col['type'] ?? 'text';
                            $value = $cells[$row][$col_name] ?? '';
                            ?>
                            <td data-col="<?= htmlspecialchars($col_name) ?>">
                                <?php if ($readonly): ?>
                                    <?= htmlspecialchars((string) $value) ?: '&mdash;' ?>
                                <?php elseif ($col_type === 'select'): ?>
                                    <select class="form-control form-control-sm dm-cell">
                                        <option value="">&mdash;</option>
                                        <?php foreach (($col['options'] ?? []) as $opt): ?>
                                            <option value="<?= htmlspecialchars($opt) ?>" <?= ((string) $value === (string) $opt) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($opt) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php else: ?>
                                    <input type="text" class="form-control form-control-sm dm-cell" value="<?= htmlspecialchars((string) $value) ?>">
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                <?php if ($readonly && empty($rows)): ?>
                    <tr><td class="text-muted text-center">No submission.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (!$readonly): ?>
<script>
(function () {
    const widget = document.getElementById('decision-matrix-widget');

    function serializeCells() {
        const cells = {};
        widget.querySelectorAll('tr[data-row]').forEach(tr => {
            const row = {};
            tr.querySelectorAll('td[data-col]').forEach(td => {
                const input = td.querySelector('.dm-cell');
                if (input) row[td.dataset.col] = input.value;
            });
            cells[tr.dataset.row] = row;
        });
        return cells;
    }

    window.getWidgetState = function () {
        return JSON.stringify({ cells: serializeCells() });
    };

    window.setWidgetState = function (content) {
        let cells = {};
        try {
            cells = JSON.parse(content || '{}').cells || {};
        } catch (e) {
            return;
        }
        widget.querySelectorAll('tr[data-row]').forEach(tr => {
            const rowData = cells[tr.dataset.row] || {};
            tr.querySelectorAll('td[data-col]').forEach(td => {
                const input = td.querySelector('.dm-cell');
                if (input && rowData[td.dataset.col] !== undefined) {
                    input.value = rowData[td.dataset.col];
                }
            });
        });
    };

    window.isWidgetFocused = function () {
        return widget.contains(document.activeElement);
    };

    window.serializeWidgetBeforeSubmit = function () {
        const codeField = document.getElementById('widget-code-value');
        if (codeField) codeField.value = window.getWidgetState();
    };
})();
</script>
<?php endif; ?>

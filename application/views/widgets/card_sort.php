<?php
// Widget C — Card Sort / Classification Board (root/docs/paperless-midterm-plan.md #4).
//
// $config   — ['bins' => [...], 'items' => [...], 'require_justification' => bool]
// $readonly — bool
// $existing — ['placements' => [ ['item','bin','justification'], ... ]] or null

$readonly = $readonly ?? false;
$existing = $existing ?? null;
$bins = $config['bins'] ?? [];
$items = $config['items'] ?? [];
$require_justification = !empty($config['require_justification']);

$placements = $existing['placements'] ?? [];
$placement_by_item = [];
foreach ($placements as $p) {
    $placement_by_item[$p['item']] = $p;
}
?>
<div id="card-sort-widget" data-require-justification="<?= $require_justification ? '1' : '0' ?>">
    <?php if ($readonly): ?>
        <?php if (empty($placements)): ?>
            <p class="text-muted text-center">No submission.</p>
        <?php else: ?>
            <?php foreach ($bins as $bin): ?>
                <div class="card mb-2">
                    <div class="card-header py-2"><strong><?= htmlspecialchars($bin) ?></strong></div>
                    <ul class="list-group list-group-flush">
                        <?php $has_any = false; ?>
                        <?php foreach ($placements as $p): ?>
                            <?php if (($p['bin'] ?? '') === $bin): $has_any = true; ?>
                                <li class="list-group-item py-2">
                                    <?= htmlspecialchars($p['item']) ?>
                                    <?php if (!empty($p['justification'])): ?>
                                        <div class="text-muted small mt-1"><?= nl2br(htmlspecialchars($p['justification'])) ?></div>
                                    <?php endif; ?>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <?php if (!$has_any): ?>
                            <li class="list-group-item py-2 text-muted">&mdash;</li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th style="width:220px">Bin</th>
                        <?php if ($require_justification): ?><th>Justification</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody id="card-sort-body">
                    <?php foreach ($items as $item): ?>
                        <?php $p = $placement_by_item[$item] ?? null; ?>
                        <tr class="cs-row" data-item="<?= htmlspecialchars($item) ?>">
                            <td><?= htmlspecialchars($item) ?></td>
                            <td>
                                <select class="form-control form-control-sm cs-bin-select">
                                    <option value="">&mdash; Unsorted &mdash;</option>
                                    <?php foreach ($bins as $bin): ?>
                                        <option value="<?= htmlspecialchars($bin) ?>" <?= (($p['bin'] ?? '') === $bin) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($bin) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <?php if ($require_justification): ?>
                                <td>
                                    <input type="text" class="form-control form-control-sm cs-justification"
                                           value="<?= htmlspecialchars($p['justification'] ?? '') ?>"
                                           style="<?= empty($p['bin']) ? 'display:none' : '' ?>">
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php if (!$readonly): ?>
<script>
(function () {
    const widget = document.getElementById('card-sort-widget');
    const requireJustification = widget.dataset.requireJustification === '1';

    widget.querySelectorAll('.cs-row').forEach(row => {
        const select = row.querySelector('.cs-bin-select');
        const justification = row.querySelector('.cs-justification');
        if (!select || !justification) return;
        select.addEventListener('change', () => {
            justification.style.display = select.value ? '' : 'none';
        });
    });

    function serializePlacements() {
        const placements = [];
        widget.querySelectorAll('.cs-row').forEach(row => {
            const select = row.querySelector('.cs-bin-select');
            if (!select.value) return; // still unsorted
            const entry = { item: row.dataset.item, bin: select.value };
            if (requireJustification) {
                const justification = row.querySelector('.cs-justification');
                entry.justification = justification ? justification.value : '';
            }
            placements.push(entry);
        });
        return placements;
    }

    window.getWidgetState = function () {
        return JSON.stringify({ placements: serializePlacements() });
    };

    window.setWidgetState = function (content) {
        let placements = [];
        try {
            placements = JSON.parse(content || '{}').placements || [];
        } catch (e) {
            return;
        }
        const byItem = {};
        placements.forEach(p => { byItem[p.item] = p; });
        widget.querySelectorAll('.cs-row').forEach(row => {
            const p = byItem[row.dataset.item];
            const select = row.querySelector('.cs-bin-select');
            const justification = row.querySelector('.cs-justification');
            select.value = p ? p.bin : '';
            if (justification) {
                justification.value = p && p.justification ? p.justification : '';
                justification.style.display = select.value ? '' : 'none';
            }
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

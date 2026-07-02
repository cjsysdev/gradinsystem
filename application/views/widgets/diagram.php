<?php
// Widget E — Diagram / Flow Builder, fixed-flow mode only
// (root/docs/paperless-midterm-plan.md #4 — free-form canvas mode deferred).
//
// $config   — ['nodes' => ['Sense', 'Transmit', ...], 'connections' => 'sequential']
// $readonly — bool
// $existing — ['node_content' => {node_label: text, ...}] or null

$readonly = $readonly ?? false;
$existing = $existing ?? null;
$nodes = $config['nodes'] ?? [];
$node_content = $existing['node_content'] ?? [];
?>
<div id="diagram-widget">
    <div class="d-flex flex-wrap align-items-stretch justify-content-center">
        <?php foreach ($nodes as $i => $node): ?>
            <div class="card mb-3" style="min-width:200px;max-width:260px" data-node="<?= htmlspecialchars($node) ?>">
                <div class="card-header py-2 text-center"><strong><?= htmlspecialchars($node) ?></strong></div>
                <div class="card-body py-2">
                    <?php if ($readonly): ?>
                        <p class="mb-0"><?= nl2br(htmlspecialchars($node_content[$node] ?? '')) ?: '<span class="text-muted">&mdash;</span>' ?></p>
                    <?php else: ?>
                        <textarea class="form-control form-control-sm diagram-node-input" rows="4"
                                  placeholder="Describe this step..."><?= htmlspecialchars($node_content[$node] ?? '') ?></textarea>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($i < count($nodes) - 1): ?>
                <div class="d-flex align-items-center justify-content-center mb-3" style="min-width:40px">
                    <i class="fas fa-arrow-right fa-lg text-muted"></i>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php if ($readonly && empty($nodes)): ?>
            <p class="text-muted text-center">No submission.</p>
        <?php endif; ?>
    </div>
</div>

<?php if (!$readonly): ?>
<script>
(function () {
    const widget = document.getElementById('diagram-widget');

    function serializeNodes() {
        const node_content = {};
        widget.querySelectorAll('[data-node]').forEach(card => {
            const input = card.querySelector('.diagram-node-input');
            if (input) node_content[card.dataset.node] = input.value;
        });
        return node_content;
    }

    window.getWidgetState = function () {
        return JSON.stringify({ node_content: serializeNodes() });
    };

    window.setWidgetState = function (content) {
        let node_content = {};
        try {
            node_content = JSON.parse(content || '{}').node_content || {};
        } catch (e) {
            return;
        }
        widget.querySelectorAll('[data-node]').forEach(card => {
            const input = card.querySelector('.diagram-node-input');
            if (input && node_content[card.dataset.node] !== undefined) {
                input.value = node_content[card.dataset.node];
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

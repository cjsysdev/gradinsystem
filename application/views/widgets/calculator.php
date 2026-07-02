<?php
// Widget G — Calculator (root/docs/paperless-midterm-plan.md #4).
//
// $config   — ['inputs' => [ ['label' => '...', 'key' => '...'], ... ], 'formula' => 'cost / savings', 'result_label' => '...']
// $readonly — bool
// $existing — ['inputs' => {key: value, ...}, 'result' => number] or null

$readonly = $readonly ?? false;
$existing = $existing ?? null;
$inputs = $config['inputs'] ?? [];
$formula = $config['formula'] ?? '';
$result_label = $config['result_label'] ?? 'Result';
$input_values = $existing['inputs'] ?? [];
$result = $existing['result'] ?? null;
?>
<div id="calculator-widget" data-formula="<?= htmlspecialchars($formula) ?>">
    <?php foreach ($inputs as $inp): ?>
        <?php $key = $inp['key'] ?? ''; ?>
        <div class="form-group">
            <label><?= htmlspecialchars($inp['label'] ?? $key) ?></label>
            <?php if ($readonly): ?>
                <p class="form-control-plaintext"><?= htmlspecialchars((string) ($input_values[$key] ?? '')) ?: '&mdash;' ?></p>
            <?php else: ?>
                <input type="number" step="any" class="form-control calc-input" data-key="<?= htmlspecialchars($key) ?>"
                       value="<?= htmlspecialchars((string) ($input_values[$key] ?? '')) ?>">
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <div class="alert alert-info" id="calc-result-wrap">
        <strong><?= htmlspecialchars($result_label) ?>:</strong>
        <span id="calc-result"><?= $result !== null ? htmlspecialchars((string) $result) : '&mdash;' ?></span>
    </div>
</div>

<?php if (!$readonly): ?>
<script>
(function () {
    const widget = document.getElementById('calculator-widget');
    const formula = widget.dataset.formula;
    const resultEl = document.getElementById('calc-result');

    // Minimal safe arithmetic evaluator (+ - * / parentheses, no eval/Function)
    // so an admin-authored formula can be evaluated against student-entered values.
    function evalFormula(expr, vars) {
        let pos = 0;
        function peek() { return expr[pos]; }
        function skipWs() { while (pos < expr.length && /\s/.test(expr[pos])) pos++; }

        function parseExpr() {
            skipWs();
            let left = parseTerm();
            while (true) {
                skipWs();
                const c = peek();
                if (c === '+' || c === '-') {
                    pos++;
                    const right = parseTerm();
                    left = c === '+' ? left + right : left - right;
                } else break;
            }
            return left;
        }
        function parseTerm() {
            skipWs();
            let left = parseFactor();
            while (true) {
                skipWs();
                const c = peek();
                if (c === '*' || c === '/') {
                    pos++;
                    const right = parseFactor();
                    left = c === '*' ? left * right : left / right;
                } else break;
            }
            return left;
        }
        function parseFactor() {
            skipWs();
            const c = peek();
            if (c === '-') { pos++; return -parseFactor(); }
            if (c === '(') {
                pos++;
                const v = parseExpr();
                skipWs();
                if (peek() === ')') pos++;
                return v;
            }
            if (/[0-9.]/.test(c)) {
                const start = pos;
                while (pos < expr.length && /[0-9.]/.test(expr[pos])) pos++;
                return parseFloat(expr.slice(start, pos));
            }
            if (/[A-Za-z_]/.test(c)) {
                const start = pos;
                while (pos < expr.length && /[A-Za-z0-9_]/.test(expr[pos])) pos++;
                const name = expr.slice(start, pos);
                const v = parseFloat(vars[name]);
                return isNaN(v) ? 0 : v;
            }
            pos++;
            return 0;
        }

        try {
            const value = parseExpr();
            return isFinite(value) ? value : null;
        } catch (e) {
            return null;
        }
    }

    function currentValues() {
        const values = {};
        widget.querySelectorAll('.calc-input').forEach(input => {
            values[input.dataset.key] = input.value;
        });
        return values;
    }

    function recalc() {
        const value = evalFormula(formula, currentValues());
        resultEl.textContent = value === null ? '—' : Math.round(value * 100) / 100;
        return value;
    }

    widget.querySelectorAll('.calc-input').forEach(input => {
        input.addEventListener('input', recalc);
    });
    recalc();

    window.getWidgetState = function () {
        return JSON.stringify({ inputs: currentValues(), result: recalc() });
    };

    window.setWidgetState = function (content) {
        let data = {};
        try {
            data = JSON.parse(content || '{}');
        } catch (e) {
            return;
        }
        const values = data.inputs || {};
        widget.querySelectorAll('.calc-input').forEach(input => {
            if (values[input.dataset.key] !== undefined) input.value = values[input.dataset.key];
        });
        recalc();
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

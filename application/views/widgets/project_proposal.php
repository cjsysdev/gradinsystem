<?php
// Project Proposal — title/type/client/problem header fields + a repeatable
// features table where each planned feature is tagged with the CRUD
// operation it implements. Not auto-graded — same manual-score-entry
// pattern as Worksheet Form/Case Study Worksheet. Renders inline via the
// standard assessment_view_code.php flow (no special-case redirect).
//
// $config — [
//   'instructions'      => '...',                          // optional, shown above the form
//   'project_types'      => ['C Programming', 'Web'],        // optional, default shown below
//   'min_features'       => int,                             // optional, default 4
//   'require_all_crud'   => bool,                            // optional, advisory CRUD-coverage hint only, never blocks submit
// ]
// $readonly — bool
// $existing — [
//   'title' => '...', 'project_type' => '...', 'client' => '...', 'problem' => '...',
//   'features' => [ ['feature' => '...', 'crud' => 'Create'|'Read'|'Update'|'Delete'], ... ]
// ] or null

$readonly         = $readonly ?? false;
$existing         = $existing ?? [];
$project_types    = $config['project_types'] ?? ['C Programming', 'Web'];
$min_features     = max(1, (int) ($config['min_features'] ?? 4));
$require_all_crud = !empty($config['require_all_crud']);
$crud_options     = ['Create', 'Read', 'Update', 'Delete'];

$title        = $existing['title'] ?? '';
$project_type = $existing['project_type'] ?? '';
$client       = $existing['client'] ?? '';
$problem      = $existing['problem'] ?? '';
$features     = $existing['features'] ?? [];

if (!$readonly && empty($features)) {
    for ($i = 0; $i < $min_features; $i++) {
        $features[] = ['feature' => '', 'crud' => ''];
    }
}
?>
<style>
    #pp-widget { text-align: left; }
    #pp-widget .pp-instructions { background: #f6f5f1; border: 1px solid #e3e1da; border-left: 5px solid #357abd; border-radius: 6px; padding: 14px 16px; margin-bottom: 18px; font-size: 14px; }
    #pp-widget .pp-header-card { background: #fff; border: 1px solid #e3e1da; border-radius: 6px; padding: 16px 18px; margin-bottom: 18px; }
    #pp-widget .pp-header-card label { font-weight: 600; font-size: 13px; margin-bottom: 4px; }
    #pp-widget .pp-crud-hint { font-size: 12px; color: #6c757d; margin: 6px 0 12px; }
    #pp-widget .pp-crud-chip { display: inline-block; font-size: 11px; font-weight: bold; padding: 3px 9px; border-radius: 12px; margin-right: 6px; border: 1.5px solid #ced4da; color: #adb5bd; background: #fff; }
    #pp-widget .pp-crud-chip.covered { border-color: #2f7a4f; color: #fff; background: #2f7a4f; }
    #pp-widget .pp-answer { white-space: pre-wrap; background: #f6f5f1; border: 1px solid #e3e1da; border-radius: 4px; padding: 8px 10px; min-height: 20px; }
</style>
<div id="pp-widget">
    <?php if (!empty($config['instructions'])): ?>
        <div class="pp-instructions"><?= nl2br(htmlspecialchars($config['instructions'])) ?></div>
    <?php endif; ?>

    <div class="pp-header-card">
        <div class="form-group">
            <label>Project Title</label>
            <?php if ($readonly): ?>
                <div class="pp-answer"><?= $title !== '' ? htmlspecialchars($title) : '<span class="text-muted">No answer.</span>' ?></div>
            <?php else: ?>
                <input type="text" class="form-control pp-field-title" placeholder="e.g. Barangay Clearance Management System" value="<?= htmlspecialchars($title) ?>">
            <?php endif; ?>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label>Project Type</label>
                <?php if ($readonly): ?>
                    <div class="pp-answer"><?= $project_type !== '' ? htmlspecialchars($project_type) : '<span class="text-muted">No answer.</span>' ?></div>
                <?php else: ?>
                    <select class="form-control pp-field-type">
                        <option value="">-- Select --</option>
                        <?php foreach ($project_types as $type): ?>
                            <option value="<?= htmlspecialchars($type) ?>" <?= $type === $project_type ? 'selected' : '' ?>><?= htmlspecialchars($type) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
            <div class="form-group col-md-6">
                <label>Possible Client / User</label>
                <?php if ($readonly): ?>
                    <div class="pp-answer"><?= $client !== '' ? htmlspecialchars($client) : '<span class="text-muted">No answer.</span>' ?></div>
                <?php else: ?>
                    <input type="text" class="form-control pp-field-client" placeholder="e.g. Barangay secretary and residents" value="<?= htmlspecialchars($client) ?>">
                <?php endif; ?>
            </div>
        </div>

        <div class="form-group mb-0">
            <label>Problem It Solves</label>
            <?php if ($readonly): ?>
                <div class="pp-answer"><?= $problem !== '' ? nl2br(htmlspecialchars($problem)) : '<span class="text-muted">No answer.</span>' ?></div>
            <?php else: ?>
                <textarea class="form-control pp-field-problem" rows="2" placeholder="What problem does this system solve, and for whom?"><?= htmlspecialchars($problem) ?></textarea>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$readonly && $require_all_crud): ?>
        <div class="pp-crud-hint">CRUD coverage: <span id="pp-crud-chips"></span></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>Planned Feature / Function</th>
                    <th style="width:150px;">CRUD Operation</th>
                    <?php if (!$readonly): ?><th></th><?php endif; ?>
                </tr>
            </thead>
            <tbody id="pp-features-body">
                <?php foreach ($features as $f): ?>
                    <tr>
                        <?php if ($readonly): ?>
                            <td><?= !empty($f['feature']) ? htmlspecialchars($f['feature']) : '<span class="text-muted">&mdash;</span>' ?></td>
                            <td><?= !empty($f['crud']) ? '<span class="badge badge-primary">' . htmlspecialchars($f['crud']) . '</span>' : '<span class="text-muted">&mdash;</span>' ?></td>
                        <?php else: ?>
                            <td><input type="text" class="form-control form-control-sm pp-feature-text" placeholder="e.g. Add a new resident record" value="<?= htmlspecialchars($f['feature'] ?? '') ?>"></td>
                            <td>
                                <select class="form-control form-control-sm pp-crud-select">
                                    <option value="">-- CRUD --</option>
                                    <?php foreach ($crud_options as $c): ?>
                                        <option value="<?= $c ?>" <?= ($f['crud'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger pp-remove-row">&times;</button></td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                <?php if ($readonly && empty($features)): ?>
                    <tr><td colspan="2" class="text-muted text-center">No submission.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if (!$readonly): ?>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="pp-add-feature">
            <i class="fas fa-plus"></i> Add Feature
        </button>
    <?php endif; ?>
</div>

<?php if (!$readonly): ?>
<script>
(function () {
    const CRUD_OPTIONS = <?= json_encode($crud_options) ?>;
    const widget = document.getElementById('pp-widget');
    const body = document.getElementById('pp-features-body');
    const chipsEl = document.getElementById('pp-crud-chips');

    function bindRemove(btn) {
        btn.addEventListener('click', () => {
            btn.closest('tr').remove();
            updateCrudChips();
        });
    }

    function addFeatureRow(values) {
        values = values || { feature: '', crud: '' };
        const tr = document.createElement('tr');

        const tdFeature = document.createElement('td');
        const featureInput = document.createElement('input');
        featureInput.type = 'text';
        featureInput.className = 'form-control form-control-sm pp-feature-text';
        featureInput.placeholder = 'e.g. Add a new resident record';
        featureInput.value = values.feature || '';
        tdFeature.appendChild(featureInput);
        tr.appendChild(tdFeature);

        const tdCrud = document.createElement('td');
        const select = document.createElement('select');
        select.className = 'form-control form-control-sm pp-crud-select';
        const blankOpt = document.createElement('option');
        blankOpt.value = '';
        blankOpt.textContent = '-- CRUD --';
        select.appendChild(blankOpt);
        CRUD_OPTIONS.forEach(c => {
            const opt = document.createElement('option');
            opt.value = c;
            opt.textContent = c;
            if (values.crud === c) opt.selected = true;
            select.appendChild(opt);
        });
        select.addEventListener('change', updateCrudChips);
        tdCrud.appendChild(select);
        tr.appendChild(tdCrud);

        const tdBtn = document.createElement('td');
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-sm btn-outline-danger pp-remove-row';
        removeBtn.innerHTML = '&times;';
        bindRemove(removeBtn);
        tdBtn.appendChild(removeBtn);
        tr.appendChild(tdBtn);

        body.appendChild(tr);
    }

    body.querySelectorAll('.pp-remove-row').forEach(bindRemove);
    body.querySelectorAll('.pp-crud-select').forEach(sel => sel.addEventListener('change', updateCrudChips));

    const addBtn = document.getElementById('pp-add-feature');
    if (addBtn) addBtn.addEventListener('click', () => { addFeatureRow(); updateCrudChips(); });

    function updateCrudChips() {
        if (!chipsEl) return;
        const covered = new Set();
        body.querySelectorAll('.pp-crud-select').forEach(sel => { if (sel.value) covered.add(sel.value); });
        chipsEl.innerHTML = CRUD_OPTIONS.map(c =>
            '<span class="pp-crud-chip' + (covered.has(c) ? ' covered' : '') + '">' + c + (covered.has(c) ? ' ✓' : '') + '</span>'
        ).join('');
    }
    updateCrudChips();

    function serializeFeatures() {
        const rows = [];
        body.querySelectorAll('tr').forEach(tr => {
            const featureInput = tr.querySelector('.pp-feature-text');
            const select = tr.querySelector('.pp-crud-select');
            if (!featureInput) return;
            rows.push({ feature: featureInput.value, crud: select ? select.value : '' });
        });
        return rows;
    }

    window.getWidgetState = function () {
        return JSON.stringify({
            title: (widget.querySelector('.pp-field-title') || {}).value || '',
            project_type: (widget.querySelector('.pp-field-type') || {}).value || '',
            client: (widget.querySelector('.pp-field-client') || {}).value || '',
            problem: (widget.querySelector('.pp-field-problem') || {}).value || '',
            features: serializeFeatures()
        });
    };

    window.setWidgetState = function (content) {
        let data = {};
        try {
            data = JSON.parse(content || '{}');
        } catch (e) {
            return;
        }
        const titleEl = widget.querySelector('.pp-field-title');
        const typeEl = widget.querySelector('.pp-field-type');
        const clientEl = widget.querySelector('.pp-field-client');
        const problemEl = widget.querySelector('.pp-field-problem');
        if (titleEl) titleEl.value = data.title || '';
        if (typeEl) typeEl.value = data.project_type || '';
        if (clientEl) clientEl.value = data.client || '';
        if (problemEl) problemEl.value = data.problem || '';

        body.innerHTML = '';
        const features = Array.isArray(data.features) ? data.features : [];
        features.forEach(f => addFeatureRow(f));
        updateCrudChips();
    };

    window.isWidgetFocused = function () {
        return widget.contains(document.activeElement);
    };

    // Called by the host page right before it submits the form — serializes
    // this widget's state into the hidden #widget-code-value field so the
    // existing AssessmentController::submit_classwork() needs zero changes.
    window.serializeWidgetBeforeSubmit = function () {
        const codeField = document.getElementById('widget-code-value');
        if (codeField) codeField.value = window.getWidgetState();
    };
})();
</script>
<?php endif; ?>

<?php
// Widget K — Chapter Worksheet (root/docs/paperless-midterm-plan.md #4).
// Timed-move table (read-only) -> read-only "the model" worked-example
// callout -> a fixed sequence of typed steps (text / grid / choice /
// checklist) -> read-only "the trap" warning callout -> a peer-check
// question -> a fixed team/date/filed/peer-checked-by sign-off block.
// Built for the Feasibility Study Worksheet Pack (10x45min dossier-chapter
// worksheets, IS Innovations & New Technologies) but shaped to be reusable
// for any "read a worked model, do the steps, get peer-checked, file it"
// in-class worksheet. Not auto-graded — same manual-score-entry pattern as
// Worksheet Form/Lab Worksheet/Case Study/Case Dossier.
//
// $config — [
//   'meta'     => ['eyebrow' => '...', 'title' => '...', 'sub' => '...'],
//   'timeline' => ['label' => '...', 'moves' => [['time'=>'0-5','move'=>'...','detail'=>'...'], ...]],  // read-only
//   'model'    => ['label' => '...', 'html' => '<p>trusted HTML</p>'],                                    // read-only
//   'steps'    => [
//     ['type'=>'text',  'label'=>'...', 'instruction'=>'...', 'prefix'=>'...', 'rows'=>3, 'placeholder'=>'...'],
//     ['type'=>'grid',  'label'=>'...', 'instruction'=>'...', 'note'=>'...',
//        'columns'=>[['name'=>'...','type'=>'text|select|checkbox','options'=>[...]], ...],
//        'rows'=>[['label'=>'...','sub'=>'...'], ...]],
//     ['type'=>'choice','label'=>'...', 'instruction'=>'...', 'options'=>[['text'=>'...','note'=>'...'], ...]],
//     ['type'=>'checklist','label'=>'...', 'instruction'=>'...', 'items'=>['...', ...]],
//   ],
//   'trap'       => ['label' => '...', 'html' => '<p>trusted HTML</p>'],  // read-only
//   'peer_check' => ['label' => '...', 'instruction' => '...', 'task' => '...', 'rows' => 3],
//   'file_it'    => ['label' => '...', 'instruction' => '...'],
// ]
//
// $readonly — bool
// $existing — [
//   'steps'      => { '<step index>' => <value shaped per type, see below> },
//   'peer_check' => '...',
//   'file_it'    => ['team'=>'...', 'date'=>'...', 'filed'=>bool, 'peer_checked_by'=>'...'],
// ] or []
//
// Step value shapes:
//   text      -> string
//   grid      -> { '<row label>' => { '<col index>' => value } }  (mirrors widgets/decision_matrix.php)
//   choice    -> int|null (selected option index)
//   checklist -> [bool, ...] (parallel to 'items')

$readonly = $readonly ?? false;
$existing = $existing ?? [];
$meta       = $config['meta'] ?? [];
$timeline   = $config['timeline'] ?? [];
$model      = $config['model'] ?? [];
$steps      = $config['steps'] ?? [];
$trap       = $config['trap'] ?? [];
$peer_check = $config['peer_check'] ?? [];
$file_it    = $config['file_it'] ?? [];

$step_answers  = $existing['steps'] ?? [];
$peer_answer   = is_string($existing['peer_check'] ?? null) ? $existing['peer_check'] : '';
$file_it_ans   = $existing['file_it'] ?? [];
?>
<style>
    #chapter-worksheet-widget { text-align: left; }
    #chapter-worksheet-widget .cw-progress { font-size: 13px; color: #6c757d; margin-bottom: 14px; }
    #chapter-worksheet-widget .cw-bar { height: 6px; background: #e8e6df; border-radius: 3px; overflow: hidden; margin-top: 5px; }
    #chapter-worksheet-widget .cw-bar i { display: block; height: 100%; width: 0; background: #357abd; transition: width .25s; }
    #chapter-worksheet-widget .cw-meta { background: #f6f5f1; border: 1px solid #e3e1da; border-left: 5px solid #357abd; border-radius: 6px; padding: 16px 18px; margin-bottom: 18px; }
    #chapter-worksheet-widget .cw-eyebrow { font-size: 11px; font-weight: bold; letter-spacing: .08em; text-transform: uppercase; color: #357abd; margin-bottom: 4px; }
    #chapter-worksheet-widget .cw-meta h4 { margin: 0 0 4px; }
    #chapter-worksheet-widget .cw-meta .cw-sub { font-size: 13px; color: #6c757d; }
    #chapter-worksheet-widget .cw-section { background: #fff; border: 1px solid #e3e1da; border-radius: 6px; padding: 16px 18px; margin-bottom: 16px; }
    #chapter-worksheet-widget .cw-section-head h5 { margin: 0; display: inline-block; }
    #chapter-worksheet-widget .cw-timing { font-size: 11px; color: #6c757d; margin-left: 8px; }
    #chapter-worksheet-widget .cw-instruction { font-size: 13px; color: #6c757d; margin: 4px 0 10px; }
    #chapter-worksheet-widget .cw-note { font-size: 12px; color: #6c757d; margin-top: 8px; font-style: italic; }
    #chapter-worksheet-widget table.cw-timeline-table th, #chapter-worksheet-widget table.cw-timeline-table td { font-size: 13px; vertical-align: middle; }
    #chapter-worksheet-widget table.cw-timeline-table thead th { background: #343a40; color: #fff; }
    #chapter-worksheet-widget .cw-callout { border-radius: 6px; padding: 14px 16px; margin-bottom: 16px; }
    #chapter-worksheet-widget .cw-callout-model { background: rgba(53,122,189,0.07); border: 1px solid #357abd; border-left: 5px solid #357abd; }
    #chapter-worksheet-widget .cw-callout-model .cw-callout-label { color: #357abd; }
    #chapter-worksheet-widget .cw-callout-trap { background: rgba(232,148,44,0.08); border: 1px solid #e8942c; border-left: 5px solid #e8942c; }
    #chapter-worksheet-widget .cw-callout-trap .cw-callout-label { color: #c9760f; }
    #chapter-worksheet-widget .cw-callout-label { font-size: 11px; font-weight: bold; letter-spacing: .06em; text-transform: uppercase; margin-bottom: 6px; }
    #chapter-worksheet-widget .cw-callout p:last-child { margin-bottom: 0; }
    #chapter-worksheet-widget .cw-answer { white-space: pre-wrap; background: #f6f5f1; border: 1px solid #e3e1da; border-radius: 4px; padding: 8px 10px; min-height: 20px; }
    #chapter-worksheet-widget .cw-prefix { font-size: 12px; color: #6c757d; margin-bottom: 4px; }
    #chapter-worksheet-widget .cw-choices { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 4px; }
    #chapter-worksheet-widget .cw-choice-btn { display: inline-block; font-size: 13px; padding: 7px 12px; border: 1.5px solid #357abd; border-radius: 6px; background: transparent; color: #357abd; cursor: pointer; }
    #chapter-worksheet-widget .cw-choice-btn.picked { background: #357abd; color: #fff; }
    #chapter-worksheet-widget .cw-choice-note { display: none; margin-top: 8px; padding: 8px 10px; border-radius: 6px; background: #eef4fb; border-left: 3px solid #357abd; font-size: 13px; }
    #chapter-worksheet-widget .cw-choice-note.show { display: block; }
    #chapter-worksheet-widget .cw-checklist label { display: block; font-size: 13px; margin-bottom: 6px; }
    #chapter-worksheet-widget .cw-fileit-row { display: flex; flex-wrap: wrap; gap: 14px; align-items: flex-end; }
    #chapter-worksheet-widget .cw-fileit-field { flex: 1 1 180px; }
    #chapter-worksheet-widget .cw-fileit-field label { font-size: 12px; color: #6c757d; margin-bottom: 2px; display: block; }
    #chapter-worksheet-widget .cw-fileit-check { flex: 0 0 auto; font-size: 13px; padding-bottom: 6px; }
</style>
<div id="chapter-worksheet-widget">
    <?php if (!empty($meta)): ?>
        <div class="cw-meta">
            <?php if (!empty($meta['eyebrow'])): ?><div class="cw-eyebrow"><?= htmlspecialchars($meta['eyebrow']) ?></div><?php endif; ?>
            <?php if (!empty($meta['title'])): ?><h4><?= htmlspecialchars($meta['title']) ?></h4><?php endif; ?>
            <?php if (!empty($meta['sub'])): ?><div class="cw-sub"><?= htmlspecialchars($meta['sub']) ?></div><?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!$readonly): ?>
        <div class="cw-progress">
            Progress: <span id="cw-progress-count">0</span>/<span id="cw-progress-total">0</span> answered
            <div class="cw-bar"><i id="cw-progress-bar"></i></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($timeline['moves'])): ?>
        <div class="cw-section">
            <div class="cw-section-head"><h5><?= htmlspecialchars($timeline['label'] ?? 'How this session runs') ?></h5></div>
            <div class="table-responsive mt-2">
                <table class="table table-bordered table-sm cw-timeline-table mb-0">
                    <thead><tr><th style="width:90px">Time</th><th style="width:160px">Move</th><th>What you do</th></tr></thead>
                    <tbody>
                        <?php foreach ($timeline['moves'] as $mv): ?>
                            <tr>
                                <td><?= htmlspecialchars($mv['time'] ?? '') ?></td>
                                <td><strong><?= htmlspecialchars($mv['move'] ?? '') ?></strong></td>
                                <td><?= htmlspecialchars($mv['detail'] ?? '') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($model)): ?>
        <div class="cw-callout cw-callout-model">
            <?php if (!empty($model['label'])): ?><div class="cw-callout-label">◆ <?= htmlspecialchars($model['label']) ?></div><?php endif; ?>
            <?= $model['html'] ?? '' ?>
        </div>
    <?php endif; ?>

    <?php foreach ($steps as $si => $step):
        $stype = $step['type'] ?? 'text';
        $answer = $step_answers[$si] ?? null;
    ?>
        <div class="cw-section" data-step="<?= $si ?>" data-type="<?= htmlspecialchars($stype) ?>">
            <div class="cw-section-head"><h5><?= htmlspecialchars($step['label'] ?? '') ?></h5></div>
            <?php if (!empty($step['instruction'])): ?><div class="cw-instruction"><?= htmlspecialchars($step['instruction']) ?></div><?php endif; ?>

            <?php if ($stype === 'text'): ?>
                <?php $text_val = is_string($answer) ? $answer : ''; ?>
                <?php if (!empty($step['prefix'])): ?><div class="cw-prefix"><?= htmlspecialchars($step['prefix']) ?></div><?php endif; ?>
                <?php if ($readonly): ?>
                    <div class="cw-answer"><?= $text_val !== '' ? nl2br(htmlspecialchars($text_val)) : '<span class="text-muted">No answer.</span>' ?></div>
                <?php else: ?>
                    <textarea class="form-control form-control-sm cw-field-text" rows="<?= (int) ($step['rows'] ?? 3) ?>" placeholder="<?= htmlspecialchars($step['placeholder'] ?? '') ?>"><?= htmlspecialchars($text_val) ?></textarea>
                <?php endif; ?>

            <?php elseif ($stype === 'grid'):
                $columns = $step['columns'] ?? [];
                $grid_rows = $step['rows'] ?? [];
                $grid_val = is_array($answer) ? $answer : [];
            ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <thead><tr><th></th><?php foreach ($columns as $col): ?><th><?= htmlspecialchars($col['name'] ?? '') ?></th><?php endforeach; ?></tr></thead>
                        <tbody>
                            <?php foreach ($grid_rows as $row):
                                $row_label = $row['label'] ?? '';
                                $row_vals = $grid_val[$row_label] ?? [];
                            ?>
                                <tr data-row="<?= htmlspecialchars($row_label) ?>">
                                    <th class="align-middle">
                                        <?= htmlspecialchars($row_label) ?>
                                        <?php if (!empty($row['sub'])): ?><div class="text-muted" style="font-size:11px;font-weight:normal;"><?= htmlspecialchars($row['sub']) ?></div><?php endif; ?>
                                    </th>
                                    <?php foreach ($columns as $ci => $col):
                                        $ctype = $col['type'] ?? 'text';
                                        $cval = $row_vals[$ci] ?? '';
                                    ?>
                                        <td data-col="<?= $ci ?>">
                                            <?php if ($readonly): ?>
                                                <?php if ($ctype === 'checkbox'): ?>
                                                    <?= !empty($cval) ? '&#9745;' : '&#9744;' ?>
                                                <?php else: ?>
                                                    <?= $cval !== '' ? nl2br(htmlspecialchars((string) $cval)) : '&mdash;' ?>
                                                <?php endif; ?>
                                            <?php elseif ($ctype === 'select'): ?>
                                                <select class="form-control form-control-sm cw-cell">
                                                    <option value="">&mdash;</option>
                                                    <?php foreach (($col['options'] ?? []) as $opt): ?>
                                                        <option value="<?= htmlspecialchars($opt) ?>" <?= ((string) $cval === (string) $opt) ? 'selected' : '' ?>><?= htmlspecialchars($opt) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            <?php elseif ($ctype === 'checkbox'): ?>
                                                <input type="checkbox" class="cw-cell" <?= !empty($cval) ? 'checked' : '' ?>>
                                            <?php else: ?>
                                                <input type="text" class="form-control form-control-sm cw-cell" value="<?= htmlspecialchars((string) $cval) ?>">
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (!empty($step['note'])): ?><div class="cw-note"><?= htmlspecialchars($step['note']) ?></div><?php endif; ?>

            <?php elseif ($stype === 'choice'):
                $options = $step['options'] ?? [];
                $selected = is_numeric($answer) ? (int) $answer : null;
            ?>
                <?php if ($readonly): ?>
                    <?php if ($selected !== null && isset($options[$selected])): ?>
                        <div class="cw-answer"><?= htmlspecialchars($options[$selected]['text'] ?? '') ?></div>
                        <?php if (!empty($options[$selected]['note'])): ?><div class="cw-choice-note show"><?= htmlspecialchars($options[$selected]['note']) ?></div><?php endif; ?>
                    <?php else: ?>
                        <div class="cw-answer"><span class="text-muted">No answer.</span></div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="cw-choices">
                        <?php foreach ($options as $oi => $opt): ?>
                            <button type="button" class="cw-choice-btn<?= $selected === $oi ? ' picked' : '' ?>" data-opt="<?= $oi ?>" data-note="<?= htmlspecialchars($opt['note'] ?? '') ?>"><?= htmlspecialchars($opt['text'] ?? '') ?></button>
                        <?php endforeach; ?>
                    </div>
                    <div class="cw-choice-note<?= $selected !== null && !empty($options[$selected]['note']) ? ' show' : '' ?>"><?= $selected !== null ? htmlspecialchars($options[$selected]['note'] ?? '') : '' ?></div>
                <?php endif; ?>

            <?php elseif ($stype === 'checklist'):
                $items = $step['items'] ?? [];
                $check_val = is_array($answer) ? $answer : [];
            ?>
                <div class="cw-checklist">
                    <?php foreach ($items as $ii => $item):
                        $checked = !empty($check_val[$ii]);
                    ?>
                        <label>
                            <?php if ($readonly): ?>
                                <?= $checked ? '&#9745;' : '&#9744;' ?> <?= htmlspecialchars($item) ?>
                            <?php else: ?>
                                <input type="checkbox" class="cw-check-item" data-item="<?= $ii ?>" <?= $checked ? 'checked' : '' ?>> <?= htmlspecialchars($item) ?>
                            <?php endif; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <?php if (!empty($trap)): ?>
        <div class="cw-callout cw-callout-trap">
            <?php if (!empty($trap['label'])): ?><div class="cw-callout-label">&#9888; <?= htmlspecialchars($trap['label']) ?></div><?php endif; ?>
            <?= $trap['html'] ?? '' ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($peer_check)): ?>
        <div class="cw-section" id="cw-peer-check">
            <div class="cw-section-head"><h5><?= htmlspecialchars($peer_check['label'] ?? 'Peer Check') ?></h5></div>
            <?php if (!empty($peer_check['instruction'])): ?><div class="cw-instruction"><?= htmlspecialchars($peer_check['instruction']) ?></div><?php endif; ?>
            <?php if (!empty($peer_check['task'])): ?><p><?= htmlspecialchars($peer_check['task']) ?></p><?php endif; ?>
            <?php if ($readonly): ?>
                <div class="cw-answer"><?= $peer_answer !== '' ? nl2br(htmlspecialchars($peer_answer)) : '<span class="text-muted">No answer.</span>' ?></div>
            <?php else: ?>
                <textarea class="form-control form-control-sm" id="cw-peer-check-field" rows="<?= (int) ($peer_check['rows'] ?? 3) ?>"><?= htmlspecialchars($peer_answer) ?></textarea>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($file_it)): ?>
        <div class="cw-section" id="cw-file-it">
            <div class="cw-section-head"><h5><?= htmlspecialchars($file_it['label'] ?? 'File It') ?></h5></div>
            <?php if (!empty($file_it['instruction'])): ?><div class="cw-instruction"><?= htmlspecialchars($file_it['instruction']) ?></div><?php endif; ?>
            <?php if ($readonly): ?>
                <div class="cw-answer">
                    Team: <?= htmlspecialchars($file_it_ans['team'] ?? '') ?: '&mdash;' ?><br>
                    Date: <?= htmlspecialchars($file_it_ans['date'] ?? '') ?: '&mdash;' ?><br>
                    Chapter filed: <?= !empty($file_it_ans['filed']) ? '&#9745;' : '&#9744;' ?><br>
                    Peer-checked by: <?= htmlspecialchars($file_it_ans['peer_checked_by'] ?? '') ?: '&mdash;' ?>
                </div>
            <?php else: ?>
                <div class="cw-fileit-row">
                    <div class="cw-fileit-field">
                        <label for="cw-fi-team">Team</label>
                        <input type="text" id="cw-fi-team" class="form-control form-control-sm" value="<?= htmlspecialchars($file_it_ans['team'] ?? '') ?>">
                    </div>
                    <div class="cw-fileit-field" style="flex-basis:140px;">
                        <label for="cw-fi-date">Date</label>
                        <input type="date" id="cw-fi-date" class="form-control form-control-sm" value="<?= htmlspecialchars($file_it_ans['date'] ?? '') ?>">
                    </div>
                    <div class="cw-fileit-field">
                        <label for="cw-fi-peer">Peer-checked by</label>
                        <input type="text" id="cw-fi-peer" class="form-control form-control-sm" value="<?= htmlspecialchars($file_it_ans['peer_checked_by'] ?? '') ?>">
                    </div>
                    <div class="cw-fileit-check">
                        <label class="mb-0"><input type="checkbox" id="cw-fi-filed" <?= !empty($file_it_ans['filed']) ? 'checked' : '' ?>> Chapter filed</label>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($readonly && empty($steps) && empty($peer_check) && empty($file_it)): ?>
        <p class="text-muted text-center">No submission.</p>
    <?php endif; ?>
</div>

<?php if (!$readonly): ?>
<script>
(function () {
    const widget = document.getElementById('chapter-worksheet-widget');
    const progressCount = document.getElementById('cw-progress-count');
    const progressTotal = document.getElementById('cw-progress-total');
    const progressBar   = document.getElementById('cw-progress-bar');

    function pickChoice(sectionEl, optIndex) {
        sectionEl.querySelectorAll('.cw-choice-btn').forEach(b => b.classList.remove('picked'));
        const noteEl = sectionEl.querySelector('.cw-choice-note');
        if (optIndex === null) {
            if (noteEl) { noteEl.textContent = ''; noteEl.classList.remove('show'); }
            return;
        }
        const btn = sectionEl.querySelector(`.cw-choice-btn[data-opt="${optIndex}"]`);
        if (btn) btn.classList.add('picked');
        if (noteEl) {
            const note = btn ? btn.dataset.note : '';
            noteEl.textContent = note || '';
            noteEl.classList.toggle('show', !!note);
        }
    }

    widget.querySelectorAll('.cw-section[data-type="choice"]').forEach(sectionEl => {
        sectionEl.querySelectorAll('.cw-choice-btn').forEach(btn => {
            btn.addEventListener('click', () => { pickChoice(sectionEl, parseInt(btn.dataset.opt, 10)); updateProgress(); });
        });
    });

    function updateProgress() {
        if (!progressCount) return;
        let done = 0, total = 0;

        widget.querySelectorAll('.cw-section[data-step]').forEach(sectionEl => {
            const type = sectionEl.dataset.type;
            if (type === 'text') {
                total++;
                const ta = sectionEl.querySelector('.cw-field-text');
                if (ta && ta.value.trim() !== '') done++;
            } else if (type === 'grid') {
                sectionEl.querySelectorAll('tr[data-row]').forEach(tr => {
                    total++;
                    const filled = [...tr.querySelectorAll('.cw-cell')].some(c => c.type === 'checkbox' ? c.checked : c.value.trim() !== '');
                    if (filled) done++;
                });
            } else if (type === 'choice') {
                total++;
                if (sectionEl.querySelector('.cw-choice-btn.picked')) done++;
            } else if (type === 'checklist') {
                total++;
                if ([...sectionEl.querySelectorAll('.cw-check-item')].some(c => c.checked)) done++;
            }
        });

        const peerField = document.getElementById('cw-peer-check-field');
        if (peerField) { total++; if (peerField.value.trim() !== '') done++; }

        progressCount.textContent = done;
        progressTotal.textContent = total;
        progressBar.style.width = (total ? (done / total * 100) : 0) + '%';
    }

    function serializeSteps() {
        const steps = {};
        widget.querySelectorAll('.cw-section[data-step]').forEach(sectionEl => {
            const si = sectionEl.dataset.step;
            const type = sectionEl.dataset.type;
            if (type === 'text') {
                const ta = sectionEl.querySelector('.cw-field-text');
                steps[si] = ta ? ta.value : '';
            } else if (type === 'grid') {
                const grid = {};
                sectionEl.querySelectorAll('tr[data-row]').forEach(tr => {
                    const rowVals = {};
                    tr.querySelectorAll('td[data-col]').forEach(td => {
                        const cell = td.querySelector('.cw-cell');
                        if (cell) rowVals[td.dataset.col] = cell.type === 'checkbox' ? cell.checked : cell.value;
                    });
                    grid[tr.dataset.row] = rowVals;
                });
                steps[si] = grid;
            } else if (type === 'choice') {
                const picked = sectionEl.querySelector('.cw-choice-btn.picked');
                steps[si] = picked ? parseInt(picked.dataset.opt, 10) : null;
            } else if (type === 'checklist') {
                steps[si] = [...sectionEl.querySelectorAll('.cw-check-item')].map(c => c.checked);
            }
        });
        return steps;
    }

    window.getWidgetState = function () {
        const peerField = document.getElementById('cw-peer-check-field');
        const fiTeam  = document.getElementById('cw-fi-team');
        const fiDate  = document.getElementById('cw-fi-date');
        const fiPeer  = document.getElementById('cw-fi-peer');
        const fiFiled = document.getElementById('cw-fi-filed');
        return JSON.stringify({
            steps: serializeSteps(),
            peer_check: peerField ? peerField.value : '',
            file_it: {
                team: fiTeam ? fiTeam.value : '',
                date: fiDate ? fiDate.value : '',
                filed: fiFiled ? fiFiled.checked : false,
                peer_checked_by: fiPeer ? fiPeer.value : ''
            }
        });
    };

    window.setWidgetState = function (content) {
        let data = {};
        try {
            data = JSON.parse(content || '{}');
        } catch (e) {
            return;
        }
        const steps = data.steps || {};

        widget.querySelectorAll('.cw-section[data-step]').forEach(sectionEl => {
            const si = sectionEl.dataset.step;
            const type = sectionEl.dataset.type;
            const val = steps[si];
            if (val === undefined) return;

            if (type === 'text') {
                const ta = sectionEl.querySelector('.cw-field-text');
                if (ta) ta.value = val || '';
            } else if (type === 'grid') {
                sectionEl.querySelectorAll('tr[data-row]').forEach(tr => {
                    const rowVals = (val || {})[tr.dataset.row] || {};
                    tr.querySelectorAll('td[data-col]').forEach(td => {
                        const cell = td.querySelector('.cw-cell');
                        if (cell && rowVals[td.dataset.col] !== undefined) {
                            if (cell.type === 'checkbox') cell.checked = !!rowVals[td.dataset.col];
                            else cell.value = rowVals[td.dataset.col];
                        }
                    });
                });
            } else if (type === 'choice') {
                pickChoice(sectionEl, (val === null || val === undefined) ? null : parseInt(val, 10));
            } else if (type === 'checklist') {
                sectionEl.querySelectorAll('.cw-check-item').forEach((c, i) => { c.checked = !!(val || [])[i]; });
            }
        });

        const peerField = document.getElementById('cw-peer-check-field');
        if (peerField && data.peer_check !== undefined) peerField.value = data.peer_check || '';

        const fi = data.file_it || {};
        const fiTeam  = document.getElementById('cw-fi-team');
        const fiDate  = document.getElementById('cw-fi-date');
        const fiPeer  = document.getElementById('cw-fi-peer');
        const fiFiled = document.getElementById('cw-fi-filed');
        if (fiTeam) fiTeam.value = fi.team || '';
        if (fiDate) fiDate.value = fi.date || '';
        if (fiPeer) fiPeer.value = fi.peer_checked_by || '';
        if (fiFiled) fiFiled.checked = !!fi.filed;

        updateProgress();
    };

    window.isWidgetFocused = function () {
        return widget.contains(document.activeElement);
    };

    window.serializeWidgetBeforeSubmit = function () {
        const codeField = document.getElementById('widget-code-value');
        if (codeField) codeField.value = window.getWidgetState();
    };

    widget.addEventListener('input', updateProgress);
    widget.addEventListener('change', updateProgress);
    updateProgress();
})();
</script>
<?php endif; ?>

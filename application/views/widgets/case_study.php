<?php
// Widget I — Case Study Worksheet (root/docs/paperless-midterm-plan.md #4).
// Narrative "story" panel (stat cards) + a fixed sequence of sections, each
// holding heterogeneous questions — for case-study-driven hands-on activities
// (e.g. "Meet Maria the calamansi farmer") that don't fit Worksheet Form's
// repeatable-row table or Lab Worksheet's code-experiment shape. Not
// auto-graded — same manual-score-entry pattern as Worksheet Form/Lab Worksheet.
//
// $config — [
//   'story'    => ['eyebrow' => '...', 'title' => '...', 'intro' => '<p>trusted admin HTML</p>', 'stats' => [['label' => '...', 'text' => '...'], ...]],
//   'sections' => [
//     [
//       'label'     => 'Meet Maria',
//       'timing'    => '3–15 min · Problem Intro',   // optional
//       'questions' => [
//         ['type' => 'text',        'badge' => 'core', 'prompt' => '...', 'rows' => 2, 'placeholder' => '...'],
//         ['type' => 'list',        'badge' => 'core', 'prompt' => '...', 'lines' => 3, 'placeholders' => ['1. ...', '2. ...', '3. ...']],
//         ['type' => 'choice',      'badge' => 'core', 'prompt' => '...', 'options' => [['text' => '...', 'note' => '...'], ...]],
//         ['type' => 'toggle_grid', 'badge' => 'bonus','prompt' => '...', 'items' => [['title' => '...', 'text' => '...'], ...]],
//       ],
//     ],
//     ...
//   ],
// ]
// $readonly — bool
// $existing — ['answers' => { '<flat question index>' => <value shaped per type> }] or null
//   text -> string, list -> string[], choice -> selected option index (int) or null, toggle_grid -> int[] (toggled-on item indices)

$readonly = $readonly ?? false;
$existing = $existing ?? [];
$story    = $config['story'] ?? [];
$sections = $config['sections'] ?? [];
$answers  = $existing['answers'] ?? [];

$total_questions = 0;
foreach ($sections as $s) {
    $total_questions += count($s['questions'] ?? []);
}
?>
<style>
    #case-study-widget { text-align: left; }
    #case-study-widget .cs-progress { font-size: 13px; color: #6c757d; margin-bottom: 14px; }
    #case-study-widget .cs-bar { height: 6px; background: #e8e6df; border-radius: 3px; overflow: hidden; margin-top: 5px; }
    #case-study-widget .cs-bar i { display: block; height: 100%; width: 0; background: #357abd; transition: width .25s; }
    #case-study-widget .cs-story { background: #f6f5f1; border: 1px solid #e3e1da; border-left: 5px solid #357abd; border-radius: 6px; padding: 16px 18px; margin-bottom: 18px; }
    #case-study-widget .cs-eyebrow { font-size: 11px; font-weight: bold; letter-spacing: .08em; text-transform: uppercase; color: #357abd; margin-bottom: 4px; }
    #case-study-widget .cs-story h4 { margin: 0 0 10px; }
    #case-study-widget .cs-stats { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 12px; }
    #case-study-widget .cs-stat { flex: 1 1 200px; background: #fdf3e4; border: 1px solid #f0ddb8; border-radius: 8px; padding: 8px 12px; font-size: 13px; }
    #case-study-widget .cs-stat b { display: block; font-size: 11px; color: #8a4b0f; margin-bottom: 2px; }
    #case-study-widget .cs-section { background: #fff; border: 1px solid #e3e1da; border-radius: 6px; padding: 16px 18px; margin-bottom: 16px; }
    #case-study-widget .cs-section-head { margin-bottom: 10px; }
    #case-study-widget .cs-section-head h5 { margin: 0; display: inline-block; }
    #case-study-widget .cs-timing { font-size: 11px; color: #6c757d; margin-left: 8px; }
    #case-study-widget .cs-q { margin-bottom: 16px; padding-bottom: 14px; border-bottom: 1px solid #eee; }
    #case-study-widget .cs-q:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
    #case-study-widget .cs-q-head { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; }
    #case-study-widget .cs-prompt { font-weight: 500; margin: 0 0 8px; }
    #case-study-widget .cs-list-line { margin-bottom: 6px; }
    #case-study-widget .cs-choice-btn { display: inline-block; font-size: 13px; padding: 7px 12px; margin: 0 6px 6px 0; border: 1.5px solid #357abd; border-radius: 6px; background: transparent; color: #357abd; cursor: pointer; }
    #case-study-widget .cs-choice-btn.picked { background: #357abd; color: #fff; }
    #case-study-widget .cs-note { display: none; margin-top: 8px; padding: 8px 10px; border-radius: 6px; background: #eef4fb; border-left: 3px solid #357abd; font-size: 13px; }
    #case-study-widget .cs-note.show { display: block; }
    #case-study-widget .cs-toggle-grid { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 6px; }
    #case-study-widget .cs-toggle-item { flex: 1 1 150px; border: 1.5px solid #e3e1da; border-radius: 8px; padding: 10px 12px; cursor: pointer; background: #fdfcf9; }
    #case-study-widget .cs-toggle-item b { display: block; font-size: 12px; color: #357abd; }
    #case-study-widget .cs-toggle-item.on { background: #eaf5ee; border-color: #2f7a4f; }
    #case-study-widget .cs-toggle-item.on b { color: #2f7a4f; }
    #case-study-widget .cs-toggle-status { font-size: 11px; color: #6c757d; margin-top: 4px; }
    #case-study-widget .cs-answer { white-space: pre-wrap; background: #f6f5f1; border: 1px solid #e3e1da; border-radius: 4px; padding: 8px 10px; min-height: 20px; }
</style>
<div id="case-study-widget">
    <?php if (!empty($story)): ?>
        <div class="cs-story">
            <?php if (!empty($story['eyebrow'])): ?><div class="cs-eyebrow"><?= htmlspecialchars($story['eyebrow']) ?></div><?php endif; ?>
            <?php if (!empty($story['title'])): ?><h4><?= htmlspecialchars($story['title']) ?></h4><?php endif; ?>
            <?php if (!empty($story['intro'])): ?><div class="cs-intro"><?= $story['intro'] ?></div><?php endif; ?>
            <?php if (!empty($story['stats'])): ?>
                <div class="cs-stats">
                    <?php foreach ($story['stats'] as $stat): ?>
                        <div class="cs-stat">
                            <b><?= htmlspecialchars($stat['label'] ?? '') ?></b>
                            <?= htmlspecialchars($stat['text'] ?? '') ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!$readonly): ?>
        <div class="cs-progress">
            Progress: <span id="cs-progress-count">0</span>/<?= $total_questions ?> answered
            <div class="cs-bar"><i id="cs-progress-bar"></i></div>
        </div>
    <?php endif; ?>

    <?php $flat_idx = 0; ?>
    <?php foreach ($sections as $section): ?>
        <div class="cs-section">
            <div class="cs-section-head">
                <h5><?= htmlspecialchars($section['label'] ?? '') ?></h5>
                <?php if (!empty($section['timing'])): ?><span class="cs-timing"><?= htmlspecialchars($section['timing']) ?></span><?php endif; ?>
            </div>

            <?php foreach ($section['questions'] ?? [] as $q): ?>
                <?php
                $idx    = $flat_idx++;
                $type   = $q['type'] ?? 'text';
                $badge  = $q['badge'] ?? 'core';
                $value  = $answers[$idx] ?? null;
                ?>
                <div class="cs-q" data-idx="<?= $idx ?>" data-type="<?= htmlspecialchars($type) ?>">
                    <div class="cs-q-head">
                        <span class="badge <?= $badge === 'bonus' ? 'badge-warning' : 'badge-primary' ?>"><?= htmlspecialchars(strtoupper($badge)) ?></span>
                    </div>
                    <p class="cs-prompt"><?= htmlspecialchars($q['prompt'] ?? '') ?></p>

                    <?php if ($type === 'text'): ?>
                        <?php $text_val = is_string($value) ? $value : ''; ?>
                        <?php if ($readonly): ?>
                            <div class="cs-answer"><?= $text_val !== '' ? nl2br(htmlspecialchars($text_val)) : '<span class="text-muted">No answer.</span>' ?></div>
                        <?php else: ?>
                            <textarea class="form-control form-control-sm cs-field-text" rows="<?= (int) ($q['rows'] ?? 2) ?>" placeholder="<?= htmlspecialchars($q['placeholder'] ?? '') ?>"><?= htmlspecialchars($text_val) ?></textarea>
                        <?php endif; ?>

                    <?php elseif ($type === 'list'): ?>
                        <?php
                        $lines        = max(1, (int) ($q['lines'] ?? 1));
                        $placeholders = $q['placeholders'] ?? [];
                        $list_val     = is_array($value) ? array_values($value) : [];
                        ?>
                        <?php if ($readonly): ?>
                            <?php $filled = array_filter($list_val, function ($v) { return trim((string) $v) !== ''; }); ?>
                            <?php if (empty($filled)): ?>
                                <div class="cs-answer"><span class="text-muted">No answer.</span></div>
                            <?php else: ?>
                                <ul class="mb-0">
                                    <?php foreach ($list_val as $line): ?>
                                        <?php if (trim((string) $line) !== ''): ?><li><?= htmlspecialchars($line) ?></li><?php endif; ?>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php for ($li = 0; $li < $lines; $li++): ?>
                                <input type="text" class="form-control form-control-sm cs-list-line cs-field-list-item" data-line="<?= $li ?>"
                                       placeholder="<?= htmlspecialchars($placeholders[$li] ?? '') ?>"
                                       value="<?= htmlspecialchars($list_val[$li] ?? '') ?>">
                            <?php endfor; ?>
                        <?php endif; ?>

                    <?php elseif ($type === 'choice'): ?>
                        <?php
                        $options  = $q['options'] ?? [];
                        $selected = is_numeric($value) ? (int) $value : null;
                        ?>
                        <?php if ($readonly): ?>
                            <?php if ($selected !== null && isset($options[$selected])): ?>
                                <div class="cs-answer"><?= htmlspecialchars($options[$selected]['text'] ?? '') ?></div>
                                <?php if (!empty($options[$selected]['note'])): ?>
                                    <div class="cs-note show"><?= htmlspecialchars($options[$selected]['note']) ?></div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="cs-answer"><span class="text-muted">No answer.</span></div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="cs-choices">
                                <?php foreach ($options as $oi => $opt): ?>
                                    <button type="button" class="cs-choice-btn<?= $selected === $oi ? ' picked' : '' ?>"
                                            data-opt="<?= $oi ?>" data-note="<?= htmlspecialchars($opt['note'] ?? '') ?>">
                                        <?= htmlspecialchars($opt['text'] ?? '') ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                            <div class="cs-note<?= $selected !== null && !empty($options[$selected]['note']) ? ' show' : '' ?>">
                                <?= $selected !== null ? htmlspecialchars($options[$selected]['note'] ?? '') : '' ?>
                            </div>
                        <?php endif; ?>

                    <?php elseif ($type === 'toggle_grid'): ?>
                        <?php
                        $items   = $q['items'] ?? [];
                        $on_list = is_array($value) ? array_map('intval', $value) : [];
                        ?>
                        <?php if ($readonly): ?>
                            <?php $on_titles = []; foreach ($on_list as $oi) { if (isset($items[$oi])) $on_titles[] = $items[$oi]['title'] ?? ''; } ?>
                            <div class="cs-answer">
                                <?= !empty($on_titles) ? 'Marked as strengths: ' . htmlspecialchars(implode(', ', $on_titles)) : '<span class="text-muted">No answer.</span>' ?>
                            </div>
                        <?php else: ?>
                            <div class="cs-toggle-grid">
                                <?php foreach ($items as $ii => $item): ?>
                                    <?php $is_on = in_array($ii, $on_list, true); ?>
                                    <div class="cs-toggle-item<?= $is_on ? ' on' : '' ?>" data-item="<?= $ii ?>">
                                        <b><?= htmlspecialchars($item['title'] ?? '') ?></b>
                                        <div><?= htmlspecialchars($item['text'] ?? '') ?></div>
                                        <div class="cs-toggle-status"><?= $is_on ? 'Tapped — marked strong' : 'Tap if this feels weak/risky' ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <?php if ($readonly && empty($sections)): ?>
        <p class="text-muted text-center">No submission.</p>
    <?php endif; ?>
</div>

<?php if (!$readonly): ?>
<script>
(function () {
    const widget = document.getElementById('case-study-widget');
    const progressCount = document.getElementById('cs-progress-count');
    const progressBar   = document.getElementById('cs-progress-bar');

    function pickChoice(qEl, optIndex) {
        qEl.querySelectorAll('.cs-choice-btn').forEach(b => b.classList.remove('picked'));
        const noteEl = qEl.querySelector('.cs-note');
        if (optIndex === null) {
            if (noteEl) { noteEl.textContent = ''; noteEl.classList.remove('show'); }
            return;
        }
        const btn = qEl.querySelector(`.cs-choice-btn[data-opt="${optIndex}"]`);
        if (btn) btn.classList.add('picked');
        if (noteEl) {
            const note = btn ? btn.dataset.note : '';
            noteEl.textContent = note || '';
            noteEl.classList.toggle('show', !!note);
        }
    }

    widget.querySelectorAll('.cs-q[data-type="choice"]').forEach(qEl => {
        qEl.querySelectorAll('.cs-choice-btn').forEach(btn => {
            btn.addEventListener('click', () => pickChoice(qEl, parseInt(btn.dataset.opt, 10)));
        });
    });

    widget.querySelectorAll('.cs-q[data-type="toggle_grid"]').forEach(qEl => {
        qEl.querySelectorAll('.cs-toggle-item').forEach(item => {
            item.addEventListener('click', () => {
                const isOn = item.classList.toggle('on');
                const status = item.querySelector('.cs-toggle-status');
                if (status) status.textContent = isOn ? 'Tapped — marked strong' : 'Tap if this feels weak/risky';
            });
        });
    });

    function updateProgress() {
        if (!progressCount) return;
        let done = 0;
        const questions = widget.querySelectorAll('.cs-q');
        questions.forEach(qEl => {
            const type = qEl.dataset.type;
            if (type === 'text') {
                const ta = qEl.querySelector('.cs-field-text');
                if (ta && ta.value.trim() !== '') done++;
            } else if (type === 'list') {
                const anyFilled = [...qEl.querySelectorAll('.cs-field-list-item')].some(i => i.value.trim() !== '');
                if (anyFilled) done++;
            } else if (type === 'choice') {
                if (qEl.querySelector('.cs-choice-btn.picked')) done++;
            } else if (type === 'toggle_grid') {
                if (qEl.querySelector('.cs-toggle-item.on')) done++;
            }
        });
        progressCount.textContent = done;
        progressBar.style.width = (questions.length ? (done / questions.length * 100) : 0) + '%';
    }

    function serializeAnswers() {
        const answers = {};
        widget.querySelectorAll('.cs-q').forEach(qEl => {
            const idx  = qEl.dataset.idx;
            const type = qEl.dataset.type;
            if (type === 'text') {
                const ta = qEl.querySelector('.cs-field-text');
                answers[idx] = ta ? ta.value : '';
            } else if (type === 'list') {
                answers[idx] = [...qEl.querySelectorAll('.cs-field-list-item')].map(i => i.value);
            } else if (type === 'choice') {
                const picked = qEl.querySelector('.cs-choice-btn.picked');
                answers[idx] = picked ? parseInt(picked.dataset.opt, 10) : null;
            } else if (type === 'toggle_grid') {
                answers[idx] = [...qEl.querySelectorAll('.cs-toggle-item.on')].map(i => parseInt(i.dataset.item, 10));
            }
        });
        return answers;
    }

    // Generic contract used by group_workspace.php to drive this widget as a
    // shared/live-collaborative editor (see application/views/widgets/worksheet.php).
    window.getWidgetState = function () {
        return JSON.stringify({ answers: serializeAnswers() });
    };

    window.setWidgetState = function (content) {
        let data = {};
        try {
            data = JSON.parse(content || '{}');
        } catch (e) {
            return;
        }
        const answers = data.answers || {};
        widget.querySelectorAll('.cs-q').forEach(qEl => {
            const idx  = qEl.dataset.idx;
            const type = qEl.dataset.type;
            const val  = answers[idx];
            if (val === undefined) return;

            if (type === 'text') {
                const ta = qEl.querySelector('.cs-field-text');
                if (ta) ta.value = val || '';
            } else if (type === 'list') {
                const inputs = qEl.querySelectorAll('.cs-field-list-item');
                inputs.forEach((input, i) => { input.value = (val && val[i]) || ''; });
            } else if (type === 'choice') {
                pickChoice(qEl, (val === null || val === undefined) ? null : parseInt(val, 10));
            } else if (type === 'toggle_grid') {
                const onSet = Array.isArray(val) ? val.map(v => parseInt(v, 10)) : [];
                qEl.querySelectorAll('.cs-toggle-item').forEach(item => {
                    const isOn = onSet.includes(parseInt(item.dataset.item, 10));
                    item.classList.toggle('on', isOn);
                    const status = item.querySelector('.cs-toggle-status');
                    if (status) status.textContent = isOn ? 'Tapped — marked strong' : 'Tap if this feels weak/risky';
                });
            }
        });
        updateProgress();
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

    widget.addEventListener('input', updateProgress);
    updateProgress();
})();
</script>
<?php endif; ?>

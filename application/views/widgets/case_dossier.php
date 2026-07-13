<?php
// Widget J — Case Dossier Rating (root/docs/paperless-midterm-plan.md #4).
// Hook question -> read-only framework explainer -> multiple parallel case
// dossiers (each rated 1-5 per factor, with a cited-evidence text field) ->
// reflection questions. Not auto-graded — same manual-score-entry pattern as
// Worksheet Form/Lab Worksheet/Case Study Worksheet.
//
// $config — [
//   'meta'       => ['eyebrow' => '...', 'title' => '...', 'sub' => '...'],
//   'hook'       => ['label' => '...', 'timing' => '...', 'intro' => '<p>trusted HTML</p>', 'questions' => [ ...text/list/choice, see below... ]],
//   'framework'  => ['label' => '...', 'timing' => '...', 'intro' => '<p>...</p>', 'factors' => [['title'=>'TECH','text'=>'...'], ...], 'anchor' => '...'],
//   'groups'     => [
//     [
//       'name'    => 'GCash',
//       'accent'  => 'mango',   // free-form CSS class hint, purely cosmetic
//       'dossier' => ['title' => '...', 'facts' => ['...', '...'], 'source' => '...'],
//       'factors' => [ ['title' => 'TECH', 'question' => 'Did the technology work?'], ... ],
//     ],
//     ...
//   ],
//   'reflection' => ['label' => '...', 'timing' => '...', 'questions' => [ ...text/list/choice... ]],
// ]
// Question shapes (hook/reflection) are identical to widgets/case_study.php:
//   text   -> ['type'=>'text','badge'=>'core|bonus','prompt'=>'...','rows'=>2,'placeholder'=>'...']
//   list   -> ['type'=>'list','badge'=>...,'prompt'=>'...','lines'=>3,'placeholders'=>[...]]
//   choice -> ['type'=>'choice','badge'=>...,'prompt'=>'...','options'=>[['text'=>'...','note'=>'...'], ...]]
//
// $readonly — bool
// $existing — [
//   'hook_answers'       => { '<flat question index>' => <value shaped per type> },
//   'group_ratings'      => { '<group index>' => { '<factor index>' => {'score' => 1-5|null, 'evidence' => '...'} } },
//   'reflection_answers' => { '<flat question index>' => <value shaped per type> },
// ] or []

$readonly   = $readonly ?? false;
$existing   = $existing ?? [];
$meta       = $config['meta'] ?? [];
$hook       = $config['hook'] ?? [];
$framework  = $config['framework'] ?? [];
$groups     = $config['groups'] ?? [];
$reflection = $config['reflection'] ?? [];

$hook_answers       = $existing['hook_answers'] ?? [];
$group_ratings      = $existing['group_ratings'] ?? [];
$reflection_answers = $existing['reflection_answers'] ?? [];

// Shared text/list/choice question renderer — same shapes as
// widgets/case_study.php, kept local since every widget here is self-contained.
function cd_render_question($q, $idx, $answers, $readonly, $section_prefix)
{
    $type   = $q['type'] ?? 'text';
    $badge  = $q['badge'] ?? 'core';
    $value  = $answers[$idx] ?? null;
    ob_start();
    ?>
    <div class="cd-q" data-idx="<?= $idx ?>" data-type="<?= htmlspecialchars($type) ?>" data-section="<?= htmlspecialchars($section_prefix) ?>">
        <div class="cd-q-head">
            <span class="badge <?= $badge === 'bonus' ? 'badge-warning' : 'badge-primary' ?>"><?= htmlspecialchars(strtoupper($badge)) ?></span>
        </div>
        <p class="cd-prompt"><?= htmlspecialchars($q['prompt'] ?? '') ?></p>

        <?php if ($type === 'text'): ?>
            <?php $text_val = is_string($value) ? $value : ''; ?>
            <?php if ($readonly): ?>
                <div class="cd-answer"><?= $text_val !== '' ? nl2br(htmlspecialchars($text_val)) : '<span class="text-muted">No answer.</span>' ?></div>
            <?php else: ?>
                <textarea class="form-control form-control-sm cd-field-text" rows="<?= (int) ($q['rows'] ?? 2) ?>" placeholder="<?= htmlspecialchars($q['placeholder'] ?? '') ?>"><?= htmlspecialchars($text_val) ?></textarea>
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
                    <div class="cd-answer"><span class="text-muted">No answer.</span></div>
                <?php else: ?>
                    <ul class="mb-0">
                        <?php foreach ($list_val as $line): ?>
                            <?php if (trim((string) $line) !== ''): ?><li><?= htmlspecialchars($line) ?></li><?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            <?php else: ?>
                <?php for ($li = 0; $li < $lines; $li++): ?>
                    <input type="text" class="form-control form-control-sm cd-list-line cd-field-list-item" data-line="<?= $li ?>"
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
                    <div class="cd-answer"><?= htmlspecialchars($options[$selected]['text'] ?? '') ?></div>
                    <?php if (!empty($options[$selected]['note'])): ?>
                        <div class="cd-note show"><?= htmlspecialchars($options[$selected]['note']) ?></div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="cd-answer"><span class="text-muted">No answer.</span></div>
                <?php endif; ?>
            <?php else: ?>
                <div class="cd-choices">
                    <?php foreach ($options as $oi => $opt): ?>
                        <button type="button" class="cd-choice-btn<?= $selected === $oi ? ' picked' : '' ?>"
                                data-opt="<?= $oi ?>" data-note="<?= htmlspecialchars($opt['note'] ?? '') ?>">
                            <?= htmlspecialchars($opt['text'] ?? '') ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <div class="cd-note<?= $selected !== null && !empty($options[$selected]['note']) ? ' show' : '' ?>">
                    <?= $selected !== null ? htmlspecialchars($options[$selected]['note'] ?? '') : '' ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

$accent_map = [
    'mango'  => ['border' => '#e8942c', 'ink' => '#c9760f', 'bg' => 'rgba(232,148,44,0.10)'],
    'teal'   => ['border' => '#1f5f5b', 'ink' => '#123f3c', 'bg' => 'rgba(31,95,91,0.08)'],
    'purple' => ['border' => '#8b5fbf', 'ink' => '#6a3f9e', 'bg' => 'rgba(139,95,191,0.10)'],
];
?>
<style>
    #case-dossier-widget { text-align: left; }
    #case-dossier-widget .cd-progress { font-size: 13px; color: #6c757d; margin-bottom: 14px; }
    #case-dossier-widget .cd-bar { height: 6px; background: #e8e6df; border-radius: 3px; overflow: hidden; margin-top: 5px; }
    #case-dossier-widget .cd-bar i { display: block; height: 100%; width: 0; background: #357abd; transition: width .25s; }
    #case-dossier-widget .cd-meta { background: #f6f5f1; border: 1px solid #e3e1da; border-left: 5px solid #357abd; border-radius: 6px; padding: 16px 18px; margin-bottom: 18px; }
    #case-dossier-widget .cd-eyebrow { font-size: 11px; font-weight: bold; letter-spacing: .08em; text-transform: uppercase; color: #357abd; margin-bottom: 4px; }
    #case-dossier-widget .cd-meta h4 { margin: 0 0 4px; }
    #case-dossier-widget .cd-meta .cd-sub { font-size: 13px; color: #6c757d; }
    #case-dossier-widget .cd-section { background: #fff; border: 1px solid #e3e1da; border-radius: 6px; padding: 16px 18px; margin-bottom: 16px; }
    #case-dossier-widget .cd-section-head h5 { margin: 0; display: inline-block; }
    #case-dossier-widget .cd-timing { font-size: 11px; color: #6c757d; margin-left: 8px; }
    #case-dossier-widget .cd-q { margin-bottom: 16px; padding-bottom: 14px; border-bottom: 1px solid #eee; }
    #case-dossier-widget .cd-q:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
    #case-dossier-widget .cd-prompt { font-weight: 500; margin: 0 0 8px; }
    #case-dossier-widget .cd-list-line { margin-bottom: 6px; }
    #case-dossier-widget .cd-choice-btn { display: inline-block; font-size: 13px; padding: 7px 12px; margin: 0 6px 6px 0; border: 1.5px solid #357abd; border-radius: 6px; background: transparent; color: #357abd; cursor: pointer; }
    #case-dossier-widget .cd-choice-btn.picked { background: #357abd; color: #fff; }
    #case-dossier-widget .cd-note { display: none; margin-top: 8px; padding: 8px 10px; border-radius: 6px; background: #eef4fb; border-left: 3px solid #357abd; font-size: 13px; }
    #case-dossier-widget .cd-note.show { display: block; }
    #case-dossier-widget .cd-answer { white-space: pre-wrap; background: #f6f5f1; border: 1px solid #e3e1da; border-radius: 4px; padding: 8px 10px; min-height: 20px; }
    #case-dossier-widget .cd-factor-card { flex: 1 1 150px; border: 1.5px solid #e3e1da; border-radius: 8px; padding: 10px 12px; background: #fdfcf9; }
    #case-dossier-widget .cd-factor-card b { display: block; font-size: 12px; color: #357abd; }
    #case-dossier-widget .cd-factor-grid { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 6px; }
    #case-dossier-widget .cd-anchor { margin-top: 14px; font-style: italic; text-align: center; padding: 10px; border-top: 1px dashed #e3e1da; border-bottom: 1px dashed #e3e1da; }
    #case-dossier-widget .cd-group { border-radius: 6px; padding: 16px 18px; margin-bottom: 16px; border: 1px solid #e3e1da; }
    #case-dossier-widget .cd-dossier { border-radius: 8px; padding: 12px 14px; margin: 10px 0; font-size: 13px; }
    #case-dossier-widget .cd-dossier ul { margin: 0; padding-left: 18px; }
    #case-dossier-widget .cd-dossier li { margin-bottom: 6px; }
    #case-dossier-widget .cd-dossier .cd-source { margin-top: 8px; font-style: italic; font-size: 12px; color: #6c757d; }
    #case-dossier-widget .cd-rating-row { margin-top: 14px; padding-top: 12px; border-top: 1px solid #eee; }
    #case-dossier-widget .cd-rating-row:first-of-type { border-top: none; margin-top: 8px; padding-top: 0; }
    #case-dossier-widget .cd-rf-name { font-weight: 700; font-size: 13px; }
    #case-dossier-widget .cd-rf-q { font-size: 13px; color: #6c757d; margin-left: 6px; }
    #case-dossier-widget .cd-rate-scale { display: flex; flex-wrap: wrap; gap: 6px; margin: 8px 0; }
    #case-dossier-widget .cd-rate-scale button { font-weight: 700; font-size: 14px; width: 34px; height: 34px; border-radius: 6px; border: 1.5px solid #e3e1da; background: #fdfcf9; color: #6c757d; cursor: pointer; }
    #case-dossier-widget .cd-rate-scale button.picked { background: #357abd; border-color: #357abd; color: #fff; }
    #case-dossier-widget .cd-evidence-label { font-size: 12px; color: #6c757d; margin-bottom: 2px; }
    #case-dossier-widget .cd-evidence-input { width: 100%; border: none; border-bottom: 1px dotted #e3e1da; background: transparent; padding: 6px 4px; }
    #case-dossier-widget .cd-evidence-input:focus { outline: none; border-bottom: 1.5px solid #357abd; }
</style>
<div id="case-dossier-widget">
    <?php if (!empty($meta)): ?>
        <div class="cd-meta">
            <?php if (!empty($meta['eyebrow'])): ?><div class="cd-eyebrow"><?= htmlspecialchars($meta['eyebrow']) ?></div><?php endif; ?>
            <?php if (!empty($meta['title'])): ?><h4><?= htmlspecialchars($meta['title']) ?></h4><?php endif; ?>
            <?php if (!empty($meta['sub'])): ?><div class="cd-sub"><?= htmlspecialchars($meta['sub']) ?></div><?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!$readonly): ?>
        <div class="cd-progress">
            Progress: <span id="cd-progress-count">0</span>/<span id="cd-progress-total">0</span> answered
            <div class="cd-bar"><i id="cd-progress-bar"></i></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($hook)): ?>
        <div class="cd-section">
            <div class="cd-section-head">
                <h5><?= htmlspecialchars($hook['label'] ?? '') ?></h5>
                <?php if (!empty($hook['timing'])): ?><span class="cd-timing"><?= htmlspecialchars($hook['timing']) ?></span><?php endif; ?>
            </div>
            <?php if (!empty($hook['intro'])): ?><div class="mb-2"><?= $hook['intro'] ?></div><?php endif; ?>
            <?php foreach ($hook['questions'] ?? [] as $qi => $q): ?>
                <?= cd_render_question($q, $qi, $hook_answers, $readonly, 'hook') ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($framework)): ?>
        <div class="cd-section">
            <div class="cd-section-head">
                <h5><?= htmlspecialchars($framework['label'] ?? '') ?></h5>
                <?php if (!empty($framework['timing'])): ?><span class="cd-timing"><?= htmlspecialchars($framework['timing']) ?></span><?php endif; ?>
            </div>
            <?php if (!empty($framework['intro'])): ?><div class="mb-2"><?= $framework['intro'] ?></div><?php endif; ?>
            <div class="cd-factor-grid">
                <?php foreach ($framework['factors'] ?? [] as $f): ?>
                    <div class="cd-factor-card">
                        <b><?= htmlspecialchars($f['title'] ?? '') ?></b>
                        <div><?= htmlspecialchars($f['text'] ?? '') ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (!empty($framework['anchor'])): ?><div class="cd-anchor">&ldquo;<?= htmlspecialchars($framework['anchor']) ?>&rdquo;</div><?php endif; ?>
        </div>
    <?php endif; ?>

    <?php foreach ($groups as $gi => $group): ?>
        <?php
        $accent = $accent_map[$group['accent'] ?? ''] ?? ['border' => '#357abd', 'ink' => '#357abd', 'bg' => 'rgba(53,122,189,0.08)'];
        $dossier = $group['dossier'] ?? [];
        $ratings = $group_ratings[$gi] ?? [];
        ?>
        <div class="cd-group" style="border-left: 5px solid <?= $accent['border'] ?>;">
            <h5 style="color:<?= $accent['ink'] ?>;"><?= htmlspecialchars($group['name'] ?? ('Group ' . ($gi + 1))) ?></h5>

            <?php if (!empty($dossier)): ?>
                <div class="cd-dossier" style="background:<?= $accent['bg'] ?>; border:1px solid <?= $accent['border'] ?>;">
                    <?php if (!empty($dossier['title'])): ?><div style="font-weight:700; color:<?= $accent['ink'] ?>;"><?= htmlspecialchars($dossier['title']) ?></div><?php endif; ?>
                    <?php if (!empty($dossier['facts'])): ?>
                        <ul>
                            <?php foreach ($dossier['facts'] as $fact): ?><li><?= htmlspecialchars($fact) ?></li><?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                    <?php if (!empty($dossier['source'])): ?><div class="cd-source"><?= htmlspecialchars($dossier['source']) ?></div><?php endif; ?>
                </div>
            <?php endif; ?>

            <?php foreach ($group['factors'] ?? [] as $fi => $factor): ?>
                <?php
                $rating   = $ratings[$fi] ?? [];
                $score    = isset($rating['score']) && is_numeric($rating['score']) ? (int) $rating['score'] : null;
                $evidence = is_string($rating['evidence'] ?? null) ? $rating['evidence'] : '';
                ?>
                <div class="cd-rating-row" data-group="<?= $gi ?>" data-factor="<?= $fi ?>">
                    <span class="cd-rf-name" style="color:<?= $accent['ink'] ?>;"><?= htmlspecialchars($factor['title'] ?? '') ?></span>
                    <span class="cd-rf-q">&mdash; <?= htmlspecialchars($factor['question'] ?? '') ?></span>

                    <?php if ($readonly): ?>
                        <div class="cd-answer">
                            <?= $score !== null ? 'Rating: ' . $score . '/5' : '<span class="text-muted">No rating.</span>' ?>
                            <?php if ($evidence !== ''): ?><br>Evidence: <?= htmlspecialchars($evidence) ?><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="cd-rate-scale">
                            <?php for ($n = 1; $n <= 5; $n++): ?>
                                <button type="button" class="cd-rate-btn<?= $score === $n ? ' picked' : '' ?>" data-score="<?= $n ?>"><?= $n ?></button>
                            <?php endfor; ?>
                        </div>
                        <div class="cd-evidence-label">Cite 1 dossier fact as evidence:</div>
                        <input type="text" class="cd-evidence-input" value="<?= htmlspecialchars($evidence) ?>">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <?php if (!empty($reflection)): ?>
        <div class="cd-section">
            <div class="cd-section-head">
                <h5><?= htmlspecialchars($reflection['label'] ?? '') ?></h5>
                <?php if (!empty($reflection['timing'])): ?><span class="cd-timing"><?= htmlspecialchars($reflection['timing']) ?></span><?php endif; ?>
            </div>
            <?php foreach ($reflection['questions'] ?? [] as $qi => $q): ?>
                <?= cd_render_question($q, $qi, $reflection_answers, $readonly, 'reflection') ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($readonly && empty($hook) && empty($groups) && empty($reflection)): ?>
        <p class="text-muted text-center">No submission.</p>
    <?php endif; ?>
</div>

<?php if (!$readonly): ?>
<script>
(function () {
    const widget = document.getElementById('case-dossier-widget');
    const progressCount = document.getElementById('cd-progress-count');
    const progressTotal = document.getElementById('cd-progress-total');
    const progressBar   = document.getElementById('cd-progress-bar');

    function pickChoice(qEl, optIndex) {
        qEl.querySelectorAll('.cd-choice-btn').forEach(b => b.classList.remove('picked'));
        const noteEl = qEl.querySelector('.cd-note');
        if (optIndex === null) {
            if (noteEl) { noteEl.textContent = ''; noteEl.classList.remove('show'); }
            return;
        }
        const btn = qEl.querySelector(`.cd-choice-btn[data-opt="${optIndex}"]`);
        if (btn) btn.classList.add('picked');
        if (noteEl) {
            const note = btn ? btn.dataset.note : '';
            noteEl.textContent = note || '';
            noteEl.classList.toggle('show', !!note);
        }
    }

    widget.querySelectorAll('.cd-q[data-type="choice"]').forEach(qEl => {
        qEl.querySelectorAll('.cd-choice-btn').forEach(btn => {
            btn.addEventListener('click', () => pickChoice(qEl, parseInt(btn.dataset.opt, 10)));
        });
    });

    widget.querySelectorAll('.cd-rating-row').forEach(row => {
        row.querySelectorAll('.cd-rate-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                row.querySelectorAll('.cd-rate-btn').forEach(b => b.classList.remove('picked'));
                btn.classList.add('picked');
                updateProgress();
            });
        });
    });

    function updateProgress() {
        if (!progressCount) return;
        let done = 0, total = 0;

        widget.querySelectorAll('.cd-q').forEach(qEl => {
            total++;
            const type = qEl.dataset.type;
            if (type === 'text') {
                const ta = qEl.querySelector('.cd-field-text');
                if (ta && ta.value.trim() !== '') done++;
            } else if (type === 'list') {
                if ([...qEl.querySelectorAll('.cd-field-list-item')].some(i => i.value.trim() !== '')) done++;
            } else if (type === 'choice') {
                if (qEl.querySelector('.cd-choice-btn.picked')) done++;
            }
        });

        widget.querySelectorAll('.cd-rating-row').forEach(row => {
            total++;
            if (row.querySelector('.cd-rate-btn.picked')) done++;
        });

        progressCount.textContent = done;
        progressTotal.textContent = total;
        progressBar.style.width = (total ? (done / total * 100) : 0) + '%';
    }

    function serializeQuestions(selector) {
        const answers = {};
        widget.querySelectorAll(selector).forEach(qEl => {
            const idx  = qEl.dataset.idx;
            const type = qEl.dataset.type;
            if (type === 'text') {
                const ta = qEl.querySelector('.cd-field-text');
                answers[idx] = ta ? ta.value : '';
            } else if (type === 'list') {
                answers[idx] = [...qEl.querySelectorAll('.cd-field-list-item')].map(i => i.value);
            } else if (type === 'choice') {
                const picked = qEl.querySelector('.cd-choice-btn.picked');
                answers[idx] = picked ? parseInt(picked.dataset.opt, 10) : null;
            }
        });
        return answers;
    }

    function serializeGroupRatings() {
        const ratings = {};
        widget.querySelectorAll('.cd-rating-row').forEach(row => {
            const gi = row.dataset.group;
            const fi = row.dataset.factor;
            const picked = row.querySelector('.cd-rate-btn.picked');
            const evidenceInput = row.querySelector('.cd-evidence-input');
            if (!ratings[gi]) ratings[gi] = {};
            ratings[gi][fi] = {
                score: picked ? parseInt(picked.dataset.score, 10) : null,
                evidence: evidenceInput ? evidenceInput.value : ''
            };
        });
        return ratings;
    }

    window.getWidgetState = function () {
        return JSON.stringify({
            hook_answers: serializeQuestions('.cd-q[data-section="hook"]'),
            group_ratings: serializeGroupRatings(),
            reflection_answers: serializeQuestions('.cd-q[data-section="reflection"]')
        });
    };

    window.setWidgetState = function (content) {
        let data = {};
        try {
            data = JSON.parse(content || '{}');
        } catch (e) {
            return;
        }
        const hookAnswers = data.hook_answers || {};
        const reflectionAnswers = data.reflection_answers || {};
        const groupRatings = data.group_ratings || {};

        widget.querySelectorAll('.cd-q').forEach(qEl => {
            const idx = qEl.dataset.idx;
            const type = qEl.dataset.type;
            const source = qEl.dataset.section === 'hook' ? hookAnswers : reflectionAnswers;
            const val = source[idx];
            if (val === undefined) return;

            if (type === 'text') {
                const ta = qEl.querySelector('.cd-field-text');
                if (ta) ta.value = val || '';
            } else if (type === 'list') {
                const inputs = qEl.querySelectorAll('.cd-field-list-item');
                inputs.forEach((input, i) => { input.value = (val && val[i]) || ''; });
            } else if (type === 'choice') {
                pickChoice(qEl, (val === null || val === undefined) ? null : parseInt(val, 10));
            }
        });

        widget.querySelectorAll('.cd-rating-row').forEach(row => {
            const gi = row.dataset.group;
            const fi = row.dataset.factor;
            const r = (groupRatings[gi] || {})[fi];
            if (!r) return;
            row.querySelectorAll('.cd-rate-btn').forEach(b => {
                b.classList.toggle('picked', r.score !== null && parseInt(b.dataset.score, 10) === parseInt(r.score, 10));
            });
            const evidenceInput = row.querySelector('.cd-evidence-input');
            if (evidenceInput && r.evidence !== undefined) evidenceInput.value = r.evidence;
        });

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
    updateProgress();
})();
</script>
<?php endif; ?>

<?php
// Widget H — Lab Worksheet (root/docs/paperless-midterm-plan.md #4).
// Predict → Observe → Explain lab activity: a fixed sequence of experiments,
// each with admin-authored instructions (may include code snippets) and a
// small set of free-text prompts the student fills in. Not auto-graded —
// same manual-score-entry pattern as Worksheet Form / Card Sort.
//
// $config — [
//   'intro'         => '<p>optional HTML shown above the experiments (objectives, timeline, etc.)</p>',
//   'experiments'   => [
//     [
//       'title'        => 'Experiment 1.1 — Declare an array and print the first element',
//       'instructions' => '<p>...</p><pre><code>...</code></pre>',  // trusted admin-authored HTML, like _interactive_quiz_template.php's section.lesson
//       'warning'      => false,        // true = "breaking it on purpose" styling
//       'prompts'      => [ ['tag' => 'predict', 'label' => 'PREDICT', 'text' => '...'], ... ],
//       'note'         => 'Fix it back: ...',  // optional, shown after the prompts
//     ],
//     ...
//   ],
//   'exit_question' => 'optional single free-text question shown after all experiments',
// ]
// $readonly — bool
// $existing — ['answers' => { '<experiment index>' => { '<prompt tag>' => '<text>' } }, 'exit_question' => '<text>'] or null

$readonly    = $readonly ?? false;
$existing    = $existing ?? [];
$intro       = $config['intro'] ?? '';
$experiments = $config['experiments'] ?? [];
$exit_q      = $config['exit_question'] ?? '';
$answers     = $existing['answers'] ?? [];
$exit_answer = $existing['exit_question'] ?? '';

$tag_colors = [
    'predict' => ['bg' => '#e4ecfb', 'ink' => '#1d4e9e'],
    'observe' => ['bg' => '#e7f2ef', 'ink' => '#0f5e4d'],
    'explain' => ['bg' => '#f3e8fb', 'ink' => '#6b2fa0'],
    'bonus'   => ['bg' => '#fdf3e4', 'ink' => '#8a4b0f'],
];
?>
<style>
    #lab-worksheet-widget .lw-progress { font-size: 13px; color: #6c757d; margin-bottom: 14px; }
    #lab-worksheet-widget .lw-bar { height: 6px; background: #e8e6df; border-radius: 3px; overflow: hidden; margin-top: 5px; }
    #lab-worksheet-widget .lw-bar i { display: block; height: 100%; width: 0; background: #0f5e4d; transition: width .25s; }
    #lab-worksheet-widget .lw-exp { text-align: left; background: #fff; border: 1px solid #e3e1da; border-left: 5px solid #0f5e4d; border-radius: 6px; padding: 16px 18px; margin-bottom: 16px; }
    #lab-worksheet-widget .lw-exp.lw-warn { background: #fdf3e4; border: 1px dashed #8a4b0f; border-left: 5px solid #8a4b0f; }
    #lab-worksheet-widget .lw-flag { font-size: 11px; font-weight: bold; letter-spacing: .08em; color: #8a4b0f; text-transform: uppercase; margin-bottom: 6px; }
    #lab-worksheet-widget .lw-exp h4 { font-size: 16px; margin: 0 0 8px; }
    #lab-worksheet-widget .lw-instructions { margin-bottom: 12px; }
    #lab-worksheet-widget .lw-instructions pre { background: #22272e; color: #e6edf3; padding: 10px 12px; border-radius: 6px; overflow-x: auto; }
    #lab-worksheet-widget .lw-tag { display: inline-block; font-size: 11px; font-weight: bold; letter-spacing: .05em; padding: 2px 8px; border-radius: 3px; margin-bottom: 4px; }
    #lab-worksheet-widget .lw-prompt { margin-bottom: 10px; }
    #lab-worksheet-widget .lw-note { font-style: italic; color: #6c757d; font-size: 13px; margin: 10px 0 0; }
    #lab-worksheet-widget .lw-answer { white-space: pre-wrap; background: #f6f5f1; border: 1px solid #e3e1da; border-radius: 4px; padding: 8px 10px; min-height: 20px; }
</style>
<div id="lab-worksheet-widget">
    <?php if ($intro): ?>
        <div class="lw-intro mb-3"><?= $intro ?></div>
    <?php endif; ?>

    <?php if (!$readonly): ?>
        <div class="lw-progress">
            Progress: <span id="lw-progress-count">0</span>/<?= count($experiments) + ($exit_q ? 1 : 0) ?> answered
            <div class="lw-bar"><i id="lw-progress-bar"></i></div>
        </div>
    <?php endif; ?>

    <?php foreach ($experiments as $i => $exp): ?>
        <?php
        $title = $exp['title'] ?? ('Experiment ' . ($i + 1));
        $is_warn = !empty($exp['warning']);
        $prompts = $exp['prompts'] ?? [];
        $exp_answers = $answers[$i] ?? [];
        ?>
        <div class="lw-exp<?= $is_warn ? ' lw-warn' : '' ?>" data-idx="<?= $i ?>">
            <?php if ($is_warn): ?><p class="lw-flag">&#9888; Breaking it on purpose</p><?php endif; ?>
            <h4><?= htmlspecialchars($title) ?></h4>
            <?php if (!empty($exp['instructions'])): ?>
                <div class="lw-instructions"><?= $exp['instructions'] ?></div>
            <?php endif; ?>

            <?php foreach ($prompts as $p): ?>
                <?php
                $tag   = $p['tag'] ?? 'predict';
                $label = $p['label'] ?? strtoupper($tag);
                $color = $tag_colors[$tag] ?? ['bg' => '#eee', 'ink' => '#333'];
                $value = $exp_answers[$tag] ?? '';
                ?>
                <div class="lw-prompt">
                    <span class="lw-tag" style="background:<?= $color['bg'] ?>; color:<?= $color['ink'] ?>;"><?= htmlspecialchars($label) ?></span>
                    <p class="mb-1"><?= htmlspecialchars($p['text'] ?? '') ?></p>
                    <?php if ($readonly): ?>
                        <div class="lw-answer"><?= $value !== '' ? nl2br(htmlspecialchars($value)) : '<span class="text-muted">No answer.</span>' ?></div>
                    <?php else: ?>
                        <textarea class="form-control form-control-sm lw-field" data-tag="<?= htmlspecialchars($tag) ?>" rows="2"><?= htmlspecialchars($value) ?></textarea>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <?php if (!empty($exp['note'])): ?>
                <p class="lw-note"><?= htmlspecialchars($exp['note']) ?></p>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <?php if ($exit_q): ?>
        <div class="lw-exp" data-exit="1">
            <h4>Exit Question</h4>
            <p class="mb-1"><?= htmlspecialchars($exit_q) ?></p>
            <?php if ($readonly): ?>
                <div class="lw-answer"><?= $exit_answer !== '' ? nl2br(htmlspecialchars($exit_answer)) : '<span class="text-muted">No answer.</span>' ?></div>
            <?php else: ?>
                <textarea class="form-control form-control-sm" id="lw-exit-question" rows="2"><?= htmlspecialchars($exit_answer) ?></textarea>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($readonly && empty($experiments)): ?>
        <p class="text-muted text-center">No submission.</p>
    <?php endif; ?>
</div>

<?php if (!$readonly): ?>
<script>
(function () {
    const widget = document.getElementById('lab-worksheet-widget');
    const progressCount = document.getElementById('lw-progress-count');
    const progressBar   = document.getElementById('lw-progress-bar');
    const exitField      = document.getElementById('lw-exit-question');

    function updateProgress() {
        if (!progressCount) return;
        let done = 0;
        let total = 0;
        widget.querySelectorAll('.lw-exp[data-idx]').forEach(expEl => {
            total++;
            const fields = expEl.querySelectorAll('.lw-field');
            const filled = [...fields].every(f => f.value.trim() !== '') && fields.length > 0;
            if (filled) done++;
        });
        if (exitField) {
            total++;
            if (exitField.value.trim() !== '') done++;
        }
        progressCount.textContent = done;
        progressBar.style.width = (total ? (done / total * 100) : 0) + '%';
    }

    function serializeAnswers() {
        const answers = {};
        widget.querySelectorAll('.lw-exp[data-idx]').forEach(expEl => {
            const idx = expEl.dataset.idx;
            const a = {};
            expEl.querySelectorAll('.lw-field').forEach(f => { a[f.dataset.tag] = f.value; });
            answers[idx] = a;
        });
        return answers;
    }

    window.getWidgetState = function () {
        return JSON.stringify({
            answers: serializeAnswers(),
            exit_question: exitField ? exitField.value : ''
        });
    };

    window.setWidgetState = function (content) {
        let data = {};
        try {
            data = JSON.parse(content || '{}');
        } catch (e) {
            return;
        }
        const answers = data.answers || {};
        widget.querySelectorAll('.lw-exp[data-idx]').forEach(expEl => {
            const a = answers[expEl.dataset.idx] || {};
            expEl.querySelectorAll('.lw-field').forEach(f => {
                if (a[f.dataset.tag] !== undefined) f.value = a[f.dataset.tag];
            });
        });
        if (exitField && data.exit_question !== undefined) exitField.value = data.exit_question;
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

<?php
// Widget — Multiple Choice Quiz (not in the original paperless-midterm plan;
// added to fold the old QuizController/json_file_path flow's "give the admin
// a file to upload" requirement into the same config-in-`given`,
// submission-in-`code` pattern the other widgets use).
//
// $config   — ['questions' => [ ['question' => '...', 'choices' => [...], 'answer' => '...'], ... ]]
//              'choices' empty/omitted => free-text question (case-insensitive match)
// $readonly — bool
// $existing — input mode (not readonly): ['answers' => {index: value, ...}] or null (prefill, e.g. resuming a group draft)
//           — readonly mode: [ ['question','user_answer','correct_answer','is_correct'], ... ] (graded results)

$readonly = $readonly ?? false;
$existing = $existing ?? null;
$questions = $config['questions'] ?? [];
$prefill = (!$readonly && is_array($existing)) ? ($existing['answers'] ?? []) : [];
$results = $readonly ? ($existing ?: []) : [];
?>
<div id="quiz-widget">
    <?php if ($readonly): ?>
        <?php if (empty($results)): ?>
            <p class="text-muted text-center">No submission.</p>
        <?php else: ?>
            <?php $correct_count = count(array_filter($results, function ($r) { return !empty($r['is_correct']); })); ?>
            <p class="font-weight-bold mb-3">
                Score: <?= $correct_count ?> / <?= count($results) ?>
            </p>
            <?php foreach ($results as $i => $r): ?>
                <div class="card mb-2 <?= !empty($r['is_correct']) ? 'border-success' : 'border-danger' ?>">
                    <div class="card-body py-2">
                        <p class="mb-1"><strong>#<?= $i + 1 ?>:</strong> <?= htmlspecialchars($r['question'] ?? '') ?></p>
                        <p class="mb-1 <?= !empty($r['is_correct']) ? 'text-success' : 'text-danger' ?>">
                            <i class="fas <?= !empty($r['is_correct']) ? 'fa-check' : 'fa-times' ?>"></i>
                            Your answer: <strong><?= htmlspecialchars((string) ($r['user_answer'] ?? '')) ?></strong>
                        </p>
                        <?php if (empty($r['is_correct'])): ?>
                            <p class="mb-0 text-success">
                                <i class="fas fa-check"></i> Correct answer: <strong><?= htmlspecialchars((string) ($r['correct_answer'] ?? '')) ?></strong>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php else: ?>
        <?php foreach ($questions as $i => $q): ?>
            <?php
            $choices = array_values(array_filter($q['choices'] ?? [], function ($c) { return trim($c) !== ''; }));
            $prefilled_value = $prefill[$i] ?? '';
            ?>
            <div class="card mb-3 quiz-question" data-index="<?= $i ?>">
                <div class="card-body">
                    <p class="font-weight-bold mb-2">#<?= $i + 1 ?>: <?= htmlspecialchars($q['question'] ?? '') ?></p>
                    <?php if (!empty($choices)): ?>
                        <?php foreach ($choices as $ci => $choice): ?>
                            <div class="form-check">
                                <input type="radio" class="form-check-input quiz-answer" name="quiz-q-<?= $i ?>"
                                       id="quiz-q-<?= $i ?>-<?= $ci ?>" value="<?= htmlspecialchars($choice) ?>"
                                       <?= ((string) $prefilled_value === (string) $choice) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="quiz-q-<?= $i ?>-<?= $ci ?>"><?= htmlspecialchars($choice) ?></label>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <input type="text" class="form-control quiz-answer" data-question="<?= $i ?>"
                               value="<?= htmlspecialchars((string) $prefilled_value) ?>" placeholder="Your answer">
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if (!$readonly): ?>
<script>
(function () {
    const widget = document.getElementById('quiz-widget');

    function serializeAnswers() {
        const answers = {};
        widget.querySelectorAll('.quiz-question').forEach(card => {
            const index = card.dataset.index;
            const radio = card.querySelector('.quiz-answer:checked');
            if (radio) {
                answers[index] = radio.value;
                return;
            }
            const text = card.querySelector('input[type="text"].quiz-answer');
            if (text) answers[index] = text.value;
        });
        return answers;
    }

    window.getWidgetState = function () {
        return JSON.stringify({ answers: serializeAnswers() });
    };

    window.setWidgetState = function (content) {
        let answers = {};
        try {
            answers = JSON.parse(content || '{}').answers || {};
        } catch (e) {
            return;
        }
        widget.querySelectorAll('.quiz-question').forEach(card => {
            const index = card.dataset.index;
            const value = answers[index];
            if (value === undefined) return;
            const radio = card.querySelector('.quiz-answer[value="' + CSS.escape(String(value)) + '"]');
            if (radio) {
                radio.checked = true;
                return;
            }
            const text = card.querySelector('input[type="text"].quiz-answer');
            if (text) text.value = value;
        });
    };

    window.isWidgetFocused = function () {
        return widget.contains(document.activeElement);
    };

    // Called by the host page right before it submits the form — serializes
    // this widget's raw answers into the hidden #widget-code-value field.
    // Scoring happens server-side (AssessmentController::submit_classwork()),
    // never trust a client-computed score.
    window.serializeWidgetBeforeSubmit = function () {
        const codeField = document.getElementById('widget-code-value');
        if (codeField) codeField.value = window.getWidgetState();
    };
})();
</script>
<?php endif; ?>

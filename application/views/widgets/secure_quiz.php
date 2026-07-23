<?php
// Widget — Timed/Secure Quiz.
// Registered as this widget's input_view for the generic preview/readonly
// paths (admin config preview, student_submission.php / all_submission.php),
// but the real interface is SecureQuizController — AssessmentController::
// assessment_view_code() redirects there before this view would ever render
// for a live student attempt. Same {question,choices,answer} config/result
// shape as widgets/quiz.php (and Widgets_model::grade_quiz()), just with a
// fullscreen/timer/tab-switch-lockdown UI instead of an inline card form.
$readonly = $readonly ?? false;
$existing = $existing ?? null;
// Accept either {"questions":[...]} or a bare list [ {...}, {...} ] of questions
// (mirrors Widgets_model::quiz_questions(); inlined so this view has no model dep).
$questions = (is_array($config ?? null) && array_key_exists('questions', $config))
    ? (is_array($config['questions']) ? $config['questions'] : [])
    : ((is_array($config ?? null) && $config === array_values($config)) ? $config : []);
$results = $readonly ? ($existing ?: []) : [];
?>
<div id="secure-quiz-widget-note">
    <?php if (!$readonly): ?>
        <p class="text-muted mb-2">
            <i class="fas fa-info-circle"></i>
            Timed/Secure Quiz &mdash; <?= count($questions) ?> question(s). Students take this in a dedicated
            fullscreen, timed page (tab-switch warnings, one attempt) instead of a form here.
        </p>
        <?php if (!empty($questions)): ?>
            <?php
            // This preview box is rendered inside manage_assessments.php's own
            // <form> (for save_assessment), so a literal nested <form> here
            // would be invalid markup — build a detached one at click-time
            // instead, the same "POST to a new tab" trick used for downloads
            // elsewhere in the app.
            ?>
            <button type="button" class="btn btn-outline-primary btn-sm"
                    data-config="<?= htmlspecialchars(json_encode($config), ENT_QUOTES, 'UTF-8') ?>"
                    onclick="secureQuizTest(this.dataset.config)">
                <i class="fas fa-external-link-alt"></i> Take Quiz (Test &mdash; not scored/recorded)
            </button>
            <?php if (empty($GLOBALS['_secure_quiz_test_js_loaded'])): ?>
                <?php $GLOBALS['_secure_quiz_test_js_loaded'] = true; ?>
                <script>
                    function secureQuizTest(configJson) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '<?= base_url('secure_quiz/test') ?>';
                        form.target = '_blank';
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'config';
                        input.value = configJson;
                        form.appendChild(input);
                        document.body.appendChild(form);
                        form.submit();
                        document.body.removeChild(form);
                    }
                </script>
            <?php endif; ?>
        <?php endif; ?>
    <?php else: ?>
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
    <?php endif; ?>
</div>

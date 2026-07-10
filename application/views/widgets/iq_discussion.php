<?php
// Widget — Interactive Discussion/Quiz.
// Registered as this widget's input_view for the generic preview/readonly
// paths (admin config preview, student_submission.php / all_submission.php),
// but the real interface is InteractiveQuizController::discussion() —
// AssessmentController::assessment_view_code() redirects there before this
// view would ever render for a live student attempt. Mirrors widgets/brainstorm.php.
$readonly = $readonly ?? false;
$config   = $config ?? [];
$existing = $existing ?? [];
$topic    = $config['topic'] ?? '';
?>
<div id="iq-discussion-widget-note">
    <p class="text-muted mb-0">
        <i class="fas fa-info-circle"></i>
        Interactive Discussion/Quiz &mdash; topic <code><?= htmlspecialchars($topic) ?></code>.
        <?php if ($readonly): ?>
            <?php if (!empty($existing)): ?>
                Student completed this topic.
            <?php else: ?>
                No submission recorded for this student yet.
            <?php endif; ?>
        <?php else: ?>
            Students are redirected straight to this topic instead of a form here.
        <?php endif; ?>
    </p>
</div>

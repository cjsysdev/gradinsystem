<?php
// Widget — Microlearning Quiz (Sololearn/Duolingo-style).
// Sibling of widgets/iq_discussion.php: same "wrap an assets/json topic file
// as a gradable assessment" idea, but the topic uses the denser microlearning
// schema (chunks + micro-checks, mcq/arrange/type checkpoints, objectives and
// recap screens) rendered by discussions/_interactive_micro_template.php.
//
// Registered as this widget's input_view for the generic preview/readonly
// paths (admin config preview, student_submission.php / all_submission.php),
// but the real interface is InteractiveQuizController::micro() —
// AssessmentController::assessment_view_code() redirects there before this
// view would ever render for a live student attempt.
$readonly      = $readonly ?? false;
$config        = $config ?? [];
$existing      = $existing ?? []; // classworks.code — array of {kind, section, section_title, question, chosen, correct_answer, is_correct}
$topic         = $config['topic'] ?? '';
$assessment_id = $assessment_id ?? null;
$review_url    = ($assessment_id && $topic) ? base_url('interactive_quiz/micro/' . $topic . '/' . $assessment_id) : null;
$uid           = 'iqm' . uniqid();
$topic_title   = $topic ? ucwords(str_replace(['_', '-'], ' ', $topic)) : 'Microlearning Quiz';
$student_name  = trim(($this->session->lastname ?? '') . ' ' . ($this->session->firstname ?? ''));
?>
<div id="iq-micro-widget-note">
    <p class="text-muted mb-0">
        <i class="fas fa-info-circle"></i>
        Microlearning Quiz &mdash; topic <code><?= htmlspecialchars($topic) ?></code>.
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

    <?php if (!$readonly && $topic): ?>
        <a href="<?= base_url('interactive_quiz/micro/' . $topic) ?>" class="btn btn-outline-primary btn-sm mt-2" target="_blank" rel="noopener">
            <i class="fas fa-external-link-alt"></i> Test Quiz
        </a>
    <?php endif; ?>

    <?php if ($readonly && !empty($existing)): ?>
        <div id="<?= $uid ?>" class="text-left mt-3" style="max-width:600px; margin-left:auto; margin-right:auto; background:#fff; padding:10px;">
            <p class="mb-3" style="font-weight:600;"><?= htmlspecialchars($topic_title) ?><?php if ($student_name): ?> &mdash; <?= htmlspecialchars($student_name) ?><?php endif; ?></p>
            <?php $last_section = null; ?>
            <?php foreach ($existing as $i => $item): ?>
                <?php $section = $item['section'] ?? null; ?>
                <?php if ($section !== null && $section !== $last_section): ?>
                    <?php $last_section = $section; ?>
                    <p class="mb-2 mt-3" style="font-weight:600; color:#357abd;">
                        <?= htmlspecialchars($item['section_title'] ?? ('Section ' . ($section + 1))) ?>
                    </p>
                <?php endif; ?>
                <div class="mb-3 pb-2" style="border-bottom:1px solid #eee;">
                    <p class="mb-1">
                        <strong>Q<?= $i + 1 ?>.</strong>
                        <?php if (($item['kind'] ?? '') === 'checkpoint'): ?>
                            <span class="badge badge-warning">Checkpoint</span>
                        <?php endif; ?>
                        <?= htmlspecialchars($item['question'] ?? '') ?>
                    </p>
                    <p class="mb-1">
                        Your answer:
                        <span style="color: <?= !empty($item['is_correct']) ? 'green' : 'red' ?>; font-weight:600;">
                            <?= htmlspecialchars($item['chosen'] ?? '') ?>
                        </span>
                    </p>
                    <?php if (empty($item['is_correct'])): ?>
                        <p class="mb-0">Correct answer: <span style="color:green; font-weight:600;"><?= htmlspecialchars($item['correct_answer'] ?? '') ?></span></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($readonly && $review_url): ?>
        <a href="<?= $review_url ?>" class="btn btn-outline-primary btn-sm mt-2" target="_blank" rel="noopener">
            <?= !empty($existing) ? 'Review / Retake Quiz' : 'Open Quiz' ?>
        </a>
        <?php if (!empty($existing)): ?>
            <button type="button" class="btn btn-outline-secondary btn-sm mt-2" onclick="iqExportImage('<?= $uid ?>', '<?= addslashes($topic) ?>')">
                <i class="fas fa-image"></i> Export as Image
            </button>
            <p class="text-muted mt-1" style="font-size:12px;">Retaking is for practice only — it will not change your recorded score.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php if ($readonly && !empty($existing) && $review_url): ?>
    <?php // Shared with widgets/iq_discussion.php — whichever renders first defines it. ?>
    <?php if (empty($GLOBALS['_iq_image_assets_loaded'])): ?>
        <?php $GLOBALS['_iq_image_assets_loaded'] = true; ?>
        <script src="<?= base_url('assets/html2canvas.min.js') ?>"></script>
        <script>
            function iqExportImage(containerId, topicSlug) {
                var el = document.getElementById(containerId);
                if (!el) return;
                html2canvas(el, { scale: 2, useCORS: true }).then(function(canvas) {
                    var link = document.createElement('a');
                    link.download = (topicSlug || 'quiz') + '_review.png';
                    link.href = canvas.toDataURL('image/png');
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                });
            }
        </script>
    <?php endif; ?>
<?php endif; ?>

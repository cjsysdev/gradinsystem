<?php
// Widget D — Brainstorm & Voting Board.
// Registered as this widget's input_view for the generic per-student
// rendering paths (student_submission.php / all_submission.php), but the
// real interface is the shared board at BrainstormController::board() —
// classworks rows for this widget are just participation markers (no
// per-student code/content), so this is a minimal fallback display.
$readonly = $readonly ?? false;
?>
<div id="brainstorm-widget-note">
    <p class="text-muted mb-0">
        <i class="fas fa-info-circle"></i>
        This is a shared class Brainstorm Board — there's no individual submission to review here.
        <?= $readonly ? 'This student participated on the board.' : '' ?>
    </p>
</div>

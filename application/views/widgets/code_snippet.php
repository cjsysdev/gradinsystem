<?php
// Code Snippet — an individual coding problem the instructor checks LIVE on
// the student's PC and marks RUN / EFFORT / ERROR. Attaching the code here is
// optional (it works like participation: every enrolled student gets a
// submission row and can be graded whether or not they turned anything in),
// and a student may add or update their code later, even after being graded —
// that path never touches the score (see AssessmentController::submit_classwork()).
// Not auto-graded. The verdict is never stored; it is derived from
// classworks.score by Widgets_model::code_snippet_verdict().
//
// $config — [
//   'problem'               => '...',             // required
//   'language'              => 'c',               // optional, CodeMirror mode hint, default 'c'
//   'starter_code'          => '...',             // optional, pre-fills the editor
//   'sample_input'          => '...',             // optional
//   'sample_output'         => '...',             // optional
//   'rubric'                => ['run' => 100, 'effort' => 70, 'error' => 40],  // optional, percent of max_score
//   'allow_code_submission' => bool,              // optional, default true; false = problem only
// ]
// $readonly — bool
// $existing — ['code' => '...'] or null
// $score, $max_score — optional (student review page): shows the verdict badge

$this->load->model('Widgets_model');

$readonly   = $readonly ?? false;
$existing   = is_array($existing ?? null) ? $existing : [];
$score      = $score ?? null;
$max_score  = $max_score ?? null;

$problem       = (string) ($config['problem'] ?? '');
$starter       = (string) ($config['starter_code'] ?? '');
$sample_in     = (string) ($config['sample_input'] ?? '');
$sample_out    = (string) ($config['sample_output'] ?? '');
$allow_code    = !array_key_exists('allow_code_submission', $config) || !empty($config['allow_code_submission']);
$saved_code    = (string) ($existing['code'] ?? '');
$cm_modes      = ['c' => 'text/x-csrc', 'cpp' => 'text/x-c++src', 'java' => 'text/x-java', 'csharp' => 'text/x-csharp'];
$cm_mode       = $cm_modes[strtolower((string) ($config['language'] ?? 'c'))] ?? 'text/x-csrc';

$verdict = ($readonly && $score !== null && $max_score !== null)
    ? $this->Widgets_model->code_snippet_verdict($config, $max_score, $score)
    : null;
$verdict_label = ['run' => 'RUN', 'effort' => 'EFFORT', 'error' => 'ERROR', 'custom' => 'CUSTOM'];
?>
<style>
    .cs-widget { text-align: left; }
    .cs-widget .cs-problem { background: #f6f5f1; border: 1px solid #e3e1da; border-left: 5px solid #357abd; border-radius: 6px; padding: 14px 16px; margin-bottom: 14px; font-size: 15px; white-space: pre-wrap; }
    .cs-widget .cs-problem-title { font-size: 12px; font-weight: bold; text-transform: uppercase; color: #357abd; margin-bottom: 6px; letter-spacing: .05em; }
    .cs-widget .cs-io { display: flex; gap: 12px; margin-bottom: 14px; flex-wrap: wrap; }
    .cs-widget .cs-io > div { flex: 1 1 200px; }
    .cs-widget .cs-io-label { font-size: 12px; font-weight: bold; color: #6c757d; margin-bottom: 4px; }
    .cs-widget pre.cs-io-box { background: #1e1e1e; color: #d4d4d4; border-radius: 4px; padding: 8px 10px; font-size: 13px; margin: 0; min-height: 36px; }
    .cs-widget .cs-optional { font-size: 13px; color: #6c757d; margin-bottom: 6px; }
    .cs-widget .CodeMirror { border: 1px solid #ced4da; border-radius: 4px; height: 260px; font-size: 14px; }
    .cs-widget pre.cs-code { background: #1e1e1e; color: #d4d4d4; border-radius: 4px; padding: 10px 12px; font-size: 13px; max-height: 420px; overflow: auto; margin: 0; }
    .cs-widget .cs-nocode { border: 1px dashed #b8c4d0; border-radius: 6px; padding: 14px; text-align: center; color: #6c757d; background: #fbfcfd; }
    .cs-verdict { display: inline-block; font-weight: bold; font-size: 13px; padding: 4px 10px; border-radius: 4px; color: #fff; letter-spacing: .04em; }
    .cs-verdict-run    { background: #28a745; }
    .cs-verdict-effort { background: #e0a800; color: #212529; }
    .cs-verdict-error  { background: #dc3545; }
    .cs-verdict-custom { background: #6c757d; }
</style>
<div class="cs-widget"<?= $readonly ? '' : ' id="cs-widget"' ?>>
    <?php if ($verdict): ?>
        <p class="mb-3">Verdict: <span class="cs-verdict cs-verdict-<?= $verdict ?>"><?= $verdict_label[$verdict] ?></span></p>
    <?php endif; ?>

    <div class="cs-problem">
        <div class="cs-problem-title"><i class="fa fa-code"></i> Problem</div>
        <?= htmlspecialchars($problem) ?>
    </div>
    <?php if ($sample_in !== '' || $sample_out !== ''): ?>
        <div class="cs-io">
            <div><div class="cs-io-label">Sample input</div><pre class="cs-io-box"><?= htmlspecialchars($sample_in) ?></pre></div>
            <div><div class="cs-io-label">Expected output</div><pre class="cs-io-box"><?= htmlspecialchars($sample_out) ?></pre></div>
        </div>
    <?php endif; ?>

    <?php if ($readonly): ?>
        <?php if ($allow_code): ?>
            <div class="cs-optional">Submitted code</div>
            <?php if (trim($saved_code) !== ''): ?>
                <pre class="cs-code"><?= htmlspecialchars($saved_code) ?></pre>
            <?php else: ?>
                <div class="cs-nocode"><i class="fa fa-desktop"></i> No code attached. Checked live on the student's PC.</div>
            <?php endif; ?>
        <?php endif; ?>
    <?php elseif ($allow_code): ?>
        <div class="cs-optional"><i class="fa fa-paperclip"></i> Your code <em>(optional &mdash; your instructor checks it live, and you can add it later)</em></div>
        <textarea id="cs-editor" class="form-control" rows="10"><?= htmlspecialchars($saved_code !== '' ? $saved_code : $starter) ?></textarea>
    <?php else: ?>
        <div class="cs-nocode"><i class="fa fa-desktop"></i> Your instructor will check this live on your PC. Nothing to submit.</div>
    <?php endif; ?>
</div>
<?php if (!$readonly): ?>
<script>
(function () {
    const widget = document.getElementById('cs-widget');
    const ta = document.getElementById('cs-editor');
    const starter = <?= json_encode($starter) ?>;
    let cm = null;

    // footer.php (which loads CodeMirror) renders after this view, so wait
    // for the page to finish parsing. Falls back to the plain textarea.
    document.addEventListener('DOMContentLoaded', function () {
        if (ta && typeof CodeMirror !== 'undefined') {
            cm = CodeMirror.fromTextArea(ta, {
                mode: <?= json_encode($cm_mode) ?>,
                lineNumbers: true,
                indentUnit: 4,
                matchBrackets: true,
                autoCloseBrackets: true
            });
        }
    });

    function currentCode() {
        if (cm) return cm.getValue();
        return ta ? ta.value : '';
    }

    // An untouched starter template is not "attached code".
    window.getWidgetState = function () {
        let code = currentCode();
        if (code.trim() === starter.trim()) code = '';
        return JSON.stringify({ code: code });
    };

    window.setWidgetState = function (content) {
        let data = {};
        try { data = JSON.parse(content || '{}'); } catch (e) { return; }
        const code = typeof data.code === 'string' && data.code !== '' ? data.code : starter;
        if (cm) cm.setValue(code);
        else if (ta) ta.value = code;
    };

    window.isWidgetFocused = function () {
        return !!widget && widget.contains(document.activeElement);
    };

    // Called by the host page right before it submits the form.
    window.serializeWidgetBeforeSubmit = function () {
        const codeField = document.getElementById('widget-code-value');
        if (codeField) codeField.value = window.getWidgetState();
    };
})();
</script>
<?php endif; ?>

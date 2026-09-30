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
// Optional timed batches (PC-limited lab sessions split into e.g. Batch 1 /
// Batch 2, each with its own admin-set start/end window — see
// AdminAssessmentController::snippet_batches()). Fully backward compatible:
// an assessment with no timer_config behaves exactly as before. All phase/
// deadline decisions are made server-side (Widgets_model::code_snippet_*) —
// this view only renders what it's told and syncs its clock to the server's.
//
// Optional problem pool: give `problems` (a list) instead of `problem` and
// each student gets ONE of them, picked server-side by Widgets_model::
// code_snippet_problem_index() (stable per student, pinned into
// classworks.code on the first save). The admin preview (no $student_id)
// shows the whole pool.
//
// $config — [
//   'problem'               => '...',             // required unless 'problems' is given
//   'problems'              => [                   // optional pool, overrides 'problem'
//       ['title' => '...', 'problem' => '...', 'starter_code' => '...',
//        'sample_input' => '...', 'sample_output' => '...'],  // only 'problem' required
//   ],
//   'language'              => 'c',               // optional, CodeMirror mode hint, default 'c'
//   'starter_code'          => '...',             // optional, pre-fills the editor (pool entries inherit it)
//   'sample_input'          => '...',             // optional
//   'sample_output'         => '...',             // optional
//   'rubric'                => ['run' => 100, 'effort' => 70, 'error' => 40],  // optional, percent of max_score
//   'allow_code_submission' => bool,              // optional, default true; false = problem only
// ]
// $readonly — bool
// $existing — ['code' => '...', 'state' => 'draft'|'submitted'|'timesup', 'saved_at' => '...'] or null
// $score, $max_score — optional (student review page): shows the verdict badge
// $assessment_id — the assessment_section_id, needed for the autosave endpoint
// $timer — Widgets_model::code_snippet_timer()'s return for this student, or
//          null when the assessment is untimed (or, in readonly mode, when
//          the caller didn't compute one). {batch, start_ts, end_ts, phase}
// $student_id — whose problem to show from the pool; null = admin preview

$this->load->model('Widgets_model');

$readonly      = $readonly ?? false;
$existing      = is_array($existing ?? null) ? $existing : [];
$score         = $score ?? null;
$max_score     = $max_score ?? null;
$assessment_id = $assessment_id ?? null;
$timer         = is_array($timer ?? null) ? $timer : null;
$student_id    = $student_id ?? null;

$pool          = $this->Widgets_model->code_snippet_problems($config);
$pool_count    = count($pool);
$is_preview    = $student_id === null || $student_id === '';
$problem_idx   = $is_preview ? 0 : $this->Widgets_model->code_snippet_problem_index($config, $existing, $student_id, $assessment_id);
$active        = $pool[$problem_idx] ?? ['title' => '', 'problem' => '', 'starter_code' => '', 'sample_input' => '', 'sample_output' => ''];
// Preview lists the whole pool; a real student sees only their own problem.
$shown         = ($is_preview && $pool_count > 1) ? $pool : [$problem_idx => $active];

// Timed batches: a student must not see their problem (or its starter code)
// before their batch opens — neither on the waiting page nor by opening the
// read-only review early. Admins always see it.
$is_admin      = $this->session->userdata('role') === 'admin';
$hide_problem  = !$is_admin && $timer && in_array($timer['phase'], ['waiting', 'unassigned'], true);
if ($hide_problem) {
    $shown = [];
}

$starter       = $hide_problem ? '' : $active['starter_code'];
$allow_code    = !array_key_exists('allow_code_submission', $config) || !empty($config['allow_code_submission']);
$saved_code    = (string) ($existing['code'] ?? '');
// Editor mode, labels and web/preview flags all come from one registry so a
// new language is added in exactly one place.
$lang          = $this->Widgets_model->code_snippet_language($config);
$cm_mode       = $lang['cm_mode'];
$preview_kind  = $lang['preview'];   // 'html'|'css'|'js', or null (C-family, PHP)
// The problem's sample_input doubles as the "starting HTML" that css/js code
// runs against in the preview. Never expose it before a timed batch opens.
$preview_start = $hide_problem ? '' : (string) $active['sample_input'];

$verdict = ($readonly && $score !== null && $max_score !== null)
    ? $this->Widgets_model->code_snippet_verdict($config, $max_score, $score)
    : null;
$verdict_label = ['run' => 'RUN', 'effort' => 'EFFORT', 'error' => 'ERROR', 'custom' => 'CUSTOM'];

// Timed-batch state for the badge, readonly mode only (editable mode shows
// the live countdown instead — see below).
$timer_state = ($readonly && $timer) ? $this->Widgets_model->code_snippet_effective_state($existing, $timer) : null;
$timer_state_label = [
    'submitted' => ['Submitted on time', 'success'],
    'timesup'   => ["TIME'S UP", 'danger'],
    'draft'     => ['Still in progress', 'secondary'],
];

$is_waiting = !$readonly && $timer && $timer['phase'] === 'waiting';
$is_open_timed = !$readonly && $timer && $timer['phase'] === 'open';
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
    .cs-timer-badge { display: inline-block; font-weight: bold; font-size: 12px; padding: 4px 10px; border-radius: 4px; letter-spacing: .03em; margin-bottom: 10px; }
    .cs-timer-badge-success { background: #28a745; color: #fff; }
    .cs-timer-badge-danger  { background: #dc3545; color: #fff; }
    .cs-timer-badge-secondary { background: #6c757d; color: #fff; }
    .cs-timer-bar { position: sticky; top: 0; z-index: 20; display: flex; align-items: center; justify-content: space-between; gap: 10px; background: #1e2a38; color: #fff; border-radius: 6px; padding: 10px 16px; margin-bottom: 14px; font-weight: bold; }
    .cs-timer-bar .cs-timer-clock { font-variant-numeric: tabular-nums; font-size: 20px; }
    .cs-timer-bar.cs-timer-warn { background: #a86a00; }
    .cs-timer-bar.cs-timer-danger { background: #a11d2e; animation: cs-pulse 1s infinite; }
    @keyframes cs-pulse { 0%, 100% { opacity: 1; } 50% { opacity: .75; } }
    .cs-waiting-card { border: 1px dashed #357abd; border-radius: 8px; padding: 24px; text-align: center; background: #f2f7fd; }
    .cs-waiting-card .cs-waiting-clock { font-size: 28px; font-weight: bold; font-variant-numeric: tabular-nums; margin: 10px 0; color: #357abd; }
    .cs-autosave-status { font-size: 12px; color: #6c757d; margin-top: 6px; }
    .cs-widget .cs-formsample { flex: 1 1 260px; }
    .cs-widget .cs-fakeform { background: #fff; border: 1px solid #ced4da; border-top: 3px solid #357abd; border-radius: 4px; padding: 12px 14px; }
    .cs-widget .cs-ff-row { margin-bottom: 10px; }
    .cs-widget .cs-ff-row:last-child { margin-bottom: 0; }
    .cs-widget .cs-ff-row > label { display: block; font-size: 13px; font-weight: 600; color: #343a40; margin-bottom: 3px; }
    .cs-widget .cs-ff-row > label code { font-weight: normal; font-size: 11px; color: #6c757d; margin-left: 4px; }
    .cs-widget .cs-ff-row .cs-ff-check { display: inline-block; font-weight: normal; margin-right: 14px; margin-bottom: 0; }
    .cs-widget .cs-ff-row .form-control:disabled { background: #f8f9fa; color: #212529; }
    .cs-widget .cs-result { background: #f2f9f3; border: 1px solid #cfe6d3; border-left: 4px solid #28a745; border-radius: 4px; padding: 8px 12px; font-size: 14px; min-height: 36px; }
    .cs-widget .cs-web-out { flex: 2 1 320px; }
    .cs-widget .cs-tabs { float: right; }
    .cs-widget .cs-tab { border: 1px solid #ced4da; background: #fff; color: #495057; font-size: 12px; padding: 1px 10px; cursor: pointer; }
    .cs-widget .cs-tab:first-child { border-radius: 4px 0 0 4px; }
    .cs-widget .cs-tab:last-child { border-radius: 0 4px 4px 0; margin-left: -1px; }
    .cs-widget .cs-tab.active { background: #357abd; border-color: #357abd; color: #fff; }
    .cs-widget iframe.cs-frame { width: 100%; height: 180px; border: 1px solid #ced4da; border-radius: 4px; background: #fff; display: block; }
    .cs-widget .cs-preview-wrap { display: none; margin-top: 10px; }
    .cs-widget .cs-preview-wrap iframe.cs-frame { height: 240px; }
    .cs-widget .cs-console { background: #1e1e1e; color: #d4d4d4; border-radius: 4px; padding: 6px 10px; font: 12px/1.4 monospace; margin-top: 6px; max-height: 120px; overflow: auto; white-space: pre-wrap; display: none; }
    .cs-widget .cs-console .cs-c-error { color: #f48771; }
    .cs-widget .cs-console .cs-c-warn { color: #e5c07b; }
    .cs-widget .cs-php-note { font-size: 13px; color: #6c757d; margin-top: 6px; }
    .cs-widget .cs-pool-note { font-size: 13px; color: #357abd; background: #f2f7fd; border: 1px dashed #357abd; border-radius: 6px; padding: 8px 12px; margin-bottom: 12px; }
</style>
<div class="cs-widget"<?= $readonly ? '' : ' id="cs-widget"' ?><?= $preview_kind ? ' data-cs-preview="' . $preview_kind . '" data-cs-start="' . htmlspecialchars($preview_start, ENT_QUOTES) . '"' : '' ?>>
    <?php if ($verdict): ?>
        <p class="mb-3">Verdict: <span class="cs-verdict cs-verdict-<?= $verdict ?>"><?= $verdict_label[$verdict] ?></span></p>
    <?php endif; ?>

    <?php if ($readonly && $timer): ?>
        <div>
            <?php if (!empty($timer['batch'])): ?>
                <span class="cs-timer-badge cs-timer-badge-secondary">Batch <?= (int) $timer['batch'] ?></span>
            <?php endif; ?>
            <?php if ($timer_state && isset($timer_state_label[$timer_state])): ?>
                <span class="cs-timer-badge cs-timer-badge-<?= $timer_state_label[$timer_state][1] ?>"><?= $timer_state_label[$timer_state][0] ?></span>
            <?php elseif ($timer['phase'] === 'unassigned'): ?>
                <span class="cs-timer-badge cs-timer-badge-secondary">Not assigned to a batch</span>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($is_open_timed): ?>
        <div class="cs-timer-bar" id="cs-timer-bar">
            <span>Batch <?= (int) $timer['batch'] ?></span>
            <span class="cs-timer-clock" id="cs-timer-clock">--:--</span>
        </div>
    <?php endif; ?>

    <?php if ($is_preview && $pool_count > 1): ?>
        <div class="cs-pool-note"><i class="fa fa-random"></i> Problem pool: each student is given ONE of these <?= $pool_count ?> problems.</div>
    <?php endif; ?>
    <?php foreach ($shown as $idx => $p):
        // Only a pooled assessment numbers its problems; students never see
        // the other problems' titles, just their own.
        $p_label = 'Problem' . ($pool_count > 1 && ($is_preview || $readonly) ? ' ' . ($idx + 1) . ' of ' . $pool_count : '')
            . ($p['title'] !== '' ? ' — ' . $p['title'] : '');
    ?>
        <div class="cs-problem">
            <div class="cs-problem-title"><i class="fa fa-code"></i> <?= htmlspecialchars($p_label) ?></div>
            <?= htmlspecialchars($p['problem']) ?>
        </div>
        <?php if ($p['sample_input'] !== '' || $p['sample_output'] !== ''): ?>
            <?php if (!$lang['web']): ?>
                <div class="cs-io">
                    <div><div class="cs-io-label">Sample input</div><pre class="cs-io-box"><?= htmlspecialchars($p['sample_input']) ?></pre></div>
                    <div><div class="cs-io-label">Expected output</div><pre class="cs-io-box"><?= htmlspecialchars($p['sample_output']) ?></pre></div>
                </div>
            <?php else: ?>
                <?php
                // PHP sample input written as `field=value, field=value` is drawn as
                // the form a user would have filled in (disabled controls: it is an
                // example, not something to type into). Anything that isn't pure
                // key=value data keeps the plain box.
                $form_fields = $lang['key'] === 'php' ? $this->Widgets_model->code_snippet_parse_form($p['sample_input']) : null;
                // PHP problems often expect a result message ("1 row inserted…"), not
                // markup; only render as a page when the output actually contains tags.
                $out_is_html = $lang['key'] !== 'php' || preg_match('/<[a-z!\/][^>]*>/i', $p['sample_output']);
                ?>
                <div class="cs-io">
                    <?php if ($form_fields): ?>
                        <div class="cs-formsample">
                            <div class="cs-io-label"><?= htmlspecialchars($lang['input_label']) ?></div>
                            <div class="cs-fakeform">
                                <?php foreach ($form_fields as $f): ?>
                                    <div class="cs-ff-row">
                                        <label><?= htmlspecialchars(ucwords(str_replace(['_', '.', '-'], ' ', $f['name']))) ?> <code><?= htmlspecialchars($f['name'] . ($f['multi'] ? '[]' : '')) ?></code></label>
                                        <?php if ($f['multi']): ?>
                                            <?php foreach ($f['values'] as $v): ?>
                                                <label class="cs-ff-check"><input type="checkbox" checked disabled> <?= htmlspecialchars($v) ?></label>
                                            <?php endforeach; ?>
                                        <?php else:
                                            $v = $f['values'][0];
                                            $t = $this->Widgets_model->code_snippet_field_type($f['name'], $v); ?>
                                            <?php if ($t === 'textarea'): ?>
                                                <textarea class="form-control form-control-sm" rows="3" disabled><?= htmlspecialchars($v) ?></textarea>
                                            <?php else: ?>
                                                <input type="<?= $t ?>" class="form-control form-control-sm" value="<?= htmlspecialchars($v, ENT_QUOTES) ?>" disabled>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php elseif ($p['sample_input'] !== ''): ?>
                        <div><div class="cs-io-label"><?= htmlspecialchars($lang['input_label']) ?></div><pre class="cs-io-box"><?= htmlspecialchars($p['sample_input']) ?></pre></div>
                    <?php endif; ?>
                    <?php if ($p['sample_output'] !== '' && !$out_is_html): ?>
                        <div><div class="cs-io-label"><?= htmlspecialchars($lang['output_label']) ?></div><div class="cs-result"><?= nl2br(htmlspecialchars($p['sample_output'])) ?></div></div>
                    <?php elseif ($p['sample_output'] !== ''): ?>
                        <div class="cs-web-out">
                            <div class="cs-io-label">
                                <?= htmlspecialchars($lang['output_label']) ?>
                                <span class="cs-tabs">
                                    <button type="button" class="cs-tab active" data-cs-tab="rendered">Rendered</button><button type="button" class="cs-tab" data-cs-tab="source">Source</button>
                                </span>
                            </div>
                            <?php /* Expected output is authored HTML. sandbox="" = no scripts, no forms, unique origin: it can only draw. */ ?>
                            <iframe class="cs-frame" data-cs-pane="rendered" sandbox="" srcdoc="<?= htmlspecialchars($p['sample_output'], ENT_QUOTES) ?>" title="Expected output, rendered"></iframe>
                            <pre class="cs-io-box" data-cs-pane="source" style="display:none"><?= htmlspecialchars($p['sample_output']) ?></pre>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php if ($hide_problem && $readonly): ?>
        <div class="cs-waiting-card mb-3">
            <i class="fa fa-lock"></i>
            <?php if ($timer['phase'] === 'waiting'): ?>
                Your problem will be shown when Batch <?= (int) $timer['batch'] ?> starts<?= $timer['start_ts'] ? ' at ' . htmlspecialchars(date('g:i A', $timer['start_ts'])) : '' ?>.
            <?php else: ?>
                Your problem will be shown once your instructor assigns you to a batch.
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($readonly): ?>
        <?php if ($allow_code && !$hide_problem): ?>
            <div class="cs-optional">Submitted code</div>
            <?php if (trim($saved_code) !== ''): ?>
                <pre class="cs-code"><?= htmlspecialchars($saved_code) ?></pre>
                <?php if ($preview_kind): ?>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" data-cs-run="1"><i class="fa fa-play"></i> Preview submitted page</button>
                    <?php $this->load->view('widgets/_code_snippet_preview_panel'); ?>
                <?php endif; ?>
            <?php else: ?>
                <div class="cs-nocode"><i class="fa fa-desktop"></i> No code attached. Checked live on the student's PC.</div>
            <?php endif; ?>
        <?php endif; ?>
    <?php elseif ($is_waiting): ?>
        <div class="cs-waiting-card" id="cs-waiting-card">
            <div><i class="fa fa-hourglass-half"></i> Batch <?= (int) $timer['batch'] ?> hasn't started yet</div>
            <?php if ($timer['start_ts']): ?>
                <div class="cs-waiting-clock" id="cs-waiting-clock">--:--:--</div>
                <div class="text-muted small">Starts at <?= htmlspecialchars(date('g:i A', $timer['start_ts'])) ?>. Your problem will appear and this page will open automatically.</div>
            <?php else: ?>
                <div class="cs-waiting-clock" id="cs-waiting-clock">Waiting for your instructor to start</div>
                <div class="text-muted small">Your problem will appear and this page will open automatically as soon as the batch starts.</div>
            <?php endif; ?>
        </div>
    <?php elseif ($allow_code): ?>
        <div class="cs-optional"><i class="fa fa-paperclip"></i> Your code <em>(optional &mdash; your instructor checks it live, and you can add it later)</em></div>
        <textarea id="cs-editor" class="form-control" rows="10"><?= htmlspecialchars($saved_code !== '' ? $saved_code : $starter) ?></textarea>
        <?php if ($preview_kind): ?>
            <button type="button" class="btn btn-sm btn-outline-primary mt-2" data-cs-run="1"><i class="fa fa-play"></i> Preview</button>
            <?php $this->load->view('widgets/_code_snippet_preview_panel'); ?>
        <?php elseif ($lang['key'] === 'php'): ?>
            <div class="cs-php-note"><i class="fa fa-server"></i> PHP runs on a server, so it can't be previewed here. Test it at <code>localhost</code> in XAMPP &mdash; your instructor checks it live.</div>
        <?php endif; ?>
        <?php if ($is_open_timed): ?>
            <div class="cs-autosave-status" id="cs-autosave-status"></div>
        <?php endif; ?>
    <?php else: ?>
        <div class="cs-nocode"><i class="fa fa-desktop"></i> Your instructor will check this live on your PC. Nothing to submit.</div>
    <?php endif; ?>
</div>
<?php /* Shared by editable + read-only mode (tabs on expected output, Preview button). Delegated and guarded so several widgets on one page don't double-bind. */ ?>
<script>
(function () {
    if (window.__csWebInit) return;
    window.__csWebInit = true;

    // Console shim injected into the preview document: forwards console.* and
    // uncaught errors to the parent, which shows them under the frame.
    var SHIM = '<script>(function(){function s(t,a){parent.postMessage({csConsole:1,t:t,m:Array.prototype.map.call(a,String).join(" ")},"*");}'
        + '["log","warn","error"].forEach(function(k){var o=console[k];console[k]=function(){s(k,arguments);o.apply(console,arguments);};});'
        + 'window.onerror=function(m,u,l){s("error",[m+" (line "+l+")"]);};})();<\/script>';

    function noCloseScript(code) { return code.replace(/<\/script/gi, '<\\/script'); }

    // kind: 'html' | 'css' | 'js'. css/js run against the problem's starting HTML.
    function buildDoc(kind, code, startHtml) {
        if (kind === 'css') {
            return '<!doctype html><html><head><meta charset="utf-8"><style>' + code.replace(/<\/style/gi, '<\\/style')
                + '</style></head><body>' + startHtml + '</body></html>';
        }
        if (kind === 'js') {
            return '<!doctype html><html><head><meta charset="utf-8"></head><body>' + startHtml + SHIM
                + '<script>' + noCloseScript(code) + '<\/script></body></html>';
        }
        return SHIM + code; // html: the student's own document, shim first
    }

    document.addEventListener('click', function (e) {
        var root, tab = e.target.closest('[data-cs-tab]');
        if (tab) {
            var out = tab.closest('.cs-web-out');
            out.querySelectorAll('[data-cs-tab]').forEach(function (b) { b.classList.toggle('active', b === tab); });
            out.querySelectorAll('[data-cs-pane]').forEach(function (p) {
                p.style.display = p.getAttribute('data-cs-pane') === tab.getAttribute('data-cs-tab') ? '' : 'none';
            });
            return;
        }

        var run = e.target.closest('[data-cs-run]');
        if (!run) return;
        root = run.closest('.cs-widget');
        var kind = root.getAttribute('data-cs-preview');
        var code;
        if (root.id === 'cs-widget' && typeof window.__csCurrentCode === 'function') {
            code = window.__csCurrentCode();          // editable: what's in the editor now
        } else {
            var pre = root.querySelector('pre.cs-code'); // read-only: the submitted code
            code = pre ? pre.textContent : '';
        }
        var wrap = root.querySelector('[data-cs-preview-wrap]');
        var frame = wrap.querySelector('[data-cs-frame]');
        var con = wrap.querySelector('[data-cs-console]');
        con.textContent = '';
        con.style.display = 'none';
        wrap.style.display = 'block';
        frame.srcdoc = buildDoc(kind, code, root.getAttribute('data-cs-start') || '');
    });

    window.addEventListener('message', function (e) {
        var d = e.data;
        if (!d || d.csConsole !== 1) return;
        // Only accept messages from one of our own preview frames.
        var frames = document.querySelectorAll('[data-cs-frame]');
        for (var i = 0; i < frames.length; i++) {
            if (frames[i].contentWindow === e.source) {
                var con = frames[i].parentNode.querySelector('[data-cs-console]');
                var line = document.createElement('div');
                line.className = 'cs-c-' + d.t;
                line.textContent = (d.t === 'log' ? '' : d.t + ': ') + String(d.m).slice(0, 500);
                con.appendChild(line);
                con.style.display = 'block';
                return;
            }
        }
    });
})();
</script>
<?php if (!$readonly): ?>
<script>
(function () {
    const widget = document.getElementById('cs-widget');
    const ta = document.getElementById('cs-editor');
    const starter = <?= json_encode($starter) ?>;
    const timer = <?= json_encode($timer) ?>; // null (untimed), or {batch,start_ts,end_ts,phase}
    const assessmentId = <?= json_encode($assessment_id) ?>;
    let cm = null;

    // Sync to the SERVER's clock, not the student's PC — a wrong PC clock
    // must never affect the countdown or when auto-submit fires. Re-anchored
    // on every autosave response below (see doAutosave()).
    let clockOffsetMs = <?= json_encode(time()) ?> * 1000 - Date.now();
    function serverNowMs() { return Date.now() + clockOffsetMs; }

    function fmtClock(totalSeconds) {
        totalSeconds = Math.max(0, Math.round(totalSeconds));
        const h = Math.floor(totalSeconds / 3600);
        const m = Math.floor((totalSeconds % 3600) / 60);
        const s = totalSeconds % 60;
        const pad = n => String(n).padStart(2, '0');
        return h > 0 ? (pad(h) + ':' + pad(m) + ':' + pad(s)) : (pad(m) + ':' + pad(s));
    }

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

    window.__csCurrentCode = currentCode; // read by the shared Preview handler above

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

    // ── Waiting for the batch to start: reload once the server-computed
    // start time arrives. No editor is rendered yet, so nothing else to do. ──
    const waitingClock = document.getElementById('cs-waiting-clock');
    if (timer && timer.phase === 'waiting' && waitingClock) {
        const turnInBtn = document.getElementById('turn-in-btn');
        if (turnInBtn) turnInBtn.style.display = 'none';

        if (timer.start_ts === null) {
            // The instructor hasn't pressed Start, so there is no start time to
            // count down to — poll by reloading; the server decides when it opens.
            setTimeout(function () { location.reload(); }, 5000);
            return;
        }

        const startMs = timer.start_ts * 1000;
        const tickWaiting = function () {
            const remain = (startMs - serverNowMs()) / 1000;
            if (remain <= 0) { location.reload(); return; }
            waitingClock.textContent = fmtClock(remain);
            setTimeout(tickWaiting, 1000);
        };
        tickWaiting();
        return; // nothing below applies while waiting
    }

    // ── Open batch with a timer: countdown bar + autosave + auto-submit. ──
    if (!timer || timer.phase !== 'open') return;

    let endMs = timer.end_ts * 1000;
    const clockEl = document.getElementById('cs-timer-clock');
    const barEl = document.getElementById('cs-timer-bar');
    const statusEl = document.getElementById('cs-autosave-status');
    let autoSubmitted = false;

    function tickCountdown() {
        if (!clockEl) return;
        const remainSec = (endMs - serverNowMs()) / 1000;
        clockEl.textContent = fmtClock(remainSec);
        if (barEl) {
            barEl.classList.toggle('cs-timer-warn', remainSec <= 300 && remainSec > 60);
            barEl.classList.toggle('cs-timer-danger', remainSec <= 60);
        }
        if (remainSec <= 0 && !autoSubmitted) {
            autoSubmitted = true;
            clearInterval(countdownInterval);
            if (statusEl) statusEl.textContent = "Time's up — submitting your code...";
            if (typeof window.submitForm === 'function') {
                window.submitForm();
            }
        }
    }
    const countdownInterval = setInterval(tickCountdown, 1000);
    tickCountdown();

    function doAutosave() {
        if (autoSubmitted || !assessmentId) return;
        const body = new URLSearchParams({ assessment_id: assessmentId, code: window.getWidgetState() });
        fetch(<?= json_encode(base_url('AssessmentController/snippet_autosave')) ?>, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body
        }).then(function (r) { return r.json(); }).then(function (data) {
            if (!data) return;
            if (data.server_now) clockOffsetMs = data.server_now * 1000 - Date.now();
            if (data.end_ts) endMs = data.end_ts * 1000; // picks up a mid-session extension
            if (data.locked) { autoSubmitted = true; clearInterval(countdownInterval); return; }
            if (statusEl && data.ok) {
                statusEl.textContent = 'Autosaved at ' + new Date().toLocaleTimeString();
            }
        }).catch(function () { /* best-effort — the 3s localStorage draft is the fallback */ });
    }
    setInterval(doAutosave, 20000);
})();
</script>
<?php endif; ?>

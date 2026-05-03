<?php $this->load->view('header') ?>

<style>
:root { --iq-primary: #04AA6D; --iq-dark: #038a57; }

/* ── Section accordion ───────────────────────────────── */
.eq-section-card {
    border: 1px solid #e9ecef;
    border-radius: 8px;
    margin-bottom: 10px;
    overflow: hidden;
}
.eq-section-header {
    background: #f8f9fa;
    padding: 11px 16px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
    user-select: none;
}
.eq-section-header:hover { background: #e9ecef; }
.eq-section-title { flex: 1; font-weight: 600; font-size: 14px; color: #333; }
.eq-q-badge {
    background: var(--iq-primary);
    color: #fff;
    border-radius: 12px;
    padding: 2px 10px;
    font-size: 12px;
    font-weight: 600;
    min-width: 36px;
    text-align: center;
}
.eq-q-badge.zero { background: #ced4da; color: #555; }
.eq-chevron { color: #aaa; font-size: 11px; transition: transform .2s; }
.eq-chevron.open { transform: rotate(180deg); }

.eq-section-body { padding: 12px 16px 14px; }
.eq-section-body.collapsed { display: none; }

/* ── Question rows ───────────────────────────────────── */
.eq-q-row {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 9px 12px;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    margin-bottom: 7px;
    background: #fafafa;
    transition: border-color .15s;
}
.eq-q-row:hover { border-color: #adb5bd; }
.eq-q-num { font-weight: 700; color: var(--iq-dark); min-width: 28px; padding-top: 1px; font-size: 13px; }
.eq-q-content { flex: 1; min-width: 0; }
.eq-q-text { font-size: 13px; font-weight: 500; margin-bottom: 4px; word-break: break-word; line-height: 1.4; }
.eq-q-answer {
    font-size: 11px; color: #155724; background: #d4edda;
    display: inline-block; padding: 2px 8px; border-radius: 10px;
}
.eq-q-expl { font-size: 11px; color: #6c757d; margin-top: 3px; font-style: italic; }
.eq-q-actions { display: flex; flex-direction: column; gap: 4px; flex-shrink: 0; }

.eq-empty { text-align: center; padding: 14px; color: #aaa; font-size: 13px; font-style: italic; }

/* ── Modal: choices ──────────────────────────────────── */
.eq-choice-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 7px;
}
.eq-choice-row input[type="radio"]  { flex-shrink: 0; width: 18px; height: 18px; cursor: pointer; }
.eq-choice-row input[type="text"]   { flex: 1; }
.eq-choice-row .eq-remove-choice    { flex-shrink: 0; }
.eq-answer-hint {
    font-size: 12px; color: #6c757d; margin-bottom: 6px; display: block;
}

/* ── Toast ───────────────────────────────────────────── */
#eq-toast {
    position: fixed; bottom: 24px; right: 24px; z-index: 9999;
    padding: 11px 20px; border-radius: 8px; font-weight: 600; font-size: 14px;
    box-shadow: 0 4px 16px rgba(0,0,0,.15);
    transition: opacity .3s ease; pointer-events: none;
}
#eq-toast.success { background: var(--iq-primary); color: #fff; }
#eq-toast.error   { background: #dc3545; color: #fff; }
#eq-toast.hidden  { opacity: 0; }
</style>

<div class="container mt-3 mb-5">
    <?php $this->load->view('admin/nav_bar') ?>

    <!-- ── Page header ── -->
    <div class="d-flex align-items-center justify-content-between my-3">
        <div class="d-flex align-items-center gap-2">
            <span style="font-size:22px; margin-right:8px;">&#9998;</span>
            <div>
                <h5 class="mb-0" style="color:var(--iq-primary);">
                    <strong>Edit Questions</strong>
                </h5>
                <small class="text-muted">
                    <code><?= htmlspecialchars($topic) ?></code>
                    &mdash; <?= htmlspecialchars($topic_data['title']) ?>
                </small>
            </div>
        </div>
        <a href="<?= site_url('interactive_quiz/manage_topics') ?>"
           class="btn btn-outline-secondary btn-sm">&larr; Back to Topics</a>
    </div>

    <!-- ── Summary bar ── -->
    <?php
        $total_q = 0;
        foreach ($topic_data['sections'] as $s) $total_q += count($s['questions'] ?? []);
    ?>
    <p class="text-muted mb-3" style="font-size:13px;">
        <?= count($topic_data['sections']) ?> sections
        &bull; <span id="eq-total-count"><?= $total_q ?></span> questions total
        &bull; <a href="<?= site_url('interactive_quiz/load/' . $topic) ?>"
                  target="_blank">Preview &rarr;</a>
    </p>

    <!-- ── Section accordion ── -->
    <?php foreach ($topic_data['sections'] as $si => $section): ?>
    <?php $qcount = count($section['questions'] ?? []); ?>
    <div class="eq-section-card">
        <div class="eq-section-header" onclick="toggleSection(<?= $si ?>)">
            <span class="eq-section-title">
                <?= $si + 1 ?>. <?= htmlspecialchars($section['title']) ?>
            </span>
            <span class="eq-q-badge <?= $qcount === 0 ? 'zero' : '' ?>"
                  id="eq-badge-<?= $si ?>">
                <?= $qcount ?>Q
            </span>
            <span class="eq-chevron open" id="eq-chev-<?= $si ?>">&#9660;</span>
        </div>
        <div class="eq-section-body" id="eq-body-<?= $si ?>">
            <div id="eq-qlist-<?= $si ?>"><!-- rendered by JS --></div>
            <button type="button"
                    class="btn btn-sm btn-outline-success mt-1"
                    onclick="openModal(<?= $si ?>, null)">
                + Add Question
            </button>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Question modal ── -->
<div class="modal fade" id="eq-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:10px; border:none;">
            <div class="modal-header" style="border-bottom:1px solid #eee;">
                <h6 class="modal-title font-weight-bold" id="eq-modal-title">Add Question</h6>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="eq-modal-err" class="alert alert-danger py-2"
                     style="display:none; font-size:13px;"></div>

                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:14px;">
                        Question <span class="text-danger">*</span>
                    </label>
                    <textarea id="eq-q-text" class="form-control" rows="3"
                              placeholder="Enter the question…"></textarea>
                </div>

                <div class="form-group">
                    <label class="font-weight-bold" style="font-size:14px;">
                        Answer Choices <span class="text-danger">*</span>
                    </label>
                    <span class="eq-answer-hint">
                        &#9711; Select the radio button next to the correct answer.
                    </span>
                    <div id="eq-choices-wrap"></div>
                    <button type="button" id="eq-add-choice"
                            class="btn btn-sm btn-outline-secondary mt-1"
                            onclick="addChoice()">+ Add Choice</button>
                </div>

                <div class="form-group mb-1">
                    <label class="font-weight-bold" style="font-size:14px;">
                        Explanation
                        <small class="text-muted font-weight-normal">— optional, shown after answering</small>
                    </label>
                    <textarea id="eq-expl" class="form-control form-control-sm" rows="2"
                              placeholder="Why is that the correct answer?"></textarea>
                </div>
            </div>
            <div class="modal-footer" style="border-top:1px solid #eee;">
                <button type="button" class="btn btn-secondary btn-sm"
                        data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-sm"
                        id="eq-save-btn" onclick="saveQuestion()">
                    Save Question
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ── Toast ── -->
<div id="eq-toast" class="hidden"></div>

<script>
// ── Bootstrapped state from PHP ─────────────────────────────────────
var TOPIC    = <?= json_encode($topic_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
var TOPIC_ID = <?= json_encode($topic) ?>;
var SAVE_URL = '<?= site_url('interactive_quiz/save_question/') ?>';
var DEL_URL  = '<?= site_url('interactive_quiz/delete_question/') ?>';

// ── Modal state ─────────────────────────────────────────────────────
var mSi      = null;   // section index
var mQi      = null;   // question index (null = new)
var mChoices = [];     // current choice strings
var mAnswer  = '';     // currently marked correct answer

// ── Initial render ──────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    for (var i = 0; i < TOPIC.sections.length; i++) renderSection(i);
});

// ── Section toggle ──────────────────────────────────────────────────
function toggleSection(si) {
    var body = document.getElementById('eq-body-' + si);
    var chev = document.getElementById('eq-chev-' + si);
    var open = !body.classList.contains('collapsed');
    body.classList.toggle('collapsed', open);
    chev.classList.toggle('open', !open);
}

// ── Render question list ────────────────────────────────────────────
function renderSection(si) {
    var qs    = TOPIC.sections[si].questions || [];
    var badge = document.getElementById('eq-badge-' + si);
    badge.textContent = qs.length + 'Q';
    badge.className   = 'eq-q-badge' + (qs.length === 0 ? ' zero' : '');

    var list = document.getElementById('eq-qlist-' + si);
    if (qs.length === 0) {
        list.innerHTML = '<div class="eq-empty">No questions yet — add the first one below.</div>';
    } else {
        var html = '';
        for (var qi = 0; qi < qs.length; qi++) {
            var q = qs[qi];
            html +=
                '<div class="eq-q-row">' +
                    '<div class="eq-q-num">Q' + (qi + 1) + '</div>' +
                    '<div class="eq-q-content">' +
                        '<div class="eq-q-text">' + esc(q.question) + '</div>' +
                        '<span class="eq-q-answer">&#10003; ' + esc(q.answer) + '</span>' +
                        (q.explanation
                            ? '<div class="eq-q-expl">' + esc(q.explanation) + '</div>'
                            : '') +
                    '</div>' +
                    '<div class="eq-q-actions">' +
                        '<button type="button" class="btn btn-sm btn-outline-primary"' +
                                ' onclick="openModal(' + si + ',' + qi + ')">Edit</button>' +
                        '<button type="button" class="btn btn-sm btn-outline-danger"' +
                                ' onclick="confirmDelete(' + si + ',' + qi + ')">Delete</button>' +
                    '</div>' +
                '</div>';
        }
        list.innerHTML = html;
    }

    // Update global total
    var total = 0;
    for (var s = 0; s < TOPIC.sections.length; s++) {
        total += (TOPIC.sections[s].questions || []).length;
    }
    document.getElementById('eq-total-count').textContent = total;
}

// ── Open add/edit modal ─────────────────────────────────────────────
function openModal(si, qi) {
    mSi = si;
    mQi = qi;

    document.getElementById('eq-modal-err').style.display = 'none';
    document.getElementById('eq-modal-title').textContent =
        (qi === null ? 'Add Question' : 'Edit Question') +
        ' — ' + TOPIC.sections[si].title;

    if (qi !== null) {
        var q = TOPIC.sections[si].questions[qi];
        document.getElementById('eq-q-text').value = q.question || '';
        document.getElementById('eq-expl').value   = q.explanation || '';
        mChoices = q.choices.slice();
        mAnswer  = q.answer;
    } else {
        document.getElementById('eq-q-text').value = '';
        document.getElementById('eq-expl').value   = '';
        mChoices = ['', ''];
        mAnswer  = '';
    }

    renderChoices();
    $('#eq-modal').modal('show');
    setTimeout(function () {
        document.getElementById('eq-q-text').focus();
    }, 300);
}

// ── Render choices inside modal ─────────────────────────────────────
function renderChoices() {
    var html = '';
    for (var i = 0; i < mChoices.length; i++) {
        var checked = mChoices[i] !== '' && mChoices[i] === mAnswer;
        html +=
            '<div class="eq-choice-row" id="crow-' + i + '">' +
                '<input type="radio" name="eq-ans" data-idx="' + i + '"' +
                       (checked ? ' checked' : '') +
                       ' title="Mark as correct answer"' +
                       ' onchange="pickAnswer(' + i + ')">' +
                '<input type="text" class="form-control form-control-sm"' +
                       ' data-idx="' + i + '"' +
                       ' value="' + escAttr(mChoices[i]) + '"' +
                       ' placeholder="Choice ' + (i + 1) + '"' +
                       ' oninput="editChoice(' + i + ', this.value)">' +
                '<button type="button"' +
                        ' class="btn btn-sm btn-outline-danger eq-remove-choice"' +
                        ' onclick="removeChoice(' + i + ')"' +
                        (mChoices.length <= 2 ? ' disabled' : '') + '>' +
                    '&times;' +
                '</button>' +
            '</div>';
    }
    document.getElementById('eq-choices-wrap').innerHTML = html;
    document.getElementById('eq-add-choice').disabled = mChoices.length >= 6;
}

function pickAnswer(idx) {
    mAnswer = mChoices[idx];
}

function editChoice(idx, val) {
    var old = mChoices[idx];
    mChoices[idx] = val;
    if (mAnswer === old) mAnswer = val;
}

function addChoice() {
    if (mChoices.length >= 6) return;
    mChoices.push('');
    renderChoices();
    // Focus the new input
    var rows = document.querySelectorAll('.eq-choice-row');
    var last = rows[rows.length - 1];
    if (last) last.querySelector('input[type="text"]').focus();
}

function removeChoice(idx) {
    if (mChoices.length <= 2) return;
    if (mAnswer === mChoices[idx]) mAnswer = '';
    mChoices.splice(idx, 1);
    renderChoices();
}

// ── Save question (AJAX) ────────────────────────────────────────────
function saveQuestion() {
    // Sync text inputs → mChoices
    document.querySelectorAll('#eq-choices-wrap input[type="text"]').forEach(function (inp) {
        mChoices[parseInt(inp.dataset.idx)] = inp.value.trim();
    });
    // Sync selected radio → mAnswer
    var checked = document.querySelector('input[name="eq-ans"]:checked');
    if (checked) mAnswer = mChoices[parseInt(checked.dataset.idx)];

    var qText   = document.getElementById('eq-q-text').value.trim();
    var expl    = document.getElementById('eq-expl').value.trim();
    var filtered = mChoices.filter(function (c) { return c !== ''; });

    if (!qText)            { showErr('Question text is required.'); return; }
    if (filtered.length < 2) { showErr('At least 2 non-empty choices are required.'); return; }
    if (!mAnswer || filtered.indexOf(mAnswer) === -1) {
        showErr('Select the correct answer by clicking a radio button next to the right choice.');
        return;
    }

    var btn = document.getElementById('eq-save-btn');
    btn.disabled    = true;
    btn.textContent = 'Saving…';

    fetch(SAVE_URL + TOPIC_ID, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({
            section_index:  mSi,
            question_index: mQi,
            question:       qText,
            choices:        filtered,
            answer:         mAnswer,
            explanation:    expl
        })
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        btn.disabled    = false;
        btn.textContent = 'Save Question';
        if (!data.success) { showErr(data.error || 'Save failed.'); return; }

        // Update local state
        if (!TOPIC.sections[mSi].questions) TOPIC.sections[mSi].questions = [];
        TOPIC.sections[mSi].questions[data.question_index] = data.question;

        renderSection(mSi);
        $('#eq-modal').modal('hide');
        toast('Question saved!', 'success');
    })
    .catch(function () {
        btn.disabled    = false;
        btn.textContent = 'Save Question';
        showErr('Network error — please try again.');
    });
}

// ── Delete question ─────────────────────────────────────────────────
function confirmDelete(si, qi) {
    var qText = (TOPIC.sections[si].questions[qi].question || '').substring(0, 80);
    if (!confirm('Delete this question?\n\n“' + qText + '”')) return;

    fetch(DEL_URL + TOPIC_ID, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ section_index: si, question_index: qi })
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (!data.success) { toast(data.error || 'Delete failed.', 'error'); return; }
        TOPIC.sections[si].questions.splice(qi, 1);
        renderSection(si);
        toast('Question deleted.', 'success');
    })
    .catch(function () { toast('Network error.', 'error'); });
}

// ── Utilities ───────────────────────────────────────────────────────
function showErr(msg) {
    var el = document.getElementById('eq-modal-err');
    el.textContent    = msg;
    el.style.display  = 'block';
}

var _toastTimer = null;
function toast(msg, type) {
    var el = document.getElementById('eq-toast');
    el.textContent = msg;
    el.className   = 'eq-toast ' + type;
    clearTimeout(_toastTimer);
    _toastTimer = setTimeout(function () { el.className += ' hidden'; }, 2600);
}

function esc(s) {
    return String(s)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escAttr(s) {
    return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;');
}
</script>

<textarea id="code-editor" style="display:none;"></textarea>
<?php $this->load->view('footer') ?>

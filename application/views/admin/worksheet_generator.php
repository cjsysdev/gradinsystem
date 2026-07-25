<?php $this->load->view('header'); ?>

<div class="container mb-5">
    <div class="dashboard">
        <?php $this->load->view('profile_only'); ?>
        <?php $this->load->view('admin/nav_bar'); ?>
    </div>

    <h5 class="mb-3"><i class="fa fa-wand-magic-sparkles"></i> Worksheet Generator</h5>
    <p class="text-muted small">
        Generate classwork content with Claude, preview it, then copy the JSON into
        the Widget config field on <a href="<?= base_url('manage_assessments') ?>">Manage Assessments</a>
        (or the discussion JSON paste flow). Nothing here is saved automatically.
    </p>

    <div class="card mb-4">
        <div class="card-body">
            <form id="wg-form">
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="small mb-1">Output Type</label>
                        <select class="form-control" id="wg-type" name="type">
                            <option value="lab_worksheet">Lab Worksheet (Predict / Observe / Explain)</option>
                            <option value="worksheet_table">Worksheet Table (columns + rows)</option>
                            <option value="discussion">Interactive Discussion + Quiz</option>
                            <option value="quiz_from_worksheet">Quiz From Existing Worksheet (Bloom's)</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label class="small mb-1">Topic</label>
                        <input type="text" class="form-control" id="wg-topic" name="topic" placeholder="e.g. Bubble Sort, PHP Arrays">
                    </div>
                    <div class="form-group col-md-4">
                        <label class="small mb-1">Course (optional)</label>
                        <input type="text" class="form-control" id="wg-course" name="course" placeholder="e.g. CC104">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label class="small mb-1"># Experiments / Sections / Questions</label>
                        <input type="number" class="form-control" id="wg-count" name="count" value="5" min="1" max="60">
                    </div>
                    <div class="form-group col-md-3">
                        <label class="small mb-1">Duration (optional)</label>
                        <input type="text" class="form-control" id="wg-duration" name="duration" placeholder="e.g. 60 minutes">
                    </div>
                </div>
                <div class="form-group">
                    <label class="small mb-1">Additional Requirements (optional)</label>
                    <textarea class="form-control" id="wg-requirements" name="requirements" rows="2" placeholder="e.g. Emphasize edge cases, avoid true/false style questions, target beginner students, use a friendly tone..."></textarea>
                </div>
                <div id="wg-source-wrap" style="display:none;">
                    <div class="form-group">
                        <label class="small mb-1 d-block">Source</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="wg-source-mode" id="wg-mode-assessment" value="assessment" checked>
                            <label class="form-check-label small" for="wg-mode-assessment">From an existing assessment</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="wg-source-mode" id="wg-mode-paste" value="paste">
                            <label class="form-check-label small" for="wg-mode-paste">Paste JSON manually</label>
                        </div>
                    </div>

                    <div id="wg-source-assessment" class="border rounded p-3 mb-3 bg-light">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label class="small mb-1">Course / Section</label>
                                <select class="form-control form-control-sm" id="wg-schedule">
                                    <option value="">Select a course/section&hellip;</option>
                                    <?php foreach (($schedules ?? []) as $sched): ?>
                                        <option value="<?= (int) $sched['schedule_id'] ?>">
                                            <?= htmlspecialchars($sched['class_code'] . ' — ' . $sched['class_name'] . ' (Section ' . $sched['section'] . ')') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="small mb-1">Assessments (select one or more)</label>
                                <div id="wg-assessment-list" class="form-control form-control-sm h-auto" style="max-height:140px; overflow-y:auto;">
                                    <span class="text-muted small">Select a course/section first&hellip;</span>
                                </div>
                            </div>
                            <div class="form-group col-md-2 d-flex align-items-end">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="wg-include-submissions" checked>
                                    <label class="form-check-label small" for="wg-include-submissions">
                                        Include student submissions
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="small text-muted" id="wg-source-status"></div>
                    </div>

                    <div class="form-group">
                        <label class="small mb-1" id="wg-source-label">Loaded Source Content</label>
                        <textarea class="form-control" id="wg-source" name="source" rows="8" style="font-family: monospace; font-size: 12px;" placeholder="Pick a course/section and assessment above, or switch to &quot;Paste JSON manually&quot; and paste an existing lab_worksheet / worksheet_table / discussion JSON here."></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" id="wg-generate-btn">
                    <i class="fa fa-bolt"></i> Generate
                </button>
                <span id="wg-loading" class="text-muted ml-2" style="display:none;">
                    <i class="fa fa-spinner fa-spin"></i> Generating&hellip; larger requests (40-60 items) can take a minute or two.
                </span>
            </form>
        </div>
    </div>

    <div id="wg-error" class="alert alert-danger" style="display:none;"></div>

    <div id="wg-result-wrap" style="display:none;">
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Generated JSON</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="wg-copy-btn">
                            <i class="fa fa-copy"></i> Copy JSON
                        </button>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control" id="wg-json-output" rows="24" style="font-family: monospace; font-size: 12px;" readonly></textarea>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header">Live Preview</div>
                    <div class="card-body" id="wg-preview"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const typeSelect       = document.getElementById('wg-type');
    const topicInput       = document.getElementById('wg-topic');
    const sourceWrap       = document.getElementById('wg-source-wrap');
    const sourceAssessment = document.getElementById('wg-source-assessment');
    const sourceTextarea   = document.getElementById('wg-source');
    const sourceLabel      = document.getElementById('wg-source-label');
    const sourceStatus     = document.getElementById('wg-source-status');
    const modeRadios       = document.getElementsByName('wg-source-mode');
    const scheduleSelect   = document.getElementById('wg-schedule');
    const assessmentList   = document.getElementById('wg-assessment-list');
    const includeSubs      = document.getElementById('wg-include-submissions');
    const form             = document.getElementById('wg-form');
    const generateBtn      = document.getElementById('wg-generate-btn');
    const loading          = document.getElementById('wg-loading');
    const errorBox         = document.getElementById('wg-error');
    const resultWrap       = document.getElementById('wg-result-wrap');
    const jsonOutput       = document.getElementById('wg-json-output');
    const previewBox       = document.getElementById('wg-preview');
    const copyBtn          = document.getElementById('wg-copy-btn');

    function runScriptsIn(container) {
        container.querySelectorAll('script').forEach(oldScript => {
            const newScript = document.createElement('script');
            Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
            newScript.textContent = oldScript.textContent;
            oldScript.replaceWith(newScript);
        });
    }

    function toggleSourceField() {
        sourceWrap.style.display = (typeSelect.value === 'quiz_from_worksheet') ? '' : 'none';
    }
    typeSelect.addEventListener('change', toggleSourceField);
    toggleSourceField();

    function currentSourceMode() {
        for (const r of modeRadios) { if (r.checked) return r.value; }
        return 'assessment';
    }

    function toggleSourceMode() {
        const isAssessment = currentSourceMode() === 'assessment';
        sourceAssessment.style.display = isAssessment ? '' : 'none';
        sourceLabel.textContent = isAssessment ? 'Loaded Source Content' : 'Source Worksheet JSON';
        sourceTextarea.readOnly = false;
    }
    modeRadios.forEach(r => r.addEventListener('change', toggleSourceMode));
    toggleSourceMode();

    function checkedAssessmentIds() {
        return Array.from(assessmentList.querySelectorAll('.wg-assessment-cb:checked')).map(cb => cb.value);
    }

    scheduleSelect.addEventListener('change', function () {
        assessmentList.innerHTML = '<span class="text-muted small">Loading&hellip;</span>';
        sourceStatus.textContent = '';
        sourceTextarea.value = '';

        if (!scheduleSelect.value) {
            assessmentList.innerHTML = '<span class="text-muted small">Select a course/section first&hellip;</span>';
            return;
        }

        fetch('<?= base_url('admin/worksheet_assessments_for_schedule') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'schedule_id=' + encodeURIComponent(scheduleSelect.value)
        })
            .then(r => r.json())
            .then(data => {
                if (!data.ok || !data.assessments.length) {
                    assessmentList.innerHTML = '<span class="text-muted small">No assessments found for this section</span>';
                    return;
                }
                assessmentList.innerHTML = data.assessments.map(a => {
                    const safeTitle = a.title.replace(/&/g, '&amp;').replace(/</g, '&lt;');
                    return '<div class="form-check">' +
                        '<input class="form-check-input wg-assessment-cb" type="checkbox" value="' + a.assessment_id + '" id="wg-a-' + a.assessment_id + '">' +
                        '<label class="form-check-label small" for="wg-a-' + a.assessment_id + '">' +
                        safeTitle + ' (' + a.submission_count + ' submission' + (a.submission_count === 1 ? '' : 's') + ')' +
                        '</label></div>';
                }).join('');
            })
            .catch(() => {
                assessmentList.innerHTML = '<span class="text-muted small">Failed to load assessments</span>';
            });
    });

    function loadSourceFromAssessment() {
        const ids = checkedAssessmentIds();
        if (!ids.length) {
            sourceTextarea.value = '';
            sourceStatus.textContent = '';
            return;
        }

        sourceStatus.textContent = 'Loading source content…';
        sourceTextarea.value = '';

        const params = new URLSearchParams();
        ids.forEach(id => params.append('assessment_ids[]', id));
        params.append('include_submissions', includeSubs.checked ? '1' : '0');

        fetch('<?= base_url('admin/worksheet_source_from_assessment') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params.toString()
        })
            .then(r => r.json())
            .then(data => {
                if (!data.ok) {
                    sourceStatus.textContent = data.error || 'Failed to load source content.';
                    return;
                }
                sourceTextarea.value = data.source;
                sourceStatus.textContent = 'Loaded ' + ids.length + ' assessment' + (ids.length === 1 ? '' : 's') + '. Review/edit below before generating.';
                if (!topicInput.value.trim()) topicInput.value = data.title;
            })
            .catch(() => {
                sourceStatus.textContent = 'Failed to load source content.';
            });
    }
    // Event delegation — assessment checkboxes are created dynamically per schedule.
    assessmentList.addEventListener('change', function (e) {
        if (e.target.classList.contains('wg-assessment-cb')) loadSourceFromAssessment();
    });
    includeSubs.addEventListener('change', function () {
        if (checkedAssessmentIds().length) loadSourceFromAssessment();
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        errorBox.style.display = 'none';
        resultWrap.style.display = 'none';
        loading.style.display = '';
        generateBtn.disabled = true;

        const body = new URLSearchParams(new FormData(form));

        fetch('<?= base_url('admin/worksheet_generate') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        })
            .then(r => r.json())
            .then(data => {
                loading.style.display = 'none';
                generateBtn.disabled = false;

                if (!data.ok) {
                    errorBox.textContent = data.error || 'Generation failed.';
                    errorBox.style.display = '';
                    if (data.raw) {
                        jsonOutput.value = data.raw;
                        resultWrap.style.display = '';
                        previewBox.innerHTML = '<p class="text-muted">No preview — generated JSON did not validate.</p>';
                    }
                    return;
                }

                jsonOutput.value = data.json;
                previewBox.innerHTML = data.preview_html;
                runScriptsIn(previewBox);
                resultWrap.style.display = '';
            })
            .catch(() => {
                loading.style.display = 'none';
                generateBtn.disabled = false;
                errorBox.textContent = 'Request failed. Check your connection and try again.';
                errorBox.style.display = '';
            });
    });

    copyBtn.addEventListener('click', function () {
        jsonOutput.select();
        navigator.clipboard.writeText(jsonOutput.value).then(function () {
            const original = copyBtn.innerHTML;
            copyBtn.innerHTML = '<i class="fa fa-check"></i> Copied!';
            setTimeout(function () { copyBtn.innerHTML = original; }, 1500);
        }).catch(function () {
            document.execCommand('copy');
        });
    });
})();
</script>

<?php $this->load->view('footer'); ?>

<?php $this->load->view('header'); ?>

<div class="container mb-5">
    <?php $this->load->view('profile_only'); ?>
    <?php $this->load->view('admin/nav_bar'); ?>

    <div class="row mt-3">
        <div class="col text-center">
            <h4><i class="fa fa-bullhorn"></i> SMS Announcements</h4>
        </div>
    </div>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger mt-2"><?= htmlspecialchars($this->session->flashdata('error')) ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success mt-2"><?= htmlspecialchars($this->session->flashdata('success')) ?></div>
    <?php endif; ?>

    <?php if (!$tables_ready): ?>
        <div class="alert alert-info mt-2 d-flex justify-content-between align-items-center">
            <span>The SMS delivery tables aren't installed on this database yet.</span>
            <a href="<?= base_url('admin/sms_install') ?>" class="btn btn-sm btn-primary">Set up SMS tables</a>
        </div>
    <?php endif; ?>

    <?php if (!$is_configured): ?>
        <div class="alert alert-warning mt-2">
            <strong>PhilSMS isn't configured.</strong>
            Set the <code>PHILSMS_API_TOKEN</code> environment variable (and
            <code>PHILSMS_SENDER_ID</code>) in the WAMP/Apache environment, or edit
            <code>application/config/philsms.php</code>. Preview works without it; sending doesn't.
        </div>
    <?php endif; ?>

    <div class="row mt-3">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-body">

                    <!-- Section picker reloads the page so the hand-picked list
                         can be rendered server-side from the real roster. -->
                    <div class="form-group">
                        <label class="form-label mb-1"><strong>Section</strong></label>
                        <select id="sectionPicker" class="form-control">
                            <option value="">Choose a section…</option>
                            <?php foreach ($sections as $s): ?>
                                <option value="<?= htmlspecialchars($s['section']) ?>"
                                    <?= $selected_section === $s['section'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['section']) ?>
                                    (<?= (int) $s['student_count'] ?> students)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label mb-1"><strong>Send to</strong></label>
                        <div class="small text-muted mb-1">
                            Pick at least one of Students / Guardians — those are the phone
                            numbers. Officers and hand-picked narrow down <em>which</em> students.
                        </div>
                        <div class="form-check">
                            <input class="form-check-input audience" type="checkbox" value="students" id="audStudents">
                            <label class="form-check-label" for="audStudents">Students' own numbers</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input audience" type="checkbox" value="guardians" id="audGuardians" checked>
                            <label class="form-check-label" for="audGuardians">Guardians (emergency contacts)</label>
                        </div>
                        <hr class="my-2">
                        <div class="form-check">
                            <input class="form-check-input audience" type="checkbox" value="officers" id="audOfficers">
                            <label class="form-check-label" for="audOfficers">Class officers only</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input audience" type="checkbox" value="picked" id="audPicked">
                            <label class="form-check-label" for="audPicked">Hand-picked students only</label>
                        </div>
                    </div>

                    <div id="pickedWrap" class="form-group d-none">
                        <label class="form-label mb-1"><strong>Students</strong></label>
                        <div class="border rounded p-2" style="max-height:220px;overflow-y:auto;">
                            <?php if (empty($students)): ?>
                                <div class="text-muted small">Choose a section first.</div>
                            <?php else: ?>
                                <?php foreach ($students as $student): ?>
                                    <div class="form-check">
                                        <input class="form-check-input picked-student" type="checkbox"
                                               value="<?= (int) $student['student_id'] ?>"
                                               id="pick<?= (int) $student['student_id'] ?>">
                                        <label class="form-check-label small" for="pick<?= (int) $student['student_id'] ?>">
                                            <?= htmlspecialchars($student['name']) ?>
                                            <?php if (!$student['has_student_no']): ?>
                                                <span class="badge badge-light" title="No personal number on file">no student #</span>
                                            <?php endif; ?>
                                            <?php if (!$student['has_guardian_no']): ?>
                                                <span class="badge badge-light" title="No emergency contact on file">no guardian #</span>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label mb-1"><strong>Message</strong></label>
                        <textarea id="message" class="form-control" rows="5"
                                  placeholder="Good day! This is CMC…"></textarea>
                        <div class="small text-muted mt-1">
                            <span id="counter">0 characters · 0 credits each</span>
                            &nbsp;·&nbsp; Sender ID: <code><?= htmlspecialchars($sender_id) ?></code>
                        </div>
                        <div class="small text-muted">
                            Include the school name — parents receive this from an unfamiliar number.
                            Avoid shortened links; SMART blocks them.
                        </div>
                    </div>

                    <button type="button" id="previewBtn" class="btn btn-primary">
                        <i class="fa fa-eye"></i> Preview recipients
                    </button>

                    <hr>

                    <label class="form-label mb-1"><strong>Test send</strong></label>
                    <div class="input-group">
                        <input type="text" id="testNumber" class="form-control" placeholder="09171234567">
                        <div class="input-group-append">
                            <button type="button" id="testBtn" class="btn btn-outline-secondary"
                                <?= $is_configured ? '' : 'disabled' ?>>Send test</button>
                        </div>
                    </div>
                    <div class="small text-muted mt-1">Sends the message above to one number only.</div>
                    <div id="testResult" class="small mt-1"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="mb-2">Before sending</h6>
                    <div class="small text-muted mb-2">
                        Balance:
                        <strong><?= $balance === null ? 'unknown' : (int) $balance . ' credits' ?></strong>
                        &nbsp;·&nbsp; Cap: <strong><?= (int) $max_recipients ?></strong> per blast
                    </div>
                    <div id="previewPanel" class="text-muted small">
                        Preview a selection to see who it reaches and what it costs.
                    </div>
                    <div id="progressWrap" class="d-none mt-2">
                        <div class="progress" style="height:18px;">
                            <div id="progressBar" class="progress-bar" role="progressbar" style="width:0%">0%</div>
                        </div>
                        <div id="progressText" class="small text-muted mt-1"></div>
                    </div>
                </div>
            </div>

            <?php if (!empty($recent)): ?>
                <div class="card shadow-sm mt-3">
                    <div class="card-body">
                        <h6 class="mb-2">Recent</h6>
                        <?php foreach ($recent as $row): ?>
                            <div class="small border-bottom py-1">
                                <a href="<?= base_url('admin/sms_history/' . (int) $row['announcement_id']) ?>">
                                    <?= htmlspecialchars($row['section'] ?: '—') ?>
                                </a>
                                &nbsp;·&nbsp; <?= (int) $row['sent_count'] ?> sent
                                <?php if ((int) $row['failed_count'] > 0): ?>
                                    <span class="text-danger">· <?= (int) $row['failed_count'] ?> failed</span>
                                <?php endif; ?>
                                <div class="text-muted"><?= htmlspecialchars(mb_substr($row['message'], 0, 60)) ?>…</div>
                            </div>
                        <?php endforeach; ?>
                        <a href="<?= base_url('admin/sms_history') ?>" class="small">All announcements →</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
(function () {
    var SECTION = <?= json_encode((string) $selected_section) ?>;
    var CONFIGURED = <?= $is_configured ? 'true' : 'false' ?>;

    var messageEl = document.getElementById('message');
    var panel     = document.getElementById('previewPanel');
    var lastPreview = null;

    document.getElementById('sectionPicker').addEventListener('change', function () {
        window.location = '<?= base_url('admin/announcements') ?>?section=' + encodeURIComponent(this.value);
    });

    document.getElementById('audPicked').addEventListener('change', function () {
        document.getElementById('pickedWrap').classList.toggle('d-none', !this.checked);
    });

    // Live credit counter — mirrors Sms_message::segments() so the cost is
    // visible while typing, not after the bill.
    function segments(text) {
        var unicode = /[^\x20-\x7E\n\r]/.test(text.replace(/[£¥èéùìòÇØøÅåΔΦΓΛΩΠΨΣΘΞÆæßÉÄÖÑÜ§¿äöñüà€]/g, 'a'));
        var len = Array.from(text).length;
        if (len === 0) return { segments: 0, unicode: unicode };
        var single = unicode ? 70 : 160, multi = unicode ? 67 : 153;
        return { segments: len <= single ? 1 : Math.ceil(len / multi), unicode: unicode };
    }

    function updateCounter() {
        var info = segments(messageEl.value);
        document.getElementById('counter').textContent =
            Array.from(messageEl.value).length + ' characters · ' + info.segments +
            ' credit' + (info.segments === 1 ? '' : 's') + ' each' +
            (info.unicode ? ' (unicode — 70 chars per credit)' : '');
    }
    messageEl.addEventListener('input', updateCounter);
    updateCounter();

    function body() {
        var params = new URLSearchParams();
        params.append('section', SECTION);
        params.append('message', messageEl.value);
        document.querySelectorAll('.audience:checked').forEach(function (el) {
            params.append('audience[]', el.value);
        });
        document.querySelectorAll('.picked-student:checked').forEach(function (el) {
            params.append('picked_ids[]', el.value);
        });
        return params;
    }

    function post(url, params) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: params.toString()
        }).then(function (r) { return r.json(); });
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    document.getElementById('previewBtn').addEventListener('click', function () {
        panel.innerHTML = '<span class="text-muted">Resolving recipients…</span>';
        post('<?= base_url('admin/sms_preview') ?>', body()).then(function (data) {
            if (!data.ok) {
                panel.innerHTML = '<div class="text-danger">' + escapeHtml(data.error) + '</div>';
                lastPreview = null;
                return;
            }
            lastPreview = data;

            var html = '<div class="mb-2"><strong>' + data.valid_count + '</strong> number' +
                (data.valid_count === 1 ? '' : 's') + ' will be messaged.</div>';
            html += '<div class="mb-2">' + data.segments + ' credit' + (data.segments === 1 ? '' : 's') +
                ' × ' + data.valid_count + ' = <strong>' + data.credits_needed + ' credits</strong>' +
                (data.balance === null ? '' : ' (balance ' + data.balance + ')') + '</div>';

            if (data.sample.length) {
                html += '<div class="small text-muted mb-2">e.g. ' +
                    escapeHtml(data.sample.join(', ')) + (data.valid_count > data.sample.length ? '…' : '') +
                    '</div>';
            }

            if (data.skipped_count) {
                html += '<details class="mb-2"><summary class="text-warning">' + data.skipped_count +
                    ' skipped</summary><div class="small text-muted mt-1">';
                data.skipped.forEach(function (s) {
                    html += escapeHtml(s.who) + ' — ' + escapeHtml(s.reason) + '<br>';
                });
                html += '</div></details>';
            }

            var blocked = '';
            if (data.valid_count === 0)   blocked = 'Nobody in this selection has a usable number.';
            else if (data.over_cap)       blocked = 'Over the ' + data.cap + '-recipient cap.';
            else if (data.insufficient)   blocked = 'Not enough credits.';
            else if (!CONFIGURED)         blocked = 'PhilSMS is not configured.';

            if (blocked) {
                html += '<div class="alert alert-danger py-1 px-2 small mb-0">' + escapeHtml(blocked) + '</div>';
            } else {
                html += '<button type="button" id="sendBtn" class="btn btn-danger btn-block">' +
                    '<i class="fa fa-paper-plane"></i> Send now</button>';
            }
            panel.innerHTML = html;

            var sendBtn = document.getElementById('sendBtn');
            if (sendBtn) {
                sendBtn.addEventListener('click', startSend);
            }
        }).catch(function () {
            panel.innerHTML = '<div class="text-danger">Request failed.</div>';
        });
    });

    function startSend() {
        if (!lastPreview) return;
        if (!confirm('Send this message to ' + lastPreview.valid_count +
                     ' number(s)? This spends ' + lastPreview.credits_needed + ' credits and cannot be undone.')) {
            return;
        }

        document.getElementById('sendBtn').disabled = true;
        var wrap = document.getElementById('progressWrap');
        var bar  = document.getElementById('progressBar');
        var text = document.getElementById('progressText');
        wrap.classList.remove('d-none');

        post('<?= base_url('admin/sms_start_send') ?>', body()).then(function (start) {
            if (!start.ok) {
                text.innerHTML = '<span class="text-danger">' + escapeHtml(start.error) + '</span>';
                return;
            }

            var totals = { sent: 0, failed: 0 };

            // Sequential, one chunk at a time — the server re-resolves the
            // recipient list per chunk, so the browser only sends an index.
            function sendChunk(index) {
                if (index >= start.chunk_count) {
                    var done = new URLSearchParams();
                    done.append('announcement_id', start.announcement_id);
                    return post('<?= base_url('admin/sms_finish_send') ?>', done).then(function () {
                        bar.style.width = '100%';
                        bar.textContent = '100%';
                        text.innerHTML = '<strong>Done.</strong> ' + totals.sent + ' sent, ' +
                            totals.failed + ' failed. ' +
                            '<a href="<?= base_url('admin/sms_history/') ?>' + start.announcement_id + '">View log</a>';
                    });
                }

                var params = new URLSearchParams();
                params.append('announcement_id', start.announcement_id);
                params.append('chunk_index', index);

                return post('<?= base_url('admin/sms_send_chunk') ?>', params).then(function (chunk) {
                    totals.sent   += chunk.sent || 0;
                    totals.failed += chunk.failed || 0;

                    var pct = Math.round(((index + 1) / start.chunk_count) * 100);
                    bar.style.width = pct + '%';
                    bar.textContent = pct + '%';
                    text.textContent = totals.sent + ' sent, ' + totals.failed + ' failed…';

                    if (!chunk.ok && chunk.error) {
                        // Stop on a hard gateway failure rather than burning
                        // through every remaining chunk against a broken API.
                        text.innerHTML = '<span class="text-danger">Stopped: ' + escapeHtml(chunk.error) +
                            '</span><br>' + totals.sent + ' sent before the failure.';
                        return;
                    }
                    return sendChunk(index + 1);
                });
            }

            return sendChunk(0);
        }).catch(function () {
            text.innerHTML = '<span class="text-danger">Request failed.</span>';
        });
    }

    document.getElementById('testBtn').addEventListener('click', function () {
        var out = document.getElementById('testResult');
        var params = new URLSearchParams();
        params.append('number', document.getElementById('testNumber').value);
        params.append('message', messageEl.value);

        out.innerHTML = '<span class="text-muted">Sending…</span>';
        post('<?= base_url('admin/sms_test') ?>', params).then(function (data) {
            out.innerHTML = data.ok
                ? '<span class="text-success">Sent to ' + escapeHtml(data.sent_to) + '.</span>'
                : '<span class="text-danger">' + escapeHtml(data.error) + '</span>';
        }).catch(function () {
            out.innerHTML = '<span class="text-danger">Request failed.</span>';
        });
    });
})();
</script>

<?php $this->load->view('footer'); ?>

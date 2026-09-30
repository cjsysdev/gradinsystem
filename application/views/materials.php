<?php $this->load->view('header'); ?>

<div class="container mb-5">
    <div class="dashboard">
        <?php $this->load->view('profile_info') ?>
    </div>

    <h5 class="mb-1"><i class="fa fa-folder-open"></i> Class Materials</h5>
    <p class="small text-muted">
        Files your instructor posted for your sections — demo files, handouts and reviewers.
    </p>

    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>

    <?php if ((int) $total === 0): ?>

        <div class="card">
            <div class="card-body text-center text-muted py-5">
                <p class="mb-2" style="font-size:36px;"><i class="fa fa-folder-open"></i></p>
                <p class="mb-0">No materials have been posted for your sections yet.</p>
            </div>
        </div>

    <?php else: ?>

        <?php
        // Which extensions get an in-page preview. Everything else is
        // download-only: nothing on the server renders docx/pptx, and routing
        // them through an external viewer would ship the file off-site.
        $previewable_images = ['png', 'jpg', 'jpeg', 'gif', 'webp'];

        // Source and plain-text formats, rendered in-page with line numbers.
        // .php and .c are the bulk of what gets uploaded, and a lecture demo is
        // something you read — making a student download the file, find it, and
        // hunt for an app that opens .c is friction for the common case.
        // The .php ones sit on disk with a .txt tail (see
        // AdminMaterialController::NEUTRALIZED_EXTS), so they come back over
        // HTTP as inert text, not as executed output.
        $previewable_text = ['php', 'c', 'cpp', 'h', 'py', 'java', 'js', 'ts', 'css', 'sql', 'json', 'txt', 'md', 'csv'];

        // Viewing means holding the whole file in memory as a string. Past a
        // couple of hundred KB that is a worse experience than the download it
        // replaced, so the button simply isn't offered.
        $preview_text_max = 262144; // 256 KB

        $lang_label = [
            'php' => 'PHP', 'c' => 'C', 'cpp' => 'C++', 'h' => 'C header',
            'py' => 'Python', 'java' => 'Java', 'js' => 'JavaScript',
            'ts' => 'TypeScript', 'css' => 'CSS', 'sql' => 'SQL',
            'json' => 'JSON', 'md' => 'Markdown', 'csv' => 'CSV', 'txt' => 'Text',
        ];

        $icon_for = function ($ext) {
            $map = [
                'pdf'  => 'fa-file-pdf text-danger',
                'doc'  => 'fa-file-word text-primary',   'docx' => 'fa-file-word text-primary',
                'odt'  => 'fa-file-word text-primary',   'rtf'  => 'fa-file-word text-primary',
                'xls'  => 'fa-file-excel text-success',  'xlsx' => 'fa-file-excel text-success',
                'csv'  => 'fa-file-csv text-success',
                'ppt'  => 'fa-file-powerpoint text-warning', 'pptx' => 'fa-file-powerpoint text-warning',
                'zip'  => 'fa-file-zipper text-secondary',   'rar'  => 'fa-file-zipper text-secondary',
                '7z'   => 'fa-file-zipper text-secondary',
                'png'  => 'fa-file-image text-info', 'jpg'  => 'fa-file-image text-info',
                'jpeg' => 'fa-file-image text-info', 'gif'  => 'fa-file-image text-info',
                'webp' => 'fa-file-image text-info',
                'mp4'  => 'fa-file-video text-dark', 'webm' => 'fa-file-video text-dark',
                'mp3'  => 'fa-file-audio text-dark',
                'sql'  => 'fa-database text-secondary',
                'txt'  => 'fa-file-lines text-muted', 'md' => 'fa-file-lines text-muted',
                'php'  => 'fa-file-code text-primary', 'c'    => 'fa-file-code text-primary',
                'cpp'  => 'fa-file-code text-primary', 'h'    => 'fa-file-code text-primary',
                'py'   => 'fa-file-code text-primary', 'java' => 'fa-file-code text-primary',
                'js'   => 'fa-file-code text-warning', 'ts'   => 'fa-file-code text-warning',
                'css'  => 'fa-file-code text-info',    'json' => 'fa-file-code text-secondary',
            ];
            return $map[$ext] ?? 'fa-file-code text-muted';
        };

        $human_size = function ($bytes) {
            $bytes = (int) $bytes;
            if ($bytes >= 1048576) { return round($bytes / 1048576, 1) . ' MB'; }
            if ($bytes >= 1024)    { return round($bytes / 1024) . ' KB'; }
            return $bytes . ' B';
        };

        $CI =& get_instance();
        $CI->load->model('Class_material');
        ?>

        <?php foreach ($grouped as $category => $rows): ?>
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong><?= html_escape($category) ?></strong>
                    <span class="badge badge-secondary"><?= count($rows) ?></span>
                </div>
                <div class="list-group list-group-flush">
                    <?php foreach ($rows as $m): ?>
                        <?php
                        $rel = $CI->Class_material->relative_path($m);
                        // rawurlencode each segment, not the whole path — a
                        // course code or filename may contain characters that
                        // would otherwise break the link.
                        $url = base_url(implode('/', array_map('rawurlencode', explode('/', $rel))));
                        $ext = strtolower($m['extension']);
                        $is_pdf   = ($ext === 'pdf');
                        $is_image = in_array($ext, $previewable_images, true);
                        $is_text  = in_array($ext, $previewable_text, true)
                                    && (int) $m['file_size'] <= $preview_text_max;
                        $pdfjs = base_url('assets/pdfjs/web/viewer.html') . '?file=' . urlencode($url);
                        ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="mr-3">
                                    <div>
                                        <i class="fa <?= $icon_for($ext) ?>"></i>
                                        <strong><?= html_escape($m['title']) ?></strong>
                                        <?php if (!empty($m['class_code'])): ?>
                                            <span class="badge badge-light"><?= html_escape($m['class_code']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($m['description'])): ?>
                                        <div class="small text-muted mt-1"><?= html_escape($m['description']) ?></div>
                                    <?php endif; ?>
                                    <div class="small text-muted mt-1">
                                        <?= html_escape($m['original_name']) ?>
                                        &middot; <?= $human_size($m['file_size']) ?>
                                        &middot; <?= date('M j, Y', strtotime($m['created_at'])) ?>
                                    </div>
                                </div>

                                <div class="text-nowrap">
                                    <?php if ($is_pdf): ?>
                                        <button type="button" class="btn btn-sm btn-outline-success js-preview"
                                                data-kind="pdf"
                                                data-src="<?= html_escape($pdfjs) ?>"
                                                data-title="<?= html_escape($m['title']) ?>">
                                            <i class="fa fa-eye"></i> Preview
                                        </button>
                                        <a href="<?= html_escape($pdfjs) ?>" target="_blank" rel="noopener"
                                           class="btn btn-sm btn-outline-secondary" title="Open in a new tab">
                                            <i class="fa fa-up-right-from-square"></i>
                                        </a>
                                    <?php elseif ($is_image): ?>
                                        <button type="button" class="btn btn-sm btn-outline-success js-preview"
                                                data-kind="image"
                                                data-src="<?= html_escape($url) ?>"
                                                data-title="<?= html_escape($m['title']) ?>">
                                            <i class="fa fa-eye"></i> Preview
                                        </button>
                                    <?php elseif ($is_text): ?>
                                        <button type="button" class="btn btn-sm btn-outline-success js-preview"
                                                data-kind="text"
                                                data-src="<?= html_escape($url) ?>"
                                                data-name="<?= html_escape($m['original_name']) ?>"
                                                data-lang="<?= html_escape($lang_label[$ext] ?? strtoupper($ext)) ?>"
                                                data-title="<?= html_escape($m['title']) ?>">
                                            <i class="fa fa-eye"></i> View
                                        </button>
                                    <?php endif; ?>

                                    <a href="<?= html_escape($url) ?>"
                                       download="<?= html_escape($m['original_name']) ?>"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-download"></i> Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- One shared modal, filled on open and emptied on close. Bootstrap
             4.5.2 markup (data-dismiss / class="close"), NOT Bootstrap 5. -->
        <div class="modal fade" id="materialPreview" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title" id="materialPreviewTitle"></h6>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-0" id="materialPreviewBody"></div>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<style>
    /* Source viewer. The gutter and the code share one scroll box so the line
       numbers can never drift out of step with the lines they label — hence
       `white-space: pre` (no wrapping) and one line-height for both. */
    .material-code {
        display: flex;
        align-items: flex-start;
        max-height: 75vh;
        overflow: auto;
        background: #f8f9fa;
        font-size: 13px;
        line-height: 1.5;
    }
    .material-code pre {
        margin: 0;
        padding: 12px 0;
        font-family: SFMono-Regular, Consolas, "Liberation Mono", Menlo, monospace;
        font-size: inherit;
        line-height: inherit;
        white-space: pre;
        background: none;
        border: 0;
    }
    .material-code-gutter {
        position: sticky;   /* stays put while the code scrolls sideways */
        left: 0;
        flex: 0 0 auto;
        padding-left: 12px !important;
        padding-right: 12px !important;
        text-align: right;
        color: #adb5bd;
        background: #f1f3f5 !important;
        border-right: 1px solid #dee2e6;
        user-select: none;  /* so selecting the code doesn't drag the numbers in */
    }
    .material-code-body {
        flex: 1 1 auto;
        padding-left: 14px !important;
        padding-right: 14px !important;
        color: #212529;
    }
    .material-code-bar {
        border-bottom: 1px solid #dee2e6;
        background: #fff;
    }
    @media (max-width: 576px) {
        .material-code { font-size: 12px; }
    }
</style>

<script>
    // Vanilla JS: footer.php loads the SLIM jQuery build, which has no $.ajax
    // and no effects. Only Bootstrap's own modal (from bootstrap.bundle) is used.
    (function () {
        var modalEl = document.getElementById('materialPreview');
        if (!modalEl) return;

        var body  = document.getElementById('materialPreviewBody');
        var title = document.getElementById('materialPreviewTitle');

        // Guard for the same reason the size cap exists server-side: a file that
        // slipped through as text but isn't (a renamed binary) shouldn't lock the
        // browser up laying out a million lines.
        var MAX_CHARS = 400000;

        document.querySelectorAll('.js-preview').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var kind = btn.getAttribute('data-kind');
                var src  = btn.getAttribute('data-src');

                title.textContent = btn.getAttribute('data-title') || 'Preview';

                if (kind === 'pdf') {
                    // pdf.js rather than a raw <iframe src="file.pdf"> or
                    // <embed>: those render blank in Chrome on Android and in
                    // in-app webviews, which is how most students open this.
                    body.innerHTML = '<iframe src="' + src + '" style="width:100%;height:80vh;border:0"></iframe>';
                } else if (kind === 'text') {
                    loadSource(src,
                        btn.getAttribute('data-name') || 'file',
                        btn.getAttribute('data-lang') || '');
                } else {
                    body.innerHTML = '<img src="' + src + '" class="img-fluid d-block mx-auto" alt="">';
                }

                if (window.jQuery) { window.jQuery(modalEl).modal('show'); }
            });
        });

        function loadSource(url, name, lang) {
            body.innerHTML = '<div class="p-5 text-center text-muted">'
                + '<i class="fa fa-spinner fa-spin"></i> Loading&hellip;</div>';

            fetch(url, { credentials: 'same-origin' })
                .then(function (res) {
                    if (!res.ok) { throw new Error('HTTP ' + res.status); }
                    return res.text();
                })
                .then(function (text) { renderSource(text, url, name, lang); })
                .catch(function (err) { renderError(url, name, err); });
        }

        function renderSource(text, url, name, lang) {
            var truncated = text.length > MAX_CHARS;
            if (truncated) { text = text.slice(0, MAX_CHARS); }

            var lines = text.replace(/\r\n?/g, '\n').split('\n');
            var numbers = [];
            for (var i = 1; i <= lines.length; i++) { numbers.push(i); }

            body.innerHTML = '';
            body.appendChild(buildBar(text, url, name, lang, lines.length));

            var wrap   = document.createElement('div');
            wrap.className = 'material-code';

            var gutter = document.createElement('pre');
            gutter.className   = 'material-code-gutter';
            gutter.textContent = numbers.join('\n');

            var pre  = document.createElement('pre');
            pre.className = 'material-code-body';
            var code = document.createElement('code');
            // textContent, never innerHTML: this is the contents of an uploaded
            // file. Injecting it as markup would turn any demo containing a
            // <script> tag into stored XSS in the student's own session.
            code.textContent = text;
            pre.appendChild(code);

            wrap.appendChild(gutter);
            wrap.appendChild(pre);
            body.appendChild(wrap);

            if (truncated) {
                var note = document.createElement('div');
                note.className = 'small text-muted px-3 py-2 border-top';
                note.textContent = 'This file is too long to show in full — download it to read the rest.';
                body.appendChild(note);
            }
        }

        /** Filename + language on the left, Copy and Download on the right. */
        function buildBar(text, url, name, lang, lineCount) {
            var bar = document.createElement('div');
            bar.className = 'material-code-bar d-flex justify-content-between align-items-center px-3 py-2';

            var left = document.createElement('div');
            left.className = 'small text-muted text-truncate mr-2';
            left.textContent = name + (lang ? ' · ' + lang : '') + ' · ' + lineCount + ' lines';

            var right = document.createElement('div');
            right.className = 'text-nowrap';

            var copy = document.createElement('button');
            copy.type = 'button';
            copy.className = 'btn btn-sm btn-outline-secondary';
            copy.innerHTML = '<i class="fa fa-copy"></i> Copy';
            copy.addEventListener('click', function () {
                copyText(text, function (ok) {
                    copy.innerHTML = ok
                        ? '<i class="fa fa-check"></i> Copied'
                        : '<i class="fa fa-xmark"></i> Press Ctrl+C';
                    setTimeout(function () { copy.innerHTML = '<i class="fa fa-copy"></i> Copy'; }, 2000);
                });
            });

            var dl = document.createElement('a');
            dl.className = 'btn btn-sm btn-outline-primary ml-1';
            dl.href = url;
            dl.setAttribute('download', name);
            dl.innerHTML = '<i class="fa fa-download"></i> Download';

            right.appendChild(copy);
            right.appendChild(dl);
            bar.appendChild(left);
            bar.appendChild(right);

            return bar;
        }

        /**
         * navigator.clipboard exists only in a secure context, and students
         * reach this over http://<lan-ip>/ — so the textarea + execCommand path
         * is the one that actually runs for most of them, not a legacy fallback.
         */
        function copyText(text, done) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text)
                    .then(function () { done(true); })
                    .catch(function () { done(legacyCopy(text)); });
                return;
            }
            done(legacyCopy(text));
        }

        function legacyCopy(text) {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.setAttribute('readonly', '');
            ta.style.position = 'fixed';
            ta.style.top = '-1000px';
            document.body.appendChild(ta);
            ta.select();

            var ok = false;
            try { ok = document.execCommand('copy'); } catch (e) { ok = false; }

            document.body.removeChild(ta);
            return ok;
        }

        function renderError(url, name, err) {
            body.innerHTML = '';

            var box = document.createElement('div');
            box.className = 'p-4 text-center text-muted';

            var msg = document.createElement('p');
            msg.className = 'mb-2';
            msg.textContent = 'This file could not be opened for viewing (' + err.message + ').';

            var dl = document.createElement('a');
            dl.className = 'btn btn-sm btn-outline-primary';
            dl.href = url;
            dl.setAttribute('download', name);
            dl.innerHTML = '<i class="fa fa-download"></i> Download instead';

            box.appendChild(msg);
            box.appendChild(dl);
            body.appendChild(box);
        }

        // Emptying on close stops pdf.js and prevents iframes stacking up
        // across repeated opens.
        if (window.jQuery) {
            window.jQuery(modalEl).on('hidden.bs.modal', function () {
                body.innerHTML = '';
                title.textContent = '';
            });
        }
    })();
</script>

<?php $this->load->view('footer'); ?>

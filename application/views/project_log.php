<?php $this->load->view('header'); ?>

<?php
    // Board columns and their label/icon/colour come from
    // Project_log_model::STATUSES (passed in as $statuses) — never hardcode a
    // status list in a view.
    $me = (int) $this->session->student_id;
?>

<style>
    /* ── Kanban board ──────────────────────────────────────────────────── */
    .pl-board {
        display: flex;
        gap: 12px;
        overflow-x: auto;
        align-items: flex-start;
        padding: 2px 2px 14px;
        -webkit-overflow-scrolling: touch;
    }
    .pl-col {
        flex: 0 0 272px;
        max-width: 272px;
        background: #f1f2f4;
        border-radius: 10px;
        display: flex;
        flex-direction: column;
        border-top: 3px solid var(--pl-color, #6c757d);
    }
    .pl-col-head {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 8px 10px 6px;
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #42526e;
    }
    .pl-col-head .pl-count {
        background: #dfe1e6;
        border-radius: 10px;
        padding: 0 7px;
        font-size: .72rem;
        color: #42526e;
    }
    .pl-col-add {
        margin-left: auto;
        border: 0;
        background: transparent;
        color: #5e6c84;
        line-height: 1;
        padding: 2px 5px;
        border-radius: 4px;
        cursor: pointer;
    }
    .pl-col-add:hover { background: #dfe1e6; color: #172b4d; }
    .pl-col-body {
        padding: 4px 8px 8px;
        min-height: 70px;
        max-height: 62vh;
        overflow-y: auto;
    }
    /* header.php hides scrollbars globally (width:0); give the columns a slim
       one back, or a long column looks like it has nothing below the fold. */
    .pl-col-body::-webkit-scrollbar { width: 6px; }
    .pl-col-body::-webkit-scrollbar-thumb { background: #c1c7d0; border-radius: 3px; }
    .pl-col-body.pl-over { background: #e4e6ea; border-radius: 0 0 10px 10px; }

    .pl-card {
        background: #fff;
        border-radius: 6px;
        box-shadow: 0 1px 1px rgba(9,30,66,.25);
        padding: 8px 10px;
        margin-bottom: 8px;
        font-size: .86rem;
    }
    .pl-card[draggable="true"] { cursor: grab; }
    .pl-card.pl-dragging { opacity: .45; }
    .pl-card.pl-saving { outline: 2px dashed #17a2b8; }
    .pl-card-title { font-weight: 600; color: #172b4d; word-break: break-word; }
    .pl-card-meta { font-size: .72rem; color: #6b778c; margin-top: 2px; }
    .pl-card-desc {
        margin: 6px 0 0;
        font-size: .8rem;
        color: #42526e;
        white-space: pre-wrap;
        word-break: break-word;
    }
    .pl-card pre { font-size: .74rem; max-height: 220px; overflow: auto; margin: 6px 0 0; }
    .pl-card-act {
        border: 0;
        background: transparent;
        color: #6b778c;
        padding: 2px 6px;
        cursor: pointer;
    }
    .pl-card-act:hover { color: #172b4d; }
    .pl-card-act.pl-danger:hover { color: #dc3545; }
    .pl-card-foot {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 8px;
        font-size: .78rem;
    }
    .pl-card-foot .pl-spacer { margin-left: auto; }
    /* A native <select> is deliberate: a Bootstrap dropdown would be clipped by
       the column's own overflow-y scroll, and its popup is the one move control
       that works on a touch screen (HTML5 drag-and-drop does not). */
    .pl-move {
        font-size: .72rem;
        height: auto;
        padding: 1px 18px 1px 5px;
        max-width: 130px;
    }
    .pl-empty {
        border: 2px dashed #c1c7d0;
        border-radius: 6px;
        color: #8993a4;
        font-size: .76rem;
        text-align: center;
        padding: 14px 6px;
    }

    /* Four columns fit a desktop container, so let them share its width evenly
       rather than huddling at the left with dead space beside them. Below lg
       they stay fixed-width and the board scrolls. */
    @media (min-width: 992px) {
        .pl-col { flex: 1 1 0; max-width: none; min-width: 0; }
    }

    /* Full width on a phone; a course name doesn't need 1400px on a desktop. */
    .pl-course { max-width: 520px; }

    /* ── Column chips (mobile board nav) ───────────────────────────────── */
    .pl-nav {
        display: flex;
        gap: 6px;
        overflow-x: auto;
        padding: 2px 0 8px;
        -webkit-overflow-scrolling: touch;
    }
    .pl-nav::-webkit-scrollbar { height: 0; }
    .pl-chip {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border: 1px solid #dfe1e6;
        background: #fff;
        color: #5e6c84;
        border-radius: 16px;
        padding: 5px 11px;
        font-size: .76rem;
        line-height: 1.2;
        white-space: nowrap;
        cursor: pointer;
    }
    .pl-chip .pl-chip-count {
        background: #f1f2f4;
        border-radius: 9px;
        padding: 0 6px;
        font-size: .7rem;
    }
    .pl-chip.pl-active {
        border-color: var(--pl-color, #6c757d);
        color: var(--pl-color, #6c757d);
        font-weight: 600;
        box-shadow: inset 0 -2px 0 var(--pl-color, #6c757d);
    }

    /* ── "Moved to ..." confirmation ───────────────────────────────────── */
    /* On a narrow board the destination column is off-screen, so a moved card
       simply vanishes. This says where it went and scrolls there on tap. */
    .pl-toast {
        position: fixed;
        left: 50%;
        bottom: 18px;
        transform: translateX(-50%) translateY(12px);
        max-width: 92vw;
        background: #172b4d;
        color: #fff;
        border-radius: 20px;
        padding: 9px 16px;
        font-size: .82rem;
        box-shadow: 0 4px 14px rgba(9,30,66,.35);
        opacity: 0;
        pointer-events: none;
        transition: opacity .18s ease, transform .18s ease;
        z-index: 1080;
        cursor: pointer;
    }
    .pl-toast.pl-show { opacity: 1; transform: translateX(-50%) translateY(0); pointer-events: auto; }

    /* ── Phones ────────────────────────────────────────────────────────── */
    @media (max-width: 767.98px) {
        /* Full-bleed past the .container's 15px gutters, so a card gets the
           whole screen width instead of ~90% of it. */
        .pl-board {
            margin-left: -15px;
            margin-right: -15px;
            padding-left: 15px;
            padding-right: 15px;
            scroll-snap-type: x mandatory;
            scroll-padding-left: 15px;
        }
        .pl-col {
            flex-basis: 86vw;
            max-width: 86vw;
            scroll-snap-align: start;
        }
        /* No nested vertical scroll on touch: a scrollable column inside a
           horizontally scrollable board traps the gesture and hides its own
           overflow. The column grows and the page scrolls instead. */
        .pl-col-body {
            max-height: none;
            overflow-y: visible;
        }
        /* Comfortable touch targets. */
        .pl-card-act { padding: 7px 10px; font-size: 1rem; }
        .pl-col-add { padding: 6px 9px; font-size: .95rem; }
        .pl-card-foot { flex-wrap: wrap; row-gap: 6px; }
        /* 16px keeps iOS from zooming the page when the picker is tapped. */
        .pl-move {
            font-size: 16px;
            flex: 1 1 auto;
            max-width: none;
            padding: 5px 30px 5px 9px; /* clears .custom-select's arrow at right .75rem */
        }
        .pl-card-desc { font-size: .84rem; }
        .pl-toolbar { flex-direction: column; align-items: stretch !important; }
        .pl-toolbar .btn { width: 100%; padding: .5rem; font-size: .95rem; }
        /* CodeMirror defaults to 300px, which buries the modal's Save button. */
        .modal-body .CodeMirror { height: 160px; }
    }
</style>

<div class="container mb-5">
    <?php $this->load->view('profile_info'); ?>

    <h5 class="mb-3"><i class="fa fa-diagram-project"></i> Project Progress Log</h5>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>

    <?php if (empty($courses)): ?>
        <p class="text-muted">None of your courses has a project log set up yet, so there is nothing to log.</p>
        </div><?php $this->load->view('footer'); return; ?>
    <?php endif; ?>

    <!-- ── Course picker ─────────────────────────────────────────────── -->
    <?php if (count($courses) > 1): ?>
        <div class="form-group pl-course">
            <label class="font-weight-bold small mb-1" for="course-picker">Course</label>
            <select class="form-control" id="course-picker"
                    onchange="location.href='<?= base_url('project_log') ?>/' + this.value;">
                <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['class_id'] ?>" <?= ((int)$c['class_id'] === (int)$selected_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['class_code'] . ' — ' . $c['class_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php else: ?>
        <p class="text-muted mb-3">
            <strong><?= htmlspecialchars($selected['class_code'] . ' — ' . $selected['class_name']) ?></strong>
        </p>
    <?php endif; ?>

    <!-- ── Team banner (group mode only) ─────────────────────────── -->
    <?php if ($mode === 'group'): ?>
        <div class="alert alert-info py-2">
            <strong><i class="fa fa-people-group"></i> <?= htmlspecialchars($group['group_name']) ?></strong>
            — shared team board. Members:
            <?php foreach ($members as $m): ?>
                <span class="badge badge-light border"><?= htmlspecialchars($m['firstname'] . ' ' . $m['lastname']) ?></span>
            <?php endforeach; ?>
            <div class="small mt-1">You can move and edit your own cards; teammates' cards are read-only.</div>
        </div>
    <?php elseif ($mode === 'ungrouped'): ?>
        <div class="alert alert-warning">
            Your instructor set up teams for this course, but you're not on a team yet — ask them to add you.
        </div>
    <?php endif; ?>

    <!-- ── Board toolbar ─────────────────────────────────────────────── -->
    <div class="pl-toolbar d-flex flex-wrap align-items-center mb-2" style="gap: 10px;">
        <?php if ($mode !== 'ungrouped'): ?>
            <button type="button" class="btn btn-info btn-sm js-add" data-status="planned">
                <i class="fa fa-plus"></i> Add Entry
            </button>
        <?php endif; ?>
        <span class="text-muted small">
            <?= (int) $total ?> entr<?= ((int) $total === 1) ? 'y' : 'ies' ?> on this board
        </span>
        <span class="ml-auto text-muted small d-none d-md-inline">
            <i class="fa fa-hand-pointer"></i> Drag a card to another column, or use its
            <i class="fa fa-right-left"></i> picker.
        </span>
    </div>

    <!-- ── Column chips: the board's nav on a phone ──────────────────── -->
    <?php // Below md the board shows one column at a time; these jump between
          // them and double as the at-a-glance count of the whole board. ?>
    <div class="pl-nav d-md-none" id="pl-nav">
        <?php foreach ($statuses as $key => $meta): ?>
            <button type="button" class="pl-chip js-goto" data-goto="<?= htmlspecialchars($key) ?>"
                    style="--pl-color: <?= htmlspecialchars($meta['color']) ?>;">
                <i class="fa <?= htmlspecialchars($meta['icon']) ?>"></i>
                <?= htmlspecialchars($meta['label']) ?>
                <span class="pl-chip-count" data-count-for="<?= htmlspecialchars($key) ?>"><?= count($board[$key]) ?></span>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- ── The board ─────────────────────────────────────────────────── -->
    <div class="pl-board" id="pl-board">
        <?php foreach ($statuses as $key => $meta): ?>
            <section class="pl-col" data-status="<?= htmlspecialchars($key) ?>"
                     style="--pl-color: <?= htmlspecialchars($meta['color']) ?>;">
                <div class="pl-col-head" title="<?= htmlspecialchars($meta['hint']) ?>">
                    <i class="fa <?= htmlspecialchars($meta['icon']) ?>" style="color: <?= htmlspecialchars($meta['color']) ?>"></i>
                    <?= htmlspecialchars($meta['label']) ?>
                    <span class="pl-count" data-count-for="<?= htmlspecialchars($key) ?>"><?= count($board[$key]) ?></span>
                    <?php if ($mode !== 'ungrouped'): ?>
                        <button type="button" class="pl-col-add js-add" data-status="<?= htmlspecialchars($key) ?>"
                                title="Add a card to <?= htmlspecialchars($meta['label']) ?>">
                            <i class="fa fa-plus"></i>
                        </button>
                    <?php endif; ?>
                </div>

                <div class="pl-col-body js-dropzone" data-status="<?= htmlspecialchars($key) ?>">
                    <?php foreach ($board[$key] as $e): ?>
                        <?php
                            $own      = ((int) $e['student_id'] === $me);
                            $has_meta = !empty($e['code']) || !empty($e['link']) || !empty($e['file_upload']);
                        ?>
                        <article class="pl-card" data-log-id="<?= (int) $e['log_id'] ?>"
                                 data-status="<?= htmlspecialchars($key) ?>"
                                 <?= $own ? 'draggable="true"' : '' ?>>
                            <div class="d-flex align-items-start">
                                <div class="pl-card-title flex-grow-1"><?= htmlspecialchars($e['title']) ?></div>
                                <?php if ($own): ?>
                                    <div class="d-flex align-items-center flex-shrink-0" draggable="false">
                                        <button type="button" class="pl-card-act js-edit" title="Edit" aria-label="Edit entry"
                                            data-id="<?= $e['log_id'] ?>"
                                            data-title="<?= htmlspecialchars($e['title'], ENT_QUOTES) ?>"
                                            data-status="<?= htmlspecialchars($key, ENT_QUOTES) ?>"
                                            data-description="<?= htmlspecialchars((string)$e['description'], ENT_QUOTES) ?>"
                                            data-link="<?= htmlspecialchars((string)$e['link'], ENT_QUOTES) ?>"
                                            data-code="<?= htmlspecialchars((string)$e['code'], ENT_QUOTES) ?>">
                                            <i class="fa fa-pen"></i>
                                        </button>
                                        <form method="post" action="<?= base_url('project_log/delete/' . $e['log_id']) ?>"
                                              onsubmit="return confirm('Remove this entry?');" class="m-0">
                                            <button type="submit" class="pl-card-act pl-danger" title="Remove" aria-label="Remove entry">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="pl-card-meta">
                                <?php if ($mode === 'group' && !empty($e['firstname'])): ?>
                                    <i class="fa fa-user"></i>
                                    <span class="font-weight-bold"><?= htmlspecialchars($e['firstname'] . ' ' . $e['lastname']) ?></span>
                                    &nbsp;·&nbsp;
                                <?php endif; ?>
                                <?= date('M d, Y', strtotime($e['created_at'])) ?>
                                <?php if (!empty($e['updated_at'])): ?>
                                    &nbsp;·&nbsp;edited <?= date('M d', strtotime($e['updated_at'])) ?>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($e['description'])): ?>
                                <p class="pl-card-desc"><?= htmlspecialchars($e['description']) ?></p>
                            <?php endif; ?>

                            <?php if (!empty($e['code'])): ?>
                                <div class="collapse" id="code-<?= (int) $e['log_id'] ?>">
                                    <pre class="mb-0"><code><?= htmlspecialchars($e['code']) ?></code></pre>
                                </div>
                            <?php endif; ?>

                            <?php if ($own || $has_meta): ?>
                                <div class="pl-card-foot">
                                    <?php if ($own): ?>
                                        <select class="custom-select custom-select-sm pl-move js-move"
                                                draggable="false" aria-label="Move to another column"
                                                title="Move to another column">
                                            <?php foreach ($statuses as $mkey => $mmeta): ?>
                                                <option value="<?= htmlspecialchars($mkey) ?>" <?= ($mkey === $key) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($mmeta['label']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    <?php endif; ?>
                                    <span class="pl-spacer"></span>
                                    <?php if (!empty($e['code'])): ?>
                                        <a class="text-muted pl-card-act" data-toggle="collapse" href="#code-<?= (int) $e['log_id'] ?>"
                                           role="button" aria-expanded="false" title="Show code"><i class="fa fa-code"></i></a>
                                    <?php endif; ?>
                                    <?php if (!empty($e['link'])): ?>
                                        <a href="<?= htmlspecialchars($e['link']) ?>" target="_blank" rel="noopener"
                                           class="text-muted pl-card-act"
                                           title="<?= htmlspecialchars($e['link']) ?>"><i class="fa fa-link"></i></a>
                                    <?php endif; ?>
                                    <?php if (!empty($e['file_upload'])): ?>
                                        <a href="<?= base_url('uploads/project_logs/' . $e['file_upload']) ?>" target="_blank"
                                           rel="noopener" class="text-muted pl-card-act"
                                           title="Attachment"><i class="fa fa-paperclip"></i></a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>

                    <?php // Kept last in the column so a dropped card lands above it. ?>
                    <div class="pl-empty"<?= empty($board[$key]) ? '' : ' style="display:none"' ?>>
                        Nothing here yet
                    </div>
                </div>
            </section>
        <?php endforeach; ?>
    </div>
</div>

<div class="pl-toast" id="pl-toast" role="status" aria-live="polite"></div>

<!-- ── Add / edit entry ───────────────────────────────────────────────── -->
<?php if ($mode !== 'ungrouped'): ?>
<div class="modal fade" id="entry-modal" tabindex="-1" role="dialog" aria-labelledby="form-heading" aria-hidden="true">
    <?php // scrollable: on a phone the code editor would otherwise push Save off
          // the bottom of the screen with no way to reach it. ?>
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <form id="log-form" action="<?= base_url('project_log/save') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="class_id" value="<?= (int)$selected_id ?>">

                <div class="modal-header py-2">
                    <h6 class="modal-title" id="form-heading">Add Progress Entry</h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-8">
                            <label class="form-label">Title / Milestone <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="f-title" class="form-control" placeholder="e.g. Finished login page" required>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label">Column</label>
                            <select name="status" id="f-status" class="form-control">
                                <?php foreach ($statuses as $key => $meta): ?>
                                    <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($meta['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-text text-muted" id="f-status-hint"></small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Description / Notes</label>
                        <textarea name="description" id="f-description" class="form-control" rows="3" placeholder="What did you work on?"></textarea>
                    </div>

                    <div class="form-group mb-0">
                        <label class="form-label">Code (optional)</label>
                        <textarea name="code" id="code-editor"></textarea>
                    </div>

                    <?php // Hidden, not removed: the link input has long been commented out of
                          // the UI, but save()/update() still read `link` — dropping the field
                          // would blank the link on any entry that already has one. ?>
                    <input type="hidden" name="link" id="f-link" value="">
                </div>

                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info" id="form-submit">Add Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
    // Label + help text per status — from Project_log_model::STATUSES.
    var PL_STATUS = <?= json_encode(array_map(function ($m) {
        return ['label' => $m['label'], 'hint' => $m['hint']];
    }, $statuses)) ?>;
    var PL_MOVE_URL = '<?= base_url('project_log/set_status') ?>/';

    document.addEventListener('DOMContentLoaded', function () {
        if (window.hljs) {
            document.querySelectorAll('pre code').forEach(function (block) {
                hljs.highlightElement(block);
            });
        }

        var board = document.getElementById('pl-board');
        var nav   = document.getElementById('pl-nav');
        var toast = document.getElementById('pl-toast');

        // Matches the CSS breakpoint where the board becomes one-column-at-a-time.
        function isNarrow() {
            return window.matchMedia('(max-width: 767.98px)').matches;
        }

        // ── Column counts / empty placeholders ───────────────────────────
        // querySelectorAll, not querySelector: the column header and the mobile
        // chip both carry data-count-for and both have to stay in step.
        function refreshColumn(zone) {
            var n = zone.querySelectorAll('.pl-card').length;
            document.querySelectorAll('[data-count-for="' + zone.dataset.status + '"]').forEach(function (el) {
                el.textContent = n;
            });
            var empty = zone.querySelector('.pl-empty');
            if (empty) { empty.style.display = n ? 'none' : ''; }
        }

        // ── Horizontal scrolling between columns ─────────────────────────
        // Rect maths rather than scrollIntoView(): the board scrolls sideways
        // inside a page that scrolls down, and scrollIntoView drags the page
        // vertically too on the browsers that ignore its options.
        function scrollToColumn(col) {
            if (!board || !col) { return; }
            var pad   = parseFloat(getComputedStyle(board).paddingLeft) || 0;
            var left  = board.scrollLeft
                      + col.getBoundingClientRect().left
                      - board.getBoundingClientRect().left
                      - pad;
            try { board.scrollTo({ left: left, behavior: 'smooth' }); }
            catch (e) { board.scrollLeft = left; }
        }

        // Whichever column is nearest the middle of the board's viewport.
        function visibleColumn() {
            if (!board) { return null; }
            var mid = board.getBoundingClientRect();
            var target = mid.left + mid.width / 2;
            var best = null, bestDist = Infinity;

            board.querySelectorAll('.pl-col').forEach(function (col) {
                var r = col.getBoundingClientRect();
                var d = Math.abs(r.left + r.width / 2 - target);
                if (d < bestDist) { bestDist = d; best = col; }
            });
            return best;
        }

        function syncNav() {
            if (!nav || !board) { return; }
            var col = visibleColumn();
            if (!col) { return; }

            nav.querySelectorAll('.pl-chip').forEach(function (chip) {
                var on = chip.dataset.goto === col.dataset.status;
                chip.classList.toggle('pl-active', on);
                // Keep the active chip in view in its own scroller.
                if (on) {
                    var cr = chip.getBoundingClientRect(), nr = nav.getBoundingClientRect();
                    if (cr.left < nr.left || cr.right > nr.right) {
                        nav.scrollLeft += cr.left - nr.left - (nr.width - cr.width) / 2;
                    }
                }
            });
        }

        if (nav) {
            nav.addEventListener('click', function (e) {
                var chip = e.target.closest('.js-goto');
                if (!chip) { return; }
                scrollToColumn(board.querySelector('.pl-col[data-status="' + chip.dataset.goto + '"]'));
            });
        }

        if (board) {
            var navTick = false;
            board.addEventListener('scroll', function () {
                if (navTick) { return; }
                navTick = true;
                window.requestAnimationFrame(function () { navTick = false; syncNav(); });
            });
            window.addEventListener('resize', syncNav);
            syncNav();
        }

        // ── "Moved to ..." confirmation ──────────────────────────────────
        var toastTimer = null;
        var toastTarget = null;

        function showToast(status) {
            if (!toast) { return; }
            var label = (PL_STATUS[status] && PL_STATUS[status].label) || status;
            toast.textContent = 'Moved to ' + label + ' — tap to view';
            toastTarget = status;
            toast.classList.add('pl-show');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(function () { toast.classList.remove('pl-show'); }, 3500);
        }

        if (toast) {
            toast.addEventListener('click', function () {
                toast.classList.remove('pl-show');
                if (!toastTarget || !board) { return; }
                scrollToColumn(board.querySelector('.pl-col[data-status="' + toastTarget + '"]'));
            });
        }

        // ── Persisting a move ────────────────────────────────────────────
        // Optimistic: the card has already moved when this runs. On failure it
        // goes back exactly where it was, so the board never shows a column the
        // server didn't store.
        function persistMove(card, status, undo) {
            var body = new FormData();
            body.append('status', status);
            card.classList.add('pl-saving');

            fetch(PL_MOVE_URL + card.dataset.logId, { method: 'POST', body: body })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    card.classList.remove('pl-saving');
                    if (!res || !res.success) { throw new Error((res && res.error) || 'Move failed'); }
                    card.dataset.status = res.status;
                    // Narrow board: the destination column is off-screen, so
                    // without this the card just disappears.
                    if (isNarrow()) { showToast(res.status); }
                })
                .catch(function (err) {
                    card.classList.remove('pl-saving');
                    undo();
                    alert('Could not move that card: ' + err.message);
                });
        }

        function moveCard(card, zone) {
            var from       = card.parentNode;
            var next       = card.nextElementSibling;
            var fromStatus = card.dataset.status;
            var picker     = card.querySelector('.js-move');

            zone.insertBefore(card, zone.firstElementChild);
            card.dataset.status = zone.dataset.status;
            if (picker) { picker.value = zone.dataset.status; }
            refreshColumn(from);
            refreshColumn(zone);

            persistMove(card, zone.dataset.status, function () {
                from.insertBefore(card, next);
                card.dataset.status = fromStatus;
                if (picker) { picker.value = fromStatus; }
                refreshColumn(from);
                refreshColumn(zone);
            });
        }

        if (board) {
            // ── Drag and drop (pointer devices) ──────────────────────────
            var dragged = null;

            board.addEventListener('dragstart', function (e) {
                // Never start a drag from a control inside the card.
                if (e.target.closest('select, button, a, input, textarea')) {
                    e.preventDefault();
                    return;
                }
                var card = e.target.closest('.pl-card[draggable="true"]');
                if (!card) { return; }
                dragged = card;
                card.classList.add('pl-dragging');
                e.dataTransfer.effectAllowed = 'move';
                // Firefox refuses to start a drag with no payload set.
                e.dataTransfer.setData('text/plain', card.dataset.logId);
            });

            board.addEventListener('dragend', function () {
                if (dragged) { dragged.classList.remove('pl-dragging'); }
                dragged = null;
                board.querySelectorAll('.pl-over').forEach(function (z) { z.classList.remove('pl-over'); });
            });

            board.addEventListener('dragover', function (e) {
                var zone = dragged ? e.target.closest('.js-dropzone') : null;
                if (!zone) { return; }
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                zone.classList.add('pl-over');
            });

            board.addEventListener('dragleave', function (e) {
                var zone = e.target.closest('.js-dropzone');
                if (zone && !zone.contains(e.relatedTarget)) { zone.classList.remove('pl-over'); }
            });

            board.addEventListener('drop', function (e) {
                var zone = dragged ? e.target.closest('.js-dropzone') : null;
                if (!zone) { return; }
                e.preventDefault();
                zone.classList.remove('pl-over');
                if (zone.dataset.status !== dragged.dataset.status) { moveCard(dragged, zone); }
            });

            // ── Move via the card's picker ───────────────────────────────
            // HTML5 drag-and-drop doesn't exist on touch screens, so this is
            // the only way to move a card on a phone — not a nicety.
            board.addEventListener('change', function (e) {
                var picker = e.target.closest('.js-move');
                if (!picker) { return; }
                var card = picker.closest('.pl-card');
                var zone = board.querySelector('.js-dropzone[data-status="' + picker.value + '"]');
                if (card && zone && zone.dataset.status !== card.dataset.status) { moveCard(card, zone); }
            });
        }

        // ── Add / edit form ──────────────────────────────────────────────
        var form = document.getElementById('log-form');
        if (!form) { return; } // ungrouped: the form isn't rendered at all

        var heading    = document.getElementById('form-heading');
        var submitBtn  = document.getElementById('form-submit');
        var statusSel  = document.getElementById('f-status');
        var statusHint = document.getElementById('f-status-hint');
        var addAction  = '<?= base_url('project_log/save') ?>';
        var updateBase = '<?= base_url('project_log/update') ?>/';

        function setCode(val) {
            // window.editor is the CodeMirror instance created in footer.php.
            if (window.editor) { window.editor.setValue(val || ''); }
            else { document.getElementById('code-editor').value = val || ''; }
        }

        function showHint() {
            var meta = PL_STATUS[statusSel.value];
            statusHint.textContent = meta ? meta.hint : '';
        }
        statusSel.addEventListener('change', showHint);

        function openModal() {
            showHint();
            if (window.jQuery) { window.jQuery('#entry-modal').modal('show'); }
        }

        // CodeMirror measures itself when it is created, and footer.php creates
        // it inside this hidden modal — without the refresh it renders as a
        // 0-height sliver the first time the modal is opened.
        if (window.jQuery) {
            window.jQuery('#entry-modal').on('shown.bs.modal', function () {
                if (window.editor) { window.editor.refresh(); }
            });
        }

        document.addEventListener('click', function (e) {
            var add = e.target.closest('.js-add');
            if (add) {
                form.action = addAction;
                heading.textContent = 'Add Progress Entry';
                submitBtn.textContent = 'Add Entry';
                document.getElementById('f-title').value = '';
                statusSel.value = add.dataset.status || 'planned';
                document.getElementById('f-description').value = '';
                document.getElementById('f-link').value = '';
                setCode('');
                openModal();
                return;
            }

            var edit = e.target.closest('.js-edit');
            if (edit) {
                form.action = updateBase + edit.dataset.id;
                heading.textContent = 'Edit Progress Entry';
                submitBtn.textContent = 'Save Changes';
                document.getElementById('f-title').value = edit.dataset.title;
                statusSel.value = edit.dataset.status || 'planned';
                document.getElementById('f-description').value = edit.dataset.description;
                document.getElementById('f-link').value = edit.dataset.link;
                setCode(edit.dataset.code);
                openModal();
            }
        });
    });
</script>

<?php $this->load->view('footer'); ?>

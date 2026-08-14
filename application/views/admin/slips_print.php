<?php
/**
 * Printable Grade & Attendance slips.
 *
 * A standalone document — no header.php / footer.php / nav_bar.php — in the
 * same house style as section_grades.php, because print chrome and screen
 * chrome do not mix. Nothing here loads from a CDN: this gets printed on the
 * campus LAN.
 *
 * Every value arrives pre-rendered from AdminController::_slip_rows(), which
 * takes them from Grade_calculator. There is no arithmetic in this file, and no
 * provisional figure can reach it — the controller never passes a grade mode,
 * so an incomplete term reads INC.
 *
 * Layout: two 5.5in x 8.5in half sheets side by side on a landscape sheet, cut
 * down the dashed guide. Single-student mode prints one half sheet on its own.
 *
 * @var array  $slips       one entry per student (see _slip_rows())
 * @var array  $sched       section/subject/semester heading data
 * @var string $term        midterm|tentative-final|final
 * @var string $term_label  the same, as printed
 * @var bool   $single      TRUE when only one student's slip was asked for
 */
$semester_text = isset($sched['semester']['description']) ? $sched['semester']['description'] : '';
// Beyond this the slip prints "... and N more" rather than running past the
// bottom of the half sheet. Measured against the tallest possible slip (INC
// reason + pending note + all four components): 8 rows is the last one that
// still fits inside 8.5in with the footer. The Absent tile always shows the
// true total, and the list says how many rows it is not showing.
$max_absences  = 6;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CARMEN MUNICIPAL COLLEGE - GRADE &amp; ATTENDANCE SLIP</title>
    <link rel="icon" href="<?= base_url('assets/logo.png') ?>" type="image/png">
    <link rel="stylesheet" href="<?= base_url('assets/fontawesome/css/all.min.css') ?>">

    <style>
        * { box-sizing: border-box; }

        body {
            background: #dfe4ea;
            font-family: -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        /* ===== Screen-only toolbar ===== */
        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #22304a;
            color: #fff;
            padding: 10px 16px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            font-size: .85rem;
        }
        .toolbar .grow { flex: 1; }
        .toolbar select {
            font: inherit;
            padding: 4px 8px;
            border-radius: 4px;
            border: 1px solid #4a5a75;
        }
        .toolbar .btn {
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            border: 0;
            border-radius: 4px;
            padding: 6px 16px;
            background: #2f6fed;
            color: #fff;
            text-decoration: none;
        }
        .toolbar .btn.ghost { background: transparent; border: 1px solid #6b7686; }
        .toolbar .hint { color: #b9c3d3; font-size: .75rem; }

        .screen-wrap { padding: 18px 12px 60px; }

        /* ===== Sheet = one piece of paper ===== */
        .sheet {
            width: 11in;
            height: 8.5in;
            margin: 0 auto 18px;
            background: #fff;
            box-shadow: 0 4px 18px rgba(0, 0, 0, .18);
            display: flex;
        }
        .sheet.single { width: 5.5in; }

        /* ===== The half sheet itself ===== */
        .slip {
            width: 5.5in;
            height: 8.5in;
            padding: .35in .4in;
            overflow: hidden;   /* a long slip is clipped, never reflowed onto page 2 */
            display: flex;
            flex-direction: column;
        }
        .slip + .slip { border-left: 1px dashed #b6bec9; }
        .slip-blank { background: #fff; }

        .slip-header {
            text-align: center;
            border-bottom: 2px solid #2f6fed;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .slip-header img { height: 34px; margin-bottom: 3px; }
        .slip-header .school {
            font-size: .95rem;
            font-weight: 800;
            color: #22304a;
            margin: 0;
        }
        .slip-header .addr { font-size: .62rem; color: #6b7686; }
        .slip-header .slip-title {
            font-size: .72rem;
            color: #6b7686;
            text-transform: uppercase;
            letter-spacing: .05em;
            margin: 2px 0 0;
        }

        .info-grid { font-size: .74rem; margin-bottom: 10px; }
        .info-grid .line { display: flex; margin-bottom: 2px; }
        .info-grid .line > div { min-width: 0; }
        .info-grid .wide { flex: 0 0 60%; }
        .info-grid .narrow { flex: 0 0 40%; }
        .info-label { color: #6b7686; font-weight: 600; }
        .info-value { color: #22304a; font-weight: 700; }

        .section-title {
            font-size: .72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: #22304a;
            border-left: 3px solid #2f6fed;
            padding-left: 7px;
            margin: 4px 0 6px;
        }

        /* ===== Grade summary ===== */
        .final-grade-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f0f5ff;
            border: 1px solid #d5e2ff;
            border-radius: 8px;
            padding: 8px 12px;
            margin-bottom: 8px;
        }
        .final-grade-number {
            font-size: 1.8rem;
            font-weight: 800;
            color: #2f6fed;
            line-height: 1;
        }
        .final-grade-number.inc { font-size: 1.5rem; color: #c0392b; }
        .final-grade-cap {
            font-size: .62rem;
            color: #6b7686;
            text-transform: uppercase;
            margin-top: 3px;
        }
        .remark-badge {
            font-size: .68rem;
            padding: 3px 12px;
            border-radius: 20px;
            font-weight: 700;
            border: 1px solid transparent;
        }
        .remark-passed { background: #e3f7e9; color: #1e8a4c; border-color: #b7e6c8; }
        .remark-failed { background: #fdeaea; color: #c0392b; border-color: #f3c4c4; }
        .remark-inc    { background: #fff6e0; color: #9a7008; border-color: #f0dda6; }

        .inc-reason {
            font-size: .66rem;
            color: #c0392b;
            margin: -4px 0 8px;
        }
        .pending-note {
            font-size: .62rem;
            color: #9a7008;
            background: #fff9ea;
            border: 1px solid #f0dda6;
            border-radius: 4px;
            padding: 3px 6px;
            margin: -4px 0 8px;
        }

        table.grade-table, table.absence-table {
            width: 100%;
            font-size: .72rem;
            border-collapse: collapse;
        }
        table.grade-table { margin-bottom: 8px; }
        table.grade-table th, table.absence-table th {
            text-align: left;
            font-size: .62rem;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: #6b7686;
            border-bottom: 1.5px solid #eef1f5;
            padding: 3px 3px;
        }
        table.grade-table td, table.absence-table td {
            padding: 2px 3px;
            border-bottom: 1px solid #f3f4f7;
        }
        table.absence-table td { padding: 2px 3px; }
        table.grade-table td.num, table.grade-table th.num { text-align: right; }
        table.grade-table tr.total td {
            border-top: 1.5px solid #e2e6ee;
            border-bottom: 0;
            font-weight: 800;
            color: #22304a;
        }
        .muted { color: #98a1b0; }

        /* ===== Attendance ===== */
        .att-tiles { display: flex; gap: 4px; margin-bottom: 4px; }
        .att-tile {
            flex: 1;
            text-align: center;
            background: #f8f9fb;
            border: 1px solid #eceff4;
            border-radius: 6px;
            padding: 5px 1px;
        }
        .att-tile .num { font-size: .95rem; font-weight: 800; color: #22304a; }
        .att-tile .lbl { font-size: .55rem; color: #6b7686; text-transform: uppercase; }
        .att-present .num { color: #1e8a4c; }
        .att-absent .num { color: #c0392b; }
        .att-rate .num { color: #2f6fed; }
        .att-caption { font-size: .58rem; color: #8592a6; margin-bottom: 8px; }

        .no-absences {
            font-size: .72rem;
            color: #1e8a4c;
            font-style: italic;
            padding: 5px 3px;
        }
        .more-absences { font-size: .62rem; color: #8592a6; padding: 3px; }

        /* ===== Footer ===== */
        .slip-footer {
            margin-top: auto;
            padding-top: 12px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            font-size: .6rem;
            color: #8592a6;
        }
        .slip-footer .stamp { max-width: 2.5in; }
        .sig-line {
            border-top: 1px solid #c9cfd8;
            padding-top: 2px;
            width: 1.9in;
            text-align: center;
            font-size: .62rem;
            color: #6b7686;
        }
        .sig-line strong { display: block; color: #22304a; font-size: .66rem; }

        /* ===== Print ===== */
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
            .screen-wrap { padding: 0; }
            .sheet {
                box-shadow: none;
                margin: 0;
                page-break-after: always;
            }
            .sheet:last-child { page-break-after: auto; }

            /* Tinted blocks carry meaning (INC, Passed/Failed), so force them
               to print; every one also has a border for printers that refuse. */
            .final-grade-row, .remark-badge, .att-tile, .pending-note {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            @page { size: 11in 8.5in; margin: 0; }
        }

        <?php if ($single): ?>
        @media print {
            /* One student: a half sheet on its own, not a landscape sheet with
               a blank right half. */
            @page { size: 5.5in 8.5in; margin: 0; }
        }
        <?php endif; ?>
    </style>
</head>

<body>

    <div class="toolbar no-print">
        <strong><?= htmlspecialchars($sched['section']) ?> &mdash; <?= htmlspecialchars($sched['class_code']) ?></strong>
        <span class="hint">
            <?= count($slips) ?> slip<?= count($slips) === 1 ? '' : 's' ?>
            &middot; <?= $single ? 'one half sheet' : '2 per landscape sheet' ?>
        </span>

        <form method="GET" action="<?= base_url('admin/print_slips') ?>" style="display:flex;align-items:center;gap:6px;">
            <input type="hidden" name="schedule_id" value="<?= (int) $schedule_id ?>">
            <?php if ($student_id): ?>
                <input type="hidden" name="student_id" value="<?= (int) $student_id ?>">
            <?php endif; ?>
            <label for="term" style="margin:0;">Term</label>
            <select name="term" id="term" onchange="this.form.submit()">
                <option value="midterm" <?= $term === 'midterm' ? 'selected' : '' ?>>Midterm</option>
                <option value="tentative-final" <?= $term === 'tentative-final' ? 'selected' : '' ?>>Tentative Final</option>
                <option value="final" <?= $term === 'final' ? 'selected' : '' ?>>Final</option>
            </select>
        </form>

        <span class="grow"></span>
        <span class="hint">Official grades &mdash; incomplete terms print as INC</span>
        <a class="btn ghost" href="<?= base_url('view_attendance?schedule_id=' . (int) $schedule_id) ?>">Back</a>
        <button type="button" class="btn" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
    </div>

    <p class="no-print" style="text-align:center;font-size:.78rem;color:#5a6577;margin:10px;">
        In the print dialog choose <strong><?= $single ? 'Portrait' : 'Landscape' ?></strong>,
        paper <strong>Letter</strong>, margins <strong>None</strong>, and tick
        <strong>Background graphics</strong>.
        <?= $single ? '' : 'Cut along the dashed line.' ?>
    </p>

    <div class="screen-wrap">
        <?php foreach (array_chunk($slips, $single ? 1 : 2) as $sheet): ?>
            <div class="sheet<?= $single ? ' single' : '' ?>">
                <?php foreach ($sheet as $s): ?>
                    <div class="slip">

                        <!-- ============ HEADER ============ -->
                        <div class="slip-header">
                            <img src="<?= base_url('assets/cmc_logo_no_bg.png') ?>" alt="CMC">
                            <p class="school">Carmen Municipal College</p>
                            <div class="addr">Pob. Norte, Carmen, Bohol</div>
                            <p class="slip-title">Grade &amp; Attendance Slip</p>
                        </div>

                        <!-- ============ STUDENT INFO ============ -->
                        <div class="info-grid">
                            <div class="line">
                                <div class="wide"><span class="info-label">Name:</span>
                                    <span class="info-value"><?= htmlspecialchars($s['fullname']) ?></span></div>
                                <div class="narrow"><span class="info-label">ID:</span>
                                    <span class="info-value"><?= htmlspecialchars($s['student_no'] !== '' ? $s['student_no'] : '—') ?></span></div>
                            </div>
                            <div class="line">
                                <div class="wide"><span class="info-label">Program/Section:</span>
                                    <span class="info-value"><?= htmlspecialchars(trim($s['course'] . ' ' . $sched['section'])) ?></span></div>
                                <div class="narrow"><span class="info-label">Term:</span>
                                    <span class="info-value"><?= htmlspecialchars($term_label) ?></span></div>
                            </div>
                            <div class="line">
                                <div class="wide"><span class="info-label">Subject:</span>
                                    <span class="info-value"><?= htmlspecialchars($sched['class_code']) ?></span>
                                    <span class="muted">(<?= htmlspecialchars($sched['type']) ?>)</span></div>
                                <div class="narrow"><span class="info-label">No.:</span>
                                    <span class="info-value"><?= (int) $s['n'] ?></span></div>
                            </div>
                            <div class="line">
                                <div class="wide"><span class="info-label">Semester:</span>
                                    <span class="info-value"><?= htmlspecialchars($semester_text) ?></span></div>
                            </div>
                        </div>

                        <!-- ============ GRADE SUMMARY ============ -->
                        <div class="section-title"><?= htmlspecialchars($term_label) ?> Grade</div>

                        <div class="final-grade-row">
                            <div>
                                <div class="final-grade-number<?= $s['grade_point'] === 'INC' ? ' inc' : '' ?>">
                                    <?= htmlspecialchars($s['grade_point']) ?>
                                </div>
                                <div class="final-grade-cap">
                                    Grade Point
                                    <?php if ($s['percentage'] !== 'INC'): ?>
                                        &middot; <?= htmlspecialchars($s['percentage']) ?>%
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="remark-badge remark-<?= $s['remark']['key'] ?>">
                                <?= htmlspecialchars($s['remark']['label']) ?>
                            </span>
                        </div>

                        <?php if ($s['inc_reason'] !== ''): ?>
                            <div class="inc-reason"><?= htmlspecialchars($s['inc_reason']) ?></div>
                        <?php endif; ?>

                        <?php if ($s['pending_count'] > 0): ?>
                            <div class="pending-note">
                                <?= (int) $s['pending_count'] ?> submission<?= $s['pending_count'] === 1 ? '' : 's' ?>
                                not yet graded &mdash; counted as 0 in this computation.
                            </div>
                        <?php endif; ?>

                        <table class="grade-table">
                            <thead>
                                <tr>
                                    <th>Component</th>
                                    <th class="num">Wt</th>
                                    <th class="num">Score</th>
                                    <th class="num">%</th>
                                    <th class="num">Contrib.</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($s['components'] as $c): ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($c['name']) ?>
                                            <?php if ($c['n_ungraded'] > 0): ?>
                                                <span class="muted">(<?= (int) $c['n_ungraded'] ?> ungraded)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="num"><?= htmlspecialchars($c['weight']) ?>%</td>
                                        <?php if ($c['n_assessments'] < 1): ?>
                                            <td class="num muted" colspan="3">none recorded</td>
                                        <?php else: ?>
                                            <td class="num"><?= htmlspecialchars($c['score']) ?>/<?= htmlspecialchars($c['max']) ?></td>
                                            <td class="num">
                                                <?= $c['percentage'] === null ? '<span class="muted">&mdash;</span>' : htmlspecialchars(number_format($c['percentage'], 2)) ?>
                                            </td>
                                            <td class="num">
                                                <?= $c['contribution'] === null ? '<span class="muted">&mdash;</span>' : htmlspecialchars(number_format($c['contribution'], 2)) ?>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="total">
                                    <td colspan="4">Term Standing</td>
                                    <td class="num"><?= htmlspecialchars($s['percentage']) ?><?= $s['percentage'] === 'INC' ? '' : '%' ?></td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- ============ ATTENDANCE SUMMARY ============ -->
                        <div class="section-title">Attendance</div>

                        <div class="att-tiles">
                            <div class="att-tile att-present">
                                <div class="num"><?= (int) $s['attendance']['present'] ?></div>
                                <div class="lbl">Present</div>
                            </div>
                            <div class="att-tile">
                                <div class="num"><?= (int) $s['attendance']['late'] ?></div>
                                <div class="lbl">Late</div>
                            </div>
                            <div class="att-tile">
                                <div class="num"><?= (int) $s['attendance']['excuse'] ?></div>
                                <div class="lbl">Excused</div>
                            </div>
                            <div class="att-tile att-absent">
                                <div class="num"><?= (int) $s['attendance']['absent'] ?></div>
                                <div class="lbl">Absent</div>
                            </div>
                            <div class="att-tile att-rate">
                                <div class="num"><?= $s['attendance']['rate'] === null ? '—' : (int) $s['attendance']['rate'] . '%' ?></div>
                                <div class="lbl">Rate</div>
                            </div>
                        </div>
                        <div class="att-caption">
                            Rate = present &divide; <?= (int) $s['attendance']['sessions'] ?> recorded session<?= $s['attendance']['sessions'] === 1 ? '' : 's' ?>
                            this semester.
                        </div>

                        <table class="absence-table">
                            <thead>
                                <tr>
                                    <th style="width:38%;">Date Absent</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($s['absences'])): ?>
                                    <tr>
                                        <td colspan="2" class="no-absences">No recorded absences.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach (array_slice($s['absences'], 0, $max_absences) as $a): ?>
                                        <tr>
                                            <td><?= htmlspecialchars(date('M d, Y', strtotime($a['date']))) ?></td>
                                            <td>
                                                <?php if (trim((string) $a['reason']) === ''): ?>
                                                    <span class="muted">&mdash; no reason recorded</span>
                                                <?php else: ?>
                                                    <?= htmlspecialchars($a['reason']) ?>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (count($s['absences']) > $max_absences): ?>
                                        <tr>
                                            <td colspan="2" class="more-absences">
                                                &hellip; and <?= count($s['absences']) - $max_absences ?> more
                                                (see the full attendance record).
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>

                        <!-- ============ FOOTER / SIGNATURE ============ -->
                        <div class="slip-footer">
                            <div class="stamp">
                                Generated <?= htmlspecialchars($generated) ?>
                                <?php if ($printed_by !== ''): ?>
                                    <br>by <?= htmlspecialchars($printed_by) ?>
                                <?php endif; ?>
                            </div>
                            <div class="sig-line">
                                <strong><?= htmlspecialchars($sched['instructor']) ?></strong>
                                Instructor
                            </div>
                        </div>

                    </div>
                <?php endforeach; ?>

                <?php if (!$single && count($sheet) === 1): ?>
                    <!-- Odd roster: the empty half keeps the cut guide on the page. -->
                    <div class="slip slip-blank"></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>

</body>

</html>

<?php
// Admin — class-wide item analysis for the `quiz` and `secure_quiz` widgets.
// Fed by AdminSubmissionController::quiz_stats() / Widgets_model::quiz_item_stats().
// Styling deliberately mirrors interactive_quiz_analytics_view.php, which is the
// same report for the iq_discussion widget, so the two read as one feature.
$stats   = $stats ?? null;
$switch  = $switch ?? ['tracked' => 0, 'flagged' => 0, 'max' => 0];
$ranking = $ranking ?? null;

// Trailing zeros off a score, so a whole 18.00 prints as "18" but a hand-entered
// 17.5 keeps its half mark.
if (!function_exists('qs_score')) {
    function qs_score($n)
    {
        return rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
    }
}
?>
<?php $this->load->view('header'); ?>

<style>
:root { --qs-primary: #04AA6D; --qs-dark: #038a57; }

.stat-card {
    border: none;
    border-radius: 10px;
    text-align: center;
    padding: 18px 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
}
.stat-card .stat-num { font-size: 34px; font-weight: 700; line-height: 1; }
.stat-card .stat-lbl { font-size: 13px; color: #666; margin-top: 4px; }
.stat-card.green  { background: #e8f5e9; }
.stat-card.blue   { background: #e3f2fd; }
.stat-card.yellow { background: #fffde7; }
.stat-card.red    { background: #fdecea; }

.qs-card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
    padding: 20px;
    margin-bottom: 24px;
    background: #fff;
}
.qs-card h6 {
    font-weight: 700;
    color: var(--qs-dark);
    margin-bottom: 16px;
    font-size: 15px;
    border-left: 4px solid var(--qs-primary);
    padding-left: 10px;
}

.miss-rate-bar { height: 8px; border-radius: 4px; background: #e0e0e0; overflow: hidden; margin-top: 3px; }
.miss-rate-fill { height: 100%; border-radius: 4px; background: #dc3545; }
.miss-low  { background: #28a745; }
.miss-mid  { background: #ffc107; }
.miss-high { background: #dc3545; }

.qs-item-row { cursor: pointer; }
.qs-item-row:hover { background: #f6f9f7; }
.qs-answer-bar { height: 6px; border-radius: 3px; background: #e0e0e0; overflow: hidden; }
.qs-answer-fill { height: 100%; border-radius: 3px; background: #dc3545; }
.qs-answer-fill.correct { background: #28a745; }
.qs-drill { background: #fafafa; }
.qs-scope-tab {
    padding: 6px 15px; border-radius: 20px; font-size: 14px; font-weight: 500;
    border: 2px solid var(--qs-primary); color: var(--qs-primary); background: #fff;
    text-decoration: none; margin-right: 8px;
}
.qs-scope-tab:hover { background: #e8f5e9; color: var(--qs-dark); text-decoration: none; }
.qs-scope-tab.active { background: var(--qs-primary); color: #fff; }

.no-data-msg { text-align: center; padding: 40px 20px; color: #aaa; font-size: 15px; }

/* Full student ranking table */
.qs-rank-table td { vertical-align: middle; }
.qs-rank-num { color: #999; font-weight: 600; font-size: 13px; }
.qs-rank-num.qs-rank-podium { color: var(--qs-primary); font-size: 15px; }
.qs-tie { color: #bbb; font-weight: 400; margin-left: 1px; }
.qs-score-bar { height: 6px; border-radius: 3px; background: #e9ecef; overflow: hidden; }
.qs-score-fill { height: 100%; border-radius: 3px; }
.qs-rank-tools { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
.qs-rank-tools input { max-width: 260px; }
.qs-summary-line {
    font-size: 13px; color: #666; background: #fafafa;
    border-radius: 6px; padding: 8px 12px; margin-bottom: 16px;
}
.qs-summary-line strong { color: #333; }
</style>

<div class="container">
    <?php $this->load->view('profile_only'); ?>
    <?php $this->load->view('admin/nav_bar'); ?>

    <div class="row mt-3">
        <div class="col">
            <h4 class="mb-1">
                <i class="fa fa-chart-bar text-success"></i> Quiz Item Statistics
            </h4>
            <?php if (!empty($assessment)): ?>
                <p class="text-muted mb-2">
                    <?= htmlspecialchars($assessment['title'] ?? 'Untitled') ?>
                    <?php if (!empty($widget)): ?>
                        &mdash; <span class="badge badge-secondary"><?= htmlspecialchars($widget['name']) ?></span>
                    <?php endif; ?>
                </p>
            <?php endif; ?>
            <?php if (!empty($assessment_id)): ?>
                <a href="<?= base_url('AdminController/all_submissions/' . $assessment_id) ?>" class="btn btn-sm btn-outline-secondary mb-3">
                    <i class="fa fa-arrow-left"></i> Back to submissions
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($error) ?></div>
        <?php $this->load->view('footer'); ?>
        <?php return; ?>
    <?php endif; ?>

    <?php // Scope toggle — only meaningful when the master is assigned to more than one section. ?>
    <?php if ($section_count > 1): ?>
        <div class="mb-3">
            <a href="<?= base_url('admin/quiz_stats/' . $assessment_id . '/section') ?>"
               class="qs-scope-tab <?= $scope === 'section' ? 'active' : '' ?>">This section</a>
            <a href="<?= base_url('admin/quiz_stats/' . $assessment_id . '/all') ?>"
               class="qs-scope-tab <?= $scope === 'all' ? 'active' : '' ?>">All <?= $section_count ?> sections</a>
            <small class="text-muted ml-2">
                Each student is served a random slice of the question bank, so pooling sections gives each item more data.
            </small>
        </div>
    <?php endif; ?>

    <?php if (empty($stats['submission_count'])): ?>
        <div class="no-data-msg">
            <i class="fa fa-inbox fa-2x d-block mb-2"></i>
            No submissions yet for this quiz.
        </div>
        <?php $this->load->view('footer'); ?>
        <?php return; ?>
    <?php endif; ?>

    <!-- Summary -->
    <div class="row mb-4">
        <div class="col-6 col-md-3 mb-2">
            <div class="stat-card blue">
                <div class="stat-num"><?= (int) $stats['submission_count'] ?></div>
                <div class="stat-lbl">Submissions</div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-2">
            <div class="stat-card yellow">
                <div class="stat-num"><?= (int) $stats['bank_count'] ?></div>
                <div class="stat-lbl">Questions in bank</div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-2">
            <div class="stat-card blue">
                <div class="stat-num"><?= number_format($stats['totals']['answers']) ?></div>
                <div class="stat-lbl">Answers recorded</div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-2">
            <?php $acc = (float) $stats['totals']['accuracy']; ?>
            <div class="stat-card <?= $acc >= 70 ? 'green' : ($acc >= 50 ? 'yellow' : 'red') ?>">
                <div class="stat-num"><?= $acc ?>%</div>
                <div class="stat-lbl">Overall accuracy</div>
            </div>
        </div>
    </div>

    <?php // Tab-switch readout — only shown once some submission actually carries the data. ?>
    <?php if (!empty($switch['tracked'])): ?>
        <div class="alert <?= $switch['flagged'] > 0 ? 'alert-warning' : 'alert-light border' ?> py-2">
            <i class="fa fa-eye"></i>
            <strong>Tab switches:</strong>
            <?= (int) $switch['flagged'] ?> of <?= (int) $switch['tracked'] ?> tracked submission(s) left the quiz tab at least once<?= $switch['max'] > 0 ? ', highest count ' . (int) $switch['max'] : '' ?>.
            <small class="text-muted d-block">
                Client-reported and easy to fake &mdash; a prompt to look closer, not evidence, and it never affects a score.
            </small>
        </div>
    <?php endif; ?>

    <!-- Worst items chart -->
    <?php $top = array_slice($stats['items'], 0, 10); ?>
    <?php if (!empty($top)): ?>
        <div class="qs-card">
            <h6>Most-missed items (top <?= count($top) ?>)</h6>
            <div style="height: <?= max(180, count($top) * 34) ?>px;">
                <canvas id="missChart"></canvas>
            </div>
        </div>
    <?php endif; ?>

    <!-- Item table -->
    <div class="qs-card">
        <h6>All items &mdash; worst first</h6>
        <p class="text-muted small">
            Click a row to see exactly which answers students chose. &ldquo;Shown&rdquo; is how many submissions
            contained that question, not how many students took the quiz.
        </p>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width:38px;">#</th>
                        <th>Question</th>
                        <th class="text-right" style="width:70px;">Shown</th>
                        <th class="text-right" style="width:80px;">Correct</th>
                        <th class="text-right" style="width:70px;">Wrong</th>
                        <th class="text-right" style="width:95px;">No answer</th>
                        <th style="width:170px;">Miss rate</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($stats['items'] as $rank => $item): ?>
                    <?php
                    $mr = (float) $item['miss_rate'];
                    $fill_class = $mr >= 60 ? 'miss-high' : ($mr >= 30 ? 'miss-mid' : 'miss-low');
                    $mr_color   = $mr >= 60 ? '#dc3545' : ($mr >= 30 ? '#856404' : '#155724');
                    ?>
                    <tr class="qs-item-row" data-toggle="collapse" data-target="#qsDrill<?= $rank ?>">
                        <td class="text-muted"><?= $rank + 1 ?></td>
                        <td>
                            <?= htmlspecialchars($item['question']) ?>
                            <?php if ($item['bank_index'] === null): ?>
                                <span class="badge badge-warning ml-1" title="This question is no longer in the assessment's question bank — it was edited or removed after these students submitted.">not in current bank</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-right"><?= (int) $item['shown'] ?></td>
                        <td class="text-right text-success"><?= (int) $item['correct'] ?></td>
                        <td class="text-right text-danger"><?= (int) $item['wrong'] ?></td>
                        <td class="text-right text-muted"><?= (int) $item['no_answer'] ?></td>
                        <td>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <div class="miss-rate-bar" style="flex:1;">
                                    <div class="miss-rate-fill <?= $fill_class ?>" style="width:<?= $mr ?>%;"></div>
                                </div>
                                <span style="min-width:44px; font-weight:600; color:<?= $mr_color ?>;"><?= $mr ?>%</span>
                            </div>
                        </td>
                    </tr>
                    <tr class="collapse qs-drill" id="qsDrill<?= $rank ?>">
                        <td colspan="7">
                            <p class="mb-2 small">
                                <i class="fa fa-check text-success"></i>
                                Correct answer: <strong><?= htmlspecialchars($item['correct_answer']) ?></strong>
                            </p>
                            <?php if (empty($item['answers'])): ?>
                                <p class="text-muted small mb-0">No answers recorded &mdash; every student skipped this item.</p>
                            <?php else: ?>
                                <table class="table table-sm mb-0" style="background:transparent;">
                                    <tbody>
                                    <?php foreach ($item['answers'] as $a): ?>
                                        <tr>
                                            <td style="width:45%;">
                                                <?php if ($a['is_correct']): ?>
                                                    <i class="fa fa-check text-success"></i>
                                                <?php else: ?>
                                                    <i class="fa fa-times text-danger"></i>
                                                <?php endif; ?>
                                                <?= htmlspecialchars($a['answer']) ?>
                                            </td>
                                            <td class="text-right" style="width:60px;"><?= (int) $a['count'] ?></td>
                                            <td>
                                                <div class="qs-answer-bar">
                                                    <div class="qs-answer-fill <?= $a['is_correct'] ? 'correct' : '' ?>" style="width:<?= $a['pct'] ?>%;"></div>
                                                </div>
                                            </td>
                                            <td class="text-right text-muted small" style="width:60px;"><?= $a['pct'] ?>%</td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if ($item['no_answer'] > 0): ?>
                                        <tr class="text-muted">
                                            <td><i class="fa fa-minus"></i> <em>No answer</em></td>
                                            <td class="text-right"><?= (int) $item['no_answer'] ?></td>
                                            <td colspan="2" class="small">skipped or ran out of time</td>
                                        </tr>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (!empty($stats['never_shown'])): ?>
        <div class="qs-card">
            <h6>Never served to anyone (<?= count($stats['never_shown']) ?>)</h6>
            <p class="text-muted small">
                The bank holds <?= (int) $stats['bank_count'] ?> questions but each student is served a random
                <?= (int) ($assessment['max_score'] ?? 0) ?>-question slice, so these simply never came up.
                No data is not the same as everyone getting them right.
            </p>
            <ul class="mb-0 small">
                <?php foreach ($stats['never_shown'] as $q): ?>
                    <li><?= htmlspecialchars($q['question']) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if (!empty($stats['score_dist'])): ?>
        <div class="qs-card">
            <h6>Score distribution</h6>
            <div style="height:220px;"><canvas id="scoreChart"></canvas></div>
            <p class="text-muted small mb-0 mt-2">
                Items answered correctly per submission, counted from the stored results.
            </p>
        </div>
    <?php endif; ?>

    <?php // Full per-student ranking, highest first. Equal scores share a rank. ?>
    <?php if (!empty($ranking['count'])): ?>
        <?php $show_section = $scope === 'all' && $section_count > 1; ?>
        <div class="qs-card">
            <h6>Student ranking &mdash; all <?= (int) $ranking['count'] ?> submissions</h6>

            <div class="qs-summary-line">
                Highest <strong><?= qs_score($ranking['highest']) ?></strong>,
                lowest <strong><?= qs_score($ranking['lowest']) ?></strong>,
                average <strong><?= qs_score($ranking['average']) ?></strong>,
                median <strong><?= qs_score($ranking['median']) ?></strong>
                out of <?= (int) $ranking['max_score'] ?>.
                <?php if ($show_section): ?>
                    Pooled across all <?= (int) $section_count ?> sections.
                <?php endif; ?>
            </div>

            <div class="qs-rank-tools">
                <input type="text" id="qsRankFilter" class="form-control form-control-sm"
                       placeholder="Filter by student name&hellip;" autocomplete="off">
                <span class="text-muted small" id="qsRankCount"></span>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover qs-rank-table mb-0" id="qsRankTable">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:56px;">Rank</th>
                            <th>Student</th>
                            <?php if ($show_section): ?><th style="width:90px;">Section</th><?php endif; ?>
                            <th class="text-right" style="width:90px;">Score</th>
                            <th class="text-right" style="width:70px;">%</th>
                            <th style="width:150px;">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($ranking['students'] as $i => $e): ?>
                        <?php
                        $pct  = $e['percent'];
                        $tone = $pct === null ? 'muted' : ($pct >= 70 ? 'success' : ($pct >= 50 ? 'warning' : 'danger'));
                        $bar  = $pct === null ? 0 : max(0, min(100, $pct));
                        ?>
                        <tr data-name="<?= htmlspecialchars(strtolower($e['name']), ENT_QUOTES, 'UTF-8') ?>">
                            <td class="qs-rank-num<?= $e['rank'] <= 3 ? ' qs-rank-podium' : '' ?>">
                                <?= (int) $e['rank'] ?><?= $e['tied'] ? '<span class="qs-tie" title="Tied with another student on the same score">=</span>' : '' ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($e['name']) ?>
                                <?php if (!$e['graded']): ?>
                                    <span class="badge badge-warning ml-1" title="No score recorded on this submission — the figure shown is a count of correct items.">ungraded</span>
                                <?php endif; ?>
                                <?php if (!empty($e['switch_count'])): ?>
                                    <span class="badge badge-light border text-muted ml-1" title="Client-reported tab switches during the quiz. Never affects a score.">
                                        <i class="fa fa-eye"></i> <?= (int) $e['switch_count'] ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($e['unanswered'] > 0): ?>
                                    <small class="text-muted d-block"><?= (int) $e['unanswered'] ?> item(s) left unanswered</small>
                                <?php endif; ?>
                            </td>
                            <?php if ($show_section): ?>
                                <td class="text-muted small"><?= htmlspecialchars($e['section'] ?? '—') ?></td>
                            <?php endif; ?>
                            <td class="text-right">
                                <strong><?= qs_score($e['score']) ?></strong><span class="text-muted small">/<?= (int) $e['max_score'] ?></span>
                            </td>
                            <td class="text-right text-<?= $tone ?>">
                                <?= $pct === null ? '&mdash;' : $pct . '%' ?>
                            </td>
                            <td>
                                <div class="qs-score-bar">
                                    <div class="qs-score-fill bg-<?= $tone === 'muted' ? 'secondary' : $tone ?>" style="width:<?= $bar ?>%;"></div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="text-muted small mb-0 mt-2" id="qsRankEmpty" style="display:none;">No student matches that filter.</p>

            <p class="text-muted small mb-0 mt-2">
                Ranked on the recorded score, so any manual adjustment is reflected; equal scores share a
                rank (marked <span class="qs-tie">=</span>). Students who never submitted are not listed
                &mdash; see the submissions page for who is missing.
            </p>
        </div>
    <?php endif; ?>
</div>

<script src="<?= base_url('assets/chart.js') ?>"></script>
<script>
(function () {
    <?php if (!empty($top)): ?>
    // Truncate long question text so the axis stays readable.
    const missLabels = <?= json_encode(array_map(function ($i) {
        $q = $i['question'];
        return mb_strlen($q) > 60 ? mb_substr($q, 0, 57) . '...' : $q;
    }, $top)) ?>;
    const missRates = <?= json_encode(array_map(function ($i) { return (float) $i['miss_rate']; }, $top)) ?>;
    const missShown = <?= json_encode(array_map(function ($i) { return (int) $i['shown']; }, $top)) ?>;

    new Chart(document.getElementById('missChart'), {
        type: 'bar',
        data: {
            labels: missLabels,
            datasets: [{
                label: 'Miss rate %',
                data: missRates,
                backgroundColor: missRates.map(r => r >= 60 ? '#dc3545' : (r >= 30 ? '#ffc107' : '#28a745'))
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        afterLabel: ctx => 'shown in ' + missShown[ctx.dataIndex] + ' submission(s)'
                    }
                }
            },
            scales: { x: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } } }
        }
    });
    <?php endif; ?>

    <?php if (!empty($stats['score_dist'])): ?>
    new Chart(document.getElementById('scoreChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_map('strval', array_keys($stats['score_dist']))) ?>,
            datasets: [{
                label: 'Students',
                data: <?= json_encode(array_values($stats['score_dist'])) ?>,
                backgroundColor: '#04AA6D'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { title: { display: true, text: 'Items correct' } },
                y: { beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });
    <?php endif; ?>

    // Ranking filter — the whole cohort is rendered, so a class of 50 needs a
    // way to jump to one student without scrolling.
    const rankFilter = document.getElementById('qsRankFilter');
    if (rankFilter) {
        const rows     = Array.from(document.querySelectorAll('#qsRankTable tbody tr'));
        const countEl  = document.getElementById('qsRankCount');
        const emptyEl  = document.getElementById('qsRankEmpty');

        const apply = () => {
            const q = rankFilter.value.trim().toLowerCase();
            let shown = 0;
            rows.forEach(tr => {
                const hit = !q || (tr.dataset.name || '').indexOf(q) !== -1;
                tr.style.display = hit ? '' : 'none';
                if (hit) shown++;
            });
            countEl.textContent = q ? shown + ' of ' + rows.length + ' shown' : '';
            emptyEl.style.display = shown === 0 ? '' : 'none';
        };

        rankFilter.addEventListener('input', apply);
    }
})();
</script>

<?php $this->load->view('footer'); ?>

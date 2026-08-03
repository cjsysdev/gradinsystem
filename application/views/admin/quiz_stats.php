<?php
// Admin — class-wide item analysis for the `quiz` and `secure_quiz` widgets.
// Fed by AdminSubmissionController::quiz_stats() / Widgets_model::quiz_item_stats().
// Styling deliberately mirrors interactive_quiz_analytics_view.php, which is the
// same report for the iq_discussion widget, so the two read as one feature.
$stats  = $stats ?? null;
$switch = $switch ?? ['tracked' => 0, 'flagged' => 0, 'max' => 0];
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
})();
</script>

<?php $this->load->view('footer'); ?>

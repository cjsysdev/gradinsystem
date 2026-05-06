<?php $this->load->view('header') ?>

<style>
:root { --iq-primary: #04AA6D; --iq-dark: #038a57; }

/* ── Topic tabs ───────────────────────────────────── */
.iq-topic-tabs { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 24px; }
.iq-topic-tab {
    padding: 7px 16px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
    border: 2px solid var(--iq-primary);
    color: var(--iq-primary);
    background: #fff;
    text-decoration: none;
    transition: background .15s, color .15s;
}
.iq-topic-tab:hover { background: #e8f5e9; color: var(--iq-dark); text-decoration: none; }
.iq-topic-tab.active { background: var(--iq-primary); color: #fff; }

/* ── Summary cards ────────────────────────────────── */
.stat-card {
    border: none;
    border-radius: 10px;
    text-align: center;
    padding: 18px 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
}
.stat-card .stat-num { font-size: 38px; font-weight: 700; line-height: 1; }
.stat-card .stat-lbl { font-size: 13px; color: #666; margin-top: 4px; }
.stat-card.green  { background: #e8f5e9; }
.stat-card.blue   { background: #e3f2fd; }
.stat-card.yellow { background: #fffde7; }

/* ── Section chart card ───────────────────────────── */
.iq-chart-card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
    padding: 20px;
    margin-bottom: 24px;
}
.iq-chart-card h6 {
    font-weight: 700;
    color: var(--iq-dark);
    margin-bottom: 16px;
    font-size: 15px;
    border-left: 4px solid var(--iq-primary);
    padding-left: 10px;
}

/* ── Missed questions table ───────────────────────── */
.miss-rate-bar {
    height: 8px;
    border-radius: 4px;
    background: #e0e0e0;
    overflow: hidden;
    margin-top: 3px;
}
.miss-rate-fill { height: 100%; border-radius: 4px; background: #dc3545; }
.miss-low  { background: #28a745; }
.miss-mid  { background: #ffc107; }
.miss-high { background: #dc3545; }

.no-data-msg {
    text-align: center;
    padding: 40px 20px;
    color: #aaa;
    font-size: 15px;
}
</style>

<div class="container mt-3 mb-5">
     <?php
    $this->load->view('profile_only');
    $this->load->view('admin/nav_bar');
    ?>

    <div class="d-flex align-items-center my-3">
        <img src="<?= base_url('assets/streak.png') ?>" height="28" alt="" class="mr-2">
        <h5 class="mb-0" style="color:var(--iq-primary);">
            <strong>Interactive Quiz Analytics</strong>
        </h5>
    </div>

    <?php if (empty($available_topics)): ?>
        <div class="no-data-msg">
            <p>No topics found in <code>assets/json/</code>.</p>
        </div>
    <?php else: ?>

    <!-- ── Topic selector ── -->
    <div class="iq-topic-tabs">
        <?php foreach ($available_topics as $slug => $label): ?>
        <a href="<?= site_url('interactive_quiz/analytics/' . $slug) ?>"
           class="iq-topic-tab <?= $slug === $topic ? 'active' : '' ?>">
            <?= htmlspecialchars($label) ?>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if ($summary['total'] == 0): ?>
    <!-- ── No data yet ── -->
    <div class="iq-chart-card">
        <div class="no-data-msg">
            <p style="font-size:40px;">&#128202;</p>
            <p>No attempt data yet for <strong><?= htmlspecialchars($topic_title) ?></strong>.</p>
            <p>Data will appear here once students start answering questions.</p>
            <a href="<?= site_url('interactive_quiz/load/' . $topic) ?>"
               class="btn btn-success mt-2">Preview topic &rarr;</a>
        </div>
    </div>

    <?php else: ?>

    <!-- ── Summary cards ── -->
    <div class="row mb-4">
        <div class="col-4">
            <div class="stat-card green">
                <div class="stat-num" style="color:var(--iq-primary);">
                    <?= number_format($summary['total']) ?>
                </div>
                <div class="stat-lbl">Total Answers</div>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card blue">
                <div class="stat-num" style="color:#1565c0;">
                    <?= number_format($summary['students']) ?>
                </div>
                <div class="stat-lbl">Students</div>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card yellow">
                <div class="stat-num" style="color:#f57f17;">
                    <?= $summary['accuracy'] ?>%
                </div>
                <div class="stat-lbl">Overall Accuracy</div>
            </div>
        </div>
    </div>

    <!-- ── Section accuracy chart ── -->
    <?php if (!empty($sections)): ?>
    <div class="iq-chart-card">
        <h6>Section Accuracy</h6>
        <canvas id="sectionChart" height="<?= max(80, count($sections) * 42) ?>"></canvas>
    </div>
    <?php endif; ?>

    <!-- ── Most-missed questions ── -->
    <div class="iq-chart-card">
        <h6>Most-Missed Questions <small class="text-muted font-weight-normal">(top 10 by miss rate)</small></h6>

        <?php if (empty($missed)): ?>
            <p class="text-muted text-center py-3">No question data yet.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm" style="font-size:14px;">
                <thead>
                    <tr>
                        <th style="width:32px;">#</th>
                        <th>Section</th>
                        <th>Question</th>
                        <th style="width:80px; text-align:right;">Attempts</th>
                        <th style="width:110px;">Miss Rate</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($missed as $rank => $q): ?>
                <?php
                    $mr = (float)$q['miss_rate'];
                    $fill_class = $mr >= 60 ? 'miss-high' : ($mr >= 30 ? 'miss-mid' : 'miss-low');
                ?>
                <tr>
                    <td class="text-muted"><?= $rank + 1 ?></td>
                    <td><small><?= htmlspecialchars($q['section_title']) ?></small></td>
                    <td><?= htmlspecialchars($q['question_text']) ?></td>
                    <td style="text-align:right;"><?= number_format($q['total']) ?></td>
                    <td>
                        <div style="display:flex; align-items:center; gap:6px;">
                            <div class="miss-rate-bar" style="flex:1;">
                                <div class="miss-rate-fill <?= $fill_class ?>"
                                     style="width:<?= $mr ?>%;"></div>
                            </div>
                            <span style="min-width:36px; font-weight:600;
                                         color:<?= $mr>=60 ? '#dc3545' : ($mr>=30 ? '#856404' : '#155724') ?>">
                                <?= $mr ?>%
                            </span>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <?php endif; /* has data */ ?>
    <?php endif; /* has topics */ ?>
</div>

<?php if (!empty($sections) && $summary['total'] > 0): ?>
<script src="<?= base_url('assets/chart.js') ?>"></script>
<script>
(function () {
    const labels   = <?= json_encode(array_column($sections, 'section_title')) ?>;
    const accuracy = <?= json_encode(array_map(function($s){ return (float)$s['accuracy']; }, $sections)) ?>;
    const totals   = <?= json_encode(array_map(function($s){ return (int)$s['total']; }, $sections)) ?>;

    new Chart(document.getElementById('sectionChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Accuracy %',
                data: accuracy,
                backgroundColor: accuracy.map(function(v) {
                    return v >= 70 ? 'rgba(4,170,109,.75)'
                         : v >= 40 ? 'rgba(255,193,7,.85)'
                         :           'rgba(220,53,69,.75)';
                }),
                borderRadius: 4,
                borderSkipped: false
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    min: 0, max: 100,
                    ticks: { callback: function(v) { return v + '%'; } },
                    grid: { color: 'rgba(0,0,0,.06)' }
                },
                y: { grid: { display: false } }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            var idx = ctx.dataIndex;
                            return ' ' + ctx.parsed.x + '% correct  (' + totals[idx] + ' answers)';
                        }
                    }
                }
            }
        }
    });
})();
</script>
<?php endif; ?>

<textarea id="code-editor" style="display:none;"></textarea>
<?php $this->load->view('footer') ?>

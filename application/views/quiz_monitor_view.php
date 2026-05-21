<?php $this->load->view('header'); ?>

<div class="container mt-3">
    <div class="dashboard">
        <?php $this->load->view('profile_only'); ?>
        <?php $this->load->view('admin/nav_bar'); ?>
    </div>

    <div class="d-flex justify-content-between align-items-start mt-3 mb-2">
        <div>
            <h5 class="mb-0">Live Monitor &mdash; <?= htmlspecialchars($assessment->title) ?></h5>
            <small class="text-muted">Max score: <?= (int)$assessment->max_score ?> &bull; Auto-refreshes every 5 seconds</small>
        </div>
        <span id="lastUpdated" class="text-muted small mt-1"></span>
    </div>

    <!-- Summary cards -->
    <div class="row mb-3">
        <div class="col-4">
            <div class="card border-secondary text-center py-2">
                <div class="card-body p-1">
                    <h4 id="cnt-not-started" class="mb-0">—</h4>
                    <small class="text-muted">Not Started</small>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-warning text-center py-2">
                <div class="card-body p-1">
                    <h4 id="cnt-answering" class="mb-0 text-warning">—</h4>
                    <small class="text-muted">Answering</small>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="card border-success text-center py-2">
                <div class="card-body p-1">
                    <h4 id="cnt-submitted" class="mb-0 text-success">—</h4>
                    <small class="text-muted">Submitted</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Monitor table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0" id="monitorTable">
                    <thead class="thead-dark">
                        <tr>
                            <th>Student</th>
                            <th>Status</th>
                            <th style="min-width:160px;">Progress</th>
                            <th title="Times the student left the quiz window">Peeks</th>
                            <th>Time Active</th>
                            <th>Score</th>
                        </tr>
                    </thead>
                    <tbody id="monitorBody">
                        <?php foreach ($enrolled as $s): ?>
                        <tr id="row-<?= (int)$s['student_id'] ?>">
                            <td><?= htmlspecialchars($s['lastname'] . ', ' . $s['firstname']) ?></td>
                            <td><span class="badge badge-secondary">Not Started</span></td>
                            <td><small class="text-muted">—</small></td>
                            <td>0</td>
                            <td>—</td>
                            <td>—</td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($enrolled)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">No students enrolled for this assessment.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
var ASSESSMENT_ID = <?= (int)$assessment_id ?>;
var MAX_SCORE     = <?= (int)$assessment->max_score ?>;
var LIVE_URL      = '<?= site_url('quiz_monitor/live_data/') ?>';

function fmtTime(seconds) {
    if (seconds === null || seconds === undefined || seconds < 0) return '—';
    var m = Math.floor(seconds / 60);
    var s = seconds % 60;
    return m + 'm ' + (s < 10 ? '0' : '') + s + 's';
}

function statusBadge(status) {
    if (status === 'submitted') return '<span class="badge badge-success">Submitted</span>';
    if (status === 'answering') return '<span class="badge badge-warning text-dark">Answering</span>';
    return '<span class="badge badge-secondary">Not Started</span>';
}

function progressCell(student) {
    if (student.status === 'not_started') return '<small class="text-muted">—</small>';
    var total = student.total_items || MAX_SCORE || 1;
    var pct   = Math.min(100, Math.round((student.items_answered / total) * 100));
    var bar   = student.status === 'submitted' ? 'bg-success' : 'bg-info';
    return '<div class="d-flex align-items-center">'
        + '<div class="progress flex-grow-1 mr-2" style="height:14px;">'
        + '<div class="progress-bar ' + bar + '" style="width:' + pct + '%"></div>'
        + '</div>'
        + '<small class="text-nowrap">' + student.items_answered + '&nbsp;/&nbsp;' + total + '</small>'
        + '</div>';
}

function rowClass(student) {
    if (student.status === 'submitted') return 'table-success';
    if (student.status === 'answering') {
        return student.blur_count >= 3 ? 'table-danger' : 'table-warning';
    }
    return '';
}

function refreshMonitor() {
    fetch(LIVE_URL + ASSESSMENT_ID)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            document.getElementById('cnt-not-started').textContent = data.summary.not_started;
            document.getElementById('cnt-answering').textContent   = data.summary.answering;
            document.getElementById('cnt-submitted').textContent   = data.summary.submitted;
            document.getElementById('lastUpdated').textContent     = 'Updated ' + data.server_time;

            data.students.forEach(function(s) {
                var row = document.getElementById('row-' + s.student_id);
                if (!row) return;

                row.className        = rowClass(s);
                row.cells[1].innerHTML  = statusBadge(s.status);
                row.cells[2].innerHTML  = progressCell(s);
                row.cells[3].textContent = s.blur_count;
                row.cells[4].textContent = fmtTime(s.elapsed_seconds);

                if (s.status === 'submitted' && s.score !== null) {
                    row.cells[5].textContent = s.score + ' / ' + MAX_SCORE;
                } else {
                    row.cells[5].textContent = '—';
                }
            });
        })
        .catch(function() {
            document.getElementById('lastUpdated').textContent = 'Update failed — retrying...';
        });
}

refreshMonitor();
setInterval(refreshMonitor, 5000);
</script>

<?php $this->load->view('footer'); ?>

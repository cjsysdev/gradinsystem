<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Performance Sheet</title>
    <link rel="icon" href="<?= base_url('./assets/logo.png') ?>" type="image/png">
    <link rel="stylesheet" href="<?= base_url('assets/bootstrap.4.5.2.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('./assets/fontawesome/css/all.min.css') ?>" />

    <style>
        body {
            background: #f0f2f5;
        }

        #performance-sheet {
            width: 794px;
            max-width: 100%;
            margin: auto;
        }

        .stat-card {
            border-radius: 12px;
            text-align: center;
            padding: 16px 10px;
            color: #fff;
        }

        .stat-card h2 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
        }

        .stat-card small {
            font-size: 0.8rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .progress {
            height: 10px;
            border-radius: 6px;
        }

        .section-title {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #6c757d;
            margin-bottom: 12px;
        }

        .badge-severity-minor    { background-color: #ffc107; color: #212529; }
        .badge-severity-moderate { background-color: #fd7e14; color: #fff; }
        .badge-severity-major    { background-color: #dc3545; color: #fff; }

        .avatar-circle {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4e73df, #224abe);
            color: #fff;
            font-size: 1.4rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .violation-row-minor    { border-left: 4px solid #ffc107; }
        .violation-row-moderate { border-left: 4px solid #fd7e14; }
        .violation-row-major    { border-left: 4px solid #dc3545; }

        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            #performance-sheet { width: 100%; }
        }

        @media (max-width: 575.98px) {
            #performance-sheet {
                padding-left: 10px;
                padding-right: 10px;
            }

            .stat-card {
                padding: 12px 6px;
            }

            .stat-card h2 {
                font-size: 1.4rem;
            }

            .stat-card small {
                font-size: 0.65rem;
            }

            .avatar-circle {
                width: 48px;
                height: 48px;
                font-size: 1.1rem;
            }
        }
    </style>

    <script src="<?= base_url('assets/2-jquery-3.5.1.slim.min.js') ?>"></script>
    <script src="<?= base_url('assets/chart.js') ?>"></script>
    <script src="<?= base_url('assets/jspdf.umd.min.js') ?>"></script>
    <script src="<?= base_url('assets/html2canvas.min.js') ?>"></script>
</head>

<body>
    <?php $this->load->view('profile_info') ?>

    <?php
    $pct = function ($score, $max) {
        return $max > 0 ? round($score / $max * 100) : 0;
    };
    $initials = strtoupper(
        substr($student['firstname'] ?? '', 0, 1) .
        substr($student['lastname']  ?? '', 0, 1)
    );
    $total_violations = count($violations);
    $total_absences   = (int)($attendance_summary['absent_count'] ?? count($absences));
    ?>

    <div class="container mt-4 mb-5" id="performance-sheet">

        <!-- ── Header ── -->
        <div class="card shadow-sm mb-4">
            <div class="card-body d-flex flex-wrap align-items-center" style="gap:12px;">
                <div class="avatar-circle mr-md-3"><?= $initials ?></div>
                <div class="mr-auto" style="min-width:0;">
                    <h4 class="mb-0 font-weight-bold text-truncate">
                        <?= htmlspecialchars($student['lastname'] . ', ' . $student['firstname']) ?>
                    </h4>
                    <span class="text-muted" style="font-size:0.9rem;">
                        BS Information System &bull; <?= htmlspecialchars($this->session->section) ?>
                    </span>
                </div>
                <div class="d-flex flex-wrap" style="gap:8px;">
                    <span class="badge badge-pill p-2 px-3 <?= $total_absences > 3 ? 'badge-danger' : 'badge-secondary' ?>"
                          style="font-size:0.85rem;">
                        <?= $total_absences ?> Absent
                    </span>
                    <span class="badge badge-pill p-2 px-3 <?= $total_violations > 0 ? 'badge-warning' : 'badge-success' ?>"
                          style="font-size:0.85rem;">
                        <?= $total_violations ?> Violation<?= $total_violations != 1 ? 's' : '' ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- ── Attendance Summary ── -->
        <p class="section-title"><i class="fas fa-calendar-check mr-1"></i>Attendance Summary</p>
        <div class="row mb-4">
            <div class="col-6 col-md-3 mb-3 mb-md-0">
                <div class="stat-card shadow-sm" style="background:linear-gradient(135deg,#1cc88a,#13855c);">
                    <h2><?= (int)($attendance_summary['present_count'] ?? 0) ?></h2>
                    <small>Present</small>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3 mb-md-0">
                <div class="stat-card shadow-sm" style="background:linear-gradient(135deg,#e74a3b,#be2617);">
                    <h2><?= (int)($attendance_summary['absent_count'] ?? 0) ?></h2>
                    <small>Absent</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card shadow-sm" style="background:linear-gradient(135deg,#f6c23e,#dda20a);">
                    <h2><?= (int)($attendance_summary['late_count'] ?? 0) ?></h2>
                    <small>Late</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card shadow-sm" style="background:linear-gradient(135deg,#36b9cc,#258391);">
                    <h2><?= (int)($attendance_summary['excuse_count'] ?? 0) ?></h2>
                    <small>Excused</small>
                </div>
            </div>
        </div>

        <!-- ── Academic Performance + Quiz Chart ── -->
        <p class="section-title"><i class="fas fa-graduation-cap mr-1"></i>Academic Performance</p>
        <div class="row mb-4">
            <div class="col-md-5">
                <div class="card shadow-sm h-100">
                    <div class="card-body">

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Activities</span>
                                <strong><?= $student['total_activity'] ?>/<?= $student['max_activity'] ?></strong>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-primary"
                                     style="width:<?= $pct($student['total_activity'], $student['max_activity']) ?>%">
                                </div>
                            </div>
                            <small class="text-muted"><?= $pct($student['total_activity'], $student['max_activity']) ?>%</small>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Quizzes</span>
                                <strong><?= $student['total_quiz'] ?>/<?= $student['max_quiz'] ?></strong>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-info"
                                     style="width:<?= $pct($student['total_quiz'], $student['max_quiz']) ?>%">
                                </div>
                            </div>
                            <small class="text-muted"><?= $pct($student['total_quiz'], $student['max_quiz']) ?>%</small>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Project (PT)</span>
                                <strong><?= $student['total_pt'] ?>/<?= $student['max_pt'] ?></strong>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-success"
                                     style="width:<?= $pct($student['total_pt'], $student['max_pt']) ?>%">
                                </div>
                            </div>
                            <small class="text-muted"><?= $pct($student['total_pt'], $student['max_pt']) ?>%</small>
                        </div>

                        <div class="mb-1">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Exam</span>
                                <strong><?= $student['total_exam'] ?>/<?= $student['max_exam'] ?></strong>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-warning"
                                     style="width:<?= $pct($student['total_exam'], $student['max_exam']) ?>%">
                                </div>
                            </div>
                            <small class="text-muted"><?= $pct($student['total_exam'], $student['max_exam']) ?>%</small>
                        </div>

                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="card shadow-sm h-100">
                    <div class="card-header py-2">
                        <span class="font-weight-bold" style="font-size:0.9rem;">
                            <i class="fas fa-chart-bar mr-1 text-info"></i>Quiz Score Breakdown
                        </span>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <?php if (empty($student['quiz'])): ?>
                            <p class="text-muted text-center mb-0">No quiz records yet.</p>
                        <?php else: ?>
                            <canvas id="quizChart" style="max-height:200px;"></canvas>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── Absences ── -->
        <p class="section-title"><i class="fas fa-calendar-times mr-1"></i>Absence Records</p>
        <div class="card shadow-sm mb-4">
            <div class="card-body p-0">
                <?php if (empty($absences)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                        <p class="mb-0">No absences recorded this semester.</p>
                    </div>
                <?php else: ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($absences as $record): ?>
                            <li class="list-group-item d-flex align-items-start">
                                <div class="mr-3 text-center" style="min-width:50px;">
                                    <span class="d-block font-weight-bold" style="font-size:1.1rem;line-height:1;">
                                        <?= date('d', strtotime($record['date'])) ?>
                                    </span>
                                    <small class="text-muted text-uppercase"><?= date('M', strtotime($record['date'])) ?></small>
                                </div>
                                <div>
                                    <span class="font-weight-bold d-block"><?= date('l, F d, Y', strtotime($record['date'])) ?></span>
                                    <?php if (!empty($record['reason'])): ?>
                                        <span class="text-muted"><?= htmlspecialchars($record['reason']) ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($record['status']) && $record['status'] !== 'absent'): ?>
                                        <!-- <span class="badge badge-info ml-1"><?= ucfirst($record['status']) ?></span> -->
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Violations ── -->
        <p class="section-title"><i class="fas fa-exclamation-triangle mr-1"></i>Violations</p>
        <div class="card shadow-sm mb-4">
            <div class="card-body p-0">
                <?php if (empty($violations)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-shield-alt fa-2x mb-2 text-success"></i>
                        <p class="mb-0">No violations recorded.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size:0.88rem;">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:110px;">Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th style="width:90px;">Severity</th>
                                    <th style="width:90px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($violations as $v):
                                    $sev = strtolower($v['severity'] ?? 'minor');
                                    $sta = strtolower($v['status']   ?? 'pending');
                                    $sev_class = ['minor' => 'badge-severity-minor', 'moderate' => 'badge-severity-moderate', 'major' => 'badge-severity-major'][$sev] ?? 'badge-secondary';
                                    $sta_class = ['pending' => 'secondary', 'resolved' => 'success', 'dismissed' => 'info'][$sta] ?? 'secondary';
                                ?>
                                    <tr class="violation-row-<?= $sev ?>">
                                        <td class="text-nowrap"><?= date('M d, Y', strtotime($v['date_of_violation'])) ?></td>
                                        <td><?= htmlspecialchars($v['violation_type']) ?></td>
                                        <td><?= htmlspecialchars($v['description'] ?? '') ?></td>
                                        <td><span class="badge <?= $sev_class ?>"><?= ucfirst($sev) ?></span></td>
                                        <td><span class="badge badge-<?= $sta_class ?>"><?= ucfirst($sta) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- /#performance-sheet -->

    <!-- Export Button -->
    <div class="text-center mt-2 mb-5 no-print">
        <button class="btn btn-primary btn-lg px-5 shadow" onclick="exportPDF()">
            <i class="fas fa-file-pdf mr-2"></i>Export as PDF
        </button>
    </div>

    <script>
        <?php if (!empty($student['quiz'])): ?>
        (function () {
            var ctx = document.getElementById('quizChart').getContext('2d');
            var scores = <?= json_encode($student['quiz']) ?>;
            var titles = <?= json_encode($student['quiz_titles']) ?>;
            var maxes  = scores.map(() => null); // placeholder

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: titles.length ? titles : scores.map((_, i) => 'Q' + (i + 1)),
                    datasets: [{
                        label: 'Score',
                        data: scores,
                        backgroundColor: scores.map(s => {
                            if (s >= 80) return 'rgba(28,200,138,0.7)';
                            if (s >= 60) return 'rgba(54,185,204,0.7)';
                            return 'rgba(231,74,59,0.65)';
                        }),
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 10 }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 20,
                                font: { size: 10 }
                            }
                        }
                    }
                }
            });
        })();
        <?php endif; ?>

        function exportPDF() {
            const el = document.getElementById('performance-sheet');
            html2canvas(el, { scale: 2, useCORS: true }).then(canvas => {
                const pdf       = new jspdf.jsPDF('p', 'mm', 'a4');
                const pageW     = 210;
                const pageH     = 297;
                const imgW      = pageW;
                const imgH      = (canvas.height * pageW) / canvas.width;
                const imgData   = canvas.toDataURL('image/png');

                let remaining = imgH;
                let offset    = 0;

                pdf.addImage(imgData, 'PNG', 0, offset, imgW, imgH);
                remaining -= pageH;

                while (remaining > 0) {
                    offset -= pageH;
                    pdf.addPage();
                    pdf.addImage(imgData, 'PNG', 0, offset, imgW, imgH);
                    remaining -= pageH;
                }

                const name = '<?= addslashes(($student['lastname'] ?? 'student') . '_' . ($student['firstname'] ?? '')) ?>';
                pdf.save(name + '_performance_sheet.pdf');
            });
        }
    </script>
</body>

</html>

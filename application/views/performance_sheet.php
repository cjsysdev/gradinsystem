<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Performance Sheet</title>
    <link rel="icon" href="<?= base_url('./assets/logo.png') ?>" type="image/png">
    <link rel="stylesheet" href="<?= base_url('assets/bootstrap.4.5.2.min.css') ?>">
    <!-- highlight -->
    <link rel="stylesheet" href="<?= base_url('assets/highlights/atom-one-light.min.css') ?>">
    <!-- fontawesome -->
    <link rel="stylesheet" href="<?= base_url('./assets/fontawesome/css/all.min.css') ?>" />
    <!-- CodeMirror CSS -->
    <link rel="stylesheet" href="<?= base_url('./assets/codemirror.min.css') ?>" />

    <script src="<?= base_url('assets/2-jquery-3.5.1.slim.min.js') ?>"></script>
    <script src="<?= base_url('assets/chart.js') ?>"></script>
    <script src="<?= base_url('assets/jspdf.umd.min.js') ?>"></script>
    <script src="<?= base_url('assets/html2canvas.min.js') ?>"></script>
</head>

<body>

    <div class="container mt-5 mb-5" id="performance-sheet">
        <div class="row">
            <div class="col-md-4 text-center">
                <div class="card shadow-sm p-3 mb-4 bg-white rounded">
                    <img src="<?= base_url('assets/user.png') ?>" class="rounded-circle mb-3 mx-auto d-block" width="150" alt="Student Photo">
                    <h3 class="mb-0">Juan Dela Cruz</h3>
                    <small class="text-muted">BS Computer Science, 3rd Year</small>
                    <hr>
                    <p class="mb-1"><i class="fas fa-user"></i> Age: 20</p>
                    <p class="mb-1"><i class="fas fa-book"></i> Current Course: Database Systems</p>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card shadow-sm p-4 bg-white rounded">
                    <h4 class="mb-3"><i class="fas fa-calendar-times text-danger"></i> Absences</h4>
                    <ul class="list-group mb-3">
                        <li class="list-group-item">2026-02-15 - Sick</li>
                        <li class="list-group-item">2026-02-22 - Family Emergency</li>
                    </ul>
                    <p class="mb-4"><span class="badge badge-danger">Total Absences: 2</span></p>
                    <h4 class="mb-3"><i class="fas fa-tasks text-warning"></i> Missed Activities</h4>
                    <p class="mb-4"><span class="badge badge-warning">Missed 3 out of 10 Activities</span></p>
                    <h4 class="mb-3"><i class="fas fa-question-circle text-info"></i> Missed Quizzes</h4>
                    <p class="mb-4"><span class="badge badge-info">Missed 5 out of 10 Quizzes</span></p>
                    <h4 class="mb-3"><i class="fas fa-chart-bar text-primary"></i> Quiz Scores</h4>
                    <canvas id="quizChart" width="400" height="150"></canvas>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fas fa-file-alt text-success"></i> Major Exam Score</h5>
                            <p class="display-4 text-success">88</p>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-star text-warning"></i> Performance Task Score</h5>
                            <p class="display-4 text-warning">95</p>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <button class="btn btn-primary btn-lg" onclick="exportPDF()"><i class="fas fa-file-pdf"></i> Export as PDF</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        var ctx = document.getElementById('quizChart').getContext('2d');
        var quizChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Quiz 1', 'Quiz 2', 'Quiz 3', 'Quiz 4'],
                datasets: [{
                    label: 'Score',
                    data: [85, 90, 78, 92],
                    backgroundColor: 'rgba(54, 162, 235, 0.5)'
                }]
            }
        });
        function exportPDF() {
            html2canvas(document.getElementById('performance-sheet')).then(function(canvas) {
                const imgData = canvas.toDataURL('image/png');
                const pdf = new jspdf.jsPDF();
                pdf.addImage(imgData, 'PNG', 10, 10, 190, 0);
                pdf.save('performance-sheet.pdf');
            });
        }
    </script>
</body>
<?php $this->load->view('web_to_image'); ?>

</html>
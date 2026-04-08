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

    <style>
        #performance-sheet {
            width: 794px;
            /* ~A4 width at 96 DPI */
            margin: auto;
            background: white;
        }

        .page-break {
            page-break-before: always;
        }

        .no-break {
            page-break-inside: avoid;
        }
    </style>

    <script src="<?= base_url('assets/2-jquery-3.5.1.slim.min.js') ?>"></script>
    <script src="<?= base_url('assets/chart.js') ?>"></script>
    <script src="<?= base_url('assets/jspdf.umd.min.js') ?>"></script>
    <script src="<?= base_url('assets/html2canvas.min.js') ?>"></script>

</head>

<body>
    <?php $this->load->view('profile_info') ?>

    <div class="container mt-5 mb-5" id="performance-sheet">
        <div class="row">
            <div class="col-md-4 text-center">
                <div class="card shadow-sm p-3 mb-4 bg-white rounded">
                    <!-- <img src="<?= base_url('assets/user.png') ?>" class="rounded-circle mb-3 mx-auto d-block" width="150" alt="Student Photo"> -->
                    <h3 class="mb-0"><?= $this->session->lastname, ', ',  $this->session->firstname ?></h3>
                    <small class="text-muted">BS Information System, <?= $this->session->section ?></small>
                    <!-- <hr> -->
                    <!-- <p class="mb-1"><i class="fas fa-user"></i> Age: 20</p> -->
                    <!-- <p class="mb-1"><i class="fas fa-book"></i> Current Course: Database Systems</p> -->
                </div>
            </div>
            <div class="col-md-8">
                <div class="card shadow-sm p-4 bg-white rounded">
                    <h4 class="mb-3">Absences</h4>
                    <?php foreach ($absences as $record): ?>
                        <ul class="list-group mb-3 no-break">
                            <li class="list-group-item">
                                <strong><?php
                                        echo date('l, F d, Y', strtotime($record['date']));
                                        ?> </strong><br>
                                <?= $record['reason'] ?>
                            </li>
                        </ul>
                    <?php endforeach; ?>
                    <div class=" alert alert-danger">
                        <strong>Total of <?php echo count($absences); ?> Absences</strong>
                    </div>
                    <div class="row m-1">
                        <div class="col alert alert-secondary mr-2">
                            Activities: <strong><?= $student['total_activity'] . '/' . $student['max_activity'] ?> </strong>
                        </div>
                        <div class="col alert alert-secondary">
                            Quiz: <strong><?= $student['total_quiz'] . '/' . $student['max_quiz'] ?> </strong>
                        </div>
                    </div>
                    <h4 class="mb-3">Quiz Scores</h4>
                    <canvas class="no-break" id="quizChart" width="400" height="150"></canvas>
                    <hr>
                    <div class="row m-1">
                        <div class="col alert alert-secondary mr-2">
                            Exam: <strong><?= $student['total_exam'] . '/' . $student['max_exam'] ?> </strong>
                        </div>
                        <div class="col alert alert-secondary">
                            Project (PT): <strong><?= $student['total_pt'] . '/' . $student['max_pt'] ?> </strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="text-center mt-4 mb-4">
        <button class="btn btn-primary btn-lg" onclick="exportPDF()"><i class="fas fa-file-pdf"></i> Export as PDF</button>
    </div>

    <script>
        var ctx = document.getElementById('quizChart').getContext('2d');
        var quizData = <?php echo json_encode($student['quiz']); ?>;

        var quizChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: quizData.map((_, index) => 'Q' + (index + 1)),
                datasets: [{
                    label: 'Score',
                    data: quizData,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)'
                }]
            }
        });

        function exportPDF() {
            const element = document.getElementById('performance-sheet');

            html2canvas(element, {
                scale: 2,
                useCORS: true
            }).then(canvas => {

                const imgData = canvas.toDataURL('image/png');

                const pdf = new jspdf.jsPDF('p', 'mm', 'a4');

                const pageWidth = 210;
                const pageHeight = 297;

                // Calculate scaling to fit BOTH width & height
                const ratio = Math.min(
                    pageWidth / canvas.width,
                    pageHeight / canvas.height
                );

                const imgWidth = canvas.width * ratio;
                const imgHeight = canvas.height * ratio;

                // Center the content
                const x = (pageWidth - imgWidth) / 2;
                const y = (pageHeight - imgHeight) / 2;

                pdf.addImage(imgData, 'PNG', x, y, imgWidth, imgHeight);

                pdf.save('performance-sheet.pdf');
            });
        }
    </script>
</body>

</html>
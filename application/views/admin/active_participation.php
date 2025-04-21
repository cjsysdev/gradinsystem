<!-- filepath: c:\wamp64\www\gradingSystem\application\views\admin\active_participation.php -->
<?php $this->load->view('header'); ?>

<div class="container">
    <h1>Active Participation</h1>

    <!-- Filter Form -->
    <form method="GET" action="<?= base_url('AdminController/active_participation/' . $assessment_id) ?>" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <label for="section_id" class="form-label">Select Section</label>
                <select name="section_id" id="section_id" class="form-control">
                    <option value="">-- Select Section --</option>
                    <?php foreach ($sections as $section): ?>
                        <option value="<?= $section['section'] ?>" <?= isset($selected_section_id) && $selected_section_id == $section['section'] ? 'selected' : '' ?>>
                            <?= $section['section'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="date" class="form-label">Date</label>
                <input type="date" name="date" id="date" class="form-control" value="<?= $date ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <?php if (!empty($students)): ?>
        <div class="row">
            <?php foreach ($students as $student): ?>
                <div class="col-md-4">
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            <h3 class="card-title"><?= $student['lastname'] . ', ' . $student['firstname'] ?></h3>
                            <form action="<?= base_url('ClassworkController/add_score') ?>" method="POST">
                                <input type="hidden" name="student_id" value="<?= $student['trans_no'] ?>">
                                <input type="hidden" name="assessment_id" value="<?= $assessment_id ?>">
                                <div class="input-group mb-3">
                                    <input type="number" name="score" class="form-control" placeholder="Enter score" min="0" required>
                                    <button type="submit" class="btn btn-info">Add Score</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <button type="button" class="btn btn-success" onclick="randomizeStudent()">Randomize Student</button>
        </div>
        <div id="randomStudentCard" class="mt-4"></div>
    <?php else: ?>
        <div class="alert alert-warning">No students found for the selected section and date.</div>
    <?php endif; ?>
</div>

<script>
    function randomizeStudent() {
        const students = <?= json_encode($students) ?>;

        if (students.length > 0) {
            const randomIndex = Math.floor(Math.random() * students.length);
            const randomStudent = students[randomIndex];

            const randomStudentCard = `
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title">${randomStudent.lastname}, ${randomStudent.firstname}</h3>
                    </div>
                </div>
            `;

            document.getElementById('randomStudentCard').innerHTML = randomStudentCard;
        } else {
            document.getElementById('randomStudentCard').innerHTML = '<div class="alert alert-warning">No students available for random selection.</div>';
        }
    }
</script>

<?php $this->load->view('footer'); ?>
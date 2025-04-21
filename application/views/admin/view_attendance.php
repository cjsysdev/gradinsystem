<?php $this->load->view('header'); ?>

<div class="container">
    <h1>View Attendance</h1>

    <!-- Filter Form -->
    <form method="GET" action="<?= base_url('AdminController/view_attendance') ?>" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <label for="section_id" class="form-label">Select Section</label>
                <select name="section_id" id="section_id" class="form-control">
                    <option value="">-- Select Section --</option>
                    <option value="1C">1C</option>

                    <!-- <?php foreach ($sections as $section): ?>
                        <option value="<?= $section['section_id'] ?>" <?= isset($selected_section_id) && $selected_section_id == $section['section_id'] ? 'selected' : '' ?>>
                            <?= $section['section_name'] ?>
                        </option>
                    <?php endforeach; ?> -->
                </select>
            </div>
            <div class="col-md-4">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="<?= $start_date ?? '' ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <?php if (!empty($attendance)): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Student ID</th>
                    <th>Firstname</th>
                    <th>Lastname</th>
                    <th>Section</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attendance as $record): ?>
                    <tr>
                        <td><?= date('Y-m-d', strtotime($record['attendance_date'])) ?></td>
                        <td><?= $record['student_id'] ?></td>
                        <td><?= $record['firstname'] ?></td>
                        <td><?= $record['lastname'] ?></td>
                        <td><?= $record['section_name'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">No attendance records found for the selected section and date.</div>
    <?php endif; ?>
</div>

<?php $this->load->view('footer'); ?>
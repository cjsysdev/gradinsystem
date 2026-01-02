<?php $this->load->view('header'); ?>

<div class="container">
    <?php
    $this->load->view('profile_only');
    $this->load->view('admin/nav_bar');
    ?>

    <!-- Filter Form -->
    <form method="GET" action="<?= base_url('AdminController/view_attendance') ?>" class="mb-4">
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
                <th>Student ID</th>
                <th>Lastname</th>
                <th>Firstname</th>
                <th>Absents</th>
                <th>Absence Dates</th>
            </thead>
            <tbody>
                <?php foreach ($attendance as $record): ?>
                    <tr>
                        <td><?= $record['student_id'] ?></td>
                        <td><?= $record['lastname'] ?></td>
                        <td><?= $record['firstname'] ?></td>
                        <td><?= $record['absents'] / 2 ?></td>
                        <td>
                            <?php
                            $dates = explode(', ', $record['absence_dates']);
                            $formatted_dates = array_map(function ($date) {
                                return date('m-d', strtotime($date));
                            }, $dates);
                            echo implode(', ', $formatted_dates);
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">No attendance records found for the selected section and date.</div>
    <?php endif; ?>
</div>

<?php $this->load->view('footer'); ?>
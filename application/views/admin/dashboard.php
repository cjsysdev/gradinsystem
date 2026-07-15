<?php $this->load->view('header'); ?>

<div class="container">
    <div class="dashboard">
        <?php $this->load->view('profile_only'); ?>
        <?php $this->load->view('admin/nav_bar'); ?>
    </div>
    <div class="row mt-3">
        <div class="col text-center">
            <h4>Dashboard</h4>
        </div>

    </div>
    <!-- <div class="col text-center">
        <p>Discussion Mode: <strong><?php echo $discussion_mode
                                        ? 'Activated'
                                        : 'Deactivated'; ?></strong></p>

        <form action="<?php echo site_url(
                            'AdminController/toggle_discussion_mode'
                        ); ?>" method="post">
            <button class="btn btn-secondary" type="submit">
                <?php echo $discussion_mode
                    ? 'Deactivate Discussion Mode'
                    : 'Activate Discussion Mode'; ?>
            </button>
        </form>
    </div> -->

    <div class="row justify-content-center mt-3">
        <div class="col-md-8">
            <form method="get" action="<?= base_url('dashboard') ?>" class="form-inline justify-content-center">
                <label class="mr-2">Date:</label>
                <input type="date" name="date" class="form-control mr-3" value="<?= $selected_date ?>" max="<?= date('Y-m-d') ?>" onchange="this.form.submit()">

                <label class="mr-2">Section:</label>
                <select name="schedule_id" class="form-control mr-3" onchange="this.form.submit()">
                    <option value="">All Sections</option>
                    <?php foreach ($schedules as $s): ?>
                        <option value="<?= $s['schedule_id'] ?>" <?= (string) $selected_schedule_id === (string) $s['schedule_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['section']) ?> &mdash; <?= htmlspecialchars($s['class_code']) ?> (<?= $s['type'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>

                <?php if ($selected_date !== date('Y-m-d') || $selected_schedule_id !== ''): ?>
                    <a href="<?= base_url('dashboard') ?>" class="btn btn-sm btn-outline-secondary">Back to Today</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="row justify-content-center mt-5">
        <div class="col">
            <h5 class="text-center mb-3">Chronic Absentees <small class="text-muted">(3+ absences)</small></h5>
            <?php if (!empty($chronic_absentees)): ?>
                <?php foreach ($chronic_absentees as $row): ?>
                    <div class="card mb-3 shadow-sm border-danger">
                        <div class="card-body">
                            <h3 class="card-title mb-1">
                                <?= $row['student_id'] . " - " . $row['lastname'] . ", " . $row['firstname'] ?>
                                <?php if (!empty($row['section'])): ?><span class="badge badge-secondary ml-2"><?= htmlspecialchars($row['section']) ?></span><?php endif; ?>
                                <span class="badge badge-danger ml-2"><?= $row['absences'] ?> absences</span>
                            </h3>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-success">No students with 3 or more absences</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row justify-content-center mt-5">
        <div id="submissionsContainer" class="col">
            <?php if (!empty($attendance)): ?>
                <?php foreach ($attendance as $row): ?>
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            <h3 class="card-title mb-1">
                                <?= $row['student_id'] . " - " . $row['lastname'] . ", " . $row['firstname'] ?>
                                <?php if (!empty($row['section'])): ?><span class="badge badge-secondary ml-2"><?= htmlspecialchars($row['section']) ?></span><?php endif; ?>
                            </h3>
                            <hr>
                            <div class="row">
                                <p class="col-8 card-text mb-3"><?= $row['date'] ?></p>
                                <div class="col text-right">
                                    <select class="form-control form-control-sm d-inline-block w-auto attendance-status-select"
                                            data-attendance-id="<?= $row['attendance_id'] ?>"
                                            data-original="<?= $row['status'] ?>"
                                            onchange="updateAttendanceStatus(this)">
                                        <?php foreach (['present', 'absent', 'late', 'excuse', 'others'] as $s): ?>
                                            <option value="<?= $s ?>" <?= $row['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-warning">No double-entry (duplicate IP) attendance found for this date</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row justify-content-center mt-5">
        <div id="submissionsContainer" class="col">
            <?php if (!empty($lates)): ?>
                <?php foreach ($lates as $row): ?>
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            <h3 class="card-title mb-1">
                                <?= $row['student_id'] . " - " . $row['lastname'] . ", " . $row['firstname'] ?>
                                <?php if (!empty($row['section'])): ?><span class="badge badge-secondary ml-2"><?= htmlspecialchars($row['section']) ?></span><?php endif; ?>
                            </h3>
                            <hr>
                            <div class="row">
                                <p class="col-8 card-text mb-3"><?= $row['date'] ?></p>
                                <div class="col text-right">
                                    <select class="form-control form-control-sm d-inline-block w-auto attendance-status-select"
                                            data-attendance-id="<?= $row['attendance_id'] ?>"
                                            data-original="<?= $row['status'] ?>"
                                            onchange="updateAttendanceStatus(this)">
                                        <?php foreach (['present', 'absent', 'late', 'excuse', 'others'] as $s): ?>
                                            <option value="<?= $s ?>" <?= $row['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-warning">No late records found for this date</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row justify-content-center mt-5">
        <div id="submissionsContainer" class="col">
            <?php if (!empty($absents)): ?>
                <?php foreach ($absents as $row): ?>
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            <h3 class="card-title mb-1">
                                <?= $row['student_id'] . " - " . $row['lastname'] . ", " . $row['firstname'] ?>
                                <?php if (!empty($row['section'])): ?><span class="badge badge-secondary ml-2"><?= htmlspecialchars($row['section']) ?></span><?php endif; ?>
                            </h3>
                            <hr>
                            <div class="row">
                                <p class="col-8 card-text mb-3"><?= $row['date'] ?></p>
                                <div class="col text-right">
                                    <select class="form-control form-control-sm d-inline-block w-auto attendance-status-select"
                                            data-attendance-id="<?= $row['attendance_id'] ?>"
                                            data-original="<?= $row['status'] ?>"
                                            onchange="updateAttendanceStatus(this)">
                                        <?php foreach (['present', 'absent', 'late', 'excuse', 'others'] as $s): ?>
                                            <option value="<?= $s ?>" <?= $row['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-warning">No absences found</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function updateAttendanceStatus(select) {
    const attendance_id = select.dataset.attendanceId;
    const status = select.value;
    const original = select.dataset.original;

    fetch('<?= base_url('AdminController/update_attendance_status') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'attendance_id=' + encodeURIComponent(attendance_id) + '&status=' + encodeURIComponent(status)
    })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                select.dataset.original = status;
            } else {
                alert('Failed to update status.');
                select.value = original;
            }
        })
        .catch(() => {
            alert('Request failed.');
            select.value = original;
        });
}
</script>

<?php $this->load->view('footer'); ?>
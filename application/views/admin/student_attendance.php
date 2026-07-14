<?php $this->load->view('header'); ?>

<div class="container">
    <?php
    $this->load->view('profile_only');
    $this->load->view('admin/nav_bar');
    ?>

    <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
        <div>
            <h4 class="mb-0"><?= $student['lastname'] . ', ' . $student['firstname'] ?></h4>
            <small class="text-muted">
                Student ID: <?= $student['trans_no'] ?>
                <?php if (!empty($active_semester['description'])): ?>
                    &mdash; <?= htmlspecialchars($active_semester['description']) ?>
                <?php endif; ?>
            </small>
        </div>
        <a href="<?= base_url('view_attendance') ?>" class="btn btn-outline-secondary btn-sm">Back to Attendance</a>
    </div>

    <?php if (!empty($records)): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Type</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $row): ?>
                    <tr>
                        <td><?= date('M d, Y g:i A', strtotime($row['date'])) ?></td>
                        <td><?= htmlspecialchars($row['class_code']) ?></td>
                        <td><?= htmlspecialchars($row['section']) ?></td>
                        <td><?= htmlspecialchars($row['type']) ?></td>
                        <td>
                            <select class="form-control form-control-sm d-inline-block w-auto attendance-status-select"
                                    data-attendance-id="<?= $row['attendance_id'] ?>"
                                    data-original="<?= $row['status'] ?>"
                                    onchange="updateAttendanceStatus(this)">
                                <?php foreach (['present', 'absent', 'late', 'excuse', 'others'] as $s): ?>
                                    <option value="<?= $s ?>" <?= $row['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">No attendance records found for this student in the active semester.</div>
    <?php endif; ?>
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

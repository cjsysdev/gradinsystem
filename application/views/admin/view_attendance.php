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
                <th>Action</th>
            </thead>
            <tbody>
                <?php foreach ($attendance as $record): ?>
                    <tr>
                        <td><?= $record['student_id'] ?></td>
                        <td><?= $record['lastname'] ?></td>
                        <td><?= $record['firstname'] ?></td>
                        <td><?= $record['absents'] ?></td>
                        <td>
                            <?php
                            $dates = explode(', ', $record['absence_dates']);
                            $formatted_dates = array_map(function ($date) {
                                return date('m-d', strtotime($date));
                            }, $dates);
                            echo implode(', ', $formatted_dates);
                            ?>
                        </td>
                        <td>
                            <?php if ((int)$record['absents'] >= 3): ?>
                                <button type="button"
                                        class="btn btn-sm btn-warning readmit-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#readmitModal"
                                        data-student-id="<?= $record['student_id'] ?>"
                                        data-student-name="<?= htmlspecialchars($record['firstname'] . ' ' . $record['lastname']) ?>"
                                        data-absents="<?= $record['absents'] ?>"
                                        data-start-date="<?= htmlspecialchars($start_date ?? '') ?>">
                                    <i class="fa fa-undo"></i> Re-admit
                                </button>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">No attendance records found for the selected section and date.</div>
    <?php endif; ?>
</div>

<!-- Re-admission Modal -->
<div class="modal fade" id="readmitModal" tabindex="-1" aria-labelledby="readmitModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="readmitModalLabel">
                    <i class="fa fa-undo"></i> Re-admit Student
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                    Student: <strong id="readmitStudentName"></strong><br>
                    Current absences: <strong id="readmitAbsents" class="text-danger"></strong>
                </p>
                <p class="text-muted small">
                    All <code>absent</code> records from the selected date onward will be
                    changed to <code>readmitted</code>.
                </p>
                <div class="form-group">
                    <label for="readmitStartDate"><strong>Re-admission effective from:</strong></label>
                    <input type="date" id="readmitStartDate" class="form-control">
                </div>
                <input type="hidden" id="readmitStudentId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmReadmitBtn">
                    <i class="fa fa-check"></i> Confirm Re-admission
                </button>
            </div>
        </div>
    </div>
</div>

<script>
var base_url = '<?= base_url() ?>';

document.addEventListener('show.bs.modal', function (e) {
    var btn = e.relatedTarget;
    if (!btn || !btn.classList.contains('readmit-btn')) return;
    document.getElementById('readmitStudentId').value         = btn.dataset.studentId;
    document.getElementById('readmitStudentName').textContent = btn.dataset.studentName;
    document.getElementById('readmitAbsents').textContent     = btn.dataset.absents;
    document.getElementById('readmitStartDate').value         = btn.dataset.startDate || '';
});

document.getElementById('confirmReadmitBtn').addEventListener('click', function () {
    var studentId = document.getElementById('readmitStudentId').value;
    var startDate = document.getElementById('readmitStartDate').value;
    if (!startDate) { alert('Please provide a start date.'); return; }

    var btn = this;
    btn.disabled = true;
    btn.textContent = 'Processing...';

    fetch(base_url + 'admin/readmit_student', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'student_id=' + encodeURIComponent(studentId) + '&start_date=' + encodeURIComponent(startDate)
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-check"></i> Confirm Re-admission';
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('readmitModal')).hide();
            alert('Re-admission successful. The page will now reload.');
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(function () {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa fa-check"></i> Confirm Re-admission';
        alert('Request failed. Please try again.');
    });
});
</script>

<?php $this->load->view('footer'); ?>
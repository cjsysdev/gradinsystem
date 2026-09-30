<?php $this->load->view('header'); ?>

<div class="container">
    <?php $this->load->view('profile_info'); ?>

    <h4 class="mt-3">My Records</h4>
    <?php $this->load->view('semester_switcher'); ?>

    <?php if (empty($viewed_semester)): ?>
        <div class="alert alert-info mt-3">No records are available to view yet.</div>
    <?php else: ?>
        <?php if (!empty($attendance)): ?>
            <div class="row mt-3 text-center">
                <?php foreach (['present_count' => 'Present', 'absent_count' => 'Absent', 'late_count' => 'Late', 'excuse_count' => 'Excused'] as $k => $label): ?>
                    <div class="col-3"><div class="card"><div class="card-body py-2">
                        <h4 class="mb-0"><?= (int) $attendance[$k] ?></h4><small><?= $label ?></small>
                    </div></div></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <h5 class="mt-4">Submissions</h5>
        <?php if (empty($classworks)): ?>
            <div class="alert alert-info py-2">No submissions in this semester.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="thead-light"><tr><th>Title</th><th>Score</th><th>Max</th><th>Submitted</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($classworks as $cw): ?>
                        <tr>
                            <td><?= htmlspecialchars($cw['title']) ?></td>
                            <td><?= $cw['score'] !== null ? $cw['score'] : '<span class="text-muted">—</span>' ?></td>
                            <td><?= $cw['max_score'] ?? '—' ?></td>
                            <td><?= $cw['created_at'] ? date('M j, Y', strtotime($cw['created_at'])) : '—' ?></td>
                            <td><a class="btn btn-sm btn-outline-primary" href="<?= base_url('student_submission/' . (int) $cw['classwork_id']) ?>">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <h5 class="mt-4">Grades by Semester</h5>
    <?php $this->load->view('semester_history'); ?>
</div>

<?php $this->load->view('footer'); ?>

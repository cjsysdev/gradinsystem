<?php $this->load->view('header'); ?>

<div class="container">
    <?php $this->load->view('profile_only'); ?>
    <?php $this->load->view('admin/nav_bar'); ?>

    <div class="d-flex align-items-center mt-4 mb-3">
        <h4 class="mb-0">Score Integrity</h4>
        <?php if (!empty($violations)): ?>
            <span class="badge badge-danger ml-2" style="font-size:.85rem;"><?= count($violations) ?></span>
        <?php endif; ?>
    </div>
    <p class="text-muted">
        Submissions scored above their assessment's maximum. Every current scoring path clamps to
        max_score automatically, so new rows should not appear here — this list is for existing data
        that predates that guard. "Cap to Max" sets the score down to the assessment's max_score;
        it does not touch anything else about the submission.
    </p>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-info">
                <tr class="text-center">
                    <th>Student</th>
                    <th>Assessment</th>
                    <th>Score</th>
                    <th>Max</th>
                    <th>Over by</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($violations)): ?>
                    <?php foreach ($violations as $v): ?>
                        <tr id="row-<?= $v['classwork_id'] ?>">
                            <td>
                                <?php if ($v['student_id'] === null): ?>
                                    <span class="text-muted">Unknown student (id <?= htmlspecialchars($v['student_id'] ?? '—') ?>)</span>
                                <?php else: ?>
                                    <?= htmlspecialchars($v['lastname'] . ', ' . $v['firstname']) ?>
                                    <?php if (!empty($v['student_no'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($v['student_no']) ?></small>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($v['title'] ?? "assessment {$v['assessment_id']}") ?></td>
                            <td class="text-center score-cell"><?= htmlspecialchars($v['score']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($v['max_score']) ?></td>
                            <td class="text-center text-danger">+<?= round($v['score'] - $v['max_score'], 2) ?></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-warning btn-sm"
                                        onclick="capScore(<?= (int) $v['classwork_id'] ?>, <?= (float) $v['max_score'] ?>)">
                                    <i class="fa fa-scissors"></i> Cap to Max
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">
                            <i class="fa fa-check-circle text-success mr-1"></i>
                            No over-max scores found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function showIntegrityAlert(success, message) {
        const old = document.getElementById('integrity-alert');
        if (old) old.remove();

        const div = document.createElement('div');
        div.id = 'integrity-alert';
        div.className = 'alert ' + (success ? 'alert-success' : 'alert-danger') + ' alert-dismissible fade show position-fixed';
        div.style.top = '20px';
        div.style.right = '20px';
        div.style.zIndex = '9999';
        div.innerHTML = '<strong></strong><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
        div.querySelector('strong').textContent = message;
        document.body.appendChild(div);
        setTimeout(() => { $(div).alert('close'); }, 2500);
    }

    function capScore(classworkId, maxScore) {
        fetch('<?= base_url('admin/fix_score/') ?>' + classworkId, { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    showIntegrityAlert(false, data.message || 'Failed to cap score.');
                    return;
                }
                const row = document.getElementById('row-' + classworkId);
                if (row) row.remove();
                showIntegrityAlert(true, 'Score capped to ' + maxScore + '.');
            })
            .catch(() => showIntegrityAlert(false, 'Error capping score.'));
    }
</script>

<?php $this->load->view('footer'); ?>

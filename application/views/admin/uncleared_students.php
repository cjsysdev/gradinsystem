<?php $this->load->view('header'); ?>

<div class="container">
    <?php
    $this->load->view('profile_only');
    $this->load->view('admin/nav_bar');
    ?>

    <h3 class="mt-4 mb-3">Uncleared Students in Section: <span class="text-primary"><?= htmlspecialchars($section) ?></span></h3>
    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-info">
                <tr class="text-center">
                    <!-- <th>ID</th> -->
                    <th>No</th>
                    <th>Lastname</th>
                    <th>Firstname</th>
                    <th>Section</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($students)): ?>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <!-- <td class="text-center"><?= $student['id'] ?></td> -->
                            <td class="text-center"><?= $student['student_id'] ?? $student['trans_no'] ?></td>
                            <td><?= htmlspecialchars($student['lastname']) ?></td>
                            <td><?= htmlspecialchars($student['firstname']) ?></td>
                            <td class="text-center"><?= htmlspecialchars($student['section']) ?></td>
                            <td class="text-center">
                                <a href="<?= site_url('AdminController/clear_student/' . $student['id'] . '/' . urlencode($section)) ?>" class="btn btn-success btn-sm">
                                    <i class="fa fa-check" aria-hidden="true"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">No uncleared students found in this section.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
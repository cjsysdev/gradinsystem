<?php $this->load->view('header'); ?>

<div class="container">
    <?php $this->load->view('profile_only'); ?>
    <?php $this->load->view('admin/nav_bar'); ?>

    <div class="d-flex align-items-center mt-4 mb-3">
        <a href="<?= base_url('uncleared_students') ?>" class="btn btn-outline-secondary btn-sm mr-3">
            <i class="fa fa-arrow-left"></i> All Sections
        </a>
        <h4 class="mb-0">
            Uncleared Students &mdash; Section: <span class="text-primary"><?= htmlspecialchars($section) ?></span>
        </h4>
        <?php if (!empty($students)): ?>
            <span class="badge badge-danger ml-2" style="font-size:.85rem;"><?= count($students) ?></span>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-info">
                <tr class="text-center">
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($students)): ?>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= htmlspecialchars($student['lastname']) ?></td>
                            <td><?= htmlspecialchars($student['firstname']) ?></td>
                            <td class="text-center">
                                <a href="<?= base_url('uncleared_students/clear/' . $student['id'] . '/' . urlencode($section)) ?>"
                                   class="btn btn-success btn-sm"
                                   title="Mark as Cleared"
                                   onclick="return confirm('Mark <?= htmlspecialchars(addslashes($student['firstname'] . ' ' . $student['lastname'])) ?> as cleared?')">
                                    <i class="fa fa-check"></i> Clear
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-3">
                            <i class="fa fa-check-circle text-success mr-1"></i>
                            No uncleared students in this section.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $this->load->view('footer'); ?>

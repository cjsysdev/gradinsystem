<?php $this->load->view('header'); ?>

<div class="container">
    <?php $this->load->view('profile_only'); ?>
    <?php $this->load->view('admin/nav_bar'); ?>

    <div class="row mt-3">
        <div class="col text-center">
            <h4>Semester Management</h4>
        </div>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <?= htmlspecialchars($this->session->flashdata('success')) ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <div class="row mt-3">

        <!-- Form: add / edit -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header font-weight-bold">
                    <?= $editing ? 'Edit Semester' : 'Add Semester' ?>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= base_url('admin/save_semester') ?>">
                        <?php if ($editing): ?>
                            <input type="hidden" name="trans_no" value="<?= (int)$editing['trans_no'] ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label>Sem Code</label>
                            <input type="text" name="semcode" class="form-control" required maxlength="20"
                                   value="<?= htmlspecialchars($editing['semcode'] ?? $this->input->post('semcode') ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <input type="text" name="description" class="form-control" required maxlength="100"
                                   value="<?= htmlspecialchars($editing['description'] ?? $this->input->post('description') ?? '') ?>">
                        </div>
                        <div class="form-row">
                            <div class="form-group col-6">
                                <label>Type</label>
                                <select name="semtype" class="form-control" required>
                                    <?php foreach ([1 => '1st', 2 => '2nd', 3 => 'Summer'] as $v => $l): ?>
                                        <option value="<?= $v ?>"
                                            <?= ((int)($editing['semtype'] ?? 1)) === $v ? 'selected' : '' ?>>
                                            <?= $l ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group col-6">
                                <label>School Year</label>
                                <input type="number" name="semyear" class="form-control" required min="2000" max="2099"
                                       value="<?= htmlspecialchars($editing['semyear'] ?? date('Y')) ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Class Started</label>
                            <input type="date" name="class_started" class="form-control"
                                   value="<?= htmlspecialchars($editing['class_started'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Passing Rate (%)</label>
                            <input type="number" name="passing_rate" class="form-control" required min="0" max="100"
                                   value="<?= htmlspecialchars($editing['passing_rate'] ?? 60) ?>">
                        </div>

                        <button type="submit" class="btn btn-primary btn-block">
                            <?= $editing ? 'Save Changes' : 'Add Semester' ?>
                        </button>
                        <?php if ($editing): ?>
                            <a href="<?= base_url('admin/semesters') ?>" class="btn btn-secondary btn-block mt-1">Cancel</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <!-- Table: all semesters -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header font-weight-bold">All Semesters</div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Code</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th>SY</th>
                                <th>Started</th>
                                <th>Pass%</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($semesters as $sem): ?>
                            <tr class="<?= $sem['is_active'] ? 'table-success' : '' ?>">
                                <td><?= htmlspecialchars($sem['semcode']) ?></td>
                                <td><?= htmlspecialchars($sem['description']) ?></td>
                                <td><?= (int)$sem['semtype'] ?></td>
                                <td><?= htmlspecialchars($sem['semyear']) ?></td>
                                <td><?= $sem['class_started'] ? htmlspecialchars($sem['class_started']) : '<span class="text-muted">—</span>' ?></td>
                                <td><?= (int)$sem['passing_rate'] ?>%</td>
                                <td>
                                    <?php if ($sem['is_active']): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-nowrap">
                                    <a href="<?= base_url('admin/semesters?edit=' . $sem['trans_no']) ?>"
                                       class="btn btn-sm btn-outline-secondary" title="Edit">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <?php if (!$sem['is_active']): ?>
                                        <a href="<?= base_url('admin/activate_semester/' . $sem['trans_no']) ?>"
                                           class="btn btn-sm btn-outline-success ml-1"
                                           title="Activate"
                                           onclick="return confirm('Activate this semester? All students without an enrollment record for it will be asked to enroll on next login.')">
                                            <i class="fa fa-check-circle"></i> Activate
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div><!-- /.row -->
</div>

<?php $this->load->view('footer'); ?>

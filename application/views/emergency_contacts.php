<?php $this->load->view('header'); ?>

<div class="container mb-5">
    <?php $this->load->view('profile_info') ?>

    <h5 class="mb-3"><i class="fa fa-phone"></i> Emergency Contacts</h5>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>

    <?php if (!empty($contacts)): ?>
        <div class="mb-4">
            <?php foreach ($contacts as $c): ?>
                <div class="card mb-2 <?= $c['is_primary'] ? 'border-danger' : '' ?>">
                    <div class="card-body py-2 px-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= htmlspecialchars($c['full_name']) ?></strong>
                                <span class="text-muted ml-2"><?= htmlspecialchars($c['relationship']) ?></span>
                                <?php if ($c['is_primary']): ?>
                                    <span class="badge badge-danger ml-1">Primary</span>
                                <?php endif; ?>
                                <div class="small text-muted">
                                    <i class="fa fa-phone"></i> <?= htmlspecialchars($c['contact_no']) ?>
                                    <?php if ($c['email']): ?>
                                        &nbsp;|&nbsp;<i class="fa fa-envelope"></i> <?= htmlspecialchars($c['email']) ?>
                                    <?php endif; ?>
                                    <?php if ($c['address']): ?>
                                        &nbsp;|&nbsp;<i class="fa fa-map-marker"></i> <?= htmlspecialchars($c['address']) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="d-flex" style="gap: 6px;">
                                <?php if (!$c['is_primary']): ?>
                                    <a href="<?= base_url('set_primary_contact/' . $c['contact_id']) ?>"
                                       class="btn btn-sm btn-outline-danger"
                                       title="Set as primary"
                                       onclick="return confirm('Set <?= htmlspecialchars($c['full_name']) ?> as primary contact?')">
                                        <i class="fa fa-star"></i>
                                    </a>
                                <?php endif; ?>
                                <!-- <a href="<?= base_url('delete_emergency_contact/' . $c['contact_id']) ?>"
                                   class="btn btn-sm btn-outline-secondary"
                                   title="Remove"
                                   onclick="return confirm('Remove this contact?')">
                                    <i class="fa fa-trash"></i>
                                </a> -->
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-muted">No emergency contacts added yet.</p>
    <?php endif; ?>

    <hr>
    <h6 class="mb-3">Add Emergency Contact</h6>

    <form action="<?= base_url('save_emergency_contact') ?>" method="POST">

        <div class="form-row">
            <div class="form-group col-md-6">
                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="full_name" class="form-control" placeholder="e.g. Maria Santos" required>
            </div>
            <div class="form-group col-md-6">
                <label class="form-label">Relationship <span class="text-danger">*</span></label>
                <select name="relationship" class="form-control" required>
                    <option value="" disabled selected>Select relationship</option>
                    <option>Mother</option>
                    <option>Father</option>
                    <option>Guardian</option>
                    <option>Sibling</option>
                    <option>Spouse</option>
                    <option>Relative</option>
                    <option>Friend</option>
                    <option>Other</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label class="form-label">Contact Number <span class="text-danger">*</span></label>
                <input type="text" name="contact_no" class="form-control" placeholder="e.g. 09171234567" required>
            </div>
            <div class="form-group col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" placeholder="(optional)">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Address</label>
            <input type="text" name="address" class="form-control" placeholder="(optional)">
        </div>

        <div class="form-group form-check">
            <input type="checkbox" name="is_primary" id="is_primary" class="form-check-input" value="1">
            <label class="form-check-label" for="is_primary">Set as primary contact</label>
        </div>

        <button type="submit" class="btn btn-danger btn-block mt-2">Add Contact</button>
    </form>
</div>

<?php $this->load->view('footer'); ?>

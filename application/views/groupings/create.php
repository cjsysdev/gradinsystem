<?php $this->load->view('header'); ?>
<div class="container mt-4">
    <h3>Create Groupings</h3>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>
    <form action="<?= base_url('Groupings/store') ?>" method="post" class="mt-3">
        <div class="form-group">
            <label>Section</label>
            <select name="section" class="form-control" required>
                <option value="">Select section</option>
                <?php foreach ($sections as $s): ?>
                    <option value="<?= htmlspecialchars($s['section']) ?>"><?= htmlspecialchars($s['section']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Group name prefix</label>
            <input type="text" name="group_name" class="form-control" value="Group">
        </div>
        <div class="form-group">
            <label>Minimum members per group</label>
            <input type="number" name="min_members" class="form-control" value="3" min="1" required>
        </div>
        <div class="form-group">
            <label>Desired number of groups (optional)</label>
            <input type="number" name="desired_groups" class="form-control" min="1" placeholder="Leave empty to compute from min members">
        </div>
        <button class="btn btn-primary">Create Groups</button>
    </form>
</div>
<?php $this->load->view('footer'); ?>
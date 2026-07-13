<?php $this->load->view('header'); ?>
<div class="container mt-4">
    <?php $this->load->view('profile_only'); ?>
    <?php $this->load->view('admin/nav_bar'); ?>

    <h3>New Grouping Set</h3>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($this->session->flashdata('error')) ?></div>
    <?php endif; ?>
    <form action="<?= base_url('Groupings/store') ?>" method="post" class="mt-3">
        <div class="form-group">
            <label>Section</label>
            <select name="section" class="form-control" required>
                <option value="">Select section</option>
                <?php foreach ($sections as $s): ?>
                    <option value="<?= htmlspecialchars($s['section']) ?>"
                        <?= $preselected_section === $s['section'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s['section']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Set name</label>
            <input type="text" name="set_name" class="form-control" placeholder="e.g. Lab Groups, Project Teams" required>
        </div>
        <div class="form-group form-check">
            <input type="checkbox" name="self_select" value="1" class="form-check-input" id="self_select_check" onchange="toggleSelfSelectFields()">
            <label class="form-check-label" for="self_select_check">Let students form their own groups (self-select)</label>
            <small class="form-text text-muted">
                Students pick their own groupmates from those present that day, up to the group size below &mdash;
                instead of the shuffle/round-robin assignment.
            </small>
        </div>
        <div id="auto_assign_fields">
            <div class="form-group">
                <label>Group name prefix</label>
                <input type="text" name="group_name" class="form-control" value="Group">
            </div>
            <div class="form-group">
                <label>Desired number of groups (optional)</label>
                <input type="number" name="desired_groups" class="form-control" min="1" placeholder="Leave empty to compute from min members">
            </div>
        </div>
        <div class="form-group">
            <label id="min_members_label">Minimum members per group</label>
            <input type="number" name="min_members" class="form-control" value="3" min="1" required>
        </div>
        <button class="btn btn-primary">Create Groups</button>
    </form>
</div>
<script>
    function toggleSelfSelectFields() {
        const isSelfSelect = document.getElementById('self_select_check').checked;
        document.getElementById('auto_assign_fields').style.display = isSelfSelect ? 'none' : '';
        document.getElementById('min_members_label').textContent = isSelfSelect ? 'Group size (target)' : 'Minimum members per group';
    }
</script>
<?php $this->load->view('footer'); ?>

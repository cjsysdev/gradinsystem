<!-- filepath: c:\xampp\htdocs\gradingsystem\application\views\manage_json_files.php -->
<div class="container">
    <h1>Manage JSON File Paths</h1>
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
    <?php endif; ?>

    <form action="<?= base_url('AdminController/manage_json_files') ?>" method="post">
        <div class="mb-3">
            <label for="assessment_id" class="form-label">Assessment</label>
            <select name="assessment_id" id="assessment_id" class="form-select" required>
                <option value="">Select an Assessment</option>
                <?php foreach ($assessments as $assessment): ?>
                    <option value="<?= $assessment['assessment_id'] ?>"><?= $assessment['title'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="json_file_path" class="form-label">JSON File Path</label>
            <input type="text" name="json_file_path" id="json_file_path" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
    </form>

    <h2 class="mt-5">Existing JSON File Paths</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Assessment ID</th>
                <th>JSON File Path</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($json_files as $file): ?>
                <tr>
                    <td><?= $file['assessment_id'] ?></td>
                    <td><?= $file['json_file_path'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
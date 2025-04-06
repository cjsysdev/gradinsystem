<!-- filepath: c:\xampp\htdocs\gradingsystem\application\views\manage_assessments.php -->
<div class="container">
    <h1>Manage Assessments</h1>
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
    <?php endif; ?>

    <form action="<?= base_url('AssessmentController/upload_pdf') ?>" method="post" enctype="multipart/form-data">
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
            <label for="pdf_file" class="form-label">Upload PDF</label>
            <input type="file" name="pdf_file" id="pdf_file" class="form-control" accept="application/pdf" required>
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>

    <h2 class="mt-5">Existing Assessments</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Assessment ID</th>
                <th>Title</th>
                <th>PDF File</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($assessments as $assessment): ?>
                <tr>
                    <td><?= $assessment['assessment_id'] ?></td>
                    <td><?= $assessment['title'] ?></td>
                    <td>
                        <?php if ($assessment['pdf_file_path']): ?>
                            <a href="<?= base_url($assessment['pdf_file_path']) ?>" target="_blank">View PDF</a>
                        <?php else: ?>
                            No PDF uploaded
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
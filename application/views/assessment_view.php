<?php $this->load->view('header') ?>

<div class="container mt-5">
  <div class="card shadow">
    <div class="card-header bg-primary text-white">
      <h4 class="mb-0">Add New Assessment</h4>
    </div>
    <div class="card-body">
      <form action="<?php echo base_url('AssessmentController/add_assessment') ?>" method="POST" enctype="multipart/form-data">
        
        <!-- IO Type Selection -->
        <div class="form-group">
          <label for="iotype_id">Assessment Type <span class="text-danger">*</span></label>
          <select class="form-control" id="iotype_id" name="iotype_id" required>
            <option selected disabled>Select Assessment Type</option>
            <option value="1">Activity</option>
            <option value="2">Performance Task</option>
            <option value="3">Major Exam</option>
            <option value="4">Quiz</option>
          </select>
        </div>

        <!-- Schedule Selection -->
        <div class="form-group">
          <label for="schedule_id">Class Schedule <span class="text-danger">*</span></label>
          <select class="form-control" id="schedule_id" name="schedule_id" required>
            <option selected disabled>Select Schedule</option>
            <?php if (isset($schedules) && !empty($schedules)): ?>
              <?php foreach ($schedules as $schedule): ?>
                <option value="<?php echo $schedule->schedule_id; ?>">
                  Section: <?php echo $schedule->section; ?> | 
                  Day: <?php echo $schedule->day; ?> | 
                  Time: <?php echo date('h:i A', strtotime($schedule->time_start)); ?> - <?php echo date('h:i A', strtotime($schedule->time_end)); ?> | 
                  Type: <?php echo $schedule->type; ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>

        <!-- Title -->
        <div class="form-group">
          <label for="title">Assessment Title <span class="text-danger">*</span></label>
          <input type="text" class="form-control" id="title" name="title" placeholder="e.g., Midterm Exam" required maxlength="64">
        </div>

        <!-- Description -->
        <div class="form-group">
          <label for="description">Description <span class="text-danger">*</span></label>
          <textarea class="form-control" id="description" name="description" rows="4" placeholder="Enter assessment details and instructions" required></textarea>
        </div>

        <!-- Is Groupings -->
        <div class="form-group">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="is_groupings" name="is_groupings" value="1">
            <label class="custom-control-label" for="is_groupings">
              This is a Group Assessment
            </label>
          </div>
        </div>

        <!-- Max Score -->
        <div class="form-group">
          <label for="max_score">Maximum Score <span class="text-danger">*</span></label>
          <input type="number" class="form-control" id="max_score" name="max_score" min="1" placeholder="100" required>
        </div>

        <!-- Status -->
        <div class="form-group">
          <label for="status">Status <span class="text-danger">*</span></label>
          <select class="form-control" id="status" name="status" required>
            <option value="0">Inactive</option>
            <option value="1">Active</option>
          </select>
        </div>

        <!-- Due Date -->
        <div class="form-group">
          <label for="due">Due Date and Time <span class="text-danger">*</span></label>
          <input type="datetime-local" class="form-control" id="due" name="due" required>
        </div>

        <!-- Term -->
        <div class="form-group">
          <label for="term">Term <span class="text-danger">*</span></label>
          <select class="form-control" id="term" name="term" required>
            <option selected disabled>Select Term</option>
            <option value="midterm">Midterm</option>
            <option value="tentative-final">Tentative Final</option>
            <option value="final">Final</option>
          </select>
        </div>

        <!-- PDF File Upload -->
        <div class="form-group">
          <label for="pdf_file">Upload PDF (Assessment Instructions/Rubric)</label>
          <div class="custom-file">
            <input type="file" class="custom-file-input" id="pdf_file" name="pdf_file" accept=".pdf">
            <label class="custom-file-label" for="pdf_file">Choose file...</label>
          </div>
          <small class="form-text text-muted">Max 10MB. Optional.</small>
        </div>

        <!-- JSON File Upload -->
        <div class="form-group">
          <label for="json_file">Upload JSON (Assessment Data)</label>
          <div class="custom-file">
            <input type="file" class="custom-file-input" id="json_file" name="json_file" accept=".json">
            <label class="custom-file-label" for="json_file">Choose file...</label>
          </div>
          <small class="form-text text-muted">Max 5MB. Optional.</small>
        </div>

        <!-- Submit Button -->
        <div class="form-group mt-4">
          <button type="submit" class="btn btn-success btn-lg btn-block">Add Assessment</button>
          <a href="<?php echo base_url('AssessmentController/assessment_view') ?>" class="btn btn-secondary btn-lg btn-block mt-2">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Update file label on file selection
document.getElementById('pdf_file').addEventListener('change', function(e) {
  var fileName = e.target.files[0].name;
  document.querySelector('label[for="pdf_file"]').textContent = fileName;
});

document.getElementById('json_file').addEventListener('change', function(e) {
  var fileName = e.target.files[0].name;
  document.querySelector('label[for="json_file"]').textContent = fileName;
});
</script>

<?php $this->load->view('footer') ?>

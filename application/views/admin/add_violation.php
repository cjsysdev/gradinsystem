<?php $this->load->view('header'); ?>

<style>
    .form-card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border-radius: 8px;
        border: none;
    }

    .form-card .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 8px 8px 0 0;
        padding: 1.5rem;
    }

    .form-card .card-header h5 {
        color: white;
        margin: 0;
        font-weight: 600;
    }

    .required-field {
        color: #dc3545;
        font-weight: bold;
    }

    .form-section {
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e9ecef;
    }

    .form-section:last-child {
        border-bottom: none;
    }

    .form-section h6 {
        color: #495057;
        font-weight: 600;
        margin-bottom: 1rem;
        font-size: 0.95rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>

<div class="container-fluid mt-4">
    <?php $this->load->view('profile_only'); ?>
    <?php $this->load->view('admin/nav_bar'); ?>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card form-card">
                <div class="card-header">
                    <h5><i class="fa fa-plus-circle"></i> Record Student Violation</h5>
                    <small style="color: rgba(255,255,255,0.85); margin-top: 0.5rem; display: block;">Fill in all required fields to document a student violation</small>
                </div>
                
                <div class="card-body p-4">
                    <?php if ($this->session->flashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa fa-exclamation-circle"></i> 
                            <?= $this->session->flashdata('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="<?= base_url('admin/add_violation') ?>" id="violationForm">
                        <!-- Student Information Section -->
                        <div class="form-section">
                            <h6><i class="fa fa-user"></i> Student Information</h6>
                            
                            <div class="mb-3">
                                <label for="student_id" class="form-label">
                                    Student <span class="required-field">*</span>
                                </label>
                                <select name="student_id" id="student_id" class="form-select" required style="width: 100%;">
                                    <option value="">-- Select Student --</option>
                                    <?php foreach ($students as $student): ?>
                                        <option value="<?= $student['trans_no'] ?>" <?= ($this->input->get('student_id') == $student['trans_no']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($student['firstname'] . ' ' . $student['lastname']) ?> (<?= $student['trans_no'] ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="form-text text-muted">The student who committed the violation</small>
                            </div>
                        </div>

                        <!-- Violation Details Section -->
                        <div class="form-section">
                            <h6><i class="fa fa-file-text"></i> Violation Details</h6>

                            <div class="mb-3">
                                <label for="violation_type" class="form-label">
                                    Violation Type <span class="required-field">*</span>
                                </label>
                                <select name="violation_type" id="violation_type" class="form-select" required>
                                    <option value="">-- Select Violation Type --</option>
                                    <?php foreach ($violation_types as $type): ?>
                                        <option value="<?= htmlspecialchars($type['type_name']) ?>">
                                            <?= htmlspecialchars($type['type_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="date_of_violation" class="form-label">
                                    Date of Violation <span class="required-field">*</span>
                                </label>
                                <input type="date" name="date_of_violation" id="date_of_violation" class="form-control" required max="<?= date('Y-m-d') ?>">
                                <small class="form-text text-muted">When did the violation occur?</small>
                            </div>

                            <div class="mb-3">
                                <label for="severity" class="form-label">
                                    Severity Level
                                </label>
                                <select name="severity" id="severity" class="form-select">
                                    <option value="minor">Minor</option>
                                    <option value="moderate" selected>Moderate</option>
                                    <option value="major">Major</option>
                                </select>
                                <small class="form-text text-muted">
                                    <i class="fa fa-info-circle"></i>
                                    Minor: Low impact | Moderate: Medium impact | Major: High impact
                                </small>
                            </div>
                        </div>

                        <!-- Description Section -->
                        <div class="form-section">
                            <h6><i class="fa fa-comment"></i> Additional Information</h6>

                            <div class="mb-3">
                                <label for="description" class="form-label">
                                    Description
                                </label>
                                <textarea name="description" id="description" class="form-control" rows="4" placeholder="Provide detailed information about what happened..."></textarea>
                                <small class="form-text text-muted">Be specific and factual about the incident</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="reported_by" class="form-label">
                                        Reported By
                                    </label>
                                    <input type="text" name="reported_by" id="reported_by" class="form-control" placeholder="Your name or title">
                                    <small class="form-text text-muted">Who is reporting this violation?</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="notes" class="form-label">
                                        Additional Notes
                                    </label>
                                    <input type="text" name="notes" id="notes" class="form-control" placeholder="Any other relevant information...">
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2 justify-content-between pt-3 border-top">
                            <a href="<?= base_url('admin/student_violations') ?>" class="btn btn-outline-secondary px-4">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-success px-4">
                                <i class="fa fa-save"></i> Save Violation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('footer'); ?>

<!-- Select2 for student search in add form -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('#student_id').select2({
            placeholder: '-- Select Student --',
            allowClear: true,
            width: '100%',
            ajax: {
                url: '<?= base_url('admin/search_students') ?>',
                dataType: 'json',
                delay: 300,
                data: function(params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                }
            }
        });
    });
</script>
</div>

<?php $this->load->view('footer'); ?>

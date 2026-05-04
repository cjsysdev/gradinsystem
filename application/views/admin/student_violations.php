<?php $this->load->view('header'); ?>

<div class="container">
    <?php $this->load->view('profile_only'); ?>
    <?php $this->load->view('admin/nav_bar'); ?>

    <!-- Flash Messages -->
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa fa-check-circle"></i> <?= $this->session->flashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa fa-exclamation-circle"></i> <?= $this->session->flashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Student Search Form -->
    <form id="violationSearchForm" class="mb-4">
        <div class="input-group">
            <input type="text" id="studentSearchInput" class="form-control" placeholder="Search student by name or ID">
            <button type="submit" class="btn btn-primary">Search</button>
        </div>
        <select id="studentDropdown" name="student_id" class="form-control mt-2" style="display:none;"></select>
    </form>

    <!-- Page Header -->
    <?php if (empty($student)): ?>
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h4><i class="fa fa-exclamation-triangle"></i> All Student Violations</h4>
                <p class="text-muted">Showing violations for all students.</p>
            </div>
            <button type="button" class="btn btn-success" onclick="openAddViolationModal()">
                <i class="fa fa-plus"></i> Add Violation
            </button>
        </div>
    <?php else: ?>
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h4><i class="fa fa-exclamation-triangle"></i> Violations for <?= htmlspecialchars($student['firstname'] . ' ' . $student['lastname']) ?></h4>
                <p class="text-muted">Student ID: <?= htmlspecialchars($student['trans_no']) ?></p>
            </div>
            <button type="button" class="btn btn-success" onclick="openAddViolationModal()">
                <i class="fa fa-plus"></i> Add Violation
            </button>
        </div>
    <?php endif; ?>

    <!-- Filter Options -->
    <div class="mb-4 d-flex gap-2">
        <select name="status" id="statusFilter" class="form-select" style="max-width: 200px;">
            <option value="">All Statuses</option>
            <option value="pending" <?= ($selected_status == 'pending') ? 'selected' : '' ?>>Pending</option>
            <option value="resolved" <?= ($selected_status == 'resolved') ? 'selected' : '' ?>>Resolved</option>
            <option value="dismissed" <?= ($selected_status == 'dismissed') ? 'selected' : '' ?>>Dismissed</option>
        </select>

        <select name="severity" id="severityFilter" class="form-select" style="max-width: 200px;">
            <option value="">All Severities</option>
            <option value="minor" <?= ($selected_severity == 'minor') ? 'selected' : '' ?>>Minor</option>
            <option value="moderate" <?= ($selected_severity == 'moderate') ? 'selected' : '' ?>>Moderate</option>
            <option value="major" <?= ($selected_severity == 'major') ? 'selected' : '' ?>>Major</option>
        </select>

        <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()">Clear Filters</button>
    </div>

    <!-- Violations Table -->
    <?php if (!empty($violations)): ?>
        <table class="table table-striped table-hover">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Student</th>
                    <th>Violation Type</th>
                    <th>Description</th>
                    <th>Severity</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($violations as $violation): ?>
                    <tr>
                        <td><?= date('M d, Y', strtotime($violation['date_of_violation'])) ?></td>
                        <td>
                            <a href="<?= base_url('admin/student_violations?student_id=' . $violation['student_id']) ?>" class="text-decoration-none">
                                <?= htmlspecialchars(substr($violation['student_id'], 0, 15)) ?>
                            </a>
                        </td>
                        <td><strong><?= htmlspecialchars($violation['violation_type']) ?></strong></td>
                        <td><?= htmlspecialchars(substr($violation['description'] ?? 'N/A', 0, 50)) ?><?= (strlen($violation['description'] ?? '') > 50) ? '...' : '' ?></td>
                        <td>
                            <?php
                            $severity_class = ($violation['severity'] == 'major') ? 'danger' : (($violation['severity'] == 'moderate') ? 'warning' : 'info');
                            ?>
                            <span class="badge bg-<?= $severity_class ?>"><?= ucfirst($violation['severity']) ?></span>
                        </td>
                        <td>
                            <?php
                            $status_class = ($violation['status'] == 'pending') ? 'warning' : (($violation['status'] == 'resolved') ? 'success' : 'secondary');
                            ?>
                            <span class="badge bg-<?= $status_class ?>"><?= ucfirst($violation['status']) ?></span>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#violationModal<?= $violation['violation_id'] ?>">
                                <i class="fa fa-eye"></i> View
                            </button>
                        </td>
                    </tr>

                    <!-- View Violation Modal -->
                    <div class="modal fade" id="violationModal<?= $violation['violation_id'] ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-light">
                                    <h5 class="modal-title"><i class="fa fa-exclamation-circle"></i> Violation Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <p class="text-muted small mb-1">Violation Type</p>
                                            <p class="fw-semibold"><?= htmlspecialchars($violation['violation_type']) ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="text-muted small mb-1">Date</p>
                                            <p class="fw-semibold"><?= date('M d, Y H:i', strtotime($violation['date_of_violation'])) ?></p>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <p class="text-muted small mb-1">Severity</p>
                                            <p>
                                                <?php $severity_class = ($violation['severity'] == 'major') ? 'danger' : (($violation['severity'] == 'moderate') ? 'warning' : 'info'); ?>
                                                <span class="badge bg-<?= $severity_class ?>"><?= ucfirst($violation['severity']) ?></span>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="text-muted small mb-1">Status</p>
                                            <p>
                                                <?php $status_class = ($violation['status'] == 'pending') ? 'warning' : (($violation['status'] == 'resolved') ? 'success' : 'secondary'); ?>
                                                <span class="badge bg-<?= $status_class ?>"><?= ucfirst($violation['status']) ?></span>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <p class="text-muted small mb-1">Description</p>
                                        <p><?= nl2br(htmlspecialchars($violation['description'] ?? 'No description')) ?></p>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <p class="text-muted small mb-1">Reported By</p>
                                            <p><?= htmlspecialchars($violation['reported_by'] ?? 'Admin') ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="text-muted small mb-1">Recorded Date</p>
                                            <p><?= date('M d, Y H:i', strtotime($violation['created_at'] ?? $violation['date_of_violation'])) ?></p>
                                        </div>
                                    </div>

                                    <?php if ($violation['notes']): ?>
                                        <div class="mb-3">
                                            <p class="text-muted small mb-1">Notes</p>
                                            <p><?= nl2br(htmlspecialchars($violation['notes'])) ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <hr>

                                    <form method="post" action="<?= base_url('admin/update_violation_status') ?>">
                                        <h6 class="mb-3">Update Status</h6>
                                        
                                        <div class="mb-3">
                                            <label for="status_<?= $violation['violation_id'] ?>" class="form-label">New Status</label>
                                            <select name="status" id="status_<?= $violation['violation_id'] ?>" class="form-select" required>
                                                <option value="pending" <?= ($violation['status'] == 'pending') ? 'selected' : '' ?>>Pending</option>
                                                <option value="resolved" <?= ($violation['status'] == 'resolved') ? 'selected' : '' ?>>Resolved</option>
                                                <option value="dismissed" <?= ($violation['status'] == 'dismissed') ? 'selected' : '' ?>>Dismissed</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="notes_<?= $violation['violation_id'] ?>" class="form-label">Notes</label>
                                            <textarea name="notes" id="notes_<?= $violation['violation_id'] ?>" class="form-control" rows="3"><?= htmlspecialchars($violation['notes'] ?? '') ?></textarea>
                                        </div>

                                        <input type="hidden" name="violation_id" value="<?= $violation['violation_id'] ?>">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-save"></i> Update Status
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">
            <i class="fa fa-info-circle"></i>
            <?php if ($student): ?>
                No violations recorded for <?= htmlspecialchars($student['firstname'] . ' ' . $student['lastname']) ?>.
            <?php else: ?>
                No violations found. Click <strong>"Add Violation"</strong> to record one.
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Add Violation Modal -->
<div class="modal fade" id="addViolationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fa fa-plus-circle"></i> Record Student Violation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="<?= base_url('admin/add_violation') ?>" id="addViolationForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><strong>Student <span class="text-danger">*</span></strong></label>
                        <div class="position-relative">
                            <input type="text" id="studentSearchBox" class="form-control"
                                   placeholder="Type name or ID to search..."
                                   autocomplete="off">
                            <div id="studentResultsList"
                                 class="list-group position-absolute w-100"
                                 style="display:none; max-height:220px; overflow-y:auto; z-index:1060; box-shadow:0 4px 12px rgba(0,0,0,.15);">
                            </div>
                        </div>
                        <input type="hidden" name="student_id" id="modal_student_id">
                        <div class="invalid-feedback d-block" id="studentSearchFeedback" style="display:none!important;"></div>
                    </div>

                    <div class="mb-3">
                        <label for="modal_violation_type" class="form-label"><strong>Violation Type <span class="text-danger">*</span></strong></label>
                        <select name="violation_type" id="modal_violation_type" class="form-select" required>
                            <option value="">-- Select Type --</option>
                            <?php foreach ($violation_types as $type): ?>
                                <option value="<?= htmlspecialchars($type['type_name']) ?>">
                                    <?= htmlspecialchars($type['type_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="modal_date_of_violation" class="form-label"><strong>Date <span class="text-danger">*</span></strong></label>
                        <input type="date" name="date_of_violation" id="modal_date_of_violation" class="form-control" required max="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="modal_severity" class="form-label"><strong>Severity</strong></label>
                        <select name="severity" id="modal_severity" class="form-select">
                            <option value="minor">Minor</option>
                            <option value="moderate" selected>Moderate</option>
                            <option value="major">Major</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="modal_description" class="form-label">Description</label>
                        <textarea name="description" id="modal_description" class="form-control" rows="3" placeholder="Details about the violation..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="modal_reported_by" class="form-label">Reported By</label>
                        <input type="text" name="reported_by" id="modal_reported_by" class="form-control" placeholder="Your name or title">
                    </div>

                    <div class="mb-3">
                        <label for="modal_notes" class="form-label">Additional Notes</label>
                        <textarea name="notes" id="modal_notes" class="form-control" rows="2" placeholder="Any other information..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save"></i> Save Violation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $this->load->view('footer'); ?>

<script>
    // Student data from PHP — used by the modal autocomplete
    var allStudents = <?= json_encode(array_map(function($s) {
        return [
            'id'   => (string)$s['trans_no'],
            'name' => $s['firstname'] . ' ' . $s['lastname'],
        ];
    }, $students ?: [])) ?>;

    // ── Modal autocomplete ──────────────────────────────────────────────────
    (function () {
        var searchBox    = document.getElementById('studentSearchBox');
        var resultsList  = document.getElementById('studentResultsList');
        var hiddenInput  = document.getElementById('modal_student_id');
        var feedback     = document.getElementById('studentSearchFeedback');

        function renderResults(query) {
            resultsList.innerHTML = '';
            var q = query.toLowerCase().trim();
            if (!q) { resultsList.style.display = 'none'; return; }

            var matches = allStudents.filter(function (s) {
                return s.name.toLowerCase().indexOf(q) !== -1 || s.id.indexOf(q) !== -1;
            }).slice(0, 10);

            if (matches.length === 0) {
                resultsList.innerHTML = '<div class="list-group-item text-muted small py-2">No students found</div>';
            } else {
                matches.forEach(function (s) {
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'list-group-item list-group-item-action py-2 px-3';
                    btn.innerHTML = '<span class="fw-semibold">' + s.name + '</span>'
                                  + ' <small class="text-muted">(' + s.id + ')</small>';
                    btn.addEventListener('mousedown', function (e) {
                        e.preventDefault(); // keep focus on searchBox long enough to register
                        hiddenInput.value = s.id;
                        searchBox.value   = s.name;
                        searchBox.classList.remove('is-invalid');
                        feedback.style.display = 'none';
                        resultsList.style.display = 'none';
                    });
                    resultsList.appendChild(btn);
                });
            }
            resultsList.style.display = 'block';
        }

        searchBox.addEventListener('input', function () {
            hiddenInput.value = ''; // clear selection when user edits
            renderResults(this.value);
        });

        searchBox.addEventListener('focus', function () {
            if (this.value) renderResults(this.value);
        });

        searchBox.addEventListener('blur', function () {
            setTimeout(function () { resultsList.style.display = 'none'; }, 150);
        });

        // Form submit guard
        document.getElementById('addViolationForm').addEventListener('submit', function (e) {
            if (!hiddenInput.value) {
                e.preventDefault();
                searchBox.classList.add('is-invalid');
                feedback.textContent   = 'Please select a student from the list.';
                feedback.style.display = 'block';
                searchBox.focus();
            }
        });

        // Pre-fill when a specific student is in context
        document.getElementById('addViolationModal').addEventListener('shown.bs.modal', function () {
            <?php if (!empty($student)): ?>
            hiddenInput.value = <?= json_encode((string)$student['trans_no']) ?>;
            searchBox.value   = <?= json_encode($student['firstname'] . ' ' . $student['lastname']) ?>;
            <?php else: ?>
            searchBox.focus();
            <?php endif; ?>
        });

        // Reset when modal closes
        document.getElementById('addViolationModal').addEventListener('hidden.bs.modal', function () {
            searchBox.value   = '';
            hiddenInput.value = '';
            searchBox.classList.remove('is-invalid');
            feedback.style.display  = 'none';
            resultsList.style.display = 'none';
        });
    }());

    // ── Utility functions ───────────────────────────────────────────────────
    function openAddViolationModal() {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('addViolationModal')).show();
    }

    function clearFilters() {
        window.location.href = '<?= base_url('admin/student_violations') ?>';
    }

    // ── Page-level student search form ──────────────────────────────────────
    document.getElementById('violationSearchForm').addEventListener('submit', function (e) {
        e.preventDefault();
        var q = document.getElementById('studentSearchInput').value.trim();
        if (!q) return;

        fetch('<?= base_url('admin/search_students?q=') ?>' + encodeURIComponent(q))
            .then(function (res) { return res.json(); })
            .then(function (students) {
                var dropdown = document.getElementById('studentDropdown');
                dropdown.innerHTML = '';
                if (students.length > 0) {
                    dropdown.style.display = 'block';
                    students.forEach(function (s) {
                        var opt = document.createElement('option');
                        opt.value = s.id;
                        opt.textContent = s.text;
                        dropdown.appendChild(opt);
                    });
                    if (students.length === 1) {
                        window.location.href = '<?= base_url('admin/student_violations?student_id=') ?>' + students[0].id;
                    }
                } else {
                    dropdown.style.display = 'none';
                    alert('No students found.');
                }
            });
    });

    document.getElementById('studentDropdown').addEventListener('change', function () {
        if (this.value) {
            window.location.href = '<?= base_url('admin/student_violations?student_id=') ?>' + this.value;
        }
    });
</script>

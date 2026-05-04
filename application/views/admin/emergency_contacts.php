<?php $this->load->view('header'); ?>

<div class="container">

    <?php
    $this->load->view('profile_only');
    $this->load->view('admin/nav_bar');
    ?>

    <form id="emergencyContactSearchForm" class="mb-4">
        <div class="input-group">
            <input type="text" id="studentSearchInput" class="form-control" placeholder="Search student by Firstname or Lastname">
            <button type="submit" class="btn btn-primary">Search</button>
        </div>
        <select id="studentDropdown" name="student_id" class="form-control mt-2" style="display:none;"></select>
    </form>

    <?php if (empty($student)): ?>
        <div class="mb-4">
            <h4>All Emergency Contacts</h4>
            <p class="text-muted">Showing contacts for all students.</p>
        </div>
    <?php else: ?>
        <div class="mb-4">
            <h4>Emergency Contacts for <?= htmlspecialchars($student['firstname'] . ' ' . $student['lastname']) ?></h4>
            <p class="text-muted">Student ID: <?= htmlspecialchars($student['trans_no']) ?></p>
        </div>
    <?php endif; ?>

    <?php if (!empty($contacts)): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Relationship</th>
                    <th>Contact Number</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Primary</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contacts as $contact): ?>
                    <tr>
                        <td><?= htmlspecialchars($contact['full_name']) ?></td>
                        <td><?= htmlspecialchars($contact['relationship']) ?></td>
                        <td><?= htmlspecialchars($contact['contact_no']) ?></td>
                        <td><?= htmlspecialchars($contact['email'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($contact['address'] ?? '-') ?></td>
                        <td>
                            <?php if ($contact['is_primary']): ?>
                                <span class="badge bg-success">Primary</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Secondary</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <?php if (empty($student)): ?>
            <div class="alert alert-info">No emergency contacts found.</div>
        <?php else: ?>
            <div class="alert alert-warning">No emergency contacts found for this student.</div>
        <?php endif; ?>
    <?php endif; ?>

</div>

<script>
    document.getElementById('emergencyContactSearchForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const searchVal = document.getElementById('studentSearchInput').value.trim();
        if (!searchVal) {
            return;
        }

        fetch('<?= base_url('admin/search_students?search=') ?>' + encodeURIComponent(searchVal))
            .then(res => res.json())
            .then(students => {
                const dropdown = document.getElementById('studentDropdown');
                dropdown.innerHTML = '';
                if (students.length > 0) {
                    dropdown.style.display = 'block';
                    students.forEach(student => {
                        const option = document.createElement('option');
                        option.value = student.trans_no;
                        option.textContent = `${student.firstname} ${student.lastname} (${student.trans_no})`;
                        dropdown.appendChild(option);
                    });
                    if (students.length === 1) {
                        window.location.href = '<?= base_url('admin/emergency_contacts?student_id=') ?>' + students[0].trans_no;
                    }
                } else {
                    dropdown.style.display = 'none';
                    alert('No students found.');
                }
            });
    });

    document.getElementById('studentDropdown').addEventListener('change', function() {
        if (this.value) {
            window.location.href = '<?= base_url('admin/emergency_contacts?student_id=') ?>' + this.value;
        }
    });
</script>

<?php $this->load->view('footer'); ?>
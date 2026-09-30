<?php $this->load->view('header'); ?>

<div class="container">
    <?php $this->load->view('profile_only'); ?>
    <?php $this->load->view('admin/nav_bar'); ?>

    <div class="row mt-3">
        <div class="col text-center">
            <h4>Students by Section</h4>
        </div>
    </div>

    <?php // Outside the section block: section_officers_install redirects here
          // with no section selected, and its result still has to be visible. ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger mt-2"><?= htmlspecialchars($this->session->flashdata('error')) ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('success')): ?>
        <div class="alert alert-success mt-2"><?= htmlspecialchars($this->session->flashdata('success')) ?></div>
    <?php endif; ?>

    <?php if (empty($officers_ready)): ?>
        <div class="alert alert-info mt-2 d-flex justify-content-between align-items-center">
            <span>Officer designations aren't set up yet on this database.</span>
            <a href="<?= base_url('admin/section_officers_install') ?>" class="btn btn-sm btn-primary">
                Set up officers table
            </a>
        </div>
    <?php endif; ?>

    <!-- Section selector: click a card to view that section's students -->
    <?php if (empty($sections)): ?>
        <div class="alert alert-warning mt-3">No sections found for the active semester.</div>
    <?php else: ?>
        <div class="row mt-3">
            <?php foreach ($sections as $sec): ?>
                <?php $is_active = ($selected_section === $sec['section']); ?>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3">
                    <a href="<?= base_url('admin/students_by_section?section=' . urlencode($sec['section'])) ?>"
                       class="text-decoration-none">
                        <div class="card h-100 shadow-sm section-card text-center <?= $is_active ? 'border-primary active-section' : '' ?>">
                            <div class="card-body p-3">
                                <!-- <i class="fa fa-users fa-2x <?= $is_active ? 'text-primary' : 'text-secondary' ?> mb-2"></i> -->
                                <p class="card-text mb-1" style="font-weight:600;line-height:1.2;">
                                    <?= htmlspecialchars($sec['section']) ?>
                                </p>
                                <small class="text-muted">
                                    <?= (int) $sec['student_count'] ?> student<?= (int) $sec['student_count'] !== 1 ? 's' : '' ?>
                                </small>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($selected_section): ?>
        <?php
        // Officers first, in the order application/config/officers.php lists the
        // positions, then everyone else alphabetically (already sorted by SQL).
        $position_rank = array_flip(array_keys($officer_positions));
        $officer_cards = [];
        $other_cards   = [];
        foreach ($students as $student) {
            $held = isset($officers[(int) $student['student_id']])
                ? $officers[(int) $student['student_id']]
                : '';
            $student['officer_position'] = $held;
            if ($held !== '' && isset($position_rank[$held])) {
                $officer_cards[] = $student;
            } else {
                $other_cards[] = $student;
            }
        }
        usort($officer_cards, function ($a, $b) use ($position_rank) {
            return $position_rank[$a['officer_position']] <=> $position_rank[$b['officer_position']];
        });
        $ordered_students = array_merge($officer_cards, $other_cards);
        ?>
        <div class="row mt-3 align-items-center">
            <div class="col-md">
                <h5 class="text-muted mb-2 mb-md-0">Section: <strong><?= htmlspecialchars($selected_section) ?></strong>
                    &mdash; <?= count($students) ?> student<?= count($students) !== 1 ? 's' : '' ?>
                    <?php if (!empty($officer_cards)): ?>
                        &mdash; <?= count($officer_cards) ?> officer<?= count($officer_cards) !== 1 ? 's' : '' ?>
                    <?php endif; ?>
                </h5>
            </div>
            <div class="col-md-auto <?= empty($officers_ready) ? 'd-none' : '' ?>">
                <button type="button" id="toggleOfficers" class="btn btn-sm btn-outline-primary">
                    <i class="fa fa-id-badge"></i> Assign Officers
                </button>
                <a href="<?= base_url('admin/export_officers?section=' . urlencode($selected_section)) ?>"
                   class="btn btn-sm btn-outline-secondary">
                    <i class="fa fa-download"></i> Export Officers
                </a>
            </div>
        </div>

        <?php if (empty($students)): ?>
            <div class="alert alert-warning mt-2">No students found in this section.</div>
        <?php else: ?>
            <div class="row mt-2">
                <?php foreach ($ordered_students as $student): ?>
                    <?php $held = $student['officer_position']; ?>
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4">
                        <div class="card h-100 shadow-sm student-card text-center"
                             data-student-card="<?= (int) $student['student_id'] ?>">
                            <!-- The link wraps only the profile block: a <select> inside
                                 an anchor navigates away the moment it is clicked. -->
                            <a href="<?= base_url('admin/student_summary/' . $student['student_id']) ?>"
                               class="text-decoration-none text-dark">
                                <div class="card-body p-2">
                                    <?php if (!empty($student['profile_pic'])): ?>
                                        <img src="<?= base_url('uploads/profile_pics/' . htmlspecialchars($student['profile_pic'])) ?>"
                                             alt="<?= htmlspecialchars($student['firstname']) ?>"
                                             class="rounded-circle mb-2"
                                             style="width:80px;height:80px;object-fit:cover;">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-2"
                                             style="width:80px;height:80px;">
                                            <i class="fa fa-user fa-2x text-white"></i>
                                        </div>
                                    <?php endif; ?>
                                    <p class="card-text mb-0" style="font-size:.85rem;font-weight:600;line-height:1.2;">
                                        <?= htmlspecialchars($student['lastname'] . ', ' . $student['firstname']) ?>
                                    </p>
                                    <small class="text-muted"><?= htmlspecialchars($student['student_id']) ?></small>
                                    <div class="officer-badge-wrap mt-1">
                                        <span class="badge badge-warning officer-badge<?= $held === '' ? ' d-none' : '' ?>">
                                            <?= $held !== '' && isset($officer_positions[$held])
                                                ? htmlspecialchars($officer_positions[$held])
                                                : '' ?>
                                        </span>
                                    </div>
                                </div>
                            </a>
                            <div class="card-footer p-1 officer-assign d-none">
                                <select class="form-control form-control-sm officer-select"
                                        style="font-size:.75rem;"
                                        data-student-id="<?= (int) $student['student_id'] ?>"
                                        data-original="<?= htmlspecialchars($held) ?>">
                                    <option value="">&mdash; none &mdash;</option>
                                    <?php foreach ($officer_positions as $key => $label): ?>
                                        <option value="<?= htmlspecialchars($key) ?>" <?= $held === $key ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.student-card,
.section-card {
    transition: transform .15s, box-shadow .15s;
    cursor: pointer;
}
.student-card:hover,
.section-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 .4rem 1rem rgba(0,0,0,.15) !important;
}
.section-card.active-section {
    border-width: 2px;
}
.officer-badge {
    font-size: .65rem;
    letter-spacing: .03em;
    text-transform: uppercase;
}
/* Cards stop lifting while assigning — the hover transform drags the open
   dropdown around under the cursor. */
.officer-mode .student-card:hover {
    transform: none;
}
</style>

<script>
(function () {
    var toggle = document.getElementById('toggleOfficers');
    if (!toggle) {
        return; // no section selected — nothing to assign
    }

    var LABELS  = <?= json_encode($officer_positions) ?>;
    var SECTION = <?= json_encode((string) $selected_section) ?>;

    toggle.addEventListener('click', function () {
        var on = document.body.classList.toggle('officer-mode');
        document.querySelectorAll('.officer-assign').forEach(function (el) {
            el.classList.toggle('d-none', !on);
        });
        toggle.classList.toggle('btn-primary', on);
        toggle.classList.toggle('btn-outline-primary', !on);
        toggle.innerHTML = on
            ? '<i class="fa fa-check"></i> Done Assigning'
            : '<i class="fa fa-id-badge"></i> Assign Officers';
    });

    // Repaint one card's badge from a position key ('' clears it).
    function paintBadge(studentId, position) {
        var card = document.querySelector('[data-student-card="' + studentId + '"]');
        if (!card) {
            return;
        }
        var badge = card.querySelector('.officer-badge');
        if (badge) {
            badge.textContent = position && LABELS[position] ? LABELS[position] : '';
            badge.classList.toggle('d-none', !position);
        }
        var select = card.querySelector('.officer-select');
        if (select) {
            select.value = position || '';
            select.dataset.original = position || '';
        }
    }

    document.querySelectorAll('.officer-select').forEach(function (select) {
        select.addEventListener('change', function () {
            var studentId = select.dataset.studentId;
            var position  = select.value;
            var original  = select.dataset.original;

            select.disabled = true;

            fetch('<?= base_url('admin/assign_officer') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'section=' + encodeURIComponent(SECTION)
                    + '&student_id=' + encodeURIComponent(studentId)
                    + '&position=' + encodeURIComponent(position)
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        paintBadge(studentId, position);
                        // Whoever was unseated by this save loses their badge too.
                        (data.cleared || []).forEach(function (id) { paintBadge(id, ''); });
                    } else {
                        alert(data.message || 'Failed to save the designation.');
                        select.value = original;
                    }
                })
                .catch(function () {
                    alert('Request failed.');
                    select.value = original;
                })
                .then(function () { select.disabled = false; });
        });
    });
})();
</script>

<?php $this->load->view('footer'); ?>

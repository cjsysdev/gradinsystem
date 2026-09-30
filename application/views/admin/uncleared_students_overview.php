<?php
/**
 * Clearance overview — one card per section, for ONE semester and ONE term.
 *
 * @var array  $sections    section, total_count, cleared_count, uncleared_count
 * @var string $term        midterm|final
 * @var array  $terms       term key => label
 * @var int    $semester_id
 * @var array  $semester    the semester_master row in scope (or null)
 * @var array  $semesters   every semester, newest first
 * @var bool   $table_ready student_clearance exists yet
 */
$this->load->view('header');
$q = function ($semester_id, $term) {
    return base_url('uncleared_students') . '?semester=' . (int) $semester_id . '&term=' . urlencode($term);
};
?>

<div class="container">
    <?php $this->load->view('profile_only'); ?>
    <?php $this->load->view('admin/nav_bar'); ?>

    <?php if ($msg = $this->session->flashdata('success')): ?>
        <div class="alert alert-success mt-3"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <?php if ($msg = $this->session->flashdata('error')): ?>
        <div class="alert alert-danger mt-3"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="row mt-3 mb-2">
        <div class="col">
            <h4 class="mb-1">Student Clearance</h4>
            <p class="text-muted mb-0">Clearance is issued per semester and per term. Select a section to clear students.</p>
        </div>
    </div>

    <?php if (!$table_ready): ?>
        <div class="alert alert-warning">
            <strong>Clearance table not installed yet.</strong>
            Run the one-time setup to create <code>student_clearance</code> and carry over the
            existing <code>class_student.is_cleared</code> flags as <em>midterm</em> clearance.
            <div class="mt-2">
                <a href="<?= base_url('uncleared_students/install') ?>" class="btn btn-sm btn-warning">
                    <i class="fa fa-database"></i> Run setup
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Scope: semester + term -->
    <div class="card mb-4">
        <div class="card-body py-3">
            <form method="get" action="<?= base_url('uncleared_students') ?>" class="form-row align-items-end">
                <!-- keeps the chosen term when only the semester changes -->
                <input type="hidden" name="term" value="<?= htmlspecialchars($term) ?>">
                <div class="col-12 col-md-6 mb-2 mb-md-0">
                    <label for="semester" class="small text-muted mb-1">Semester</label>
                    <select name="semester" id="semester" class="form-control form-control-sm"
                            onchange="this.form.submit()">
                        <?php foreach ($semesters as $sem): ?>
                            <option value="<?= (int) $sem['trans_no'] ?>"
                                <?= (int) $sem['trans_no'] === (int) $semester_id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sem['description']) ?><?= !empty($sem['is_active']) ? ' (active)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-6">
                    <span class="small text-muted d-block mb-1">Term</span>
                    <div class="btn-group btn-group-sm" role="group">
                        <?php foreach ($terms as $key => $label): ?>
                            <a href="<?= $q($semester_id, $key) ?>"
                               class="btn <?= $term === $key ? 'btn-primary' : 'btn-outline-primary' ?>">
                                <?= htmlspecialchars($label) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <noscript><button type="submit" class="btn btn-sm btn-secondary ml-2">Go</button></noscript>
            </form>
        </div>
    </div>

    <?php
    $total_uncleared = 0;
    foreach ($sections as $sec) {
        $total_uncleared += (int) $sec['uncleared_count'];
    }
    ?>
    <p class="text-muted">
        Showing <strong><?= htmlspecialchars($terms[$term]) ?></strong> clearance for
        <strong><?= $semester ? htmlspecialchars($semester['description']) : 'no semester' ?></strong>
        &mdash; <?= $total_uncleared ?> uncleared student<?= $total_uncleared === 1 ? '' : 's' ?>.
    </p>

    <?php if (empty($sections)): ?>
        <div class="alert alert-info">
            <i class="fa fa-info-circle mr-1"></i> No enrolled sections in this semester.
        </div>
    <?php elseif ($total_uncleared === 0): ?>
        <div class="alert alert-success">
            <i class="fa fa-check-circle mr-1"></i>
            All students are cleared for the <?= htmlspecialchars(strtolower($terms[$term])) ?> of this semester.
        </div>
    <?php endif; ?>

    <div class="row">
        <?php foreach ($sections as $sec): ?>
            <?php
            $uncleared = (int) $sec['uncleared_count'];
            $total     = (int) $sec['total_count'];
            $done      = $uncleared === 0;
            ?>
            <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-4">
                <a href="<?= base_url('uncleared_students/' . rawurlencode($sec['section'])) ?>?semester=<?= (int) $semester_id ?>&term=<?= urlencode($term) ?>"
                   class="text-decoration-none text-dark">
                    <div class="card h-100 shadow-sm section-card text-center">
                        <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center">
                            <div class="rounded-circle <?= $done ? 'bg-success' : 'bg-warning' ?> d-inline-flex align-items-center justify-content-center mb-2"
                                 style="width:56px;height:56px;">
                                <i class="fa <?= $done ? 'fa-check' : 'fa-user-times' ?> fa-lg text-white"></i>
                            </div>
                            <p class="card-text mb-1" style="font-size:.9rem;font-weight:600;">
                                <?= htmlspecialchars($sec['section']) ?>
                            </p>
                            <span class="badge <?= $done ? 'badge-success' : 'badge-danger' ?>" style="font-size:.8rem;">
                                <?= $done ? 'All cleared' : $uncleared . ' uncleared' ?>
                            </span>
                            <small class="text-muted mt-1"><?= (int) $sec['cleared_count'] ?>/<?= $total ?> cleared</small>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.section-card {
    transition: transform .15s, box-shadow .15s;
    cursor: pointer;
}
.section-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 .4rem 1rem rgba(0,0,0,.15) !important;
}
</style>

<?php $this->load->view('footer'); ?>

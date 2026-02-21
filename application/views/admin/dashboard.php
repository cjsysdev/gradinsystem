<?php $this->load->view('header'); ?>

<div class="container">
    <div class="dashboard">
        <?php $this->load->view('profile_only'); ?>
        <?php $this->load->view('admin/nav_bar'); ?>
    </div>
    <div class="row mt-3">
        <div class="col text-center">
            <h4>Dashboard</h4>
        </div>

    </div>
    <div class="col text-center">
        <!-- Display the current state of discussion mode -->
        <p>Discussion Mode: <strong><?php echo $discussion_mode
                                        ? 'Activated'
                                        : 'Deactivated'; ?></strong></p>

        <!-- Button to toggle discussion mode -->
        <form action="<?php echo site_url(
                            'AdminController/toggle_discussion_mode'
                        ); ?>" method="post">
            <button class="btn btn-secondary" type="submit">
                <?php echo $discussion_mode
                    ? 'Deactivate Discussion Mode'
                    : 'Activate Discussion Mode'; ?>
            </button>
        </form>
    </div>

    <div class="row justify-content-center mt-5">
        <div id="submissionsContainer" class="col">
            <?php if (!empty($attendance)): ?>
                <?php foreach ($attendance as $row): ?>
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            <h3 class="card-title mb-1"><?= $row['student_id'] . " - " . $row['lastname'] . ", " . $row['firstname'] ?></h3>
                            <hr>
                            <div class="row">
                                <p class="col-8 card-text mb-3"><?= $row['date'] ?></p>
                                <div class="col text-right">
                                    <p class="badge badge-secondary card-text mb-3"><?= $row['status'] ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-warning">No double entry found for the attendance</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row justify-content-center mt-5">
        <div id="submissionsContainer" class="col">
            <?php if (!empty($lates)): ?>
                <?php foreach ($lates as $row): ?>
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            <h3 class="card-title mb-1"><?= $row['student_id'] . " - " . $row['lastname'] . ", " . $row['firstname'] ?></h3>
                            <hr>
                            <div class="row">
                                <p class="col-8 card-text mb-3"><?= $row['date'] ?></p>
                                <div class="col text-right">
                                    <p class="badge badge-warning card-text mb-3"><?= $row['status'] ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-warning">No double entry found for the lates</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row justify-content-center mt-5">
        <div id="submissionsContainer" class="col">
            <?php if (!empty($absents)): ?>
                <?php foreach ($absents as $row): ?>
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            <h3 class="card-title mb-1"><?= $row['student_id'] . " - " . $row['lastname'] . ", " . $row['firstname'] ?></h3>
                            <hr>
                            <div class="row">
                                <p class="col-8 card-text mb-3"><?= $row['date'] ?></p>
                                <div class="col text-right">
                                    <p class="badge badge-danger card-text mb-3"><?= $row['status'] ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-warning">No double entry found for the lates</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php $this->load->view('footer'); ?>
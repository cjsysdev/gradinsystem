<?php $this->load->view('header'); ?>


<div class="container">
    <div class="dashboard">
        <div class="row profile-section text-center mt-3">
            <div class="col">
                <h6 class="m-0"><strong><?= $this->session->lastname,
                    ', ',
                    $this->session->firstname ?></strong></h6>
                <!-- <p><?= $this->session->course,
                    ' - ',
                    $this->session->current_year ?></p> -->
            </div>
            <div class="col text-center">
                <a href="<?= base_url(
                    'update_account_form'
                ) ?>" class=" btn btn-outline-dark"><i class="fa fa-user" aria-hidden="true"></i></a>
                <a href="<?= base_url(
                    'logout'
                ) ?>" class=" btn btn-outline-dark">Logout</a>
            </div>
        </div>
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
                <button type="submit">
                    <?php echo $discussion_mode
                        ? 'Deactivate Discussion Mode'
                        : 'Activate Discussion Mode'; ?>
                </button>
            </form>
        </div>
</div>

<?php $this->load->view('footer'); ?>

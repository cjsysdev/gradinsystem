<?php $this->load->view('header'); ?>


<div class="container">
    <div class="dashboard">
        <?php $this->load->view('profile_only'); ?>
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

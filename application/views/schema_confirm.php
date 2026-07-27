<?php $this->load->view('header'); ?>
<div class="container mt-4">
    <?php $this->load->view('profile_only'); ?>
    <?php $this->load->view('admin/nav_bar'); ?>

    <div class="card border-warning" style="max-width: 720px;">
        <div class="card-body">
            <h4 class="card-title text-warning">
                <i class="fa fa-triangle-exclamation"></i> Confirm schema operation
            </h4>

            <p class="mb-2">
                You are about to run <strong><?= htmlspecialchars($what) ?></strong>.
            </p>

            <p class="text-muted small">
                Schema routes are only meant to be run once, during setup or an upgrade.
                They are not part of day-to-day use. This confirmation exists because
                these are plain URLs &mdash; a bookmark, browser history entry, or page
                prefetch can otherwise trigger one without anybody clicking anything.
            </p>

            <?php if (!empty($tables)): ?>
                <div class="alert alert-light border">
                    <div class="small text-muted mb-1">Tables that will be backed up first:</div>
                    <?php foreach ($tables as $t): ?>
                        <code class="mr-2"><?= htmlspecialchars($t) ?></code>
                    <?php endforeach; ?>
                    <div class="small text-muted mt-2">
                        A restorable dump is written to <code>uploads/schema_backups/</code>
                        before anything runs.
                    </div>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= base_url($route) ?>" class="mt-3">
                <input type="hidden" name="schema_guard_confirm" value="yes">
                <button type="submit" class="btn btn-warning">
                    <i class="fa fa-play"></i> Yes, run it
                </button>
                <a href="<?= base_url() ?>" class="btn btn-outline-secondary ml-2">Cancel</a>
            </form>
        </div>
    </div>
</div>
<?php $this->load->view('footer'); ?>

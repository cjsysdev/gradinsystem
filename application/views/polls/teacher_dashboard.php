<?php $this->load->view('header'); ?>
<div class="container mt-4">

  <!-- flash messages -->
  <?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($this->session->flashdata('success')) ?>
      <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
  <?php endif; ?>
  <?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= htmlspecialchars($this->session->flashdata('error')) ?>
      <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
  <?php endif; ?>

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0"><i class="fas fa-poll"></i> Polls</h3>
    <a href="<?= base_url('poll/create') ?>" class="btn btn-primary">
      <i class="fas fa-plus"></i> New Poll
    </a>
  </div>

  <?php if (empty($polls)): ?>
    <div class="text-center text-muted py-5">
      <i class="fas fa-poll fa-3x mb-3 d-block"></i>
      <p>No polls yet. Create your first one!</p>
      <a href="<?= base_url('poll/create') ?>" class="btn btn-primary">Create Poll</a>
    </div>
  <?php else: ?>
    <div class="row">
      <?php foreach ($polls as $poll): ?>
        <div class="col-md-4 mb-4">
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <h5 class="card-title mb-0"><?= htmlspecialchars($poll['title']) ?></h5>
                <?php
                  $badge = ['draft' => 'secondary', 'active' => 'success', 'closed' => 'dark'];
                  $cls   = $badge[$poll['status']] ?? 'secondary';
                ?>
                <span class="badge badge-<?= $cls ?>"><?= ucfirst($poll['status']) ?></span>
              </div>
              <p class="text-muted small mb-1">
                PIN: <strong class="text-dark"><?= $poll['pin'] ?></strong>
              </p>
              <p class="text-muted small">
                Created: <?= date('M j, Y', strtotime($poll['created_at'])) ?>
              </p>
            </div>
            <div class="card-footer bg-white d-flex justify-content-between">
              <a href="<?= base_url('poll/present/' . $poll['poll_id']) ?>"
                 class="btn btn-sm btn-outline-primary">
                <i class="fas fa-play"></i> Present
              </a>
              <button class="btn btn-sm btn-outline-danger btn-delete-poll"
                      data-id="<?= $poll['poll_id'] ?>">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>
<?php $this->load->view('footer'); ?>
<script>
document.querySelectorAll('.btn-delete-poll').forEach(btn => {
  btn.addEventListener('click', function () {
    if (!confirm('Delete this poll and all its data?')) return;
    fetch('<?= base_url('poll/delete_poll/') ?>' + this.dataset.id, { method: 'POST' })
      .then(r => r.json()).then(d => { if (d.ok) location.reload(); });
  });
});
</script>

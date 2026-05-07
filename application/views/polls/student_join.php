<?php $this->load->view('header'); ?>
<style>
  body { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); min-height: 100vh; }
  .join-card { background: #fff; border-radius: 16px; padding: 40px 32px; max-width: 420px; margin: 80px auto; box-shadow: 0 20px 60px rgba(0,0,0,.4); }
  .join-title { font-size: 1.6rem; font-weight: 800; color: #1a1a2e; margin-bottom: 6px; }
  .join-subtitle { color: #666; margin-bottom: 28px; }
  .pin-input { font-size: 2rem; letter-spacing: 8px; text-align: center; text-transform: uppercase; font-weight: 700; border: 2px solid #dee2e6; border-radius: 10px; padding: 12px; }
  .pin-input:focus { border-color: #e94560; box-shadow: 0 0 0 3px rgba(233,69,96,.2); outline: none; }
  .btn-join { background: #e94560; border: none; color: #fff; border-radius: 10px; padding: 14px; font-size: 1.1rem; font-weight: 700; width: 100%; margin-top: 16px; cursor: pointer; transition: background .2s; }
  .btn-join:hover { background: #c73652; }
</style>

<div class="join-card">
  <div class="text-center mb-3">
    <i class="fas fa-poll" style="font-size:2.5rem; color:#e94560;"></i>
  </div>
  <p class="join-title text-center">Join Poll</p>
  <p class="join-subtitle text-center">Enter the PIN shown by your teacher</p>

  <?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($this->session->flashdata('error')) ?></div>
  <?php endif; ?>

  <form method="get" action="<?= base_url('poll/join') ?>" id="join-form">
    <input type="text" name="pin" id="pin-input" class="form-control pin-input"
           maxlength="6" placeholder="XXXXXX" autocomplete="off" autofocus>
    <button type="submit" class="btn-join">
      <i class="fas fa-arrow-right"></i> Join
    </button>
  </form>
</div>

<?php $this->load->view('footer'); ?>
<script>
// Redirect GET ?pin=XXXX to /poll/join/XXXX
document.getElementById('join-form').addEventListener('submit', function (e) {
  e.preventDefault();
  const pin = document.getElementById('pin-input').value.trim().toUpperCase();
  if (pin.length < 4) return;
  window.location.href = '<?= base_url('poll/join/') ?>' + pin;
});

// Auto-uppercase input
document.getElementById('pin-input').addEventListener('input', function () {
  this.value = this.value.toUpperCase();
});
</script>

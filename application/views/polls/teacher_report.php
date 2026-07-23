<?php $this->load->view('header'); ?>
<link rel="stylesheet" href="<?= base_url('assets/fontawesome/css/all.min.css') ?>">
<style>
  .report-wrap { max-width: 900px; margin: 0 auto; padding: 24px 16px; }
  .report-header { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; margin-bottom: 24px; }
  .pin-chip { background: #f4f6f9; border-radius: 8px; padding: 6px 14px; font-weight: 700; color: #0f3460; }
  .q-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.08); padding: 20px 24px; margin-bottom: 20px; }
  .q-card-head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; }
  .q-type-badge { font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
                  border-radius: 4px; padding: 3px 8px; }
  .badge-mc { background: #e8f4fd; color: #0f3460; }
  .badge-oe { background: #fdf3e8; color: #c0392b; }
  .q-total { color: #999; font-size: .85rem; }
  .opt-row { margin-bottom: 12px; }
  .opt-row-top { display: flex; justify-content: space-between; font-weight: 600; margin-bottom: 4px; }
  .opt-row-top .opt-name.winner { color: #2ecc71; }
  .opt-row-top .opt-name.winner:after { content: " \2713"; }
  .bar-wrap { height: 10px; background: #eee; border-radius: 5px; overflow: hidden; }
  .bar-fill { height: 100%; background: #e94560; border-radius: 5px; }
  .bar-fill.winner { background: #2ecc71; }
  .no-responses { color: #aaa; text-align: center; padding: 20px 0; }

  #oe-list-wrap { display: flex; flex-wrap: wrap; gap: 8px 10px; }
  .wc-word { border-radius: 6px; padding: 4px 12px; font-weight: 700; background: #f8f9fa; }
</style>

<div class="report-wrap">
  <div class="report-header">
    <div>
      <h4 class="mb-1"><?= htmlspecialchars($poll['title']) ?></h4>
      <span class="pin-chip">PIN: <?= htmlspecialchars($poll['pin']) ?></span>
      <span class="badge badge-<?= $poll['status'] === 'closed' ? 'dark' : ($poll['status'] === 'active' ? 'success' : 'secondary') ?> ml-2">
        <?= ucfirst($poll['status']) ?>
      </span>
    </div>
    <a href="<?= base_url('poll/dashboard') ?>" class="btn btn-outline-secondary btn-sm mt-2">
      <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
  </div>

  <?php if (empty($questions)): ?>
    <p class="text-muted text-center py-5">No questions in this poll.</p>
  <?php endif; ?>

  <?php foreach ($questions as $i => $q): ?>
    <?php $is_oe = $q['question_type'] === 'open_ended'; ?>
    <div class="q-card">
      <div class="q-card-head">
        <div>
          <span class="q-type-badge <?= $is_oe ? 'badge-oe' : 'badge-mc' ?>">
            <?= $is_oe ? 'Open-Ended' : 'Multiple Choice' ?>
          </span>
          <p class="mb-0 mt-2" style="font-weight:700;font-size:1.1rem">
            Q<?= $i + 1 ?>. <?= htmlspecialchars($q['question_text']) ?>
          </p>
        </div>
        <span class="q-total"><i class="fas fa-users"></i> <?= (int) $q['total'] ?> responses</span>
      </div>

      <?php if ($is_oe): ?>
        <?php if (empty($q['results'])): ?>
          <p class="no-responses">No responses recorded.</p>
        <?php else: ?>
          <div id="oe-list-wrap">
            <?php foreach ($q['results'] as $r): ?>
              <span class="wc-word">
                <?= htmlspecialchars($r['response_text']) ?>
                <?php if ($r['count'] > 1): ?> &times;<?= (int) $r['count'] ?><?php endif; ?>
              </span>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      <?php else: ?>
        <?php if (empty($q['results'])): ?>
          <p class="no-responses">No options for this question.</p>
        <?php else: ?>
          <?php
            $max_votes = 0;
            foreach ($q['results'] as $r) {
                $max_votes = max($max_votes, (int) $r['votes']);
            }
          ?>
          <?php foreach ($q['results'] as $r): ?>
            <?php
              $votes  = (int) $r['votes'];
              $pct    = $q['total'] > 0 ? round($votes / $q['total'] * 100) : 0;
              $winner = $max_votes > 0 && $votes === $max_votes;
            ?>
            <div class="opt-row">
              <div class="opt-row-top">
                <span class="opt-name <?= $winner ? 'winner' : '' ?>"><?= htmlspecialchars($r['option_text']) ?></span>
                <span><?= $votes ?> vote<?= $votes != 1 ? 's' : '' ?> &middot; <?= $pct ?>%</span>
              </div>
              <div class="bar-wrap">
                <div class="bar-fill <?= $winner ? 'winner' : '' ?>" style="width:<?= $pct ?>%"></div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
<?php $this->load->view('footer'); ?>

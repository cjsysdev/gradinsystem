
<?php
// Only declare once at the top
if (!function_exists('truncate_html_preserve')) {
  function truncate_html_preserve($html, $maxLen) {
    $printedLength = 0;
    $tags = array();
    $result = '';
    $regex = '/<[^>]+>|[^<]+/';
    preg_match_all($regex, $html, $tokens);
    foreach ($tokens[0] as $token) {
      if ($token[0] == '<') {
        if ($token[1] == '/') {
          array_pop($tags);
          $result .= $token;
        } else {
          preg_match('/<([a-z0-9]+)(?:\s[^>]*)?>/i', $token, $tagMatch);
          if (isset($tagMatch[1])) $tags[] = $tagMatch[1];
          $result .= $token;
        }
      } else {
        $str = $token;
        if ($printedLength + mb_strlen($str) > $maxLen) {
          $result .= mb_substr($str, 0, $maxLen - $printedLength) . '...';
          break;
        } else {
          $result .= $str;
          $printedLength += mb_strlen($str);
        }
      }
    }
    while (!empty($tags)) {
      $result .= '</' . array_pop($tags) . '>';
    }
    return $result;
  }
}
?>
<?php $this->load->view('header') ?>

<div class="container">
  <div class="dashboard">
    <?php $this->load->view('profile_info') ?>
    <div class="row justify-content-center">
      <div class="col">
        <?php if ($this->session->flashdata('success')) : ?>
          <div class="alert alert-success">
            <?= $this->session->flashdata('success'); ?>
          </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('warning')) : ?>
          <div class="alert alert-warning">
            <?= $this->session->flashdata('warning'); ?>
          </div>
        <?php endif; ?>

        <!-- Dropdown to choose between Assessments and Submitted -->
        <div class="mb-4">
          <div class="dropdown">
            <button class="btn btn-secondary btn-block dropdown-toggle w-100" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              Filter: All
            </button>
            <ul class="dropdown-menu w-100 shadow-sm" aria-labelledby="filterDropdown">
              <li><a class="dropdown-item filter-option" href="#" data-filter="all">All</a></li>
              <li><a class="dropdown-item filter-option" href="#" data-filter="assessments">Missing</a></li>
              <li><a class="dropdown-item filter-option" href="#" data-filter="locked">Past due</a></li>
              <li><a class="dropdown-item filter-option" href="#" data-filter="submitted">Submitted</a></li>
            </ul>
          </div>
        </div>

        <!-- Assessments and Submitted Cards -->
        <div id="cards-container">
          <?php foreach ($assessments as $row) : ?>
            <?php
              $locked = !empty($row['locked']);
              $grant  = $row['grant'] ?? null;
              $req    = $row['request'] ?? null;
              $reqStatus = $req['status'] ?? null;
              // Locked, no live grant: the student cannot answer it directly.
              $needsRequest = $locked && !$grant;
            ?>
            <div class="card mb-4 assessment-card<?= $needsRequest ? ' locked-card' : '' ?>">
              <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                  <h4 class="card-title mb-1"><?= $row['title'] ?></h4>
                  <?php if ($grant): ?>
                    <span class="badge badge-success">Reopened</span>
                  <?php elseif ($locked): ?>
                    <span class="badge badge-secondary">Past due</span>
                  <?php else: ?>
                    <span class="badge badge-danger">Missing</span>
                  <?php endif; ?>
                </div>
                <p class="card-text mb-1" style="font-size: small;">
                  <span class="text-secondary"><?= convert_datetime_string($row['due']) ?> • <span><?= $row['type'] ?> • <?= $row['assessment_id'] ?></span>
                </p>
              </div>
              <div class="card-body">
                <?php
                  $desc = $row['description'];
                  $maxLen = 120;
                  $plain = strip_tags($desc);
                  $isLong = mb_strlen($plain) > $maxLen;
                  $shortDesc = $isLong ? truncate_html_preserve($desc, $maxLen) : $desc;
                ?>
                <div class="card-text mb-3 description-text">
                  <span class="desc-short"<?= $isLong ? '' : ' style="display:inline"' ?>><?= $shortDesc ?></span>
                  <?php if ($isLong): ?>
                    <span class="desc-full" style="display:none;"><?= $desc ?></span>
                    <button type="button" class="btn btn-link btn-sm p-0 see-more-btn">See more</button>
                  <?php endif; ?>
                </div>
                <?php if ($needsRequest): ?>
                  <?php if ($reqStatus === 'pending'): ?>
                    <button type="button" class="btn btn-secondary btn-block" disabled>
                      <i class="fa fa-hourglass-half"></i> Request pending
                    </button>
                    <small class="text-muted d-block text-center mt-1">Waiting for your instructor to review it.</small>
                  <?php else: ?>
                    <?php if ($reqStatus === 'rejected'): ?>
                      <div class="alert alert-warning py-2 small mb-2">
                        <strong>Request denied.</strong>
                        <?= !empty($req['admin_notes']) ? htmlspecialchars($req['admin_notes']) : '' ?>
                      </div>
                    <?php elseif ($reqStatus === 'approved'): ?>
                      <div class="alert alert-secondary py-2 small mb-2">Your reopened window has ended.</div>
                    <?php endif; ?>
                    <button type="button" class="btn btn-warning btn-block request-access-btn"
                            data-id="<?= (int) $row['assessment_id'] ?>"
                            data-title="<?= htmlspecialchars(strip_tags($row['title']), ENT_QUOTES) ?>">
                      <i class="fa fa-paper-plane"></i> <?= $reqStatus ? 'Request again' : 'Request access' ?>
                    </button>
                    <small class="text-muted d-block text-center mt-1">
                      This is past its due date. Ask your instructor to reopen it.
                    </small>
                  <?php endif; ?>
                <?php elseif (!clearance_allows($row)): ?>
                  <!-- The gate itself is enforced server-side at every entry
                       point (clearance_helper.php); this only stops the student
                       walking into a dead end. -->
                  <button type="button" class="btn btn-secondary btn-block" disabled>
                    <i class="fa fa-lock"></i> Clearance required
                  </button>
                  <small class="text-muted d-block text-center mt-1">
                    Settle your clearance to take this <?= ($row['iotype_id'] == 3) ? 'exam' : 'assessment' ?>.
                  </small>
                <?php else: ?>
                  <?php if ($grant): ?>
                    <small class="text-success d-block mb-1">
                      <i class="fa fa-unlock"></i> Reopened until <?= convert_datetime_string($grant['granted_until']) ?>
                    </small>
                  <?php endif; ?>
                  <a href="<?= base_url('assessment/' . $row['assessment_id']) ?>" class="btn btn-info btn-block">
                    <?= ($row['iotype_id'] == 3) ? "Start Exam" : "Create" ?>
                  </a>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>

          <?php if (!$this->session->exam_term): ?>
            <?php foreach ($submitted as $row) : ?>
              <div class="card mb-4 submitted-card">
                <div class="card-header">
                  <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-1"><?= $row['title'] ?></h4>
                    <span>
                      <?php if (!empty($row['was_late'])): ?><span class="badge badge-warning">Late (approved)</span><?php endif; ?>
                      <span class="badge badge-success">Submitted</span>
                    </span>
                  </div>
                  <p class="card-text mb-1" style="font-size: small;">
                    <span class="text-secondary"><?= convert_datetime_string($row['due']) ?> • <span><?= $row['type'] ?> • <?= $row['assessment_id'] ?></span>
                  </p>
                </div>
                <div class="card-body">
                  <?php
                    $desc = isset($row['description']) ? $row['description'] : '';
                    $maxLen = 120;
                    $plain = strip_tags($desc);
                    $isLong = mb_strlen($plain) > $maxLen;
                    $shortDesc = $isLong ? truncate_html_preserve($desc, $maxLen) : $desc;
                  ?>
                  <div class="card-text mb-3 description-text">
                    <span class="desc-short"<?= $isLong ? '' : ' style="display:inline"' ?>><?= $shortDesc ?></span>
                    <?php if ($isLong): ?>
                      <span class="desc-full" style="display:none;"><?= $desc ?></span>
                      <button type="button" class="btn btn-link btn-sm p-0 see-more-btn">See more</button>
                    <?php endif; ?>
                  </div>
                  <a href="<?= base_url('student_submission/' . $row['classwork_id']) ?>" class="btn btn-outline-info btn-block">View</a>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Request access modal (one shared form, filled per card) -->
<div class="modal fade" id="requestAccessModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form class="modal-content" method="post" action="<?= base_url('classwork/request_access') ?>">
      <div class="modal-header">
        <h5 class="modal-title">Request access</h5>
        <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
        <p class="mb-2">Ask your instructor to reopen <strong id="requestAccessTitle"></strong>.</p>
        <input type="hidden" name="assessment_id" id="requestAccessId">
        <label for="requestAccessReason" class="small">Why did you miss it?</label>
        <textarea class="form-control" name="reason" id="requestAccessReason" rows="3" maxlength="500" required></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-warning">Send request</button>
      </div>
    </form>
  </div>
</div>

<script>
  document.querySelectorAll('.request-access-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      document.getElementById('requestAccessId').value = this.dataset.id;
      document.getElementById('requestAccessTitle').textContent = this.dataset.title;
      document.getElementById('requestAccessReason').value = '';
      if (window.jQuery && jQuery.fn.modal) {
        jQuery('#requestAccessModal').modal('show');
      } else if (window.bootstrap) {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('requestAccessModal')).show();
      }
    });
  });

  document.querySelectorAll('.filter-option').forEach(option => {
    option.addEventListener('click', function(e) {
      e.preventDefault();
      const filterValue = this.getAttribute('data-filter');
      const filterButton = document.getElementById('filterDropdown');
      const assessmentCards = document.querySelectorAll('.assessment-card');
      const submittedCards = document.querySelectorAll('.submitted-card');

      // Update the dropdown button text
      filterButton.textContent = `Filter: ${this.textContent}`;

      // Filter cards based on the selected option
      if (filterValue === 'all') {
        assessmentCards.forEach(card => card.style.display = 'block');
        submittedCards.forEach(card => card.style.display = 'block');
      } else if (filterValue === 'assessments') {
        assessmentCards.forEach(card => card.style.display = 'block');
        submittedCards.forEach(card => card.style.display = 'none');
      } else if (filterValue === 'locked') {
        assessmentCards.forEach(card => card.style.display = card.classList.contains('locked-card') ? 'block' : 'none');
        submittedCards.forEach(card => card.style.display = 'none');
      } else if (filterValue === 'submitted') {
        assessmentCards.forEach(card => card.style.display = 'none');
        submittedCards.forEach(card => card.style.display = 'block');
      }
    });
  });

  // See more/less toggle for PHP-generated descriptions
  document.querySelectorAll('.see-more-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const container = this.closest('.description-text');
      const shortSpan = container.querySelector('.desc-short');
      const fullSpan = container.querySelector('.desc-full');
      if (fullSpan.style.display === 'none') {
        shortSpan.style.display = 'none';
        fullSpan.style.display = 'inline';
        this.textContent = 'See less';
      } else {
        shortSpan.style.display = 'inline';
        fullSpan.style.display = 'none';
        this.textContent = 'See more';
      }
    });
  });
</script>

<?php $this->load->view('footer') ?>
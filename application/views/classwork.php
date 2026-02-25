
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
              <li><a class="dropdown-item filter-option" href="#" data-filter="submitted">Submitted</a></li>
            </ul>
          </div>
        </div>

        <!-- Assessments and Submitted Cards -->
        <div id="cards-container">
          <?php foreach ($assessments as $row) : ?>
            <div class="card mb-4 assessment-card">
              <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                  <h4 class="card-title mb-1"><?= $row['title'] ?></h4>
                  <span class="badge badge-danger">Missing</span>
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
                <a href="<?= base_url('assessment/' . $row['assessment_id']) ?>" class="btn btn-info btn-block">
                  <?= ($row['iotype_id'] == 3) ? "Start Exam" : "Create" ?>
                </a>
              </div>
            </div>
          <?php endforeach; ?>

          <?php if (!$this->session->exam_term): ?>
            <?php foreach ($submitted as $row) : ?>
              <div class="card mb-4 submitted-card">
                <div class="card-header">
                  <div class="d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-1"><?= $row['title'] ?></h4>
                    <span class="badge badge-success">Submitted</span>
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

<script>
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
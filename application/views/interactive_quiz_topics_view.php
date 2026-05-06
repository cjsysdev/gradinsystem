<?php $this->load->view('header') ?>

<style>
.disc-tab-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 20px;
    font-size: 14px;
    font-weight: 600;
    border: 2px solid #dee2e6;
    border-radius: 6px;
    cursor: pointer;
    background: #fff;
    color: #555;
    transition: all .2s;
}
.disc-tab-btn.active {
    background: #17a2b8;
    color: #fff;
    border-color: #17a2b8;
}
.disc-tab-btn:not(.active):hover {
    background: #f8f9fa;
    color: #333;
}
.disc-tab-btn .badge-count {
    background: rgba(0,0,0,.1);
    border-radius: 20px;
    padding: 1px 8px;
    font-size: 11px;
}
.disc-tab-btn.active .badge-count {
    background: rgba(255,255,255,.25);
}
.disc-panel { display: none; }
.disc-panel.active { display: block; }
</style>

<div class="container">
    <div class="dashboard">
        <?php $this->load->view('profile_info') ?>
    </div>

    <h2 class="mb-4 text-center">Discussion &amp; Topics</h2>

    <!-- Tab buttons -->
    <div class="mb-4 d-flex" style="gap:10px;">
        <button class="disc-tab-btn active flex-fill justify-content-center" onclick="switchTab('static', this)">
            Static Discussions
            <span class="badge-count"><?= count($static_topics) ?></span>
        </button>
        <!-- <button class="disc-tab-btn flex-fill justify-content-center" onclick="switchTab('interactive', this)">
            Interactive Topics
            <span class="badge-count"><?= count($interactive_topics) ?></span>
        </button> -->
    </div>

    <!-- ══ STATIC DISCUSSIONS ════════════════════════════════════ -->
    <div id="panel-static" class="disc-panel active">
        <?php if (empty($static_topics)): ?>
            <div class="alert alert-info">No static discussions found.</div>
        <?php else: ?>
        <div class="row">
            <?php foreach ($static_topics as $t): ?>
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h4 class="card-title"><?= htmlspecialchars($t['title']) ?></h4>
                        <p class="card-text"><?= htmlspecialchars($t['description']) ?></p>
                        <?php if (!empty($t['link'])): ?>
                        <a href="<?= htmlspecialchars($t['link']) ?>"
                           class="btn btn-outline-info btn-block btn-xl"
                           <?= strpos($t['link'], 'http') === 0 ? 'target="_blank"' : '' ?>>
                            <?= strpos($t['link'], '.pdf') !== false ? 'Open PDF' : 'Learn More' ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- ══ INTERACTIVE TOPICS ════════════════════════════════════ -->
    <div id="panel-interactive" class="disc-panel">
        <?php if (empty($interactive_topics)): ?>
            <div class="alert alert-info">
                No interactive topics found. Add <code>.json</code> files to <code>assets/json/</code>.
            </div>
        <?php else: ?>
        <div class="row">
            <?php foreach ($interactive_topics as $t): ?>
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h4 class="card-title"><?= htmlspecialchars($t['title']) ?></h4>
                        <p class="card-text"><?= htmlspecialchars($t['description']) ?></p>
                        <a href="<?= $t['url'] ?>" class="btn btn-outline-info btn-block btn-xl">
                            <?= $t['format'] === 'discussion' ? 'Start Discussion' : 'Start Quiz' ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="<?= base_url('./assets/pdf.js') ?>"></script>

<script>
function switchTab(panel, btn) {
    document.querySelectorAll('.disc-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.disc-tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('panel-' + panel).classList.add('active');
    btn.classList.add('active');
}
</script>

<?php $this->load->view('footer') ?>

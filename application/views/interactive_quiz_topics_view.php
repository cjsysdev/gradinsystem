<?php $this->load->view('header') ?>

<style>
.topic-card {
    border: none;
    box-shadow: 0 2px 10px rgba(0,0,0,.08);
    border-radius: 10px;
    transition: transform .15s, box-shadow .15s;
    overflow: hidden;
    height: 100%;
}
.topic-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 18px rgba(0,0,0,.13);
}
.topic-card .card-header {
    background: #04AA6D;
    color: #fff;
    padding: 14px 16px;
}
.topic-card .card-header h5 { margin: 0; font-size: 16px; }
.topic-badge {
    background: rgba(255,255,255,.2);
    border-radius: 20px;
    padding: 2px 10px;
    font-size: 12px;
    margin-right: 4px;
}
.topic-card .card-body { padding: 14px 16px; }
.topic-desc { color: #555; font-size: 14px; margin-bottom: 12px; }
</style>

<div class="container mt-4 mb-5">
    <?php $this->load->view('profile_info') ?>

    <div class="d-flex align-items-center mb-4 mt-2">
        <img src="<?= base_url('assets/streak.png') ?>" height="30" alt="streak" class="mr-2">
        <h4 class="mb-0" style="color:#04AA6D;"><strong>Interactive Learning Topics</strong></h4>
    </div>

    <?php if (empty($topics)): ?>
        <div class="alert alert-info">
            No topics found. Add <code>.json</code> files to <code>assets/json/</code> to create topics.
        </div>
    <?php else: ?>
    <div class="row">
        <?php foreach ($topics as $t): ?>
        <div class="col-sm-6 col-md-4 mb-4">
            <div class="topic-card card">
                <div class="card-header">
                    <h5><?= htmlspecialchars($t['title']) ?></h5>
                    <div class="mt-1">
                        <span class="topic-badge"><?= (int)$t['sections'] ?> sections</span>
                        <span class="topic-badge"><?= (int)$t['questions'] ?> questions</span>
                    </div>
                </div>
                <div class="card-body d-flex flex-column">
                    <?php if (!empty($t['description'])): ?>
                    <p class="topic-desc"><?= htmlspecialchars($t['description']) ?></p>
                    <?php endif; ?>
                    <div class="mt-auto">
                        <a href="<?= $t['url'] ?>" class="btn btn-success btn-block">
                            Start &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<textarea id="code-editor" style="display:none;"></textarea>
<?php $this->load->view('footer') ?>

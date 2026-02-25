<?php $this->load->view('header'); ?>

<div class="container mt-4 mb-5">
    <div class="dashboard">
        <?php $this->load->view('profile_info') ?>
    </div>
    <h2 class="mb-4 text-center">Discussion & Topics</h2>
    <div class="row">
        <?php foreach ($topics as $topic): ?>
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h4 class="card-title"><?= htmlspecialchars($topic['title']) ?></h4>
                        <p class="card-text"><?= htmlspecialchars($topic['description']) ?></p>
                        <?php if (!empty($topic['link'])): ?>
                            <!-- <a href="<?= htmlspecialchars($topic['link']) ?>" target="_self" class="btn btn-outline-info btn-block btn-xl">
                                Learn More
                            </a> -->
                            <a href="<?= htmlspecialchars($topic['link']) ?>" class="btn btn-outline-info btn-block btn-xl">
                                <?= strpos($topic['link'], '.pdf') !== false ? 'Open PDF' : 'Learn More' ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
</div>

<script src="<?= base_url('./assets/pdf.js') ?>"></script>


<?php $this->load->view('footer'); ?>
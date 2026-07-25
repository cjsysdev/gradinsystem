<?php
// Minimal read-only preview for the "discussion" and "quiz" Worksheet
// Generator output types (these aren't rendered by a widget view, unlike
// lab_worksheet / worksheet_table, so they get a small bespoke partial).
//
// $mode — 'discussion' | 'quiz'
// $data — the decoded, validated generated JSON
?>
<?php if ($mode === 'discussion'): ?>
    <h6><?= htmlspecialchars($data['title'] ?? '') ?></h6>
    <?php if (!empty($data['description'])): ?>
        <p class="text-muted small"><?= htmlspecialchars($data['description']) ?></p>
    <?php endif; ?>
    <?php foreach (($data['sections'] ?? []) as $section): ?>
        <div class="border rounded p-2 mb-2">
            <div class="small text-muted mb-1">Section <?= (int) ($section['id'] ?? 0) ?></div>
            <strong><?= htmlspecialchars($section['title'] ?? '') ?></strong>
            <div class="small mt-1"><?= $section['lesson'] ?? '' ?></div>
            <?php if (!empty($section['quiz'])): ?>
                <div class="mt-2 p-2 bg-light rounded">
                    <div class="small font-weight-bold mb-1"><i class="fa fa-circle-question"></i> <?= htmlspecialchars($section['quiz']['question'] ?? '') ?></div>
                    <?php if (!empty($section['quiz']['code'])): ?>
                        <pre class="small bg-dark text-light p-2 rounded"><code><?= $section['quiz']['code'] ?></code></pre>
                    <?php endif; ?>
                    <ol type="A" class="small mb-0">
                        <?php foreach (($section['quiz']['options'] ?? []) as $i => $opt): ?>
                            <li<?= ((int) ($section['quiz']['correct'] ?? -1) === $i) ? ' class="text-success font-weight-bold"' : '' ?>>
                                <?= htmlspecialchars($opt) ?><?= ((int) ($section['quiz']['correct'] ?? -1) === $i) ? ' <i class="fa fa-check"></i>' : '' ?>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php elseif ($mode === 'quiz'): ?>
    <h6><?= htmlspecialchars($data['title'] ?? '') ?></h6>
    <?php foreach (($data['questions'] ?? []) as $q): ?>
        <div class="border rounded p-2 mb-2">
            <div class="small text-muted mb-1">
                Q<?= (int) ($q['id'] ?? 0) ?>
                <span class="badge badge-secondary"><?= htmlspecialchars($q['bloomLevel'] ?? '') ?></span>
            </div>
            <strong><?= htmlspecialchars($q['question'] ?? '') ?></strong>
            <?php if (!empty($q['code'])): ?>
                <pre class="small bg-dark text-light p-2 rounded mt-1"><code><?= $q['code'] ?></code></pre>
            <?php endif; ?>
            <ol type="A" class="small mb-0 mt-1">
                <?php foreach (($q['choices'] ?? []) as $choice): ?>
                    <li<?= ($choice === ($q['answer'] ?? null)) ? ' class="text-success font-weight-bold"' : '' ?>>
                        <?= htmlspecialchars($choice) ?><?= ($choice === ($q['answer'] ?? null)) ? ' <i class="fa fa-check"></i>' : '' ?>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php $this->load->view('header'); ?>

<div class="container">
    <?php $this->load->view('profile_info'); ?>

    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0"><?= htmlspecialchars($assessment['title']) ?></h5>
        </div>
        <div class="card-body">
            <?php if (!empty($config['prompt'])): ?>
                <p class="lead"><?= htmlspecialchars($config['prompt']) ?></p>
            <?php endif; ?>

            <div class="input-group mb-4">
                <input type="text" id="note-input" class="form-control" maxlength="280" placeholder="Add your idea...">
                <div class="input-group-append">
                    <button class="btn btn-primary" id="add-note-btn"><i class="fas fa-plus"></i> Post</button>
                </div>
            </div>

            <p class="text-muted small">
                Votes remaining: <span id="votes-remaining"><?= (int) ($config['max_votes_per_student'] ?? 3) ?></span>
                / <?= (int) ($config['max_votes_per_student'] ?? 3) ?>
            </p>

            <div id="notes-board" class="d-flex flex-wrap" style="gap:14px;"></div>
        </div>
        <div class="card-footer text-center">
            <p class="text-muted mb-0">This board is shared with your whole class &mdash; updates appear within a couple seconds.</p>
        </div>
    </div>
</div>

<script>
(function () {
    const BASE = '<?= base_url() ?>';
    const assessmentId = <?= (int) $assessment['assessment_id'] ?>;
    const myStudentId = <?= json_encode((string) $student_id) ?>;
    const maxVotes = <?= (int) ($config['max_votes_per_student'] ?? 3) ?>;

    const board = document.getElementById('notes-board');
    const noteInput = document.getElementById('note-input');
    const addBtn = document.getElementById('add-note-btn');
    const votesRemainingEl = document.getElementById('votes-remaining');

    const WC_COLORS = ['#fff3cd', '#d1e7dd', '#cfe2ff', '#f8d7da', '#e2d9f3', '#d1ecf1'];

    function renderNotes(notes) {
        board.innerHTML = '';
        let usedVotes = 0;

        notes.forEach((note, i) => {
            const voted = (note.votes || []).includes(myStudentId);
            if (voted) usedVotes++;

            const card = document.createElement('div');
            card.className = 'card';
            card.style.width = '220px';
            card.style.backgroundColor = WC_COLORS[i % WC_COLORS.length];
            card.innerHTML = `
                <div class="card-body py-2 px-3">
                    <p class="mb-1" style="white-space:pre-wrap;">${escapeHtml(note.text)}</p>
                    <p class="text-muted small mb-2">&mdash; ${escapeHtml(note.author || 'Anonymous')}</p>
                    <button type="button" class="btn btn-sm ${voted ? 'btn-success' : 'btn-outline-secondary'} vote-btn" data-id="${note.id}">
                        <i class="fas fa-thumbs-up"></i> ${(note.votes || []).length}
                    </button>
                </div>`;
            board.appendChild(card);
        });

        votesRemainingEl.textContent = Math.max(0, maxVotes - usedVotes);

        board.querySelectorAll('.vote-btn').forEach(btn => {
            btn.addEventListener('click', () => toggleVote(btn.dataset.id));
        });
    }

    function escapeHtml(s) {
        const div = document.createElement('div');
        div.textContent = s || '';
        return div.innerHTML;
    }

    function fetchState() {
        fetch(BASE + 'BrainstormController/state/' + assessmentId)
            .then(r => r.json())
            .then(d => { if (d.ok) renderNotes(d.notes || []); })
            .catch(() => {});
    }

    function addNote() {
        const text = noteInput.value.trim();
        if (!text) return;
        fetch(BASE + 'BrainstormController/add_note/' + assessmentId, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'text=' + encodeURIComponent(text),
        })
        .then(r => r.json())
        .then(d => { if (d.ok) { noteInput.value = ''; fetchState(); } });
    }

    function toggleVote(noteId) {
        fetch(BASE + 'BrainstormController/toggle_vote/' + assessmentId, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'note_id=' + encodeURIComponent(noteId),
        })
        .then(r => r.json())
        .then(d => { if (d.ok) fetchState(); else if (d.msg) alert(d.msg); });
    }

    addBtn.addEventListener('click', addNote);
    noteInput.addEventListener('keydown', e => { if (e.key === 'Enter') addNote(); });

    fetchState();
    setInterval(fetchState, 2000);
})();
</script>

<?php $this->load->view('footer'); ?>

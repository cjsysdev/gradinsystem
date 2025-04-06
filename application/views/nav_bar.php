<div class="container" id="nav-bar-container">
    <!-- The navigation bar will be dynamically hidden if discussion mode is activated -->
    <?php if (!$this->session->exam_term): ?>
        <div class="form-group row">
            <a href="<?= base_url('attendance') ?>" class="btn btn-outline-secondary col m-2">Stream</a>
            <a href="<?= base_url('classwork') ?>" class="btn btn-outline-success col m-2">Classwork</a>
            <!-- <a href="<?= base_url('output_upload') ?>" class="btn btn-outline-secondary col m-2">Project</a> -->
            <a href="<?= base_url('grades') ?>" class="btn btn-outline-secondary col m-2">Grades</a>
        </div>
    <?php else: ?>
        <div class="form-group row">
            <a href="<?= base_url('classwork') ?>" class="btn btn-outline-success col m-2">Midterm Exam</a>
        </div>
    <?php endif; ?>
</div>

<script>
    // Function to check discussion mode status
    function checkDiscussionMode() {
        fetch('<?= base_url('student/get-discussion-mode') ?>')
            .then(response => response.json())
            .then(data => {
                if (data.discussion_mode) {
                    // Hide the navigation bar if discussion mode is activated
                    document.getElementById('nav-bar-container').style.display = 'none';
                } else {
                    // Show the navigation bar if discussion mode is deactivated
                    document.getElementById('nav-bar-container').style.display = 'block';
                }
            })
            .catch(error => console.error('Error fetching discussion mode:', error));
    }

    // Check discussion mode on page load
    checkDiscussionMode();

    // Optionally, check discussion mode periodically (e.g., every 24 hours)
    setInterval(checkDiscussionMode, 3000); // 24 hours in milliseconds
</script>
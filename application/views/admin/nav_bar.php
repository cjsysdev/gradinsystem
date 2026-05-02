<div class="container mt-3" id="nav-bar-container">
    <div class="form-group row">
        <a href="<?= base_url(
                        'dashboard'
                    ) ?>" class="btn btn-outline-secondary col m-2">Dashboard</a>
          <a href="<?= base_url(
                        'all_submissions/1'
                    ) ?>" class="btn btn-outline-secondary col m-2">Assessments</a>
        <a href="<?= base_url(
                        'all_submissions/1'
                    ) ?>" class="btn btn-outline-secondary col m-2">Classwork</a>
        <a href="<?= base_url(
                        'AdminController/student_submissions'
                    ) ?>" class="btn btn-outline-secondary col m-2">Submissions</a>
        <a href="<?= base_url(
                        'view_attendance'
                    ) ?>" class="btn btn-outline-secondary col m-2">Attendance</a>
        <a href="<?= base_url(
                        'interactive_quiz/analytics'
                    ) ?>" class="btn btn-outline-secondary col m-2">IQ Analytics</a>
        <a href="<?= base_url(
                        'interactive_quiz/manage_topics'
                    ) ?>" class="btn btn-outline-secondary col m-2">IQ Topics</a>
    </div>
</div>
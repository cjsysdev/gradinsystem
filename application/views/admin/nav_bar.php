<div class="container mt-3" id="nav-bar-container">
    <div class="form-group row">
        <a href="<?= base_url(
                        'dashboard'
                    ) ?>" class="btn btn-outline-secondary col m-2" title="Dashboard"><i class="fa fa-tachometer-alt"></i></a>
          <a href="<?= base_url(
                        'manage_assessments'
                    ) ?>" class="btn btn-outline-secondary col m-2" title="Assessments"><i class="fa fa-tasks"></i></a>
        <a href="<?= base_url(
                        'all_submissions/1'
                    ) ?>" class="btn btn-outline-secondary col m-2" title="Classwork"><i class="fa fa-folder-open"></i></a>
        <a href="<?= base_url(
                        'AdminController/student_submissions'
                    ) ?>" class="btn btn-outline-secondary col m-2" title="Submissions"><i class="fa fa-file-alt"></i></a>
        <a href="<?= base_url(
                        'view_attendance'
                    ) ?>" class="btn btn-outline-secondary col m-2" title="Attendance"><i class="fa fa-calendar-check"></i></a>
        <a href="<?= base_url(
                        'admin/emergency_contacts'
                    ) ?>" class="btn btn-outline-secondary col m-2" title="Emergency Contacts"><i class="fa fa-phone"></i></a>
        <a href="<?= base_url(
                        'admin/student_violations'
                    ) ?>" class="btn btn-outline-secondary col m-2" title="Student Violations"><i class="fa fa-exclamation-triangle"></i></a>
        <!-- <a href="<?= base_url(
                        'interactive_quiz/analytics'
                    ) ?>" class="btn btn-outline-secondary col m-2" title="IQ Analytics"><i class="fa fa-chart-line"></i></a>
        <a href="<?= base_url(
                        'interactive_quiz/manage_topics'
                    ) ?>" class="btn btn-outline-secondary col m-2" title="IQ Topics"><i class="fa fa-book"></i></a> -->
        <a href="<?= base_url(
                        'admin/students_by_section'
                    ) ?>" class="btn btn-outline-secondary col m-2" title="Students by Section"><i class="fa fa-users"></i></a>
        <a href="<?= base_url(
                        'admin/semesters'
                    ) ?>" class="btn btn-outline-secondary col m-2" title="Semesters"><i class="fa fa-calendar-alt"></i></a>
        <a href="<?= base_url(
                        'admin/student_requests'
                    ) ?>" class="btn btn-outline-secondary col m-2" title="Student Requests"><i class="fa fa-hand"></i></a>
        <a href="<?= base_url(
                        'poll/dashboard'
                    ) ?>" class="btn btn-outline-danger col m-2" title="Polls"><i class="fa fa-poll"></i></a>
    </div>
</div>
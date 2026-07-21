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
        <!-- <a href="<?= base_url(
                        'group_submissions/1'
                    ) ?>" class="btn btn-outline-secondary col m-2" title="Group Submissions"><i class="fa fa-user-group"></i></a> -->
        <!-- <a href="<?= base_url(
                        'AdminController/student_submissions'
                    ) ?>" class="btn btn-outline-secondary col m-2" title="Submissions"><i class="fa fa-file-alt"></i></a> -->
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
                        'AdminController/manage_discussions'
                    ) ?>" class="btn btn-outline-secondary col m-2" title="Discussions"><i class="fa fa-comments"></i></a> -->
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
        <?php $pw_pending = $this->db->table_exists('password_reset_requests') ? $this->password_reset_request->count_pending() : 0; ?>
        <a href="<?= base_url(
                        'admin/password_resets'
                    ) ?>" class="btn btn-outline-secondary col m-2 position-relative" title="Password Resets"><i class="fa fa-key"></i>
            <?php if ($pw_pending > 0): ?>
                <span class="badge badge-danger position-absolute" style="top:0; right:0; transform:translate(25%,-25%);"><?= $pw_pending ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= base_url(
                        'poll/dashboard'
                    ) ?>" class="btn btn-outline-secondary col m-2" title="Polls"><i class="fa fa-poll"></i></a>
        <a href="<?= base_url(
                        'Groupings'
                    ) ?>" class="btn btn-outline-secondary col m-2" title="Groupings"><i class="fa fa-people-group"></i></a>
        <a href="<?= base_url(
                        'admin/project_logs'
                    ) ?>" class="btn btn-outline-secondary col m-2" title="Project Logs"><i class="fa fa-diagram-project"></i></a>
        <?php $over_max_count = $this->classworks->count_scores_exceeding_max(); ?>
        <a href="<?= base_url(
                        'admin/score_integrity'
                    ) ?>" class="btn btn-outline-secondary col m-2 position-relative" title="Score Integrity"><i class="fa fa-scale-unbalanced"></i>
            <?php if ($over_max_count > 0): ?>
                <span class="badge badge-danger position-absolute" style="top:0; right:0; transform:translate(25%,-25%);"><?= $over_max_count ?></span>
            <?php endif; ?>
        </a>
        <!-- <a href="<?= base_url(
                        'uncleared_students'
                    ) ?>" class="btn btn-outline-secondary col m-2" title="Uncleared Students"><i class="fa fa-user-times"></i></a> -->
    </div>
</div>
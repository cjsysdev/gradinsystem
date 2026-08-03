<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'main';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Authentication Routes
$route['login'] = 'AuthenticationController/login';
$route['logout'] = 'AuthenticationController/logout';

// Forgot Password Routes (student-facing, public)
$route['forgot_password'] = 'AuthenticationController/forgot_password';
$route['forgot_password/submit'] = 'AuthenticationController/submit_forgot_password';

// Password Reset Routes (admin — confirm & issue temporary credentials)
$route['admin/password_resets'] = 'AdminStudentController/password_resets';
$route['admin/process_password_reset'] = 'AdminStudentController/process_password_reset';

// Student Routes
$route['student_info'] = 'StudentController/student_info';
$route['find_id'] = 'StudentController/find_id';
$route['get_id'] = 'StudentController/get_id';
$route['update_account'] = 'StudentController/update_account';
$route['update_account_form'] = 'StudentController/update_account_form';
$route['emergency_contacts'] = 'StudentController/emergency_contacts';
$route['save_emergency_contact'] = 'StudentController/save_emergency_contact';
$route['delete_emergency_contact/(:num)'] = 'StudentController/delete_emergency_contact/$1';
$route['set_primary_contact/(:num)'] = 'StudentController/set_primary_contact/$1';
$route['student/get-discussion-mode'] = 'StudentController/get_discussion_mode';
$route['student/add_section'] = 'StudentController/add_section';
$route['student/section'] = 'StudentController/section';

// Grades Routes
$route['grades'] = 'GradesController/grades';
$route['grades/all'] = 'GradesController/AllSectionGrades';
$route['sectiongrades/(:any)'] = 'GradesController/sectionGrades/$1';
$route['sectionFinalGrades/(:any)'] = 'GradesController/sectionFinalGrades/$1';

// Student Request Routes (student-facing — combined)
$route['requests'] = 'StudentController/requests';

// Student Request Routes (student-facing — absence excuses)
$route['advance_excuse'] = 'StudentController/advance_excuse';
$route['advance_excuse/submit'] = 'StudentController/submit_advance_excuse';
$route['advance_excuse/cancel/(:num)'] = 'StudentController/cancel_advance_excuse/$1';

// Student Request Routes (student-facing — leaving passes)
$route['leaving_pass'] = 'StudentController/leaving_pass';
$route['leaving_pass/submit'] = 'StudentController/submit_leaving_pass';
$route['leaving_pass/cancel/(:num)'] = 'StudentController/cancel_leaving_pass/$1';

// Student Request Routes (admin — unified view)
$route['admin/student_requests'] = 'AdminStudentController/student_requests';
$route['admin/process_student_request'] = 'AdminStudentController/process_student_request';
// Legacy redirects so old bookmarks still work
$route['admin/advance_excuses'] = 'AdminStudentController/student_requests';
$route['admin/leaving_passes'] = 'AdminStudentController/student_requests';

// Attendance Routes
$route['attendance'] = 'AttendanceController/attendance_main';
$route['attendance_visualizer'] = 'AttendanceController/attendance_visualizer';
$route['add_reason'] = 'AttendanceController/add_reason';

// Classwork Routes
$route['classwork'] = 'ClassworkController/classwork';
$route['submit_classwork'] = 'ClassworkController/submit_classwork';
$route['student_submission/(:num)'] = 'ClassworkController/student_submission/$1';
$route['start_class'] = 'ClassworkController/start_class';
$route['add_score'] = 'ClassworkController/add_score';
$route['error_submission'] = 'ClassworkController/error_submission';
// Removed: add_rand_score was an unauthenticated GET route that wrote an
// arbitrary, unclamped score straight into classworks. Use
// AdminController/add_rand_score_incremental instead.

// Discussion Routes
$route['discussion'] = 'DiscussionController/index';

// Assessment Routes
$route['assessment/(:num)'] = 'AssessmentController/assessment_view_code/$1';
$route['assessment_view'] = 'AssessmentController/assessment_view';
$route['upload_activity'] = 'AssessmentController/upload_activity';

// Quiz Routes
$route['quiz/(:num)'] = 'QuizController/index/$1';
$route['quiz/submit/(:num)'] = 'QuizController/submit/$1';
$route['quiz/check_session'] = 'QuizController/check_session';

// Secure Quiz Routes (widget-driven — see SecureQuizController)
$route['secure_quiz/test'] = 'SecureQuizController/test';
$route['secure_quiz/submit_test'] = 'SecureQuizController/submit_test';
$route['secure_quiz/(:num)'] = 'SecureQuizController/index/$1';
$route['secure_quiz/submit/(:num)'] = 'SecureQuizController/submit/$1';

// Interactive Quiz Routes
$route['interactive_quiz/topics'] = 'InteractiveQuizController/list_topics';
$route['interactive_quiz/data/(:any)'] = 'InteractiveQuizController/get_data/$1';
$route['interactive_quiz/save_result'] = 'InteractiveQuizController/save_result';
$route['interactive_quiz/record_attempt'] = 'InteractiveQuizController/record_attempt';
$route['interactive_quiz/analytics/(:any)'] = 'InteractiveQuizController/analytics/$1';
$route['interactive_quiz/analytics'] = 'InteractiveQuizController/analytics';
$route['interactive_quiz/manage_topics'] = 'InteractiveQuizController/manage_topics';
$route['interactive_quiz/upload_topic'] = 'InteractiveQuizController/upload_topic';
$route['interactive_quiz/delete_topic/(:any)'] = 'InteractiveQuizController/delete_topic/$1';
$route['interactive_quiz/edit_topic/(:any)'] = 'InteractiveQuizController/edit_topic/$1';
$route['interactive_quiz/save_question/(:any)'] = 'InteractiveQuizController/save_question/$1';
$route['interactive_quiz/delete_question/(:any)'] = 'InteractiveQuizController/delete_question/$1';
$route['interactive_quiz/save_topic_settings/(:any)'] = 'InteractiveQuizController/save_topic_settings/$1';
$route['interactive_quiz/load/(:any)/(:num)'] = 'InteractiveQuizController/load/$1/$2';
$route['interactive_quiz/load/(:any)'] = 'InteractiveQuizController/load/$1';
$route['interactive_quiz/discussion/(:any)/(:num)'] = 'InteractiveQuizController/discussion/$1/$2';
$route['interactive_quiz/discussion/(:any)'] = 'InteractiveQuizController/discussion/$1';
$route['interactive_quiz/micro/(:any)/(:num)'] = 'InteractiveQuizController/micro/$1/$2';
$route['interactive_quiz/micro/(:any)'] = 'InteractiveQuizController/micro/$1';
$route['interactive_quiz/discussion_results/(:any)'] = 'InteractiveQuizController/discussion_results/$1';
$route['interactive_quiz/choice_stats/(:any)'] = 'InteractiveQuizController/get_choice_stats/$1';

// Project Log Routes
$route['project_log'] = 'ProjectLogController/index';
$route['project_log/save'] = 'ProjectLogController/save';
$route['project_log/update/(:num)'] = 'ProjectLogController/update/$1';
$route['project_log/delete/(:num)'] = 'ProjectLogController/delete/$1';
$route['project_log/install'] = 'ProjectLogController/install';
$route['project_log/(:num)'] = 'ProjectLogController/index/$1'; // keep after the specific routes above
$route['admin/project_logs'] = 'AdminController/project_logs';
$route['admin/save_project_log_groupings'] = 'AdminController/save_project_log_groupings';

// Miscellaneous Routes
$route['test'] = 'Main/test';
$route['signup'] = 'Main/signup';
$route['signup_submit'] = 'Main/signup_submit';
$route['register'] = 'Main/register';
$route['check_username_public'] = 'Main/check_username_public';
$route['input_submit'] = 'Main/input_submit';
$route['output_upload'] = 'Main/output_upload';

// Admin Routes
// AdminController was split into five controllers (see application/core/
// MY_Controller.php); the URLs below are unchanged, only their targets moved.
$route['dashboard'] = 'AdminController/dashboard';
$route['view_attendance'] = 'AdminController/view_attendance';
$route['admin/student_attendance/(:num)'] = 'AdminController/student_attendance/$1';

$route['manage_assessments'] = 'AdminAssessmentController/manage_assessments';
$route['class_assessments'] = 'AdminAssessmentController/class_assessments';
$route['save_assessment'] = 'AdminAssessmentController/save_assessment';
$route['update_assessment_status'] = 'AdminAssessmentController/update_assessment_status';

$route['all_submissions/(:num)'] = 'AdminSubmissionController/all_submissions/$1';
$route['group_submissions/(:num)'] = 'AdminSubmissionController/group_submissions/$1';
$route['student_submissions/(:num)'] = 'AdminSubmissionController/view_student_submissions/$1';
$route['active_participation/(:num)'] = 'AdminSubmissionController/active_participation/$1';
$route['admin/check_new_submissions_by_assessment/(:num)'] = 'AdminSubmissionController/check_new_submissions_by_assessment/$1';
$route['admin/score_integrity'] = 'AdminSubmissionController/score_integrity';
$route['admin/fix_score/(:num)'] = 'AdminSubmissionController/fix_score/$1';

$route['admin/emergency_contacts'] = 'AdminStudentController/emergency_contacts';
$route['admin/export_emergency_contacts'] = 'AdminStudentController/export_emergency_contacts';
$route['admin/student_violations'] = 'AdminStudentController/student_violations';
$route['admin/add_violation'] = 'AdminStudentController/add_violation';
$route['admin/update_violation_status'] = 'AdminStudentController/update_violation_status';
$route['admin/search_students'] = 'AdminStudentController/search_students';
$route['uncleared_students'] = 'AdminStudentController/uncleared_students_overview';
$route['uncleared_students/clear/(:num)/(:any)'] = 'AdminStudentController/clear_student/$1/$2';
$route['uncleared_students/(:any)'] = 'AdminStudentController/uncleared_students/$1';
$route['admin/students_by_section'] = 'AdminStudentController/students_by_section';
$route['admin/student_summary/(:num)'] = 'AdminStudentController/student_summary/$1';
$route['admin/register_student'] = 'AdminStudentController/register_student';
$route['admin/check_student_no'] = 'AdminStudentController/check_student_no';
$route['admin/check_username'] = 'AdminStudentController/check_username';
$route['admin/semesters'] = 'AdminStudentController/semesters';
$route['admin/save_semester'] = 'AdminStudentController/save_semester';
$route['admin/activate_semester/(:num)'] = 'AdminStudentController/activate_semester/$1';

$route['manage_json_files'] = 'AdminContentController/manage_json_files';
$route['admin/worksheet_generator'] = 'AdminContentController/worksheet_generator';
$route['admin/worksheet_generate'] = 'AdminContentController/worksheet_generate';
$route['admin/worksheet_assessments_for_schedule'] = 'AdminContentController/worksheet_assessments_for_schedule';
$route['admin/worksheet_source_from_assessment'] = 'AdminContentController/worksheet_source_from_assessment';

// Legacy AdminController/* URLs.
//
// Most admin URLs never had a route entry — they resolved through CI's default
// Controller/method routing, and the views still hardcode them
// (base_url('AdminController/preview_widget') and friends), as do a handful of
// in-code redirects and any bookmark an admin has saved. Those URLs must keep
// working, so every method that moved out of AdminController is re-pointed at
// its new home below.
//
// The (:any) tiers cover the same argument counts default routing used to pass
// through, so behaviour is identical for every method regardless of arity — the
// deepest is add_group_score/{assessment}/{group}/{score}. Add a method's name
// here whenever you move one between admin controllers, or its old URL 404s.
$legacy_admin_routes = [
    'AdminSubmissionController' => 'all_submissions|group_submissions|add_group_score'
        . '|view_student_submissions|student_submissions|active_participation'
        . '|check_new_submissions_by_assessment|increment_randomized_count|add_score'
        . '|add_rand_score_incremental|score_integrity|fix_score',
    'AdminAssessmentController' => 'manage_assessments|save_assessment|assign_master'
        . '|class_assessments|update_class_assessment_master|backfill_assessment_class_id'
        . '|delete_class_assessment|preview_widget|update_assessment_status'
        . '|bulk_update_assessment_status|delete_assessment',
    'AdminStudentController' => 'emergency_contacts|export_emergency_contacts'
        . '|uncleared_students_overview|uncleared_students|clear_student|student_violations'
        . '|add_violation|update_violation_status|students_by_section|student_summary'
        . '|login_as_student|register_student|check_student_no|check_username'
        . '|student_requests|process_student_request|password_resets|process_password_reset'
        . '|search_students|semesters|save_semester|activate_semester',
    'AdminContentController' => 'manage_json_files|manage_discussions|save_discussion'
        . '|delete_discussion|worksheet_generator|worksheet_assessments_for_schedule'
        . '|worksheet_source_from_assessment|worksheet_generate',
];
foreach ($legacy_admin_routes as $legacy_target => $legacy_methods) {
    $route['AdminController/(' . $legacy_methods . ')'] = $legacy_target . '/$1';
    $route['AdminController/(' . $legacy_methods . ')/(:any)'] = $legacy_target . '/$1/$2';
    $route['AdminController/(' . $legacy_methods . ')/(:any)/(:any)'] = $legacy_target . '/$1/$2/$3';
    $route['AdminController/(' . $legacy_methods . ')/(:any)/(:any)/(:any)'] = $legacy_target . '/$1/$2/$3/$4';
}
unset($legacy_admin_routes, $legacy_target, $legacy_methods);

// Poll Routes (Mentimeter-like module)
$route['poll/install']                   = 'PollController/install';
$route['poll/dashboard']                 = 'PollController/dashboard';
$route['poll/create']                    = 'PollController/create';
$route['poll/present/(:num)']            = 'PollController/present/$1';
$route['poll/report/(:num)']             = 'PollController/report/$1';
$route['poll/activate_question/(:num)']  = 'PollController/activate_question/$1';
$route['poll/toggle_results/(:num)']     = 'PollController/toggle_results/$1';
$route['poll/close_poll/(:num)']         = 'PollController/close_poll/$1';
$route['poll/delete_poll/(:num)']        = 'PollController/delete_poll/$1';
$route['poll/results/(:num)']            = 'PollController/results/$1';
$route['poll/active_poll']               = 'PollController/active_poll';
$route['poll/answer/(:any)']             = 'PollController/answer/$1';
$route['poll/student_state/(:any)']      = 'PollController/student_state/$1';
$route['poll/submit_answer']             = 'PollController/submit_answer';

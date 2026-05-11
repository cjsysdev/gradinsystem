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
$route['admin/student_requests'] = 'AdminController/student_requests';
$route['admin/process_student_request'] = 'AdminController/process_student_request';
// Legacy redirects so old bookmarks still work
$route['admin/advance_excuses'] = 'AdminController/student_requests';
$route['admin/leaving_passes'] = 'AdminController/student_requests';

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
$route['add_rand_score/(:num)/(:num)/(:num)'] = 'ClassworkController/add_rand_score/$1/$2/$3';

// Discussion Routes
$route['discussion'] = 'DiscussionController/index';
$route['discussion/structs'] = 'DiscussionController/structs';
$route['discussion/includes'] = 'DiscussionController/includes';

// Assessment Routes
$route['assessment/(:num)'] = 'AssessmentController/assessment_view_code/$1';
$route['assessment_view'] = 'AssessmentController/assessment_view';
$route['upload_activity'] = 'AssessmentController/upload_activity';

// Quiz Routes
$route['quiz/(:num)'] = 'QuizController/index/$1';
$route['quiz/submit/(:num)'] = 'QuizController/submit/$1';
$route['quiz/check_session'] = 'QuizController/check_session';

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
$route['interactive_quiz/discussion/(:any)'] = 'InteractiveQuizController/discussion/$1';
$route['interactive_quiz/discussion_results/(:any)'] = 'InteractiveQuizController/discussion_results/$1';
$route['interactive_quiz/choice_stats/(:any)'] = 'InteractiveQuizController/get_choice_stats/$1';

// Miscellaneous Routes
$route['test'] = 'Main/test';
$route['signup'] = 'Main/signup';
$route['signup_submit'] = 'Main/signup_submit';
$route['register'] = 'Main/register';
$route['check_student_no_public'] = 'Main/check_student_no_public';
$route['check_username_public'] = 'Main/check_username_public';
$route['input_submit'] = 'Main/input_submit';
$route['output_upload'] = 'Main/output_upload';

// Admin Routes
$route['manage_assessments'] = 'AdminController/manage_assessments';
$route['save_assessment'] = 'AdminController/save_assessment';
$route['update_assessment_status'] = 'AdminController/update_assessment_status';
$route['dashboard'] = 'AdminController/dashboard';
$route['manage_json_files'] = 'AdminController/manage_json_files';
$route['all_submissions/(:num)'] = 'AdminController/all_submissions/$1';
$route['student_submissions/(:num)'] = 'AdminController/view_student_submissions/$1';
$route['view_attendance'] = 'AdminController/view_attendance';
$route['active_participation/(:num)'] = 'AdminController/active_participation/$1';
$route['admin/check_new_submissions_by_assessment/(:num)'] = 'AdminController/check_new_submissions_by_assessment/$1';
$route['admin/emergency_contacts'] = 'AdminController/emergency_contacts';
$route['admin/student_violations'] = 'AdminController/student_violations';
$route['admin/add_violation'] = 'AdminController/add_violation';
$route['admin/update_violation_status'] = 'AdminController/update_violation_status';
$route['admin/search_students'] = 'AdminController/search_students';
$route['uncleared_students/(:any)'] = 'AdminController/uncleared_students/$1';
$route['admin/students_by_section'] = 'AdminController/students_by_section';
$route['admin/student_summary/(:num)'] = 'AdminController/student_summary/$1';
$route['admin/register_student'] = 'AdminController/register_student';
$route['admin/check_student_no'] = 'AdminController/check_student_no';
$route['admin/check_username'] = 'AdminController/check_username';
$route['admin/semesters'] = 'AdminController/semesters';
$route['admin/save_semester'] = 'AdminController/save_semester';
$route['admin/activate_semester/(:num)'] = 'AdminController/activate_semester/$1';

// Poll Routes (Mentimeter-like module)
$route['poll/install']                   = 'PollController/install';
$route['poll/dashboard']                 = 'PollController/dashboard';
$route['poll/create']                    = 'PollController/create';
$route['poll/present/(:num)']            = 'PollController/present/$1';
$route['poll/activate_question/(:num)']  = 'PollController/activate_question/$1';
$route['poll/toggle_results/(:num)']     = 'PollController/toggle_results/$1';
$route['poll/close_poll/(:num)']         = 'PollController/close_poll/$1';
$route['poll/delete_poll/(:num)']        = 'PollController/delete_poll/$1';
$route['poll/results/(:num)']            = 'PollController/results/$1';
$route['poll/active_poll']               = 'PollController/active_poll';
$route['poll/answer/(:any)']             = 'PollController/answer/$1';
$route['poll/student_state/(:any)']      = 'PollController/student_state/$1';
$route['poll/submit_answer']             = 'PollController/submit_answer';

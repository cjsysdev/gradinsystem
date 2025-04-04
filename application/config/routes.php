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


// Grades Routes
$route['grades'] = 'GradesController/grades';
$route['section_grades/(:any)'] = 'GradesController/sectionGrades/$1';

// Attendance Routes
$route['attendance'] = 'AttendanceController/attendance_main';

// Classwork Routes
$route['classwork'] = 'ClassworkController/classwork';
$route['submit_classwork'] = 'ClassworkController/submit_classwork';
$route['student_submission/(:num)'] = 'ClassworkController/student_submission/$1';
$route['start_class'] = 'ClassworkController/start_class';
$route['all_submissions/(:num)'] = 'ClassworkController/all_submissions/$1';
$route['add_score'] = 'ClassworkController/add_score';

// Assessment Routes
$route['assessment/(:num)'] = 'AssessmentController/assessment_view_code/$1';
$route['assessment_view'] = 'AssessmentController/assessment_view';
$route['upload_activity'] = 'AssessmentController/upload_activity';

// Quiz Routes
$route['quiz/(:num)'] = 'QuizController/index/$1';
$route['quiz/submit/(:num)'] = 'QuizController/submit/$1';
$route['quiz/check_session'] = 'QuizController/check_session';

// Miscellaneous Routes
$route['test'] = 'Main/test';
$route['signup'] = 'Main/signup';
$route['signup_submit'] = 'Main/signup_submit';
$route['input_submit'] = 'Main/input_submit';
$route['output_upload'] = 'Main/output_upload';

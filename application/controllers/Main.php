<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Main extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model(['accounts', 'assessments', 'student_master', 'classworks', 'class_schedule', 'attendance', 'class_student']);
        $this->load->helper(['url']);
        $this->load->library(['session', 'upload']);
        $this->is_offline = !isset($_SESSION['online']);
    }

    public function test()
    {
        $this->load->view('test');
    }

    public function index()
    {
        $this->load->view('login');
    }

    public function find_id()
    {
        $this->load->view('find_id');
    }

    public function signup()
    {
        $this->load->view('signup');
    }

    public function student_info()
    {
        $this->load->view('student_info');
    }

    public function home()
    {
        if ($this->is_offline) redirect();
        $this->load->view('home');
    }

    public function assessment_view()
    {
        if ($this->is_offline) redirect();
        $this->load->view('assessment_view');
    }

    public function add_inputs()
    {
        $this->load->view('add_inputs');
    }

    public function admin()
    {
        $this->load->view('admin');
    }

    public function output_upload()
    {
        if ($this->is_offline) redirect();
        $this->load->view('output_upload');
    }

    public function attendance_main()
    {
        if ($this->is_offline) redirect();
        date_default_timezone_set('Asia/Manila');

        $day = date('D');
        $date = date('Y-m-d');

        $class = $this->class_schedule->class_today($day);
        $student_id = $this->session->student_id;

        $account = $this->class_student->get(['student_id' => $student_id]);
        $admin_id = 14;

        if ($this->shouldDenyAttendance($class, $student_id, $admin_id, $account)) {
            $this->session->set_flashdata('error', 'No available class');
        } else {
            $this->handleStudentAttendance($class, $student_id, $date);
        }

        $attendance_record = $this->attendance->get_student_attendance($student_id);

        $data = [
            "class" => $class,
            "record" => $attendance_record,
            "events" => json_encode($attendance_record)
        ];

        $this->load->view('attendance_view', $data);
    }

    private function shouldDenyAttendance($class, $student_id, $admin_id, $account)
    {
        return !$class || $student_id == $admin_id ||
            (isset($account->section)) && $account->section != $class['section'];
    }

    private function handleStudentAttendance($class, $student_id, $date)
    {
        $this->attendance->start_class($class['schedule_id'], $class['section'], $date);

        $check_student = $this->attendance->where([
            'student_id' => $student_id,
            'schedule_id' => $class['schedule_id'],
            'date(date)' => $date
        ])->get();

        if (isset($check_student->status) && $check_student->status === 'absent') {
            $client_ip = $this->input->ip_address();
            $this->attendance->update_status('present', $client_ip, $student_id, $date);
        }
    }

    public function login()
    {
        $post = $this->input->post();
        $user = $this->accounts->with_student()->get(['username' => $post['username']]);

        if ($user && $user->password == $post['password']) {

            $session_data = [
                'account_id' => $user->account_id,
                'student_id' => $user->student_id,
                'student_no' => $user->student->student_no,
                'lastname' => $user->student->lastname,
                'firstname' => $user->student->firstname,
                'course' => $user->student->course,
                'current_year' => $user->student->current_year,
                'role' => $user->role,
                'online' => true,
                'exam_term' => false,
                'exam_review' => false
            ];

            $this->session->set_userdata($session_data);

            redirect('attendance');
        } else {
            $this->session->set_flashdata('error', 'Login Error');
            redirect();
        }
    }

    public function logout()
    {
        $this->session->sess_destroy();
        redirect();
    }

    public function get_id()
    {
        $input = $this->input->post();
        $student = $this->student_master->where([
            'lastname' => $input['lastname'],
            'firstname' => $input['firstname']
        ])->get();

        if (!$student) {
            $this->session->set_flashdata('error', 'Student not found');
            redirect('find_id');
        }

        $this->load->view('student_details', $student);
    }

    public function input_submit()
    {
        $this->assessment->insert($this->input->post());
    }

    public function student_submission($classwork_id)
    {
        $submission = $this->classworks->with_assessments()->as_array()->get($classwork_id);

        $data = [
            'classwork' =>  $submission
        ];

        $this->load->view('student_submission', $data);
    }

    public function assessment_view_code($classwork_id)
    {
        $classwork = $this->assessments->as_array()->get($classwork_id);

        if (!$classwork) {
            show_404();
        }

        $data = [
            'classwork' =>  $classwork
        ];

        $this->load->view('assessment_view_code', $data);
    }

    public function classwork()
    {
        if ($this->is_offline) redirect();
        $student_id = $this->session->student_id;
        $student = $this->class_student->where('student_id', $student_id)->get();

        if (!$student) {
            $this->session->set_flashdata('error', 'Student section not found');
            redirect('attendance');
        }

        $missing = $this->assessments->get_students_assessments(
            $student_id,
            $student->section
        );

        $submmitted = $this->assessments->get_submmited_assessments(
            $student_id
        );

        $data = [
            'assessments' => $missing,
            'submitted' => $submmitted
        ];

        $this->load->view('classwork', $data);
    }

    public function submit_classwork()
    {

        $post = $this->input->post();
        $value = $this->classworks->where(
            [
                'student_id' => $this->session->student_id,
                'assessment_id' => $post['assessment_id']
            ]
        )->get();

        if (!$value) {
            $this->classworks->insert($post);
            $this->session->set_flashdata('success', 'Classwork submitted successfully');
        } else {
            $this->session->set_flashdata('warning', 'NAKA PASS NA LAGE KA!!!!');
        }

        redirect('classwork');
    }

    public function all_submissions()
    {
        $submissions = $this->classworks->get_all_submissions(10);

        $data = ["submissions" => $submissions];

        $this->load->view('all_submission', $data);
    }

    public function add_score($classwork_id, $type)
    {

        switch ($type) {
            case 1:
                $score = randomizeNumber(5.0, 7.4);
                break;
            case 2:
                $score = randomizeNumber(7.5, 7.9);
                break;
            case 3:
                $score = randomizeNumber(8.0, 8.9);
                break;
            case 4:
                $score = randomizeNumber(9.0, 9.4);
                break;
            case 5:
                $score = randomizeNumber(9.5, 10.0);
                break;
            default:
                $score = null;
                break;
        }

        $update = $this->classworks->add_score($classwork_id, $score);
        redirect('all_submissions');
    }

    public function upload_activity()
    {

        $filename = $this->class_student->get(
            ['student_id' =>
            $this->session->student_id]
        )->section . "-MID-PT-" . $this->session->lastname;

        $config['upload_path']          = './uploads/outputs';
        $config['allowed_types']        = '*';
        $config['max_size']             = 51200; // 50MB
        $config['file_name']            = $filename;

        $this->upload->initialize($config);


        if (!$this->upload->do_upload('photo-upload')) {
            $error = array('error' => $this->upload->display_errors());
            $this->session->set_flashdata('error', $this->upload->display_errors());
            redirect('output_upload');
        } else {
            $this->upload->data();
            $this->session->set_flashdata('success', 'Upload Successful');
            redirect('output_upload');
        }
    }

    public function grades()
    {
        // Fetch grades for Midterm
        $midtermGrades = $this->classworks->getGradesByIotype(
            'midterm',
            $this->session->student_id
        );

        // Calculate total Midterm grade
        $midtermTotalGrade = 0;
        foreach ($midtermGrades as $grade) {
            $midtermTotalGrade += $grade['percentage'] * ($grade['iotype_percentage'] / 100);
        }

        // Fetch grades for Final Term (Tentative Final)
        $finalGrades = $this->classworks->getGradesByIotype(
            'final',
            $this->session->student_id
        );

        // Calculate total Final Term grade
        $finalTotalGrade = 0;

        if (isset($finalGrades)) {
            foreach ($finalGrades as $grade) {
                $finalTotalGrade += $grade['percentage'] * ($grade['iotype_percentage'] / 100);
            }
        }
        // Calculate overall final total grade (50% Midterm + 50% Final Term)
        $overallFinalGrade = ($midtermTotalGrade * 0.5) + ($finalTotalGrade * 0.5);

        // Pass the data to the view
        $data['midtermGrades'] = $midtermGrades;
        $data['midtermTotalGrade'] = round($midtermTotalGrade, 2); // Round to 2 decimal places
        $data['finalGrades'] = $finalGrades;
        $data['finalTotalGrade'] = round($finalTotalGrade, 2); // Round to 2 decimal places
        $data['overallFinalGrade'] = round($overallFinalGrade, 2); // Round to 2 decimal places

        $this->load->view('home', $data);
    }

    public function sectionGrades($section)
    {
        $term = 'midterm'; // Example term

        // Fetch grades for all students in the section
        $grades = $this->classworks->getGradesBySection($term, $section);

        // Calculate the Midterm Total Grade and Grade Point for each student
        $studentsGrades = [];
        foreach ($grades as $grade) {
            $studentId = $grade['student_id'];

            if (!isset($studentsGrades[$studentId])) {
                $studentsGrades[$studentId] = [
                    'student_id' => $studentId,
                    'firstname' => $grade['firstname'],
                    'lastname' => $grade['lastname'],
                    'section' => $grade['section'],
                    'midterm_total_grade' => 0,
                    'grade_point' => 0,
                    'has_iotype_2' => false,
                    'has_iotype_3' => false,
                    'is_incomplete' => false, // Flag to track incomplete grades
                ];
            }

            // Check for iotype_id = 2 and iotype_id = 3
            if ($grade['iotype_id'] == 2) {
                $studentsGrades[$studentId]['has_iotype_2'] = true;
                if (is_null($grade['total_score']) || is_null($grade['percentage'])) {
                    $studentsGrades[$studentId]['is_incomplete'] = true;
                }
            }
            if ($grade['iotype_id'] == 3) {
                $studentsGrades[$studentId]['has_iotype_3'] = true;
                if (is_null($grade['total_score']) || is_null($grade['percentage'])) {
                    $studentsGrades[$studentId]['is_incomplete'] = true;
                }
            }

            // Calculate the midterm total grade if not incomplete
            if (!$studentsGrades[$studentId]['is_incomplete']) {
                $studentsGrades[$studentId]['midterm_total_grade'] += $grade['percentage'] * ($grade['iotype_percentage'] / 100);
            }
        }

        // Finalize grades and check for missing or incomplete iotype_id = 2 or iotype_id = 3
        foreach ($studentsGrades as $studentId => &$student) {
            if (!$student['has_iotype_2'] || !$student['has_iotype_3'] || $student['is_incomplete']) {
                $student['midterm_total_grade'] = 'INC';
                $student['grade_point'] = 'INC';
            } else {
                // Calculate the grade point using the helper function
                $student['grade_point'] = convertPercentageToGradePoint($student['midterm_total_grade']);
            }
        }

        // Pass the data to the view
        $data['studentsGrades'] = $studentsGrades;
        $data['term'] = $term;
        $data['section'] = $section;
        $data['class_code'] = $grades[0]['class_code'] ?? '';
        $data['class_name'] = $grades[0]['class_name'] ?? '';
        $data['schedule'] = $grades[0]['schedule'] ?? '';

        $this->load->view('section_grades', $data);
    }
}

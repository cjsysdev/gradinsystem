<?php
defined('BASEPATH') or exit('No direct script access allowed');

class PerformanceSheetController extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper(['url']);
        $this->load->library(['session', 'upload']);
        $this->load->model(['student_master', 'Violation']);
    }

    public function index()
    {
        $student_id = $this->session->student_id;
        $student = $this->student_master->get_student_info($student_id);

        $student['course'] = $this->student_master->get_current_course($student_id);
        $student['classworks'] = $this->student_master->get_student_classworks($student_id);

        $student['total_activity'] = 0;
        $student['max_activity']   = 0;
        $student['total_pt']       = 0;
        $student['max_pt']         = 0;
        $student['total_quiz']     = 0;
        $student['max_quiz']       = 0;
        $student['total_exam']     = 0;
        $student['max_exam']       = 0;
        $student['quiz']           = [];
        $student['quiz_titles']    = [];

        foreach ($student['classworks'] as $record) {
            if ($record['iotype_id'] == 1) {
                $student['total_activity'] += (int)$record['score'];
                $student['max_activity']   += (int)$record['max_score'];
            } elseif ($record['iotype_id'] == 2) {
                $student['total_pt']  += (int)$record['score'];
                $student['max_pt']    += (int)$record['max_score'];
            } elseif ($record['iotype_id'] == 4) {
                $student['total_quiz']    += (int)$record['score'];
                $student['max_quiz']      += (int)$record['max_score'];
                $student['quiz'][]         = (int)$record['score'];
                $student['quiz_titles'][]  = $record['title'];
            } elseif ($record['iotype_id'] == 3) {
                $student['total_exam'] += (int)$record['score'];
                $student['max_exam']   += (int)$record['max_score'];
            }
        }

        $data = [
            'student'            => $student,
            'absences'           => $this->student_master->get_absences($student_id),
            'attendance_summary' => $this->student_master->get_attendance_summary($student_id),
            'violations'         => $this->Violation->get_by_student($student_id),
        ];

        $this->load->view('performance_sheet', $data);
    }
}

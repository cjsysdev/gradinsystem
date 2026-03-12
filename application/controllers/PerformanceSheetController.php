<?php
defined('BASEPATH') or exit('No direct script access allowed');

class PerformanceSheetController extends CI_Controller
{

    public function index()
    {
        $this->load->view('performance_sheet');
    }

    public function index2($student_id)
    {
        // Load student model and data
        $this->load->model('student_master');
        $student = $this->student_master->get_student_info($student_id);
        $student['course'] = $this->student_master->get_current_course($student_id);

        // Placeholder for other data
        $absences = $this->student_master->get_absences($student_id);
        $missed_activities = $this->student_master->get_missed_activities($student_id);
        $missed_quizzes = $this->student_master->get_missed_quizzes($student_id);
        $quiz_scores = $this->student_master->get_quiz_scores($student_id);
        $major_exam = $this->student_master->get_major_exam_score($student_id);
        $performance_task = $this->student_master->get_performance_task_score($student_id);

        $data = [
            'student' => $student,
            'absences' => $absences,
            'missed_activities' => $missed_activities,
            'missed_quizzes' => $missed_quizzes,
            'quiz_scores' => $quiz_scores,
            'major_exam' => $major_exam,
            'performance_task' => $performance_task
        ];
        $this->load->view('performance_sheet', $data);
    }
}

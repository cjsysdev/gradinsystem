<?php
defined('BASEPATH') or exit('No direct script access allowed');

class student_master extends MY_Model
{
    // Fetch major exam score
    public function get_major_exam_score($student_id)
    {
        $this->db->select('score');
        $this->db->from('assessments');
        $this->db->where('student_id', $student_id);
        $this->db->where('type', 'major_exam');
        $result = $this->db->get()->row_array();
        return $result ? $result['score'] : '';
    }

    // Fetch performance task score
    public function get_performance_task_score($student_id)
    {
        $this->db->select('score');
        $this->db->from('assessments');
        $this->db->where('student_id', $student_id);
        $this->db->where('type', 'performance_task');
        $result = $this->db->get()->row_array();
        return $result ? $result['score'] : '';
    }
    // Fetch quiz scores for graph
    public function get_quiz_scores($student_id)
    {
        $this->db->select('quiz_name as quiz, score');
        $this->db->from('assessments');
        $this->db->where('student_id', $student_id);
        $this->db->where('type', 'quiz');
        $this->db->order_by('quiz_date', 'asc');
        return $this->db->get()->result_array();
    }
    // Fetch missed activities
    public function get_missed_activities($student_id)
    {
        $this->db->select('activity_name, due_date');
        $this->db->from('classworks');
        $this->db->where('student_id', $student_id);
        $this->db->where('status', 'missed');
        return $this->db->get()->result_array();
    }

    // Fetch missed quizzes
    public function get_missed_quizzes($student_id)
    {
        $this->db->select('quiz_name, quiz_date');
        $this->db->from('assessments');
        $this->db->where('student_id', $student_id);
        $this->db->where('status', 'missed');
        return $this->db->get()->result_array();
    }
    // Fetch absences with dates and reasons
    public function get_absences($student_id)
    {
        $this->db->select('date, reason');
        $this->db->from('attendance');
        $this->db->where('student_id', $student_id);
        $this->db->where('status', 'absent');
        $this->db->order_by('date', 'desc');
        return $this->db->get()->result_array();
    }

    public $table = 'student_master';
    public $primary_key = 'trans_no';
    public $protected = array('trans_no');

    public function __construct()
    {
        $this->timestamps = FALSE;
        $this->has_one['accounts'] = array(
            'foreign_model' => 'accounts',
            'foreign_table' => 'accounts',
            'foreign_key' => 'student_id',
            'local_key' => 'trans_no'
        );
        parent::__construct();
    }

    public function search_by_name($search)
    {
        $this->db->like('firstname', $search);
        $this->db->or_like('lastname', $search);
        return $this->db->get('student_master')->result_array();
    }

    // Fetch student info for performance sheet
    public function get_student_info($student_id)
    {
        $this->db->select('trans_no, firstname, lastname, age, program, year, photo_url');
        $this->db->where('trans_no', $student_id);
        $student = $this->db->get('student_master')->row_array();
        if ($student) {
            $student['name'] = $student['firstname'] . ' ' . $student['lastname'];
        }
        return $student;
    }

    // Fetch current course for student
    public function get_current_course($student_id)
    {
        $this->db->select('c.class_name as course');
        $this->db->from('class_student cs');
        $this->db->join('classes c', 'cs.class_id = c.class_id');
        $this->db->where('cs.student_id', $student_id);
        $this->db->order_by('cs.trans_no', 'desc');
        $result = $this->db->get()->row_array();
        return $result ? $result['course'] : '';
    }
}

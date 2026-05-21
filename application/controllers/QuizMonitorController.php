<?php
defined('BASEPATH') or exit('No direct script access allowed');

class QuizMonitorController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['Quiz_live_status', 'assessments']);
        $this->load->helper(['url']);
        $this->load->library(['session']);

        // Students should not see the monitor
        if ($this->session->student_id) {
            redirect('attendance');
        }

        $this->Quiz_live_status->ensure_table();
    }

    public function index($assessment_id)
    {
        $assessment = $this->assessments->get((int)$assessment_id);
        if (!$assessment) {
            show_error('Assessment not found.', 404);
            return;
        }

        $enrolled = $this->_get_enrolled($assessment_id);

        $data['assessment']    = $assessment;
        $data['assessment_id'] = (int)$assessment_id;
        $data['enrolled']      = $enrolled;
        $this->load->view('quiz_monitor_view', $data);
    }

    public function live_data($assessment_id)
    {
        header('Content-Type: application/json');

        $assessment = $this->assessments->get((int)$assessment_id);
        if (!$assessment) {
            echo json_encode(['error' => 'Not found']);
            return;
        }

        $enrolled  = $this->_get_enrolled($assessment_id);
        $live_rows = $this->Quiz_live_status->get_for_assessment($assessment_id);

        $live_map = [];
        foreach ($live_rows as $row) {
            $live_map[(int)$row['student_id']] = $row;
        }

        $students = [];
        $summary  = ['not_started' => 0, 'answering' => 0, 'submitted' => 0];

        foreach ($enrolled as $s) {
            $sid = (int)$s['student_id'];

            if (isset($live_map[$sid])) {
                $row     = $live_map[$sid];
                $status  = $row['status'];
                $elapsed = $row['started_at']
                    ? max(0, time() - strtotime($row['started_at']))
                    : null;

                $students[] = [
                    'student_id'      => $sid,
                    'name'            => $s['lastname'] . ', ' . $s['firstname'],
                    'status'          => $status,
                    'items_answered'  => (int)$row['items_answered'],
                    'total_items'     => (int)$row['total_items'] ?: (int)$assessment->max_score,
                    'blur_count'      => (int)$row['blur_count'],
                    'elapsed_seconds' => $elapsed,
                    'score'           => $row['score'] !== null ? (float)$row['score'] : null,
                    'last_heartbeat'  => $row['last_heartbeat'],
                ];
                $summary[$status]++;
            } else {
                $students[] = [
                    'student_id'      => $sid,
                    'name'            => $s['lastname'] . ', ' . $s['firstname'],
                    'status'          => 'not_started',
                    'items_answered'  => 0,
                    'total_items'     => (int)$assessment->max_score,
                    'blur_count'      => 0,
                    'elapsed_seconds' => null,
                    'score'           => null,
                    'last_heartbeat'  => null,
                ];
                $summary['not_started']++;
            }
        }

        echo json_encode([
            'assessment' => [
                'title'     => $assessment->title,
                'max_score' => (int)$assessment->max_score,
            ],
            'students'    => $students,
            'summary'     => $summary,
            'server_time' => date('H:i:s'),
        ]);
    }

    private function _get_enrolled($assessment_id)
    {
        $result = $this->db->query("
            SELECT DISTINCT sm.trans_no AS student_id, sm.firstname, sm.lastname
            FROM assessments a
            JOIN class_schedule cs ON cs.schedule_id = a.schedule_id
            JOIN semester_master sem ON sem.trans_no = cs.semester_id AND sem.is_active = 1
            JOIN class_student cls ON cls.section = cs.section AND cls.semester_id = sem.trans_no
            JOIN student_master sm ON sm.trans_no = cls.student_id
            WHERE a.assessment_id = ?
            ORDER BY sm.lastname, sm.firstname
        ", [(int)$assessment_id]);

        return $result ? $result->result_array() : [];
    }
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');

class classworks extends MY_Model
{
    public $table = 'classworks';
    public $primary_key = 'classwork_id';
    public $protected = array('classwork_id');
    public $timestamps = FALSE;

    public function __construct()
    {
        $this->timestamps = FALSE;
        $this->has_many['student'] =  array(
            'foreign_model' => 'student_master',
            'foreign_table' => 'student_master',
            'foreign_key' => 'trans_no',
            'local_key' => 'student_id'
        );
        $this->has_many['assessments'] =  array(
            'foreign_model' => 'assessments',
            'foreign_table' => 'assessments',
            'foreign_key' => 'assessment_id',
            'local_key' => 'assessment_id'
        );
        parent::__construct();
    }

    public function get_all_submissions($assessment_id)
    {
        $sql = "SELECT c.classwork_id, s.trans_no, c.score, s.firstname,
        s.lastname, c.code, c.file_upload, c.created_at, a.max_score, a.iotype_id
                FROM classworks c
                JOIN student_master s ON s.trans_no = c.student_id
                JOIN assessment_full a ON a.assessment_id = c.assessment_id
                JOIN class_schedule cs ON cs.schedule_id = a.schedule_id
                JOIN semester_master sem ON cs.semester_id = sem.trans_no
                WHERE a.assessment_id = ?
                ORDER BY c.created_at";

        $query = $this->db->query($sql, [$assessment_id]);

        if ($query === false) {
            $error = $this->db->error();
            log_message('error', 'Database error: ' . $error['message']);
            return []; // Return an empty array or handle the error as needed
        }

        return $query->result_array();
    }

    // Enrolled students (for the assessment's schedule) who have no
    // classworks row for this assessment yet — i.e. haven't submitted.
    public function get_missing_submissions($assessment_id)
    {
        $sql = "SELECT s.trans_no, s.firstname, s.lastname
                FROM class_student cst
                JOIN student_master s ON s.trans_no = cst.student_id
                JOIN assessment_full a ON a.schedule_id = cst.schedule_id
                WHERE a.assessment_id = ?
                AND cst.status = 'enrolled'
                AND NOT EXISTS (
                    SELECT 1 FROM classworks c
                    WHERE c.assessment_id = a.assessment_id
                    AND c.student_id = cst.student_id
                )
                ORDER BY s.lastname, s.firstname";

        $query = $this->db->query($sql, [$assessment_id]);

        if ($query === false) {
            $error = $this->db->error();
            log_message('error', 'Database error: ' . $error['message']);
            return [];
        }

        return $query->result_array();
    }

    /**
     * The single validated way to write a score.
     *
     * Every scoring path should go through here. Scores used to be written raw
     * from several controllers with no validation and no upper bound, which is
     * how 14 rows ended up scoring above their assessment's max_score — and
     * those inflate the student's percentage directly.
     *
     * @param  int        $classwork_id
     * @param  mixed      $score        must be numeric and >= 0
     * @param  string|null $error       set to a reason when the write is refused
     * @return bool                     TRUE when the row was updated
     */
    public function set_score($classwork_id, $score, &$error = null)
    {
        if (!is_numeric($score) || $score < 0) {
            $error = 'Score must be a number of 0 or greater.';
            return FALSE;
        }

        $row = $this->db->query("
            SELECT c.classwork_id, a.max_score
            FROM classworks c
            JOIN assessment_full a ON a.assessment_id = c.assessment_id
            WHERE c.classwork_id = ?
        ", [$classwork_id])->row_array();

        if (!$row) {
            $error = 'Submission not found, or it points at an assessment that no longer exists.';
            return FALSE;
        }

        // Clamp rather than reject: graders routinely award the maximum and a
        // hard failure here would lose the rest of a bulk grading pass.
        $max = (float) $row['max_score'];
        if ($max > 0 && $score > $max) {
            $score = $max;
            $error = 'Score exceeded the maximum and was capped at ' . $max . '.';
        }

        $this->db->where('classwork_id', $classwork_id)
            ->update('classworks', ['score' => $score]);

        return $this->db->affected_rows() >= 0;
    }

    /**
     * Clamp a score against its assessment's max_score, for INSERT paths that
     * don't have a classwork_id yet (set_score() only handles UPDATEs).
     *
     * Every auto-graded widget computes its score server-side already, so this
     * should normally be a no-op — it exists as a second layer of defense in
     * case a widget's question count and the assessment's configured max_score
     * ever drift apart, and it's the only thing standing between a raw
     * client-supplied score (InteractiveQuizController::save_result()) and the
     * database.
     */
    public function clamp_score_for_assessment($assessment_id, $score)
    {
        if (!is_numeric($score) || $score < 0) {
            return 0;
        }

        $max = $this->db->select('max_score')
            ->where('assessment_id', $assessment_id)
            ->get('assessment_full')
            ->row('max_score');

        if ($max !== null && (float) $score > (float) $max) {
            log_message('info', "clamp_score_for_assessment: assessment $assessment_id score $score capped to $max");
            return (float) $max;
        }

        return $score;
    }

    /** Cheap count for nav badges — avoids the join in get_scores_exceeding_max(). */
    public function count_scores_exceeding_max()
    {
        return (int) $this->db->query("
            SELECT COUNT(*) c
            FROM classworks c
            JOIN assessment_full a ON a.assessment_id = c.assessment_id
            WHERE c.score > a.max_score
        ")->row('c');
    }

    /** classworks rows scored above their own assessment's max_score. */
    public function get_scores_exceeding_max()
    {
        return $this->db->query("
            SELECT c.classwork_id, c.student_id, c.assessment_id, c.score, a.max_score, a.title,
                   sm.firstname, sm.lastname, sm.student_no
            FROM classworks c
            JOIN assessment_full a      ON a.assessment_id = c.assessment_id
            LEFT JOIN student_master sm ON sm.trans_no = c.student_id
            WHERE c.score > a.max_score
            ORDER BY (c.score - a.max_score) DESC
        ")->result_array();
    }

    /** @deprecated Use set_score() — this performs no validation or clamping. */
    public function add_score($classwork_id, $score)
    {
        return $this->set_score($classwork_id, $score);
    }

    public function update_score($classwork_id, $student_id, $score, &$error = null)
    {
        // student_id is kept as a guard so a mismatched pair cannot be written.
        $owner = $this->db->select('student_id')
            ->where('classwork_id', $classwork_id)
            ->get('classworks')
            ->row('student_id');

        if ((string) $owner !== (string) $student_id) {
            $error = 'Submission does not belong to that student.';
            return FALSE;
        }

        return $this->set_score($classwork_id, $score, $error);
    }

    // Bulk-creates a blank (no score/code) classworks row for every enrolled
    // student in $schedule_id who doesn't already have one for $assessment_id
    // — used for participation-style assessments where the admin scores
    // students directly (e.g. via randomizing) instead of students submitting
    // work. Mirrors BrainstormController::_mark_participated()'s row shape.
    public function create_blank_for_schedule($assessment_id, $schedule_id)
    {
        $enrolled = $this->db->select('student_id')
            ->where('schedule_id', $schedule_id)
            ->where('status', 'enrolled')
            ->get('class_student')
            ->result_array();

        if (empty($enrolled)) {
            return 0;
        }

        $existing_ids = array_column(
            $this->db->select('student_id')
                ->where('assessment_id', $assessment_id)
                ->get('classworks')
                ->result_array(),
            'student_id'
        );

        $now = date('Y-m-d H:i:s');
        $rows = [];
        foreach (array_unique(array_column($enrolled, 'student_id')) as $student_id) {
            if (in_array($student_id, $existing_ids)) {
                continue;
            }
            $rows[] = [
                'student_id'    => $student_id,
                'assessment_id' => $assessment_id,
                'status'        => 'submitted',
                'submitted_at'  => $now,
                'created_at'    => $now,
            ];
        }

        if (!empty($rows)) {
            $this->db->insert_batch('classworks', $rows);
        }

        return count($rows);
    }

    // NOTE: the four grade-aggregation queries that used to live here
    // (getActivitiesGrade, getGradesByIotype, getGradesBySection,
    // getAllGradesBySection) were removed when grading was consolidated into
    // Grade_calculator. They duplicated the transmutation formula four times
    // and built the roster from class_student.section, which pulled in
    // prior-semester and non-enrolled rows. This model now owns submission
    // CRUD only — it does not compute grades.

    public function get_submissions_by_student($student_id)
    {
        $sql = "
            SELECT 
                c.*, 
                a.title, 
                s.firstname, 
                s.lastname,
                a.max_score,
                a.iotype_id 
            FROM 
                classworks c
            JOIN
                assessment_full a
            ON
                c.assessment_id = a.assessment_id
            JOIN 
                student_master s 
            ON 
                c.student_id = s.trans_no
            JOIN
                class_schedule cs ON a.schedule_id = cs.schedule_id
            JOIN
                semester_master sem ON cs.semester_id = sem.trans_no AND sem.is_active = 1
            WHERE 
                c.student_id = ?
            ORDER BY 
                c.created_at ASC, c.submitted_at ASC
        ";

        $query = $this->db->query($sql, [$student_id]);

        return $query->result_array();
    }
}

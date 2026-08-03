<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Reading and scoring student submissions: the per-assessment submission
 * lists, group submissions, active participation, and every score write.
 *
 * All score writes go through classworks::set_score(), which validates and
 * clamps to max_score — never write classworks.score directly from here.
 * Split out of AdminController; see Admin_Controller in
 * application/core/MY_Controller.php.
 */
class AdminSubmissionController extends Admin_Controller
{
    public function all_submissions($assessment_id = null)
    {
        // Fetch all assessments for the dropdown

        $day = date('D');
        $class = $this->class_schedule->class_today($day);

        $data['assessments'] = $this->assessments->get_for_schedule($class['schedule_id']);

        // Fetch submissions for the selected assessment
        $data['widget'] = null;
        $data['widget_config'] = [];

        if ($assessment_id) {
            $data['submissions'] = $this->classworks->get_all_submissions(
                $assessment_id
            );
            $data['missing_students'] = $this->classworks->get_missing_submissions($assessment_id);
            $data['selected_assessment_id'] = $assessment_id;

            $assessment = $this->assessments->as_array()->get($assessment_id);
            if (!empty($assessment['widget_id'])) {
                $this->load->model('Widgets_model');
                $data['widget'] = $this->Widgets_model->get($assessment['widget_id']);
                $data['widget_config'] = json_decode($assessment['given'] ?? '', true) ?: [];
            }
        } else {
            $data['submissions'] = [];
            $data['missing_students'] = [];
            $data['selected_assessment_id'] = null;
        }

        $this->load->view('admin/all_submission', $data);
    }

    // Group-aware sibling of all_submissions(): shows one card per group with
    // the group's single shared submission, instead of the flat per-student
    // list (where a group submission appears N identical times). Group
    // submissions are fanned out into one classworks row per member
    // (GroupWorkController::submit_group), so membership is derived from the
    // grouping tables and the submission content is read off any member's row.
    public function group_submissions($assessment_id = null)
    {
        $this->load->model(['Grouping_model', 'Group_member_model', 'Live_state_model']);

        $day   = date('D');
        $class = $this->class_schedule->class_today($day);

        // Dropdown lists only the group-enabled assessments for today's class.
        $all_for_schedule    = $this->assessments->get_for_schedule($class['schedule_id']);
        $data['assessments'] = array_values(array_filter($all_for_schedule, function ($a) {
            return !empty($a['is_groupings']);
        }));

        $data['widget']                 = null;
        $data['widget_config']          = [];
        $data['groups']                 = [];
        $data['missing_students']       = [];
        $data['is_group_assessment']    = false;
        $data['selected_assessment_id'] = $assessment_id ? (int) $assessment_id : null;

        if ($assessment_id) {
            $set_id     = $this->Grouping_model->get_set_for_assessment($assessment_id);
            $assessment = $this->assessments->as_array()->get($assessment_id);

            // A group view only makes sense when the assessment is linked to a
            // grouping set; otherwise the view renders a notice + link back to
            // the per-student page.
            if ($set_id) {
                $data['is_group_assessment'] = true;

                // Widget resolution — copied from all_submissions().
                if (!empty($assessment['widget_id'])) {
                    $this->load->model('Widgets_model');
                    $data['widget']        = $this->Widgets_model->get($assessment['widget_id']);
                    $data['widget_config'] = json_decode($assessment['given'] ?? '', true) ?: [];
                }

                // The two topic-file widgets (iq_discussion, iq_micro) keep a
                // shared live blob shaped nothing like the graded results list
                // their readonly view renders — { v, sections: {...} } /
                // { v, driver, answers: {"si:ci": ...} } vs a plain list of
                // {question, chosen, correct_answer, is_correct}. Handing the
                // raw blob to the widget view fataled on it, so translate the
                // draft here through the same grader the group's own submit
                // uses, and the widget renders it unchanged.
                $iq_kind     = null;
                $iq_sections = null;
                if ($data['widget'] && in_array($data['widget']['widget_key'], ['iq_micro', 'iq_discussion'], true)) {
                    $this->load->model('Iq_topic_model');
                    $topic_data  = $this->Iq_topic_model->load_topic($data['widget_config']['topic'] ?? '');
                    $iq_sections = $topic_data ? $topic_data['sections'] : null;
                    // No resolvable topic file = nothing to grade a draft
                    // against; the draft is then simply not shown.
                    $iq_kind     = $iq_sections ? $data['widget']['widget_key'] : 'unrenderable';
                }

                // Index every fanned-out submission row by the member's trans_no.
                $submissions = $this->classworks->get_all_submissions($assessment_id);
                $by_student  = [];
                foreach ($submissions as $s) {
                    $by_student[$s['trans_no']] = $s;
                }

                // Build one entry per group: members annotated with their own
                // row, plus the shared submission (identical across members).
                $groups = $this->Grouping_model->get_groups_with_members($set_id);
                foreach ($groups as &$g) {
                    $submitted_count = 0;
                    $shared          = null;
                    $classwork_ids   = [];

                    foreach ($g['members'] as &$m) {
                        $row               = $by_student[$m['trans_no']] ?? null;
                        $m['classwork_id'] = $row['classwork_id'] ?? null;
                        $m['score']        = $row['score'] ?? null;
                        $m['submitted']    = $row !== null;
                        if ($row) {
                            $submitted_count++;
                            $classwork_ids[] = $row['classwork_id'];
                            if ($shared === null) {
                                $shared = $row; // first submitted member's content
                            }
                        }
                    }
                    unset($m);

                    $g['submission']      = $shared;                 // null = no submission yet
                    $g['member_count']    = count($g['members']);
                    $g['submitted_count'] = $submitted_count;
                    $g['classwork_ids']   = $classwork_ids;
                    $g['score']           = $shared['score'] ?? null;
                    $g['max_score']       = $shared['max_score'] ?? ($assessment['max_score'] ?? null);

                    // In-progress shared draft (assessment_live_state) so the
                    // instructor can watch a group's collaborative work before
                    // it's submitted. Snapshot at page load; ungraded and
                    // non-authoritative — surfaced only for groups still
                    // drafting (no submission yet) with something actually filled.
                    $g['live_draft']      = null;
                    $g['live_edited_by']  = null;
                    $g['live_updated_at'] = null;
                    $g['live_progress']   = null;
                    $g['live_score']      = null;
                    if ($shared === null) {
                        $live = $this->Live_state_model->get_state($assessment_id, $g['group_id']);
                        if ($live && trim((string) $live['content']) !== '') {
                            $decoded = json_decode($live['content'], true);
                            $decoded = is_array($decoded) ? $decoded : null;

                            if ($iq_kind === null) {
                                $g['live_draft'] = $decoded;
                            } elseif ($decoded !== null && $iq_kind !== 'unrenderable') {
                                $graded = ($iq_kind === 'iq_micro')
                                    ? $this->Iq_topic_model->grade_micro($iq_sections, $decoded['answers'] ?? [])
                                    : $this->Iq_topic_model->grade_discussion($iq_sections, $decoded['sections'] ?? []);

                                $answered = 0;
                                foreach ($graded['results'] as $r) {
                                    if (!empty($r['answered'])) {
                                        $answered++;
                                    }
                                }

                                // A blob with nothing answered yet (the group
                                // opened the quiz but hasn't tapped anything)
                                // is not worth a draft panel.
                                if ($answered > 0) {
                                    $g['live_draft']    = $graded['results'];
                                    $g['live_score']    = $graded['score'];
                                    $g['live_progress'] = [
                                        'answered' => $answered,
                                        'total'    => $graded['total'],
                                        'empty'    => $graded['total'] - $answered,
                                    ];
                                }
                            }

                            if ($g['live_draft'] !== null) {
                                $g['live_edited_by']  = $live['last_edited_by'];
                                $g['live_updated_at'] = $live['updated_at'];
                            }
                        }
                    }
                }
                unset($g);

                $data['groups']           = $groups;
                $data['missing_students'] = $this->classworks->get_missing_submissions($assessment_id);
            }
        }

        $this->load->view('admin/group_submission', $data);
    }

    // Applies one score to a whole group: writes the same score to every
    // member's classworks row for this assessment. Since a group submission is
    // fanned out into per-member rows, grading it once has to update them all.
    // Every write goes through classworks::set_score() (the single validated,
    // max_score-clamped score-write path) — never raw score SQL.
    public function add_group_score($assessment_id, $group_id, $score)
    {
        $this->load->model('Group_member_model');

        $members     = $this->Group_member_model->get_members_by_group($group_id);
        $student_ids = array_column($members, 'trans_no');

        if (empty($student_ids)) {
            echo json_encode([
                'success'       => false,
                'notice'        => 'This group has no members.',
                'score'         => null,
                'updated_count' => 0,
            ]);
            return;
        }

        // Resolve each member's classworks row for this assessment. Members who
        // have no row yet (nobody in the group has submitted, or the row was
        // never fanned out to them) are skipped and reported.
        $rows = $this->db->select('classwork_id')
            ->from('classworks')
            ->where('assessment_id', $assessment_id)
            ->where_in('student_id', $student_ids)
            ->get()->result_array();

        if (empty($rows)) {
            echo json_encode([
                'success'       => false,
                'notice'        => 'No submissions to score for this group yet.',
                'score'         => null,
                'updated_count' => 0,
            ]);
            return;
        }

        $updated = 0;
        $notice  = null;
        $stored  = null;
        foreach ($rows as $row) {
            $err = null;
            $ok  = $this->classworks->set_score($row['classwork_id'], $score, $err);
            // set_score() still succeeds when it caps to max_score, carrying the
            // cap notice in $err — so surface $err on success too, not just fail.
            if ($err !== null) {
                $notice = $err;
            }
            if ($ok) {
                $updated++;
                $stored = $this->db->select('score')
                    ->where('classwork_id', $row['classwork_id'])
                    ->get('classworks')
                    ->row('score');
            }
        }

        echo json_encode([
            'success'       => $updated > 0,
            'notice'        => $notice,
            'score'         => $stored,
            'updated_count' => $updated,
            'skipped_count' => count($student_ids) - $updated,
        ]);
    }

    public function view_student_submissions($student_id = null)
    {
        // Check if a student ID is provided
        if (!$student_id) {
            $this->session->set_flashdata('error', 'No student selected.');
            redirect('AdminController/dashboard');
        }

        // Fetch student details
        $data['student'] = $this->accounts->as_array()->get(['student_id' => $student_id]);

        if (!$data['student']) {
            $this->session->set_flashdata('error', 'Student not found.');
            redirect('AdminController/dashboard');
        }

        // Fetch all classworks (submitted and missing) for the student
        $this->load->model('classworks');
        $this->load->model('assessments');
        $submitted_classworks = $this->classworks->get_submissions_by_student($student_id);
        $all_assessments = $this->assessments->get_all_assessments();

        // Merge submitted classworks with missing ones
        $classworks = [];
        foreach ($all_assessments as $assessment) {
            $found = false;
            foreach ($submitted_classworks as $submission) {
                if ($submission['assessment_id'] == $assessment['assessment_id']) {
                    $classworks[] = $submission;
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $classworks[] = [
                    'assessment_id' => $assessment['assessment_id'],
                    'title' => $assessment['title'],
                    'classwork_id' => null,
                    'score' => null,
                    'created_at' => null,
                    'status' => 'missing',
                ];
            }
        }

        $data['classworks'] = $classworks;

        // Load the view
        $this->load->view('admin/student_submissions', $data);
    }

    public function student_submissions()
    {
        $student_id = $this->input->get('student_id');
        $data['students'] = $this->student_master->get_all(); // Already correct

        if ($student_id) {
            // Fetch submissions for the selected student
            $data['submissions'] = $this->classworks->get_submissions_by_student($student_id);
        } else {
            $data['submissions'] = [];
        }

        // Load the view
        $this->load->view('admin/student_submissions', $data);
    }

    public function active_participation($assessment_id = null)
    {
        $section_id = $this->input->get('section_id');
        $date = $this->input->get('date') ?? date('Y-m-d');

        // Fetch all sections for the dropdown
        $this->db->distinct();
        $this->db->select('section');
        $data['sections'] = $this->db->get('class_schedule')->result_array();

        // Fetch present students if section and date are provided
        if ($section_id) {
            $this->load->model('attendance');
            $data['students'] = $this->attendance->get_present_students($section_id, $date);
            $data['selected_section_id'] = $section_id;
            $data['date'] = $date;
        } else {
            $data['students'] = [];
            $data['selected_section_id'] = null;
            $data['date'] = $date;
        }

        // Pass the assessment ID for scoring
        $data['assessment_id'] = $assessment_id;

        // Load the view
        $this->load->view('admin/active_participation', $data);
    }

    public function check_new_submissions_by_assessment($assessment_id)
    {
        // Fetch the latest submissions for the assessment
        $submissions = $this->classworks->get_all_submissions($assessment_id);

        // Return the data as JSON
        echo json_encode($submissions);
    }

    public function increment_randomized_count($classwork_id)
    {
        $this->classworks->set('randomized_count', 'randomized_count+1', FALSE)
            ->where('classwork_id', $classwork_id)
            ->update('classwork');
        echo json_encode(['success' => true]);
    }

    public function add_score($classwork_id, $score)
    {
        $error  = null;
        $result = $this->classworks->set_score($classwork_id, $score, $error);

        // A capped write still succeeds; $error carries the notice so the
        // grading UI can show what was actually stored.
        echo json_encode([
            'success' => $result,
            'notice'  => $error,
            'score'   => $this->db->select('score')
                ->where('classwork_id', $classwork_id)
                ->get('classworks')
                ->row('score'),
        ]);
    }

    public function add_rand_score_incremental($classwork_id, $points = 2)
    {
        $points = (int) $points;
        if ($points < 1) {
            $points = 1;
        }

        $result = $this->db->query(
            "UPDATE classworks c
             JOIN assessment_full a ON a.assessment_id = c.assessment_id
             SET c.score = LEAST(COALESCE(c.score, 0) + ?, a.max_score)
             WHERE c.classwork_id = ?",
            [$points, $classwork_id]
        );

        $score = $this->db->select('score')
            ->where('classwork_id', $classwork_id)
            ->get('classworks')
            ->row('score');

        echo json_encode(['success' => (bool)$result, 'score' => $score]);
    }

    /** Every classworks row currently scored above its assessment's max_score. */
    public function score_integrity()
    {
        $violations = $this->classworks->get_scores_exceeding_max();

        $this->load->view('admin/score_integrity', [
            'violations' => $violations,
        ]);
    }

    /**
     * Cap one over-max row down to its assessment's max_score. Reuses
     * set_score()'s own clamp — passing the row's current (over-max) score
     * back in is what triggers the cap, so there is exactly one place that
     * decides what "capped" means.
     */
    public function fix_score($classwork_id)
    {
        $row = $this->db->select('score')
            ->where('classwork_id', $classwork_id)
            ->get('classworks')
            ->row_array();

        if (!$row) {
            echo json_encode(['success' => FALSE, 'message' => 'Submission not found.']);
            return;
        }

        $error = null;
        $ok = $this->classworks->set_score($classwork_id, $row['score'], $error);

        echo json_encode([
            'success' => $ok,
            'message' => $error ?: 'Score capped.',
            'score'   => $this->db->select('score')->where('classwork_id', $classwork_id)->get('classworks')->row('score'),
        ]);
    }
}

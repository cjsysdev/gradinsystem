<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Grade presentation.
 *
 * All grade arithmetic lives in Grade_calculator. This controller only resolves
 * which schedule(s) a request refers to, asks the calculator for the numbers,
 * and shapes them for a view. It must not compute a grade.
 */
class GradesController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->is_offline = !isset($_SESSION['online']);
        $this->load->model('Grade_calculator');
    }

    // ------------------------------------------------------------------
    // Student-facing
    // ------------------------------------------------------------------

    public function grades()
    {
        if ($this->is_offline) redirect();

        $result = $this->Grade_calculator->for_student($this->session->student_id);

        if (!$result) {
            $this->load->view('home', [
                'midtermGrades'     => [],
                'finalGrades'       => [],
                'midterm'           => null,
                'final'             => null,
                'overall'           => null,
                'recommendations'   => [],
                'no_enrollment'     => TRUE,
            ]);
            return;
        }

        // Components are shown even when the term as a whole is INC — each
        // component's own percentage is valid and is what a student needs in
        // order to act on it. Only the term total is withheld.
        $data = [
            'midtermGrades'   => array_values($result['midterm_components']),
            'finalGrades'     => array_values($result['final_components']),
            'midterm'         => $result['midterm'],
            'final'           => $result['final'],
            'overall'         => $result['overall'],
            'io_types'        => $result['io_types'],
            'no_enrollment'   => FALSE,
            'recommendations' => $this->buildRecommendations($result),
        ];

        $this->load->view('home', $data);
    }

    // ------------------------------------------------------------------
    // Section sheets
    // ------------------------------------------------------------------

    /** Midterm sheet for one section. */
    public function sectionGrades($section)
    {
        $term      = 'midterm';
        $schedules = $this->Grade_calculator->schedules_for_section($section);

        $studentsGrades = [];
        foreach ($schedules as $sched) {
            $result = $this->Grade_calculator->for_schedule($sched['schedule_id'], $term, TRUE);
            foreach ($result['students'] as $s) {
                $studentsGrades[] = [
                    'student_id'  => $s['student_id'],
                    'student_no'  => $s['student_no'],
                    'firstname'   => $s['firstname'],
                    'lastname'    => $s['lastname'],
                    'middlename'  => $s['middlename'],
                    'section'     => $sched['section'],
                    'grade_point' => $this->Grade_calculator->display_grade_point($s['term'], 1),
                    'percentage'  => $this->Grade_calculator->display_percentage($s['term']),
                    'is_inc'      => $s['term']['status'] !== 'ok',
                    'inc_reason'  => $this->_inc_reason($s['term'], $result['io_types']),
                    'pending'     => $s['term']['pending_count'],
                    // Attendance is now scoped to this schedule, so these are
                    // real counts. The views used to divide by 2 to undo the
                    // old query's cross-schedule double counting.
                    'present'     => $s['attendance']['present'],
                    'absent'      => $s['attendance']['absent'],
                    'late'        => $s['attendance']['late'],
                ];
            }
        }

        $first = $schedules[0] ?? [];
        $this->load->view('section_grades', [
            'studentsGrades' => $studentsGrades,
            'term'           => $term,
            'section'        => $section,
            'class_code'     => $first['class_code'] ?? '',
            'class_name'     => $first['class_name'] ?? '',
            'schedule'       => $first ? $this->Grade_calculator->format_schedule($first) : '',
        ]);
    }

    /** Midterm + final + overall sheet for one section. */
    public function sectionFinalGrades($section)
    {
        $schedules      = $this->Grade_calculator->schedules_for_section($section);
        $studentsGrades = [];

        foreach ($schedules as $sched) {
            $result = $this->Grade_calculator->for_schedule_final($sched['schedule_id']);
            foreach ($result['students'] as $s) {
                $studentsGrades[] = $this->_final_row($s, $sched);
            }
        }

        $this->_sort_by_final($studentsGrades);

        $first = $schedules[0] ?? [];
        $this->load->view('section_grades_finals', [
            'studentsGrades' => $studentsGrades,
            'section'        => $section,
            'class_code'     => $first['class_code'] ?? '',
            'class_name'     => $first['class_name'] ?? '',
            'schedule'       => $first ? $this->Grade_calculator->format_schedule($first) : '',
        ]);
    }

    /** Every active section on one sheet. */
    public function AllSectionGrades()
    {
        $studentsGrades = [];

        foreach ($this->Grade_calculator->active_schedules() as $sched) {
            $result = $this->Grade_calculator->for_schedule_final($sched['schedule_id']);
            foreach ($result['students'] as $s) {
                $studentsGrades[] = $this->_final_row($s, $sched);
            }
        }

        $this->load->view('section_grades_final_new', [
            'studentsGrades' => $studentsGrades,
            'section'        => 'N/A',
            'class_code'     => '',
            'class_name'     => '',
            'schedule'       => '',
        ]);
    }

    // ------------------------------------------------------------------
    // Shaping helpers
    // ------------------------------------------------------------------

    /** One row of a midterm/final/overall sheet. */
    private function _final_row(array $s, array $sched)
    {
        $gc = $this->Grade_calculator;

        return [
            'student_id'     => $s['student_id'],
            'student_no'     => $s['student_no'],
            'firstname'      => $s['firstname'],
            'lastname'       => $s['lastname'],
            'middlename'     => $s['middlename'],
            'section'        => $sched['section'],
            'class_code'     => $sched['class_code'],
            'class_name'     => $sched['class_name'],
            'schedule'       => $gc->format_schedule($sched),
            'midterm_grade'  => $gc->display_grade_point($s['midterm'], 1),
            'final_grade'    => $gc->display_grade_point($s['final'], 1),
            'overall_grade'  => $gc->display_grade_point($s['overall'], 1),
            'is_inc'         => $s['overall']['status'] !== 'ok',
            'inc_reason'     => $s['overall']['reason'],
            'present'        => $s['attendance']['present'],
            'absent'         => $s['attendance']['absent'],
            'late'           => $s['attendance']['late'],
        ];
    }

    /** Numeric grades ascending (1.0 best), INC last. */
    private function _sort_by_final(array &$rows)
    {
        usort($rows, function ($a, $b) {
            $ga = is_numeric($a['overall_grade']) ? (float) $a['overall_grade'] : 999;
            $gb = is_numeric($b['overall_grade']) ? (float) $b['overall_grade'] : 999;
            if ($ga === $gb) {
                return strcmp($a['lastname'], $b['lastname']);
            }
            return $ga <=> $gb;
        });
    }

    /** Human-readable explanation of why a term is INC. */
    private function _inc_reason(array $term, array $io_types)
    {
        if ($term['status'] === 'ok') {
            return '';
        }
        if (($term['reason'] ?? '') === 'missing_components' && !empty($term['missing_iotypes'])) {
            $names = [];
            foreach ($term['missing_iotypes'] as $id) {
                $names[] = $io_types[$id]['type'] ?? "io_type $id";
            }
            return 'No ' . implode(', ', $names) . ' recorded yet';
        }
        if (($term['reason'] ?? '') === 'below_passing') {
            return 'Below passing';
        }
        return 'Incomplete';
    }

    // ------------------------------------------------------------------
    // Recommendations
    // ------------------------------------------------------------------

    private function buildRecommendations(array $result): array
    {
        $recommendations = [];
        $attendance = $this->student_master->get_attendance_summary($this->session->student_id);
        $absences = (int) ($attendance['absent_count'] ?? 0);
        $lates    = (int) ($attendance['late_count'] ?? 0);

        if ($absences >= 4) {
            $recommendations[] = [
                'type'    => 'danger',
                'message' => "Critical: You have $absences absences this semester. Excessive absences may result in automatic failure.",
            ];
        } elseif ($absences >= 2) {
            $recommendations[] = [
                'type'    => 'warning',
                'message' => "Warning: You have $absences absences. Please improve your attendance to avoid grade penalties.",
            ];
        }

        if ($lates >= 3) {
            $recommendations[] = [
                'type'    => 'warning',
                'message' => "Note: You have been late $lates times. Consistent tardiness may affect your class participation record.",
            ];
        }

        foreach (['Midterm' => $result['midterm_components'], 'Final' => $result['final_components']] as $label => $components) {
            foreach ($components as $c) {
                // A component with no assessments has nothing to advise on.
                if (empty($c['n_assessments']) || $c['percentage'] === null) {
                    continue;
                }
                $pct  = (float) $c['percentage'];
                $name = $c['iotype_name'];
                if ($pct < 60) {
                    $recommendations[] = [
                        'type'    => 'danger',
                        'message' => "$label $name: Your score is below passing (" . round($pct, 1) . "%). Prioritise reviewing this area.",
                    ];
                } elseif ($pct < 75) {
                    $recommendations[] = [
                        'type'    => 'warning',
                        'message' => "$label $name: Your score (" . round($pct, 1) . "%) is passing but has room for improvement.",
                    ];
                }
            }
        }

        // Ungraded work is counted as zero, which can make a grade look worse
        // than it is. Say so rather than letting the student guess.
        $pending = (int) ($result['midterm']['pending_count'] ?? 0)
                 + (int) ($result['final']['pending_count'] ?? 0);
        if ($pending > 0) {
            $recommendations[] = [
                'type'    => 'info',
                'message' => "You have $pending submission(s) awaiting grading. They currently count as 0 and your grade will change once they are marked.",
            ];
        }

        // Only advise on an overall standing once one actually exists.
        $reference = null;
        if (($result['overall']['status'] ?? '') === 'ok') {
            $reference = $result['overall']['percentage'];
        } elseif (($result['midterm']['status'] ?? '') === 'ok') {
            $reference = $result['midterm']['percentage'];
        }

        if ($reference === null) {
            $recommendations[] = [
                'type'    => 'info',
                'message' => 'Your term grade is incomplete until every graded component has been recorded. The percentages above reflect the work marked so far.',
            ];
            return $recommendations;
        }

        if ($reference < 60) {
            $recommendations[] = [
                'type'    => 'danger',
                'message' => 'Your current overall grade (' . round($reference, 1) . '%) is below passing. Immediate improvement is required across all components.',
            ];
        } elseif ($reference < 75) {
            $recommendations[] = [
                'type'    => 'info',
                'message' => 'Your current overall grade (' . round($reference, 1) . '%) is passing. Consistent effort will help you achieve a better standing.',
            ];
        } elseif ($reference < 85) {
            $recommendations[] = [
                'type'    => 'success',
                'message' => 'Good performance! Your current overall grade is ' . round($reference, 1) . '%. Keep maintaining your study habits.',
            ];
        } else {
            $recommendations[] = [
                'type'    => 'success',
                'message' => 'Excellent performance! Your current overall grade is ' . round($reference, 1) . '%. Keep up the outstanding work!',
            ];
        }

        return $recommendations;
    }
}

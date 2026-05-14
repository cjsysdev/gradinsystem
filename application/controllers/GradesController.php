<?php
defined('BASEPATH') or exit('No direct script access allowed');

class GradesController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->is_offline = !isset($_SESSION['online']);
    }

    public function grades()
    {
        if ($this->is_offline) redirect();
        $not_cleared = ($this->class_student->where(['is_cleared' => NULL])->as_array()->fields('student_id')->get_all());
        $not_cleared = array_column($not_cleared, 'student_id');
        // if (in_array($this->session->student_id, $not_cleared)) redirect('attendance');
        $midtermGrades = $this->classworks->getGradesByIotype('midterm', $this->session->student_id) ?? [];
        $midtermTotalGrade = 0;
        foreach ($midtermGrades as $grade) {
            $midtermTotalGrade += $grade['percentage'] * ($grade['iotype_percentage'] / 100);
        }

        $finalGrades = $this->classworks->getGradesByIotype('final', $this->session->student_id);

        $finalTotalGrade = 0;
        if (isset($finalGrades)) {
            foreach ($finalGrades as $grade) {
                $finalTotalGrade += $grade['percentage'] * ($grade['iotype_percentage'] / 100);
            }
        }

        $overallFinalGrade = ($midtermTotalGrade * 0.5) + ($finalTotalGrade * 0.5);

        $data['midtermGrades'] = $midtermGrades;
        $data['midtermTotalGrade'] = round($midtermTotalGrade, 2);
        $data['finalGrades'] = $finalGrades;
        $data['finalTotalGrade'] = round($finalTotalGrade, 2);
        $data['overallFinalGrade'] = round($overallFinalGrade, 2);
        $data['recommendations'] = $this->buildRecommendations(
            $midtermGrades,
            $finalGrades,
            round($midtermTotalGrade, 2),
            round($overallFinalGrade, 2)
        );

        $this->load->view('home', $data);
    }

    public function sectionGrades($section)
    {
        $term = 'midterm';
        $grades = $this->classworks->getGradesBySection($term, $section);

        $studentsGrades = [];
        foreach ($grades as $grade) {
            $studentId = $grade['student_id'];

            if (!isset($studentsGrades[$studentId])) {
                $studentsGrades[$studentId] = [
                    'student_id' => $studentId,
                    'firstname' => $grade['firstname'],
                    'lastname' => $grade['lastname'],
                    'middlename' => $grade['middlename'],
                    'section' => $grade['section'],
                    'present' => $grade['present'],
                    'absent' => $grade['absences'],
                    'late' => $grade['lates'],
                    'midterm_total_grade' => 0,
                    'grade_point' => 0,
                    'has_iotype_2' => false,
                    'has_iotype_3' => false,
                    'is_incomplete' => false,
                ];
            }

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

            if (!$studentsGrades[$studentId]['is_incomplete']) {
                $studentsGrades[$studentId]['midterm_total_grade'] += $grade['percentage'] * ($grade['iotype_percentage'] / 100);
            }
        }

        foreach ($studentsGrades as $studentId => &$student) {
            if (!$student['has_iotype_2'] || !$student['has_iotype_3'] || $student['is_incomplete']) {
                $student['midterm_total_grade'] = 'INC';
                $student['grade_point'] = 'INC';
                $student['grade_point'] = convertPercentageToGradePoint($student['midterm_total_grade']);
            } else {
                $student['grade_point'] = convertPercentageToGradePoint($student['midterm_total_grade']);
            }
        }

        $data['studentsGrades'] = $studentsGrades;
        $data['term'] = $term;
        $data['section'] = $section;
        $data['class_code'] = $grades[0]['class_code'] ?? '';
        $data['class_name'] = $grades[0]['class_name'] ?? '';
        $data['schedule'] = $grades[0]['schedule'] ?? '';

        $this->load->view('section_grades', $data);
    }

    public function sectionFinalGrades($section)
    {
        // if ($this->is_offline) redirect();

        // Fetch midterm and final grades for the section
        $midtermGrades = $this->classworks->getGradesBySection('midterm', $section);
        $finalGrades = $this->classworks->getGradesBySection('final', $section);

        $studentsGrades = [];

        // Process midterm grades
        foreach ($midtermGrades as $grade) {
            $studentId = $grade['student_id'];

            if (!isset($studentsGrades[$studentId])) {
                $studentsGrades[$studentId] = [
                    'student_id' => $studentId,
                    'firstname' => $grade['firstname'],
                    'lastname' => $grade['lastname'],
                    'section' => $grade['section'],
                    'midterm_grade' => 0,
                    'final_grade' => 0,
                    'tentative_final_grade' => 0,
                    'is_incomplete' => false,
                ];
            }

            if (is_null($grade['total_score']) || is_null($grade['percentage'])) {
                $studentsGrades[$studentId]['is_incomplete'] = true;
            } else {
                $studentsGrades[$studentId]['midterm_grade'] += $grade['percentage'] * ($grade['iotype_percentage'] / 100);
            }
        }

        // Process final grades
        foreach ($finalGrades as $grade) {
            $studentId = $grade['student_id'];

            if (!isset($studentsGrades[$studentId])) {
                $studentsGrades[$studentId] = [
                    'student_id' => $studentId,
                    'firstname' => $grade['firstname'],
                    'lastname' => $grade['lastname'],
                    'section' => $grade['section'],
                    'midterm_grade' => 0,
                    'final_grade' => 0,
                    'tentative_final_grade' => 0,
                    'is_incomplete' => false,
                ];
            }

            if (is_null($grade['total_score']) || is_null($grade['percentage'])) {
                $studentsGrades[$studentId]['is_incomplete'] = true;
            } else {
                $studentsGrades[$studentId]['tentative_final_grade'] += $grade['percentage'] * ($grade['iotype_percentage'] / 100);
            }
        }

        // Calculate final grades
        foreach ($studentsGrades as $studentId => &$student) {
            if ($student['is_incomplete']) {
                $student['midterm_grade'] = 'INC';
                $student['tentative_final_grade'] = 'INC';
                $student['final_grade'] = 'INC';
            } else {
                $student['final_grade'] = convertPercentageToGradePoint(round(($student['midterm_grade'] * 0.5) + ($student['tentative_final_grade'] * 0.5), 2));
                if ($student['final_grade'] > 3.1) {
                    $student['final_grade'] = 'INC';
                }
            }
        }

        // Sort students by final_grade descending (numeric grades only, INC at the bottom)
        // usort($studentsGrades, function ($a, $b) {
        //     $gradeA = is_numeric($a['final_grade']) ? floatval($a['final_grade']) : 999;
        //     $gradeB = is_numeric($b['final_grade']) ? floatval($b['final_grade']) : 999;
        //     return $gradeA <=> $gradeB;
        // });

        $data['studentsGrades'] = $studentsGrades;
        $data['section'] = $section;
        $data['class_code'] = $midtermGrades[0]['class_code'] ?? '';
        $data['class_name'] = $midtermGrades[0]['class_name'] ?? '';
        $data['schedule'] = $midtermGrades[0]['schedule'] ?? '';
        $data['schedule'] = date('g:iA', strtotime($midtermGrades[0]['start'])) . ' - ' . date('g:iA', strtotime($midtermGrades[0]['end'])) . ' (' . $midtermGrades[0]['day'] . ')';

        var_dump($data['studentsGrades']);
        $this->load->view('section_grades_finals', $data);
        // $this->load->view('section_inc_grades', $data);
    }

    public function AllSectionGrades()
    {
        // if ($this->is_offline) redirect();

        // Fetch midterm and final grades for the section
        $midtermGrades = $this->classworks->getAllGradesBySection('midterm');
        $finalGrades = $this->classworks->getAllGradesBySection('final');

        $studentsGrades = [];

        // Process midterm grades
        foreach ($midtermGrades as $grade) {
            $studentId = $grade['student_id'];

            if (!isset($studentsGrades[$studentId])) {
                $studentsGrades[$studentId] = [
                    'class_code' => $grade['class_code'],
                    'class_name' => $grade['class_name'],
                    'schedule' => date('g:iA', strtotime($grade['start'])) . ' - ' . date('g:iA', strtotime($grade['end'])) . ' (' . $grade['day'] . ')',
                    'student_id' => $studentId,
                    'firstname' => $grade['firstname'],
                    'lastname' => $grade['lastname'],
                    'section' => $grade['section'],
                    'present' => $grade['present'],
                    'absent' => $grade['absences'],
                    'late' => $grade['lates'],
                    'midterm_grade' => 0,
                    'final_grade' => 0,
                    'tentative_final_grade' => 0,
                    'is_incomplete' => false,
                ];
            }

            if (is_null($grade['total_score']) || is_null($grade['percentage'])) {
                $studentsGrades[$studentId]['is_incomplete'] = true;
            } else {
                $studentsGrades[$studentId]['midterm_grade'] += $grade['percentage'] * ($grade['iotype_percentage'] / 100);
            }
        }

        // Process final grades
        foreach ($finalGrades as $grade) {
            $studentId = $grade['student_id'];

            if (!isset($studentsGrades[$studentId])) {
                $studentsGrades[$studentId] = [
                    'class_code' => $grade['class_code'],
                    'class_name' => $grade['class_name'],
                    'schedule' => date('g:iA', strtotime($grade['start'])) . ' - ' . date('g:iA', strtotime($grade['end'])) . ' (' . $grade['day'] . ')',
                    'student_id' => $studentId,
                    'firstname' => $grade['firstname'],
                    'lastname' => $grade['lastname'],
                    'section' => $grade['section'],
                    'present' => $grade['present'],
                    'absent' => $grade['absences'],
                    'late' => $grade['lates'],
                    'midterm_grade' => 0,
                    'final_grade' => 0,
                    'tentative_final_grade' => 0,
                    'is_incomplete' => false,
                ];
            }

            if (is_null($grade['total_score']) || is_null($grade['percentage'])) {
                $studentsGrades[$studentId]['is_incomplete'] = true;
            } else {
                $studentsGrades[$studentId]['tentative_final_grade'] += $grade['percentage'] * ($grade['iotype_percentage'] / 100);
            }
        }

        // Calculate final grades
        foreach ($studentsGrades as $studentId => &$student) {
            if ($student['is_incomplete']) {
                $student['midterm_grade'] = 'INC';
                $student['tentative_final_grade'] = 'INC';
                $student['final_grade'] = 'INC';
            } else {
                $student['final_grade'] = convertPercentageToGradePoint(round(($student['midterm_grade'] * 0.5) + ($student['tentative_final_grade'] * 0.5), 2));
                if ($student['final_grade'] >= 3.05) {
                    $student['final_grade'] = 'INC';
                }
            }
        }

        // Sort students by final_grade descending (numeric grades only, INC at the bottom)
        // usort($studentsGrades, function ($a, $b) {
        //     $gradeA = is_numeric($a['final_grade']) ? floatval($a['final_grade']) : 999;
        //     $gradeB = is_numeric($b['final_grade']) ? floatval($b['final_grade']) : 999;
        //     return $gradeA <=> $gradeB;
        // });

        $data['studentsGrades'] = $studentsGrades;
        $data['section'] = 'N/A';
        $data['class_code'] = $midtermGrades[0]['class_code'] ?? '';
        $data['class_name'] = $midtermGrades[0]['class_name'] ?? '';
        $data['schedule'] = $midtermGrades[0]['schedule'] ?? '';
        $data['schedule'] = date('g:iA', strtotime($midtermGrades[0]['start'])) . ' - ' . date('g:iA', strtotime($midtermGrades[0]['end'])) . ' (' . $midtermGrades[0]['day'] . ')';

        // $this->load->view('allgrades', $data);
        $this->load->view('section_grades_finals', $data);
        // $this->load->view('section_grades_midterm', $data);
        // $this->load->view('section_inc_grades', $data);
    }

    public function sectionGradesPerType($section)
    {
        $term = 'midterm';
        $grades = $this->classworks->getGradesBySection($term, $section);

        $studentsGrades = [];

        var_dump($grades);
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
                    'is_incomplete' => false,
                ];
            }


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

            if (!$studentsGrades[$studentId]['is_incomplete']) {
                $studentsGrades[$studentId]['midterm_total_grade'] += $grade['percentage'] * ($grade['iotype_percentage'] / 100);
            }
        }

        foreach ($studentsGrades as $studentId => &$student) {
            if (!$student['has_iotype_2'] || !$student['has_iotype_3'] || $student['is_incomplete']) {
                // $student['midterm_total_grade'] = 'INC';
                // $student['grade_point'] = 'INC';
                $student['grade_point'] = convertPercentageToGradePoint($student['midterm_total_grade']);
            } else {
                $student['grade_point'] = convertPercentageToGradePoint($student['midterm_total_grade']);
            }
        }

        $data['studentsGrades'] = $studentsGrades;
        $data['term'] = $term;
        $data['section'] = $section;
        $data['class_code'] = $grades[0]['class_code'] ?? '';
        $data['class_name'] = $grades[0]['class_name'] ?? '';
        $data['schedule'] = $grades[0]['schedule'] ?? '';

        $this->load->view('section_grades', $data);
    }

    private function buildRecommendations(array $midtermGrades, $finalGrades, float $midtermTotal, float $overallTotal): array
    {
        $recommendations = [];
        $attendance  = $this->student_master->get_attendance_summary($this->session->student_id);
        $absences    = (int)($attendance['absent_count']  ?? 0);
        $lates       = (int)($attendance['late_count']    ?? 0);
        $present     = (int)($attendance['present_count'] ?? 0);
        $excused     = (int)($attendance['excuse_count']  ?? 0);
        $totalSessions = $absences + $lates + $present + $excused;
        $attendanceRate = $totalSessions > 0
            ? round(($present + $excused) / $totalSessions * 100, 1)
            : 100;

        // --- Attendance ---
        if ($absences >= 4) {
            $recommendations[] = [
                'type'    => 'danger',
                'message' => "Critical: You have $absences absences this semester (attendance rate: {$attendanceRate}%). Excessive absences may result in automatic failure.",
            ];
        } elseif ($absences >= 2) {
            $recommendations[] = [
                'type'    => 'warning',
                'message' => "Warning: You have $absences absences (attendance rate: {$attendanceRate}%). Please improve your attendance to avoid grade penalties.",
            ];
        } elseif ($absences === 0 && $lates === 0) {
            $recommendations[] = [
                'type'    => 'success',
                'message' => "Perfect attendance! You have no absences or tardiness this semester. Keep it up!",
            ];
        } elseif ($absences === 0) {
            $recommendations[] = [
                'type'    => 'info',
                'message' => "Good attendance! You have no absences this semester. Your attendance rate is {$attendanceRate}%.",
            ];
        }

        if ($lates >= 3) {
            $recommendations[] = [
                'type'    => 'warning',
                'message' => "Note: You have been late $lates times. Consistent tardiness may affect your class participation record.",
            ];
        }

        // --- Per-component alerts + track weakest ---
        $weakest    = null;
        $weakestPct = PHP_INT_MAX;

        foreach ($midtermGrades as $grade) {
            $pct    = (float)($grade['percentage']         ?? 0);
            $name   = $grade['iotype_name']                ?? 'Component';
            $weight = (float)($grade['iotype_percentage']  ?? 0);
            if ($pct < $weakestPct) {
                $weakestPct = $pct;
                $weakest    = ['term' => 'Midterm', 'name' => $name, 'pct' => $pct, 'weight' => $weight];
            }
            if ($pct < 60) {
                $recommendations[] = [
                    'type'    => 'danger',
                    'message' => "Midterm $name: Your score is below passing (" . round($pct, 1) . "%). Prioritise reviewing this area before the next term.",
                ];
            } elseif ($pct < 75) {
                $recommendations[] = [
                    'type'    => 'warning',
                    'message' => "Midterm $name: Your score (" . round($pct, 1) . "%) is passing but has room for improvement. Consistent practice will help.",
                ];
            }
        }

        if (!empty($finalGrades)) {
            // Build midterm map for trend analysis
            $midtermMap = [];
            foreach ($midtermGrades as $g) {
                $midtermMap[$g['iotype_name'] ?? ''] = (float)($g['percentage'] ?? 0);
            }

            foreach ($finalGrades as $grade) {
                $pct    = (float)($grade['percentage']        ?? 0);
                $name   = $grade['iotype_name']               ?? 'Component';
                $weight = (float)($grade['iotype_percentage'] ?? 0);
                if ($pct < $weakestPct) {
                    $weakestPct = $pct;
                    $weakest    = ['term' => 'Final', 'name' => $name, 'pct' => $pct, 'weight' => $weight];
                }
                if ($pct < 60) {
                    $recommendations[] = [
                        'type'    => 'danger',
                        'message' => "Final $name: Your score is below passing (" . round($pct, 1) . "%). Focus on this component immediately.",
                    ];
                } elseif ($pct < 75) {
                    $recommendations[] = [
                        'type'    => 'warning',
                        'message' => "Final $name: Your score (" . round($pct, 1) . "%) needs improvement. Keep up your study efforts.",
                    ];
                }

                // Trend: compare final vs midterm for same component
                if (isset($midtermMap[$name])) {
                    $diff = $pct - $midtermMap[$name];
                    if ($diff <= -10) {
                        $recommendations[] = [
                            'type'    => 'warning',
                            'message' => "Trend — $name: Your final score (" . round($pct, 1) . "%) dropped " . round(abs($diff), 1) . "% from midterm. Identify what changed and address it.",
                        ];
                    } elseif ($diff >= 10) {
                        $recommendations[] = [
                            'type'    => 'success',
                            'message' => "Improvement — $name: You improved by " . round($diff, 1) . "% from midterm to final. Your efforts are paying off!",
                        ];
                    }
                }
            }
        }

        // --- Weakest component callout (only when not already in danger zone) ---
        if ($weakest !== null && $weakest['pct'] >= 60 && $weakest['pct'] < 80) {
            $recommendations[] = [
                'type'    => 'info',
                'message' => "Focus Area — {$weakest['term']} {$weakest['name']} ({$weakest['weight']}% weight): At " . round($weakest['pct'], 1) . "%, this is your lowest-scoring component. A targeted effort here will improve your overall grade the most.",
            ];
        }

        // --- Grade needed to pass final term (shown only before final grades exist) ---
        if (empty($finalGrades) && $midtermTotal > 0) {
            // overallFinalGrade = midterm * 0.5 + final * 0.5  →  final needed = (75 - midterm*0.5) / 0.5
            $needed = (75 - $midtermTotal * 0.5) / 0.5;
            if ($needed > 100) {
                $recommendations[] = [
                    'type'    => 'danger',
                    'message' => "Even a perfect final term score may not be enough to achieve a passing grade. Speak with your instructor about your options.",
                ];
            } elseif ($needed > 0) {
                $recommendations[] = [
                    'type'    => 'info',
                    'message' => "To achieve a passing overall grade (75%), you need at least " . round($needed, 1) . "% in the final term.",
                ];
            } else {
                $recommendations[] = [
                    'type'    => 'success',
                    'message' => "Your midterm performance (" . round($midtermTotal, 1) . "%) ensures you will pass even with a minimum final term score. Stay consistent!",
                ];
            }
        }

        // --- Interactive quiz performance ---
        $iqRow = $this->db->query(
            "SELECT COUNT(*) AS total, SUM(is_correct) AS correct,
                    ROUND(SUM(is_correct) / COUNT(*) * 100, 1) AS accuracy
             FROM iq_attempts WHERE student_id = ?",
            [$this->session->student_id]
        )->row_array();

        if (!empty($iqRow) && (int)($iqRow['total'] ?? 0) > 0) {
            $iqAccuracy = (float)($iqRow['accuracy'] ?? 0);
            if ($iqAccuracy < 50) {
                $recommendations[] = [
                    'type'    => 'warning',
                    'message' => "Interactive Quizzes: Your overall accuracy is " . round($iqAccuracy, 1) . "%. Revisit the discussion topics to strengthen your conceptual understanding.",
                ];
            } elseif ($iqAccuracy >= 80) {
                $recommendations[] = [
                    'type'    => 'success',
                    'message' => "Interactive Quizzes: Great work! Your overall quiz accuracy is " . round($iqAccuracy, 1) . "%. Keep reinforcing your knowledge.",
                ];
            }
        }

        // --- Overall grade summary ---
        $referenceGrade = !empty($finalGrades) ? $overallTotal : $midtermTotal;
        if ($referenceGrade < 60) {
            $recommendations[] = [
                'type'    => 'danger',
                'message' => "Your current overall grade (" . round($referenceGrade, 1) . "%) is below passing. Immediate improvement is required across all components.",
            ];
        } elseif ($referenceGrade < 75) {
            $recommendations[] = [
                'type'    => 'info',
                'message' => "Your current overall grade (" . round($referenceGrade, 1) . "%) is passing. Consistent effort will help you achieve a better standing.",
            ];
        } elseif ($referenceGrade < 85) {
            $recommendations[] = [
                'type'    => 'success',
                'message' => "Good performance! Your current overall grade is " . round($referenceGrade, 1) . "%. Keep maintaining your study habits.",
            ];
        } else {
            $recommendations[] = [
                'type'    => 'success',
                'message' => "Excellent performance! Your current overall grade is " . round($referenceGrade, 1) . "%. Keep up the outstanding work!",
            ];
        }

        return $recommendations;
    }
}

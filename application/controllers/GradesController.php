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
        $midtermGrades = $this->classworks->getGradesByIotype('midterm', $this->session->student_id);

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
                $student['midterm_total_grade'] = 'INC';
                $student['grade_point'] = 'INC';
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
        if ($this->is_offline) redirect();

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
            }
        }

        $data['studentsGrades'] = $studentsGrades;
        $data['section'] = $section;
        $data['class_code'] = $midtermGrades[0]['class_code'] ?? '';
        $data['class_name'] = $midtermGrades[0]['class_name'] ?? '';
        $data['schedule'] = $midtermGrades[0]['schedule'] ?? '';

        $this->load->view('section_grades_finals', $data);
    }
}

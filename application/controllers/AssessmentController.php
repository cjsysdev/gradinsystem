<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AssessmentController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Manila');
        $this->load->model(['assessments', 'classworks', 'class_schedule']);
        $this->load->helper(['url']);
        $this->load->library(['session']);
        $this->is_offline = !isset($_SESSION['online']);
    }

    public function assessment_view()
    {
        if ($this->is_offline) redirect();
        $this->load->view('assessment_view');
    }

    public function assessment_view_code($classwork_id)
    {
        $classwork = $this->assessments->as_array()->get($classwork_id);

        $student_info = $this->class_student->get_class_student_info($this->session->student_id);

        if (!$classwork) {
            show_404();
        }

        $widget = null;
        if (!empty($classwork['widget_id'])) {
            $this->load->model('Widgets_model');
            $widget = $this->Widgets_model->get($classwork['widget_id']);
        }

        // Brainstorm Board is a shared, section-wide board, not a per-student
        // "fill this in and submit" form — it gets its own full-page flow
        // regardless of grouping/iotype.
        if ($widget && $widget['widget_key'] === 'brainstorm') {
            redirect('BrainstormController/board/' . $classwork_id);
            return;
        }

        // Timed/Secure Quiz is its own fullscreen/timer/lockdown page, not a
        // form embedded in this one — see SecureQuizController.
        if ($widget && $widget['widget_key'] === 'secure_quiz') {
            redirect('secure_quiz/' . $classwork['assessment_id']);
            return;
        }

        // Interactive Discussion/Quiz wraps an assets/json/{topic}.json lesson,
        // not a form either — hand off to InteractiveQuizController, which
        // records the score/answers on first completion (see save_result()).
        // Exception: when it's also a grouping assessment, fall through to the
        // is_groupings block below so the whole group plays one shared/synced
        // copy via GroupWorkController::workspace() instead of solo.
        if ($widget && $widget['widget_key'] === 'iq_discussion') {
            $is_group_iq = false;
            if (!empty($classwork['is_groupings'])) {
                $this->load->model('Grouping_model');
                $is_group_iq = (bool) $this->Grouping_model->get_set_for_assessment($classwork_id);
            }
            if (!$is_group_iq) {
                $config = json_decode($classwork['given'] ?? '', true) ?: [];
                $topic  = $config['topic'] ?? '';
                if ($topic) {
                    redirect('interactive_quiz/discussion/' . $topic . '/' . $classwork_id);
                    return;
                }
            }
        }

        if (!empty($classwork['is_groupings'])) {
            $this->load->model('Grouping_model');
            if ($this->Grouping_model->get_set_for_assessment($classwork_id)) {
                redirect('GroupWorkController/workspace/' . $classwork_id);
                return;
            }
        }

        if(empty($student_info['is_cleared']) && $classwork['iotype_id'] == 3) {
            $this->session->set_flashdata('warning', 'Only students with cleared clearance requirements may take the exam.');
            redirect('attendance');
        }

        // Prefill the widget with the student's prior submission (if any) —
        // without this, re-opening a submitted widget always renders blank,
        // and Turn In would overwrite the good submission with blank JSON.
        // Excluded for `quiz`: classworks.code there holds graded *results*
        // (a list), not the {'answers': ...} shape input mode expects — see
        // the $existing contract documented in widgets/quiz.php.
        $widget_existing = null;
        if ($widget && $widget['widget_key'] !== 'quiz') {
            $prior = $this->classworks->where([
                'student_id'    => $this->session->student_id,
                'assessment_id' => $classwork_id,
            ])->get();
            $widget_existing = $prior ? (json_decode($prior->code ?? '', true) ?: null) : null;
        }

        $data = [
            'classwork' => $classwork,
            'is_cleared' => $student_info['is_cleared'],
            'widget' => $widget,
            'widget_config' => $widget ? (json_decode($classwork['given'] ?? '', true) ?: []) : [],
            'widget_existing' => $widget_existing,
        ];

        $this->load->view('assessment_view_code', $data);
    }

    // Retired: this was the pre-modal add-assessment form, predating the
    // widget system and the master/assessment_section split — it wrote a
    // flat row with no widget support and a legacy 'active' status string,
    // which is now the wrong shape entirely (content vs. per-section fields
    // live on different tables). manage_assessments' Add/Edit modal
    // (AdminController::save_assessment()) fully supersedes it.
    public function input_submit()
    {
        redirect('manage_assessments');
    }

    public function add_assessment()
    {
        redirect('manage_assessments');
    }

    public function upload_activity()
    {
        $filename = $this->class_student->get(
            ['student_id' => $this->session->student_id]
        )->section . '-' . $this->session->lastname . time();

        $post = $this->input->post();
        $config['upload_path'] = './uploads/error_submission';
        $config['allowed_types'] = '*';
        $config['max_size'] = 51200; // 50MB
        $config['file_name'] = $filename . '-' . $post['project_title'] . '-' . $post['members'];

        $this->upload->initialize($config);

        if (!$this->upload->do_upload('photo-upload')) {
            $this->session->set_flashdata('error', $this->upload->display_errors());
            redirect('output_upload');
        } else {
            $this->upload->data();
            $this->session->set_flashdata('success', 'Upload Successful');
            redirect('output_upload');
        }
    }

    public function submit_classwork()
    {
        $post = $this->input->post();
        $student_id = $this->session->student_id;
        $assessment_id = $post['assessment_id'];

        // Initialize submission data
        $submission_data = [
            'student_id' => $student_id,
            'assessment_id' => $assessment_id,
            'status' => 'submitted',
            'submitted_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s') // For new submissions
        ];

        // Check if a file is uploaded
        if (!empty($_FILES['file-upload']['name'])) {
            $filename = $this->class_student->get(['student_id' => $student_id])->section . '-' . $this->session->lastname . '-' . time();

            $config['upload_path'] = './uploads/classworks';
            $config['allowed_types'] = '*'; // Allow all file types
            $config['max_size'] = 51200; // 50MB
            $config['file_name'] = $filename;

            $this->upload->initialize($config);

            if (!$this->upload->do_upload('file-upload')) {
                $this->session->set_flashdata('error', $this->upload->display_errors());
                redirect('classwork');
            } else {
                $upload_data = $this->upload->data();
                $uploaded_file_path = $upload_data['full_path'];
                $file_type = $upload_data['file_type'];

                // Check if the uploaded file is an image
                if (strpos($file_type, 'image') !== false) {
                    // Compress the uploaded image
                    $config['image_library'] = 'gd2';
                    $config['source_image'] = $uploaded_file_path;
                    $config['quality'] = '70%'; // Adjust quality to reduce size
                    $config['maintain_ratio'] = TRUE;
                    $config['width'] = 1024; // Resize width
                    $config['height'] = 768; // Resize height

                    $this->load->library('image_lib', $config);
                    $this->image_lib->initialize($config);
                    $this->image_lib->resize();
                    $this->image_lib->clear();

                    if (!$this->image_lib->resize()) {
                        $this->session->set_flashdata('error', $this->image_lib->display_errors());
                        redirect('classwork');
                    }
                }

                $submission_data['file_upload'] = $upload_data['file_name'];
                $submission_data['code'] = null; // Clear the code field for file submissions
            }
        } elseif (!empty($post['code'])) {
            $assessment = $this->assessments->as_array()->get($assessment_id);

            if (!empty($assessment['widget_id'])) {
                $this->load->model('Widgets_model');
                $widget = $this->Widgets_model->get($assessment['widget_id']);

                if (!empty($widget) && $widget['widget_key'] === 'quiz') {
                    // Auto-graded: never trust a client-computed score — grade
                    // server-side from the assessment's own config.
                    $config = json_decode($assessment['given'] ?? '', true) ?: [];
                    $answers = json_decode($post['code'], true)['answers'] ?? [];
                    $graded = $this->Widgets_model->grade_quiz($config, $answers);

                    $submission_data['code'] = json_encode($graded['results']);
                    $submission_data['score'] = $graded['score'];
                    $submission_data['file_upload'] = null;
                    $redirect_to_result = true;
                } else {
                    // Widget submissions are structured JSON — keep them in the
                    // code column so student_submission.php / grading can read
                    // them back, instead of writing to a file like plain text.
                    $submission_data['code'] = $post['code'];
                    $submission_data['file_upload'] = null;
                }
            } else {
                // Handle textarea submission: save code as a text file and store filename
                $section = $this->class_student->get(['student_id' => $student_id])->section;
                $filename = $section . '-' . $this->session->lastname . '-' . time() . '.txt';
                $upload_path = './uploads/classworks/';
                if (!is_dir($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }
                file_put_contents($upload_path . $filename, $post['code']);
                $submission_data['file_upload'] = $filename;
                $submission_data['code'] = null; // Optionally clear the code field
            }
        } else {
            // No file or code submitted
            $this->session->set_flashdata('error', 'Please enter code or upload a file before submitting.');
            redirect('classwork');
        }

        // Check if a submission already exists
        $existing_submission = $this->classworks->where([
            'student_id' => $student_id,
            'assessment_id' => $assessment_id
        ])->get();

        if (!$existing_submission) {
            // Insert new submission
            $classwork_id = $this->classworks->insert($submission_data);
            $this->session->set_flashdata('success', 'Classwork submitted successfully!');
        } else {
            // Update existing submission.
            // MY_Model::update() takes (data, where) — NOT (where, data).
            $this->classworks->update($submission_data, $existing_submission->classwork_id);
            $classwork_id = $existing_submission->classwork_id;
            $this->session->set_flashdata('success', 'Classwork updated successfully!');
        }

        if (!empty($redirect_to_result)) {
            redirect('student_submission/' . $classwork_id);
            return;
        }

        redirect('classwork');
    }

    public function upload_pdf()
    {
        $assessment_id = $this->input->post('assessment_id');

        // Configure upload settings
        $config['upload_path'] = './uploads/assessments';
        $config['allowed_types'] = 'pdf';
        $config['max_size'] = 10240; // 10MB
        $config['file_name'] = 'assessment_' . $assessment_id . '_given';

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('pdf_file')) {
            $this->session->set_flashdata('error', $this->upload->display_errors());
            redirect('AdminController/manage_assessments');
        } else {
            $upload_data = $this->upload->data();
            $pdf_file_path = 'uploads/assessments/' . $upload_data['file_name'];

            // $assessment_id posted here is a section id (assessment_section_id)
            // — pdf_file_path lives on the shared master, so it's set via a
            // join rather than a direct update keyed by assessment_id.
            $this->db->query(
                "UPDATE assessments m
                 JOIN assessment_section s ON s.assessment_id = m.assessment_id
                 SET m.pdf_file_path = ?
                 WHERE s.assessment_section_id = ?",
                [$pdf_file_path, $assessment_id]
            );

            $this->session->set_flashdata('success', 'PDF uploaded successfully!');
            redirect('AdminController/manage_assessments');
        }
    }
}

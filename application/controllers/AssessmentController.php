<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AssessmentController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['assessments', 'classworks']);
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

        if (!$classwork) {
            show_404();
        }

        $data = [
            'classwork' => $classwork
        ];

        $this->load->view('assessment_view_code', $data);
    }

    public function input_submit()
    {
        $this->assessments->insert($this->input->post());
    }

    public function upload_activity()
    {
        $filename = $this->class_student->get(
            ['student_id' => $this->session->student_id]
        )->section . "-MID-PT-" . $this->session->lastname;

        $config['upload_path'] = './uploads/outputs';
        $config['allowed_types'] = '*';
        $config['max_size'] = 51200; // 50MB
        $config['file_name'] = $filename;

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
            $config['allowed_types'] = '*';
            $config['max_size'] = 51200; // 50MB
            $config['file_name'] = $filename;

            $this->upload->initialize($config);

            if (!$this->upload->do_upload('file-upload')) {
                $this->session->set_flashdata('error', $this->upload->display_errors());
                redirect('classwork');
            } else {
                $upload_data = $this->upload->data();
                $submission_data['file_upload'] = $upload_data['file_name'];
                $submission_data['code'] = null; // Clear the code field for file submissions
            }
        } else {
            // Handle textarea submission
            $submission_data['code'] = $post['code'];
            $submission_data['file_upload'] = null; // Clear the file_upload field for text submissions
        }

        // Check if a submission already exists
        $existing_submission = $this->classworks->where([
            'student_id' => $student_id,
            'assessment_id' => $assessment_id
        ])->get();

        if (!$existing_submission) {
            // Insert new submission
            $this->classworks->insert($submission_data);
            $this->session->set_flashdata('success', 'Classwork submitted successfully!');
        } else {
            // Update existing submission
            $this->classworks->update($existing_submission->classwork_id, $submission_data);
            $this->session->set_flashdata('success', 'Classwork updated successfully!');
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

            // Update the assessment with the PDF file path
            $this->db->where('assessment_id', $assessment_id);
            $this->db->update('assessments', ['pdf_file_path' => $pdf_file_path]);

            $this->session->set_flashdata('success', 'PDF uploaded successfully!');
            redirect('AdminController/manage_assessments');
        }
    }
}

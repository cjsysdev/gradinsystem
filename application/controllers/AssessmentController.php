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
}
<?php
defined('BASEPATH') or exit('No direct script access allowed');

class DiscussionController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('discussions');
    }

    public function index()
    {
        $cc105 = $this->discussions->as_array()->order_by('created_at', 'desc')->get_all(['class_id' => 1]);
        $business_intelligence = $this->discussions->as_array()->order_by('created_at', 'desc')->get_all(['class_id' => 5]);

        $class = $this->class_student->get(['student_id' => $_SESSION['student_id']])->class_id;

        $cc104 = $this->discussions->as_array()->order_by('created_at', 'desc')->get_all(['class_id' => 3]);
        $ws101 = $this->discussions->as_array()->order_by('created_at', 'desc')->get_all(['class_id' => 4]);

        $topics = ($class == '3') ? $cc105 : $business_intelligence;

        $data['topics'] = $topics;
        $this->load->view('discussion_view', $data);
    }

    public function css_cascade_activity()
    {
        if ($this->session->role != 'admin') {
            redirect();
        }
        $this->load->view('discussions/css_cascading_activity');
    }

    public function topic($title)
    {
        $this->load->view('discussions/' . $title);
    }
}

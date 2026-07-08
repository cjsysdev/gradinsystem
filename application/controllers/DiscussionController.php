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
        $cc105 = $this->discussions->get_visible_by_class(1);
        $business_intelligence = $this->discussions->get_visible_by_class(5);

        $enrollment = $this->class_student->get_class_student_info($_SESSION['student_id']);
        $class = $enrollment ? $enrollment['class_id'] : null;

        $cc104 = $this->discussions->get_visible_by_class(3);
        $ws101 = $this->discussions->get_visible_by_class(4);

        $topics = ($class == '3') ? $cc105 : $business_intelligence;

        $data['topics'] = $topics;
        $this->load->view('discussion_view', $data);
    }

    public function css_cascade_activity()
    {
        if ($this->session->role != 'admin') {
            redirect();
        }
        $this->load->view('discussions/frontend/css_cascading_activity');
    }

    // URI segments after the method name are passed as separate positional
    // arguments by CI3's default routing (NOT as one slash-joined string), so
    // "topic/105/105_constraints" arrives as topic('105', '105_constraints')
    // rather than topic('105/105_constraints'). Reassemble them here. Falls
    // back to a flat single segment for any old-style bookmarked links.
    public function topic($folder, $file = null)
    {
        $title = $file === null ? $folder : $folder . '/' . $file;

        if (!preg_match('#^[a-zA-Z0-9_\-]+(/[a-zA-Z0-9_\-]+)*$#', $title)) {
            show_error('Invalid topic name.', 400);
            return;
        }
        $this->load->view('discussions/' . $title);
    }
}

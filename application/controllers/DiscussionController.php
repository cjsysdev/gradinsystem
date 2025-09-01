<?php
defined('BASEPATH') or exit('No direct script access allowed');

class DiscussionController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Load model if you have a topics/discussion model
        // $this->load->model('discussion_model');
    }

    public function index()
    {
        // Example static topics array, replace with DB query if needed
        $topics = [
            [
                'title' => 'C Structs',
                'description' => 'A struct in C is a user-defined data type that allows grouping variables of different types under a single name.',
                'link' => base_url('discussion/structs')
            ]
            // Add more topics as needed
        ];

        $data['topics'] = $topics;
        $this->load->view('discussion_view', $data);
    }

    public function structs()
    {
        $this->load->view('discussions/structs2');
    }
}

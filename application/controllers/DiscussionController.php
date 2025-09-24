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

        $class = $this->class_student->get(['student_id' => $_SESSION['student_id']])->class_id;

        $cc104 = [
            [
                'title' => 'DSA Linked Lists in Memory',
                'description' => 'To explain what linked lists are, and how linked lists are different from arrays, we need to understand some basics about how computer memory works.',
                'link' => base_url('DiscussionController/linked_list_memory')
            ],
            [
                'title' => 'Linked List',
                'description' => 'A Linked List is, as the word implies, a list where the nodes are linked together. Each node contains data and a pointer. The way they are linked together is that each node points to where in the memory the next node is placed.
            ',
                'link' => base_url('DiscussionController/linked_list')
            ],
            [
                'title' => 'C Structs',
                'description' => 'A struct in C is a user-defined data type that allows grouping variables of different types under a single name.',
                'link' => base_url('discussion/structs')
            ]
        ];

        $ws101 = [
            [
                'title' => 'PHP includes',
                'description' => 'The include (or 
require) statement takes all the text/code/markup that exists in the specified file and copies it into
the file that uses the include statement.',
                'link' => base_url('assets/pdfjs/web/viewer.html') . '?file=' . urlencode(base_url('uploads/discussions/web_dev.pdf')) . '#page=145'
            ],
            [
                'title' => 'PHP sessions',
                'description' => 'A session is a way to store information (in variables) to be used across multiple pages.',
                'link' => base_url('assets/pdfjs/web/viewer.html') . '?file=' . urlencode(base_url('uploads/discussions/web_dev.pdf')) . '#page=411'
            ],
            [
                'title' => 'PHP Superglobals',
                'description' => ' Some predefined variables in PHP are "superglobals", which means that they are always accessible, regardless of scope - and you can access them from any function, class or file without having to do anything special',
                'link' => base_url('assets/pdfjs/web/viewer.html') . '?file=' . urlencode(base_url('uploads/discussions/web_dev.pdf')) . '#page=93'
            ],
            [
                'title' => 'PHP Form Handling',
                'description' => ' The PHP superglobals $_GET and $_POST are used to collect form-data.',
                'link' => base_url('assets/pdfjs/web/viewer.html') . '?file=' . urlencode(base_url('uploads/discussions/web_dev.pdf')) . '#page=366'
            ],
            [
                'title' => 'HTML Tags Chart',
                'description' => ' To use any of the following HTML tags, simply select the HTML code you like and copy and paste it into your web page.',
                'link' => base_url('uploads/discussions/html-tags-chart.pdf')
            ]
        ];

        $topics = ($class == '3') ? $cc104 : $ws101;

        $data['topics'] = $topics;
        $this->load->view('discussion_view', $data);
    }

    public function structs()
    {
        $this->load->view('discussions/structs2');
    }

    public function includes()
    {
        $this->load->view('discussions/php_includes');
    }

    public function linked_list()
    {
        $this->load->view('discussions/dsa_theory_linkedlists');
    }

    public function linked_list_memory()
    {
        $this->load->view('discussions/dsa_theory_linkedlists_memory');
    }
}

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
                'title' => 'Introduction to Queues',
                'description' => 'Learn how the queue data structure works using arrays and the FIFO principle.',
                'link' => base_url('DiscussionController/topic/queue_intro')
            ],
            [
                'title' => 'Allocate Memory',
                'description' => 'The process of reserving memory is called allocation. The way to allocate memory depends on the type of memory.',
                'link' => base_url('DiscussionController/topic/memory_allocate')
            ],
            [
                'title' => 'DSA Linked Lists in Memory',
                'description' => 'To explain what linked lists are, and how linked lists are different from arrays, we need to understand some basics about how computer memory works.',
                'link' => base_url('DiscussionController/topic/linked_list_memory')
            ],
            [
                'title' => 'Linked List',
                'description' => 'A Linked List is, as the word implies, a list where the nodes are linked together. Each node contains data and a pointer. The way they are linked together is that each node points to where in the memory the next node is placed.
            ',
                'link' => base_url('DiscussionController/topic/linked_list')
            ],
            [
                'title' => 'C Structs',
                'description' => 'A struct in C is a user-defined data type that allows grouping variables of different types under a single name.',
                'link' => base_url('discussion/structs')
            ]
        ];

        $ws101 = [
            [
                'title' => 'Introduction to JavaScript',
                'description' => 'Learn the basics of JavaScript, including syntax, variables, and data types.',
                'link' => base_url('DiscussionController/topic/js_intro')
            ],
            [
                'title' => 'Bootstrap Tables',
                'description' => 'Learn how to create clean, responsive, and user-friendly tables using Bootstrap 4.',
                'link' => base_url('DiscussionController/topic/bootstrap_table')
            ],
            [
                'title' => 'Web Sandbox',
                'description' => 'Create interactive code and display its result',
                'link' => base_url('DiscussionController/topic/bootstrap_sandbox')
            ],
            [
                'title' => 'Bootstrap Forms',
                'description' => 'Learn how to design responsive and user-friendly forms using Bootstrap 4 components and utilities.',
                'link' => base_url('DiscussionController/topic/bootstrap_forms')
            ],
            [
                'title' => 'Bootstrap Framework Basics',
                'description' => 'Learn how to install and use Bootstrap containers and the grid system
                to create responsive web layouts with ease.',
                'link' => base_url('DiscussionController/topic/css_bootstrap')
            ],
            [
                'title' => 'CSS Display',
                'description' => 'Explore common display values with live examples and a small playground. Click any value to see what it does.',
                'link' => base_url('DiscussionController/topic/css_display')
            ],
            [
                'title' => 'CSS Essential Properties',
                'description' => 'Learn the most common and useful CSS properties that make your web pages beautiful and structured!',
                'link' => base_url('DiscussionController/topic/css_properties')
            ],
            [
                'title' => 'CSS Errors',
                'description' => 'Errors in CSS can lead to unexpected behavior or styles not being applied correctly. This page shows common CSS mistakes and how to avoid them.',
                'link' => base_url('DiscussionController/topic/css_error')
            ],
            [
                'title' => 'CSS Cascading and Selector Priority',
                'description' => 'When multiple CSS rules target the same HTML element, the cascade determines which style is applied.',
                'link' => base_url('DiscussionController/topic/css_cascade')
            ],
            [
                'title' => 'CSS Cascading and Selector Priority - Interactive Activity',
                'description' => 'When multiple CSS rules target the same HTML element, the cascade determines which style is applied.',
                'link' => base_url('DiscussionController/topic/css_cascade_activity_nojs')
            ],
            // [
            //     'title' => 'Introduction to CSS [PDF]',
            //     'description' => 'In this lesson, we will look at how to make your web pages more attractive, controlling the design of them using CSS.',
            //     'link' => base_url('DiscussionController/topic/css_intro')
            // ],
            // [
            //     'title' => 'CSS Syntax [Web]',
            //     'description' => 'In this lesson, we will look at how to make your web pages more attractive, controlling the design of them using CSS.',
            //     'link' => base_url('DiscussionController/topic/css_syntax')
            // ],
            // [
            //     'title' => 'CSS How to [Web]',
            //     'description' => 'In this lesson, we will look at how to make your web pages more attractive, controlling the design of them using CSS.',
            //     'link' => base_url('DiscussionController/topic/css_howto')
            // ],
            // [
            //     'title' => 'Introduction to CSS [PDF]',
            //     'description' => 'In this lesson, we will look at how to make your web pages more attractive, controlling the design of them using CSS.',
            //     'link' => base_url('assets/pdfjs/web/viewer.html') . '?file=' . urlencode(base_url('uploads/discussions/html_css.pdf')) . '#page=236'
            // ],
            [
                'title' => 'PHP includes',
                'description' => 'The include (or require) statement takes all the text/code/markup that exists in the specified file and copies it into the file that uses the include statement.',
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

        $cc105 =  [
            [
                'title' => 'Databases and RDBMS',
                'description' => 'Understand the fundamentals of databases and relational database management systems.',
                'link' => base_url('DiscussionController/topic/105c_rdbms')
            ],
            [
                'title' => 'Data, Information, and Metadata',
                'description' => 'Learn how the difference between data, information, and metadata works in computing systems.',
                'link' => base_url('DiscussionController/topic/105a_data')
            ],
            [
                'title' => 'Traditional vs Database Approach',
                'description' => 'Learn how the difference between traditional file processing and database approaches works in computing systems.',
                'link' => base_url('DiscussionController/topic/105b_trad_vs_dbms')
            ]
        ];
        $business_intelligence = [
             [
                'title' => 'Introduction to Business Intelligence',
                'description' => 'Understand how data is transformed into insights that support business decisions',
                'link' => base_url('DiscussionController/topic/BI_intro')
             ],
             [
                'title' => 'Decision Making',
                'description' => 'Understanding the importance and process of decision making in business intelligence',
                'link' => base_url('DiscussionController/topic/BI_decision')
            ]
        ];

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

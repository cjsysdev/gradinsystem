<?php
defined('BASEPATH') or exit('No direct script access allowed');

class class_student extends MY_Model
{
    public $table = 'class_student';
    public $primary_key = 'id';
    public $protected = array('id');

    public function __construct()
    {
        $this->timestamps = TRUE;
        $this->has_one['student_master'] =  array(
            'foreign_model' => 'student_master',
            'foreign_table' => 'student_master',
            'foreign_key' => 'trans_no',
            'local_key' => 'trans_no'
        );
        parent::__construct();
    }
}

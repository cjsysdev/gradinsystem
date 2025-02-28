<?php
defined('BASEPATH') or exit('No direct script access allowed');

class classworks extends MY_Model
{
    public $table = 'classworks';
    public $primary_key = 'classwork_id';
    public $protected = array('classwork_id');
    public $timestamps = TRUE;

    public function __construct()
    {
        $this->timestamps = TRUE;
        $this->has_many['student'] =  array(
            'foreign_model' => 'student_master',
            'foreign_table' => 'student_master',
            'foreign_key' => 'trans_no',
            'local_key' => 'student_id'
        );
        $this->has_many['assessments'] =  array(
            'foreign_model' => 'assessments',
            'foreign_table' => 'assessments',
            'foreign_key' => 'assessment_id',
            'local_key' => 'assessment_id'
        );
        parent::__construct();
    }
}

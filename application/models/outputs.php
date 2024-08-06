<?php
defined('BASEPATH') or exit('No direct script access allowed');

class outputs extends MY_Model
{
    public $table = 'outputs';
    public $primary_key = 'output_id';
    public $protected = array('output_id');
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
        $this->has_many['inputs'] =  array(
            'foreign_model' => 'inputs',
            'foreign_table' => 'inputs',
            'foreign_key' => 'input_id',
            'local_key' => 'input_id'
        );
        parent::__construct();
    }
}

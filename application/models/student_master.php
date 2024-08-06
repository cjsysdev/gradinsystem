<?php
defined('BASEPATH') or exit('No direct script access allowed');

class student_master extends MY_Model
{
    public $table = 'student_master';
    public $primary_key = 'trans_no';
    public $protected = array('trans_no');

    public function __construct()
    {
        $this->timestamps = TRUE;
        $this->has_one['accounts'] = array(
            'foreign_model' => 'accounts',
            'foreign_table' => 'accounts',
            'foreign_key' => 'student_id',
            'local_key' => 'trans_no'
        );
        parent::__construct();
    }
}

<?php
defined('BASEPATH') or exit('No direct script access allowed');

class accounts extends MY_Model
{
    public $table = 'accounts';
    public $primary_key = 'account_id';
    public $protected = array('account_id');

    public function __construct()
    {
        $this->timestamps = TRUE;
        $this->has_one['student'] =  array(
            'foreign_model' => 'student_master',
            'foreign_table' => 'student_master',
            'foreign_key' => 'trans_no',
            'local_key' => 'student_id'
        );
        parent::__construct();
    }
}

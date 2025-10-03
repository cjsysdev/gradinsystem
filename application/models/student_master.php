<?php
defined('BASEPATH') or exit('No direct script access allowed');

class student_master extends MY_Model
{
    public $table = 'student_master';
    public $primary_key = 'trans_no';
    public $protected = array('trans_no');

    public function __construct()
    {
        $this->timestamps = FALSE;
        $this->has_one['accounts'] = array(
            'foreign_model' => 'accounts',
            'foreign_table' => 'accounts',
            'foreign_key' => 'student_id',
            'local_key' => 'trans_no'
        );
        parent::__construct();
    }

    public function search_by_name($search)
    {
        $this->db->like('firstname', $search);
        $this->db->or_like('lastname', $search);
        return $this->db->get('student_master')->result_array();
    }
}

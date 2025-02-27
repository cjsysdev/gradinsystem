<?php
defined('BASEPATH') or exit('No direct script access allowed');

class assessments extends MY_Model
{
    public $table = 'assessments';
    public $primary_key = 'input_id';
    public $protected = array('input_id');
    public $timestamps = TRUE;

    public function __construct()
    {
        $this->timestamps = TRUE;
        $this->has_one['type'] =  array(
            'foreign_model' => 'io_type',
            'foreign_table' => 'io_type',
            'foreign_key' => 'iotype_id',
            'local_key' => 'iotype_id'
        );
        $this->has_one['subject'] =  array(
            'foreign_model' => 'subject',
            'foreign_table' => 'subject',
            'foreign_key' => 'subject_id',
            'local_key' => 'subject_id'
        );
        parent::__construct();
    }
}

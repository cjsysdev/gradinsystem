<?php
defined('BASEPATH') or exit('No direct script access allowed');

class io_type extends MY_Model
{
    public $table = 'io_type';
    public $primary_key = 'io_type_id';
    public $protected = array('io_type_id');
    public $timestamps = TRUE;

    public function __construct()
    {
        $this->timestamps = TRUE;
        $this->has_many['inputs'] =  array(
            'foreign_model' => 'inputs',
            'foreign_table' => 'inputs',
            'foreign_key' => 'input_id',
            'local_key' => 'input_id'
        );
        parent::__construct();
    }
}

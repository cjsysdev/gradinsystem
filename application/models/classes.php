<?php
defined('BASEPATH') or exit('No direct script access allowed');

class classes extends MY_Model
{
    public $table = 'classes';
    public $primary_key = 'class_id';
    public $protected = array('class_id');

    public function __construct()
    {
        $this->timestamps = TRUE;
        parent::__construct();
    }
}

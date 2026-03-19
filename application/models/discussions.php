<?php
defined('BASEPATH') or exit('No direct script access allowed');

class discussions extends MY_Model
{
    public $table = 'discussions';
    public $primary_key = 'id';
    public $protected = array('id');

    public function __construct()
    {
        $this->timestamps = TRUE;
        parent::__construct();
    }
}

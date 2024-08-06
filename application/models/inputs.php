<?php
defined('BASEPATH') or exit('No direct script access allowed');

class inputs extends MY_Model
{
    public $table = 'inputs';
    public $primary_key = 'input_id';
    public $protected = array('input_id');
    public $timestamps = TRUE;

    public function __construct()
    {
        parent::__construct();
    }
    
}

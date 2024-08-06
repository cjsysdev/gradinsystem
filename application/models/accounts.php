<?php
defined('BASEPATH') or exit('No direct script access allowed');

class accounts extends MY_Model
{
    public $table = 'accounts';
    public $primary_key = 'account_no';
    public $protected = array('account_no');

    public function __construct()
    {
        $this->timestamps = TRUE;
        parent::__construct();
    }
    
}

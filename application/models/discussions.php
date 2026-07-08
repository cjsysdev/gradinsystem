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

    // Student-facing fetch — hides discussions whose display_date is still
    // in the future. Rows with no display_date are always visible.
    public function get_visible_by_class($class_id)
    {
        return $this->db
            ->where('class_id', $class_id)
            ->where('(display_date IS NULL OR display_date <= NOW())', null, false)
            ->order_by('created_at', 'desc')
            ->get($this->table)
            ->result_array();
    }
}

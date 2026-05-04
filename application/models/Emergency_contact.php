<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Emergency_contact extends MY_Model
{
    public $table = 'student_emergency_contacts';
    public $primary_key = 'contact_id';
    public $protected = ['contact_id'];

    public function get_by_student($student_id)
    {
        return $this->db
            ->order_by('is_primary', 'DESC')
            ->order_by('created_at', 'ASC')
            ->get_where('student_emergency_contacts', ['student_id' => $student_id])
            ->result_array();
    }

    public function set_primary($contact_id, $student_id)
    {
        $this->db->where('student_id', $student_id)->update('student_emergency_contacts', ['is_primary' => 0]);
        $this->db->where('contact_id', $contact_id)->update('student_emergency_contacts', ['is_primary' => 1]);
    }
}

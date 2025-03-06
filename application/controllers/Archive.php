<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Main extends CI_Controller
{

    public function upload_activity()
    {
        $filename = implode('-', [$_SESSION['student_id'], $_SESSION['input_id'], $_SESSION['account_id'], $_SESSION['section']]);

        $config['upload_path']          = './uploads/outputs';
        $config['allowed_types']        = 'gif|jpg|jpeg|png';
        $config['max_size']             = 51200; // 50MB
        // $config['encrypt_name']      = TRUE; // Encrypt the file name for security
        $config['file_name']            = $filename;

        $this->upload->initialize($config);


        if (!$this->upload->do_upload('photo-upload')) {
            $error = array('error' => $this->upload->display_errors());
            redirect('output_upload');
        } else {
            $upload_data = $this->upload->data();
            $score = $this->input->post('score');

            $insert_data = [
                'student_id' => $_SESSION['student_id'],
                'input_id' => $_SESSION['input_id'],
                'score' =>  $score,
                'file_upload' => $upload_data['file_name'],
            ];

            $this->outputs->insert($insert_data);

            redirect('output_upload');
        }
    }


    public function signup_submit()
    {
        $input = $this->input->post();

        $student = [
            'student_no' => generate_random_numbers(),
            'lastname' => strtoupper($input['lastname']),
            'firstname' => strtoupper($input['firstname']),
            'gender' => $input['gender'],
            'course' => 'BSIS',
            'current_year' => '1'
        ];

        $this->db->trans_start(); // Start transaction

        try {
            $this->student_master->insert($student);
            $trans_no = ($this->student_master->where('student_no', $student['student_no'])->get()->trans_no);

            $acc_data = [
                'student_id' => $trans_no,
                'username' => $input['username'],
                'password' => $input['password']
            ];

            $this->accounts->insert($acc_data);

            $this->db->trans_complete(); // Complete the transaction

            if ($this->db->trans_status() === FALSE) {
                // If the transaction failed
                throw new Exception('Transaction failed');
            }

            $this->session->set_flashdata('success', 'Signup Successful');
            redirect();
        } catch (Exception $e) {
            $this->db->trans_rollback(); // Rollback transaction
            $this->session->set_flashdata('error', 'Signup Error');

            redirect('signup');
            if ($e->getCode() == 1062) {
                $this->session->set_flashdata('error', 'Signup Error');

                redirect('signup');
                // Handle duplicate entry error
                echo 'Error: Duplicate entry for key.';
            } else {
                $this->session->set_flashdata('error', 'Signup Error');
                redirect('signup');
                echo 'Error: ' . $e->getMessage();
            }
        }

        $this->session->set_flashdata('error', 'Signup Error');
        redirect('signup');
    }
}

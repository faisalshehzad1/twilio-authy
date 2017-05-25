<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

class Auth_model extends CI_Model
{

    function __construct()
    {
        parent::__construct();
    }

    /*
     * 
     */

    public function Authentification()
    {
        $notif = array();
        $email = $this->input->post('email');
        $password = Utils::hash('sha1', $this->input->post('password'), AUTH_SALT);

        $this->db->select('*');
        $this->db->from('users');
        $this->db->where('email', $email);
        $this->db->where('password', $password);
        $this->db->limit(1);

        $query = $this->db->get();

        if ($query->num_rows() == 1) {
            $row = $query->row();
            if ($row->is_active != 1) {
                $notif['message'] = 'Your account is disabled !';
                $notif['type'] = 'danger';
            } else {
                $sess_data = array(
                    'users_id' => $row->users_id,
                    'first_name' => $row->first_name,
                    'last_name' => $row->last_name,
                    'authy_id' => $row->authy_id,
                    'email' => $row->email
                );
                $this->session->set_userdata('logged_in', $sess_data);
                $this->update_last_login($row->users_id);
            }
        } else {
            $notif['message'] = 'Username or password incorrect !';
            $notif['type'] = 'danger';
        }

        return $notif;
    }


    /*
     * 
     */

    private function update_last_login($users_id)
    {
        $sql = "UPDATE users SET last_login = NOW() WHERE users_id=" . $this->db->escape($users_id);
        $this->db->query($sql);
    }

    /*
     * 
     */

    public function register()
    {
        $notif = array();
        $data = array(
            'first_name' => $this->input->post('first_name'),
            'last_name' => $this->input->post('last_name'),
            'email' => $this->input->post('email'),
            'password' => Utils::hash('sha1', $this->input->post('password'), AUTH_SALT),
            'is_active' => 0
        );
        $this->db->insert('users', $data);
        $users_id = $this->db->insert_id();
        if ($this->db->affected_rows() > 0) {
            return $users_id;
        } else {
            return false;
        }

    }

    /*
     * 
     */

    public function check_email($email)
    {
        $sql = "SELECT * FROM users WHERE email = " . $this->db->escape($email);
        $res = $this->db->query($sql);
        if ($res->num_rows() > 0) {
            $row = $res->row();
            return $row;
        }
        return null;
    }
	
	public function update_by_authy_id($data, $authy_id){
		$this->db->where('authy_id', $id);
        $this->db->update('users', $data);
	}

    public function update_auth($data, $id)
    {
        $this->db->where('users_id', $id);
        $this->db->update('users', $data);
    }


    public function get_all($last_id, $col)
    {
        $this->db->select('*');
        $this->db->from('users');
        $this->db->where($col, $last_id);
        $this->db->limit(1);

        $query = $this->db->get();

        if ($query->num_rows() == 1) {
            $row = $query->row();
            return $row;
        } else {
            return false;
        }
    }

}

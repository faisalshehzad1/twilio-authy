<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller
{

    private $authy_api;

    function __construct()
    {
        parent::__construct();
        Utils::no_cache();
        $this->load->library('session');
        $this->authy_api = new \Authy\AuthyApi($this->config->item('authy_key'), 'http://api.authy.com');
        if ($this->session->userdata('verified_logged_in')) {
            redirect(base_url('dashboard'));
            exit;
        }
    }

    /**
     *
     */
    public function index()
    {
        redirect(base_url('auth/login'));
    }

    /**
     *
     */
    public function login()
    {
        $data['title'] = 'Login';
        $this->load->model('auth_model');
        $this->load->helper('security');
        $this->form_validation->set_rules('email', 'Email address', 'trim|required|valid_email|xss_clean');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            /**
             * kill session
             * */
            $this->kill_all_sessions();
            $data['notif']['message'] = validation_errors();
            $data['notif']['type'] = 'danger';
            /*
             * Load view
             */
            $this->load->view('auth/includes/header', $data);
            $this->load->view('auth/includes/navbar');
            $this->load->view('auth/login');
            $this->load->view('auth/includes/footer');
        } else {
            $data['notif'] = $this->auth_model->Authentification();
            if ($data['notif']['type'] == 'danger') {
                $errors[] = $data['notif']['message'];
                $data['error_message'] = $errors;
                $data['type'] = 'danger';
                $this->session->set_flashdata('message', $data);
                redirect(base_url('/auth/login'));
            }
            if ($this->session->userdata('logged_in')) {
                redirect(base_url('auth/verify_login'));
                exit;
            }
        }
    }
	
	public function approval_update(){
		if (!$this->input->is_ajax_request()) {
            redirect(base_url('/auth/login'));
        }
        $this->load->model('auth_model');
		$current_login_id = $this->session->userdata('logged_in')['users_id'];		
		$data = $this->auth_model->get_all($current_login_id, 'users_id');		
		if($data){
			$this->auth_model->update_auth(array('authy_status' => $this->input->post('status')), $current_login_id);
			$this->session->set_userdata('verified_logged_in', '1');			
			echo 'ok';
		}else{
			echo 'error';
		}		
		
	}


    public function oneTouchLogin()
    {
        if (!$this->session->userdata('logged_in')) {
            redirect(base_url('/auth/login'));
        }

        $this->load->model('auth_model');
        $login_id = $this->session->userdata('logged_in')['users_id'];
        $login_data = $this->auth_model->get_all($login_id, 'users_id');
        if ($login_data->authy_status != 'unverified') {
            $this->auth_model->update_auth(array(
                'authy_status' => 'unverified'
            ), $login_id);
        }

        $status = $this->authy_api->userStatus($login_data->authy_id);
        if ($status->ok()) {
			$requestId = '';
            $params = array(
                'api_key' => $this->config->item('authy_key'),
                'message' => 'Request to Login to demo app',
                'details[Email]' => $login_data->email,
				'requestId' => ''
            );

            $defaults = array(
                CURLOPT_URL => "https://api.authy.com/onetouch/json/users/$login_data->authy_id/approval_requests",
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $params,
            );
            $ch = curl_init();
            curl_setopt_array($ch, $defaults);
            $output = curl_exec($ch);
            curl_close($ch);
            $json			= json_decode($output, TRUE);
            return $json;
        } else {
            foreach ($status->errors() as $field => $error) {
                $errors[] = 'Error on ' . $field . ': ' . $error;
            }
            $data['error_message'] = $errors;
            $data['type'] = 'danger';
            $this->session->set_flashdata('message', $data);
            redirect(base_url('/auth/login'));
        }

    }

	public function check_approval_request(){
		if (!$this->input->is_ajax_request()) {
            redirect(base_url('/auth/login'));
        }
		$uuid = $this->input->post('id_data');		
		$defaults = array(
            CURLOPT_URL => "http://api.authy.com/onetouch/json/approval_requests/" . $uuid . "?api_key=" . $this->config->item('authy_key'),
        );
        $ch = curl_init();
        curl_setopt_array($ch, $defaults);
        $output = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($output);
        return $json;
	}
	
	

    public function checkCallStatus()
    {        
        if (!$this->input->is_ajax_request() && $this->input->post('row_data')) {
            redirect(base_url('/auth/login'));
        }
        $uuid = $this->input->post('row_data');
        $defaults = array(
            CURLOPT_URL => "http://api.authy.com/onetouch/json/approval_requests/" . $uuid . "?api_key=" . $this->config->item('authy_key'),
        );
        $ch = curl_init();
        curl_setopt_array($ch, $defaults);
        $output = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($output);
        return $json;

    }

    /**
     *
     */
    public function verify_login()
    {
        if (!$this->session->userdata('logged_in')) {
            redirect(base_url('/auth/login'));
        }
        $data['title'] = 'Login Verification code';
        $this->load->model('auth_model');
        $this->load->helper('security');
        $login_id = $this->session->userdata('logged_in')['users_id'];
        $login_data = $this->auth_model->get_all($login_id,'users_id');
        $this->form_validation->set_rules('sms_code', 'Token Code', 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $data['notif']['cell_phone'] = '+' . $login_data->country_code . ' ' . $this->string2xxx($login_data->cellphone, 0, 5);
            $data['notif']['message'] = validation_errors();
            $data['notif']['type'] = 'danger';
            $this->load->view('auth/includes/header', $data);
            $this->load->view('auth/includes/navbar');
            $this->load->view('auth/login_factor');
            $this->load->view('auth/includes/footer');
        } else {
            $sms_code = $this->input->post('sms_code');
            $sms = $this->authy_api->verifyToken($login_data->authy_id, $sms_code);
            if ($sms->ok()) {
                //dashboard area
                $this->session->set_userdata('verified_logged_in', '1');
                redirect(base_url('dashboard'));
                exit;
            } else {
                foreach ($sms->errors() as $field => $error) {
                    $errors[] = 'Error on ' . $field . ': ' . $error;
                }
                $data['error_message'] = $errors;
                $data['type'] = 'danger';
                $this->session->set_flashdata('message', $data);
                redirect(base_url('/auth/login'));
            }
        }
    }


    public function request_token()
    {
        if (!$this->session->userdata('logged_in')) {
            redirect(base_url('/auth/login'));
        }
        $this->load->model('auth_model');
        $login_id = $this->session->userdata('logged_in')['users_id'];
        $login_data = $this->auth_model->get_all($login_id,'users_id');
        $sms = $this->authy_api->requestSms($login_data->authy_id);
        if ($sms->ok()) {
            $errors[] = 'Text message was sent.';
            $data['error_message'] = $errors;
            $data['type'] = 'success';
            $this->session->set_flashdata('message', $data);
            redirect(base_url('/auth/verify_login'));
        } else {
            foreach ($sms->errors() as $field => $error) {
                $errors[] = 'Error on ' . $field . ': ' . $error;
            }
            $data['error_message'] = $errors;
            $data['type'] = 'danger';
            $this->session->set_flashdata('message', $data);
            redirect(base_url('/auth/login'));
        }
    }

    /**
     *
     */
    public function register()
    {
        $data['title'] = 'Register';
        $this->load->model('auth_model');

        $this->load->helper('security');
        $this->form_validation->set_rules('first_name', 'First name', 'trim|required');
        $this->form_validation->set_rules('last_name', 'Last Name', 'trim|required');
        $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email|is_unique[users.email]');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');
        $this->form_validation->set_rules('confirm_password', 'Password', 'trim|required|matches[password]|min_length[6]|alpha_numeric|callback_password_check');

        if ($this->form_validation->run() == false) {
            $data['notif']['message'] = validation_errors();
            $data['notif']['type'] = 'danger';

            /**
             * kill session
             * */
            $this->kill_all_sessions();
            /**
             *
             * */
            $this->load->view('auth/includes/header', $data);
            $this->load->view('auth/includes/navbar');
            $this->load->view('auth/register');
            $this->load->view('auth/includes/footer');

        } else {
            $last_id_insert = $this->auth_model->register();
            if ($last_id_insert) {
                $user_data = array(
                    'first_name' => $this->input->post('first_name'),
                    'last_name' => $this->input->post('last_name'),
                    'email_address' => $this->input->post('email'),
                    'last_id_insert' => $last_id_insert
                );
                $this->session->set_userdata($user_data);
                redirect(base_url('auth/two_factor'));
            } else {
                //error message
                $errors[] = 'Something wrong !';
                $data['error_message'] = $errors;
                $data['type'] = 'danger';
                $this->session->set_flashdata('message', $data);
                redirect(base_url('/auth/register'));
            }
        }

        if ($this->session->userdata('logged_in')) {
            redirect(base_url('dashboard'));
            exit;
        }

    }

    /**
     *
     */
    public function two_factor()
    {
        if (!$this->session->last_id_insert) {
            redirect(base_url('/'));
        }

        if ($this->input->post('save') && $this->input->post('save') == 'yes') {
            $this->load->model('auth_model');
            $last_record = $this->session->last_id_insert;
            $row = $this->auth_model->get_all($last_record,'users_id');
            if (!empty($row)) {
                $email_address = $row->email;
                $country = $this->input->post('country-code');
                $cellphone = $this->input->post('authy-cellphone');
                $user = $this->authy_api->registerUser($email_address, $cellphone, $country);
                if ($user->ok()) {
                    $auth_id = $user->id();
                    $this->auth_model->update_auth(array(
                        'authy_id' => $auth_id,
                        'cellphone' => $cellphone,
                        'country_code' => $country,
                    ), $last_record);

                    /**
                     * sending sms to the user
                     */
                    $sms = $this->authy_api->requestSms($auth_id);
                    if ($sms->ok()) {//sms alert sent
                        redirect(base_url('/auth/verify'));
                    } else {
                        foreach ($sms->errors() as $field => $error) {
                            $errors[] = 'Error on ' . $field . ': ' . $error;
                        }
                        $data['error_message'] = $errors;
                        $data['type'] = 'danger';
                        $this->session->set_flashdata('message', $data);
                        redirect(base_url('/auth/register'));
                    }
                } else {
                    foreach ($user->errors() as $field => $error) {
                        $errors[] = 'Error on ' . $field . ': ' . $error;
                    }
                    $data['error_message'] = $errors;
                    $data['type'] = 'danger';
                    $this->session->set_flashdata('message', $data);
                    redirect(base_url('/auth/register'));
                }
            } else {
                $errors[] = 'Something wrong !';
                $data['error_message'] = $errors;
                $data['type'] = 'danger';
                $this->session->set_flashdata('message', $data);
                redirect(base_url('/auth/register'));
            }
        } else {
            $data['title'] = 'Enable Two-Factor Authentication';
            $this->load->view('auth/includes/header', $data);
            $this->load->view('auth/includes/navbar');
            $this->load->view('auth/two_factor');
            $this->load->view('auth/includes/footer');
        }
    }

    /**
     *
     */
    public function verify()
    {
        if (!$this->session->email_address) {
            redirect(base_url('/'));
        }
        $this->form_validation->set_rules('sms_code', 'SMS Code', 'trim|required');

        if ($this->form_validation->run() == false) {
            $data['notif']['message'] = validation_errors();
            $data['notif']['type'] = 'danger';
            $data['title'] = 'Authentication Code';
            $this->load->view('auth/includes/header', $data);
            $this->load->view('auth/includes/navbar');
            $this->load->view('auth/verify_code');
            $this->load->view('auth/includes/footer');
        } else {
            $this->load->model('auth_model');
            $current_id = $this->session->last_id_insert;
            $row = $this->auth_model->get_all($current_id,'users_id');
            $sms_code = $this->input->post('sms_code');
            $verified = $this->authy_api->verifyToken($row->authy_id, $sms_code);
            if ($verified->ok()) {
                $this->auth_model->update_auth(array(
                    'is_active' => 1
                ), $current_id);
                /**
                 * send user to login page with success message
                 * */
                $errors[] = 'You have been successfully registered. Please login to proceed.';
                $data['error_message'] = $errors;
                $data['type'] = 'success';
                $this->session->set_flashdata('message', $data);
                redirect(base_url('auth/login'));
                exit();
            } else {
                foreach ($verified->errors() as $field => $error) {
                    $errors[] = 'Error on ' . $field . ': ' . $error . '<br>';
                }
                $data['notif']['message'] = $errors;
                $data['notif']['type'] = 'danger';
                $data['title'] = 'Authentication Code';
                $this->load->view('auth/includes/header', $data);
                $this->load->view('auth/includes/navbar');
                $this->load->view('auth/verify_code');
                $this->load->view('auth/includes/footer');
            }
        }
    }

    /**
     *
     */
    public function forgot_password()
    {
        $data['title'] = 'Forgot password';
        $this->load->model('auth_model');
        if (count($_POST)) {
            $this->load->helper('security');
            $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');

            if ($this->form_validation->run() == false) {
                $data['notif']['message'] = validation_errors();
                $data['notif']['type'] = 'danger';
            } else {
                $result = $this->auth_model->check_email($this->input->post('email'));
                if ($result) {
                    $data['notif']['message'] = 'Implement the function to send the email';
                    $data['notif']['type'] = 'success';
                } else {
                    $data['notif']['message'] = 'This email does not exist on the system';
                    $data['notif']['type'] = 'danger';
                }
            }
        }
        /*
         * Load view
         */
        $this->load->view('auth/includes/header', $data);
        $this->load->view('auth/includes/navbar');
        $this->load->view('auth/forgot_password');
        $this->load->view('auth/includes/footer');
    }

    /*
     * Custom callback function
     */
    public function password_check($str)
    {
        if (preg_match('#[0-9]#', $str) && preg_match('#[a-zA-Z]#', $str)) {
            return true;
        }
        return false;
    }

    /**
     * @param string $string
     * @param int $first
     * @param int $last
     * @param string $rep
     * @return string
     */
    public function string2xxx($string = '', $first = 0, $last = 0, $rep = 'X')
    {
        $begin = substr($string, 0, $first);
        $middle = str_repeat($rep, strlen(substr($string, $first, $last)));
        $end = substr($string, $last);
        $stars = $begin . $middle . $end;
        return $stars;
    }


    /**
     *
     */
    public function logout()
    {
        /**
         * kill session
         * */
        $this->kill_all_sessions();

        $this->session->unset_userdata('verified_logged_in');
        $this->session->sess_destroy();
        $this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate, no-transform, max-age=0, post-check=0, pre-check=0");
        $this->output->set_header("Pragma: no-cache");
        redirect(base_url('auth/login'));
    }


    /**
     *
     */
    public function kill_all_sessions()
    {
        $array_items = array('first_name', 'last_name', 'email_address', 'last_id_insert');
        $this->session->unset_userdata($array_items);
        $this->session->sess_destroy();

    }
}
<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Forgot_password extends CI_Controller {

	function __construct() {

		parent::__construct();

		$this->load->config('auth/auth');
		$this->load->helper(array('language', 'ssl', 'url', 'form'));
        $this->load->library(array('auth/authentication', 'auth/recaptcha', 'form_validation'));
		$this->load->model('auth/account_model');
		$this->load->language(array('general', 'auth'));
	}

	function index() {

		// Enable SSL?
		maintain_ssl($this->config->item("ssl_enabled"));

		if($this->authentication->is_signed_in()) redirect('');

		// Check recaptcha
		$recaptcha_result = $this->recaptcha->check();
		
		// Store recaptcha pass in session so that users only needs to complete captcha once
		if ($recaptcha_result === TRUE) $this->session->set_userdata('forget_password_recaptcha_pass', TRUE);
		
		// Setup form validation
		//$this->form_validation->set_error_delimiters('<span class="field_error">', '</span>');
		$this->form_validation->set_rules(array(
			array('field'=>'forgot_password_username_email', 'label'=>'lang:forgot_password_username_email', 'rules'=>'trim|required')
		));

		// Run form validation
		if ($this->form_validation->run()) {

			// User has neither already passed recaptcha nor just passed recaptcha
			if ($this->session->userdata('forget_password_recaptcha_pass') != TRUE && $recaptcha_result !== TRUE) {

				$data['forgot_password_recaptcha_error'] = $this->input->post('recaptcha_response_field') ? lang('forgot_password_recaptcha_incorrect') : lang('forgot_password_recaptcha_required');
			}
			else {

				// Remove recaptcha pass
				$this->session->unset_userdata('forget_password_recaptcha_pass');
				
				// Username does not exist
				if ( ! $account = $this->account_model->get_by_username_email($this->input->post('forgot_password_username_email'))) {

					$data['forgot_password_username_email_error'] = lang('forgot_password_username_email_does_not_exist');
				}
				elseif ( ! $account->password ) {

					$data['forgot_password_username_email_error'] = lang('forgot_password_does_not_manage_password');
				}
				else {

					// Set reset datetime
					$time = $this->account_model->update_reset_sent_datetime($account->id);
					
					// Load email library
					$this->load->library('email');
					
					// Generate reset password url
					$password_reset_url = site_url('auth/reset_password?id='.$account->id.'&token='.sha1($account->id.$time.$this->config->item('password_reset_secret')));
					
					// Send reset password email
					$this->email->from($this->config->item('password_reset_email'), lang('reset_password_email_sender'));
					$this->email->to($account->email);
					$this->email->subject(lang('reset_password_email_subject'));
					$this->email->message($this->load->view('reset_password_email', array('username' => $account->username, 'password_reset_url' => anchor($password_reset_url, $password_reset_url)), TRUE));
					echo $this->load->view('reset_password_email_new', array('username' => $account->username, 'password_reset_url' => anchor($password_reset_url, $password_reset_url)), TRUE);
					@$this->email->send();
					
					// Load reset password sent view
					$this->load->view('auth/reset_password_sent', isset($data) ? $data : NULL);
					return;
				}
			}
		}

		// Load recaptcha code
		if ($this->session->userdata('forget_password_recaptcha_pass') != TRUE) 
			$data['recaptcha'] = $this->recaptcha->load($recaptcha_result, $this->config->item("ssl_enabled"));
		
		// Load forgot password view
		$this->load->view('auth/forgot_password', isset($data) ? $data : NULL);
	}
}

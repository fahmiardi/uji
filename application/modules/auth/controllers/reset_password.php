<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reset_password extends CI_Controller {

	function __construct() {

		parent::__construct();

		// Load the necessary stuff...
		$this->load->config('auth/auth');
		$this->load->helper(array('date', 'language', 'ssl', 'url'));
        $this->load->library(array('auth/authentication', 'auth/recaptcha', 'form_validation'));
		$this->load->model(array('auth/account_model'));
		$this->load->language(array('general', 'auth/auth'));
	}

	function index() {

		maintain_ssl($this->config->item("ssl_enabled"));
		
		// Redirect signed in users to homepage
		if ($this->authentication->is_signed_in()) redirect('');
		
		// Check recaptcha
		$recaptcha_result = $this->recaptcha->check();
		
		// User has not passed recaptcha
		if ($recaptcha_result !== TRUE) {

			if ($this->input->post('recaptcha_challenge_field')) {

				$data['reset_password_recaptcha_error'] = $recaptcha_result ? lang('reset_password_recaptcha_incorrect') : lang('reset_password_recaptcha_required');
			}

			// Load recaptcha code
			$data['recaptcha'] = $this->recaptcha->load($recaptcha_result, $this->config->item("ssl_enabled"));
			
			// Load reset password captcha view
			$this->load->view('reset_password_captcha', isset($data) ? $data : NULL);
			return;
		}

		// Get account by email
		if ($account = $this->account_model->get_by_id($this->input->get('id'))) {

			// Check if reset password has expired
			if (now() < (strtotime($account->resetsenton) + $this->config->item("password_reset_expiration"))) {

				// Check if token is valid
				if ($this->input->get('token') == sha1($account->id.strtotime($account->resetsenton).$this->config->item('password_reset_secret'))) {

					// Remove reset sent on datetime
					$this->account_model->remove_reset_sent_datetime($account->id);
					
					// Upon sign in, redirect to change password page
					$this->session->set_userdata('sign_in_redirect', 'account/account_password');
					
					// Run sign in routine
					$this->authentication->sign_in($account->id);
				}
			}
		}

		// Load reset password unsuccessful view
		$this->load->view('reset_password_unsuccessful', isset($data) ? $data : NULL);
	}
}

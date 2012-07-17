<?php
/*
 * Sign_in Controller
 */
class Sign_in extends CI_Controller {
	
	function __construct() {

		parent::__construct();

		// Load the necessary stuff...
		$this->load->config('auth/auth');
		$this->load->helper(array('language', 'ssl', 'url'));
		$this->load->library(array('auth/authentication', 'connect/facebook_lib', 'auth/recaptcha', 'form_validation'));
		$this->load->model(array('auth/account_model', 'connect/account_facebook_model'));
		$this->load->language('auth');
	}

	function index() {

		// Enable SSL?
		maintain_ssl($this->config->item("ssl_enabled"));
		
		// Redirect signed in users to homepage
		if ($this->authentication->is_signed_in()) redirect('');
		
		// Set default recaptcha pass
		$recaptcha_pass = $this->session->userdata('sign_in_failed_attempts') < $this->config->item('sign_in_recaptcha_offset') ? TRUE : FALSE;
		
		// Check recaptcha
		$recaptcha_result = $this->recaptcha->check();
		
		// Setup form validation
		//$this->form_validation->set_error_delimiters('<div class="errors">', '</div>');
		$this->form_validation->set_rules(array(
			array('field'=>'sign_in_username_email', 'label'=>lang('sign_in_username_email'), 'rules'=>'trim|required', 'class'=>'text'),
			array('field'=>'sign_in_password', 'label'=>lang('sign_in_password'), 'rules'=>'trim|required', 'class'=>'text')
		));
		
		// Run form validation
		if ($this->form_validation->run() === TRUE) {

			// Get user by username / email
			if( ! $user = $this->account_model->get_by_username_email($this->input->post('sign_in_username_email')) ) {

				// Username / email doesn't exist
				$data['sign_in_username_email_error'] = lang('sign_in_username_email_does_not_exist');
			}
			else {

				// Either don't need to pass recaptcha or just passed recaptcha
				if ( ! ($recaptcha_pass === TRUE || $recaptcha_result === TRUE) && $this->config->item("sign_in_recaptcha_enabled") === TRUE) {
					
					$data['sign_in_recaptcha_error'] = $this->input->post('recaptcha_response_field') ? lang('sign_in_recaptcha_incorrect') : lang('sign_in_recaptcha_required');
				}
				else {

					// Check password
					if ( ! $this->authentication->check_password($user->password, $this->input->post('sign_in_password'))) {

						// Increment sign in failed attempts
						$this->session->set_userdata('sign_in_failed_attempts', (int)$this->session->userdata('sign_in_failed_attempts')+1);
						
						$data['sign_in_error'] = lang('sign_in_combination_incorrect');
					}
					else {
	
						// Clear sign in fail counter
						$this->session->unset_userdata('sign_in_failed_attempts');
						
						// Run sign in routine
						$this->authentication->sign_in($user->id, $this->input->post('sign_in_remember'));
						
						// Check if user is signed in on facebook
						if ($this->facebook_lib->user) {
							
							$user_fb = $this->account_facebook_model->get_by_facebook_id($this->facebook_lib->user['id']);
							
							//check session aktif fb
							if($user_fb->account_id === $this->session->userdata('account_id'))						
								//post to wall facebook 'thank you for login back'					
								$this->facebook_lib->post_feed('http://dev.lofable.com/ujian', $this->facebook_lib->user['first_name'].', Thank you for your login back' );
						}
						
						redirect('');
					}
				}
			}
		}

		// Load recaptcha code
		if ($this->config->item("sign_in_recaptcha_enabled") === TRUE) 
			if ($this->config->item('sign_in_recaptcha_offset') <= $this->session->userdata('sign_in_failed_attempts')) 
				$data['recaptcha'] = $this->recaptcha->load($recaptcha_result, $this->config->item("ssl_enabled"));
		
		// Load sign in view
		$this->load->view('sign_in', isset($data) ? $data : NULL);
	}
}

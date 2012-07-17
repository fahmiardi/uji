<?php

class Sign_up extends CI_Controller {

	function __construct() {

		parent::__construct();
		
		// Load the necessary stuff...
		$this->load->config('auth/auth');
		$this->load->helper(array('language', 'ssl', 'url'));
       	$this->load->library(array('auth/authentication', 'connect/facebook_lib', 'connect/twitter_lib', 'auth/recaptcha', 'form_validation'));
		$this->load->model(array('auth/account_model', 'auth/account_details_model', 'connect/account_facebook_model', 'connect/account_twitter_model', 'connect/account_openid_model'));
		$this->load->language(array('auth/auth'));
	}

	function index() {
			
		// Enable SSL?
		maintain_ssl($this->config->item("ssl_enabled"));
		
		if($this->session->userdata('sign_client')) $this->session->unset_userdata('sign_client');
		
		// Redirect signed in users to homepage
		if ($this->authentication->is_signed_in()) redirect('');
		
		// Redirect user to home if 'connect_create' session data doesn't exist
		if ( $this->session->userdata('account_linking') ) $data['account_linking'] = $this->session->userdata('account_linking');
		
		// Check recaptcha
		$recaptcha_result = $this->recaptcha->check();
		
		// Store recaptcha pass in session so that users only needs to complete captcha once
		if ($recaptcha_result === TRUE) $this->session->set_userdata('sign_up_recaptcha_pass', TRUE);
		
		// Setup form validation
		//$this->form_validation->set_error_delimiters('<div class="errors"><p>', '</p></div>');
		$this->form_validation->set_rules(array(
			array('field'=>'sign_up_username', 'label'=>'lang:sign_up_username', 'rules'=>'trim|required|alpha_dash|min_length[2]|max_length[24]'),
			array('field'=>'sign_up_first_name', 'label'=>'lang:sign_up_first_name', 'rules'=>'trim|required'),
			array('field'=>'sign_up_last_name', 'label'=>'lang:sign_up_last_name', 'rules'=>'trim|required'),
			array('field'=>'sign_up_password', 'label'=>'lang:sign_up_password', 'rules'=>'trim|required|min_length[6]'),
			array('field'=>'sign_up_email', 'label'=>'lang:sign_up_email', 'rules'=>'trim|required|valid_email|max_length[160]')
		));
		
		// Run form validation
		if ($this->form_validation->run() === TRUE) {
			
			// Check if user name is taken
			if ($this->username_check($this->input->post('sign_up_username')) === TRUE) {
				$data['sign_up_username_error'] = lang('sign_up_username_taken');
			}
			// Check if email already exist
			elseif ($this->email_check($this->input->post('sign_up_email')) === TRUE) {
				$data['sign_up_email_error'] = lang('sign_up_email_exist');
			}
			// Either already pass recaptcha or just passed recaptcha
			elseif ( ! ($this->session->userdata('sign_up_recaptcha_pass') == TRUE || $recaptcha_result === TRUE) && $this->config->item("sign_up_recaptcha_enabled") === TRUE) {
				$data['sign_up_recaptcha_error'] = $this->input->post('recaptcha_response_field') ? lang('sign_up_recaptcha_incorrect') : lang('sign_up_recaptcha_required');
			}
			else {
				// Remove recaptcha pass
				$this->session->unset_userdata('sign_up_recaptcha_pass');
				
				// Destroy 'connect_create' session data
				$this->session->unset_userdata('account_linking');
				
				// Create user
				$user_id = $this->account_model->create($this->input->post('sign_up_username'), $this->input->post('sign_up_email'), $this->input->post('sign_up_password'));
				
				$empty=null;
				
				// Add user details (auto detected country, language, timezone)
				$attributes = array(
									'fullname' => isset($data['account_linking'][1]['fullname']) ? $data['account_linking'][1]['fullname'] : $empty,
									'firstname' => $this->input->post('sign_up_first_name'),
									'lastname' => $this->input->post('sign_up_last_name'),
									'gender' => isset($data['account_linking'][1]['gender']) ? $data['account_linking'][1]['gender'] : $empty,
									'dateofbirth' => isset($data['account_linking'][1]['dateofbirth']) ? $data['account_linking'][1]['dateofbirth'] : $empty,
									'picture' => isset($data['account_linking'][1]['picture']) ? $data['account_linking'][1]['picture'] : $empty,
									'postalcode' => isset($data['account_linking'][1]['postalcode']) ? $data['account_linking'][1]['postalcode'] : $empty,
									'country' => isset($data['account_linking'][1]['country']) ? $data['account_linking'][1]['country'] : $empty,
									'language' => isset($data['account_linking'][1]['language']) ? $data['account_linking'][1]['language'] : $empty,
									'timezone' => isset($data['account_linking'][1]['timezone']) ? $data['account_linking'][1]['timezone'] : $empty
								);
				$this->account_details_model->update($user_id, $attributes);
				
				if(isset($data['account_linking'])) {
					// Connect third party account to user
					switch($data['account_linking'][0]['provider'])
					{
						case 'facebook': 
							
							$this->account_facebook_model->insert($user_id, $data['account_linking'][0]['provider_id']); 
							
							// Check if user is signed in on facebook
							if ($this->facebook_lib->user) {
								
								//post to wall facebook 'thank you for join'					
								$this->facebook_lib->post_feed('http://dev.lofable.com/ujian/auth/sign_up', $this->facebook_lib->user['first_name'].' joined in this website. Thank you.' );
							}
							break;
						case 'twitter': 
							
							
							$this->account_twitter_model->insert($user_id, $data['account_linking'][0]['provider_id'], $data['account_linking'][0]['token'], $data['account_linking'][0]['secret']); 
							
							try {

								// Perform token exchange
								$this->twitter_lib->etw->setToken($data['account_linking'][0]['token'], $data['account_linking'][0]['secret']);								
							}
							catch (Exception $e) {

								$this->authentication->is_signed_in() ?
									redirect('account/account_linked') :
										redirect('auth/sign_up');
							}
							
							//post tweet 'thank you for join'
							$this->twitter_lib->post_tweet('I joined with http://dev.lofable.com/ujian');
							
							break;
						case 'openid': 
							$this->account_openid_model->insert($data['account_linking'][0]['provider_id'], $user_id); 
							break;
					}
				}
				
				// Auto sign in after sign up
				if ($this->config->item("sign_up_auto_sign_in")) {
					
					//valid redirect
					$this->session->set_userdata('sign_in_redirect','auth/sign_in');
					
					// Run sign in routine
					$this->authentication->sign_in($user_id);
				}
				
				redirect('auth/sign_in');
			}
		}

		// Load recaptcha code
		if ($this->config->item("sign_up_recaptcha_enabled") === TRUE)
			if ($this->session->userdata('sign_up_recaptcha_pass') != TRUE) 
				$data['recaptcha'] = $this->recaptcha->load($recaptcha_result, $this->config->item("ssl_enabled"));
		
		// Load sign up view
		$this->load->view('sign_up', isset($data) ? $data : NULL);
	}

	function username_check($username) {

		return $this->account_model->get_by_username($username) ? TRUE : FALSE;
	}
	
	//live check availablity username
	function check_username() {		
		
		if($this->input->post('uname') =="") {
			echo true;
			break;
		}
		if($this->username_check($this->input->post('uname'))) {
			echo true;
			break;
		}
		echo false;
	}

	function email_check($email) {

		return $this->account_model->get_by_email($email) ? TRUE : FALSE;
	}
}

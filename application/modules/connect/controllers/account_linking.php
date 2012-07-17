<?php

class Account_linking extends CI_Controller {

	function __construct() {

		parent::__construct();

		// Load the necessary stuff...
		$this->load->config('auth/auth');
		$this->load->helper(array('language', 'ssl', 'url'));
        $this->load->library(array('auth/authentication', 'connect/facebook_lib', 'connect/twitter_lib', 'auth/recaptcha', 'form_validation'));
		$this->load->model(array('auth/account_model', 'auth/account_details_model', 'connect/account_facebook_model', 'connect/account_twitter_model', 'connect/account_openid_model'));
		$this->load->language(array('connect/connect'));
	}

	function index() {

		// Enable SSL?
		maintain_ssl($this->config->item("ssl_enabled"));
		
		if($this->session->userdata('sign_client')) $this->session->unset_userdata('sign_client');
		
		// Redirect user to home if 'connect_create' session data doesn't exist
		if ( ! $data['account_linking'] = $this->session->userdata('account_linking')) redirect('auth/sign_in');
		
		//pull config provider
		$providers = $this->config->item($data['account_linking'][0]['provider'],'social_media_providers');
		
		// Check if config sign up third party is enabled
		if($this->config->item('social_media_enabled')===TRUE && $providers['enabled']===TRUE) {
						
			// Set default recaptcha pass
			$recaptcha_pass = $this->session->userdata('connect_linking_failed_attempts') < $this->config->item('sign_in_recaptcha_offset') ? TRUE : FALSE;
			
			// Check recaptcha
			$recaptcha_result = $this->recaptcha->check();
		
			// Setup form validation
			$this->form_validation->set_error_delimiters('<span class="field_error">', '</span>');
			$this->form_validation->set_rules(array(
				array('field'=>'connect_linking_username_email', 'label'=>'lang:connect_linking_username_email', 'rules'=>'trim|required|alpha_numeric|min_length[2]|max_length[16]'),
				array('field'=>'connect_linking_password', 'label'=>'lang:connect_linking_password', 'rules'=>'trim|required|min_length[6]'),
			));
		
			// Run form validation
			if ($this->form_validation->run() === TRUE) {		
					
				// Get user by username / email
				if ( ! $user_id = $this->account_model->get_by_username_email($this->input->post('connect_linking_username_email')) ) {
					
					// Username / email doesn't exist
					$data['connect_linking_username_email_error'] = lang('connect_linking_username_email_does_not_exist');
				}
				else {
					
					// Either don't need to pass recaptcha or just passed recaptcha
					if ( ! ($recaptcha_pass === TRUE || $recaptcha_result === TRUE) && $this->config->item("sign_in_recaptcha_enabled") === TRUE) {
						
						$data['conect_linking_recaptcha_error'] = $this->input->post('recaptcha_response_field') ? lang('connect_linking_recaptcha_incorrect') : lang('connect_linking_recaptcha_required');
					}
					else {

						// Check password
						if ( ! $this->authentication->check_password($user_id->password, $this->input->post('connect_linking_password'))) {

							// Increment sign in failed attempts
							$this->session->set_userdata('connect_linking_failed_attempts', (int)$this->session->userdata('connect_linking_failed_attempts')+1);
							
							$data['connect_linking_error'] = lang('connect_linking_combination_incorrect');
						}
						else {
		
							// Clear sign in fail counter
							$this->session->unset_userdata('connect_linking_failed_attempts');
							
							// Destroy 'connect_create' session data
							$this->session->unset_userdata('account_linking');
														
							// Connect third party account to user
							switch($data['account_linking'][0]['provider'])
							{
								case 'facebook': 
									
									//insert facebook account
									$this->account_facebook_model->insert($user_id->id, $data['account_linking'][0]['provider_id']);
									
									// Check if user is signed in on facebook
									if ($this->facebook_lib->user) {
										
										//post to wall facebook 'was linked'					
										$this->facebook_lib->post_feed('http://dev.lofable.com/ujian/auth/sign_in', $this->facebook_lib->user['first_name'].' was linked account with '.$data['account_linking'][0]['provider'] );
									}
									break;
								case 'twitter': 
									
									//insert twitter account
									$this->account_twitter_model->insert($user_id->id, $data['account_linking'][0]['provider_id'], $data['account_linking'][0]['token'], $data['account_linking'][0]['secret']); 
									
									try {

										// Perform token exchange
										$this->twitter_lib->etw->setToken($data['account_linking'][0]['token'], $data['account_linking'][0]['secret']);
									}
									catch (Exception $e) {

										$this->authentication->is_signed_in() ?
											redirect('account/account_linked') :
												redirect('auth/sign_up');
									}
									
									$this->twitter_lib->post_tweet('I linked twitter with http://dev.lofable.com/ujian');

									break;
								case 'openid': case 'google': case 'yahoo': 
								
									$this->account_openid_model->insert($data['account_linking'][0]['provider_id'], $user_id->id); 
									break;
							}
							
							$this->session->set_userdata('sign_in_redirect', 'account/account_linked');
							
							// Run sign in routine
							$this->authentication->sign_in($user_id->id, $this->input->post('connect_linking_remember'));
						}
					}
				}
			}
			
			// Load recaptcha code
			if ($this->config->item("sign_in_recaptcha_enabled") === TRUE) 
				if ($this->config->item('sign_in_recaptcha_offset') <= $this->session->userdata('connect_linking_failed_attempts')) 
					$data['recaptcha'] = $this->recaptcha->load($recaptcha_result, $this->config->item("ssl_enabled"));

			$this->load->view('connect/account_linking', isset($data) ? $data : NULL);
		}
		else {
			
			// Destroy 'connect_create' session data
			$this->session->unset_userdata('account_linking');
			
			redirect('auth/sign_in');
		}
	}

	function username_check($username) {

		return $this->account_model->get_by_username($username) ? TRUE : FALSE;
	}

	function email_check($email) {

		return $this->account_model->get_by_email($email) ? TRUE : FALSE;
	}
}

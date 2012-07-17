<?php

class Twitter extends CI_Controller {

	function __construct() {

		parent::__construct();
		
		// Load the necessary stuff...
		$this->load->config('auth/auth');
		$this->load->helper(array('language', 'ssl', 'url'));
        $this->load->library(array('auth/authentication', 'connect/twitter_lib'));
		$this->load->model(array('connect/account_twitter_model'));
		$this->load->language(array('connect/connect'));
	}

	function index() {
	
		// Enable SSL?
		maintain_ssl($this->config->item("ssl_enabled"));

		$providers = $this->config->item('twitter','social_media_providers');
		
		// Check if config third party and provider are enabled
		if($this->config->item('social_media_enabled')===TRUE && $providers['enabled']===TRUE ) {
			
			//check client
			if($this->input->get('client')) {
				$this->session->set_userdata('sign_client', $this->input->get('client'));
			}
			
			$sign_client = $this->session->userdata('sign_client');
			
			if ($this->input->get('oauth_token')) {

				try {

					// Perform token exchange
					//$this->twitter_lib->etw->setCallback('http://dev.lofable.com/connect/twitter');
					$this->twitter_lib->etw->setToken($this->input->get('oauth_token'));
					$twitter_token = $this->twitter_lib->etw->getAccessToken();
					$this->twitter_lib->etw->setToken($twitter_token->oauth_token, $twitter_token->oauth_token_secret);
					
					// Get account credentials
					$twitter_info = $this->twitter_lib->etw->get_accountVerify_credentials()->response;
				}
				catch (Exception $e) {

					$this->authentication->is_signed_in() ?
						redirect('account/account_linked') :
							redirect('auth/sign_up');
				}

				// Check if user has connect twitter to a3m
				if ($user = $this->account_twitter_model->get_by_twitter_id($twitter_info['id'])) {

					// Check if user is not signed in on a3m
					if ( ! $this->authentication->is_signed_in()) {

						// Run sign in routine
						$this->authentication->sign_in($user->account_id);
					}
					
					//post to twitter 'thank you for login via etwApps'
					$this->twitter_lib->post_tweet('I logged on http://dev.lofabel.com/ujian');
					
					$user->account_id === $this->session->userdata('account_id') ?
						$this->session->set_flashdata('linked_error', sprintf(lang('linked_linked_with_this_account'), lang('connect_twitter'))) :
							$this->session->set_flashdata('linked_error', sprintf(lang('linked_linked_with_another_account'), lang('connect_twitter')));
					
					redirect('account/account_linked');
				}
				// The user has not connect twitter to a3m
				else {

					// Check if user is signed in on a3m
					if ( ! $this->authentication->is_signed_in()) {
						
						// Store user's twitter data in session
						$this->session->set_userdata('account_linking', array(
							array(
								'provider' => 'twitter', 
								'provider_id' => $twitter_info['id'],
								//'username' => $twitter_info['screen_name'],
								'token' => $twitter_token->oauth_token,
								'secret' => $twitter_token->oauth_token_secret
							), 
							array(
								'fullname' => $twitter_info['name'],
								'picture' => $twitter_info['profile_image_url']
							)
						));
						
						if($sign_client == 'sign_in') {
							
							//login and linking account
							redirect('connect/account_linking');
						}
						elseif($sign_client == 'sign_up') {
							
							//register account
							redirect('auth/sign_up');
						}
						else {
							
							redirect('auth/sign_in');
						}	
					}
					else {

						// Connect twitter to a3m
						$this->account_twitter_model->insert($this->session->userdata('account_id'), $twitter_info['id'], $twitter_token->oauth_token, $twitter_token->oauth_token_secret);
						$this->session->set_flashdata('linked_info', sprintf(lang('linked_linked_with_your_account'), lang('connect_twitter')));

						redirect('account/account_linked');
					}
				}
			}
			
			
			
			//$params['x_auth_access_type'] = 'write';
			
			// Redirect to authorize url
			header("Location: ".$this->twitter_lib->etw->getAuthenticateUrl());
		}
		else {

			redirect('auth/sign_in');
		}
	}
}
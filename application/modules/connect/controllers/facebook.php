<?php

class Facebook extends CI_Controller {

	function __construct() {

		parent::__construct();

		// Load the necessary stuff...
		$this->load->config('auth/auth');
		$this->load->helper(array('language', 'ssl', 'url'));
        $this->load->library(array('auth/authentication', 'connect/facebook_lib'));
		$this->load->model(array('connect/account_facebook_model'));
		$this->load->language(array('connect/connect'));
	}

	function index() {

		// Enable SSL?
		maintain_ssl($this->config->item("ssl_enabled"));

		$providers = $this->config->item('facebook','social_media_providers');
		
		// Check if config third party and provider are enabled
		if($this->config->item('social_media_enabled')===TRUE && $providers['enabled']===TRUE ) {
			
			//check client
			if($this->input->get('client')) {
				$this->session->set_userdata('sign_client', $this->input->get('client'));
			}
			
			$sign_client = $this->session->userdata('sign_client');
			
			// Check if user is signed in on facebook
			if ($this->facebook_lib->user) {

				// Check if user has connect facebook to a3m
				if ($user = $this->account_facebook_model->get_by_facebook_id($this->facebook_lib->user['id'])) {

					// Check if user is not signed in on a3m
					if ( ! $this->authentication->is_signed_in()) {

						// Run sign in routine
						$this->authentication->sign_in($user->account_id);
					}
					
					//post to wall facebook 'thank you for login back via fbApps'					
					$this->facebook_lib->post_feed('http://dev.lofable.com/ujian', $this->facebook_lib->user['first_name'].', Thank you for your login back via fbApps' );
					
					$user->account_id === $this->session->userdata('account_id') ?
						$this->session->set_flashdata('linked_error', sprintf(lang('linked_linked_with_this_account'), lang('connect_facebook'))) :
							$this->session->set_flashdata('linked_error', sprintf(lang('linked_linked_with_another_account'), lang('connect_facebook')));
					
					redirect('account/account_linked');
				}
				// The user has not connect facebook to a3m
				else {

					// Check if user is not signed in on a3m
					if ( ! $this->authentication->is_signed_in()) {

						// Store user's facebook data in session
						$this->session->set_userdata('account_linking', array(
							array(
								'provider' => 'facebook', 
								'provider_id' => $this->facebook_lib->user['id']
							), 
							array(
								//'fullname' => $this->facebook_lib->user['name'],
								'firstname' => $this->facebook_lib->user['first_name'],
								'lastname' => $this->facebook_lib->user['last_name'],
								'gender' => $this->facebook_lib->user['gender'],
								//'dateofbirth' => $this->facebook_lib->user['birthday'],
								'picture' => 'http://graph.facebook.com/'.$this->facebook_lib->user['id'].'/picture/?type=large'
								// $this->facebook_lib->user['link']
								// $this->facebook_lib->user['bio']
								// $this->facebook_lib->user['timezone']
								// $this->facebook_lib->user['locale']
								// $this->facebook_lib->user['verified']
								// $this->facebook_lib->user['updated_time']
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

						// Connect facebook to a3m
						$this->account_facebook_model->insert($this->session->userdata('account_id'), $this->facebook_lib->user['id']);
						$this->session->set_flashdata('linked_info', sprintf(lang('linked_linked_with_your_account'), lang('connect_facebook')));
						
						redirect('account/account_linked');
					}
				}
			}

			// Redirect to login url
			header("Location: ".$this->facebook_lib->fb->getLoginUrl(array('scope' => 'publish_stream', 'req_perms' => 'user_birthday')));
		}
		else {

			redirect('auth/sign_in');
		}
	}
}

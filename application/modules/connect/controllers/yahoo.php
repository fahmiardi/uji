<?php

class Yahoo extends CI_Controller {

	function __construct() {
	
		parent::__construct();
		
		// Load the necessary stuff...
		$this->load->config('auth/auth');
		$this->load->helper(array('language', 'ssl', 'url', 'connect/openid'));
        $this->load->library(array('auth/authentication'));
		$this->load->model(array('connect/account_openid_model'));
		$this->load->language(array('connect/connect'));
	}
	
	function index() {
	
		// Enable SSL?
		maintain_ssl($this->config->item("ssl_enabled"));
		
		$providers = $this->config->item('yahoo','social_media_providers');
		
		// Check if config third party and provider are enabled
		if($this->config->item('social_media_enabled')===TRUE && $providers['enabled']===TRUE ) {
			
			//check client
			if($this->input->get('client')) {
				$this->session->set_userdata('sign_client', $this->input->get('client'));
			}
			
			$sign_client = $this->session->userdata('sign_client');
		
			$this->load->config('connect/connect');
			
			// Get OpenID store object
			$store = new Auth_OpenID_FileStore($this->config->item("openid_file_store_path"));
			
			// Get OpenID consumer object
			$consumer = new Auth_OpenID_Consumer($store);
			
			if ($this->input->get('janrain_nonce')) {
			
				// Complete authentication process using server response
				$response = $consumer->complete(site_url('connect/yahoo'));
				
				// Check the response status
				if ($response->status == Auth_OpenID_SUCCESS) {
				
					// Check if user has connect yahoo to a3m
					if ($user = $this->account_openid_model->get_by_openid($response->getDisplayIdentifier())) {
					
						// Check if user is not signed in on a3m
						if ( ! $this->authentication->is_signed_in()) {
						
							// Run sign in routine
							$this->authentication->sign_in($user->account_id);
						}
						
						$user->account_id === $this->session->userdata('account_id') ?
							$this->session->set_flashdata('linked_error', sprintf(lang('linked_linked_with_this_account'), lang('connect_yahoo'))) :
								$this->session->set_flashdata('linked_error', sprintf(lang('linked_linked_with_another_account'), lang('connect_yahoo')));
						
						redirect('account/account_linked');
					}
					// The user has not connect yahoo to a3m
					else {
					
						// Check if user is signed in on a3m
						if ( ! $this->authentication->is_signed_in()) {
						
							$openid_yahoo = array();
					
							if ($ax_args = Auth_OpenID_AX_FetchResponse::fromSuccessResponse($response)) {
							
								$ax_args = $ax_args->data;
								//if (isset($ax_args['http://axschema.org/namePerson/friendly'][0])) $username = $ax_args['http://axschema.org/namePerson/friendly'][0];
								//if (isset($ax_args['http://axschema.org/contact/email'][0])) $email = $ax_args['http://axschema.org/contact/email'][0];
								if (isset($ax_args['http://axschema.org/namePerson'][0])) $openid_yahoo['fullname'] = $ax_args['http://axschema.org/namePerson'][0];
								if (isset($ax_args['http://axschema.org/birthDate'][0])) $openid_yahoo['dateofbirth'] = $ax_args['http://axschema.org/birthDate'][0];
								if (isset($ax_args['http://axschema.org/person/gender'][0])) $openid_yahoo['gender'] = $ax_args['http://axschema.org/person/gender'][0];
								if (isset($ax_args['http://axschema.org/contact/postalCode/home'][0])) $openid_yahoo['postalcode'] = $ax_args['http://axschema.org/contact/postalCode/home'][0];
								if (isset($ax_args['http://axschema.org/contact/country/home'][0])) $openid_yahoo['country'] = $ax_args['http://axschema.org/contact/country/home'][0];
								if (isset($ax_args['http://axschema.org/pref/language'][0])) $openid_yahoo['language'] = $ax_args['http://axschema.org/pref/language'][0];
								if (isset($ax_args['http://axschema.org/pref/timezone'][0])) $openid_yahoo['timezone'] = $ax_args['http://axschema.org/pref/timezone'][0];
								if (isset($ax_args['http://axschema.org/media/image/default'][0])) $openid_yahoo['picture'] = $ax_args['http://axschema.org/media/image/default'][0]; // yahoo only
							}
							
							// Store user's twitter data in session
							$this->session->set_userdata('account_linking', array(
								array(
									'provider' => 'yahoo', 
									'provider_id' => $response->getDisplayIdentifier(),
									//'username' => isset($username) ? $username : NULL,
									//'email' => isset($email) ? $email : NULL
								), $openid_yahoo
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
						
							// Connect yahoo to a3m
							$this->account_openid_model->insert($response->getDisplayIdentifier(), $this->session->userdata('account_id'));
							$this->session->set_flashdata('linked_info', sprintf(lang('linked_linked_with_your_account'), lang('connect_yahoo')));
							redirect('account/account_linked');
						}
					}
				}
				// Auth_OpenID_CANCEL or Auth_OpenID_FAILURE or anything else
				else {
				
					$this->authentication->is_signed_in() ?
						redirect('account/account_linked') :
							redirect('auth/sign_up');
				}
			}
			
			// Begin OpenID authentication process
			$auth_request = $consumer->begin($this->config->item("openid_yahoo_discovery_endpoint"));
			
			// Create ax request (Attribute Exchange)
			$ax_request = new Auth_OpenID_AX_FetchRequest;
			//$ax_request->add(Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson/friendly', 1, TRUE, 'username'));
			//$ax_request->add(Auth_OpenID_AX_AttrInfo::make('http://axschema.org/contact/email', 1, TRUE, 'email'));
			$ax_request->add(Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson', 1, TRUE, 'fullname'));
			$ax_request->add(Auth_OpenID_AX_AttrInfo::make('http://axschema.org/birthDate', 1, TRUE, 'dateofbirth'));
			$ax_request->add(Auth_OpenID_AX_AttrInfo::make('http://axschema.org/person/gender', 1, TRUE, 'gender'));
			$ax_request->add(Auth_OpenID_AX_AttrInfo::make('http://axschema.org/contact/postalCode/home', 1, TRUE, 'postalcode'));
			$ax_request->add(Auth_OpenID_AX_AttrInfo::make('http://axschema.org/contact/country/home', 1, TRUE, 'country'));
			$ax_request->add(Auth_OpenID_AX_AttrInfo::make('http://axschema.org/pref/language', 1, TRUE, 'language'));
			$ax_request->add(Auth_OpenID_AX_AttrInfo::make('http://axschema.org/pref/timezone', 1, TRUE, 'timezone'));
			$ax_request->add(Auth_OpenID_AX_AttrInfo::make('http://axschema.org/media/image/default', 1, TRUE, 'picture')); // yahoo only
			$auth_request->addExtension($ax_request);
			
			// Redirect to authorizate URL
			header("Location: ".$auth_request->redirectURL(base_url(), site_url('connect/yahoo')));
		}
		else {
		
			redirect('auth/sign_in');
		}
	}
}
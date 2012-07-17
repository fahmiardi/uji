<?php
class Openid extends CI_Controller {

	function __construct() {
	
		parent::__construct();

		// Load the necessary stuff...
		$this->load->config('auth/auth');
		$this->load->helper(array('language', 'ssl', 'url', 'connect/openid'));
        $this->load->library(array('auth/authentication', 'form_validation'));
		$this->load->model(array('auth/account_model', 'connect/account_openid_model'));
		$this->load->language(array('connect/connect'));
	}

	

	function index() {

		// Enable SSL?
		maintain_ssl($this->config->item("ssl_enabled"));		

		$providers = $this->config->item('openid','social_media_providers');
		
		// Check if config third party and provider are enabled
		if($this->config->item('social_media_enabled')===TRUE && $providers['enabled']===TRUE ) {
			
			//check client
			if($this->input->get('client')) {
				$this->session->set_userdata('sign_client', $this->input->get('client'));
			}
			
			$sign_client = $this->session->userdata('sign_client');
		
			$this->load->config('connect/connect');
			
			// Retrieve sign in user
			if ($this->authentication->is_signed_in()) {
			
				$data['account'] = $this->account_model->get_by_id($this->session->userdata('account_id'));
			}

			//$data['account_details'] = $this->account_details_model->get_by_account_id($this->session->userdata('account_id'));
			
			// Get OpenID store object
			$store = new Auth_OpenID_FileStore($this->config->item("openid_file_store_path"));			

			// Get OpenID consumer object
			$consumer = new Auth_OpenID_Consumer($store);			

			if ($this->input->get('janrain_nonce')) {		

				// Complete authentication process using server response
				$response = $consumer->complete(site_url('connect/openid'));				

				// Check the response status
				if ($response->status == Auth_OpenID_SUCCESS) {				

					// Check if user has connect the openid to a3m
					if ($user = $this->account_openid_model->get_by_openid($response->getDisplayIdentifier())) {					

						// Check if user is not signed in on a3m
						if ( ! $this->authentication->is_signed_in()) {						

							// Run sign in routine
							$this->authentication->sign_in($user->account_id);
						}						

						$user->account_id === $this->session->userdata('account_id') ?
							$this->session->set_flashdata('linked_error', sprintf(lang('linked_linked_with_this_account'), lang('connect_openid'))) :
								$this->session->set_flashdata('linked_error', sprintf(lang('linked_linked_with_another_account'), lang('connect_openid')));				

						redirect('account/account_linked');
					}

					// The user has not connect openid to a3m
					else {
					
						// Check if user is signed in on a3m
						if ( ! $this->authentication->is_signed_in()) {						

							$openid_all = array();							

							// Extract Simple Registration data
							if ($sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response)) {							

								$sreg = $sreg_resp->contents();

								//if (isset($sreg['nickname'])) $username = $sreg['nickname'];
								//if (isset($sreg['email'])) $email = $sreg['email'];
								if (isset($sreg['fullname'])) $openid_all['fullname'] = $sreg['fullname'];
								if (isset($sreg['firstname'])) $openid_all['firstname'] = $sreg['firstname'];
								if (isset($sreg['lastname'])) $openid_all['lastname'] = $sreg['lastname'];
								if (isset($sreg['gender'])) $openid_all['gender'] = $sreg['gender'];
								//if (isset($sreg['dob'])) $openid_all['dateofbirth'] = $sreg['dob'];
								if (isset($sreg['postcode'])) $openid_all['postalcode'] = $sreg['postcode'];
								if (isset($sreg['country'])) $openid_all['country'] = $sreg['country'];
								if (isset($sreg['language'])) $openid_all['language'] = $sreg['language'];
								if (isset($sreg['timezone'])) $openid_all['timezone'] = $sreg['timezone'];
							}
							else {
								exit();
							}

							// Store user's twitter data in session
							$this->session->set_userdata('account_linking', array(

								array(

									'provider' => 'openid', 
									'provider_id' => $response->getDisplayIdentifier(),
									//'username' => isset($username) ? $username : NULL,
									//'email' => isset($email) ? $email : NULL
								), $openid_all

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

							// Connect openid to a3m
							$this->account_openid_model->insert($response->getDisplayIdentifier(), $this->session->userdata('account_id'));
							$this->session->set_flashdata('linked_info', sprintf(lang('linked_linked_with_your_account'), lang('connect_openid')));

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

			// Setup form validation
			$this->form_validation->set_error_delimiters('<span class="field_error">', '</span>');
			$this->form_validation->set_rules(array(
				array('field'=>'connect_openid_url', 'label'=>'lang:connect_openid_url', 'rules'=>'trim|required')
			));			

			// Run form validation
			if ($this->form_validation->run()===TRUE) {			

				// Get OpenID store object
				$store = new Auth_OpenID_FileStore($this->config->item("openid_file_store_path"));				

				// Get OpenID consumer object
				$consumer = new Auth_OpenID_Consumer($store);			

				// Begin OpenID authentication process
				if ( ! $auth_request = $consumer->begin($this->input->post('connect_openid_url'))) {			

					$data['connect_openid_error'] = sprintf(lang('connect_invalid_openid'), lang('connect_openid'));
				}
				else {				

					// Create sreg_request (Simple Registration)
					if ($sreg_request = Auth_OpenID_SRegRequest::build(array(
																		//'nickname', 
																		//'email', 
																		'fullname', 
																		'firstname', 
																		'lastname', 
																		'gender', 
																		//'dob', 
																		'postcode', 
																		'country', 
																		'language', 
																		'timezone'
																		)
																		)
						) 

						$auth_request->addExtension($sreg_request);					

					// Redirect to authorizate URL
					header("Location: ".$auth_request->redirectURL(base_url(), site_url('connect/openid')));
				}
			}			

			$this->load->view('openid', isset($data) ? $data : NULL);
		}
		else {		

			redirect('auth/sign_in');
		}
	}
}
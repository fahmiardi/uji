<?php

class Home extends CI_Controller {

	function __construct() {
        	
		parent::__construct();
		
		// Load the necessary stuff...
		$this->load->helper(array('language', 'url', 'form', 'ssl'));
		$this->load->library('auth/authentication');
		$this->load->model('auth/account_model');
		$this->load->language('general');
	}
	
	function index() {

		maintain_ssl();
		
		if ($this->authentication->is_signed_in())
		{
			if( ! $data['account'] = $this->account_model->get_by_id($this->session->userdata('account_id'))) {
				
				$this->authentication->sign_out();
				
				redirect('auth/sign_up');
			}
		}
		
		$this->load->view('home', isset($data) ? $data : NULL);
	}
}

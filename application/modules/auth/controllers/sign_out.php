<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sign_out extends CI_Controller {

	function __construct() {

		parent::__construct();
		
		$this->load->config('auth/auth');
		$this->load->helper(array('language', 'url'));
		$this->load->library(array('auth/authentication'));
		$this->load->language(array('general', 'auth/auth'));
	}

	function index() {

		if ( ! $this->authentication->is_signed_in() ) redirect('');

		$this->authentication->sign_out();

		if( ! $this->config->item("sign_out_view_enabled") ) redirect('');

		$this->load->view('auth/sign_out');
	}
}

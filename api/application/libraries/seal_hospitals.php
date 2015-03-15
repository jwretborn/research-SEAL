<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class seal_hospitals extends DI_Simplelib {

	public function __construct() {
		$this->CI =& get_instance();

		$this->CI->load->model('seal_hospital', 'seal_hospital');
		$this->rest_model = 'seal_hospital';
	}

	public function get($args='') {

		return parent::get($args);
	}

	public function post($args='') {
		return parent::post($args);
	}
}
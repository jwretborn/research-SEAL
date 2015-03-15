<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class seal_timepoints extends DI_Simplelib {

	public function __construct() {
		$this->CI =& get_instance();

		$this->CI->load->model('seal_timepoint', 'rest_model');
	}

	public function get($args='') {
		return parent::get($args);
	}

	public function post($args='') {
		return parent::post($args);
	}
}
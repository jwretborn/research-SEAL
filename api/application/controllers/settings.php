<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Settings extends DI_Controller {

	public function __construct() {
		parent::__construct();

		$this->load->library('DI_Settings');
	}

	/*
	 * Fetching settings
	 */
	public function setting_get($args=array(), $options='') {
		if ( ! parent::auth() )
			return $this->permission_denied();
		
		$data = $this->di_settings->get($args, $options);

		// We do not want to expose the password
		if (isset($data['zavann_password']))
			unset($data['zavann_password']);
		if (isset($data[0]['zavann_password']))
			unset($data[0]['zavann_password']);

		return $this->response($data[0], $data[1]);
	}

	/*
	 * Updating settings
	 */
	public function setting_put($args='') {
		if ( ! parent::auth() )
			return $this->permission_denied();

		$allowed_keys = array('zavann_password', 'zavann_username', 'zavann_url', 'price_self_cost');

 		foreach ($allowed_keys as $key)
			$_POST[$key] = $data[$key] = isset($data[$key]) ? $data[$key] : $this->body($key);
		
		$response = $this->di_settings->put($args, $data);
	
		return $this->response($response[0], $response[1]);
	}
}
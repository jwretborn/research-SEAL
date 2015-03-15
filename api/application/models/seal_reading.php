<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

	Class seal_reading extends DI_Model
	{
		function __construct()
		{
			parent::__construct();
			
			$this->table = 'seal_readings';
			$this->key = 'id';
			
			$this->validation_rules = array(
											array(
													'field' => 'hospital_id',
													'label' => 'Hospital',
													'rules' => 'required'
												),
											array(
													'field' => 'timestamp',
													'label' => 'Time',
													'rules' => 'required'
												)
											);
			
			$this->allowed_get_keys = array('id', 'hospital_id', 'timestamp');
		}
	}

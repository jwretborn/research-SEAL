<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

	Class seal_hospital extends DI_Model
	{
		function __construct()
		{
			parent::__construct();
			
			$this->table = 'seal_hospitals';
			$this->key = 'id';
			
			$this->validation_rules = array(
											array(
													'field' => 'name',
													'label' => 'Namn',
													'rules' => 'required'
												)
											);
			
			$this->allowed_get_keys = array('id', 'name');
		}
	}

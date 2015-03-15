<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

	Class seal_timepoint extends DI_Model
	{
		function __construct()
		{
			parent::__construct();
			
			$this->table = 'seal_timepoints';
			$this->key = 'id';
			
			$this->validation_rules = array(
											array(
													'field' => 'relativehour',
													'label' => 'Relative hour',
													'rules' => 'required'
												)
											);
			
			$this->allowed_get_keys = array('id', 'relativehour');
		}

		public function get($where = array(), $include_object = FALSE, $order_by = FALSE, $where_like = FALSE, $use_get_keys = FALSE) {
			return parent::get($where, $include_object, 'relativehour', $where_like, $use_get_keys);

		}
	}

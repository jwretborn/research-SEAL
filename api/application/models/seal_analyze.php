<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

	Class seal_analyze extends DI_Model
	{
		function __construct()
		{
			parent::__construct();
			
			$this->table = 'seal_analyze';
			$this->key = 'id';
			
			$this->validation_rules = array(
											array(
													'field' => 'reading_id',
													'label' => 'Hospital',
													'rules' => 'required'
												),
											array(
													'field' => 'doc_assess',
													'label' => 'Hospital',
													'rules' => 'required'
												),
											array(
													'field' => 'nurse_assess',
													'label' => 'Hospital',
													'rules' => 'required'
												),
											array(
													'field' => 'mean_assess',
													'label' => 'Hospital',
													'rules' => 'required'
												),
											array(
													'field' => 'prio',
													'label' => 'Hospital',
													'rules' => 'required'
												),
											array(
													'field' => 'triage_prio',
													'label' => 'Hospital',
													'rules' => 'required'
												),
											array(
													'field' => 'awaiting_md',
													'label' => 'Hospital',
													'rules' => 'required'
												),
											array(
													'field' => 'average_time',
													'label' => 'Time',
													'rules' => 'required'
												),
											array(
													'field' => 'longest_stay',
													'label' => 'Hospital',
													'rules' => 'required'
												),
											array(
													'field' => 'occupancy',
													'label' => 'Hospital',
													'rules' => 'required'
												),
											array(
													'field' => 'occupancy_rate',
													'label' => 'Hospital',
													'rules' => 'required'
												),
											array(
													'field' => 'volume_average',
													'label' => 'Hospital',
													'rules' => 'required'
												),
											array(
													'field' => 'admit_index',
													'label' => 'Hospital',
													'rules' => 'required'
												)
											);
			
			$this->allowed_get_keys = array('id', 'reading_id', 'doc_assess', 'nurse_assess', 'mean_assess', 'prio', 'triage_prio', 'awaiting_md', 'average_time', 'longest_stay', 'occupancy', 'occupancy_rate', 'volume_average', 'admit_index');
		}

		public function where_in($key, $val) {
			return $this->db->where_in($key, $val);
		}
	}

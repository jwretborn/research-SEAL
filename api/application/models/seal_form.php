<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

	Class seal_form extends DI_Model
	{
		function __construct()
		{
			parent::__construct();
			
			$this->table = 'seal_forms';
			$this->key = 'id';
			
			$this->validation_rules = array(
											array(
													'field' => 'reading_id',
													'label' => 'Reading',
													'rules' => 'required'
												),
											array(
													'field' => 'type',
													'label' => 'Type',
													'rules' => 'required'
												),
											array(
													'field' => 'question_1',
													'label' => 'Question 1',
													'rules' => 'required'
												),
											array(
													'field' => 'question_2',
													'label' => 'Question 2',
													'rules' => 'required'
												)
											);
			
			$this->allowed_get_keys = array('id', 'reading_id', 'type', 'question_1', 'question_2');
		}

		public function get_join_readings($where=array(), $order_by=FALSE, $where_like=FALSE, $use_get_keys=TRUE) {
			if (isset($this->allowed_get_keys) && $use_get_keys === TRUE) {
				$select = $this->table.'.id as id, reading_id, type, question_1, question_2, seal_forms.created, seal_readings.timestamp as reading_timestamp';
			}
			else {
				$select = $this->table.'.*';
			}

			$this->db->select($select);

			if ($order_by === FALSE) {
				$this->db->order_by($this->table.'.'.$this->key, "desc");
			}
			else {
				$this->db->order_by($order_by);
			}
			
			if ( ! is_array($where) && isset($where) && ctype_digit((string)$where))
			{
				if($where_like === TRUE)
					$this->db->where($this->table.'.'.$this->key, $where);
				else
					$this->db->like($this->table.'.'.$this->key, $where);
			}
			else if (is_array($where) && ! empty($where))
			{
				if($where_like === TRUE)
					$this->db->like($where);
				else
					$this->db->where($where);
			}

			$this->db->join('seal_readings', $this->table.'.reading_id = seal_readings.id');

			$query = $this->db->get($this->table);

			if ($query->num_rows() > 0)
			{
				$result = $query->result_array();
			}
			else
			{
				$result = FALSE;
			}
			
			return $result;
		} 
	}

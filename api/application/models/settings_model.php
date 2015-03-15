<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Settings_model extends DI_Model {

	function __construct()
	{
		parent::__construct();
		
		$this->table = 'di_settings';
		$this->key = 'id';
		
		$this->validation_rules = array(
										array(
												'field' => 'url',
												'label' => 'Länk',
												'rules' => 'required'
											)
										);

		// All keys that should be allowed to be fetched by the API must be set here
		$this->allowed_get_keys = array('zavann_username', 'zavann_password', 'zavann_url', 'price_self_cost', 'price_powertax', 'price_vat', 'price_nordpool', 'price_swedish_powergrid', 'price_power_certificate', 'price_priceId_1', 'price_priceId_2', 'price_priceId_3');

	}

	public function get($where = array(), $include_object = FALSE, $order_by = FALSE, $where_like = FALSE, $use_get_keys = TRUE) {
		$set = (is_array($where) && isset($where['set'])) ? $where['set'] : 1;

		// We need to remove set from the array since it will fuck up the filtering later on
		
		$tmp = array();
		if (is_array($where)) {
			foreach ($where as $key => $val) 
				if ($key !== 'set')
					$tmp[$key] = $val;
			$where = $tmp;
		}

		$this->db->where(array('set' => $set));
		$q = $this->db->get($this->table);

		if ($q->num_rows() > 0)
			$data = $q->result_array();
		else
			return FALSE;

		$res = array();
		foreach ($data as $index => $row)
			if ($row['type'] === 'array')
				$res[$row['field']] = array('value' => json_decode($row['data']), 'type' => $row['type']);
			else
				$res[$row['field']] = array('value' => $row['data'], 'type' => $row['type']);

		$arr = array();

		if ( ! is_array($where) ) {
			if (isset($res[$where]))
				return array($where => $res[$where]);
		}
		else if ( is_array($where) && ! empty($where) ) {
			foreach ($where as $key)
				if (isset($res[$key]))
					$arr[$key] = $res[$key];
		}
		else {
			foreach ($this->allowed_get_keys as $key)
				if (isset($res[$key]))
					$arr[$key] = $res[$key];
		}

		return $arr;
	}

	public function update($id, $data) {
		if ($id === '')
			$id = 1;

		foreach ($data as $key => $value) {
			$this->db->where(array('set' => $id, 'field' => $key));
			$this->db->update($this->table, array('data' => $value));
		}
		return TRUE;
	}

}

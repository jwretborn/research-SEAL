<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

	Class seal_patient extends DI_Model
	{
		function __construct()
		{
			parent::__construct();
			
			$this->table = 'seal_patients';
			$this->key = 'id';
			
			$this->allowed_get_keys = array('id', 'in_timestamp', 'out_timestamp', 'out_reason', 'in_unit', 'out_unit', 'ward', 'hospital_id');
		}

		/* 
		 * Fetch all patients in the ED at a given timestamp
		 */
		public function get_for_timestamp($timestamp, $hospital_id, $period=3600) {

			if ($cache = $this->cache->get('patient.timestamp.'.$timestamp.'.'.$hospital_id)) {
				return $cache;
			}

			$this->db->select('*');
			$this->db->where(
				array(
					'in_timestamp <=' => $timestamp,
					'out_timestamp >=' => $timestamp-$period,
					'hospital_id' => $hospital_id
				)
			);
			$q = $this->db->get($this->table);

			if ($q->num_rows() > 0) {
				$return = $q->result_array();
				$this->cache->save('patient.timestamp.'.$timestamp.'.'.$hospital_id, $return, 30);
				return $return;
			}
			else {
				return FALSE;
			}
		}

		/*
		 * Fetch all patients discharged within the $period from $timestamp
		 */
		public function get_discharged($timestamp, $hospital_id, $period=3600) {
			$this->db->select('*');
			$this->db->where(
				array(
					'out_timestamp <=' => $timestamp,
					'out_timestamp >=' => $timestamp-$period,
					'hospital_id' => $hospital_id
					)
				);
			$q = $this->db->get($this->table);

			if ($q->num_rows() > 0) {
				return $q->result_array();
			}
			else {
				return FALSE;
			}
		}

		/*
		 * Fetch all patients registered within the $period seconds before $timestamp
		 */
		public function get_registered($timestamp, $hospital_id, $period=3600) {
			$this->db->select('*');
			$this->db->where(
				array(
					'in_timestamp <=' => $timestamp,
					'in_timestamp >=' => $timestamp-$period,
					'hospital_id' => $hospital_id 
					)
				);
			$q = $this->db->get($this->table);

			if ($q->num_rows() > 0) {
				return $q->result_array();
			}
			else {
				return FALSE;
			}
		}
	}

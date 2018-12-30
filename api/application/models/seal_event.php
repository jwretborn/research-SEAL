<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

	Class seal_event extends DI_Model
	{
		function __construct()
		{
			parent::__construct();
			
			$this->table = 'seal_events';
			$this->key = 'id';
			
			$this->allowed_get_keys = array('id', 'in_timestamp', 'out_timestamp', 'out_reason', 'in_unit', 'out_unit', 'ward', 'hospital_id');
		}

		/* 
		 * Fetch all events in the ED at a given timestamp
		 */
		public function get_for_timestamp($timestamp, $hospital_id, $period=3600) {

			if ($cache = $this->cache->get('event.timestamp.'.$timestamp.'.'.$hospital_id)) {
				return $cache;
			}

			$this->db->select($this->table.'.*');
			$this->db->where(
				array(
					'seal_patients.in_timestamp <=' => $timestamp,
					'seal_patients.out_timestamp >=' => $timestamp-$period,
					$this->table.'.hospital_id' => $hospital_id,
					$this->table.'.timestamp <=' => $timestamp
				)
			);
			$this->db->join('seal_patients', 'seal_patients.id = '.$this->table.'.patient_id');
			$this->db->order_by('patient_id, timestamp asc');

			$q = $this->db->get($this->table);

			if ($q->num_rows() > 0) {
				$res = $q->result_array();
				$return = array();
				array_push($return, $res[0]);
				$tmp = 0;
				// Loop over all events to filter out the values relevant for the timestamp
				foreach ($res as $index => $row) {
					if ($timestamp-$row['timestamp'] <= $period) {
						// Timestamp is within one hour, add it straight away
						$row['diff'] = $timestamp-$row['timestamp'];
						array_push($return, $row);
					}
					else {
						// Timestamp is not within one hour, don't add it straight away
						if ($row['patient_id'] !== $return[$tmp]['patient_id']) {
							// We are looking at a new patient, add the timestamp for the new patient
							$row['diff'] = $timestamp-$row['timestamp'];
							array_push($return, $row);
							$tmp = count($return)-1;
						}
						else {
							// If we have a timestamp closer to our period we want to save it
							if ($row['timestamp'] > $return[$tmp]['timestamp']) {
								$row['diff'] = $timestamp-$row['timestamp'];
								$return[$tmp] = $row;
							}
						}
					}
				}

				$this->cache->save('event.timestamp.'.$timestamp.'.'.$hospital_id, $return, 30);

				return $return;
			}
			else {
				return FALSE;
			}
		}

		public function get_before_timestamp($timestamp, $hospital_id, $period=3600) {

			if ($cache = $this->cache->get('event.before.timestamp.'.$timestamp.'.'.$hospital_id)) {
				return $cache;
			}

			$this->db->select($this->table.'.*');
			$this->db->where(
				array(
					'seal_patients.in_timestamp <=' => $timestamp,
					'seal_patients.out_timestamp >=' => $timestamp-$period,
					$this->table.'.hospital_id' => $hospital_id,
					$this->table.'.timestamp <=' => $timestamp
				)
			);
			$this->db->join('seal_patients', 'seal_patients.id = '.$this->table.'.patient_id');
			$this->db->order_by('patient_id, timestamp asc');

			$q = $this->db->get($this->table);

			if ($q->num_rows() > 0) {
				$res = $q->result_array();
				$this->cache->save('event.before.timestamp.'.$timestamp.'.'.$hospital_id, $res, 30);
				return $res;
			}
			else {
				return FALSE;
			}
		}

		public function get_for_patients($timestamp, $hospital_id, $period=3600) {
			if ($cache = $this->cache->get('event.patients.timestamp.'.$timestamp.'.'.$hospital_id)) {
				return $cache;
			}

			$this->db->select($this->table.'.*');
			$this->db->where(
				array(
					'seal_patients.in_timestamp <=' => $timestamp,
					'seal_patients.out_timestamp >=' => $timestamp-$period,
					$this->table.'.hospital_id' => $hospital_id
				)
			);
			$this->db->join('seal_patients', 'seal_patients.id = '.$this->table.'.patient_id');
			$this->db->order_by('patient_id, timestamp asc');

			$q = $this->db->get($this->table);

			if ($q->num_rows() > 0) {
				$res = $q->result_array();
				$this->cache->save('event.before.timestamp.'.$timestamp.'.'.$hospital_id, $res, 30);
				return $res;
			}
			else {
				return FALSE;
			}
		}
	}
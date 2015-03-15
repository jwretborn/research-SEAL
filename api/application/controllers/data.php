<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Data extends DI_Controller {

	public function __construct() {
		parent::__construct();
	}

	/*
	 *
	 */
	public function hospital_get($args='') {
		$this->load->library('seal_hospitals');
		$this->load->library('seal_readings');
		$this->load->library('seal_forms');

		$data = $this->seal_hospitals->get($args);
		$data = $data[0];

		if ($args !== '' && $args === 'readings') {
			$hospitals = $data;
			$readings = $this->seal_readings->get();
			$forms = $this->seal_forms->get_with_reading();

			$data = array();

			foreach($hospitals as $key => $value) {
				$value['readings'] = array();
				$value['forms'] = array();
				array_push($data, $value);
			}

			foreach ($readings[0] as $key => $value) {
				foreach ($data as $k => $val) {
					if ($val['id'] === $value['hospital_id']) {
						array_push($data[$k]['readings'], $value);
					}
				}
			}

			foreach ($forms[0] as $key => $value) {
				foreach ($data as $k => $val) {
					if ($val['id'] === $value['hospital_id']) {
						array_push($data[$k]['forms'], $value);
					}
				}
			}
		}

		return $this->response($data, 200);
	}

	/*
	 *
	 */
	public function hospital_post($args='') {
		$this->load->library('seal_hospitals');
		$allowed_keys = array('name');

		// Fetch data
		$data = array();
 		foreach ($allowed_keys as $key)
			$_POST[$key] = $data[$key] = isset($data[$key]) ? $data[$key] : $this->body($key);

		$data = $this->seal_hospitals->post($data);

		return $this->response($data[0], $data[1]);
	}

	/*
	 *
	 */
	public function reading_get($args='') {
		$this->load->library('seal_readings');
		$this->load->model('seal_hospital');
		$where = array();

		if (($filter = $this->input->get('filter')) !== FALSE && ctype_digit($filter) && $filter >= 0)
			$where['hospital_id'] = $filter;

		if ($args !== '' && ctype_digit($args))
			$where['id'] = $args;

		$data = $this->seal_readings->get($where);
		$hospital = $this->seal_hospital->get();
		$sorted = array();

		foreach ($hospital as $index => $hospital) {
			$sorted[$hospital['id']] = $hospital;
		}

		foreach ($data[0] as $index => $reading) {
			if (isset($sorted[$reading['hospital_id']]))
				$data[0][$index]['hospital'] = $sorted[$reading['hospital_id']]['name'];
		}

		return $this->response($data[0], $data[1]);
	}

	/*
	 *
	 */
	public function reading_post($n, $start, $end) {
		$this->load->library('seal_readings');

		$data = $this->seal_readings->generate_readings($n, strtotime($start), strtotime($end));

		return $this->response($data[0][0], $data[1]);
	}

	/*
	 *
	 */
	public function reading_delete($args='') {
		$this->load->library('seal_readings');

		if ($args !== '') {
			$data = $this->seal_readings->delete($args);
		}
		else {
			$data = $this->seal_readings->remove_all();
		}

		return $this->response($data[0], $data[1]);
	}

	/*
	 *
	 */
	public function form_get($args='') {
		$this->load->library('seal_forms');

		$data = $this->seal_forms->get($args);

		return $this->response($data[0], $data[1]);
	}

	/*
	 *
	 */
	public function form_post($args='') {
		$this->load->library('seal_forms');
		$allowed_keys = array('reading_id', 'type', 'question_1', 'question_2');

		// Fetch data
		$data = array();
 		foreach ($allowed_keys as $key)
			$_POST[$key] = $data[$key] = isset($data[$key]) ? $data[$key] : $this->body($key);

		$data = $this->seal_forms->post($data);

		return $this->response($data[0], $data[1]);
	}

	/*
	 *
	 */
	public function timepoint_get($args='') {
		$this->load->library('seal_timepoints');
		$this->load->library('seal_readings');
		$this->load->library('seal_forms');

		$res = $this->seal_timepoints->get($args);

		$data = array();
		foreach ($res[0] as $key => $value) {
			if ( ! isset($data[count($data)-1]) || $data[count($data)-1]['relativehour'] != $value['relativehour']) {
				array_push($data, $value);
			}
		}

		if ($args !== '') {
			if ($args === 'readings') {
				$format = 'G';
				$selection = 'relativehour';
			}
			else {
				$format = 'N';
				$selection = 'day';
				$data = array(
					array('day' => '1', 'desc' => 'Måndag'),
					array('day' => '2', 'desc' => 'Tisdag'),
					array('day' => '3', 'desc' => 'Onsdag'),
					array('day' => '4', 'desc' => 'Torsdag'),
					array('day' => '5', 'desc' => 'Fredag'),
					array('day' => '6', 'desc' => 'Lördag'),
					array('day' => '7', 'desc' => 'Söndag')
				);
			}

			$loop = $data;
			$readings = $this->seal_readings->get();
			$forms = $this->seal_forms->get_with_reading();

			$data = array();

			foreach($loop as $key => $value) {
				$value['readings'] = array();
				$value['forms'] = array();
				array_push($data, $value);
			}
			foreach ($readings[0] as $key => $value) {
				foreach ($data as $k => $val) {
					if (date($format, $value['timestamp']) === $val[$selection]) {
						array_push($data[$k]['readings'], $value);
					}
				}
			}

			foreach ($forms[0] as $key => $value) {
				foreach ($data as $k => $val) {
					if (date($format, $value['timestamp']) === $val[$selection]) {
						array_push($data[$k]['forms'], $value);
					}
				}
			}
		}


		return $this->response($data, $data[1]);
	}

	/*
	 *
	 */
	public function timepoint_post($args='') {
		$this->load->library('seal_timepoints');
		$allowed_keys = array('relativehour');

		// Fetch data
		$data = array();
 		foreach ($allowed_keys as $key)
			$_POST[$key] = $data[$key] = isset($data[$key]) ? $data[$key] : $this->body($key);

		$data = $this->seal_timepoints->post($data);

		return $this->response($data[0], $data[1]);
	}

	/*
	 *
	 */
	public function composite_get($hospital_id='', $db=FALSE) {
		$this->load->library('seal_readings');
		$this->load->library('seal_data');

		$timestamp = $this->input->get('timestamp');
		$start = $this->input->get('start');
		$end = $this->input->get('end');


		if ($timestamp !== FALSE) {
			$readings = array($this->seal_readings->get_for_timestamp($timestamp, $hospital_id));
		}
		else {
			$where = array(
				'hospital_id' => $hospital_id
			);

			if ($start !== FALSE) {
				$where['timestamp >='] = $start;
			}
			if ($end !== FALSE) {
				$where['timestamp <='] = $end;
			}
			$readings = $this->seal_readings->get($where);
		}

		$update = $this->seal_data->update_composite_data($readings[0], $db);

		return $this->response($update, 200);
	}

	/*
	 *
	 */
	public function analyze_get($hospital_id='') {
		$this->load->library('seal_data');
		$this->load->library('seal_readings');
		$this->load->library('seal_hospitals');

		$timestamp = $this->input->get('timestamp');
		$start = $this->input->get('start');
		$end = $this->input->get('end');

		if ($hospital_id == 'all') {
			$hosps = array();
			$hospitals = $this->seal_hospitals->get();
			foreach ($hospitals[0] as $index => $hospital) {
				array_push($hosps, $hospital['id']);
			}
		}
		else {
			$hosps = array($hospital_id);
		}

		$result = $this->seal_data->analyze_timeperiod($start, $end, $hosps);

		return $this->response($result, 200);
	}

	public function staff_get() {
		$this->load->model('seal_event');

		$this->db->select('*');
		$this->db->group_by('doc');
		$q = $this->db->get('seal_events');
		$res = $q->result_array();

		$staff = array();

		foreach ($res as $id => $row) {
			if (strlen($row['doc']) > 2) {
				$nurse = trim($row['doc']);
				if ( ! isset($staff[$nurse])) {
					$staff[$nurse] = true;
				}
			}
		}

		foreach ($staff as $name => $tmp) {
			$this->db->insert('seal_staff', array('name' => $name));
		}
	}

	public function match_get() {
		$events = array();

		$this->db->select('*');
		$this->db->where('doc IS NOT NULL');
		$q = $this->db->get('seal_events');
		$events = $q->result_array();

		$this->db->select('*');
		$q = $this->db->get('seal_staff');
		$staffs = $q->result_array();

		foreach ($events as $id => $event) {
			foreach ($staffs as $i => $staff) {
				if (strtolower($event['doc']) === strtolower($staff['name'])) {
					$this->db->where('id', $event['id']);
					$this->db->update('seal_events', array('doc' => $staff['id']));
					break;
				}
			}
		}
	}

	public function reset_get() {
		$min = 1624486;
		$max = 1634868;

		for ($i = $min; $i <= $max; $i++) {
			$this->db->select('*');
			$this->db->where(array('id' => $i));
			$q = $this->db->get('seal_events');
			if ($q->num_rows() > 0) {
				$res = $q->result_array();
				$nurse = trim($res[0]['nurse']);
				if (strlen($nurse) > 2) {
					$nurse = $nurse;
				}
				else {
					$nurse = NULL;
				}

				$this->db->where('id', $i);
				$this->db->update('seal_events', array('nurse' => $nurse));
			}
		}
		print('done');
	}

	/*
	 *
	 */
	public function test_get($timestamp, $hospital_id=3) {
		$this->load->library('seal_data');
		$this->load->library('seal_readings');
		$this->load->library('seal_forms');
		$this->load->model('seal_event');

		$events = $this->seal_event->get_for_timestamp($timestamp, $hospital_id);	

		$reading = $this->seal_readings->get_for_timestamp($timestamp, $hospital_id);

		$reading = $reading[0];

		//$assessment = $this->seal_forms->calc_assessment($reading);
		//$prio = $this->seal_data->calc_high_priority($reading);
		//$md = $this->seal_data->calc_awaiting_md($reading);
		//$average = $this->seal_data->calc_average_time($reading);
		//$los = $this->seal_data->calc_longest_stay($reading);
		//$triage_prio = $this->seal_data->calc_triage_priority($reading);
		//$occu_rate = $this->seal_data->calc_occupancy_rate($reading);
		//$average_index = $this->seal_data->calc_average_index($reading);
		//$admit_index = $this->seal_data->calc_admit_index($reading);
		//$occupancy = $this->seal_data->calc_occupancy($reading);
		//$patient_hours = $this->seal_data->calc_patient_hours($reading);
		//$composite = $this->seal_data->update_composite_data($reading, FALSE, TRUE);
		$unseen = $this->seal_data->calc_unseen($reading);
		$docs = $this->seal_data->calc_mds($reading);
		$nurses = $this->seal_data->calc_nurses($reading);

		return $this->response($nurses, 200);
	}

	public function date_get($date) {
		return var_dump(date(DateTime::ATOM, $date));
	}
}


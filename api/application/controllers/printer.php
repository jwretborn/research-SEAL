<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Printer extends CI_Controller {

	public function __construct() {
		parent::__construct();
	}


	public function index() {
		var_dump("reading");
	}

	public function form($id='', $filter='', $order='', $start='', $end='') {
		$this->load->model('seal_reading');
		$this->load->model('seal_hospital');
		$this->load->model('seal_form');

		$data = array(
			'readings' => array(
				'id' => 0,
				'day' => 0,
				'hour' => 0,
				'hospital' => 'unknown'
				)
			);

		$hospitals = $this->seal_hospital->get();
		$swe_days = array('Mon' => 'Mån', 'Tue' => 'Tis', 'Wed' => 'Ons', 'Thu' => 'Tors', 'Fri' => 'Fre', 'Sat' => 'Lör', 'Sun' => 'Sön');

		if ($id !== '' && $filter === 'reading') {
			if (($reading = $this->seal_reading->get($id)) !== FALSE) {

				$reading = $reading[0];

				// Get the forms
				$reading['forms'] = $this->seal_form->get(array('reading_id' => $reading['id']));

				foreach ($hospitals as $index => $hospital) {
					if ($hospital['id'] === $reading['hospital_id']) {
						$reading['hospital'] = $hospital['name'];
					}
				}
				$reading['day'] = strtr(date('D d/n', $reading['timestamp']), $swe_days);
				$reading['hour'] = date('H:i', $reading['timestamp']);
				if ($reading['hour'] == '00:00')
					$reading['hour'] = '24:00';
				$data['readings'] = array(array(array($reading)));
			}
		}
		else {
			if ($id !== '' && $filter === 'hospital') {
				$where = array('hospital_id' => $id);
			}
			else {
				$where = array();
			}

			if ($start !== '') {
				$where['timestamp >'] = strtotime($start);
			}

			if ($end !== '') {
				$where['timestamp <'] = strtotime($end)+(24*3600);
			}

			$readings = $this->seal_reading->get($where, FALSE, 'timestamp ASC');
			$data['readings'] = array();
			$keys = array();

			$min = $readings[0]['timestamp'] - $readings[0]['timestamp']%(60*60*24); 

			foreach($hospitals as $index => $hospital) {
				$keys[$hospital['id']] = array('i' => $index, 'name' => $hospital['name']);
			}

			if ( $order === 'repeat' ) {
				$repeat = 1;
			}
			else {
				$repeat = 0;
			}


			for ($j=0; $j<=$repeat; $j++) {
				foreach($readings as $key => $reading) {
					if ( ! isset($data['readings'][$keys[$reading['hospital_id']]['i']+$j]) )
						$data['readings'][$keys[$reading['hospital_id']]['i']+$j] = array();
	
					$i = ($reading['timestamp'] - $min)/(60*60*24);
	
					if ( ! isset($data['readings'][$keys[$reading['hospital_id']]['i']+$j][$i]) )
						$data['readings'][$keys[$reading['hospital_id']]['i']+$j][$i] = array();
	
					array_push($data['readings'][$keys[$reading['hospital_id']]['i']+$j][$i], array(
						'day' => strtr((date('H:i', $reading['timestamp']) == '00:00' ? date('D j/n', $reading['timestamp']-1) : date('D d/n', $reading['timestamp'])), $swe_days),
						'hour' => strtr(date('H:i', $reading['timestamp']), array('00:00' => '24:00')),
						'id' => $reading['id'],
						'forms' => $this->seal_form->get(array('reading_id' => $reading['id'], 'type' => $j+1), FALSE, 'type ASC'),
						'hospital' => $keys[$reading['hospital_id']]['name']
						)
					);
				}
			}
		}

		return $this->load->view('form_adjustment', $data);
	}

	public function readinglist($filter='') {
		$this->load->model('seal_reading');
		$this->load->model('seal_hospital');

		$data = array();
		$where = array();

		$h = '';
		$hospitals = $this->seal_hospital->get();

		if ($filter !== '') {
			foreach($hospitals as $key => $hospital) {
				if ((ctype_digit($filter) && $hospital['id'] === $filter) || strtolower($filter) === strtolower($hospital['name'])) {
					$where = array('hospital_id' => $hospital['id']);
					$h = $hospital['name'];
				}
			}
		}
		else {
			$h = 'Alla';
		}

		if ($readings = $this->seal_reading->get($where, FALSE, 'timestamp ASC')) {
			$min = $readings[0]['timestamp'];
			foreach ($readings as $key => $r) {
				$index = (($r['timestamp']-($r['timestamp']%(60*60*24)))-($min-($min%(60*60*24))))/(60*60*24);
				if ( ! isset($data[$index]) )
					$data[$index] = array();
				
				array_push($data[$index], $r);
			}
		}

		$data['readings'] = $data;
		$data['hospital'] = $h;
		$data['swe_days'] = array('Monday' => 'Måndag', 'Tuesday' => 'Tisdag', 'Wednesday' => 'Onsdag', 'Thursday' => 'Torsdag', 'Friday' => 'Fredag', 'Saturday' => 'Lördag', 'Sunday' => 'Söndag');
		
		return $this->load->view('list', $data);
	}
}
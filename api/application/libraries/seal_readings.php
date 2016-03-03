<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class seal_readings extends DI_Simplelib {

	public function __construct() {
		$this->CI =& get_instance();

		$this->CI->load->model('seal_reading', 'seal_reading');
		$this->rest_model = 'seal_reading';
	}

	public function get($args='') {
		$result = parent::get($args, FALSE, FALSE, 'timestamp asc');
		foreach ($result[0] as $key => $reading) {
			$result[0][$key]['human_date'] = date(DateTime::ATOM, $reading['timestamp']);
		}

		return $result;
	}

	public function get_for_timestamp($timestamp, $hospital_id='') {
		if ($hospital_id !== '')
			$reading = $this->CI->seal_reading->get(array('timestamp' => $timestamp, 'hospital_id' => $hospital_id));
		else
			$reading = $this->CI->seal_reading->get(array('timestamp' => $timestamp));

		if ( ! $reading ) {
			return FALSE;
		}
		else {
			return $reading;
		}
	}

	/*
	 *	Fetch all readings from $start to $end from $hospital and output
	 *	how many forms of each reading is filled
	 */
	public function get_with_form_count($start, $end='', $hospital='') {
		$this->CI->load->model('seal_form');

		if ($end !== '') {
			$where = array(
				'timestamp >=' => $start,
				'timestamp <=' => $end
				);
		}
		else {
			$where = array('timestamp' => $start);
		}

		if ($hospital !== '') {
			$where['hospital_id'] = $hospital;
		}

		if (($readings = $this->CI->seal_reading->get($where)) !== FALSE) {
			foreach ($readings as $key => $reading) {
				$readings[$key]['forms'] = array();
				if (($forms = $this->CI->seal_form->get(array('reading_id' => $reading['id']))) !== FALSE) {
					foreach ($forms as $index => $form) {
						if (isset($readings[$key]['forms'][$form['type']])) {
							$readings[$key]['forms'][$form['type']] += 1;
						}
						else {
							$readings[$key]['forms'][$form['type']] = 1;
						}
					}
				}
			}
		}
		else {

		}

		return array($readings, 200);

	}

	public function delete($args='') {
		return parent::delete($args);
	}

	public function remove_all() {
		$readings = parent::get(array());
		$readings = $readings[0];
		$return = array();
		foreach ($readings as $reading) {
			array_push($return, $reading['id']);
			parent::delete($reading['id']);
		}

		$result = array('readings' => $return, 'message' => 'DELETED!');
            	
        return array($result, 200); // 200 being the HTTP response code
	}

	public function generate_readings($n, $start_date, $end_date) {
		$this->CI->load->model('seal_hospital');
		$this->CI->load->model('seal_timepoint');
		$this->CI->load->model('seal_form');

		// Set the hospital_count (number of readings for each hospital)
		$h_count = $n;
		$readings = array();

		// Get hospitals and timepoints
		$hospitals = $this->CI->seal_hospital->get(array('id' => 1));
		$timepoints = $this->CI->seal_timepoint->get();

		// Get the number of days between the dates
		$diff = floor(($end_date-$start_date)/(60*60*24));

		srand();
		foreach ($hospitals as $key => $hospital) {
			$rands = array();
			for ($i = 0; $i<$h_count; $i++) {
				do {
					$exists = FALSE;
					$t = rand(0, count($timepoints)-1);
					$d = rand(0, $diff);

					// Check if we've already used this timepoint
					if (isset($rands[$d]) && isset($rands[$d][$timepoints[$t]['relativehour']])) {
						$exists = TRUE;
					}
					else {
						// Make sure we don't choose this date and timepoint again
						if ( ! isset($rands[$d]) )
							$rands[$d] = array();
						$rands[$d][$timepoints[$t]['relativehour']] = TRUE;
					}

				} while($exists === TRUE);

				$date = $start_date + ($d*60*60*24); // Get the unix timestamp for the randomized date
				$time = mktime($timepoints[$t]['relativehour'], 0, 0, date('n', $date), date('j', $date), date('Y', $date));

				$timepoint = array(
					'hospital_id' => $hospital['id'],
					'timestamp' => $time
				);

				// Create the reading
				if ($created_id = $this->CI->seal_reading->create($timepoint)) {
					// Create the associated form
					/*
					$form = array('reading_id' => $created_id, 'type' => '1');
					$this->CI->seal_form->create($form);
					$form['type'] = '2';
					$this->CI->seal_form->create($form);
					
					$result = $this->CI->seal_reading->get($created_id);
					array_push($readings, array_pop($result));
					*/
				}

			}
		}

		return array($readings, 201);
	}
}
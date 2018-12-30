<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class seal_data extends DI_Simplelib {

	public function __construct() {
		$this->CI =& get_instance();

		$this->CI->load->model('seal_patient');
		$this->CI->load->model('seal_event');
		$this->CI->load->model('seal_hospital');
		$this->CI->load->model('seal_analyze');

		$this->discard_prio = TRUE;
		$this->exclude_priority = array('0', '5');
	}

	public function get_composite_data($readings) {
		$result = array();
		$r_ids = array();

		foreach ($readings as $key => $reading) {
			array_push($r_ids, $reading['id']);
		}

		$this->CI->seal_analyze->where_in('reading_id', $r_ids);
		$result = $this->CI->seal_analyze->get();

		return $result;

	}

	// Analyze score for each period from start to stop
	public function analyze_timeperiod($start, $stop, $hospitals, $period=3600) {
		$this->CI->load->library('seal_forms');
		$this->CI->load->model('seal_score');
		$result = array();

		if ( ! is_array($hospitals) )
			$hospitals = array($hospitals);

		$hours = ($stop-$start)/3600;
		foreach ($hospitals as $index => $hospital) {
			for ($i=0; $i<$hours; $i++) {
				$reading = array(
					'timestamp' => $start + ($i*3600),
					'hospital_id' => $hospital
					);

				$read['timestamp'] = array('timestamp' => $reading['timestamp']);
				$read['hospital_id'] = array('hospital_id' => $reading['hospital_id']);
				$read['prio'] = $this->calc_advanced_priority($reading);
				$read['triage_prio'] = $this->calc_triage_priority($reading);
				$read['high_prio'] = $this->calc_high_priority($reading);
				$read['awaiting_md'] = $this->calc_awaiting_md($reading);
				$read['patient_hours'] = $this->calc_patient_hours($reading);
				$read['average_time'] = $this->calc_average_time($reading);
				$read['longest_stay'] = $this->calc_longest_stay($reading);
				$read['volume_average'] = $this->calc_volume_average($reading);
				$read['admit_index'] = $this->calc_admit_index($reading);
				$read['occupancy'] = $this->calc_occupancy($reading);
				$read['occupancy_rate'] = $this->calc_occupancy_rate($reading);
				$read['unseen'] = $this->calc_unseen($reading);
				$read['mds'] = $this->calc_mds($reading);
				$read['nurses'] = $this->calc_nurses($reading);
				$read['mean_model'] = $this->calc_mean_model($read);
				$read['nurse_model'] = $this->calc_nurse_model($read);
				$read['doc_model'] = $this->calc_doc_model($read);
				$read['full_model'] = $this->calc_full_model($read);

				foreach ($read as $j => $val) {
					$read[$j] = $val[$j];
				}

				if ( ! ($score = $this->CI->seal_score->get($reading)) ) {
					$this->CI->seal_score->create($read);
				}
				else {
					$this->CI->seal_score->update($score[0]['id'], $read);
				}
			}
		}
	}

	public function update_composite_data($readings, $update_db=FALSE, $display_full=FALSE) {
		$this->CI->load->library('seal_forms');
		$result = array();

		foreach ($readings as $key => $reading) {
			$read = array(
				'reading_id' => array('reading_id' => (int)$reading['id'])
			);
			$assessment = $this->CI->seal_forms->calc_assessment($reading);
			$read['doc_assess'] = $assessment;
			$read['nurse_assess'] = $assessment;
			$read['mean_assess'] = $assessment;
			$read['prio'] = $this->calc_advanced_priority($reading);
			$read['triage_prio'] = $this->calc_triage_priority($reading);
			$read['high_prio'] = $this->calc_high_priority($reading);
			$read['awaiting_md'] = $this->calc_awaiting_md($reading);
			$read['patient_hours'] = $this->calc_patient_hours($reading);
			$read['average_time'] = $this->calc_average_time($reading);
			$read['longest_stay'] = $this->calc_longest_stay($reading);
			$read['volume_average'] = $this->calc_volume_average($reading);
			$read['admit_index'] = $this->calc_admit_index($reading);
			$read['occupancy'] = $this->calc_occupancy($reading);
			$read['occupancy_rate'] = $this->calc_occupancy_rate($reading);
			$read['unseen'] = $this->calc_unseen($reading);
			$read['mds'] = $this->calc_mds($reading);
			$read['nurses'] = $this->calc_nurses($reading);
			$read['mean_model'] = $this->calc_mean_model($read);
			$read['nurse_model'] = $this->calc_nurse_model($read);
			$read['doc_model'] = $this->calc_doc_model($read);
			$read['full_model'] = $this->calc_full_model($read);
			
			foreach ($read as $i => $val) {
				if ( ! $display_full ) {
					$read[$i] = $val[$i];
				}
			}

			array_push($result, $read);
		}

		if ($update_db !== FALSE && $display_full === FALSE) {
			foreach ($result as $key => $val) {
				if ( ! ($obj = $this->CI->seal_analyze->get(array('reading_id' => $val['reading_id']))) ) {
					$this->CI->seal_analyze->create($val);
				}
				else {
					$this->CI->seal_analyze->update($obj[0]['id'], $val);
				}
			}
		}

		return $result;
	}

	// Workload-summary
	// Occupancy-rate

	// Priority for patients in the ED
	/*
	 * Fetch patient selection
	 * Fetch event selection
	 * For each event add current priority to sum
	 *	if priority change, add mean priority
	 *	handle priority 0 (?)
	 * devide sum by number of patients
	 */
	public function calc_simple_priority($reading) {
		$events = $this->CI->seal_event->get_for_timestamp($reading['timestamp'], $reading['hospital_id']);

		if ( ! $events ) {
			return FALSE;
		}

		$tmp_pat = $events[0];
		$tmp_sum = (int)$events[0]['priority'];
		$sum_prio = 0;
		$sum_count = 1;

		// Avoid wrong data if initial prio is 0
		if ($tmp_sum === 0)
			$tmp_count = 0;
		else
			$tmp_count = 1;

		foreach ($events as $key => $event) {
			if (in_array($event['priority'], $this->exclude_priority) && $this->discard_prio)
				continue;

			if ($tmp_pat['patient_id'] !== $event['patient_id']) {
				$sum_prio += ($tmp_sum/$tmp_count);
				$tmp_pat = $event;
				$sum_count++;
				$tmp_count = 1;
				$tmp_sum = (int)$event['priority'];
			}
			else {
				if ($tmp_pat['priority'] !== $event['priority']) {
					$tmp_pat = $event;
					$tmp_sum += (int)$event['priority'];
					$tmp_count++;
				}
			}
		}
		// Add the last patient
		$sum_prio += $tmp_sum/$tmp_count;

		if ($sum_count > 0) {
			$result = array(
				'total_prio' => $sum_prio,
				'num_patients' => $sum_count,
				'prio' => $sum_prio/$sum_count
				);
		}
		else {
			$result = array(
				'total_prio' => $sum_prio,
				'num_patients' => $sum_count,
				'prio' => 0
				);
		}

		return $result;
	}

	public function calc_advanced_priority($reading, $period=3600) {
		$pats = $this->CI->seal_patient->get_for_timestamp($reading['timestamp'], $reading['hospital_id']);
		$events = $this->CI->seal_event->get_for_timestamp($reading['timestamp'], $reading['hospital_id']);

		if ( ! $events ) {
			return FALSE;
		}

		$tmp_pat = $events[0]; // Store current pat.event
		$tmp_sum = 0; // Store current pat.prio-time-sum
		$tmp_count = 0; // Store curernt.pat.prio-time
		$sum_prio = 0; // Store totalt prio-time-sum
		$sum_count = 1; // Store number of patients

		foreach ($events as $key => $event) {
			$time = 0;

			if (in_array($event['priority'], $this->exclude_priority) && $this->discard_prio)
				continue;

			// Check if we are working on a new patient
			if ($tmp_pat['patient_id'] !== $event['patient_id']) {
				$out_time = $reading['timestamp'];
				// Check the discharge-time for the patient, might have been discharged
				foreach ($pats as $index => $pat) {
					if ($pat['id'] === $tmp_pat['patient_id']) {
						if ($pat['out_timestamp'] < $reading['timestamp']) {
							// Pat was dischared before period.end, use discharge-time
							$out_time = $pat['out_timestamp'];
						}
						break;
					}
				}

				// Check if the most recent priority was set before our measuring point
				if ($tmp_pat['timestamp'] < ($reading['timestamp']-$period)) {
					// If so count patient time as the whole period
					$time += $out_time-($reading['timestamp']-$period);
				}
				else {
					// If not, work with the latest timestamp
					$time += $out_time-$tmp_pat['timestamp'];
				}
				// Add prio*time to sum
				$tmp_sum += (int)$tmp_pat['priority']*(int)$time;
				$tmp_count += $time;

				// We are switchin patients so add it to the sum
				if ( $time > 0 && ! (in_array($event['priority'], $this->exclude_priority) && $this->discard_prio) ) {
					$sum_prio += ($tmp_sum/$tmp_count);
					$sum_count++;
				}

				// Switch patient
				$tmp_pat = $event;
				$tmp_count = 0;
				$tmp_sum = 0;
			}
			else {
				// No new patient, check if we have a new priority set
				if ($tmp_pat['priority'] !== $event['priority']) {
					// Yup, check if the last prio was set before our period
					if ($tmp_pat['timestamp'] < ($reading['timestamp']-$period)) {
						// Yes, calc time from start of period to new prio time
						$time += $event['timestamp']-($reading['timestamp']-$period);
					}
					else {
						// Nope, calc time from old prio-time to new
						$time += $event['timestamp']-$tmp_pat['timestamp'];
					}
					// Add it to the tmp sum
					if ( ! (in_array($tmp_pat['priority'], $this->exclude_priority) && $this->discard_prio) ) {
						$tmp_sum += (int)$tmp_pat['priority']*(int)$time;
						$tmp_count += $time;
					}

					// Switch event
					$tmp_pat = $event;
				}
			}
		}
		// Add the last patient
		if ( ! (in_array($tmp_pat['priority'], $this->exclude_priority) && $this->discard_prio) ) {
			if ($tmp_pat['timestamp'] < ($reading['timestamp']-$period)) {
				// If so count patient time as the whole period
				$time += $period;
			}
			else {
				// If not, work with the latest timestamp
				$time += $reading['timestamp']-$tmp_pat['timestamp'];
			}
			// Add prio*time to sum
			$tmp_sum += (int)$tmp_pat['priority']*(int)$time;
			$tmp_count += $time;
		}

		// We are switchin patients so add it to the sum
		if ($tmp_count > 0)
			$sum_prio += ($tmp_sum/$tmp_count);

		if ($sum_count > 0) {
			$result = array(
				'total_prio' => $sum_prio,
				'num_patients' => $sum_count,
				'prio' => $sum_prio/$sum_count
				);
		}
		else {
			$result = array(
				'total_prio' => $sum_prio,
				'num_patients' => $sum_count,
				'prio' => 0
				);
		}

		return $result;
	}

	// Number of patients with high priority as part of total ED
	/*
	 * Fetch event selection
	 * For each event
	 *  if event.prio < cutoff
	 *   Add pat to sum
	 * Devide sum by selection.pats
	 */
	public function calc_high_priority($reading, $cutoff=3) {
		$events = $this->CI->seal_event->get_for_timestamp($reading['timestamp'], $reading['hospital_id']);
		$hospitals = $this->CI->seal_hospital->get($reading['hospital_id']);

		if ( ! $events ) {
			return FALSE;
		}

		$tmp_pat = $events[0];
		$prio_count = 0;
		$sum_count = 1;

		// Avoid wrong data if initial prio is 0
		if ($tmp_pat['priority'] < $cutoff && $tmp_pat['priority'] > 0) 
			$prio_count = 1;
		else
			$prio_count = 0;

		foreach ($events as $key => $event) {
			if (in_array($event['priority'], $this->exclude_priority) && $this->discard_prio)
				continue;

			// Check if we have a new patient
			if ($tmp_pat['patient_id'] !== $event['patient_id']) {
				// New patient, add to counter, check if we have a high prio patient
				$sum_count++;
				if ($event['priority'] < $cutoff && $event['priority'] > 0) {
					$prio_count++;
				}
				$tmp_pat = $event;
			}
			else {
				if ($event['priority'] < $cutoff && $event['priority'] > 0) {
					if ($tmp_pat['priority'] >= $cutoff) {
						// Pat not counted, count it
						$prio_count++;
						$tmp_pat = $event;
					}
				}
			}
		}

		if ($sum_count > 0) {
			$result = array(
				'prio_patients' => $prio_count,
				'num_patients' => $sum_count,
				'ed_beds' => $hospitals[0]['ed_beds'],
				'high_prio' => $prio_count/$hospitals[0]['ed_beds']
				);
		}
		else {
			$result = array(
				'prio_patients' => $prio_count,
				'num_patients' => $sum_count,
				'ed_beds' => $hospitals[0]['ed_beds'],
				'high_prio' => $prio_count/$hospitals[0]['ed_beds']
				);
		}

		return $result;
	}

	// Patient complexity at triage
	/*
	 * Fetch patient selection
	 * Fetch events for each patient
	 * 	For each patient.initial_event add priority to sum
	 *	If patient.initial_event.priority is 0, take next priority
	 */
	public function calc_triage_priority($reading) {
		$events = $this->CI->seal_event->get_for_timestamp($reading['timestamp'], $reading['hospital_id']);

		if ( ! $events ) {
			return FALSE;
		}

		$sum_pat = 1;
		$sum_prio = 0;
		$tmp_pat = $events[0];
		foreach ($events as $key => $event) {
			if ($tmp_pat['patient_id'] !== $event['patient_id']) {
				// We are looking at a new patient
				if ($tmp_pat['priority'] !== '0') {
					$sum_prio += $tmp_pat['priority'];
					$sum_pat++;
				}
				$tmp_pat = $event;
			}
			else if ($tmp_pat['priority'] === '0' && $event['priority'] !== '0') {
				$tmp_pat = $event;
			}
		}

		// Don't forget the last patient
		if ($tmp_pat['priority'] !== '0') {
			$sum_prio += $tmp_pat['priority'];
		}

		$result = array(
			'sum_prio' => $sum_prio,
			'sum_pat' => $sum_pat,
			'triage_prio' => $sum_prio/$sum_pat
			);

		return $result;
	}

	// Number of patients waiting for a MD
	/*
	 * Fetch patient selection
	 * Fetch event selection
	 * For each event
	 *	if event vplak add vplak.time to sum
	 * Devide sum by k[num of ED-patients]
	 *
	 */
	public function calc_awaiting_md($reading) {
		$pats = $this->CI->seal_patient->get_for_timestamp($reading['timestamp'], $reading['hospital_id']);
		$events = $this->CI->seal_event->get_for_timestamp($reading['timestamp'], $reading['hospital_id']);

		if ( ! $events ) {
			return FALSE;
		}

		$tmp_pat = $events[0];
		$pat_count = 1;
		$lak_count = 0;
		$lak_time = 0;
		foreach ($events as $key => $event) {
			if (in_array($event['priority'], $this->exclude_priority) && $this->discard_prio)
				continue;

			if ($tmp_pat['patient_id'] !== $event['patient_id']) {
				if ($tmp_pat['activity'] === 'vplak') {
					// Pat is still waiting for a doc, calc the time

					$found = FALSE;
					// Make sure the pat isn't discharged
					foreach ($pats as $index => $pat) {
						if ($pat['id'] === $tmp_pat['patient_id']) {
							if ($pat['out_timestamp'] < $reading['timestamp']) {
								// Pat was dischared before period.end, use discharge-time
								$lak_time += $pat['out_timestamp'] - $tmp_pat['timestamp'];
								$found = TRUE;
							}
							break;
						}
					}

					if ( ! $found ) {
						$lak_time += $reading['timestamp']-$tmp_pat['timestamp'];
					}

					$lak_count++;
				}

				$pat_count++;
				$tmp_pat = $event;
			}

			if ($event['activity'] === 'vplak' && $tmp_pat['activity'] !== 'vplak') {
				$tmp_pat = $event;
			}
			else if ($event['activity'] !== 'vplak' && $tmp_pat['activity'] === 'vplak') {
				$lak_time += $event['timestamp']-$tmp_pat['timestamp'];
				$tmp_pat = $event;
				$lak_count++;
			}
		}

		if ($pat_count > 0) {
			$result = array(
				'lak_time' => $lak_time,
				'lak_count' => $lak_count,
				'num_patients' => $pat_count,
				'awaiting_md' => ($lak_time/3600)/$pat_count
				);
		}
		else {
			$result = array(
				'lak_time' => $lak_time,
				'lak_count' => $lak_count,
				'num_patients' => $pat_count,
				'awaiting_md' => 0
				);
		}

		return $result;
	}

	// Average time at the ED for patients discharged in the previous hour
	/*
	 * Fetch patients discharged in the previous hour
	 * For each patient
	 *	Added patient.ED-time to sum
	 * Devide sum by number of patients
	 */
	public function calc_average_time($reading) {
		$pats = $this->CI->seal_patient->get_discharged($reading['timestamp'], $reading['hospital_id']);

		if ( ! $pats ) {
			return array(
				'time' => 0,
				'pats' => 0,
				'average_time' => 0
			);
		} 

		$sum_time = 0;
		foreach ($pats as $key => $pat) {
			$sum_time += ($pat['out_timestamp']-$pat['in_timestamp']);
		}

		$result = array(
			'time' => $sum_time/3600,
			'pats' => count($pats),
			'average_time' => ($sum_time/count($pats))/3600
			);

		return $result;
	}

	// Admin index/boarders
	/*
	 * Fetch event selection
	 * For each event
	 * 	if event.vpavd then add patient to sum
	 * Devide sum by k[hospital beds]
	 */
	public function calc_admit_index($reading) {
		$hospitals = $this->CI->seal_hospital->get($reading['hospital_id']);
		$events = $this->CI->seal_event->get_for_timestamp($reading['timestamp'], $reading['hospital_id']);

		if ( ! $events ) {
			return FALSE;
		}

		$tmp_pat = $events[0];
		$pat_count = 1;
		$adm_count = 0;
		foreach ($events as $key => $event) {
			if (in_array($event['priority'], $this->exclude_priority) && $this->discard_prio)
				continue;

			// We are looking at a new patient
			if ($tmp_pat['patient_id'] !== $event['patient_id']) {
				$pat_count++;

				// Have the previous patient been waiting for a ward
				if ($tmp_pat['activity'] === 'vpavd') {
					$adm_count++;
				}
				$tmp_pat = $event;
			}
			
			if ($event['activity'] === 'vpavd' && $tmp_pat['activity'] !== 'vpavd') {
				$tmp_pat = $event;
			}
		}

		if ($pat_count > 0) {
			$result = array(
				'boarders' => $adm_count,
				'tot_patients' => $pat_count,
				'hospital_beds' => $hospitals[0]['hospital_beds'],
				'admit_index' => $adm_count/$hospitals[0]['hospital_beds']
				);
		}
		else {
			$result = array(
				'boarders' => $adm_count,
				'tot_patients' => $pat_count,
				'hospital_beds' => $hospitals[0]['hospital_beds'],
				'admit_index' => $adm_count/$hospitals[0]['hospital_beds']
				);
		}

		return $result;
	}

	// LOS (Length of stay) longest for any patient in the ED
	/*
	 * Fetch patient selection
	 * For each patient
	 *	Find patient with longest time in ED
	 * Return time
	 */
	public function calc_longest_stay($reading) {
		$pats = $this->CI->seal_patient->get_for_timestamp($reading['timestamp'], $reading['hospital_id']);

		if ( ! $pats ) {
			return FALSE;
		}

		$tmp_pat = $pats[0];
		if ($tmp_pat['out_timestamp'] > $reading['timestamp']) {
			$tmp_pat['t'] = $reading['timestamp'];
		}
		else {
			$tmp_pat['t'] = $tmp_pat['out_timestamp'];
		}

		foreach ($pats as $key => $pat) {
			if ($pat['out_timestamp'] > $reading['timestamp']) {
				$pat['t'] = $reading['timestamp'];
			}
			else {
				$pat['t'] = $pat['out_timestamp'];
			}

			if (($tmp_pat['t']-$tmp_pat['in_timestamp']) < ($pat['t']-$pat['in_timestamp'])) {
				$tmp_pat = $pat;
			}
		}

		$result = array(
			'out_timestamp' => $tmp_pat['t'],
			'in_timestamp' => $tmp_pat['in_timestamp'],
			'longest_stay' => ($tmp_pat['t']-$tmp_pat['in_timestamp'])/3600
			);

		return $result;
	}

	// ED patient volume standardized for annual average
	/*
	 * Fetch patient selection
	 * Devide selection by daily average for ED
	 */
	public function calc_volume_average($reading) {
		$hospitals = $this->CI->seal_hospital->get($reading['hospital_id']);
		$pats = $this->CI->seal_patient->get_for_timestamp($reading['timestamp'], $reading['hospital_id']);

		if ( ! $pats ) {
			return FALSE;
		}

		$result = array(
			'daily_average' => $hospitals[0]['daily_visits'],
			'patients' => count($pats),
			'volume_average' => count($pats)/$hospitals[0]['daily_visits']
			);

		return $result;
	}

	// Calculate the totalt amount of hours spent by patient in the ED the previous period
	/*
	 * Fetch patient selection
	 * Foreach patient sum the amount of time spent in the ED the pervious period
	 * Divide the totalt sum with the daily visits for the hospital
	 */
	public function calc_patient_hours($reading, $period=3600) {
		$pats = $this->CI->seal_patient->get_for_timestamp($reading['timestamp'], $reading['hospital_id']);
		$hospital = $this->CI->seal_hospital->get($reading['hospital_id']);

		if ( ! $pats ) {
			return FALSE;
		}

		$seconds = 0;
		foreach ($pats as $key => $pat) {
			if ($pat['in_timestamp'] < $reading['timestamp']-$period) {
				$in = $reading['timestamp']-$period;
			}
			else {
				$in = $pat['in_timestamp'];
			}

			if ($pat['out_timestamp'] > $reading['timestamp']) {
				$out = $reading['timestamp'];
			}
			else {
				$out = $pat['out_timestamp'];
			}

			$seconds += $out-$in;
		}

		$hours = $seconds/$period;

		$result = array(
			'total_hours' => $hours,
			'num_patients' => count($pats),
			'patient_hours' => $hours/$hospital[0]['daily_visits']
			);

		return $result;
	}

	// Patient index/occupancy
	/*
	 * Fetch patient seletion
	 * Devide number of patients with k[ED-beds]
	 */
	public function calc_occupancy($reading) {
		$events = $this->CI->seal_event->get_for_timestamp($reading['timestamp'], $reading['hospital_id']);
		$hospital = $this->CI->seal_hospital->get($reading['hospital_id']);

		if ( ! $events ) {
			return FALSE;
		}

		$tmp_pat = $events[0];
		$pat_count = 1;
		foreach ($events as $key => $event) {
			//if (in_array($event['priority'], $this->exclude_priority) && $this->discard_prio)
			//	continue;
			
			if ($tmp_pat['patient_id'] !== $event['patient_id']) {
				$pat_count++;
				$tmp_pat = $event;
			}
		}

		if ($pat_count > 0) {
			$result = array(
				'ed_beds' => $hospital[0]['ed_beds'],
				'patients' => $pat_count,
				'occupancy' => $pat_count/$hospital[0]['ed_beds']
				);
		}
		else {
			$result = array(
				'ed_beds' => $hospital[0]['ed_beds'],
				'patients' => $pat_count,
				'occupancy' => $pat_count/$hospital[0]['ed_beds']
				);
		}

		return $result;
	}

	// ED occupancy-rate
	/*
	 * Fetch patients registered in the previous hour
	 * Fetch patient selection
	 * Devid number of [patients with selection, daily average]
	 */
	public function calc_occupancy_rate($reading) {
		$hospitals = $this->CI->seal_hospital->get($reading['hospital_id']);
		$pats = $this->CI->seal_patient->get_registered($reading['timestamp'], $reading['hospital_id']);

		if ( ! $pats ) {
			return array(
				'ed_beds' => $hospitals[0]['ed_beds'],
				'recent_hour' => 0,
				'occupancy_rate' => 0
			);
		}

		$result = array(
			'ed_beds' => $hospitals[0]['ed_beds'],
			'recent_hour' => count($pats),
			'occupancy_rate' => count($pats)/$hospitals[0]['ed_beds']
			);

		return $result;
	}

	// Calculate the part of patients not seen by a md yet
	/*
	 *
	 */
	public function calc_unseen($reading) {
		$events = $this->CI->seal_event->get_before_timestamp($reading['timestamp'], $reading['hospital_id']);
		$hospitals = $this->CI->seal_hospital->get($reading['hospital_id']);

		if ( ! $events )
			return FALSE;

		$tmp_pat = $events[0];
		$seen_count = 0;
		$pat_count = 1;
		foreach ($events as $key => $event) {
			if (in_array($event['priority'], $this->exclude_priority) && $this->discard_prio)
				continue;
			
			if ($tmp_pat['patient_id'] !== $event['patient_id']) {
				if (isset($tmp_pat['doc']) && $tmp_pat['doc'] !== NULL && strlen($tmp_pat['doc']) > 0) {
					$seen_count++;
				}
				$tmp_pat = $event;
				$pat_count++;
			}

			if ( ( ! isset($tmp_pat['doc']) || $tmp_pat['doc'] === NULL) && (isset($event['doc']) && $event['doc'] !== NULL) ) {
				$tmp_pat = $event;
			}
		}

		if ( ( ! isset($tmp_pat['doc']) || $tmp_pat['doc'] === NULL) && (isset($event['doc']) && $event['doc'] !== NULL) ) {
			$seen_count++;
		}

		if ($pat_count > 0) {
			$result = array(
				'unseen_count' => $pat_count-$seen_count,
				'patients' => $pat_count,
				'unseen' => ($pat_count-$seen_count)/$hospitals[0]['ed_beds']
				);
		}
		else {
			$result = array(
				'seen_count' => $pat_count-$seen_count,
				'patients' => $pat_count,
				'unseen' => ($pat_count-$seen_count)/$hospitals[0]['ed_beds']
				);
		}

		return $result;
	}

	// Calculate the number of mds at the emergency based on the ones regisitered in the system
	/*
	 *
	 */
	public function calc_mds($reading) {
		$events = $this->CI->seal_event->get_for_timestamp($reading['timestamp'], $reading['hospital_id']);

		if ( ! $events )
			return array(
					'md_count' => 0,
					'patients' => 0,
					'mds' => 0
				);

		$tmp_pat = $events[0];
		$docs = array();
		$doc_count = 0;
		$pat_count = 1;
		foreach ($events as $key => $event) {
			if (in_array($event['priority'], $this->exclude_priority) && $this->discard_prio)
				continue;
			
			if ($tmp_pat['patient_id'] !== $event['patient_id']) {
				if (isset($tmp_pat['doc']) && $tmp_pat['doc'] !== NULL && strlen($tmp_pat['doc']) > 0) {
					if ( ! in_array($tmp_pat['doc'], $docs) ) {
						array_push($docs, $tmp_pat['doc']);
						$doc_count++;
					}
				}
				$tmp_pat = $event;
				$pat_count++;
			}

			if ( ( ! isset($tmp_pat['doc']) || $tmp_pat['doc'] === NULL) && (isset($event['doc']) && $event['doc'] !== NULL) ) {
				$tmp_pat = $event;
			}
		}

		if ( ( ! isset($tmp_pat['doc']) || $tmp_pat['doc'] === NULL) && (isset($event['doc']) && $event['doc'] !== NULL) ) {
			if ( ! in_array($tmp_pat['doc'], $docs) ) {
				array_push($docs, $tmp_pat['doc']);
				$doc_count++;
			}
		}

		if ($pat_count > 0) {
			$result = array(
				'md_count' => $doc_count,
				'patients' => $pat_count,
				'mds' => ($doc_count)/$pat_count
				);
		}
		else {
			$result = array(
				'md_count' => $doc_count,
				'patients' => $pat_count,
				'mds' => 0
				);
		}

		return $result;
	}

	/*
	 *
	 */
	public function calc_nurses($reading) {
		$events = $this->CI->seal_event->get_for_timestamp($reading['timestamp'], $reading['hospital_id']);

		if ( ! $events )
			return FALSE;

		$tmp_pat = $events[0];
		$nurses = array();
		$nurse_count = 0;
		$pat_count = 1;
		foreach ($events as $key => $event) {
			if (in_array($event['priority'], $this->exclude_priority) && $this->discard_prio)
				continue;
			
			if ($tmp_pat['patient_id'] !== $event['patient_id']) {
				if (isset($tmp_pat['nurse']) && $tmp_pat['nurse'] !== NULL && strlen($tmp_pat['nurse']) > 0) {
					if ( ! in_array($tmp_pat['nurse'], $nurses) ) {
						array_push($nurses, $tmp_pat['nurse']);
						$nurse_count++;
					}
				}
				$tmp_pat = $event;
				$pat_count++;
			}

			if ( ( ! isset($tmp_pat['nurse']) || $tmp_pat['nurse'] === NULL) && (isset($event['nurse']) && $event['nurse'] !== NULL) ) {
				$tmp_pat = $event;
			}
		}

		if ( ( ! isset($tmp_pat['nurse']) || $tmp_pat['nurse'] === NULL) && (isset($event['nurse']) && $event['nurse'] !== NULL) ) {
			if ( ! in_array($tmp_pat['nurse'], $nurses) ) {
				array_push($nurses, $tmp_pat['nurse']);
				$nurse_count++;
			}
		}

		if ($pat_count > 0) {
			$result = array(
				'nurse_count' => $nurse_count,
				'patients' => $pat_count,
				'nurses' => ($nurse_count)/$pat_count
				);
		}
		else {
			$result = array(
				'nurse_count' => $nurse_count,
				'patients' => $pat_count,
				'nurses' => 0
				);
		}

		return $result;
	}

	/*
	 * Calc old-school time to md. Only calculated and counted for patients that have a MD assigned. Pts without MD assigned are not counted.
	 * Only the time to the first assigned MD is counted
	 **/
	public function calc_time_to_md($reading) {
		$patients = $this->CI->seal_patient->get_for_timestamp($reading['timestamp'], $reading['hospital_id']);
		$events = $this->CI->seal_event->get_for_patients($reading['timestamp'], $reading['hospital_id']);
		$res = [];
		$pat_count = $tot_time = $time = $tmp_id = 0;
		foreach ($events as $key => $value) {
			if ($value['patient_id'] !== $tmp_id) {
				$pat_count++;
				$tmp_id = $value['patient_id'];
			}
			if ($value['doc'] !== null) {
				if (isset($res[$value['patient_id']]) && $value['timestamp'] > $res[$value['patient_id']]['timestamp'] ) {
					continue; # We want the earliest registered doctor
				}
				$res[$value['patient_id']] = $value;
				foreach ($patients as $index => $pt) {
					if ($pt['id'] === $value['patient_id']) {
						$t = $value['timestamp'] - $pt['in_timestamp'];
						$res[$value['patient_id']]['time_to_md'] = $t;
						$tot_time += $t;
					}
				}
			}
		}
		$result = array(
			'patients' => $pat_count,
			'total_time' => ($tot_time / 3600 ),
			'time_to_md' => ($tot_time / 3600 / count($res))
		);
		return $result;
	}

	public function calc_mean_model($data) {
		$result = array(
				'constant' => 1.542,
				'high_prio' => $data['high_prio']['high_prio'],
				'awaiting_md' => $data['awaiting_md']['awaiting_md'],
				'patient_hours' => $data['patient_hours']['patient_hours'],
				'occupancy' => $data['occupancy']['occupancy'],
				'mean_model' => 0
			);

		$result['mean_model'] = $result['constant']+(1.799*(float)$result['high_prio'])+(1.391*(float)$result['awaiting_md'])+(14.727*(float)$result['patient_hours'])+(-1.103*(float)$result['occupancy']);

		return $result;
	}

	public function calc_nurse_model($data) {
		$result = array(
				'constant' => 3.441,
				'prio' => $data['prio']['prio'],
				'awaiting_md' => $data['awaiting_md']['awaiting_md'],
				'patient_hours' => $data['patient_hours']['patient_hours'],
				'nurse_model' => 0
			);

		$result['nurse_model'] = $result['constant']+((-0.760)*(float)$result['prio'])+(1.263*(float)$result['awaiting_md'])+(11.453*(float)$result['patient_hours']);

		return $result;
	}

	public function calc_doc_model($data) {
		$result = array(
				'constant' => 4.027,
				'prio' => $data['prio']['prio'],
				'patient_hours' => $data['patient_hours']['patient_hours'],
				'doc_model' => 0
			);

		$result['doc_model'] = $result['constant']+((-0.761)*(float)$result['prio'])+(9.739*(float)$result['patient_hours']);

		return $result;
	}

	public function calc_full_model($data) {
		$r = array(
			'constant' => 3.283
			);

		foreach ($data as $key => $val) {
			$r[$key] = (float)$val[$key];
		}

		$r['full_model'] = $r['constant']+((-0.291)*$r['prio'])+((-0.259)*$r['triage_prio'])+(0.776*$r['high_prio'])+(1.375*$r['awaiting_md'])+(0.053*$r['average_time'])+(0.005*$r['longest_stay'])+(16.185*$r['patient_hours'])+((-0.888)*$r['occupancy'])+(0.351*$r['occupancy_rate'])+((-1.723)*$r['volume_average'])+(0.954*$r['admit_index'])+((-0.217)*$r['unseen'])+((-0.654)*$r['mds'])+((-0.549)*$r['nurses']);
		
		return $r;
	}
}
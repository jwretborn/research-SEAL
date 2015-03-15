<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Analyze extends DI_Controller {

	var $map;

	public function __construct() {
		parent::__construct();

		$this->map = array(
			'1' => 'doc',
			'2' => 'nurse',
			'3' => 'dal',
			'4' => 'dask',
			'5' => 'clerk'
		);

		$this->exclude_clerks = TRUE;
		$this->exclude_no_doc_or_nurse = TRUE;

		$this->scores = array(0, 0, 0, 0, 0, 0, 0);
	}

	/*
	 * Calc data characteristics based on readings between a given timepoint
	 */
	public function reading_get($start, $end='', $hospital='') {
		$this->load->library('seal_readings');

		if (strtotime($start))
			$start = strtotime($start);

		if ($end !== '' && strtotime($end))
			$end = strtotime($end);

		$readings = $this->seal_readings->get_with_form_count($start, $end, $hospital);

		$stats = array(
			'total' => count($readings[0]),
			'included' => 0,
			'empty' => array(
				'total' => 0,
				'hours' => array(
					'08' => 0,
					'12' => 0,
					'16' => 0,
					'20' => 0,
					'23' => 0
				)
			),
			'complete' => 0,
			'partial' => array(
				'total' => 0,
				'no_doc' => 0,
				'no_nurse' => 0
			),
			'incomplete' => array(
				'total' => 0,
				'hours' => array(
					'08' => 0,
					'12' => 0,
					'16' => 0,
					'20' => 0,
					'23' => 0
				),
				'id' => array()
			)
		);

		foreach (array('pos', 'tot', 'avg') as $index => $key) {
			foreach ($this->map as $k => $m) {
				$stats[$key][$m] = 0;
			}
		}

		// Calc some stats from the forms
		foreach ($readings[0] as $key => $reading) {
			$readings[0][$key]['hour'] = date('H', $reading['timestamp']);

			// Filled or not
			if (empty($reading['forms'])) {
				$stats['empty']['total'] += 1;
				$stats['empty']['hours'][date('H', $reading['timestamp'])] += 1;
				continue;
			}
			else if (count($reading['forms']) === 5) {
				$stats['complete'] += 1;
			}
			else {
				$stats['partial']['total'] += 1;
				if ( ! isset($reading['forms']['1']) && ! isset($reading['forms']['3']) ) {
					$stats['partial']['no_doc'] += 1;
					$stats['incomplete']['total'] += 1;
					$stats['incomplete']['hours'][date('H', $reading['timestamp'])] += 1;
					array_push($stats['incomplete']['id'], $reading['id']);
					continue; // Do not do position, totalt or average count on incompletes
				}
				if ( ! isset($reading['forms']['2']) && ! isset($reading['forms']['4']) ) {
					$stats['partial']['no_nurse'] += 1;
					$stats['incomplete']['total'] += 1;
					$stats['incomplete']['hours'][date('H', $reading['timestamp'])] += 1;
					continue;
				}
			}

			$stats['included'] += 1;

			// detail
			foreach ($this->map as $k => $m) {
				if (isset($reading['forms'][$k])) {
					$stats['pos'][$m] += 1;
					$stats['tot'][$m] += $reading['forms'][$k];
				}
			}

		}

		foreach ($this->map as $k => $m) {
			if ($stats['pos'][$m] > 0)
				$stats['avg'][$m] = round($stats['tot'][$m]/$stats['pos'][$m], 1);
		}

		//array_push($readings[0], $stats);

		return $this->response($stats, $readings[1]);
	}

	/*
	 * Calc form characteristics between given timpoints
	 */
	public function form_get($start, $end='', $hospital) {
		$this->load->library('seal_forms');

		if (strtotime($start))
			$start = strtotime($start);

		if ($end !== '' && strtotime($end))
			$end = strtotime($end);

		if ($end !== '') {
			$args = array(
				'timestamp >=' => $start,
				'timestamp <=' => $end
				);
		}
		else {
			$args = array('timestamp' => $start);
		}

		$forms = $this->seal_forms->get_with_reading($args);

		$stats = array(
			'total' => 0,
			'time' => array(
				'max' => 0,
				'min' => 1000000,
				'average' => 0,
				'excluded' => 0
			),
			'excluded' => array(
				'tot' => 0,
				'clerks' => 0,
				'incomplete' => 0,
				'time' => 0
			) 
		);

		foreach (array('count', 'sum', 'avg', 'tot') as $k => $m) {
			foreach ($this->scores as $index => $val) {
				$stats[$m][$index] = 0;
			}
		}

		$tsum = 0; // Sum of time differences
		$cread = $forms[0][0]['reading_id']; // current reading
		$fcount = 0; // Form count for this reading
		$fsum = 0; // form sum for this reading
		$count = 0; // total number of counted forms
		$doc = FALSE;
		$nurse = FALSE;
		foreach ($forms[0] as $key => $form) {

			$diff = $form['created']-$form['reading_timestamp']; // Time diff

			if (($this->exclude_clerks && $form['type'] == '5') || abs($diff) > (3600*2)) {
				$stats['excluded']['tot'] += 1;
				if (abs($diff) > (3600*2))
					$stats['excluded']['time'] += 1;
				else
					$stats['excluded']['clerks'] += 1;
				continue;
			}

			if ($form['reading_id'] != $cread) { // new reading, do calculations
				if ($fcount > 0) {
					// Exlude readings that are incomplete (no doc or no nurse answered)
					if ($this->exclude_no_doc_or_nurse && ( ! $doc || ! $nurse)) {
						$count -= $fcount; // This will fuckup time-counting
						$stats['excluded']['incomplete'] += $fcount;
						$stats['excluded']['tot'] += $fcount;
					}
					else {
						$stats['count'][$fsum/$fcount] += 1;
						$stats['sum'][$fsum/$fcount] += $fcount;
					}
				}

				$fsum = 0;
				$fcount = 0;
				$doc = FALSE;
				$nurse = FALSE;
			}

			// Check for doc and nurse
			if ( ( ! $doc ) && (intval($form['type']) === 1 || intval($form['type']) === 3) ) // doc or dal
				$doc = TRUE;
			if ( ( ! $nurse) && ( intval($form['type']) === 2 || intval($form['type']) === 4)) // nurse or dask
				$nurse = TRUE;

			$stats['tot'][intval($form['question_1'])] += 1;
			$fcount += 1;
			$fsum += intval($form['question_1']);
			$cread = $form['reading_id'];

			$tsum += abs($diff); // Add the absolut timediff to the sum

			// Time calculations
			if ($diff > $stats['time']['max']) {
				$stats['time']['max'] = $form['created']-$form['reading_timestamp'];
			}
			else if ($diff < $stats['time']['min']) {
				$stats['time']['min'] = $form['created']-$form['reading_timestamp'];
			}

			$count += 1;
		}

		// Calc avg. number of scores
		for ($i=1; $i<7; $i++) {
			$stats['avg'][$i] = round($stats['sum'][$i]/$stats['count'][$i], 1);
		}

		$stats['total'] = $count;
		$stats['time']['average'] = $stats['time']['min'] + ($tsum/$count);

		return $this->response($stats, $forms[1]);
	}

}
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class seal_forms extends DI_Simplelib {

	public function __construct() {
		$this->CI =& get_instance();

		$this->CI->load->model('seal_form', 'seal_form');
		$this->rest_model = 'seal_form';
	}

	public function get($args='') {
		return parent::get($args);
	}

	public function get_with_reading($args='') {
		$forms = $this->CI->seal_form->get_join_readings($args);
		if ($forms) {
			return array($forms, 200);
		}
		else {
			return array($forms, 400);
		}
	}

	public function calc_assessment($reading) {
		$forms = $this->CI->seal_form->get(array('reading_id' => $reading['id']));

		if ( ! $forms ) {
			return FALSE;
		}
		else {
			$result = array(
				'doc_assess' => 0,
				'nurse_assess' => 0,
				'mean_assess' => 0,
				'doc_count' => 0,
				'nurse_count' => 0,
				'doc_q2' => 0,
				'nurse_q2' => 0
				);
			foreach ($forms as $key => $form) {
				if (ctype_digit($form['question_1'])) {
					if ($form['type'] === '1' || $form['type'] === '3') {
						$result['doc_assess'] += $form['question_1'];
						$result['doc_count']++;
						(ctype_digit($form['question_2'])) ? $result['doc_q2'] += $form['question_2'] : '' ;
					}
					else if ($form['type'] === '2' || $form['type'] === '4') {
						$result['nurse_assess'] += $form['question_1'];
						$result['nurse_count']++;
						(ctype_digit($form['question_2'])) ? $result['nurse_q2'] += $form['question_2'] : '';
					}
				}
			}
			$sum = 0;
			$count = 0;
			if ($result['doc_assess'] > 0) {
				$sum = $sum+$result['doc_assess'];
				$count++;
				$result['doc_assess'] = $result['doc_assess']/$result['doc_count'];
			}
			if ($result['nurse_assess'] > 0) {
				$sum = $sum+$result['nurse_assess'];
				$count++;
				$result['nurse_assess'] = $result['nurse_assess']/$result['nurse_count'];
			}
			if ($count > 0) {
				$result['mean_assess'] = $sum / ($result['doc_count']+$result['nurse_count']);
				return $result;
			}
			else {
				return FALSE;
			}
		}
	}

	public function post($args='') {
		return parent::post($args);
	}
}
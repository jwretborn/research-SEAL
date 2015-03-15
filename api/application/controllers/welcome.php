<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function index($id=2)
	{
		$this->load->model('seal_reading');
		$this->load->model('seal_hospital');
		$this->load->model('seal_form');
		$this->load->helper('url');

		$data = array('readings' => array());
		$where = array('hospital_id' => $id);
		$swe_days = array('Mon' => 'Mån', 'Tue' => 'Tis', 'Wed' => 'Ons', 'Thu' => 'Tors', 'Fri' => 'Fre', 'Sat' => 'Lör', 'Sun' => 'Sön');

		$h = '';
		$hospitals = $this->seal_hospital->get($id);

		$readings = $this->seal_reading->get($where, FALSE, 'timestamp ASC');
		$time = time();

		foreach ($readings as $key => $reading) {
			if (($time < ($reading['timestamp']-(60*60*24)) && count($data['readings']) > 8) || ($time > $reading['timestamp']+(60*60*12)))
				continue;

			$data['readings'][$key]['day'] = strtr((date('H:i', $reading['timestamp']) == '00:00' ? date('D d/n', $reading['timestamp']-1) : date('D d/n', $reading['timestamp'])), $swe_days);
			$data['readings'][$key]['hour'] = strtr(date('H:i', $reading['timestamp']), array('00:00' => '24:00'));
			$data['readings'][$key]['id'] = $reading['id'];

			if ($time < $reading['timestamp']-2400) {
				$data['readings'][$key]['code'] = 0; // Due
				$data['readings'][$key]['status'] = 'Kommande';
			}
			else if ($time > $reading['timestamp']+3600) {
				$data['readings'][$key]['code'] = 2; // Overdue

				$str = FALSE;
				if ( ! $str ) {
					$data['readings'][$key]['status'] = 'Klar';
					$data['readings'][$key]['code'] = 3;
				}
				else {
					$data['readings'][$key]['status'] = $str . 'ej ifylld';
				}
			}
			else {
				$data['readings'][$key]['code'] = 1; // Active
				$data['readings'][$key]['status'] = 'Aktiv';

				$str = TRUE;
				$forms = $this->seal_form->get(array('reading_id' => $reading['id']));
			}
		}

		$data['status'] = array('due', 'active', 'overdue', 'finished');
		$data['swe_days'] = array('Monday' => 'Måndag', 'Tuesday' => 'Tisdag', 'Wednesday' => 'Onsdag', 'Thursday' => 'Torsdag', 'Friday' => 'Fredag', 'Saturday' => 'Lördag', 'Sunday' => 'Söndag');
		
		return $this->load->view('web_list', $data);
	}

	public function reading($id='') {
		$this->load->model('seal_reading');
		$this->load->model('seal_form');
		$this->load->helper('url');


		if (($reading = $this->input->post('reading'))) {
			$str = FALSE;
			if ( ! ($type = $this->input->post('type')) ) {
				$str = 'Arbetsroll måste fyllas i';
			}

			if ( ! ($q1 = $this->input->post('question_1')) ) {
				if ($str)
					$str .= "<br />";
				$str .= 'Fråga 1 måste fyllas i.';
			}

			if ( ! ($q2 = $this->input->post('question_2')) ) {
				if ($str)
					$str .= "<br />";
				$str .= 'Fråga 2 måste fyllas i.';
			}

			if ($str) {
				$data['alert'] = array('short' => 'Fel!', 'message' => $str, 'type' => '');
			}
			else {
				$create = array(
					'reading_id' => $reading,
					'type' => $type,
					'question_1' => $q1,
					'question_2' => $q2,
					'reading_id' => $id,
					'created' => time()
				);
				
				if ($this->seal_form->create($create)) {
					$data['alert'] = array('message' => 'Formuläret har sparats. Tack för att du tog dig tid att fylla i det.', 'type' => 'alert-success');
				}
				else {
					$data['alert'] = array('message' => 'Lyckades inte spara, var god försök igen.', 'type' => 'alert');
				}
			}
		}

		if ($id !== '') {
			$readings = $this->seal_reading->get($id);
			$forms = $this->seal_form->get(array('reading_id' => $id));
			$data['disabled'] = array(FALSE, FALSE, FALSE);
			/*
			 * Not used when we have more than one nurse and doc
			foreach ($forms as $index => $form) {
				$data['disabled'][$form['type']] = FALSE;
			}

			if ($data['disabled'][1] && $data['disabled'][2])
				return $this->load->view('web_success');
			*/
		}

		$data['reading'] = $readings[0];
		$this->load->view('web_form', $data);
	}

	public function form($id='')
	{
		$this->load->model('seal_reading');
		$this->load->model('seal_form');
		$this->load->helper('url');


		if (($reading = $this->input->post('reading'))) {
			$str = FALSE;
			if ( ! ($type = $this->input->post('type')) ) {
				$str = 'Arbetsroll måste fyllas i';
			}

			if ( ! ($q1 = $this->input->post('question_1')) ) {
				if ($str)
					$str .= "<br />";
				$str .= 'Fråga 1 måste fyllas i.';
			}

			if ( ! ($q2 = $this->input->post('question_2')) ) {
				if ($str)
					$str .= "<br />";
				$str .= 'Fråga 2 måste fyllas i.';
			}

			if ($str) {
				$data['alert'] = array('short' => 'Fel!', 'message' => $str, 'type' => '');
			}
			else {
				$update = array(
					'question_1' => $q1,
					'question_2' => $q2,
					'reading_id' => $id,
					'created' => time()
				);
				
				$form = $this->seal_form->get(array('reading_id' => $id, 'type' => $this->input->post('type')));
				if (isset($form)) {
					$this->seal_form->update($form[0]['id'], $update);
					$data['alert'] = array('message' => 'Formuläret har sparats. Tack för att du tog dig tid att fylla i det.', 'type' => 'alert-success');
				}
				else {
					$data['alert'] = array('message' => 'Lyckades inte spara, var god försök igen.', 'type' => 'alert');
				}
			}
		}

		if ($id !== '') {
			$readings = $this->seal_reading->get($id);
			$forms = $this->seal_form->get(array('reading_id' => $id));
			$data['disabled'] = array();
			foreach ($forms as $index => $form) {
				$data['disabled'][$form['type']] = (ctype_digit($form['question_1']) ? TRUE : FALSE);
			}

			if ($data['disabled'][1] && $data['disabled'][2])
				return $this->load->view('web_success');
		}

		$data['reading'] = $readings[0];
		$this->load->view('web_form', $data);
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
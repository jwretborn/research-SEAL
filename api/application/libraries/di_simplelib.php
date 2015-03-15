<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class DI_Simplelib {

	public $rest_model = 'rest_model';

	public function __construct() {
		$this->CI =& get_instance();
	}

	public function get($where, $return_atom = FALSE, $where_like = FALSE, $order_by = FALSE) {		
		if($where && (is_array($where) || ctype_digit($where))) { // $where is where-array or ID for table
			$data = $this->CI->{$this->rest_model}->get($where, FALSE, $order_by, $where_like, TRUE);
			if(!$data) { // No objects found, error
				if($return_atom === TRUE)
					return array(array('error' => 'The object could not be found'), 404);
				else
					return array(array(), 200);
			}
		}
		else {
			$data = $this->CI->{$this->rest_model}->get(array(), FALSE, $order_by, $where_like, TRUE); // No filtering, return all
			if(!$data) {
				return array(array(), 200);
			}
		}
		if($return_atom === TRUE && sizeof($data) == 1) {
			return array(array_pop($data), 200); // 200 being the HTTP response code			
		}
		return array($data, 200); // 200 being the HTTP response code
	}

	public function post($data = array()) {
		if (($valid = $this->validate()) !== TRUE) 
			return $valid;

		if ($created_id = $this->CI->{$this->rest_model}->create($data)) {
			$result = $this->CI->{$this->rest_model}->get($created_id);
        	return array(array_pop($result), 201); // 201 being the HTTP response code
        }
		else {
			return array('', 500);
		}
	}

	public function put($id='', $data) {		
		if (($valid = $this->validate()) !== TRUE) 
			return $valid;
		
		if ( $id === '') {
			$id = $this->CI->uri->segments[count($this->CI->uri->segments)];
		}
		
		try {
			$this->CI->{$this->rest_model}->update($id, $data);
			return array($data, 201); // 200 being the HTTP response code
		} catch (Exception $e)
		{
			return array($e->getMessage(), 500);
		}
	}

	public function delete($args) {		
		$args = explode("/", $args);

		if ( empty($args) && ( ! empty($args) && ! ctype_digit((string)$args[0]) ) )
		{
			return array('No id given', 500);
		}
		else
		{
			if ($this->CI->{$this->rest_model}->remove($args[0]))
			{
        		$message = array('id' => $args[0], 'message' => 'DELETED!');
            	
        		return array($message, 200); // 200 being the HTTP response code
    		}
		}
	}

	public function validate() {
		$this->CI->load->library('form_validation');
		
		if (isset($this->CI->{$this->rest_model}->validation_rules)) {
			$this->CI->form_validation->set_rules($this->CI->{$this->rest_model}->validation_rules);
			if ( ! $this->CI->form_validation->run() )
			{
				$errors = array('errors' => array());
				foreach ($this->CI->{$this->rest_model}->validation_rules as $rule)
					if ($this->CI->form_validation->error($rule['field']) !== '')
						array_push($errors['errors'], $this->CI->form_validation->error($rule['field'], ' ', ' '));
				
				return array($errors, 400);
			}
		}

		return true;
	}
}
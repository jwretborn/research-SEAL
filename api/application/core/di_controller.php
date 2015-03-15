<?php defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
require_once(APPPATH.'core/REST_Controller.php');

class DI_Controller extends REST_Controller
{
	
	private $max_img_width = 1280;

	function __construct() {
		parent::__construct();

		$this->load->driver('cache', array('adapter' => 'file'));
	}

	/*
	 * $where can be an ID or an where-array
	*/
	function get($where, $d, $return_atom = FALSE, $where_like = FALSE, $order_by = FALSE)
    {
		$this->load->model($d['model'], 'rest_model', TRUE);
		if($where && (is_array($where) || ctype_digit($where))) { // $where is where-array or ID for table
			$data = $this->rest_model->get($where, FALSE, $order_by, $where_like, TRUE);
			if(!$data) { // No objects found, error
				if($return_atom === TRUE)
					return $this->response(array('error' => 'The object could not be found'), 404);
				else
					return $this->response(array(), 200);
			}
		}
		else {
			$data = $this->rest_model->get(array(), FALSE, $order_by, $where_like, TRUE); // No filtering, return all
			if(!$data) {
				return $this->response(array(), 200);
			}
		}
		if($return_atom === TRUE && sizeof($data) == 1) {
			return $this->response(array_pop($data), 200); // 200 being the HTTP response code			
		}
		return $this->response($data, 200); // 200 being the HTTP response code
    }
    
	// Set response to false to run several post() in same request
    function post($d, $additional_data = array(), $response = TRUE)
    {
		$this->load->model($d['model']);
		$this->load->library('form_validation');
		
		$rest_model = $d["model"];

		$data = array();
		$data = array_merge($data, $additional_data);

        foreach ($d['allowed_keys'] as $val)
        {
			$data[$val] = $this->body($val);
			$_POST[$val] = $data[$val];
		}
		
		if (isset($this->{$rest_model}->validation_rules)) {
			$this->form_validation->set_rules($this->{$rest_model}->validation_rules);
			if ( ! $this->form_validation->run() )
			{
				$errors = array('errors' => array());
				foreach ($this->{$rest_model}->validation_rules as $rule) {
					if ($this->form_validation->error($rule['field']) !== '')
						array_push($errors['errors'], $this->form_validation->error($rule['field'], ' ', ' '));
				}
				
				if ($response === TRUE)
					return $this->response($errors, 400);
				else
					return array($errors, 400);
			}
		}
		
		if ($created_id = $this->{$rest_model}->create($data))
		{
			$data = $this->{$rest_model}->get($created_id);
			$this->location = current_url() . "/" . $created_id;
			if($response === TRUE)
        		return $this->response(array_pop($data), 201); // 201 being the HTTP response code
			else
				return array(array_pop($data), 201);
		}
		else
		{
			if($response === TRUE)
				return $this->response('', 500);
			else
				return array('', 500);
		}
    }

	function put($d, $additional_data = array())
	{
		$this->load->model($d['model']);
		$this->load->library('form_validation');
		
		$rest_model = $d['model'];
		
		$id = $this->uri->segments[count($this->uri->segments)];

		$data = array();
		$data = array_merge($data, $additional_data);
		
        foreach ($d['allowed_keys'] as $val)
        {
			$data[$val] = $this->body($val);
			$_POST[$val] = $data[$val];
		}
		
		if (isset($this->{$rest_model}->validation_rules)) {
			$this->form_validation->set_rules($this->{$rest_model}->validation_rules);
			if ( ! $this->form_validation->run() )
			{
				$errors = array('errors' => array());
				foreach ($this->{$rest_model}->validation_rules as $rule) {
					if ($this->form_validation->error($rule['field']) !== '')
						array_push($errors['errors'], $this->form_validation->error($rule['field'], ' ', ' '));
				}
				
				return $this->response($errors, 400);
			}
		}
		
		try {
			$this->{$rest_model}->update($id, $data);
        	$this->location = current_url() . "/" . $id;
			return $this->response($data, 201); // 200 being the HTTP response code
		} catch (Exception $e)
		{
			return $this->response($e->getMessage(), 500);
		}
	}
    
    function delete($args, $d)
    {
		$this->load->model($d['model'], 'rest_model', TRUE);
		
		$args = explode("/", $args);

		if ( empty($args) && ( ! empty($args) && ! ctype_digit((string)$args[0]) ) )
		{
			$this->response('No id given', 500);
		}
		else
		{
			if ($this->rest_model->remove($args[0]))
			{
        		$message = array('id' => $args[0], 'message' => 'DELETED!');
            	
        		$this->response($message, 200); // 200 being the HTTP response code
    		}
		}
	}
	
	function body($key=NULL, $xss_clean=TRUE)
	{
		if(!$this->request->body) 
			$this->request->body = array();

		if ($key === NULL)
		{
			return $this->request->body;
		}

		if (array_key_exists($key, $this->request->body))
		{
			return $this->_xss_clean($this->request->body[$key], $xss_clean);
		}
		else
		{
			return FALSE;
		}
	}
	
	function set_body($key, $val)
	{
		$this->request->body[$key] = $val;
	}
	
	public function _is_admin($auth_id = FALSE) {
		if($auth_id !== FALSE) {
			$this->load->model("auth_model");
			$group_data = $this->auth_model->get_groups($auth_id);
			$groups = array();
			foreach($group_data as $d)
				array_push($groups, $d['group_id']);
		}
		else if ( ! ($groups = $this->session->userdata("groups")) )
			$groups = array();
		return in_array($this->config->item("admin_group"), $groups) ? TRUE : FALSE; // Admin
	}
	
	public function _is_coop($coop_id = FALSE) {
		if ( ! ($groups = $this->session->userdata("groups")) )
			$groups = array();
		
		if ($coop_id !== FALSE) {
			$this->load->model("user_model");
			if($this->session->userdata("auth_id")) {
				$user = array_pop($this->user_model->get(array("auth_id" => $this->session->userdata("auth_id"))));
				if ($coop_id !== $user['coop_id'])
					return FALSE;
			}
		}
		
		return in_array($this->config->item("coop_group"), $groups) ? TRUE : FALSE; // Coop-Admin
	}
	
	public function _is_user($user_id = FALSE) {
		if ( ! ($groups = $this->session->userdata("groups")) )
			$groups = array();
		
		if ($user_id !== FALSE) {
			$this->load->model("user_model");

			if($this->session->userdata("auth_id")) {
				$user = array_pop($this->user_model->get(array("auth_id" => $this->session->userdata("auth_id"))));
				if ($user_id == $user['id'])
					return TRUE;
			}
			return FALSE;
		}
		
		return in_array($this->config->item('user_group'), $groups) ? TRUE : FALSE; // User
	}
	
	public function _is_owner($params) {
		$this->load->model("user_model");
		$user = array_pop($this->user_model->get(array("auth_id" => $this->session->userdata("auth_id"))));
		
		$this->load->model($params['model'], 'object_model');
		if (($m = $this->object_model->get(array('id' => $this->uri->segments[count($this->uri->segments)], 'user_id' => $user['id']))) !== FALSE)
			return TRUE;
		else 
			return FALSE;
	}

	public function auth($level='user') {
		switch ($level) {
			case 'user' :
				return ($this->session->userdata('logged_in') === TRUE) ? TRUE : FALSE;
				break;
			case 'admin' :
				if ($this->session->userdata('logged_in') !== TRUE)
					return FALSE;
				$id = $this->session->userdata('customerId');

				$this->load->model('di_customer_model');

				if ( ! ($customer = $this->di_customer_model->get($id)) )
					return FALSE;

				return $customer[0]['admin'] === 1 ? TRUE : FALSE;

				break;
			default :
				return FALSE;
				break;
		}
	}
	
	function permission_denied() {
		return $this->response(array('error' => 'Permission denied.'), 403);
	}

	function bad_request() {
		return $this->response(array('error' => 'Bad Request'), 400);
	}
	
	protected function upload_get_image($resize_to = FALSE)
	{
		$this->load->config('di_images');
		if(isset($_FILES['images']) && isset($_POST['upload_code']))
		{
			if($_FILES['images']['size'] > $this->config->item('images_max_image_size')) {
				return $this->response(array("error" => "The images it too large, the maximum size is " . $this->config->item('max_image_size') . " bytes."), 502);
			}
			$this->load->library('image_lib');
			try
			{
				if ($auth_id = $this->image_lib->validate_upload_code($this->input->post('upload_code')))
				{
					$image_id = $this->image_lib->upload_image('', $auth_id);
					if(!$image_id) {
						LOG_ERROR("Could not handle uploaded file");
						$this->response(array("error" => "Could not handle uploaded file"), 500);
					}

					$image = $this->image->get($image_id);

					if(is_numeric($resize_to))
					{
						$img_width = min($resize_to, $this->max_img_width); // Resize to this width
						if (file_exists($image[0]['image']) && $img_width && $img_width < $image[0]['image_width'])
						{
							$img_height = ($img_width/$image[0]['image_width'])*$image[0]['image_height'];
							$this->image_lib->resize_image($image[0]['image'], $img_width, $img_height, FALSE);
						}
					}
					$this->load->model("image", "image_model");
					if($image = $this->image_model->get($image_id))
						return array_pop($image);
					return array("id" => $image_id);
				}
				else {
					LOG_ERROR("Upload code validation failed!");
					$this->response(array("error" => "Upload code validation failed!"), 500);
				}
			} catch (Exception $e)
			{
				LOG_ERROR($e->getMessage());
				$this->response(array("error" => $e->getMessage()), 500);
			}
		}
		$this->response(array("error" => "Invalid parameters passed"), 403);
	}
	
	protected function upload_get_user()
	{
		if(isset($_POST['upload_code'])) {
			$upload_code = $this->input->post('upload_code');
			$this->load->model('image_upload_codes');
			$this->load->model('auth_model');
			
			$result = $this->image_upload_codes->get(array('upload_code' => $upload_code));
			if($result && $result[0] && $result[0]['auth_id']) {
				$auth_id = $result[0]['auth_id'];
				$user = $this->auth_model->get_user($auth_id);
				return $user;
			}
		}
		return FALSE;
	}
}
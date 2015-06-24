<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

	Class DI_Model extends CI_Model
	{
		const DI_COMMENTS = 'comments';
		
		private $obj_table;
		private $obj_key;
		private $obj_type_table;
		private $obj_type_key;
		protected $table;
		protected $key;
		protected $has_many;
		protected $field_prefix;
		protected $field_wildcards;
		public $validation_rules;
		
		function __construct()
		{
			parent::__construct();
			
			$this->obj_table = 'di_objects';
			$this->obj_key = 'id';
			$this->obj_type_table = 'di_object_types';
			$this->obj_type_key = 'id';
			$this->table = "di_objects";
			$this->key = 'id';
			$this->has_many = '';
			
			$this->field_prefix = 'field_';
			$this->field_wildcards = array();
			
			$this->validation_rules = array();
		}
		
		/**
		 * Converts any field from the database to the wildcard field
		 *
		 * @author Jens Wretborn
		 * @access public
		 * @return array
		 */
		public function convert_to_wildcard_fields($items)
		{
			foreach ($items as $index => $item)
			{
				foreach ($item as $field => $value)
				{
					if (isset($this->field_wildcards[$field]))
					{
						$items[$index][$this->field_wildcards[$field]] = $value;
						unset($items[$index][$field]);
					}
				}
			}
			
			return $items;
		}
		
		/**
		* Creates a new di_object from given data
		*
		* @author Jens Wretborn
		* @since 0.0.1
		* @version 0.0.1
		* @category alpha
		* @access public
		* @param array Array with object data
		* @param boolean Set this to false if you do not want to create an entry in the di_objects table
		* @return integer|boolean False if an error occured, otherwise the id of the new object is returned
		*/
		public function create($object = array(), $create_conn = TRUE) 
		{
			if ($create_conn && ! isset($object['created']))
				$object['created'] = time();
			
			// Insert the data in to the table
			if ( ! empty($object) && $this->db->insert($this->table, $object))
			{
				$id = '';
				$obj_type_id = '';
				
				$this->db->select('MAX('.$this->key.') as '. $this->key, TRUE);
				$query = $this->db->get($this->table);
				if ($query->num_rows() > 0)
				{
					$result = $query->result_array();
					$id = $result[0][$this->key];
				}
				else
				{
					return FALSE;
				}
				
				// We do not want to create an entry in the di_objects table
				if ( ! $create_conn )
				{
					return $id;
				}
				
				// Get the object type id based on the table
				$this->db->where('table', $this->table);
				$query = $this->db->get($this->obj_type_table);
				if ($query->num_rows() > 0)
				{ // The entry for the table does already exists
					$tmp_result = $query->result_array();
					$obj_type_id = $tmp_result[0][$this->obj_type_key];
				}
				else
				{ // No entry for this table was made, let's create it
					$this->db->insert($this->obj_type_table, array('type' => $this->table, 'table' => $this->table));
					$this->db->where('table', $this->table);
					$query = $this->db->get($this->obj_type_table);
					if ($query->num_rows() > 0)
					{
						$tmp_result = $query->result_array();
						$obj_type_id = $tmp_result[0][$this->obj_type_key];
					}
					else
					{
						return FALSE;
					}
				}
				// Add the new object to the di_objects table
				if ($this->db->insert($this->obj_table, array('id_in_table' => $id, 'object_type_id' => $obj_type_id)))
				{
					$this->db->select($this->obj_key);
					$this->db->where(array('id_in_table' => $id, 'object_type_id' => $obj_type_id));
					$query = $this->db->get($this->obj_table);
					if ($query->num_rows() > 0)
					{
						$result = $query->result_array();
						$obj_id = $result[0][$this->key];
					}
					else
					{
						$this->db->delete($this->table, array($this->key => $id));
						return FALSE;
					}
				}
				
				// Update the table with the new object id
				$this->update($id, array('object_id' => $obj_id));
				
				return $id;
			}
			else
			{
				return FALSE;
			}
		}
		
		/**
		 *  Fetches post data for every column in the database with the "field_" prefix and returns them in an array.
		 *
		 * @author Jens Wretborn
		 * @access public
		 * @return array
		 */
		public function fetch_data_from_post()
		{
			$fields = array();
			$data = array(array());
			
			// Fetch all fields from the database
			$this->db->where($this->key, TRUE);
			$this->db->limit(1);
			$q = $this->db->get($this->table);
			if ($q->num_rows() < 1)
			{ // No data in the table
				return FALSE;
			}

			$result = $q->result_array();
						
			// Select all fields that's fetchable
			foreach ($result[0] as $column_name => $value)
			{
				if (substr($column_name, 0, strlen($this->field_prefix)) == $this->field_prefix)
				{
					array_push($fields, $column_name);
				}
			}
			
			// Store field data from post into an array and return it
			foreach ($fields as $index => $field_name)
			{
				if (isset($this->field_wildcards[$field_name]))
				{
					$name = $this->field_wildcards[$field_name];
				}
				else
				{
					$name = $field_name;
				}
				
				if ($value = $this->input->post($name))
				{
					if (is_array($value))
					{
						foreach ($value as $key => $val)
						{
							$data[$key][$field_name] = $val;
						}
					}
					else
					{
						$data[0][$field_name] = $value;
					}
				}
				else if ((isset($_POST[$name]) && $_POST[$name] == ""))
				{
					$value = $_POST[$name];
					if (is_array($value))
					{
						foreach ($value as $key => $val)
						{
							$data[$key][$field_name] = $val;
						}
					}
					else
					{
						$data[0][$field_name] = $value;
					}
				}
			}
			
			return $data;
		}
		
		/**
		 * This function is used to fetch data from $_POST based on $this->validation_rules()
		 *
		 * @author Jens Wretborn
		 * @return mixed
		 */
		public function fetch_postdata()
		{
			$data = array();
			
			foreach ($this->validation_rules as $index => $rule)
			{
				if (isset($_POST[$rule['field']]))
				{
					$data[$rule['field']] = $this->input->post($rule['field']);
				}
			}
			
			return $data;
		}
	
		/**
		 * Fetches one or several objects for a given search criteria
		 *
		 * @author Jens Wretborn
		 * @since 1.0.0
		 * @version 1.0.0
		 * @category alpha
		 * @access public
		 * @param integer|array object id or array with properties can be fed.
		 * @return boolean|array
		 **/
		public function get($where = array(), $include_object = FALSE, $order_by = FALSE, $where_like = FALSE, $use_get_keys = FALSE)
		{
			if (isset($this->allowed_get_keys) && $use_get_keys === TRUE)
				$select = implode(' ,', $this->allowed_get_keys);
			else
				$select = $this->table.'.*';
			
			if ($include_object)
			{	
				$this->db->select($select.', '.$this->table.'.'.$this->key.' as id, di_objects.id as object_id, di_object_types.type as object_type');
			}
			else {
				$this->db->select($select);
			}

			if ($order_by === FALSE) {
				$this->db->order_by($this->table.'.'.$this->key, "desc");
			}
			else {
				$this->db->order_by($order_by);
			}
			
			if ( ! is_array($where) && isset($where) && ctype_digit((string)$where))
			{
				if($where_like === TRUE)
					$this->db->where($this->table.'.'.$this->key, $where);
				else
					$this->db->like($this->table.'.'.$this->key, $where);
			}
			else if (is_array($where) && ! empty($where))
			{
				if($where_like === TRUE)
					$this->db->like($where);
				else
					$this->db->where($where);
			}

			if ($include_object)
			{
				$this->db->join('di_objects', $this->table.'.id = di_objects.id_in_table');
				$this->db->join('di_object_types', 'di_objects.object_type_id = di_object_types.id');
				$this->db->where('di_object_types.table', $this->table);
			}
			
			$query = $this->db->get($this->table);

			if ($query->num_rows() > 0)
			{
				$result = $query->result_array();
			}
			else
			{
				$result = FALSE;
			}
			
			return $result;
		}
		
		public function get_object($where)
		{
			$this->db->select('*, di_objects.id as id, di_object_types.id as object_type_id, di_object_types.type as object_type');
			
			if ( ! is_array($where) && isset($where) && ctype_digit((string)$where))
			{
				$this->db->where($this->obj_table.'.'.$this->obj_key, $where);
			}
			else if (is_array($where) && ! empty($where))
			{
				$this->db->where($where);
			}
			
			$this->db->join('di_object_types', 'di_objects.object_type_id = di_object_types.id');
			$this->db->orderby($this->obj_table.'.'.$this->obj_key);
			$query = $this->db->get($this->obj_table);

			if ($query->num_rows() > 0)
			{
				$result = $query->result_array();
			}
			else
			{
				$result = FALSE;
			}
			
			return $result;
		}
		
		/**
		 * This function make sure we get neccessary data for all fields down to the js-gui by parsing the "$this->validation_rules" of the given
		 * model
		 * @author Jens Wretborn
		 * @param mixed A normal associative array as 'field' => 'value' array
		 * @param integer The id of the "main" object if nested objects are supposed to be fetched
		 * @param mixed An simple list of fields that should be included in the returned data
		 * @return mixed|Exception
		 */
		public function parse_js_data($data, $id = FALSE, $filter = FALSE)
		{
			if (empty($this->validation_rules))
			{
				Throw new Exception('Missing validation rules', 400);
			}

			$return_data = array();
			
			foreach ($this->validation_rules as $index => $rule)
			{
				if (isset($data[$rule['field']]))
				{
					if ($filter === FALSE || (is_array($filter) && in_array($rule['field'], $filter)))
					{
						$rule['value'] = $data[$rule['field']];
						array_push($return_data, $rule);
					}
				}
				else if (substr($rule['rule'], 0, strpos($rule['rule'], '|')) == 'also_fetch' && $id !== FALSE) 
				{ // We got nested objects, let's load it
					if ($filter === FALSE || (is_array($filter) && in_array($rule['field'], $filter)))
					{
						$rules = explode("|", $rule['rule']);
						$CI =& get_instance();
						$CI->load->model($rules[3], $rules[1]);
						
						$assoc_ids = $CI->$rules[1]->get(array($rules[2] => $id));
						$data = array();
						if(isset($assoc_ids) && sizeof($assoc_ids) > 0)
						{
							foreach((array) $assoc_ids as $key => $val)
							{
								if(isset($val['id']))
									array_push($data, $val['id']);
							}
						}
						$rule['value'] = $data;
						array_push($return_data, $rule);
					}
				}
			}
			
			return $return_data;
		}
		
		public function parse_js_object($id, $model, $module, $data, $load_template = TRUE)
		{
			$template = "";
			if ($load_template === TRUE)
			{
				if(file_exists(APPPATH . 'modules/' . $module . '/views/private/themes/' . $this->config->item('admin_theme') . '/html/' . $model . '.php'))
					$template = $this->load->view($module, 'private/themes/' . $this->config->item('admin_theme') . '/html/' . $model . '.php', TRUE, TRUE);
				else
					$template = "Could not find template: " . APPPATH . 'modules/' . $module . '/views/private/themes/' . $this->config->item('admin_theme') . '/html/' . $model . '.php';
			}

			$object = array(
						'ci_module' => $module,
						'ci_model' => $model,
						'ci_id' => $id,
						'data_type' => 'DIStorageCIObject',
						'template' => $template,
						'data' => $data
						);
			
			return $object;
		}
	
		/**
		* Removes a selected object from the database.
		*
		* @author Jens Wretborn
		* @since 1.0.0
		* @version 1.0.0
		* @category alpha
		* @access public
		* @param integer Id of the object that should be removed
		*			or a where array for the objects to delete
		* @return boolean
		*/
		public function remove($id, $remove_conn = TRUE) 
		{
			if(is_array($id))
			{
				if($objects = $this->get($id))
				{
					foreach($objects as $obj)
					{
						$this->remove($obj['id']);
					}
				}
				return;
			}

			if ( ! ctype_digit((string)$id))
			{
				return FALSE;
			}
			
			if ($obj = $this->get($id))
			{
				if ($this->has_many !== '')
				{ // Check if we have any connections to other tables
					if ( ! is_array($this->has_many) )
					{ // This might be set to just one option
						$this->has_many = array($this->has_many);
					}

					foreach ($this->has_many as $index => $type)
					{
						switch ($type)
						{
							case DI_Object::DI_COMMENTS :
								$this->remove_comments($obj[0]['object_id']);
								break;
							default :
								break;
						}
					}
				}
				
				if ($remove_conn && isset($obj[0]['object_id'])) {
					$this->db->where($this->obj_key, $obj[0]['object_id']);
					$this->db->delete($this->obj_table);
				}
			}
			
			$this->db->where($this->key, $id);
			return $this->db->delete($this->table);
		}

		/**
		 * Simple function that removes any comments associated with and object.
		 *
		 * @author Jens Wretborn
		 * @since 0.0.1
		 * @version 0.0.1
		 * @category alpha
		 * @access public
		 * @param integer	Object id
		 */
		public function remove_comments($object_id)
		{
			$this->db->where('target_id', $object_id);
			$this->db->delete(DI_Object::DI_COMMENTS);
		}
		
		/**
		 * A function that loads and sets any validation rules for a particular object
		 *
		 */
		public function set_validation_rules($exclude = array())
		{
			if (empty($exclude) && isset($this->exclude)) {
				$exclude = $this->exclude;
			}
			
			foreach ($this->validation_rules as $index => $rule)
			{
				if (is_array($exclude) && ! empty($exclude) && in_array($rule['field'], $exclude))
				{
					continue;
				}
				else
				{
					$this->form_validation->set_rules($rule['field'], $rule['label'], $rule['rule']);
				}
			}
			
			return;
		}
		
		/**
		* Updates a specified object with given data
		*
		* @author Jens Wretborn
		* @since 1.0.0
		* @version 1.0.0
		* @category alpha
		* @access public
		* @param integer Id of the object being updated
		* @param array Array of update data
		* @return boolean
		*/
		public function update($object_id, $update)
		{
			if ( ! ctype_digit((string)$object_id) || ! $this->get($object_id) )
			{
				return FALSE;
			}
			
			foreach ($update as $key => $data) {
				if (is_array($data) || is_object($data)) {
					Throw new Exception('Could not save object: ' . $object_id . ", " . $key . ' is array or object', 412);
				}
			}
			
			$this->db->where($this->key, $object_id);
			return $this->db->update($this->table, $update);
		}
		
		public function validate_json_data($data)
		{
			try 
			{
				if ( ! is_array($data) || count($data) < 0)
				{
					Throw new Exception('No data given', 412);
				}
				
				foreach ($data as $index => $obj_field)
				{
					if ((isset($this->exclude) && is_array($this->exclude) && ! in_array($obj_field['field'], $this->exclude)) || ! isset($this->exclude))
					{
						if (isset($obj_field['value']))
							$_POST[$obj_field['field']] = $obj_field['value'];
						else
							$_POST[$obj_field['field']] = '';
					}
				}
				
				return $this->validate_post();
			} catch (Exception $e) {
				Throw new Exception($e->getMessage(), $e->getCode());
			}
		}  
		
		/** 
		 * This function is used by the ajaxengine-controllers to automatically validate data sent by the js gui
		 *
		 * @author Jens Wretborn
		 * @return mixed|Exception
		 */
		public function validate_post()
		{
			$this->load->library('form_validation');
			$this->set_validation_rules();
			
			if ($this->form_validation->run())
			{
				$data = $this->fetch_postdata();
				return $data;
			}
			else
			{
				if (count($_POST) == 0)
					Throw new Exception('No data given', 412);
					
				Throw new Exception($this->form_validation->error_string(), 412);
			}
		}
	}

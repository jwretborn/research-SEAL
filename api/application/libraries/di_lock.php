<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class DI_Lock {
	
	public function get($name, $timeout = 10)
	{
		$name = mysql_real_escape_string($name);
		$timeout = intval($timeout);
		$CI =& get_instance();

		$q = $CI->db->query("SELECT GET_LOCK('$name', $timeout) as l");
		return ($q->num_rows() == 1 && $q->row()->l== 1);
	}

	public function release($name)
	{
		$name = mysql_real_escape_string($name);
		$CI =& get_instance();

		$q = $CI->db->query("SELECT RELEASE_LOCK('$name') as l");
		return ($q->num_rows() == 1 && $q->row()->l== 1);
	}

}

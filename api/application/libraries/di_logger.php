<?php
	/**
	*
	* @package 	Acrom logging library
	* @author	Jens Wretborn
	* @category	Alpha
	* @since	1.0.0
	* @version	1.0.0
	* @link 	http://acrom.se
	*/
	class DI_Logger extends CI_Log
	{
		var $CI;
		var $_log_stack;
		var $_date_fmt = 'Y-m-d H:i:s';
		
		/** 
		* This function loads the selected settings from the config.php file into the
		* class variables.
		*
		* @author Jens Wretborn
		* @since 1.0.0
		* @version 1.0.0
		* @category alpha
		* @access public
		*/
		function __construct()
		{
			parent::__construct();

			$this->CI =& get_instance();
			
			$this->_log_stack = array();
			$this->_log_table = 'di_logs';
		}
		
		/** 
		* Writes the log messages to the database.
		*
		* @author Jens Wretborn
		* @since 1.0.0
		* @version 1.0.0
		* @category alpha
		* @access private
		*/
		function _write_to_db()
		{
			while ( ! empty($this->_log_stack) )
			{
				$log = array_pop($this->_log_stack);
				$this->CI->db->insert($this->_log_table,
										array(
											'level' => $log['level'],
											'user' => $log['user'],
											'message' => $log['message'],
											'timestamp' => $log['timestamp'])
										);
			}
		}
		
		/** 
		* Writes the log messages to a selected file.
		*
		* @author Jens Wretborn
		* @since 1.0.0
		* @version 1.0.0
		* @category alpha
		* @access private
		*/
		function _write_to_file()
		{
			$filepath = $this->_log_path.'log-'.date('Y-m-d').EXT;
			$message  = '';

			if ( ! file_exists($filepath))
			{
				$message .= "<"."?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); ?".">\n\n";
			}

			if ( ! $fp = @fopen($filepath, FOPEN_WRITE_CREATE))
			{
				return FALSE;
			}
			
			foreach ($this->_log_stack as $log)
			{
				$message .= $log['level'].' - '.$log['timestamp']. ' - USER: ' .$log['user']. ' --> '.$log['message']."\n";
				flock($fp, LOCK_EX);	
				fwrite($fp, $message);
				flock($fp, LOCK_UN);
				
				$message = '';
			}

			fclose($fp);

			@chmod($filepath, FILE_WRITE_MODE); 		
			return TRUE;
		}
		
		/** 
		* Checks the config to determine wheter to write the logs to a file or to the database.
		*
		* @author Jens Wretborn
		* @since 1.0.0
		* @version 1.0.0
		* @category alpha
		* @access public
		*/
		function write_log()
		{
			if ($this->CI->config->item('log_use_database') === TRUE)
				$this->_write_to_db();
			else
				$this->_write_to_file();
		}
		
		/** 
		* Creates and saves a log message to the stack of log messages.
		*
		* @author Jens Wretborn
		* @since 1.0.0
		* @version 1.0.0
		* @category alpha
		* @access public
		* @param	string	The message to be displayed in the log.
		* @param	integer	The system id of the user currently running the service
		* @param	integer	The level/type of the log message.
		*/
		function log_message($level, $message, $user=-1)
		{
			if ($this->_threshold < $this->_levels[$level])
				return FALSE;

			$new_log = array(
							'message' => $message,
							'user' => $user == -1 ? "NONE" : $user,
							'level' => $level,
							'timestamp' => time()
							);
			array_push($this->_log_stack, $new_log);
			$this->write_log();
			return TRUE;
		}

		function log_m($level, $message)
		{
			if ($this->_log_level < $this->_levels[$level])
				return FALSE;
			else
				$this->log_message($level, $message);
		}
	}

<?php
/**
 * Language class
 *
 *
 * @author		Jens Wretborn
 */
class DI_Lang extends CI_Lang {

	/**
	 * Extends the ci_lang->line function to add insert-value functionality
	 *
	 *
	 */
	public function line($line='', $val='') 
	{
		if (($message = parent::line($line)) !== FALSE && $val !== '')
			return sprintf($message, $this->_translate_fieldname($val));
		
		return $message;
	}

	/**
	 * Translate a field name
	 *
	 * @access	private
	 * @param	string	the field name
	 * @return	string
	 */
	protected function _translate_fieldname($fieldname)
	{
		// Do we need to translate the field name?
		// We look for the prefix lang: to determine this
		if (substr($fieldname, 0, 5) == 'lang:')
		{
			// Grab the variable
			$line = substr($fieldname, 5);

			// Were we able to translate the field name?  If not we use $line
			if (FALSE === ($fieldname = $this->line($line)))
			{
				return $line;
			}
		}

		return $fieldname;
	}
}
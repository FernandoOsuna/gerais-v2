<?php if (!defined('BASEPATH')) {exit('No direct script access allowed');}

/**
 * Rapyd Library Loader
 *
 * Put the 'Rapyd' folder under 'Library' in CI installation's 'application/libraries' folder
 * You can put it elsewhere but remember to alter the script accordingly
 *
 * Usage:
 * $this->load->library('rapyd');
 *
 */
class CI_Rapyd
{
	/**
	 * Constructor
	 *
	 * @param	string $class class name
	 */
	function __construct($class = NULL)
	{

		// include path for rapyd library
		// alter it accordingly if you have put the 'rapyd' folder elsewhere
		// by default '/rapyd' folder should be placed at the same level of '/system'
 		require_once(BASEPATH.'../rapyd/include.php');

		//todo: I must use a rapyd wrapper to log
		log_message('debug', "Rapyd Class Initialized");

		$this->db = rpd::$db;
		$this->uri = rpd::$uri;
		$this->qs  = rpd::$qs;
        $this->html = new rpd_html_helper();
	}



  function head()
  {
        return rpd::head();
  }

  function js($js, $external=false)
  {
        return rpd::js($js, $external);
  }

  function css($css, $external=false)
  {
        return rpd::css($css, $external);
  }

  function refill_get()
  {
	//global $_GET; is its needed?
	parse_str($_SERVER['QUERY_STRING'],$_GET);
  }
}

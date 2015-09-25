<?php

namespace App;

/*
	Controller class

	basic usage:
	$controller -> init()
	$controller -> run()
*/

class Controller {

	protected
		$path = null;


	/**
	 * Construct, fill $path variable
	 *
	 * @return void
	 */
	public function __construct()
	{
		$fullpath = explode('/', trim(Request :: $path, '/'));
		$this -> path = array_slice($fullpath, 1);
	}


	/**
	 * init controller
	 * if returns TRUE - this controller will render the html page
	 * if return FALSE - Router proceeds in seeking the right route
	 *
	 * @return boolean
	 */
	public function init()
	{
		return true;
	}


	/**
	 * Renders the page, fills the Response by outdata
	 *
	 * @return boolean
	 */
	public function run()
	{
		return true;
	}


	/**
	 * Return this controller vars by names
	 *
	 * @param array $names
	 * @return array
	 */
	public function get($names = null)
	{
		if (is_array($names)) {
			return array_intersect_key(get_object_vars($this), array_flip($names));
		} else {
			return get_object_vars($this);
		}
	}

}
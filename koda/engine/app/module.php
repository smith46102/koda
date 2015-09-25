<?php

namespace App;

/*

	basic module class

*/

class Module {

	/**
	 * Checks if User have permission to acces this module
	 * Use this function in descendant classes
	 *
	 * @return boolean
	 */
	public function havePermission()
	{
		return true;
	}

}
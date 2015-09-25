<?php

namespace Controllers;

use
	\Modules;

/*

	Dumper
	shows dumps, save and restore them

*/

class Dumper extends \App\Controller {

	public function init()
	{
		$this -> command = _array($this -> path, 0);
		return true;
	}


	public function run()
	{
		$module = new Modules\Dumper\Dumper;
		if ($this -> command == 'make') {
			$module -> makeDump();
		} elseif ($this -> command == 'restore') {
			$module -> restoreDump();
		} else {
			$module -> render();
		}

		return true;
	}

}
<?php

namespace Controllers;

use \App\Response;

/*

	ModuleController
	runs modules methods by name

*/

class Module extends \App\Controller {


	public function init()
	{
		$this -> module = _array($this -> path, 0);
		$this -> command = _array($this -> path, 1);

		if ($this -> module && $this -> command)
			return true;

		return false;
	}


	public function run()
	{
		$modClass = '\Modules\\' . str_replace('.', '\\', $this -> module);
		Response :: setJson();

		try {
			$module = new $modClass();

			if (!$module -> havePermission())
				throw new \BadMethodCallException("you dont have permission to use this module");

			if (!method_exists($module, $this -> command))
				throw new \BadMethodCallException("can`t call method $modClass -> {$this -> command}");

			if (!$module -> {$this -> command}($_REQUEST))
				throw new \BadMethodCallException("module action fails");

		} catch (\Exception $e) {
			Response :: add([
				'status' => 'system_error',
				'data'=> $e -> getMessage()
			]);
		}

		return true;
	}

}
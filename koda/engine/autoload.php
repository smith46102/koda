<?php

/*
	Autoload class
*/

class Autoload {

	public static
		$paths;


	/**
	 * Load config file, setup autoload paths
	 *
	 * @param string $file
	 * @return void
	 */
	public static function init($file)
	{
		if (is_readable(DOCUMENT_ROOT . '/' . $file)) {
			self :: $paths = require $file;
			spl_autoload_register(array('Autoload', 'load'));
		} else {
			exit ("fail setup autoloding paths");
		}
	}


	/**
	 * Autoload function.
	 * Loads class by name with namespace.
	 * Search class by root-path string in paths, configured by init()
	 *
	 * @param string $class
	 * @return void
	 */
	public static function load($class)
	{
		$class = strtolower($class);
		$path = explode('\\', $class);
		$root = $path[0];
		$file = null;

		if (isset(self :: $paths[$root])) {
			$file = DOCUMENT_ROOT . self :: $paths[$root] . "/" . implode('/', array_slice($path, 1)) . ".php";
		}

		if ($file and is_readable($file)) {
			include_once $file;
		} else {
			throw new Exception("fail autoload $class class");
		}
	}


}
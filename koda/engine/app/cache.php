<?php

namespace App;

/*
	Cache class - stores and finds data
*/

class Cache {

	private static
		$models = [];


	// saves model to memory
	public static function saveModel($class, $id, Model $model)
	{
		$key = "$class|$id";
		self :: $models[$key] = $model;
	}


	// gets model from memory
	public static function getModel($class, $id)
	{
		$key = "$class|$id";
		if (isset(self :: $models[$key])) {
			return self :: $models[$key];
		}
		return null;
	}


	// private static function makeHash($key)
	// {
	// 	return 'c.' . substr(md5($key), 0, 16) . '.html';
	// }

	// public static function save($name, $data)
	// {
	// 	if (!is_writeable(DIR_CACHE))
	// 		return false;

	// 	$file = DIR_CACHE . self :: makeHash($name);
	// 	file_put_contents($file, $data);
	// 	return $file;
	// }


	// public static function get($name, $updateAge = null)
	// {
	// 	$file = DIR_CACHE . self :: makeHash($name);
	// 	if (is_readable($file)) {
	// 		self :: $age = $age = filemtime($file);
	// 		if ((!$updateAge) || ($updateAge && $updateAge < $age)) {
	// 			return file_get_contents($file);
	// 		}
	// 	}
	// 	return false;
	// }


	// // include file from cache
	// public static function find($name)
	// {
	// 	$file = DIR_CACHE . self :: makeHash($name);
	// 	if (is_readable($file)) {
	// 		return $file;
	// 	}
	// 	return false;
	// }


	// public function flush()
	// {
	// 	if ($files = _scanFiles(DIR_CACHE)) {
	// 		foreach ($files as $file) {
	// 			unlink(DIR_CACHE . '/' . $file);
	// 		}
	// 		return true;
	// 	}
	// 	return false;
	// }

}
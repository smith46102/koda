<?php

namespace App;

/*
	Config class

	basic usage:
	$controller -> load($file)
	$controller -> applyHostSettings()
*/

class Config {

	private static

		/**
		 * @var settings read from file
		 */
		$settings,

		/**
		 * @var hosts settings read from file
		 */
		$hosts;


	/**
	 * Load config from file
	 *
	 * @param string $file
	 * @return void
	 */
	public static function load($file)
	{
		self :: $settings = require DOCUMENT_ROOT . "/$file";
	}


	/**
	 * Apply special host settings,
	 * Taken from "hosts" part of site config file
	 *
	 * @return void
	 */
	public static function applyHostSettings($file)
	{
		self :: $hosts = require DOCUMENT_ROOT . "/$file";
		$server = preg_replace('|^www.|', '', $_SERVER['HTTP_HOST']);

		if (isset(self :: $hosts[$server])) {
			$host = self :: $hosts[$server];
			self :: $settings = array_merge(self :: $settings, $host);
		} else {
			throw new \Exception('wrong server config');
		}
	}


	/**
	 * Get settings by name
	 *
	 * @param string $name
	 * @param mixed  $default
	 * @return void
	 */
	public static function get($name, $default = null)
	{
		return isset(self :: $settings[$name]) ? self :: $settings[$name] : $default;
	}


}
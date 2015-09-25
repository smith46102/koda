<?php

namespace App;

/*

	Response helper class

*/

class Response {

	public static
		/**
		 * @var enables output data in JSON format
		 */
		$outJson = false,

		/**
		 * @var response contents
		 */
		$data = '';


	/**
	 * Set response header with $name and $value
	 *
	 * @param string $name
	 * @param string $value
	 * @return void
	 */
	public static function header($name, $value = null)
	{
		$name = $value ? $name .= ': ' . $value : $name;
		header($name);
	}


	/**
	 * Redirect to another url, if current URL not equals to $to
	 *
	 * @param string $to
	 * @return void
	 */
	public static function redirect($to)
	{
		if ($to != Request :: $uri) {
			self :: header('HTTP/1.1 301 Moved Permanently');
			self :: header('Location', $to);
			exit;
		}
	}


	/**
	 * Set 404 reponse header
	 *
	 * @return void
	 */
	public static function set404()
	{
		self :: header('HTTP/1.1 404 Not Found');
	}


	/**
	 * Set response output to JSON mode
	 *
	 * @return void
	 */
	public static function setJson()
	{
		if (!self :: $outJson) {
			self :: $outJson = true;
			self :: $data = [];
		}
	}


	/**
	 * Add data to response output.
	 * If JSON mode is on, array data will be attached to output-array
	 * If JSON mode is off, data will be flatten, and attached as string to output
	 *
	 * @param mixed $data
	 * @return void
	 */
	public static function add($data)
	{
		if (self :: $outJson and is_array($data)) {
			self :: $data = self :: $data + $data;
		} else {
			if (is_array($data)) {
				$data = implode('', array_values($data));
			}
			self :: $data .= $data;
		}
	}


	/**
	 * Make Response content echoes to php output buffer
	 *
	 * @return void
	 */
	public static function out()
	{
		if (self :: $outJson) {
			self :: header('Content-Type', 'application/json');
			echo json_encode(self :: $data);
		} else {
			echo self :: $data;
		}
		exit;
	}

}

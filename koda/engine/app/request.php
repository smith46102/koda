<?php

namespace App;

/*

	Request helper class

*/

class Request {

	const
		MAX_UPLOAD_FILE_SIZE = 10000000;

	public static
		$uri = '',
		$path = '',
		$query = '',
		$fragment = '',
		$headers = [];


	/**
	 * Parse input uri, init Request vars
	 *
	 * @return void
	 */
	public static function parse()
	{
		self :: parseRequestHeaders();
		$requestUri = self :: server('REQUEST_URI');
		self :: $uri = self :: server('REDIRECT_URL', $requestUri);

		if ($u = parse_url(self :: $uri)) {
			self :: $path = _array($u, 'path', '/');
			self :: $query = _array($u, 'query');
			self :: $fragment = _array($u, 'fragment');
		} else {
			throw new \Exception('wrong URI');
		}
	}


	/**
	 * Get request header value by name
	 *
	 * @param string $name
	 * @return string
	 */
	public static function header($name)
	{
		return isset(self :: $headers[$name]) ? self :: $header[$name] : null;
	}


	/**
	 * Get all headers from Server environment
	 * saves the headers array to static variable
	 *
	 * @return void
	 */
	private static function parseRequestHeaders() {
		$headers = [];
		foreach($_SERVER as $key => $value) {
			if (substr($key, 0, 5) <> 'HTTP_') {
				continue;
			}
			$header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
			$headers[$header] = $value;
		}
		self :: $headers = $headers;
	}


	/**
	 * Get fields from $_GET.
	 * If $name is string - returns 1 field,
	 * If $name is array - returns array with keys $name
	 * and values taken from $_GET
	 * If cant find value in $_GET, use $default value
	 *
	 * @param mixed $name
	 * @param mixed $default
	 * @return mixed
	 */
	public static function get($name, $default = null)
	{
		if (is_array($name)) {
			$result = [];
			foreach ($name as $n) {
				$result[$n] = isset($_GET[$n]) ? $_GET[$n] : $default;
			}
			return $result;
		} else {
			return isset($_GET[$name]) ? $_GET[$name] : $default;
		}
	}


	/**
	 * Get fields from $_POST.
	 * If $name is string - returns 1 field,
	 * If $name is array - returns array with keys $name
	 * and values taken from $_POST
	 * If cant find value in $_POST, use $default value
	 *
	 * @param mixed $name
	 * @param mixed $default
	 * @return mixed
	 */
	public static function post($name, $default = null)
	{
		if (is_array($name)) {
			$result = [];
			foreach ($name as $n) {
				$result[$n] = isset($_POST[$n]) ? $_POST[$n] : $default;
			}
			return $result;
		} else {
			return isset($_POST[$name]) ? $_POST[$name] : $default;
		}
	}


	/**
	 * Get field from $_REQUEST
	 *
	 * @param mixed $name
	 * @param mixed $default
	 * @return mixed
	 */
	public static function req($name, $default = null)
	{
		return isset($_REQUEST[$name]) ? $_REQUEST[$name] : $default;
	}


	/**
	 * Test if field $name is exists in $_REQUEST
	 *
	 * @param string $name
	 * @return boolean
	 */
	public static function is($name)
	{
		return isset($_REQUEST[$name]) ? true : false;
	}


	/**
	 * Get field from $_SERVER
	 *
	 * @param string $name
	 * @return mixed
	 */
	public static function server($name, $default = null)
	{
		return (isset($_SERVER[$name])) ? $_SERVER[$name] : $default;
	}


	/**
	 * Get field from $_SESSION
	 *
	 * @param string $name
	 * @return mixed
	 */
	public static function session($name, $default = null)
	{
		return (isset($_SESSION[$name])) ? $_SESSION[$name] : $default;
	}


	/**
	 * Set field in $_SESSION, if $value = null, remove it
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public static function setSession($name, $value = null)
	{
		if ($value) {
			$_SESSION[$name] = $value;
		} else {
			$_SESSION[$name] = null;
			unset($_SESSION[$name]);
		}
	}


	/**
	 * Get cookie value by $name
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return string
	 */
	public static function cookie($name, $default = null)
	{
		return (isset($_COOKIE[$name])) ? $_COOKIE[$name] : $default;
	}


	/**
	 * Set cookie value
	 * If $value is null - cookie will be deleted
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public static function setCookie($name, $value = null)
	{
		$cookieTime = 60*60*24*30;
		if ($value) {
			setcookie($name, $value, time() + $cookieTime, '/');
		} else {
			setcookie($name, '', time() - $cookieTime, '/');
		}
	}


	/**
	 * Get client IP
	 *
	 * @return string
	 */
	public static function clientIP() {
		$ip = '';
		if (isset($_SERVER['HTTP_CLIENT_IP']))
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		elseif (isset($_SERVER['HTTP_X_FORWARDED']))
			$ip = $_SERVER['HTTP_X_FORWARDED'];
		elseif (isset($_SERVER['HTTP_FORWARDED_FOR']))
			$ip = $_SERVER['HTTP_FORWARDED_FOR'];
		elseif (isset($_SERVER['HTTP_FORWARDED']))
			$ip = $_SERVER['HTTP_FORWARDED'];
		elseif (isset($_SERVER['REMOTE_ADDR']))
			$ip = $_SERVER['REMOTE_ADDR'];
		else
			$ip = null;
		return $ip;
	}


	/**
	 * Get request-file from $_FILES by $name as \Data object
	 *
	 * @param string $name
	 * @return \Data
	 */
	public static function file($name)
	{
		return isset($_FILES[$name]) ? new Data($_FILES[$name]) : null;
	}


	/**
	 * Upload request-file to $destDir
	 * Name maked as sha1 encode of file
	 * Returns \File instance of uploaded file
	 *
	 * @param string $name
	 * @param string $destDir
	 * @return \File
	 */
	public static function uploadFile($name, $destDir)
	{
		$file = self :: file($name);

		if (!isset($file -> error) || is_array($file -> error)) {
			throw new \Exception('Invalid parameters.');
		}

		if ($file -> error !== UPLOAD_ERR_OK) {
			throw new \Exception('File send error.');
		}

		if ($file -> size > self :: MAX_UPLOAD_FILE_SIZE) {
			throw new \Exception('Exceeded filesize limit.');
		}

		$finfo = new \finfo(FILEINFO_MIME_TYPE);
		$mimeType = $finfo -> file($file -> tmp_name);
		$mimesToUpload = [
			'jpg' => 'image/jpeg',
			'png' => 'image/png',
			'gif' => 'image/gif'
		];

		if (false === $ext = array_search($mimeType, $mimesToUpload, true)) {
			throw new \Exception('Invalid file format.');
		}

		$nameHash = sha1_file($file -> tmp_name);
		$destPath = sprintf(DOCUMENT_ROOT . $destDir . '/%s.%s', $nameHash, $ext);

		if (!move_uploaded_file($file -> tmp_name, $destPath)) {
			throw new \Exception('Failed to move uploaded file.');
		}

		return new File($destPath);
	}


}
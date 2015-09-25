<?php

namespace App;

/*
	File class
	serves base files read-write and scan functions
*/

class File {

	public
		$path;


	/**
	 * Set initial path of file
	 *
	 * @param string $path
	 * @return void
	 */
	public function __construct($path)
	{
		$this -> path = $path;
	}


	/**
	 * Properties like "_dir" will be called as methods getDir()
	 *
	 * @param string $name
	 * @retun mixed
	 */
	public function __get($name)
	{
		$method = 'get' . $name;
		if (method_exists($this, $method)) {
			return $this -> $method();
		}
		return null;
	}


	/**
	 * Get dir name from file path
	 *
	 * @return string
	 */
	public function getDir()
	{
		return dirname($this -> path);
	}


	/**
	 * Get file path reduced by document root
	 *
	 * @return string
	 */
	public function getDocpath()
	{
		return str_replace(DOCUMENT_ROOT, '', $this -> path);
	}


	/**
	 * Get file name from file path
	 *
	 * @return string
	 */
	public function getName()
	{
		return basename($this -> path);
	}


	/**
	 * Get file name from file path, without extension
	 *
	 * @return string
	 */
	public function getBasename()
	{
		$info = pathinfo($this -> path);
		return $info['filename'];
	}


	/**
	 * Check if this filepath is file
	 *
	 * @return boolean
	 */
	public function getIsFile()
	{
		return is_file($this -> path);
	}


	/**
	 * Check if this filepath is dir
	 *
	 * @return boolean
	 */
	public function getIsDir()
	{
		return is_dir($this -> path);
	}


	/**
	 * Check if filepath is readable
	 *
	 * @return boolean
	 */
	public function getReadable()
	{
		return is_readable($this -> path);
	}


	/**
	 * Check if filpath is writable
	 *
	 * @return boolean
	 */
	public function getWritable()
	{
		return is_writable($this -> path);
	}


	/**
	 * Get filepath modified date
	 *
	 * @return int
	 */
	public function getModified()
	{
		return filemtime($this -> path);
	}


	/**
	 * Read filepath contents
	 *
	 * @return string
	 */
	public function read()
	{
		if (is_readable($this -> path)) {
			return file_get_contents($this -> path);
		}
		return null;
	}


	/**
	 * Save $contents to filepath
	 *
	 * @param string $contents
	 * @return boolean
	 */
	public function save($contents)
	{
		if (is_writable($this -> path) or is_writable($this -> dir)) {
			return file_put_contents($this -> path, $contents);
		}
		return false;
	}


	/**
	 * Delete file with filename
	 *
	 * @return boolean
	 */
	public function delete()
	{
		if ($this -> readable and $this -> writable) {
			return unlink($this -> path);
		}
		return false;
	}


	/**
	 * Delete file with $path
	 *
	 * @param string $path
	 * @return boolean
	 */
	public static function deleteFile($path)
	{
		if (file_exists($path) and is_writable($path)) {
			return unlink($path);
		}
		return false;
	}


	/**
	 * Get dirs collection by $path
	 *
	 * @param string $path
	 * @return \Collection
	 */
	public static function scanDirs($path) {
		$path = rtrim($path, '/') . '/';
		if (is_dir($path)) {
			$result = [];
			$list = scandir($path);
			foreach ($list as $name) {
				$dir = $path . $name;
				if ($name !== '.' && $name !== '..' && is_dir($dir)) {
					$result[] = new File($dir);
				}
			}
			return new Collection($result);
		}
		return false;
	}


	/**
	 * Get files collection by path
	 * with REGEX mask and recursive mode
	 *
	 * @param string $path
	 * @param string $mask
	 * @param boolean $recursive
	 * @return \Collection
	 */
	public static function scanFiles($path, $mask = null, $recursive = false) {
		$path = rtrim($path, '/') . '/';
		if (is_dir($path)) {
			$result = [];
			$list = scandir($path);
			foreach ($list as $name) {
				if ($name !== '.' && $name !== '..') {
					$file = $path . $name;
					if ($recursive and is_dir($file)) {
						$subfiles = self :: scanFiles($file, $mask, $recursive);
						if ($subfiles -> count > 0) {
							$result = array_merge($result, $subfiles -> getContainer());
						}
					} elseif (is_file($file)) {
						$canAdd = $mask ? preg_match("/$mask/", $name) : true;
						$canAdd and $result[] = new File($file);
					}
				}
			}
			return new Collection($result);
		}
		return false;
	}

}
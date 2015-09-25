<?php

// setup a custom error handler

function customErrorHandler($errno, $errstr, $errfile, $errline)
{
	if (!(error_reporting() & $errno)) return;
	$message = "error on line $errline<br> $errfile <br>";
	$message .= "<b>$errstr</b>";
	throw new Exception($message);
	exit(1);
	return true;
}

set_error_handler("customErrorHandler");
error_reporting(E_ALL);
ini_set('display_errors', true);


// set internal encoding

mb_internal_encoding("UTF-8");


// define paths consts

define ('DOCUMENT_ROOT', rtrim($_SERVER['DOCUMENT_ROOT'], '/'));


/**
 * Gets value from array, or return default
 *
 * @param array $arr
 * @param mixed $key
 * @param mixed $def
 * @return mixed
 */
function _array(array $arr, $key, $def = null) {
	return isset($arr[$key]) ? $arr[$key] : $def;
}


/**
 * Pack array of numbers into {1}{2}{3} string
 *
 * @param array $ids
 * @return string
 */
function packIDS(array $ids) {
	if (count($ids) > 0) {
		return '{' . implode('}{', $ids) . '}';
	}
	return '';
}


/**
 * Unpack string from {1}{2}{3} to array
 *
 * @param string $ids
 * @return array
 */
function unpackIDS($ids)
{
	if (preg_match_all('/\{(\d+)\}/ui', $ids, $match)) {
		return $match[1];
	}
	return array();
}


/**
 * Extract from $array values with keys, what exists in $wantedKeys
 *
 * @param array $array
 * @param array $wantedKeys
 * @return array
 */
function filterKeys(array $array, array $wantedKeys)
{
	return array_intersect_key($array, array_flip($wantedKeys));
}

<?php

namespace App;

/*
	Debug class

	basic usage:

	try {
		// some code...
	} catch (Exception $e) {
		Debug :: showException($e);
		Debug :: showItems($arrayData);
	}
*/

class Debug {


	// show exception with callstack-tracing
	public static function showException($e)
	{
		$trace = $e -> getTraceAsString();
		$message = $e -> getMessage();
		echo '<pre style="padding: 20px; margin: 10px; background: #fbb; white-space: pre-wrap; word-wrap: break-word;">' . $message . "\n\n" . $trace . '</pre>';
	}


	// show debug code block
	public static function showCode($code)
	{
		echo '<pre style="padding: 20px; margin: 10px; background: #bfb; white-space: pre-wrap; word-wrap: break-word;">'.$code.'</pre>';
	}


	// show one-item array or object
	public static function showItem($item, $return = false)
	{
		if (!is_object($item) && !is_array($item))
			return false;

		if (is_object($item)) {
			$item = (array) $item;
		}

		$echo = '<table cellpadding="5" cellspacing="0">';
		foreach ($item as $key => $value) {
			if ($value === NULL) {
				$value = "'NULL'";
			}
			$echo .= '<tr>';
			$echo .= '<td style="border-bottom: 1px solid #ccc; font: normal 12px/17px Arial;">'.$key.'</td>';
			$echo .= '<td style="border-bottom: 1px solid #ccc; font: normal 12px/17px Arial;">'.$value.'</td>';
			$echo .= '</tr>';
		}
		$echo .= '</table>';

		if ($return) {
			return $echo;
		} else {
			echo $echo;
		}
	}


	// show array-data in table
	public static function showData($data, $what = null, $return = false)
	{
		if (!is_array($data))
			return false;

		if ($what) {
			$what =  explode(',', $what);
		} else {
			$what = array_keys(reset($data));
		}

		$echo = '<table cellpadding="3" cellspacing="0"><tr><th style="border-bottom: 2px solid #aaa; color: #555; font: bold 12px/17px Arial;">#</th>';
		foreach ($what as $w) {
			$echo .= '<th style="border-bottom: 2px solid #aaa; color: #555; font: bold 12px/17px Arial;">'.$w.'</th>';
		}
		$echo .= '</tr>';
		$i = 1;
		foreach ($data as $item) {
			$echo .= '<tr><td style="border-bottom: 1px solid #ccc; font: normal 12px/17px Arial;">'.$i.'</td>';
			if (is_array($item)) {
				foreach ($what as $w) {
					if (isset($item[$w])) {
						$echo .= '<td style="border-bottom: 1px solid #ccc; font: normal 12px/17px Arial;">'.$item[$w].'</td>';
					} else {
						$echo .= '<td style="border-bottom: 1px solid #ccc;"></td>';
					}
				}
			} elseif (is_object($item)) {
				foreach ($what as $w) {
					if (isset($item -> $w)) {
						$echo .= '<td style="border-bottom: 1px solid #ccc; font: normal 12px/17px Arial;">'.$item -> $w.'</td>';
					} else {
						$echo .= '<td style="border-bottom: 1px solid #ccc;"></td>';
					}
				}
			} else {
				$echo .= '<td style="border-bottom: 1px solid #ccc; font: normal 12px/17px Arial;">'.$item.'</td>';
			}
			$i++;
			$echo .= '</tr>';
		}
		$echo .= '</table>';

		if ($return) {
			return $echo;
		} else {
			echo $echo;
		}
	}

}
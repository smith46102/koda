<?php

namespace App;

/*
	Timer class used to:
	1) get time and date in normal format (compatible to Mysql)
	2) setup micro timer to mark App-working time
*/

class Timer {

	public static
		$start = 0.0,
		$end = 0.0;


	/**
	 * Starts timer
	 *
	 * @return void
	 */
	public static function start()
	{
		self :: $start = microtime(true);
	}


	/**
	 * Stops timer, and return delta in milliseconds
	 *
	 * @return float
	 */
	public static function stop()
	{
		self :: $end = microtime(true);
		$delta = (self :: $end - self :: $start) * 1000.0;
		return $delta;
	}


	/**
	 * Add execution time to response
	 *
	 * @return void
	 */
	public static function showExecutionTime()
	{
		$time = Timer :: stop();
		if (Response :: $outJson) {
			Response :: add(['executionTime' => $time . 'ms']);
		} else {
			Response :: add("<!-- executionTime {$time}ms -->");
		}
	}


	/**
	 * Returns current datetime in Mysql format
	 *
	 * @return string
	 */
	public static function now()
	{
		return date("Y-m-d H:i:s", time());
	}


	/**
	 * Returns current date in Mysql format
	 *
	 * @return string
	 */
	public static function date()
	{
		return date("Y-m-d", time());
	}


	/**
	 * Returns current time in Mysql format
	 *
	 * @return string
	 */
	public static function time()
	{
		return date("H:i:s", time());
	}

}
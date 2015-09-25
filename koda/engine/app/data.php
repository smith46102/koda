<?php

namespace App;

/*
	Basic data-object
	provides acces to properties in container
*/

class Data {


	/**
	 * Construct model, and sets it fields
	 *
	 * @param array $fields
	 * @return void
	 */
	public function __construct(array $fields = null)
	{
		if (is_array($fields)) {
			$this -> fields($fields);
		}
	}


	/**
	 * All unset fields of Data object will return null
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		return null;
	}


	/**
	 * Set fields, if $fields isset
	 * or returns Data object fields if $fields is null
	 *
	 * @param array $fields
	 * @return array
	 */
	public function fields(array $fields = null)
	{
		if (is_array($fields)) {
			foreach ($fields as $name => $value) {
				$this -> {$name} = $value;
			}
		} else {
			return get_object_vars($this);
		}
	}


}
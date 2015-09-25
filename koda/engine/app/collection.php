<?php

namespace App;

/*
	Collection class replaces standart array
	to simplify data-objects manipulating
*/

class Collection implements \Iterator, \ArrayAccess, \Countable {

	private
		$container = [];


	public function __construct(array $items = null)
	{
		if (is_array($items)) {
			$this -> container = $items;
		}
	}

	public function __get($name)
	{
		if ($name == 'count') {
			return $this -> count();
		}
		return null;
	}

	public function count()
	{
		return count($this -> container);
	}

	public function rewind()
	{
		return reset($this -> container);
	}

	public function current()
	{
		return current($this -> container);
	}

	public function key()
	{
		return key($this -> container);
	}

	public function next()
	{
		return next($this -> container);
	}

	public function valid()
	{
		return key($this -> container) !== null;
	}

	public function offsetSet($offset, $value)
	{
		if (is_null($offset)) {
			$this -> container[] = $value;
		} else {
			$this -> container[$offset] = $value;
		}
	}

	public function offsetExists($offset)
	{
		return isset($this -> container[$offset]);
	}

	public function offsetUnset($offset)
	{
		unset($this -> container[$offset]);
	}

	public function offsetGet($offset)
	{
		return isset($this -> container[$offset]) ? $this -> container[$offset] : null;
	}


	public function getContainer()
	{
		return $this -> container;
	}


	public function keys()
	{
		if (is_array($this -> container)) {
			return array_keys($this -> container);
		}
		return null;
	}


	public function chunk($size)
	{
		$chunks = [];
		foreach (array_chunk($this -> container, $size, true) as $chunk) {
			$chunks[] = new self($chunk);
		}
		return new self($chunks);
	}


	public function groupBy(Callable $callback)
	{
		$groups = [];

		foreach ($this -> container as $i => $item) {
			$group = $callback($item);
			if (!array_key_exists($group, $groups)) {
				$groups[$group] = new self;
			}
			$groups[$group][] = $item;
		}

		return new static($groups);
	}


}
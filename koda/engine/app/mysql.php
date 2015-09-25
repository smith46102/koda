<?php

namespace App;

/*

	Mysql helper class

	basic usage:

		Mysql :: setHost();
		Mysql :: connect();
		Mysql :: exec(sql);
		Mysql :: escape(value);
		Mysql :: error();
		Mysql :: showResources();

	selecting models:

		Mysql :: select(sql, class)
		Mysql :: selectOne(sql, class)

	for constructing queries:

		Mysql :: prepareSelect(table, where, order, limit)
		Mysql :: prepareInsert(table, fields)
		Mysql :: prepareUpdate(table, fields, where)
		Mysql :: prepareDelete(table, where)

*/

class Mysql {

	private static
		/**
		 * @var Mysqli object
		 */
		$mysqli = null;

	public static
		/**
		 * @var stores current DB connection settings
		 */
		$host = null,

		/**
		 * @var stores table prefix
		 */
		$prefix = '',

		/**
		 * @var number of executed queries
		 */
		$queries = 0;


	/**
	 * Sets current Mysql host configuration
	 *
	 * @param array $host
	 * @return void
	 */
	public static function setHost(array $host)
	{
		self :: $host = $host;
	}


	/**
	 * Connect to mysql with current host configuration
	 *
	 * @return boolean
	 */
	public static function connect()
	{
		if (self :: $mysqli)
			return true;

		if (self :: $host) {
			extract(self :: $host);
			self :: $mysqli = new \mysqli($host, $user, $password, $name);

			if (self :: $mysqli -> connect_error) {
				throw new \Exception('bad mysql connection options');
			}

			self :: $prefix = $prefix;
			self :: exec('SET NAMES UTF8');
			return true;
		}

		if (!self :: $mysql) {
			throw new \Exception('bad mysql connection options');
			return false;
		}
	}


	/**
	 * Executes sql query
	 *
	 * @param string $sql
	 * @return \mysqli_result|boolean
	 */
	public static function exec($sql)
	{
		self :: connect();
		$result = self :: $mysqli -> query($sql);
		$error = self :: error();

		if ($error === '') {
			self :: $queries += 1;
			return $result;
		} else {
			throw new \Exception("<b>mysql query error:</b>\n$error\n\n$sql");
			return false;
		}
	}


	/**
	 * Returns last mysql error
	 *
	 * @return string
	 */
	public static function error()
	{
		if (self :: $mysqli) {
			return self :: $mysqli -> error;
		}
		return '';
	}


	/**
	 * Show queries number for debug
	 * Add queries count to response (json or plain)
	 *
	 * @return void
	 */
	public static function showResources()
	{
		$count = self :: $queries;
		if (Response :: $outJson) {
			Response :: add(['db queries' => $count]);
		} else {
			Response :: add("<!-- db queries {$count} -->");
		}
	}


	/**
	 * Escape string with \Mysqli method
	 *
	 * @param string $str
	 * @return string
	 */
	public static function escape($str)
	{
		self :: connect();
		return self :: $mysqli -> escape_string($str);
	}


	/**
	 * Executes sql like "SELECT ... LIMIT 1"
	 * Returns one model, created with $modelClass
	 *
	 * @param  string $selectQuery
	 * @param  string $modelClass
	 * @return \App\Model
	 */
	public static function selectOne($selectQuery, $modelClass)
	{
		if (class_exists($modelClass) and $result = self :: exec($selectQuery)) {
			$model = $result -> fetch_object($modelClass);
			$result -> free();
			return $model;
		}
		return null;
	}


	/**
	 * Executes sql like "SELECT ... ".
	 * Returns collection of models, created with $modelClass
	 *
	 * @param  string $selectQuery - Mysql select query
	 * @param  string $class - class of Data object, what will store Mysql result row
	 * @param  string $key - what field must be passed as key to result collection
	 * @return \App\Collection
	 */
	public static function select($selectQuery, $modelClass, $key = 'id')
	{
		if (class_exists($modelClass) and $result = self :: exec($selectQuery)) {
			$list = [];
			while ($model = $result -> fetch_object($modelClass)) {
				$list[$model -> {$key}] = $model;
			}
			return new Collection($list);
			$result -> free();
		}
		return null;
	}


	/**
	 * Executes sql like "SELECT ... ".
	 * Returns list of associative arrays.
	 * Use $key to define result list keys.
	 *
	 * @param  string $selectQuery
	 * @param  string $key
	 * @return array
	 */
	public static function selectData($selectQuery, $key = null)
	{
		if ($result = self :: exec($selectQuery)) {
			$list = [];
			while ($row = $result -> fetch_assoc()) {
				if ($key) {
					$list[$row[$key]] = $row;
				} else {
					$list[] = $row;
				}
			}
			$result -> free();
			return $list;
		}
		return null;
	}


	/**
	 * Executes sql like "SELECT ... LIMIT 1".
	 * Returns 1 associative array.
	 *
	 * @param  string $selectQuery
	 * @return array
	 */
	public static function selectDataOne($selectQuery)
	{
		if ($result = self :: exec($selectQuery)) {
			$row = $result -> fetch_assoc();
			$result -> free();
			return $row;
		}
		return null;
	}


	/**
	 * Executes query SELECT FOUND_ROWS()
	 * and returns found rows count.
	 *
	 * @return int
	 */
	public static function selectFoundRows()
	{
		if ($rows = self :: selectDataOne('SELECT FOUND_ROWS() as `c`')) {
			return intval($rows['c']);
		}
		return 0;
	}


	/**
	 * Prepare SELECT query with conditions.
	 * Uses table name with Mysql :: $prefix,
	 * translate $where to where-expression, sets order and limit,
	 * setups $fields to select from.
	 *
	 * @param string $table
	 * @param array  $where
	 * @param stinrg $order
	 * @param string $limit
	 * @param array $fields
	 * @return string
	 */
	public static function prepareSelect($table, array $where = null, $order = null, $limit = null, array $fields = null)
	{
		$fields = $fields ? implode(',', $fields) : '*';
		$where = self :: prepareWhere($where);
		$order = $order ? 'ORDER BY '.$order : 'ORDER BY `id` DESC';
		$limits = $limit ? 'LIMIT '.$limit : '';

		if ($table) {
			$table = self :: $prefix . $table;
			return "SELECT $fields FROM {$table} $where $order $limits";
		}
		return '';
	}


	/**
	 * Create WHERE expression from array
	 *
	 * @param array $where
	 * @return string
	 */
	private static function prepareWhere(array $where = null)
	{
		if (!is_array($where)) {
			return '';
		}
		$result = [];

		foreach ($where as $key => $value) {

			// 'field ' => [1, 2, 3]
			if (is_array($value) and count($value) > 0) {
				$tmp = [];
				foreach ($value as $v) {
					$tmp[] = "'" . self :: escape($v) . "'";
				}
				$values = "'" . implode("', '", $value) . "'";
				$key = self :: escape($key);
				$result[] = "`$key` IN ($values)";

			// 'field LIKE' => '%somevalue%'
			} elseif (preg_match('/^(\w+) LIKE$/ui', $key, $m)) {
				$key = $m[1];
				$value = self :: escape($value);
				$result[] = "`$key` LIKE ('$value')";

			// 'field' => null
			} elseif ($value === null) {
				$key = self :: escape($key);
				$result[] = "`$key` IS NULL";

			// 'field' => 'some value'
			} else {
				$key = self :: escape($key);
				$value = self :: escape($value);
				$result[] = "`$key`='$value'";
			}
		}

		// join parts into one expression
		if (is_array($result)) {
			return 'WHERE '.implode(' AND ', $result);
		}
		return '';
	}


	/**
	 * Prepares UPDATE query for $table
	 * Setup $fields to update, and WHERE expression.
	 *
	 * @param string $table
	 * @param array  $fields
	 * @param array  $where
	 * @return string
	 */
	public static function prepareUpdate($table, $fields, array $where = null)
	{
		$set = self :: prepareSet($fields);
		$where = self :: prepareWhere($where);

		if ($table and $where and $set) {
			$table = self :: $prefix . $table;
			return "UPDATE `$table` SET $set $where";
		}
		return '';
	}


	/**
	 * Prepares Mysql INSERT query in $table, with SET of $fields
	 *
	 * @param string $table
	 * @param array $fields
	 * @return string
	 */
	public static function prepareInsert($table, array $fields)
	{
		$set = self :: prepareSet($fields);

		if ($table and $set) {
			$table = self :: $prefix . $table;
			return "INSERT INTO `$table` SET $set";
		}
		return '';
	}


	/**
	 * Prepares Mysql DELETE query for $table, and WHERE expression,
	 * constructed from $where array
	 *
	 * @param string $table
	 * @param array  $where
	 * @return string
	 */
	public static function prepareDelete($table, array $where = null)
	{
		$where = self :: prepareWhere($where);

		if ($table) {
			$table = self :: $prefix . $table;
			return "DELETE FROM `$table` $where";
		}
		return '';
	}


	/**
	 * Prepares Mysql SET expression for UPDATE and INSERT queries from $fields array
	 * Key 'id' in $fields will be unset, unless $keepID is set to 'true'
	 *
	 * @param array $fields
	 * @param boolean $keepID
	 * @return string
	 */
	private static function prepareSet(array $fields, $keepID = false)
	{
		// for tables with 'id' as FOREIGN KEY
		// we must keep 'id' field
		if (!$keepID or !$fields['id']) {
			unset($fields['id']);
		}

		$result = [];

		foreach ($fields as $field => $value) {
			$field = self :: escape($field);
			if ($value === null) {
				$result[] = "`$field`=NULL";
			} elseif ($value === '') {
				$result[] = "`$field`=''";
			} else {
				$value = self :: escape($value);
				$result[] = "`$field`='$value'";
			}
		}
		return implode(', ', $result);
	}


	/**
	 * Returns last insert id
	 *
	 * @return int
	 */
	public static function insertID()
	{
		if (self :: $mysqli) {
			return self :: $mysqli -> insert_id;
		}
		return 0;
	}


}
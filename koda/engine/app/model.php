<?php

namespace App;

/*

	basic model class
	works with Mysql like ActiveRecord

	Create model:

		$page = new Models\Page(['title' => 'Some Title']);
		$page -> name = 'new page';

	Find models:

		$pages = PageModel :: get(12);
		$pages = PageModel :: find(['name LIKE' => 'something'])
		$pages = PageModel :: findOne(['parent' => '1'])

	Saving, creating, deleting

		$page -> save();
		$page -> save(['name', 'title']);  -- choose what fields must be saved
		$page -> create();
		$page -> delete();

*/

class Model extends Data {

	public $errors = null;
	public $_created = false;
	public $id = null;


	/**
	 * Returns table name for this model
	 *
	 * @return string
	 */
	public static function getTable() {}


	/**
	 * Returns fields array, table contains
	 * this need to make correct SQL for UPDATE and INSERT queries
	 *
	 * @return array
	 */
	public static function describe()
	{
		return ['id'];
	}


	/**
	 * All unset fields will return null
	 * properties like _name will be called as methods getName()
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		if (preg_match('/^_(\w+)$/', $name, $m)) {
			$method = 'get' . $m[1];
			if (method_exists($this, $method)) {
				return $this -> $method();
			}
		}
		return null;
	}


	/**
	 * Construct model with $fields
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
	 * Basic find models, uses $where as array of conditions
	 * $order and $limit to make query
	 *
	 * @param array $where
	 * @param string $order
	 * @param string $limit
	 * @return \Collection
	 */
	public static function find(array $where = null, $order = null, $limit = null)
	{
		if ($sql = Mysql :: prepareSelect(static :: getTable(), $where, $order, $limit)) {
			return Mysql :: select($sql, get_called_class());
		}
		return null;
	}


	/**
	 * Basic find one model by conditions
	 *
	 * @param array $where
	 * @param string $order
	 * @return \Model
	 */
	public static function findOne(array $where = null, $order = null)
	{
		if ($sql = Mysql :: prepareSelect(static :: getTable(), $where, $order, 1)) {
			return Mysql :: selectOne($sql, get_called_class());
		}
		return null;
	}


	/**
	 * Find model by id
	 *
	 * @param int $id
	 * @return \Model
	 */
	public static function get($id)
	{
		if (is_numeric($id)) {
			if ($model = Cache :: getModel(get_called_class(), $id)) {
				return $model;
			} elseif ($model = static :: findOne(['id' => $id])) {
				Cache :: saveModel(get_called_class(), $id, $model);
				return $model;
			}
		}
		return null;
	}


	/**
	 * Get count of models by $where condition
	 *
	 * @param array $where
	 * @return int
	 */
	public static function count(array $where = null)
	{
		$fields = ["count(*) as `count`"];
		$sql = Mysql :: prepareSelect(static :: getTable(), $where, null, null, $fields);
		if ($result = Mysql :: selectDataOne($sql)) {
			return $result['count'];
		}
		return false;
	}


	/**
	 * This called before saving or creating the model
	 * if return false - save or create will stop
	 *
	 * @return boolean
	 */
	protected function beforeSave() { return true; }


	/**
	 * This called after saving or creating the model
	 *
	 * @return \Model
	 */
	protected function afterSave() { return $this; }


	/**
	 * Saves model to table, filter fields by $what
	 *
	 * @param array $what
	 * @return \Model|boolean
	 */
	public function save(array $what = null)
	{
		if ($this -> id and $this -> beforeSave()) {
			$fields = $what ? filterKeys($this -> fields(), $what) : $this -> fields();
			$sql = Mysql :: prepareUpdate(static :: getTable(), $fields, ['id' => $this -> id]);
			if (Mysql :: exec($sql)) {
				return $this -> afterSave();
			} else {
				throw new \Exception(Mysql :: error());
			}
		}
		return false;
	}


	/**
	 * Creates model in table
	 *
	 * @return \Model
	 */
	public function create()
	{
		if ($this -> beforeSave()) {
			$sql = Mysql :: prepareInsert(static :: getTable(), $this -> fields());
			if (Mysql :: exec($sql)) {
				$this -> id = Mysql :: insertID();
				$this -> _created = true;
				return $this -> afterSave();
			} else {
				throw new \Exception(Mysql :: error());
			}
		}
		return false;
	}


	/**
	 * Sets fields from array or
	 * return model fields from this object
	 * fields filtered by describe() - to make them SQL correct for this table
	 *
	 * @param array $fields
	 * @return array
	 */
	public function fields(array $fields = null)
	{
		$describe = array_flip(static :: describe());

		if (is_array($fields)) {
			$fields = array_intersect_key($fields, $describe);
			foreach ($fields as $k => $v) {
				$this -> {$k} = $v;
			}

		} else {
			return array_intersect_key(get_object_vars($this), $describe);
		}
	}


	/**
	 * Called before delete, if return false - delete will stop
	 *
	 * @return boolean
	 */
	protected function beforeDelete() { return true; }


	/**
	 * Called after delete
	 *
	 * @return boolean
	 */
	protected function afterDelete() { return true; }


	/**
	 * Deletes this model from table
	 *
	 * @return boolean
	 */
	public function delete()
	{
		if ($this -> id and $this -> beforeDelete()) {
			$sql = Mysql :: prepareDelete(static :: getTable(), ['id' => $this -> id]);
			if (Mysql :: exec($sql)) {
				return $this -> afterDelete();
			}
		}
		return false;
	}


	/**
	 * Make descendant model class from static class
	 *
	 * @param \Model $model
	 * @return \Model
	 */
	public static function make(Model $model)
	{
		if (is_a($model, 'Model')) {
			return new static(get_object_vars($model));
		}
		return null;
	}


}
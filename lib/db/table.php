<?php

abstract class Db_Table {

	const SQL_ERR_CODE_DUP_KEY = 1062;
	const ORDER_DIR_ASC = 'ASC';
	const ORDER_DIR_DESC = 'DESC';
	protected $table;
	protected $columns = [];
	protected $arguments = [];
	private static $_instances = [];
	private static $exception;
	private static $debug = false;

	protected function __construct(...$arguments) {
		$class = get_class($this);
		$this->table = constant("{$class}::TABLE");
		$this->unpack_args($arguments);
	}

	final public static function i(...$arguments) {
        $class = get_called_class();
        if (!isset(self::$_instances[$class])) {
			self::$_instances[$class] = new $class(...$arguments);
        }
        return self::$_instances[$class];
	}

	protected function unpack_args($arguments) {
		$this->arguments = $arguments;
	}

	protected function get_columns() {
		return $this->columns;
	}

	public function get_by_id($id, $options = null) {
		$options = $this->prepare_sql_options($options);
		return $this->get($options['columns'], 'WHERE id = ?', [$id], true);
	}

	public function get($columns = null, $clause = null, $params = null, $single = false, $options = []) {
		$columns = $columns ?: '*';
		$columns = is_array($columns) ? implode(', ', $columns) : $columns;
		$clause = $clause ? " {$clause}" : '';
		$parenthesis = $options['parenthesis'] ? '(' : '';
		$sql = "{$parenthesis}SELECT {$columns} FROM {$this->table}{$clause}";
		$params = $this->prepare_sql_params($params);
		if (self::debugging()) {
			self::dump($sql, $params);
		}
        try {
			$statement = Db::o()->prepare($sql);
        	$statement->execute($params);
        	return $single ? ($statement->fetch() ?: null) : $statement->fetchAll();
	    } catch (PDOException $ex) {
	    	return self::handle_exception($ex, $statement);
		}
	}

	protected function last_insert_id() {
		return Db::o()->lastInsertId();
	}

	public function insert($insert_fields) {
		$data = $this->prepare_insert_fields($insert_fields);
		$sql = "INSERT INTO {$this->table}
				({$data['columns']})
				VALUES
				({$data['placeholders']})";
		if (self::debugging()) {
			self::dump($sql, $data['values']);
		}
		try {
			$statement = Db::o()->prepare($sql);
	        $result = $statement->execute($data['values']);
	        return $this->last_insert_id();
	    } catch (PDOException $ex) {
	    	return self::handle_exception($ex, $statement);
		}
	}

	public function update_by_id($update_fields, $id) {
		return $this->update($update_fields, 'id = ? LIMIT 1', $id);
	}

	public function update_by_ids($update_fields, $ids) {
		$placeholders = $this->prepare_in_placeholders($ids);
		return $this->update($update_fields, "id IN ({$placeholders})", $ids);
	}

	public function update($update_fields, $where_clause, $where_params = null) {
		$data = $this->prepare_update_fields($update_fields);
		$sql = "UPDATE {$this->table} SET {$data['columns']} WHERE {$where_clause}";
		$where_params = $this->prepare_sql_params($where_params);
		$sql_params = array_merge($data['values'], $where_params);
		if (self::debugging()) {
			self::dump($sql, $sql_params);
		}
		try {
			$statement = Db::o()->prepare($sql);
        	return $statement->execute($sql_params);
        } catch (PDOException $ex) {
	    	return self::handle_exception($ex, $statement);
		}
	}

	public function delete_by_id($id) {
		return $this->delete('id = ? LIMIT 1', $id);
	}

	public function delete_by_ids($ids) {
		$placeholders = $this->prepare_in_placeholders($ids);
		return $this->delete("id IN ({$placeholders})", $ids);
	}

	public function delete($where_clause, $where_params = null) {
		$sql = "DELETE FROM {$this->table} WHERE {$where_clause}";
		$where_params = $this->prepare_sql_params($where_params);
		if (self::debugging()) {
			self::dump($sql, $where_params);
		}
		try {
			$statement = Db::o()->prepare($sql);
        	return $statement->execute($where_params);
        } catch (PDOException $ex) {
	    	return self::handle_exception($ex, $statement);
		}
	}

	public function toggle_by_id($toggle_fields, $id) {
		return $this->toggle($toggle_fields, 'id = ? LIMIT 1', $id);
	}

	public function toggle_by_ids($toggle_fields, $ids) {
		$placeholders = $this->prepare_in_placeholders($ids);
		return $this->toggle($toggle_fields, "id IN ({$placeholders})", $ids);
	}

	public function toggle($toggle_fields, $where_clause, $where_params = null) {
		$toggle_fields = is_array($toggle_fields) ? $toggle_fields : [$toggle_fields];
		$columns = [];
		foreach ($toggle_fields as $field) {
			array_push($columns, "{$field} = !{$field}");
		}
		$columns = implode(', ', $columns);
		$sql = "UPDATE {$this->table} SET {$columns} WHERE {$where_clause}";
		$where_params = $this->prepare_sql_params($where_params);
		if (self::debugging()) {
			self::dump($sql, $where_params);
		}
		try {
			$statement = Db::o()->prepare($sql);
        	return $statement->execute($where_params);
        } catch (PDOException $ex) {
	    	return self::handle_exception($ex, $statement);
		}
	}

	public function exec($sql, $params = null) {
		$params = $this->prepare_sql_params($params);
		if (self::debugging()) {
			self::dump($sql, $params);
		}
		try {
			$statement = Db::o()->prepare($sql);
        	return $statement->execute($params);
        } catch (PDOException $ex) {
	    	return self::handle_exception($ex, $statement);
		}
	}

	protected function begin() {
		return Db::o()->beginTransaction();
	}

	protected function commit() {
		return Db::o()->commit();
	}

	protected function rollback($return_false = false) {
		return $return_false ? false : Db::o()->rollBack();
	}

	public function value_exists($value, $column, $where = null) {
		$sql_params = [$value];
		$sql = "SELECT {$column} FROM {$this->table} WHERE {$column} = ?";
		if ($where) {
			if (is_array($where)) {
				$where_clause = $where['clause'] ?: $where[0];
				$where_params = $where['params'] ?: $where[1];
				if (!is_array($where_params)) {
					$where_params = [$where_params];
				}
			} else {
				$where_clause = $where;
			}
			$sql .= " AND {$where_clause}";
			if ($where_params) {
				$sql_params = array_merge($sql_params, $where_params);
			}
		}
		try {
			$statement = Db::o()->prepare($sql);
	        $statement->execute($sql_params);
	        return $statement->fetchColumn() ? true : false;
	 	} catch (PDOException $ex) {
	    	return self::handle_exception($ex, $statement);
		}
	}

	protected function prepare_insert_fields($data) {
		$columns = $placeholders = $values = [];
		$fields = $this->get_columns();
		foreach ($fields as $column) {
			if (array_key_exists($column, $data)) {
				array_push($columns, $column);
				if (self::is_expr($data[$column])) {
					array_push($placeholders, $data[$column]);
				} else {
					array_push($placeholders, '?');
					array_push($values, $data[$column]);
				}
			}
		}
		return [
			'columns'      => implode(', ', $columns),
			'placeholders' => implode(', ', $placeholders),
			'values'       => $values,
		];
	}

	protected function prepare_update_fields($data) {
		$columns = $values = [];
		$fields = $this->get_columns();
		foreach ($fields as $column) {
			if (array_key_exists($column, $data)) {
				if (self::is_expr($data[$column])) {
					array_push($columns, "{$column} = {$data[$column]}");
				} else {
					array_push($columns, "{$column} = ?");
					array_push($values, $data[$column]);
				}
			}
		}
		return [
			'columns' => implode(', ', $columns),
			'values'  => $values,
		];
	}

	protected function prepare_in_placeholders($list) {
		$placeholders = array_fill(0, count($list), '?');
		$placeholders = implode(', ', $placeholders);
		return $placeholders;
	}

	protected function prepare_sql_options($options) {
		if (!is_array($options)) {
			$options = ['columns' => $options];
		}
		return $options;
	}

	protected function prepare_sql_params($params) {
		if ($params !== null) {
			if (!is_array($params)) {
				$params = [$params];
			}
		} else {
			$params = [];
		}
		return $params;
	}

	protected static function is_expr($value) {
		return $value instanceof Db_Expr;
	}

	public static function exception() {
		return self::$exception;
	}

	public static function error() {
		return self::exception() ? self::exception()->getMessage() : null;
	}

	protected static function handle_exception($ex, $statement = null) {
		self::$exception = $ex;
		if ($statement) {
			self::$exception->sql_err = $statement->errorInfo();
		}
		return false;
	}

	public static function has_error_dup_key() {
		return self::is_error_dup_key(self::exception());
	}

	public static function is_error_dup_key($ex) {
		return $ex->sql_err && $ex->sql_err[1] == self::SQL_ERR_CODE_DUP_KEY;
	}

	protected static function dump($sql, $params, $return = false) {
		$params = is_array($params) ? $params : [$params];
		while(strpos($sql, '?') !== false && $params) {
			$param = array_shift($params);
			$sql = preg_replace('/\?/', "'{$param}'", $sql, 1);
		}
		$limiter = 50;
		while(strpos($sql, ':') !== false && $limiter) {
			$limiter--;
			$sql = preg_replace_callback('/:([a-z_]+)/', function($matches) use ($params) {
				return "'{$params[$matches[1]]}'";
			}, $sql, 1);
		}
		if ($return) {
			return $sql;
		}
		var_dump($sql);
	}

	public static function debug($state = true) {
		self::$debug = $state;
	}

	protected static function debugging() {
		return self::$debug;
	}

}

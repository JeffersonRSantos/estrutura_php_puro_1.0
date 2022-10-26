<?php

namespace Core;
use App\Config;
use Core\Database;

class Querybuilder extends Database {
    private $table;
	private $distinct = "";
	private $wheres = "";
	private $joins = "";
	private $select = "*";
	private $groupBy = "";
	private $orderBy = "";
	private $limit = "";
	private $offset = "";
	private $sql = "";
	public $softDelete = false;
	private $onlyTrash = false;
    public function __construct($tablename) {
		$this->table = $tablename;
		return $this;
	}
	public function setTable($tablename) {
		$this->table = $tablename;
		return $this;
	}
	public function where($column1,$operator,$value=false) {
		if($this->wheres !== "") $this->wheres .= " AND ";
		if($value !== false) {
			$this->wheres .= self::Escape($column1,true) . " " . self::Escape($operator,true) . " " . self::Escape($value);
		}
		else {
			if($operator === null) {
				$this->wheres .= self::Escape($column1,true) . " IS NULL";
			} else {
				$this->wheres .= self::Escape($column1,true) . " = " . self::Escape($operator);
			}
		}
		return $this;
	}
	public function whereNull($column) {
		if($this->wheres !== "") $this->wheres .= " AND ";
		$this->wheres .= self::Escape($column,true) . " IS NULL";
		return $this;
	}
	public function whereNotNull($column) {
		if($this->wheres !== "") $this->wheres .= " AND ";
		$this->wheres .= self::Escape($column,true) . " IS NOT NULL";
		return $this;
	}
	public function whereRaw($where,$bind=[]) {
		if($this->wheres !== "") $this->wheres .= " AND ";
		$this->wheres .= self::pdo_bind($where,$bind);
		return $this;
	}
	public function orWhere($column1,$operator,$value=false) {
		if($this->wheres !== "") $this->wheres .= " OR ";
		if($value !== false) {
			$this->wheres .= self::Escape($column1,true) . " " . self::Escape($operator,true) . " " . self::Escape($value);
		}
		else {
			$this->wheres .= self::Escape($column1,true) . " = " . self::Escape($operator);
		}
		return $this;
	}
	public function orWhereRaw($where,$bind=[]) {
		if($this->wheres !== "") $this->wheres .= " OR ";
		$this->wheres .= self::pdo_bind($where,$bind);
		return $this;
	}
	public function join($table,$where1,$operator,$where2) {
		if($this->joins !== "") $this->joins .= " ";
		$this->joins .= "JOIN " . $table . " ON " . $where1 . " " . $operator . " " . $where2;
		return $this;
	}
	public function leftJoin($table,$where1,$operator,$where2) {
		if($this->joins !== "") $this->joins .= " ";
		$this->joins .= "LEFT JOIN " . $table . " ON " . $where1 . " " . $operator . " " . $where2;
		return $this;
	}
	public function select() {
		$this->select = "";
		foreach (func_get_args() as $param) {
			if($this->select !== "") $this->select .= ", ";
			$this->select .= $param;
		}
		return $this;
	}
	public function insert(array $items = null) {
		if(!$items) return false;
		if(count($items) == 0) return false;
		$columns = "";
		$values = "";
		foreach($items as $k => $v) {
			$columns .= self::Escape($k,true) . ", ";
			$values .= ($v === null ? "NULL" : self::Escape($v)) . ", ";
		}
		$columns = substr($columns,0,-2);
		$values = substr($values,0,-2);
		$this->sql = "INSERT INTO " . $this->table . " (" . $columns . ") VALUES (" . $values . ")";
		$q = self::getDB()->query($this->sql);
		return $q;
	}
	public function update($items) {
		$update = "";
		foreach($items as $k => $v) {
			$update .= self::Escape($k,true) . " = ";
			$update .= ($v === null ? "NULL" : self::Escape($v)) . ", ";
		}
		$update = substr($update,0,-2);
		$this->sql = "UPDATE " . $this->table . " SET " . $update;
		if($this->wheres !== "") $this->sql .= " WHERE " . $this->wheres;
		$q = self::getDB()->query($this->sql);
		return $q;
	}
	public function delete() {
		$this->sql = "DELETE FROM " . $this->table . " WHERE " . $this->wheres;
		if($this->softDelete) $this->sql = "UPDATE " . $this->table . " SET deleted_at = NOW() WHERE " . $this->wheres;
		$q = self::getDB()->query($this->sql);
		return $q;
	}
	public function deleteAll($values) {
		$this->sql = "DELETE FROM " . $this->table;
		$q = self::getDB()->query($this->sql);
		return $q;
	}
	public function distinct($distinct) {
		$this->distinct = $distinct;
		return $this;
	}
	public function groupBy($groupBy) {
		$this->groupBy = $groupBy;
		return $this;
	}
	public function orderBy($order, $sec=null) {
		$this->orderBy = $order;
		if($sec) $this->orderBy .= " " . $sec;
		return $this;
	}
	public function limit($limit) {
		$this->limit = $limit;
		return $this;
	}
	public function offset($offset) {
		$this->offset = $offset;
		return $this;
	}
	public function withTrash() {
		$this->softDelete = false;
		return $this;
	}
	private function genSelect() {
		if($this->softDelete) {
			if($this->wheres !== "") $this->wheres .= " AND ";
			$this->wheres .= ($this->onlyTrash ? "(" . $this->table . ".deleted_at IS NOT NULL)" : "(" . $this->table . ".deleted_at IS NULL)");
		}

		$this->sql = "SELECT " . $this->select . " FROM " . $this->table;
		if($this->distinct !== "") $this->sql = "SELECT DISTINCT " . $this->distinct . " FROM " . $this->table;
		if($this->joins !== "") $this->sql .= " " . $this->joins;
		if($this->wheres !== "") $this->sql .= " WHERE " . $this->wheres;
		if($this->groupBy !== "") $this->sql .= " GROUP BY " . $this->groupBy;
		if($this->orderBy !== "") $this->sql .= " ORDER BY " . $this->orderBy;
		if($this->limit !== "") $this->sql .= " LIMIT " . $this->limit;
		if($this->offset !== "") $this->sql .= " OFFSET " . $this->offset;
		return $this;
	}
	public function first() {
		$this->limit = 1;
		$this->genSelect();
		$q = self::getDB()->query($this->sql)->fetch();
		return $q;
	}
	public function get() {
		$this->genSelect();
		$q = self::getDB()->query($this->sql)->fetchAll();
		return $q;
	}
	public function count() {
		if($this->softDelete) {
			if($this->wheres !== "") $this->wheres .= " AND ";
			$this->wheres .= ($this->onlyTrash ? "(" . $this->table . ".deleted_at IS NOT NULL)" : "(" . $this->table . ".deleted_at IS NULL)");
		}

		$this->sql = "SELECT COUNT(*) FROM " . $this->table;
		if($this->wheres !== "") $this->sql .= " WHERE " . $this->wheres;
		$q = self::getDB()->query($this->sql);
		if(!$q) return false;
		$q = $q->fetch()["COUNT(*)"];

		// Check DISTINCT
		if($this->distinct !== "") {
			$this->sql = "SELECT COUNT(DISTINCT " . $this->distinct . ") FROM " . $this->table;
				if($this->wheres !== "") $this->sql .= " WHERE " . $this->wheres;
				$q = self::getDB()->query($this->sql)->fetch()["COUNT(DISTINCT " . $this->distinct . ")"];
			}
			
		//

			return intval($q);
		}
		public function sum($field) {
			$this->sql = "SELECT SUM({$field}) FROM {$this->table}";
			if($this->wheres !== "") $this->sql .= " WHERE " . $this->wheres;
			$q = self::getDB()->query($this->sql)->fetch();
			return intval($q["SUM({$field})"]);
		}
		public function max($field) {
			$this->sql = "SELECT MAX({$field}) FROM {$this->table}";
			if($this->wheres !== "") $this->sql .= " WHERE " . $this->wheres;
			$q = self::getDB()->query($this->sql)->fetch();
			return intval($q["MAX({$field})"]);
		}
		public function min($field) {
			$this->sql = "SELECT MIN({$field}) FROM {$this->table}";
			if($this->wheres !== "") $this->sql .= " WHERE " . $this->wheres;
			$q = self::getDB()->query($this->sql)->fetch();
			return intval($q["MIN({$field})"]);
		}
		public function avg($field) {
			$this->sql = "SELECT AVG({$field}) FROM {$this->table}";
			if($this->wheres !== "") $this->sql .= " WHERE " . $this->wheres;
			$q = self::getDB()->query($this->sql)->fetch();
			return floatval($q["AVG({$field})"]);
		}
		public function raw($sql,$bind=[]) {
			$sql = self::pdo_bind($sql,$bind);
			$q = self::getDB()->query($sql);
			return $q;
		}
		public function sql() {
			$this->genSelect();
			return $this->sql;
		}
		public static function pdo_bind($prepare, $bind) {
			foreach($bind as $value) {
				$escaped = self::getDB()->quote($value);
				$prepare = preg_replace("/\?/",$escaped,$prepare,1);
			}
			return $prepare;
		}
    }
<?php

use Core\Database;

namespace Core;

class Migrations extends Database {
    private $tableName = "";
	private $fields = "";
	private $endOpt = "";
	private $lastField = "";
	private $softdeletefield = false;
	private $after = null;
	public function table($table) {
		$this->tableName = $table;
        return $this;
	}
	public function increments($field) {
		if($this->fields !== "") $this->fields .= ", ";
		$this->fields .= "{$field} INT UNSIGNED AUTO_INCREMENT";
		return $this;
	}
	public function string($field) {
		if($this->fields !== "") $this->fields .= ", ";
		$this->fields .= "{$field} VARCHAR(255) NOT NULL";
		return $this;
	}
	public function integer($field) {
		if($this->fields !== "") $this->fields .= ", ";
		$this->fields .= "{$field} INT NOT NULL";
		return $this;
	}
	public function biginteger($field) {
		if($this->fields !== "") $this->fields .= ", ";
		$this->fields .= "{$field} BIGINT NOT NULL";
		return $this;
	}
	public function text($field) {
		if($this->fields !== "") $this->fields .= ", ";
		$this->fields .= "{$field} TEXT NOT NULL";
		return $this;
	}
	public function longtext($field) {
		if($this->fields !== "") $this->fields .= ", ";
		$this->fields .= "{$field} LONGTEXT NOT NULL";
		return $this;
	}
	public function decimal($field,$s=15,$d=2) {
		if($this->fields !== "") $this->fields .= ", ";
		$this->fields .= "{$field} DECIMAL({$s},{$d}) NOT NULL";
		return $this;
	}
	public function float($field) {
		if($this->fields !== "") $this->fields .= ", ";
		$this->fields .= "{$field} FLOAT NOT NULL";
		return $this;
	}
	public function timestamp($field) {
		if($this->fields !== "") $this->fields .= ", ";
		$this->fields .= "{$field} TIMESTAMP NOT NULL";
		return $this;
	}
	public function nullable() {
		$this->fields = preg_replace("/(.*) NOT NULL(.*)/","$1$2",$this->fields);
		return $this;
	}
	public function default($value) {
		$this->fields .= " DEFAULT " . self::Escape($value);
		return $this;
	}
	public function unsigned() {
		$this->fields .= " UNSIGNED";
		return $this;
	}
	public function unique() {
		$this->fields .= " UNIQUE";
		return $this;
	}
	public function foreign($field) {
		if($this->fields !== "") $this->fields .= ", ";
		$this->fields .= "{$field} INT UNSIGNED NOT NULL";
		$this->lastField = $field;
		return $this;
	}
	public function references($table) {
		$table = explode(".", $table);
		$column = $table[1];
		$table = $table[0];
		$this->endOpt .= ", FOREIGN KEY ({$this->lastField}) REFERENCES {$table}({$column})";
		return $this;
	}
	public function onDelete($option) {
		$this->endOpt .= " ON DELETE ". strtoupper($option);
		return $this;
	}
	public function onUpdate($option) {
		$this->endOpt .= " ON UPDATE ". strtoupper($option);
		return $this;
	}
	public function softdelete() {
		$this->softdeletefield = true;
		return $this;
	}
	public function make() {
		$this->fields .= ", created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
		if($this->softdeletefield) $this->fields .= ", deleted_at TIMESTAMP NULL";
		$this->endOpt = ", PRIMARY KEY (id)" . $this->endOpt;
		$q = "CREATE TABLE {$this->tableName} ({$this->fields} {$this->endOpt}) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
		return self::getDB()->query($q);
	}
	public function after($column) {
		$this->after = $column;
	}
	public function alter() {
		$q = "ALTER TABLE {$this->tableName} MODIFY {$this->fields}";
		$query = self::getDB()->query($q);
		if($this->endOpt !== "") self::getDB()->query("ALTER TABLE {$this->tableName} ADD " . substr($this->endOpt,2));
		return $query;
	}
	public function add() {
		$q = "ALTER TABLE {$this->tableName} ADD {$this->fields}";
		if($this->after) $q .= " AFTER " . $this->after;
		$query = self::getDB()->query($q);
		if($this->endOpt !== "") self::getDB()->query("ALTER TABLE {$this->tableName} ADD " . substr($this->endOpt,2));
		return $query;
	}

	public static function dropTable($table) {
		self::getDB()->query("DROP TABLE " . $table . ";");
	}
	public static function dropColumn($table, $column) {
		self::getDB()->query("ALTER TABLE " . $table . " DROP COLUMN " . $column . ";");
	}
	public static function dropForeign($table, $column) {
		self::getDB()->query("ALTER TABLE " . $table . " DROP FOREIGN KEY " . $column . ";");
	}
}
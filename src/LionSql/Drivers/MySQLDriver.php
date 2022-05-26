<?php

namespace LionSql\Drivers;

use \PDO;
use \PDOStatement;
use \PDOException;
use LionSql\Connection;

class MySQLDriver extends Connection {

	const FETCH = "fetch";
	const FETCH_ALL = "fetchAll";

	private static string $where = " WHERE";
	private static string $as = " AS";
	private static string $and = " AND";
	private static string $or = " OR";
	private static string $between = " BETWEEN";
	private static string $select = " SELECT";
	private static string $from = " FROM";
	private static string $join = " JOIN";
	private static string $on = " ON";
	private static string $left = " LEFT";
	private static string $right = " RIGHT";
	private static string $inner = " INNER";
	private static string $insert = " INSERT INTO";
	private static string $values = " VALUES";
	private static string $update = " UPDATE";
	private static string $set = " SET";
	private static string $delete = " DELETE";
	private static string $call = " CALL";
	private static string $like = " LIKE";
	private static string $groupBy = ' GROUP BY';
	private static string $asc = ' ASC';
	private static string $desc = ' DESC';
	private static string $orderBy = ' ORDER BY';
	private static string $count = ' COUNT(?)';
	private static string $max = ' MAX(?)';
	private static string $min = ' MIN(?)';
	private static string $sum = ' SUM(?)';
	private static string $avg = ' AVG(?)';
	private static string $limit = ' LIMIT';
	private static string $having = ' HAVING';

	public function __construct() {

	}
	
	public static function init($config): void {
		self::getConnection($config, 'mysql');
	}

	private static function addCharacter(array $files, int $count): string {
		$addValues = "";

		foreach ($files as $key => $file) {
			$addValues.= $key === ($count - 1) ? "?" : "?, ";
		}

		return $addValues;
	}

	public static function findColumn(string $table = "", string $find_column = "", string $columns = "", array $values = []): object {
		$columns_separate = explode(',', $columns);

		if($table === "") {
			return self::$response->error("You must select the table");
		}

		if ($find_column === "") {
			return self::$response->error("You must select the column to search");
		}

		if (count($columns_separate) != count($values)) {
			return self::$response->error("The number of columns must be equal to the number of submitted values");
		}

		if (count($columns_separate) === 1) {
			return self::select(static::FETCH, $table, null, $find_column, [
				self::where($columns_separate[0], '=')
			], $values);
		}

		$addValues = "";
		foreach ($columns_separate as $key => $column) {
			$addValues.= $key === 0 ? self::where(trim($column), '=') : self::and(trim($column), '=');
		}

		return self::select(static::FETCH, $table, null, $find_column, [$addValues], $values);
	}

	public static function limit(bool $index = true): string {
		return !$index ? (self::$limit . " ?") : (self::$limit . " ?, ?");
	}

	public static function min(string $column, ?string $alias = null): string {
		return str_replace("?", $column, self::$min) . " " . ($alias != null ? self::$as . " {$alias}" : '');
	}

	public static function max(string $column, ?string $alias = null): string {
		return str_replace("?", $column, self::$max) . " " . ($alias != null ? self::$as . " {$alias}" : '');
	}

	public static function count(?string $column = null, ?string $alias = null): string {
		return ($column != null ? str_replace("?", $column, self::$count) : str_replace("?", "*", self::$count)) . " " . ($alias != null ? self::$as . " {$alias}" : '');
	}

	public static function avg(string $column, ?string $alias = null): string {
		return str_replace("?", $column, self::$avg) . " " . ($alias != null ? self::$as . " {$alias}" : '');
	}

	public static function sum(?string $column = null, ?string $alias = null): string {
		return str_replace("?", $column, self::$sum) . " " . ($alias != null ? self::$as . " {$alias}" : '');
	}

	public static function orderBy(string $column, ?string $type = null): string {
		return self::$orderBy . " {$column} " . ($type != null ? strtoupper($type) : '');
	}

	public static function groupBy(string $column, ?string $type = null): string {
		return self::$groupBy . " {$column} " . ($type != null ? strtoupper($type) : '');
	}

	public static function having(string $column, ?string $operator = null): string {
		return self::$having . " {$column} " . ($operator != null ? "{$operator} ?" : '');
	}

	public static function like(): string {
		return self::$like . " ?";
	}

	public static function call(string $call_name = "", array $files = []): object {
		try {
			if ($call_name === "") {
				return self::$response->error("You must select the stored procedure");
			}

			$count = count($files);
			if ($count <= 0) {
				return self::$response->error("At least one row must be entered");
			}

			$sql = self::$call . " {$call_name}(" . self::addCharacter($files, $count) . ")";
			if (!self::bindValue(self::prepare($sql), $files)->execute()) {
				return self::$response->error("An error occurred while executing the process");
			}

			return self::$response->success("Execution finished");
		} catch (PDOException $e) {
			return self::$response->error($e->getMessage());
		}
	}

	public static function delete(string $table = "", string $id_column = "", array $files = []): object {
		try {
			if($table === "") {
				return self::$response->error("You must select the table");
			}

			if ($id_column === "") {
				return self::$response->error("You must select the identifier");
			}

			if (count($files) === 0) {
				return self::$response->error("At least one row must be entered");
			}

			$sql = self::$delete . self::$from . " {$table} " . self::$where . " {$id_column}=?";
			if (!self::bindValue(self::prepare($sql), [$files])->execute()) {
				return self::$response->error("An error occurred while executing the process");
			}

			return self::$response->success("Row deleted successfully");
		} catch (PDOException $e) {
			return self::$response->error($e->getMessage());
		}
	}

	public static function update(string $table = "", string $columns = "", array $files = []): object {
		try {
			if ($table === "") {
				return self::$response->error("You must select the table");
			}

			if ($columns === "") {
				return self::$response->error("columns must be specified");
			}

			$columns = explode(":", $columns);
			if (count($columns) <= 1 || $columns[1] === '') {
				return self::$response->error("column must be specified where after ':'");
			}

			$count = count($files);
			$addValues = "";

			if ($count <= 0) {
				return self::$response->error("At least one row must be entered");
			}

			$sql = self::$update . " {$table} " . self::$set . " " . str_replace(",", "=?, ", $columns[0]) . "=? " . self::$where . " {$columns[1]}" . "=?";
			if (!self::bindValue(self::prepare($sql), $files)->execute()) {
				return self::$response->error("An error occurred while executing the process");
			}

			return self::$response->success("Rows updated successfully");
		} catch (PDOException $e) {
			return self::$response->error($e->getMessage());
		}
	}

	public static function insert(string $table = "", string $columns = "", array $files = []): object {
		try {
			if ($table === "") {
				return self::$response->error("You must select the table");
			}

			if ($columns === "") {
				return self::$response->error("You must select the columns");
			}

			$count = count($files);
			if ($count <= 0) {
				return self::$response->error("At least one row must be entered");
			}

			$sql = self::$insert . " {$table} (" . str_replace(",", ", ", $columns) . ")" . self::$values . " (" . self::addCharacter($files, $count) . ")";
			if (!self::bindValue(self::prepare($sql), $files)->execute()) {
				return self::$response->error("An error occurred while executing the process");
			}

			return self::$response->success("Rows inserted correctly");
		} catch (PDOException $e) {
			return self::$response->error($e->getMessage());
		}
	}

	public static function select(string $method, string $table, ?string $alias = null, ?string $columns = null, array $joins = [], array $files = []): object {
		try {
			$addJoins = "";
			if (count($joins) > 0) {
				foreach ($joins as $key => $join) {
					$addJoins.= "{$join} ";
				}
			}

			$sql = self::$select . " " . ($columns != null ? str_replace(",", ", ", $columns) : '*') . " " . self::$from . " {$table} " . ($alias != null ? self::$as . " {$alias} " : '') . " " . $addJoins;
			$prepare = self::prepare($sql);

			if (count($files) <= 0) {
				if ($method === static::FETCH) {
					return self::fetch($prepare);
				} elseif ($method === static::FETCH_ALL) {
					return self::fetchAll($prepare);
				}

				return self::fetchAll($prepare);
			}

			$bind = self::bindValue($prepare, $files);
			if ($method === static::FETCH) {
				return self::fetch($bind);
			} elseif ($method === static::FETCH_ALL) {
				return self::fetchAll($bind);
			}

			return self::fetchAll($bind);
		} catch (PDOException $e) {
			return self::$response->error($e->getMessage());
		}
	}

	public static function where(string $column, ?string $operator = null): string {
		return self::$where . " {$column}" . ($operator != null ? "{$operator}?" : '');
	}

	public static function and(string $column, string $operator): string {
		return self::$and . " {$column}{$operator}?";
	}

	public static function or(string $column, string $operator): string {
		return self::$or . " {$column}{$operator}?";
	}

	public static function between($column): string {
		return self::where($column) . self::$between . " ? " . self::$and . " ? ";
	}

	public static function leftJoin(string $table, ?string $alias, string $condition): string {
		return self::$left . self::$join . " " . ($table) . " " . ($alias != null ? self::$as . " {$alias} " : '') . " " . self::$on . " " . ($condition);
	}

	public static function rightJoin(string $table, ?string $alias, string $condition): string {
		return self::$right . self::$join . " " . ($table) . " " . ($alias != null ? self::$as . " {$alias} " : '') . " " . self::$on . " " . ($condition);
	}

	public static function innerJoin(string $table, ?string $alias, string $condition): string {
		return self::$inner . self::$join . " " . ($table) . " " . ($alias != null ? self::$as . " {$alias} " : '') . " " . self::$on . " " . ($condition);
	}
	
}
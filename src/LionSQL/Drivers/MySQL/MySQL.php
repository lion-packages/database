<?php

namespace LionSQL\Drivers\MySQL;

use \Closure;
use LionSQL\Functions;

class MySQL extends Functions {

	public static function init(array $connections): void {
		self::$mySQL = new MySQL();
		self::$connections = $connections;
		self::$active_connection = self::$connections['default'];
		self::$dbname = self::$connections['connections'][self::$connections['default']]['dbname'];
		self::mysql();
	}

	// ---------------------------------------------------------------------------------------------

	public static function create(): MySQL {
		self::$sql .= self::$keywords['create'];
		return self::$mySQL;
	}

	public static function procedure(): MySQL {
		self::$sql .= self::$keywords['procedure'];
		return self::$mySQL;
	}

	public static function status(): MySQL {
		self::$sql .= self::$keywords['status'];
		return self::$mySQL;
	}

	public static function end(string $end = ";"): MySQL {
		self::$sql .= $end;
		return self::$mySQL;
	}

	public static function full(): MySQL {
		self::$sql .= self::$keywords['full'];
		return self::$mySQL;
	}

	public static function groupQuery(Closure $callback): MySQL {
		self::openGroup(self::$mySQL);
		$callback(self::$mySQL);
		self::closeGroup(self::$mySQL);
		return self::$mySQL;
	}

	public static function recursive(string $name): MySQL|string {
		self::$sql .= self::$keywords['recursive'] . " {$name}" . self::$keywords['as'];
		return self::$mySQL;
	}

	public static function with(bool $return = false): MySQL|string {
		if ($return) {
			return self::$keywords['with'];
		}

		self::$sql .= self::$keywords['with'];
		return self::$mySQL;
	}

	public static function connection(string $connection_name): MySQL {
		self::$active_connection = $connection_name;
		self::$dbname = self::$connections['connections'][$connection_name]['dbname'];
		self::mysql();

		return self::$mySQL;
	}

	public static function table(string $table, bool $option = false, bool $nest = false): MySQL {
		if (!$option) {
			if (!$nest) {
				self::$table = self::$dbname . "." . $table;
			} else {
				self::$sql .= self::$keywords['table'] . " " . self::$dbname . "." . $table;
			}
		} else {
			if (!$nest) {
				self::$table = $table;
			} else {
				self::$sql .= self::$keywords['table'] . " " . $table;
			}
		}

		return self::$mySQL;
	}

	public static function view(string $view, bool $option = false, bool $nest = false): MySQL {
		if (!$option) {
			if (!$nest) {
				self::$view = self::$dbname . "." . $view;
			} else {
				self::$sql .= self::$keywords['view'] . " " . self::$dbname . "." . $view;
			}
		} else {
			if (!$nest) {
				self::$view = $view;
			} else {
				self::$sql .= self::$keywords['view'] . " " . $view;
			}
		}

		return self::$mySQL;
	}

	public static function isNull(): MySQL {
		self::$sql .= self::$keywords['is-null'];
		return self::$mySQL;
	}

	public static function isNotNull(): MySQL {
		self::$sql .= self::$keywords['is-not-null'];
		return self::$mySQL;
	}

	public static function offset(int $increase = 0): MySQL {
		self::$sql .= self::$keywords['offset'] . " ?";
		self::addRows([$increase]);
		return self::$mySQL;
	}

	public static function unionAll(): MySQL {
		self::$sql .= self::$keywords['union'] . self::$keywords['all'];
		return self::$mySQL;
	}

	public static function union(): MySQL {
		self::$sql .= self::$keywords['union'];
		return self::$mySQL;
	}

	public static function as(string $column, string $as): string {
		return $column . self::$keywords['as'] . " {$as}";
	}

	public static function concat() {
		return str_replace("*", implode(", ", func_get_args()), self::$keywords['concat']);
	}

	public static function showCreateTable(): MySQL {
		self::$sql = self::$keywords['show'] . self::$keywords['create'] . self::$keywords['table'] . " " . self::$table;
		return self::$mySQL;
	}

	public static function show(): MySQL {
		self::$sql = self::$keywords['show'];
		return self::$mySQL;
	}

	public static function from(string $from = null): MySQL {
		if ($from === null) {
			if (self::$table === "") {
				self::$sql .= self::$keywords['from'] . " " . self::$view;
			} else {
				self::$sql .= self::$keywords['from'] . " " . self::$table;
			}
		} else {
			self::$sql .= self::$keywords['from'] . " " . $from;
		}

		return self::$mySQL;
	}

	public static function indexes(): MySQL {
		self::$sql .= self::$keywords['index'] . self::$keywords['from'] . " " . self::$table;
		return self::$mySQL;
	}

	public static function drop(): MySQL {
		if (self::$table === "") {
			self::$sql = self::$keywords['drop'] . self::$keywords['view'] . " " . self::$view;
		} else {
			self::$sql = self::$keywords['drop'] . self::$keywords['table'] . " " . self::$table;
		}

		return self::$mySQL;
	}

	public static function constraints(): MySQL {
		self::$sql = self::$keywords['select'] . " CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME" . self::$keywords['from'] . " information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND REFERENCED_COLUMN_NAME IS NOT NULL";
		self::addRows(explode(".", self::$table));
		return self::$mySQL;
	}

	public static function tables(): MySQL {
		self::$sql .= self::$keywords['tables'];
		return self::$mySQL;
	}

	public static function columns(): MySQL {
		self::$sql .= self::$keywords['columns'];
		return self::$mySQL;
	}

	public static function query(string $sql): MySQL {
		self::$sql = $sql;
		self::$message = "Execution finished";
		return self::$mySQL;
	}

	public static function bulk(array $columns, array $rows): MySQL {
		if (count($columns) <= 0) {
			return (object) ['status' => 'database-error', 'message' => 'At least one column must be entered'];
		}

		if (count($rows) <= 0) {
			return (object) ['status' => 'database-error', 'message' => 'At least one row must be entered'];
		}

		foreach ($rows as $key => $row) {
			self::addRows($row);
		}

		self::$message = "Rows inserted successfully";
		self::$sql = self::$keywords['insert'] . " " . self::$table . " (" . self::addColumns($columns) . ")" . self::$keywords['values'] . " " . self::addCharacterBulk($rows);
		return self::$mySQL;
	}

	public static function in(): MySQL {
		$columns = func_get_args();
		self::addRows($columns);
		self::$sql .= str_replace("?", self::addCharacter($columns), self::$keywords['in']);
		return self::$mySQL;
	}

	public static function call(string $store_procedure, array $rows = []): MySQL {
		if (count($rows) <= 0) {
			return (object) ['status' => 'database-error', 'message' => 'At least one row must be entered'];
		}

		self::addRows($rows);
		self::$message = "Procedure executed successfully";
		self::$sql .= self::$keywords['call'] . " " . self::$dbname . ".{$store_procedure}(" . self::addCharacter($rows) . ")";

		return self::$mySQL;
	}

	public static function delete(): MySQL {
		self::$message = "Rows deleted successfully";
		self::$sql .= self::$keywords['delete'] . self::$keywords['from'] . " " . self::$table;
		return self::$mySQL;
	}

	public static function update(array $rows = []): MySQL {
		if (count($rows) <= 0) {
			return (object) ['status' => 'database-error', 'message' => 'At least one row must be entered'];
		}

		self::addRows($rows);
		self::$message = "Rows updated successfully";
		self::$sql .= self::$keywords['update'] . " " . self::$table . self::$keywords['set'] . " " . self::addCharacterEqualTo($rows);

		return self::$mySQL;
	}

	public static function insert(array $rows = []): MySQL {
		if (count($rows) <= 0) {
			return (object) ['status' => 'database-error', 'message' => 'At least one row must be entered'];
		}

		self::addRows($rows);
		self::$message = "Rows inserted successfully";
		self::$sql .= self::$keywords['insert'] . self::$keywords['into'] . " " . self::$table . " (" . self::addColumns(array_keys($rows)) . ")" . self::$keywords['values'] . " (" . self::addCharacterAssoc($rows) . ")";

		return self::$mySQL;
	}

	public static function having(string $column, ?string $value = null): MySQL {
		self::$sql .= self::$keywords['having'] . " {$column}";
		self::$data_info[] = $value;
		return self::$mySQL;
	}

	public static function select(): MySQL {
		$stringColumns = self::addColumns(func_get_args());

		if (self::$table === "") {
			self::$sql .= self::$keywords['select'] . " {$stringColumns}" . self::$keywords['from'] . " " . self::$view;
		} else {
			self::$sql .= self::$keywords['select'] . " {$stringColumns}" . self::$keywords['from'] . " " . self::$table;
		}

		return self::$mySQL;
	}

	public static function selectDistinct(): MySQL {
		$stringColumns = self::addColumns(func_get_args());

		if (self::$table === "") {
			self::$sql .= self::$keywords['select'] . self::$keywords['distinct'] . " {$stringColumns}" . self::$keywords['from'] . " " . self::$view;
		} else {
			self::$sql .= self::$keywords['select'] . self::$keywords['distinct'] . " {$stringColumns}" . self::$keywords['from'] . " " . self::$table;
		}

		return self::$mySQL;
	}

	public static function between(mixed $between, mixed $and): MySQL {
		self::$sql .= self::$keywords['between'] . " ?" . self::$keywords['and'] . " ? ";
		self::$data_info[] = $between;
		self::$data_info[] = $and;
		return self::$mySQL;
	}

	public static function like(string $like): MySQL {
		self::$sql .= self::$keywords['like'] . " " . self::addCharacter([$like]);
		self::$data_info[] = $like;
		return self::$mySQL;
	}

	public static function groupBy(): MySQL {
		self::$sql .= self::$keywords['groupBy'] . " " . self::addColumns(func_get_args());
		return self::$mySQL;
	}

	public static function limit(int $start, ?int $limit = null): MySQL {
		$items = [$start];

		if (!empty($limit)) {
			$items[] = $limit;
		}

		self::$sql .= self::$keywords['limit'] . " " . self::addCharacter($items);
		self::$data_info[] = $start;

		if (!empty($limit)) {
			self::$data_info[] = $limit;
		}

		return self::$mySQL;
	}

	public static function asc(bool $is_string = false): MySQL|string {
		if ($is_string) {
			return self::$keywords['asc'];
		}

		self::$sql .= self::$keywords['asc'];
		return self::$mySQL;
	}

	public static function desc(bool $is_string = false): MySQL|string {
		if ($is_string) {
			return self::$keywords['desc'];
		}

		self::$sql .= self::$keywords['desc'];
		return self::$mySQL;
	}

	public static function orderBy(): MySQL {
		self::$sql .= self::$keywords['orderBy'] . " " . self::addColumns(func_get_args());
		return self::$mySQL;
	}

	public static function innerJoin(string $table, string $value_from, string $value_up_to, bool $option = false): MySQL {
		if (!$option) {
			self::$sql .= self::$keywords['inner'] . self::$keywords['join'] . " " . self::$dbname . ".{$table}" . self::$keywords['on'] . " {$value_from}={$value_up_to}";
		} else {
			self::$sql .= self::$keywords['inner'] . self::$keywords['join'] . " {$table}" . self::$keywords['on'] . " {$value_from}={$value_up_to}";
		}

		return self::$mySQL;
	}

	public static function leftJoin(string $table, string $value_from, string $value_up_to): MySQL {
		self::$sql .= self::$keywords['left'] . self::$keywords['join'] . " " . self::$dbname . ".{$table}" . self::$keywords['on'] . " {$value_from}={$value_up_to}";
		return self::$mySQL;
	}

	public static function rightJoin(string $table, string $value_from, string $value_up_to): MySQL {
		self::$sql .= self::$keywords['right'] . self::$keywords['join'] . " " . self::$dbname . ".{$table}" . self::$keywords['on'] . " {$value_from}={$value_up_to}";
		return self::$mySQL;
	}

	public static function where(string $value_type, mixed $value = null): MySQL {
		self::$sql .= !empty($value) ? self::$keywords['where'] . " {$value_type}" : self::$keywords['where'] . " {$value_type}";

		if (!empty($value)) {
			self::$data_info[] = $value;
		}

		return self::$mySQL;
	}

	public static function and(string $value_type, mixed $value = null): MySQL {
		self::$sql .= !empty($value) ? self::$keywords['and'] . " {$value_type}" : self::$keywords['and'] . " {$value_type}";

		if (!empty($value)) {
			self::$data_info[] = $value;
		}

		return self::$mySQL;
	}

	public static function or(string $value_type, mixed $value): MySQL {
		self::$sql .= !empty($value) ? self::$keywords['or'] . " {$value_type}" : self::$keywords['or'] . " {$value_type}";

		if (!empty($value)) {
			self::$data_info[] = $value;
		}
		return self::$mySQL;
	}

	public static function column(string $value, string $table = ""): string {
		if ($table === "") {
			return $value;
		}

		return "{$table}.{$value}";
	}

	public static function equalTo(string $column): string {
		return $column . "=?";
	}

	public static function greaterThan(string $column): string {
		return $column . " > ?";
	}

	public static function lessThan(string $column): string {
		return $column . " < ?";
	}

	public static function greaterThanOrEqualTo(string $column): string {
		return $column . " >= ?";
	}

	public static function lessThanOrEqualTo(string $column): string {
		return $column . " <= ?";
	}

	public static function notEqualTo(string $column): string {
		return $column . " <> ?";
	}

	public static function min(string $column): string {
		return trim(str_replace("?", $column, self::$keywords['min']));
	}

	public static function max(string $column): string {
		return trim(str_replace("?", $column, self::$keywords['max']));
	}

	public static function avg(string $column): string {
		return trim(str_replace("?", $column, self::$keywords['avg']));
	}

	public static function sum(string $column): string {
		return trim(str_replace("?", $column, self::$keywords['sum']));
	}

	public static function count(string $column): string {
		return trim(str_replace("?", $column, self::$keywords['count']));
	}

	public static function day(string $column): string {
		return trim(str_replace("?", $column, self::$keywords['day']));
	}

	public static function month(string $column): string {
		return trim(str_replace("?", $column, self::$keywords['month']));
	}

	public static function year(string $column): string {
		return trim(str_replace("?", $column, self::$keywords['year']));
	}

}
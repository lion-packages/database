<?php

namespace LionDatabase\Drivers\MySQL;

use \Closure;

class MySQL extends \LionDatabase\Functions {

	public static function init(array $connections): void {
		self::$mySQL = new MySQL();
		self::$connections = $connections;
		self::$active_connection = self::$connections['default'];
		self::$dbname = self::$connections['connections'][self::$connections['default']]['dbname'];
	}

	// ---------------------------------------------------------------------------------------------

	public static function transaction(): MySQL {
		self::$is_transaction = true;
		return self::$mySQL;
	}

	public static function create(): MySQL {
		self::$sql .= self::$words['create'];
		return self::$mySQL;
	}

	public static function procedure(): MySQL {
		self::$sql .= self::$words['procedure'];
		return self::$mySQL;
	}

	public static function status(): MySQL {
		self::$sql .= self::$words['status'];
		return self::$mySQL;
	}

	public static function end(string $end = ";"): MySQL {
		self::$sql .= $end;
		return self::$mySQL;
	}

	public static function full(): MySQL {
		self::$sql .= self::$words['full'];
		return self::$mySQL;
	}

	public static function groupQuery(Closure $callback): MySQL {
		self::openGroup(self::$mySQL);
		$callback(self::$mySQL);
		self::closeGroup(self::$mySQL);
		return self::$mySQL;
	}

	public static function recursive(string $name): MySQL|string {
		self::$sql .= self::$words['recursive'] . " {$name}" . self::$words['as'];
		return self::$mySQL;
	}

	public static function with(bool $return = false): MySQL|string {
		if ($return) {
			return self::$words['with'];
		}

		self::$sql .= self::$words['with'];
		return self::$mySQL;
	}

	public static function connection(string $connection_name): MySQL {
		self::$active_connection = $connection_name;
		self::$dbname = self::$connections['connections'][$connection_name]['dbname'];
		return self::$mySQL;
	}

	public static function table(string $table, bool $option = false, bool $nest = false): MySQL {
		if (!$option) {
			if (!$nest) {
				self::$table = self::$dbname . "." . $table;
			} else {
				self::$sql .= self::$words['table'] . " " . self::$dbname . "." . $table;
			}
		} else {
			if (!$nest) {
				self::$table = $table;
			} else {
				self::$sql .= self::$words['table'] . " " . $table;
			}
		}

		return self::$mySQL;
	}

	public static function view(string $view, bool $option = false, bool $nest = false): MySQL {
		if (!$option) {
			if (!$nest) {
				self::$view = self::$dbname . "." . $view;
			} else {
				self::$sql .= self::$words['view'] . " " . self::$dbname . "." . $view;
			}
		} else {
			if (!$nest) {
				self::$view = $view;
			} else {
				self::$sql .= self::$words['view'] . " " . $view;
			}
		}

		return self::$mySQL;
	}

	public static function isNull(): MySQL {
		self::$sql .= self::$words['is-null'];
		return self::$mySQL;
	}

	public static function isNotNull(): MySQL {
		self::$sql .= self::$words['is-not-null'];
		return self::$mySQL;
	}

	public static function offset(int $increase = 0): MySQL {
		self::$sql .= self::$words['offset'] . " ?";
		self::addRows([$increase]);
		return self::$mySQL;
	}

	public static function unionAll(): MySQL {
		self::$sql .= self::$words['union'] . self::$words['all'];
		return self::$mySQL;
	}

	public static function union(): MySQL {
		self::$sql .= self::$words['union'];
		return self::$mySQL;
	}

	public static function as(string $column, string $as): string {
		return $column . self::$words['as'] . " {$as}";
	}

	public static function concat() {
		return str_replace("*", implode(", ", func_get_args()), self::$words['concat']);
	}

	public static function showCreateTable(): MySQL {
		self::$sql = self::$words['show'] . self::$words['create'] . self::$words['table'] . " " . self::$table;
		return self::$mySQL;
	}

	public static function show(): MySQL {
		self::$actual_code = uniqid();
		self::$fetch_mode[self::$actual_code] = \PDO::FETCH_OBJ;
		self::$sql = self::$words['show'];
		return self::$mySQL;
	}

	public static function from(string $from = null): MySQL {
		if ($from === null) {
			if (self::$table === "") {
				self::$sql .= self::$words['from'] . " " . self::$view;
			} else {
				self::$sql .= self::$words['from'] . " " . self::$table;
			}
		} else {
			self::$sql .= self::$words['from'] . " " . $from;
		}

		return self::$mySQL;
	}

	public static function indexes(): MySQL {
		self::$sql .= self::$words['index'] . self::$words['from'] . " " . self::$table;
		return self::$mySQL;
	}

	public static function drop(): MySQL {
		if (self::$table === "") {
			self::$sql = self::$words['drop'] . self::$words['view'] . " " . self::$view;
		} else {
			self::$sql = self::$words['drop'] . self::$words['table'] . " " . self::$table;
		}

		return self::$mySQL;
	}

	public static function constraints(): MySQL {
		self::$sql = self::$words['select'] . " CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME" . self::$words['from'] . " information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND REFERENCED_COLUMN_NAME IS NOT NULL";
		self::addRows(explode(".", self::$table));
		return self::$mySQL;
	}

	public static function tables(): MySQL {
		self::$sql .= self::$words['tables'];
		return self::$mySQL;
	}

	public static function columns(): MySQL {
		self::$sql .= self::$words['columns'];
		return self::$mySQL;
	}

	public static function query(string $sql): MySQL {
		self::$actual_code = uniqid();
		self::$sql .= $sql;
		self::$message = "Execution finished";
		return self::$mySQL;
	}

	public static function bulk(array $columns, array $rows): MySQL {
		self::$actual_code = uniqid();
		foreach ($rows as $key => $row) {
			self::addRows($row);
		}

		self::$message = "Rows inserted successfully";
		self::$sql = self::$words['insert'] . " " . self::$table . " (" . self::addColumns($columns) . ")" . self::$words['values'] . " " . self::addCharacterBulk($rows);
		return self::$mySQL;
	}

	public static function in(): MySQL {
		$columns = func_get_args();
		self::addRows($columns);
		self::$sql .= str_replace("?", self::addCharacter($columns), self::$words['in']);
		return self::$mySQL;
	}

	public static function call(string $store_procedure, array $rows = []): MySQL {
		self::$actual_code = uniqid();
		self::addRows($rows);
		self::$message = "Procedure executed successfully";
		self::$sql .= self::$words['call'] . " " . self::$dbname . ".{$store_procedure}(" . self::addCharacter($rows) . ")";
		return self::$mySQL;
	}

	public static function delete(): MySQL {
		self::$actual_code = uniqid();
		self::$message = "Rows deleted successfully";
		self::$sql .= self::$words['delete'] . self::$words['from'] . " " . self::$table;
		return self::$mySQL;
	}

	public static function update(array $rows = []): MySQL {
		self::$actual_code = uniqid();
		self::addRows($rows);
		self::$message = "Rows updated successfully";
		self::$sql .= self::$words['update'] . " " . self::$table . self::$words['set'] . " " . self::addCharacterEqualTo($rows);

		return self::$mySQL;
	}

	public static function insert(array $rows = []): MySQL {
		self::$actual_code = uniqid();
		self::addRows($rows);
		self::$message = "Rows inserted successfully";
		self::$sql .= self::$words['insert'] . self::$words['into'] . " " . self::$table . " (" . self::addColumns(array_keys($rows)) . ")" . self::$words['values'] . " (" . self::addCharacterAssoc($rows) . ")";

		return self::$mySQL;
	}

	public static function having(string $column, ?string $value = null): MySQL {
		self::$sql .= self::$words['having'] . " {$column}";
		self::addRows([$value]);
		return self::$mySQL;
	}

	public static function select(): MySQL {
		self::$actual_code = uniqid();
		self::$fetch_mode[self::$actual_code] = \PDO::FETCH_OBJ;
		$stringColumns = self::addColumns(func_get_args());

		if (self::$table === "") {
			self::$sql .= self::$words['select'] . " {$stringColumns}" . self::$words['from'] . " " . self::$view;
		} else {
			self::$sql .= self::$words['select'] . " {$stringColumns}" . self::$words['from'] . " " . self::$table;
		}

		return self::$mySQL;
	}

	public static function selectDistinct(): MySQL {
		self::$actual_code = uniqid();
		self::$fetch_mode[self::$actual_code] = \PDO::FETCH_OBJ;
		$stringColumns = self::addColumns(func_get_args());

		if (self::$table === "") {
			self::$sql .= self::$words['select'] . self::$words['distinct'] . " {$stringColumns}" . self::$words['from'] . " " . self::$view;
		} else {
			self::$sql .= self::$words['select'] . self::$words['distinct'] . " {$stringColumns}" . self::$words['from'] . " " . self::$table;
		}

		return self::$mySQL;
	}

	public static function between(mixed $between, mixed $and): MySQL {
		self::$sql .= self::$words['between'] . " ?" . self::$words['and'] . " ? ";
		self::addRows([$between, $and]);
		return self::$mySQL;
	}

	public static function like(string $like): MySQL {
		self::$sql .= self::$words['like'] . " " . self::addCharacter([$like]);
		self::addRows([$like]);
		return self::$mySQL;
	}

	public static function groupBy(): MySQL {
		self::$sql .= self::$words['groupBy'] . " " . self::addColumns(func_get_args());
		return self::$mySQL;
	}

	public static function limit(int $start, ?int $limit = null): MySQL {
		$items = [$start];

		if (!empty($limit)) {
			$items[] = $limit;
		}

		self::$sql .= self::$words['limit'] . " " . self::addCharacter($items);
		self::addRows([$start]);

		if (!empty($limit)) {
			self::addRows([$limit]);
		}

		return self::$mySQL;
	}

	public static function asc(bool $is_string = false): MySQL|string {
		if ($is_string) {
			return self::$words['asc'];
		}

		self::$sql .= self::$words['asc'];
		return self::$mySQL;
	}

	public static function desc(bool $is_string = false): MySQL|string {
		if ($is_string) {
			return self::$words['desc'];
		}

		self::$sql .= self::$words['desc'];
		return self::$mySQL;
	}

	public static function orderBy(): MySQL {
		self::$sql .= self::$words['orderBy'] . " " . self::addColumns(func_get_args());
		return self::$mySQL;
	}

	public static function inner(): MySQL {
		self::$sql .= self::$words['inner'];
		return self::$mySQL;
	}

	public static function left(): MySQL {
		self::$sql .= self::$words['left'];
		return self::$mySQL;
	}

	public static function right(): MySQL {
		self::$sql .= self::$words['right'];
		return self::$mySQL;
	}

	public static function join(string $table, string $value_from, string $value_up_to, bool $option = false) {
		if (!$option) {
			self::$sql .= self::$words['join'] . " " . self::$dbname . ".{$table}" . self::$words['on'] . " {$value_from}={$value_up_to}";
		} else {
			self::$sql .= self::$words['join'] . " {$table}" . self::$words['on'] . " {$value_from}={$value_up_to}";
		}

		return self::$mySQL;
	}

	public static function where(string $value_type, mixed $value = null): MySQL {
		self::$sql .= !empty($value) ? (self::$words['where'] . " {$value_type}") : (self::$words['where'] . " {$value_type}");

		if (!empty($value)) {
			self::addRows([$value]);
		}

		return self::$mySQL;
	}

	public static function and(string $value_type, mixed $value = null): MySQL {
		self::$sql .= !empty($value) ? (self::$words['and'] . " {$value_type}") : (self::$words['and'] . " {$value_type}");

		if (!empty($value)) {
			self::addRows([$value]);
		}

		return self::$mySQL;
	}

	public static function or(string $value_type, mixed $value = null): MySQL {
		self::$sql .= !empty($value) ? (self::$words['or'] . " {$value_type}") : (self::$words['or'] . " {$value_type}");

		if (!empty($value)) {
			self::addRows([$value]);
		}

		return self::$mySQL;
	}

	public static function column(string $value, string $table = ""): string {
		return $table === "" ? trim($value) : trim("{$table}.{$value}");
	}

	public static function equalTo(string $column): string {
		return trim($column . "=?");
	}

	public static function greaterThan(string $column): string {
		return trim($column . " > ?");
	}

	public static function lessThan(string $column): string {
		return trim($column . " < ?");
	}

	public static function greaterThanOrEqualTo(string $column): string {
		return trim($column . " >= ?");
	}

	public static function lessThanOrEqualTo(string $column): string {
		return trim($column . " <= ?");
	}

	public static function notEqualTo(string $column): string {
		return trim($column . " <> ?");
	}

	public static function min(string $column): string {
		return trim(str_replace("?", $column, self::$words['min']));
	}

	public static function max(string $column): string {
		return trim(str_replace("?", $column, self::$words['max']));
	}

	public static function avg(string $column): string {
		return trim(str_replace("?", $column, self::$words['avg']));
	}

	public static function sum(string $column): string {
		return trim(str_replace("?", $column, self::$words['sum']));
	}

	public static function count(string $column): string {
		return trim(str_replace("?", $column, self::$words['count']));
	}

	public static function day(string $column): string {
		return trim(str_replace("?", $column, self::$words['day']));
	}

	public static function month(string $column): string {
		return trim(str_replace("?", $column, self::$words['month']));
	}

	public static function year(string $column): string {
		return trim(str_replace("?", $column, self::$words['year']));
	}

}
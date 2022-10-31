<?php

namespace LionSQL\Drivers;

use \PDO;
use \PDOException;
use LionSQL\Connection;

class MySQLDriver extends Connection {

	protected static int $cont = 1;
	protected static string $selected_fetch = "";
	protected static string $sql = "";
	protected static string $class_name = "";
	protected static string $dbname = "";
	protected static string $table = "";
	protected static string $store_procedure = "";
	protected static string $success_message = "";
	protected static string $message = "";
	protected static array $data_info = [];

	public function __construct() {

	}

	public static function init($config): object {
		self::$dbname = $config['dbname'];
		self::$mySQLDriver = new MySQLDriver();
		return self::getConnection($config);
	}

	// ---------------------------------------------------------------------------------------------

	public static function prepare(): MySQLDriver {
		echo("'" . trim(self::$sql) . "'\n\n");
		self::$stmt = self::$conn->prepare(trim(self::$sql));
		return self::$mySQLDriver;
	}

	public static function fetchClass(mixed $class): MySQLDriver {
		self::$class_name = $class;
		return self::$mySQLDriver;
	}

	private static function addRows($rows): void {
		foreach ($rows as $key => $row) {
			self::$data_info[] = $row;
		}
	}

	public static function table(string $table): MySQLDriver {
		self::$table = self::$dbname . ".{$table}";
		return self::$mySQLDriver;
	}

	public static function bindValue(array $list): MySQLDriver {
		$type = function($value) {
			if (gettype($value) === 'integer') {
				return PDO::PARAM_INT;
			} elseif (gettype($value) === 'boolean') {
				return PDO::PARAM_BOOL;
			} elseif (gettype($value) === 'NULL') {
				return PDO::PARAM_NULL;
			} else {
				return PDO::PARAM_STR;
			}
		};

		foreach ($list as $key => $value) {
			self::$stmt->bindValue(self::$cont, $value, $type($value));
			self::$cont++;
		}

		return self::$mySQLDriver;
	}

	public static function execute(string $success_message = "", string $error_message = ""): array|object {
		try {
			self::prepare();
			self::bindValue(self::$data_info);
			self::$stmt->execute();
			return self::$response->success(empty($success_message) ? self::$message : $success_message);
		} catch (PDOException $e) {
			self::$response->finish(
				self::$response->response("database-error", empty($error_message) ? $e->getMessage() : $error_message, (object) [
					'file' => $e->getFile(),
					'line' => $e->getLine()
				])
			);
		}
	}

	public static function get(): array|object {
		self::prepare();

		if (count(self::$data_info) > 0) {
			self::bindValue(self::$data_info);
		}

		return self::fetch();
	}

	public static function getAll(): array|object {
		self::prepare();

		if (count(self::$data_info) > 0) {
			self::bindValue(self::$data_info);
		}

		return self::fetchAll();
	}

	public static function fetch(): array|object {
		try {
			if (!self::$stmt->execute()) {
				return self::$response->error("An unexpected error has occurred");
			}

			if (empty(self::$class_name)) {
				$request = self::$stmt->fetch();
			} else {
				self::$stmt->setFetchMode(PDO::FETCH_CLASS, self::$class_name);
				$request = self::$stmt->fetch();
				self::$class_name = "";
			}

			return !$request ? self::$response->success("No data available") : $request;
		} catch (PDOException $e) {
			self::$response->finish(
				self::$response->response("database-error", $e->getMessage(), (object) [
					'file' => $e->getFile(),
					'line' => $e->getLine()
				])
			);
		}
	}

	public static function fetchAll(): array|object {
		try {
			if (!self::$stmt->execute()) {
				return self::$response->error("An unexpected error has occurred");
			}

			if (empty(self::$class_name)) {
				$request = self::$stmt->fetchAll();
			} else {
				self::$stmt->setFetchMode(PDO::FETCH_CLASS, self::$class_name);
				$request = self::$stmt->fetchAll();
				self::$class_name = "";
			}

			return !$request ? self::$response->success("No data available") : $request;
		} catch (PDOException $e) {
			self::$response->finish(
				self::$response->response("database-error", $e->getMessage(), (object) [
					'file' => $e->getFile(),
					'line' => $e->getLine()
				])
			);
		}
	}

	// ---------------------------------------------------------------------------------------------

	public static function call(string $store_procedure, array $rows = []): MySQLDriver {
		$count = count($rows);
		if ($count <= 0) {
			return self::$response->error("At least one row must be entered");
		}

		self::addRows($rows);
		self::$sql = self::$keywords['call'] . " " . self::$dbname . ".{$store_procedure}(" . self::addCharacter($rows) . ")";
		self::$message = "Execution finished";
		return self::$mySQLDriver;
	}

	public static function delete(): MySQLDriver {
		self::$sql = self::$keywords['delete'] . self::$keywords['from'] . " " . self::$table;
		self::$message = "Rows deleted successfully";
		return self::$mySQLDriver;
	}

	public static function update(array $rows = []): MySQLDriver {
		$count = count($rows);
		if ($count <= 0) {
			return self::$response->error("At least one row must be entered");
		}

		self::addRows($rows);
		self::$sql = self::$keywords['update'] . " " . self::$table . self::$keywords['set'] . " " . self::addCharacterEqualTo($rows);
		self::$message = "Rows updated successfully";
		return self::$mySQLDriver;
	}

	public static function insert(array $rows = []): MySQLDriver {
		$count = count($rows);
		if ($count <= 0) {
			return self::$response->response("database-error", "At least one row must be entered");
		}

		self::addRows($rows);
		self::$sql = self::$keywords['insert'] . " " . self::$table . " (" . self::addColumns(array_keys($rows)) . ")" . self::$keywords['values'] . " (" . self::addCharacterAssoc($rows) . ")";
		self::$message = "successfully executed";
		return self::$mySQLDriver;
	}

	public static function having(string $column, ?string $value = null): MySQLDriver {
		self::$sql.= self::$keywords['having'] . " {$column}";
		self::$data_info[] = $value;
		return self::$mySQLDriver;
	}

	public static function select(): MySQLDriver {
		$columns = func_get_args();
		$stringColumns = self::addColumns(count($columns) > 0 ? $columns : []);
		self::$sql = self::$keywords['select'] . " {$stringColumns}" . self::$keywords['from'] . " " . self::$table;
		return self::$mySQLDriver;
	}

	public static function between(mixed $between, mixed $and): MySQLDriver {
		self::$sql.= self::$keywords['between'] . " ?" . self::$keywords['and'] . " ? ";
		self::$data_info[] = $between;
		self::$data_info[] = $and;
		return self::$mySQLDriver;
	}

	public static function like(string $like): MySQLDriver {
		self::$sql.= self::$keywords['like'] . " " . self::addCharacter([$like]);
		self::$data_info[] = $like;
		return self::$mySQLDriver;
	}

	public static function groupBy(string $column): MySQLDriver {
		self::$sql.= self::$keywords['groupBy'] . " {$column}";
		return self::$mySQLDriver;
	}

	public static function limit(int $start, ?int $limit = null): MySQLDriver {
		$items = [$start];

		if (!empty($limit)) {
			$items[] = $limit;
		}

		self::$sql.= self::$keywords['limit'] . " " . self::addCharacter($items);
		self::$data_info[] = $start;

		if (!empty($limit)) {
			self::$data_info[] = $limit;
		}

		return self::$mySQLDriver;
	}

	public static function asc(): MySQLDriver {
		self::$sql.= self::$keywords['asc'];
		return self::$mySQLDriver;
	}

	public static function desc(): MySQLDriver {
		self::$sql.= self::$keywords['desc'];
		return self::$mySQLDriver;
	}

	public static function orderBy(string $column): MySQLDriver {
		self::$sql.= self::$keywords['orderBy'] . " {$column}";
		return self::$mySQLDriver;
	}

	public static function innerJoin(string $table, string $value_from, string $value_up_to): MySQLDriver {
		self::$sql.= self::$keywords['inner'] . self::$keywords['join'] . " " . self::$dbname . ".{$table}" . self::$keywords['on'] . " {$value_from}={$value_up_to}";
		return self::$mySQLDriver;
	}

	public static function leftJoin(string $table, string $value_from, string $value_up_to): MySQLDriver {
		self::$sql.= self::$keywords['left'] . self::$keywords['join'] . " " . self::$dbname . ".{$table}" . self::$keywords['on'] . " {$value_from}={$value_up_to}";
		return self::$mySQLDriver;
	}

	public static function rightJoin(string $table, string $value_from, string $value_up_to): MySQLDriver {
		self::$sql.= self::$keywords['right'] . self::$keywords['join'] . " " . self::$dbname . ".{$table}" . self::$keywords['on'] . " {$value_from}={$value_up_to}";
		return self::$mySQLDriver;
	}

	public static function where(string $value_type, mixed $value = null): MySQLDriver {
		self::$sql.= !empty($value) ? self::$keywords['where'] . " {$value_type}" : self::$keywords['where'] . " {$value_type}";

		if (!empty($value)) {
			self::$data_info[] = $value;
		}

		return self::$mySQLDriver;
	}

	public static function and(string $value_type, mixed $value): MySQLDriver {
		self::$sql.= self::$keywords['and'] . " {$value_type}";
		self::$data_info[] = $value;
		return self::$mySQLDriver;
	}

	public static function or(string $value_type, mixed $value): MySQLDriver {
		self::$sql.= self::$keywords['or'] . " {$value_type}";
		self::$data_info[] = $value;
		return self::$mySQLDriver;
	}

	public static function showTables(string $dbname): MySQLDriver {
		self::$sql = trim(self::$keywords['show'] . self::$keywords['tables'] . self::$keywords['from'] . " {$dbname}");
		return self::$mySQLDriver;
	}

	public static function showColumns(): MySQLDriver {
		self::$sql = trim(self::$keywords['show'] . self::$keywords['columns'] . self::$keywords['from'] . " " . self::$table);
		return self::$mySQLDriver;
	}

	public static function alias(string $value, string $as, bool $isColumn = false): string {
		if (!$isColumn) {
			return $value . self::$keywords['as'] . " {$as}";
		}

		return "{$as}.{$value}";
	}

	public static function equalTo(string $column): string {
		return "{$column}=?";
	}

	public static function greaterThan(string $column): string {
		return "{$column} > ?";
	}

	public static function lessThan(string $column): string {
		return "{$column} < ?";
	}

	public static function greaterThanOrEqualTo(string $column): string {
		return "{$column} >= ?";
	}

	public static function lessThanOrEqualTo(string $column): string {
		return "{$column} <= ?";
	}

	public static function notEqualTo(string $column): string {
		return "{$column} <> ?";
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

}
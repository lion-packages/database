<?php

namespace LionSQL;

use LionSQL\Drivers\MySQL;

class Keywords {

	protected static ?MySQL $mySQL = null;

	protected static int $cont = 1;
	protected static string $sql = "";
	protected static string $class_name = "";
	protected static string $dbname = "";
	protected static string $table = "";
	protected static string $view = "";
	protected static string $message = "";
	protected static array $data_info = [];
	protected static string $active_connection = "";
	protected static bool $active_function = false;
	protected static array $connections = [];
	protected static int $fetch_mode = 4;

	protected static array $keywords = [
		'year' => " YEAR(?)",
		'month' => " MONTH(?)",
		'day' => " DAY(?)",
		'in' => " IN(?)",
		'where' => " WHERE",
		'as' => " AS",
		'and' => " AND",
		'or' => " OR",
		'between' => " BETWEEN",
		'select' => " SELECT",
		'from' => " FROM",
		'join' => " JOIN",
		'on' => " ON",
		'left' => " LEFT",
		'right' => " RIGHT",
		'inner' => " INNER",
		'insert' => " INSERT INTO",
		'values' => " VALUES",
		'update' => " UPDATE",
		'set' => " SET",
		'delete' => " DELETE",
		'call' => " CALL",
		'like' => " LIKE",
		'groupBy' => ' GROUP BY',
		'asc' => ' ASC',
		'desc' => ' DESC',
		'orderBy' => ' ORDER BY',
		'count' => ' COUNT(?)',
		'max' => ' MAX(?)',
		'min' => ' MIN(?)',
		'sum' => ' SUM(?)',
		'avg' => ' AVG(?)',
		'limit' => ' LIMIT',
		'having' => ' HAVING',
		'show' => ' SHOW',
		'tables' => ' TABLES',
		'columns' => ' COLUMNS',
		'drop' => ' DROP',
		'table' => ' TABLE',
		'index' => ' INDEX',
		'create' => ' CREATE',
		'view' => ' VIEW',
		'concat' => ' CONCAT(*)',
		'union' => ' UNION',
		'all' => ' ALL',
		'distinct' => ' DISTINCT',
		'offset' => ' OFFSET',
		'is-not-null' => " IS NOT NULL",
		'is-null' => " IS NULL"
	];

	protected static function clean(): void {
		self::$cont = 1;
		self::$sql = "";
		self::$class_name = "";
		self::$table = "";
		self::$view = "";
		self::$data_info = [];
		self::$active_connection = self::$connections['default'];
		self::$dbname = self::$connections['connections'][self::$connections['default']]['dbname'];
		self::$fetch_mode = 4;
	}

	protected static function addCharacterBulk(array $rows): string {
		$addValues = "";
		$indexSize = count($rows) - 1;

		foreach ($rows as $key => $row) {
			$str = "(" . self::addCharacter($row) . ")";
			$addValues.= $key === $indexSize ? $str : "{$str}, ";
		}

		return $addValues;
	}

	protected static function addCharacterEqualTo(array $rows): string {
		$addValues = "";
		$index = 0;
		$indexSize = count($rows) - 1;

		foreach ($rows as $key => $row) {
			$addValues.= $index === $indexSize ? "{$key}=?" : "{$key}=?, ";
			$index++;
		}

		return $addValues;
	}

	protected static function addCharacterAssoc(array $rows): string {
		$addValues = "";
		$indexSize = count($rows) - 1;

		for ($i = 0; $i < count($rows); $i++) {
			$addValues.= $i === $indexSize ? "?" : "?, ";
		}

		return $addValues;
	}

	protected static function addCharacter(array $rows): string {
		$addValues = "";
		$indexSize = count($rows) - 1;

		foreach ($rows as $key => $file) {
			$addValues.= $key === $indexSize ? "?" : "?, ";
		}

		return $addValues;
	}

	protected static function addColumns(array $columns): string {
		$stringColumns = "";
		$countColumns = count($columns);
		$indexSize = $countColumns - 1;

		if ($countColumns > 0) {
			foreach ($columns as $key => $column) {
				$stringColumns.= $key === $indexSize ? "{$column}" : "{$column}, ";
			}
		} else {
			$stringColumns = "*";
		}

		return $stringColumns;
	}

}
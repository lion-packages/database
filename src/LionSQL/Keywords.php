<?php

namespace LionSQL;

use LionSQL\Drivers\MySQL\MySQL;
use LionSQL\Drivers\MySQL\Schema;

class Keywords {

	protected static ?MySQL $mySQL = null;
	protected static ?Schema $schema = null;
	protected static bool $is_schema = false;
	protected static bool $is_create_schema = false;

	protected static int $cont = 1;
	protected static string $sql = "";
	protected static string $class_name = "";
	protected static string $dbname = "";
	protected static string $table = "";
	protected static string $schema_str = "";
	protected static string $view = "";
	protected static string $message = "";
	protected static array $data_info = [];
	protected static string $active_connection = "";
	protected static bool $active_function = false;
	protected static array $connections = [];
	protected static int $fetch_mode = 4;
	protected static string $engine = "INNODB";
	protected static string $character_set = "UTF8";
	protected static string $collate = "UTF8_SPANISH_CI";
	protected static array $schema_options = ['columns' => [], 'indexes' => [], 'foreign' => ['index' => [], 'constraint' => []]];

	protected static array $keywords = [
		'engine' => " ENGINE",
		'collate' => " COLLATE",
		'character' => " CHARACTER",
		'schema' => " SCHEMA",
		'database' => " DATABASE",
		'full' => " FULL",
		'with' => " WITH",
		'recursive' => " RECURSIVE",
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
		'insert' => " INSERT",
		'into' => " INTO",
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
		'unique' => ' UNIQUE',
		'create' => ' CREATE',
		'view' => ' VIEW',
		'concat' => ' CONCAT(*)',
		'union' => ' UNION',
		'all' => ' ALL',
		'distinct' => ' DISTINCT',
		'offset' => ' OFFSET',
		'primary-key' => " PRIMARY KEY (?)",
		'auto-increment' => " AUTO_INCREMENT",
		'comment' => " COMMENT",
		'default' => " DEFAULT",
		'is-not-null' => " IS NOT NULL",
		'is-null' => " IS NULL",
		'null' => " NULL",
		'not-null' => " NOT NULL",
		'int' => " INT(?)",
		'bigint' => " BIGINT(?)",
		'decimal' => " DECIMAL",
		'double' => " DOUBLE",
		'float' => " FLOAT",
		'mediumint' => " MEDIUMINT(?)",
		'real' => " REAL",
		'smallint' => " SMALLINT(?)",
		'tinyint' => " TINYINT(?)",
		'char' => " CHAR(?)",
		'json' => " JSON",
		'nchar' => " NCHAR(?)",
		'nvarchar' => " NVARCHAR(?)",
		'varchar' => " VARCHAR(?)",
		'longtext' => " LONGTEXT",
		'mediumtext' => " MEDIUMTEXT",
		'text' => " TEXT(?)",
		'tinytext' => " TINYTEXT",
		'enum' => " ENUM(?)",
		'date' => " DATE",
		'time' => " TIME",
		'timestamp' => " TIMESTAMP",
		'datetime' => " DATETIME",
		'alter' => " ALTER",
		'add' => " ADD",
		'constraint' => " CONSTRAINT",
		'key' => " KEY",
		'foreign' => " FOREIGN",
		'references' => " REFERENCES",
		'restrict' => " RESTRICT",
	];

	protected static function clean(): void {
		self::$cont = 1;
		self::$sql = "";
		self::$class_name = "";
		self::$table = "";
		self::$view = "";
		self::$schema_str = "";
		self::$data_info = [];
		self::$active_connection = self::$connections['default'];
		self::$dbname = self::$connections['connections'][self::$connections['default']]['dbname'];
		self::$fetch_mode = 4;
		self::$engine == "INNODB";
		self::$character_set = "UTF8";
		self::$collate = "UTF8_SPANISH_CI";
		self::$schema_options = ['columns' => [], 'indexes' => [], 'foreign' => ['index' => [], 'constraint' => []]];
		self::$is_schema = false;
		self::$is_create_schema = false;
	}

	protected static function getColumnSettings(): string {
		$union = "";
		$foreign_index = "";
		$foreign_constraint = "";

		if (count(self::$schema_options['columns']) > 0) {
			$union .= self::addColumns(self::$schema_options['columns']);
		}

		if (count(self::$schema_options['indexes']) > 0) {
			$union .= ", " . self::addColumns(self::$schema_options['indexes']);
		}

		if (count(self::$schema_options['foreign']['index']) > 0) {
			$foreign_index .= self::addColumns(self::$schema_options['foreign']['index'], false) . ";";
		}

		if (count(self::$schema_options['foreign']['constraint']) > 0) {
			$foreign_constraint .= self::addColumns(self::$schema_options['foreign']['constraint'], false)  . ";";
		}

		$new_sql = str_replace("--FOREIGN_INDEX--", $foreign_index, self::$sql);
		$new_sql = str_replace("--FOREIGN_CONSTRAINT--", $foreign_constraint, $new_sql);

		return str_replace("--COLUMN_SETTINGS--", $union, trim($new_sql));
	}

	protected static function addColumnSettings(string $column, array $settings): void {
		$separate_table = explode(".", self::$table);

		if (!isset($settings['primary-key'])) {
			$column = !isset($settings['foreign-key']) ? "{$separate_table[1]}_{$column}" : $column;
		} else {
			$column = "{$column}{$separate_table[1]}";
		}

		$str_column_setting = $column;
		$str_column_indexes = "";

		// columns
		if (in_array($settings['type'], ["enum", "char", "nchar", "nvarchar", "varchar", "longtext", "mediumtext", "text", "tinytext"])) {
			if ($settings['type'] === "enum") {
				$str_column_setting .= str_replace(
					"?",
					self::addEnumColumns($settings['options']),
					self::$keywords[$settings['type']]
				);
			} else {
				$str_column_setting .= str_replace(
					"?",
					isset($settings['lenght']) ? $settings['lenght'] : 45,
					self::$keywords[$settings['type']]
				);
			}
		}

		if (in_array($settings['type'], ["date", "time", "timestamp", "datetime"])) {
			$str_column_setting .= self::$keywords[$settings['type']];
		}

		if (in_array($settings['type'], ["int", "bigint", "decimal", "double", "float", "mediumint", "real", "smallint", "tinyint"])) {
			$str_column_setting .= str_replace(
				"?",
				isset($settings['lenght']) ? $settings['lenght'] : 11,
				self::$keywords[$settings['type']]
			);
		}

		if (isset($settings['null'])) {
			$str_column_setting .= !$settings['null'] ? self::$keywords['not-null'] : self::$keywords['null'];
		} else {
			$str_column_setting .= self::$keywords['null'];
		}

		if (isset($settings['auto-increment'])) {
			if ($settings['auto-increment']) {
				$str_column_setting .= self::$keywords['auto-increment'];
			}
		}

		if (isset($settings['default'])) {
			if ($settings['default'] != false) {
				$str_column_setting .= self::$keywords['default'] . " '{$settings['default']}'";
			}
		} else {
			if (!isset($settings['primary-key'])) {
				$str_column_setting .= self::$keywords['default'] . self::$keywords['null'];
			}
		}

		if (isset($settings['comment'])) {
			$str_column_setting .= self::$keywords['comment'] . " '{$settings['comment']}'";
		}

		// Indexes
		if (isset($settings['primary-key'])) {
			if ($settings['primary-key']) {
				$str_column_indexes .= str_replace("?", $column, self::$keywords['primary-key']);
			}
		}

		if (isset($settings['unique'])) {
			if ($settings['unique']) {
				$str_column_indexes .= self::$keywords['unique'] . self::$keywords['index'] . " {$column}_UNIQUE ({$column}" . self::$keywords['asc'] . ")";
			}
		}

		if (isset($settings['foreign-key'])) {
			$column_fk = " {$separate_table[1]}_{$column}_FK";

			if (count(self::$schema_options['foreign']['index']) > 0) {
				self::$schema_options['foreign']['index'][] = self::$keywords['add'] . self::$keywords['index'] . "{$column_fk}_idx ({$column} " . self::$keywords['asc'] . ")";
			} else {
				self::$schema_options['foreign']['index'][] = self::$keywords['alter'] . self::$keywords['table'] . " " . self::$table . self::$keywords['add'] . self::$keywords['index'] . "{$column_fk}_idx ({$column} " . self::$keywords['asc'] . ")";
			}

			if (count(self::$schema_options['foreign']['constraint']) > 0) {
				self::$schema_options['foreign']['constraint'][] = self::$keywords['add'] . self::$keywords['constraint'] . $column_fk . self::$keywords['foreign'] . self::$keywords['key'] . " ({$column})" . self::$keywords['references'] . " {$separate_table[0]}.{$settings['foreign-key']['table']} ({$settings['foreign-key']['column']})" . self::$keywords['on'] . self::$keywords['delete'] . self::$keywords['restrict'] . self::$keywords['on'] . self::$keywords['update'] . self::$keywords['restrict'];
			} else {
				self::$schema_options['foreign']['constraint'][] .= self::$keywords['alter'] . self::$keywords['table'] . " " . self::$table . self::$keywords['add'] . self::$keywords['constraint'] . $column_fk . self::$keywords['foreign'] . self::$keywords['key'] . " ({$column})" . self::$keywords['references'] . " {$separate_table[0]}.{$settings['foreign-key']['table']} ({$settings['foreign-key']['column']})" . self::$keywords['on'] . self::$keywords['delete'] . self::$keywords['restrict'] . self::$keywords['on'] . self::$keywords['update'] . self::$keywords['restrict'];
			}
		}

		self::$schema_options['columns'][] = trim($str_column_setting);
		self::$schema_options['indexes'][] = trim($str_column_indexes);
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

	protected static function addColumns(array $columns, bool $spacing = true): string {
		$stringColumns = "";
		$new_columns = [];

		foreach ($columns as $key => $column) {
			if (!empty(trim($column))) {
				$new_columns[] = $column;
			}
		}

		$countColumns = count($new_columns);
		$indexSize = $countColumns - 1;

		if ($countColumns > 0) {
			foreach ($new_columns as $key => $column) {
				if (!empty($column)) {
					$stringColumns.= $key === $indexSize ? "{$column}" : (!$spacing ? "{$column}," : "{$column}, ");
				}
			}
		} else {
			$stringColumns = "*";
		}

		return $stringColumns;
	}

	protected static function addEnumColumns(array $columns, bool $spacing = true): string {
		$stringColumns = "";
		$new_columns = self::cleanSettings($columns);
		$countColumns = count($new_columns);
		$indexSize = $countColumns - 1;

		if ($countColumns > 0) {
			foreach ($new_columns as $key => $column) {
				if (!empty($column)) {
					$stringColumns.= $key === $indexSize ? "'{$column}'" : (!$spacing ? "'{$column}'," : "'{$column}', ");
				}
			}
		} else {
			$stringColumns = "*";
		}

		return $stringColumns;
	}

	protected static function cleanSettings(array $columns): array {
		$new_columns = [];

		foreach ($columns as $key => $column) {
			if (!empty(trim($column))) {
				$new_columns[] = $column;
			}
		}

		return $new_columns;
	}

}
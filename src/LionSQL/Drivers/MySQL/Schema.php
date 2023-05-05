<?php

namespace LionSQL\Drivers\MySQL;

use LionSQL\Functions;

class Schema extends Functions {

	public static function init(): void {
		if (self::$schema === null) {
			self::$schema = new Schema();
		}
	}

	public static function table(string $table, bool $option = false): Schema {
		if (!$option) {
			self::$table = self::$dbname . "." . $table;
		} else {
			self::$table = $table;
		}

		return self::$schema;
	}

	public static function view(string $view, bool $option = false): Schema {
		if (!$option) {
			self::$view = self::$dbname . "." . $view;
		} else {
			self::$view = $view;
		}

		return self::$schema;
	}

	public static function connection(string $connection_name): Schema {
		self::$active_connection = $connection_name;
		self::$dbname = self::$connections['connections'][$connection_name]['dbname'];
		self::mysql();

		return self::$schema;
	}

	public static function create(): Schema {
		self::$is_schema = true;
		self::$message = "Execution finished";
		self::$sql .= self::$keywords['create'] . self::$keywords['table'] . " " . self::$table . "(--COLUMN_SETTINGS--) ENGINE=" . self::$engine . " DEFAULT CHARACTER SET=" . self::$character_set . " COLLATE = " . self::$collate . ";--FOREIGN_INDEX----FOREIGN_CONSTRAINT--";
		return self::$schema;
	}

	public static function column(string $column_name, array $column_options): Schema {
		self::addColumnSettings($column_name, $column_options);
		return self::$schema;
	}

}
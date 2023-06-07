<?php

namespace LionSQL\Drivers\MySQL;

use LionSQL\Functions;

class Schema extends Functions {

	public static function init(): void {
		if (self::$schema === null) {
			self::$schema = new Schema();
		}
	}

	public static function schema(string $schema): Schema {
		self::$is_create_schema = true;
		self::$schema_str = $schema;
		return self::$schema;
	}

	public static function table(string $table, bool $option = false): Schema {
		self::$is_create_schema = false;

		if (!$option) {
			self::$table = self::$dbname . "." . $table;
		} else {
			self::$table = $table;
		}

		return self::$schema;
	}

	public static function view(string $view, bool $option = false): Schema {
		self::$is_create_schema = false;

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

	public static function create(string $character_set = "UTF8", string $collate = "UTF8_SPANISH_CI", string $engine = "INNODB"): Schema {
		self::$is_schema = true;
		self::$character_set = $character_set;
		self::$collate = $collate;

		if (!self::$is_create_schema) {
			self::$engine = $engine;
			self::$message = "Table created";
			self::$sql .= self::$keywords['create'] . self::$keywords['table'] . " " . self::$table . " (--COLUMN_SETTINGS--)" . self::$keywords['engine'] . " = " . self::$engine . self::$keywords['default'] . self::$keywords['character'] . self::$keywords['set'] . " = " . self::$character_set . self::$keywords['collate'] . " = " . self::$collate . ";--FOREIGN_INDEX----FOREIGN_CONSTRAINT--";
		} else {
			self::$message = "Database created";
			self::$sql .= self::$keywords['create'] . self::$keywords['schema'] . " " . self::$schema_str . self::$keywords['default'] . self::$keywords['character'] . self::$keywords['set'] . " " . self::$character_set . self::$keywords['collate'] . " " . self::$collate;
		}

		return self::$schema;
	}

	public static function column(string $column_name, array $column_options): Schema {
		self::addColumnSettings($column_name, $column_options);
		return self::$schema;
	}

}
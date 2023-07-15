<?php

namespace LionDatabase\Drivers\MySQL;

use \Closure;
use \ReflectionFunction;

class Schema extends \LionDatabase\Functions {

	public static function init(): void {
		self::$schema = new Schema();
	}

	public static function groupQuery(Closure $callback): Schema {
		$parameters = (new ReflectionFunction($callback))->getParameters();

		self::openGroup(self::$schema);
		$callback(
			$parameters[0]->getType()->getName() === self::$mySQL::class
				? self::$mySQL
				: self::$schema
		);
		self::closeGroup(self::$schema);

		return self::$schema;
	}

	public static function groupQueryParams(Closure $callback): Schema {
		self::openGroup(self::$schema);
		$callback(self::$schema);
		self::closeGroup(self::$schema);

		return self::$schema;
	}

	public static function groupQueryBegin(Closure $callback): Schema {
		self::$sql .= self::$words['begin'];
		$callback(self::$mySQL);
		self::$sql .= self::$words['end'] . ";";
		self::$message = "Procedure created successfully";

		return self::$schema;
	}

	public static function schema(string $schema): Schema {
		self::$is_create_schema = true;
		self::$schema_str = $schema;
		return self::$schema;
	}

	public static function procedure(string $procedure): Schema {
		self::$is_create_procedure = true;
		self::$procedure = $procedure;
		return self::$schema;
	}

	public static function table(string $table, bool $option = false): Schema {
		self::$is_create_table = true;

		if (!$option) {
			self::$table = self::$dbname . "." . $table;
		} else {
			self::$table = $table;
		}

		return self::$schema;
	}

	public static function view(string $view, bool $option = false): Schema {
		self::$is_create_view = true;

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
		return self::$schema;
	}

	public static function end(string $end = ";"): Schema {
		self::$sql .= $end;
		return self::$schema;
	}

	// ---------------------------------------------------------------------------------------------

	public static function in(): Schema {
		self::$sql .= str_replace("(?)", "", self::$words['in']);
		return self::$schema;
	}

	public static function int(string $name, int $lenght): Schema {
		$int = str_replace("?", $lenght, self::$words['int']);
		self::$sql .= " {$name}{$int}";
		return self::$schema;
	}

	public static function bigInt(string $name, int $lenght): Schema {
		$bigint = str_replace("?", $lenght, self::$words['bigint']);
		self::$sql .= " {$name}{$bigint}";
		return self::$schema;
	}

	public static function decimal(string $name): Schema {
		self::$sql .= " {$name}" . self::$words['decimal'];
		return self::$schema;
	}

	public static function double(string $name): Schema {
		self::$sql .= " {$name}" . self::$words['double'];
		return self::$schema;
	}

	public static function float(string $name): Schema {
		self::$sql .= " {$name}" . self::$words['float'];
		return self::$schema;
	}

	public static function mediumInt(string $name, int $lenght = 5): Schema {
		$mediumint = str_replace("?", $lenght, self::$words['mediumint']);
		self::$sql .= " {$name}{$mediumint}";
		return self::$schema;
	}

	public static function real(string $name): Schema {
		self::$sql .= " {$name}" . self::$words['real'];
		return self::$schema;
	}

	public static function smallInt(string $name, int $lenght): Schema {
		$smallint = str_replace("?", $lenght, self::$words['smallint']);
		self::$sql .= " {$name}{$smallint}";
		return self::$schema;
	}

	public static function tinyInt(string $name, int $lenght): Schema {
		$tinyint = str_replace("?", $lenght, self::$words['tinyint']);
		self::$sql .= " {$name}{$tinyint}";
		return self::$schema;
	}

	public static function blob(string $name): Schema {
		self::$sql .= " {$name}" . self::$words['blob'];
		return self::$schema;
	}

	public static function varBinary(string $name): Schema {
		self::$sql .= " {$name}" . self::$words['varbinary'];
		return self::$schema;
	}

	public static function char(string $name, int $lenght): Schema {
		$char = str_replace("?", $lenght, self::$words['char']);
		self::$sql .= " {$name}{$char}";
		return self::$schema;
	}

	public static function json(string $name): Schema {
		self::$sql .= " {$name}" . self::$words['json'];
		return self::$schema;
	}

	public static function nchar(string $name, int $lenght): Schema {
		$nchar = str_replace("?", $lenght, self::$words['nchar']);
		self::$sql .= " {$name}{$nchar}";
		return self::$schema;
	}

	public static function nvarchar(string $name, int $lenght): Schema {
		$nvarchar = str_replace("?", $lenght, self::$words['nvarchar']);
		self::$sql .= " {$name}{$nvarchar}";
		return self::$schema;
	}

	public static function varchar(string $name, int $lenght): Schema {
		$varchar = str_replace("?", $lenght, self::$words['varchar']);
		self::$sql .= " {$name}{$varchar}";
		return self::$schema;
	}

	public static function longText(string $name): Schema {
		self::$sql .= " {$name}" . self::$words['longtext'];
		return self::$schema;
	}

	public static function mediumText(string $name): Schema {
		self::$sql .= " {$name}" . self::$words['mediumtext'];
		return self::$schema;
	}

	public static function text(string $name, int $lenght): Schema {
		$text = str_replace("?", $lenght, self::$words['text']);
		self::$sql .= " {$name}{$text}";
		return self::$schema;
	}

	public static function tinyText(string $name): Schema {
		self::$sql .= " {$name}" . self::$words['tinytext'];
		return self::$schema;
	}

	public static function enum(string $name, array $options): Schema {
		$split = array_map(fn($op) => "'{$op}'", $options);
		self::$sql .= " {$name}" . str_replace("?", implode(",", $split), self::$words['enum']);
		return self::$schema;
	}

	public static function date(string $name): Schema {
		self::$sql .= " {$name}" . self::$words['date'];
		return self::$schema;
	}

	public static function time(string $name): Schema {
		self::$sql .= " {$name}" . self::$words['time'];
		return self::$schema;
	}

	public static function timeStamp(string $name): Schema {
		self::$sql .= " {$name}" . self::$words['timestamp'];
		return self::$schema;
	}

	public static function dateTime(string $name): Schema {
		self::$sql .= " {$name}" . self::$words['datetime'];
		return self::$schema;
	}

	public static function create(string $character_set = "UTF8", string $collate = "UTF8_SPANISH_CI", string $engine = "INNODB"): Schema {
		self::$actual_code = uniqid();
		self::$is_schema = true;
		self::$character_set = $character_set;
		self::$collate = $collate;

		if (self::$is_create_schema === true) {
			self::$message = "Database created";
			self::$sql .= self::$words['create'] . self::$words['schema'] . " " . self::$schema_str . self::$words['default'] . self::$words['character'] . self::$words['set'] . " " . self::$character_set . self::$words['collate'] . " " . self::$collate;
		}

		if (self::$is_create_table === true) {
			self::$engine = $engine;
			self::$message = "Table created";
			self::$sql .= self::$words['use'] . " `" . self::$dbname . "`;" . self::$words['drop'] . self::$words['table'] . self::$words['if'] . self::$words['exists'] . " `" . self::$table . "`;" . self::$words['create'] . self::$words['table'] . " " . self::$table . " (--COLUMN_SETTINGS--)" . self::$words['engine'] . "=" . self::$engine . self::$words['default'] . self::$words['charset'] . "=" . self::$character_set . self::$words['collate'] . "=" . self::$collate . ";--FOREIGN_INDEX----FOREIGN_CONSTRAINT--";
		}

		if (self::$is_create_procedure === true) {
			self::$sql .= self::$words['use'] . " `" . self::$dbname . "`;" . self::$words['drop'] . self::$words['procedure'] . self::$words['if'] . self::$words['exists'] . " `" . self::$procedure . "`;" . self::$words['create'] . self::$words['procedure']  . " `" . self::$procedure . "`";
		}

		if (self::$is_create_view === true) {
			self::$message = "View created";
			self::$sql .= self::$words['use'] . " `" . self::$dbname . "`;" . self::$words['create'] . self::$words['or'] . self::$words['replace'] . self::$words['view'] . " " . self::$view . self::$words['as'];
		}

		return self::$schema;
	}

	public static function column(string $column_name, array $column_options): Schema {
		self::addColumnSettings($column_name, $column_options);
		return self::$schema;
	}

}
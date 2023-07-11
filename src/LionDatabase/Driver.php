<?php

namespace LionDatabase;

class Driver extends \LionDatabase\Connection {

	public static function run(array $connections): object {
		if ($connections['default'] === "") {
			return (object) [
				'status' => 'database-error',
				'message' => 'the default driver is required'
			];
		}

		$connection = $connections['connections'][$connections['default']];
		if (strtolower($connection['type']) === "mysql") {
			\LionDatabase\Drivers\MySQL\MySQL::init($connections);
			\LionDatabase\Drivers\MySQL\Schema::init();
		} else {
			return (object) [
				'status' => 'database-error',
				'message' => 'the driver does not exist'
			];
		}

		return (object) [
			'status' => 'success',
			'message' => 'enabled connections'
		];
	}

	public static function addLog() {
		if (function_exists("logger")) {
			self::$active_function = true;
		} else {
			self::$active_function = false;
		}
	}

}
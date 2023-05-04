<?php

namespace LionSQL;

use LionRequest\Response;

class Driver extends \LionSQL\Connection {

	public static function run(array $connections): object {
		if ($connections['default'] === "") {
			return Response::response('database-error', "the default driver is required");
		}

		$connection = $connections['connections'][$connections['default']];
		if (strtolower($connection['type']) === "mysql") {
			\LionSQL\Drivers\MySQL::init($connections);
		} else {
			return Response::response('database-error', "the driver does not exist");
		}

		return Response::success('enabled connections');
	}

	public static function addLog() {
		if (function_exists("logger")) {
			self::$active_function = true;
		} else {
			self::$active_function = false;
		}
	}

}
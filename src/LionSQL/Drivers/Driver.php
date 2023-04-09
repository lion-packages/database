<?php

namespace LionSQL\Drivers;

use LionRequest\Response;
use LionSQL\Connection;
use LionSQL\Drivers\MySQL;

class Driver extends Connection {

	public static function run(array $connections): object {
		if ($connections['default'] === "") {
			return Response::response('database-error', "the default driver is required");
		}

		$connection = $connections['connections'][$connections['default']];
		if (strtolower($connection['type']) === "mysql") {
			MySQL::init($connections);
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
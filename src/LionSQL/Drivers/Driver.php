<?php

namespace LionSQL\Drivers;

use LionSQL\Connection;
use LionSQL\Drivers\MySQL;

class Driver extends Connection {

	public static function run(array $options): object {
		$res = self::getConnection($options);
		$type = strtolower($options['type']);

		if ($res->status === "database-error") {
			return $res;
		}

		if ($type === "mysql") {
			MySQL::init($options['dbname']);
		}

		return $res;
	}

}
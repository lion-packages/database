<?php

namespace LionSQL;

use \PDO;
use \PDOException;
use \PDOStatement;
use LionRequest\Response;
use LionSQL\Keywords;

class Connection extends Keywords {

	const FETCH = "fetch";
	const FETCH_ALL = "fetchAll";

	protected static PDO $conn;
	protected static PDOStatement $stmt;
	protected static bool $active_function = false;

	protected static function getConnection(array $config): object {
		$type = strtolower($config['type']);

		if ($type === 'mysql') {
			return self::mysql($config);
		}

		return Response::response("database-error", "The driver '{$type}' does not exist");
	}

	private static function mysql(array $config): object {
		try {
			self::$conn = new PDO(
				"mysql:host=" . $config['host'] . ";port=" . $config['port'] . ";dbname=" . $config['dbname'],
				$config['user'],
				$config['password'],
				isset($config['options']) ? $config['options'] : [
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
				]
			);

			return Response::success('MySQL connection established');
		} catch (PDOException $e) {
			if (self::$active_function) {
				logger($e->getMessage(), "error");
			}

			return Response::response("database-error", $e->getMessage(), (object) [
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]);
		}
	}

}
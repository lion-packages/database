<?php

namespace LionSQL;

use \PDO;
use \PDOException;
use \PDOStatement;
use LionRequest\Response;
use LionSQL\Keywords;
use LionSQL\Drivers\MySQLDriver;

class Connection extends Keywords {

	const FETCH = "fetch";
	const FETCH_ALL = "fetchAll";

	protected static PDO $conn;
	protected static Response $response;
	protected static MySQLDriver $mySQLDriver;
	protected static PDOStatement $stmt;

	public function __construct() {

	}

	protected static function getConnection(array $config): object {
		self::$response = Response::getInstance();
		$type = strtolower($config['type']);

		if ($type === 'mysql') {
			return self::mysql($config);
		}

		return self::$response->error("The driver '{$type}' does not exist");
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

			return self::$response->success('mysql connection established');
		} catch (PDOException $e) {
			return self::$response->error($e->getMessage());
		}
	}

}
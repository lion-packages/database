<?php

namespace LionSql;

use \PDO;
use \PDOStatement;
use \PDOException;
use LionRequest\Response;

class Connection {
	
	private static PDO $conn;
	protected static Response $response;
	
	public function __construct() {

	}

	protected static function getConexion(array $config): void {
		self::$response = Response::getInstance();

		switch ($config['type']) {
			case 'mysql':
			self::mysql($config);
			break;

			default:
			self::mysql($config);
			break;
		}
	}

	private static function mysql(array $config): void {
		try {
			self::$conn = new PDO(
				"mysql:host={$config['host']};dbname={$config['db_name']};charset={$config['charset']}",
				$config['user'],
				$config['password'],
				isset($config['options']) ? $config['options'] : [
					PDO::ATTR_EMULATE_PREPARES => false,
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
					PDO::ATTR_TIMEOUT => 5
				]
			);
		} catch (PDOException $e) {
			echo($e->getMessage());
		}
	}

	protected static function bindValue(PDOStatement $stmt, array $data): PDOStatement {
		$count = 1;
		foreach ($data as $key => $dt) {
			if (isset($dt[1])) {
				$type = strtolower($dt[1]);
				$stmt->bindValue($count, $type === "int" ? (int) $dt[0] : $dt[0], $type === "int" ? PDO::PARAM_INT : PDO::PARAM_STR);
			} else {
				$stmt->bindValue($count, $dt[0], PDO::PARAM_STR);
			}

			$count++;
		}

		return $stmt;
	}

	protected static function prepare(string $query): PDOStatement {
		return self::$conn->prepare(trim($query));
	}

	protected static function fetch(PDOStatement $stmt): object {
		if (!$stmt->execute()) {
			return self::request->error("An unexpected error has occurred");
		}

		$request = $stmt->fetch();
		return !$request ? self::$response->success("No data available") : (object) $request;
	}

	protected static function fetchAll(PDOStatement $stmt): object {
		if (!$stmt->execute()) {
			return self::$request->error("An unexpected error has occurred");
		}

		$request = $stmt->fetchAll();
		return !$request ? self::$response->success("No data available") : (object) $request;
	}

}
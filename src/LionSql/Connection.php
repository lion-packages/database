<?php

namespace LionSQL;

use \PDO;
use \PDOStatement;
use \PDOException;
use LionRequest\Response;

class Connection {

	private static PDO $conn;
	protected static Response $response;
	protected static ?string $class_name = null;

	public function __construct() {

	}

	protected static function getConnection(array $config, string $type): object {
		self::$response = Response::getInstance();
		$type = strtolower($type);

		if ($type === 'mysql') {
			return self::mysql($config);
		}

		return self::$response->error("The driver '{$type}' does not exist");
	}

	private static function mysql(array $config): object {
		try {
			$host = '';

			if (isset($config['port'])) {
				$host = "mysql:host=" . $config['host'] . ";port=" . $config['port'] . ";dbname=" . $config['db_name'];
			} else {
				$host = "mysql:host=" . $config['host'] . ";dbname=" . $config['db_name'];
			}

			self::$conn = new PDO(
				$host,
				$config['user'],
				$config['password'],
				isset($config['options']) ? $config['options'] : [
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
				]
			);

			return Response::success('mysql connection established');
		} catch (PDOException $e) {
			return Response::error($e->getMessage());
		}
	}

	protected static function bindValue(PDOStatement $stmt, array $list): PDOStatement {
		$type = function($value) {
			switch (gettype($value)) {
				case 'integer':
				return PDO::PARAM_INT;
				break;

				case 'boolean':
				return PDO::PARAM_BOOL;
				break;

				case 'NULL':
				return PDO::PARAM_NULL;
				break;

				default:
				return PDO::PARAM_STR;
				break;
			}
		};

		$count = 1;
		foreach ($list as $key => $value) {
			$stmt->bindValue($count, $value, $type($value));
			$count++;
		}

		return $stmt;
	}

	protected static function prepare(string $query): PDOStatement {
		return self::$conn->prepare(trim($query));
	}

	protected static function fetch(PDOStatement $stmt): array|object {
		if (!$stmt->execute()) {
			return self::$response->error("An unexpected error has occurred");
		}

		if (self::$class_name === null) {
			$request = $stmt->fetch();
		} else {
			$stmt->setFetchMode(PDO::FETCH_CLASS, self::$class_name);
			$request = $stmt->fetch();
			self::$class_name = null;
		}

		return !$request ? self::$response->success("No data available") : $request;
	}

	protected static function fetchAll(PDOStatement $stmt): array|object {
		if (!$stmt->execute()) {
			return self::$response->error("An unexpected error has occurred");
		}

		if (self::$class_name === null) {
			$request = $stmt->fetchAll();
		} else {
			$stmt->setFetchMode(PDO::FETCH_CLASS, self::$class_name);
			$request = $stmt->fetchAll();
			self::$class_name = null;
		}

		return !$request ? self::$response->success("No data available") : $request;
	}

}
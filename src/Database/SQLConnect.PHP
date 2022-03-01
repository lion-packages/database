<?php

namespace LionSql\Database;

use \PDO;
use \PDOStatement;
use \PDOException;

class SQLConnect {
	private static PDO $conn;
	
	public function __construct() {
	}

	public static function connectDatabase(array $config) {
		try {
			self::$conn = new PDO(
				"mysql:host={$config['host']};dbname={$config['db_name']};charset={$config['charset']}", 
				$config['user'], 
				$config['password'], 
				isset($config['options']) ? $config['options'] : [
					PDO::ATTR_EMULATE_PREPARES => false,
					PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
					PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
					PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
					PDO::ATTR_TIMEOUT => 5
				]
			);
		} catch (PDOException $e) {
			echo($e);
		}
	}

	protected static function bindValue(PDOStatement $stmt, array $data): PDOStatement {
		foreach ($data as $key => $dt) {
			$stmt->bindValue(($key + 1), strtolower($dt[1]) === "int" ? (int) $dt[0] : $dt[0], strtolower($dt[1]) === "int" ? PDO::PARAM_INT : PDO::PARAM_STR);
		}

		return $stmt;
	}

	protected static function prepare(string $query): PDOStatement {
		return self::$conn->prepare($query);
	}

	protected static function fetch($stmt): array {
		return !$stmt->execute() ? ['status' => "error", 'message' => "an unexpected error has occurred."] : $stmt->fetch();
	}

	protected static function fetchAll($stmt): array {
		return !$stmt->execute() ? ['status' => "error", 'message' => "an unexpected error has occurred."] : $stmt->fetchAll();
	}

}
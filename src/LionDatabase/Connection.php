<?php

namespace LionDatabase;

use \Closure;
use \PDO;
use \PDOException;
use \PDOStatement;

class Connection extends \LionDatabase\Keywords {

	const FETCH = "fetch";
	const FETCH_ALL = "fetchAll";

	protected static PDO $conn;
	protected static PDOStatement|bool $stmt;

	protected static function mysql(Closure $callback): array|object {
		$connection = self::$connections['connections'][self::$active_connection];
		$dbname = $connection['dbname'];
		$host = $connection['host'];
		$port = $connection['port'];
		$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ];

		try {
			self::$conn = new PDO(
				"mysql:host={$host};port={$port};dbname={$dbname}",
				$connection['user'],
				$connection['password'],
				(isset($connection['options']) ? $connection['options'] : $options)
			);

			if (self::$is_transaction) {
				self::$conn->beginTransaction();
			}

			return $callback();
		} catch (PDOException $e) {
			if (self::$active_function) logger($e->getMessage(), "error");

			return (object) [
				'status' => 'database-error',
				'message' => $e->getMessage(),
				'data' => (object) [
					'file' => $e->getFile(),
					'line' => $e->getLine()
				]
			];
		}
	}

}
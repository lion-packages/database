<?php

namespace LionSQL;

use \Exception;
use \PDO;
use \PDOException;
use \PDOStatement;
use LionSQL\Keywords;

class Connection extends Keywords {

	const FETCH = "fetch";
	const FETCH_ALL = "fetchAll";

	protected static PDO $conn;
	protected static bool|PDOStatement $stmt;

	protected static function mysql(): object {
		$connection = self::$connections['connections'][self::$active_connection];
		$dbname = $connection['dbname'];
		$host = $connection['host'];
		$port = $connection['port'];
		$options = isset($connection['options'])
			? $connection['options']
			: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ];

		try {
			self::$conn = new PDO(
				"mysql:host={$host};port={$port};dbname={$dbname}",
				$connection['user'],
				$connection['password'],
				$options
			);

			return (object) [
				'status' => 'success',
				'message' => 'MySQL connection established'
			];
		} catch (PDOException $e) {
			if (self::$active_function) {
				logger($e->getMessage(), "error");
			}

			return (object) [
				'status' => 'database-error',
				'message' => $e->getMessage(),
				'data' => (object) [
					'file' => $e->getFile(),
					'line' => $e->getLine()
				]
			];
		} catch (Exception $e) {
			if (self::$active_function) {
				logger($e->getMessage(), "error");
			}

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
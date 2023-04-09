<?php

namespace LionSQL;

use Exception;
use \PDO;
use \PDOException;
use \PDOStatement;
use LionRequest\Response;
use LionSQL\Keywords;

class Connection extends Keywords {

	const FETCH = "fetch";
	const FETCH_ALL = "fetchAll";

	protected static PDO $conn;
	protected static bool|PDOStatement $stmt;

	protected static function mysql(): object {
		$dbname = self::$connections['connections'][self::$active_connection]['dbname'];
		$host = self::$connections['connections'][self::$active_connection]['host'];
		$port = self::$connections['connections'][self::$active_connection]['port'];
		$options = isset(self::$connections['connections'][self::$active_connection]['options'])
			? self::$connections['connections'][self::$active_connection]['options']
			: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ];

		try {
			self::$conn = new PDO(
				"mysql:host={$host};port={$port};dbname={$dbname}",
				self::$connections['connections'][self::$active_connection]['user'],
				self::$connections['connections'][self::$active_connection]['password'],
				$options
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
		} catch (Exception $e) {
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
<?php

namespace LionSQL;

use LionRequest\Response;
use LionSQL\Connection;
use \PDO;
use \PDOException;

class Functions extends Connection {

	private static function clean(): void {
		self::$cont = 1;
		self::$sql = "";
		self::$class_name = "";
		self::$table = "";
		self::$view = "";
		self::$data_info = [];
	}

	private static function prepare(): void {
		self::$stmt = self::$conn->prepare(trim(self::$sql));
	}

	private static function bindValue(array $list): void {
		$type = function($value) {
			if (gettype($value) === 'integer') {
				return PDO::PARAM_INT;
			} elseif (gettype($value) === 'boolean') {
				return PDO::PARAM_BOOL;
			} elseif (gettype($value) === 'NULL') {
				return PDO::PARAM_NULL;
			} else {
				return PDO::PARAM_STR;
			}
		};

		foreach ($list as $key => $value) {
			self::$stmt->bindValue(self::$cont, $value, $type($value));
			self::$cont++;
		}
	}

	private static function addRows($rows): void {
		foreach ($rows as $key => $row) {
			self::$data_info[] = $row;
		}
	}

	public static function getQueryString(): string {
		return trim(self::$sql);
	}

	public static function execute(): array|object {
		try {
			self::prepare();
			self::bindValue(self::$data_info);
			self::$stmt->execute();
			self::clean();
			return Response::success(self::$message);
		} catch (PDOException $e) {
			return Response::response("database-error", $e->getMessage(), (object) [
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]);
		}
	}

	public static function get(): array|object {
		$request = null;

		try {
			self::prepare();

			if (count(self::$data_info) > 0) {
				self::bindValue(self::$data_info);
			}

			if (!self::$stmt->execute()) {
				return Response::error("An unexpected error has occurred");
			}

			if (empty(self::$class_name)) {
				$request = self::$stmt->fetch();
			} else {
				self::$stmt->setFetchMode(PDO::FETCH_CLASS, self::$class_name);
				$request = self::$stmt->fetch();
				self::$class_name = "";
			}

			self::clean();
			return !$request ? Response::success("No data available") : $request;
		} catch (PDOException $e) {
			return Response::response("database-error", $e->getMessage(), (object) [
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]);
		}
	}

	public static function getAll(): array|object {
		$request = null;

		try {
			self::prepare();

			if (count(self::$data_info) > 0) {
				self::bindValue(self::$data_info);
			}

			if (!self::$stmt->execute()) {
				return Response::error("An unexpected error has occurred");
			}

			if (empty(self::$class_name)) {
				$request = self::$stmt->fetchAll();
			} else {
				self::$stmt->setFetchMode(PDO::FETCH_CLASS, self::$class_name);
				$request = self::$stmt->fetchAll();
				self::$class_name = "";
			}

			self::clean();
			return !$request ? Response::success("No data available") : $request;
		} catch (PDOException $e) {
			return Response::response("database-error", $e->getMessage(), (object) [
				'file' => $e->getFile(),
				'line' => $e->getLine()
			]);
		}
	}

}
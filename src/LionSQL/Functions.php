<?php

namespace LionSQL;

use \Exception;
use LionRequest\Response;
use LionSQL\Connection;
use LionSQL\Drivers\MySQL\MySQL;
use \PDO;
use \PDOException;

class Functions extends Connection {

	public static function table(string $table, bool $option = false): MySQL {
		if (!$option) {
			self::$table = self::$dbname . "." . $table;
		} else {
			self::$table = $table;
		}

		return self::$mySQL;
	}

	public static function view(string $view, bool $option = false): MySQL {
		if (!$option) {
			self::$view = self::$dbname . "." . $view;
		} else {
			self::$view = $view;
		}

		return self::$mySQL;
	}

	public static function fetchMode(int $fetch_mode, string $class = ""): MySQL {
		self::$fetch_mode = $fetch_mode;
		self::$class_name = $class;
		return self::$mySQL;
	}

	public static function getConnections(): array {
		return self::$connections;
	}

	public static function connection(string $connection_name): MySQL {
		self::$active_connection = $connection_name;
		self::$dbname = self::$connections['connections'][$connection_name]['dbname'];
		self::mysql();

		return self::$mySQL;
	}

	public static function fetchClass(mixed $class): MySQL {
		self::$class_name = $class;
		return self::$mySQL;
	}

	protected static function prepare(): void {
		self::$stmt = self::$conn->prepare(trim(self::$sql));
	}

	protected static function bindValue(array $list): void {
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

	protected static function addRows($rows): void {
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

			if (self::$fetch_mode != 4) {
				self::$stmt->setFetchMode(self::$fetch_mode);
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

			if (self::$fetch_mode != 4) {
				self::$stmt->setFetchMode(self::$fetch_mode);
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
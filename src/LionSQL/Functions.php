<?php

namespace LionSQL;

use \Exception;
use LionSQL\Connection;
use LionSQL\Drivers\MySQL\MySQL;
use \PDO;
use \PDOException;

class Functions extends Connection {

	protected static function openGroup(mixed $object): mixed {
		self::$sql .= " (";
		return $object;
	}

	protected static function closeGroup(mixed $object): mixed {
		self::$sql .= " )";
		return $object;
	}

	public static function fetchMode(int $fetch_mode, string $class = ""): MySQL {
		self::$fetch_mode = $fetch_mode;
		self::$class_name = $class;
		return self::$mySQL;
	}

	public static function getConnections(): array {
		return self::$connections;
	}

	public static function fetchClass(mixed $class): MySQL {
		self::$class_name = $class;
		return self::$mySQL;
	}

	protected static function prepare(): void {
		if (!self::$is_schema) {
			self::$stmt = self::$conn->prepare(trim(self::$sql));
		} else {
			self::$stmt = self::$conn->prepare(trim(self::getColumnSettings()));
		}
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

	public static function getQueryString(): object {
		if (!self::$is_schema) {
			return (object) [
				'status' => 'success',
				'message' => 'SQL query generated successfully',
				'data' => (object) [
					'sql' => trim(self::$sql)
				]
			];
		}

		return (object) [
			'status' => 'success',
			'message' => 'SQL query generated successfully',
			'data' => (object) [
				'sql' => self::getColumnSettings(),
				'options' => (object) [
					'columns' => self::$schema_options['columns'],
					'indexes' => self::cleanSettings(self::$schema_options['indexes']),
					'foreigns' => (object) [
						'index' => self::cleanSettings(self::$schema_options['foreign']['index']),
						'constraint' => self::cleanSettings(self::$schema_options['foreign']['constraint'])
					]
				]
			]
		];
	}

	public static function execute(): array|object {
		try {
			self::prepare();
			self::bindValue(self::$data_info);
			self::$stmt->execute();
			self::clean();

			return (object) [
				'status' => 'success',
				'message' => self::$message
			];
		} catch (PDOException $e) {
			self::clean();

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
			self::clean();

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

	public static function get(): array|object {
		$request = null;

		try {
			self::prepare();

			if (count(self::$data_info) > 0) {
				self::bindValue(self::$data_info);
			}

			if (!self::$stmt->execute()) {
				return (object) [
					'status' => 'database-error',
					'message' => 'An unexpected error has occurred'
				];
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

			if (!$request) {
				return (object) [
					'status' => 'success',
					'message' => 'No data available'
				];
			} else {
				return $request;
			}
		} catch (PDOException $e) {
			self::clean();

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
			self::clean();

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

	public static function getAll(): array|object {
		$request = null;

		try {
			self::prepare();

			if (count(self::$data_info) > 0) {
				self::bindValue(self::$data_info);
			}

			if (!self::$stmt->execute()) {
				return (object) [
					'status' => 'database-error',
					'message' => 'An unexpected error has occurred'
				];
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

			if (!$request) {
				return (object) [
					'status' => 'success',
					'message' => 'No data available'
				];
			} else {
				return $request;
			}
		} catch (PDOException $e) {
			self::clean();

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
			self::clean();

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
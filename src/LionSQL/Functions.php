<?php

namespace LionSQL;

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

	protected static function prepare(string $sql): void {
		if (!self::$is_schema) {
			self::$stmt = self::$conn->prepare(trim($sql));
		} else {
			self::$stmt = self::$conn->prepare(trim(self::getColumnSettings(trim($sql))));
		}
	}

	protected static function bindValue(string $code): void {
		if (!self::$is_schema) {
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

			$cont = 1;
			foreach (self::$data_info[$code] as $keyValue => $value) {
				self::$stmt->bindValue($cont, $value, $type($value));
				$cont++;
			}
		} else {
			$index = 0;

			self::$sql = preg_replace_callback('/\?/', function($matches) use (&$index) {
				$value = self::$data_info[$index];
				$index++;
				return $value;
			}, self::$sql);
		}
	}

	protected static function addRows(array $rows): void {
		foreach ($rows as $keyRow => $row) {
			self::$data_info[self::$actual_code][] = $row;
		}
	}

	public static function getQueryString(): object {
		if (!self::$is_schema) {
			$new_sql = self::$sql;
			self::$sql = "";

			return (object) [
				'status' => 'success',
				'message' => 'SQL query generated successfully',
				'data' => (object) [
					'sql' => trim($new_sql)
				]
			];
		}

		// self::bindValue();
		// $new_sql = self::getColumnSettings();
		// self::$sql = "";

		// return (object) [
		// 	'status' => 'success',
		// 	'message' => 'SQL query generated successfully',
		// 	'data' => (object) [
		// 		'sql' => $new_sql,
		// 		'options' => (object) [
		// 			'columns' => self::$schema_options['columns'],
		// 			'indexes' => self::cleanSettings(self::$schema_options['indexes']),
		// 			'foreigns' => (object) [
		// 				'index' => self::cleanSettings(self::$schema_options['foreign']['index']),
		// 				'constraint' => self::cleanSettings(self::$schema_options['foreign']['constraint'])
		// 			]
		// 		]
		// 	]
		// ];
	}

	public static function execute(): object {
		return self::mysql(function() {
			$response = (object) [];
			$split = explode(";", trim(self::$sql));
			self::$list_sql = array_map(fn($value) => trim($value), array_filter($split, fn($value) => trim($value) != ""));

			try {
				foreach (array_keys(self::$data_info) as $key => $code) {
					$sql = self::$list_sql[$key];

					if (self::$is_schema) {
						self::bindValue($code);
						self::prepare($sql);
					} else {
						self::prepare($sql);
						self::bindValue($code);
					}

					if (self::$is_transaction) {
						self::$message = "Transaction executed successfully";
					}

					self::$stmt->execute();
					self::$stmt->closeCursor();
					$response = (object) ['status' => 'success', 'message' => self::$message];
				}

				if (self::$is_transaction) {
					self::$conn->commit();
				}

				self::clean();
			} catch (PDOException $e) {
				if (self::$is_transaction) self::$conn->rollBack();
				if (self::$active_function) logger($e->getMessage(), "error");
				self::clean();

				return (object) [
					'status' => 'database-error',
					'message' => $e->getMessage(),
					'data' => (object) [
						'file' => $e->getFile(),
						'line' => $e->getLine()
					]
				];
			}

			return $response;
		});
	}

	public static function get(): void {
		// return self::mysql(function() {
		// 	$request = null;

		// 	try {
		// 		self::prepare();

		// 		if (count(self::$data_info) > 0) {
		// 			self::bindValue();
		// 		}

		// 		if (!self::$stmt->execute()) {
		// 			return (object) ['status' => 'database-error', 'message' => 'An unexpected error has occurred'];
		// 		}

		// 		if (self::$fetch_mode != 4) {
		// 			self::$stmt->setFetchMode(self::$fetch_mode);
		// 		}

		// 		if (empty(self::$class_name)) {
		// 			$request = self::$stmt->fetch();
		// 		} else {
		// 			self::$stmt->setFetchMode(PDO::FETCH_CLASS, self::$class_name);
		// 			$request = self::$stmt->fetch();
		// 			self::$class_name = "";
		// 		}

		// 		self::clean();

		// 		if (!$request) {
		// 			return (object) ['status' => 'success', 'message' => 'No data available'];
		// 		} else {
		// 			return $request;
		// 		}
		// 	} catch (PDOException $e) {
		// 		self::clean();

		// 		if (self::$active_function) {
		// 			logger($e->getMessage(), "error");
		// 		}

		// 		return (object) ['status' => 'database-error', 'message' => $e->getMessage(), 'data' => (object) ['file' => $e->getFile(), 'line' => $e->getLine()]];
		// 	}
		// });
	}

	public static function getAll(): void {
		// return self::mysql(function() {
		// 	$request = null;

		// 	try {
		// 		self::prepare();

		// 		if (count(self::$data_info) > 0) {
		// 			self::bindValue();
		// 		}

		// 		if (!self::$stmt->execute()) {
		// 			return (object) ['status' => 'database-error', 'message' => 'An unexpected error has occurred'];
		// 		}

		// 		if (self::$fetch_mode != 4) {
		// 			self::$stmt->setFetchMode(self::$fetch_mode);
		// 		}

		// 		if (empty(self::$class_name)) {
		// 			$request = self::$stmt->fetchAll();
		// 		} else {
		// 			self::$stmt->setFetchMode(PDO::FETCH_CLASS, self::$class_name);
		// 			$request = self::$stmt->fetchAll();
		// 			self::$class_name = "";
		// 		}

		// 		self::clean();

		// 		if (!$request) {
		// 			return (object) ['status' => 'success', 'message' => 'No data available'];
		// 		} else {
		// 			return $request;
		// 		}
		// 	} catch (PDOException $e) {
		// 		self::clean();

		// 		if (self::$active_function) {
		// 			logger($e->getMessage(), "error");
		// 		}

		// 		return (object) ['status' => 'database-error', 'message' => $e->getMessage(), 'data' => (object) ['file' => $e->getFile(), 'line' => $e->getLine()]];
		// 	}
		// });
	}

}
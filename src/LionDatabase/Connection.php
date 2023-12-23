<?php

declare(strict_types=1);

namespace LionDatabase;

use Closure;
use LionDatabase\Helpers\FunctionsTrait;
use PDO;
use PDOException;
use PDOStatement;

abstract class Connection
{
    use FunctionsTrait;

	protected static PDO $conn;
	protected static PDOStatement|bool $stmt;

	protected static function mysql(Closure $callback): array|object
	{
		$connection = self::$connections['connections'][self::$activeConnection];
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

			if (self::$isTransaction) {
				self::$conn->beginTransaction();
			}

			return $callback();
		} catch (PDOException $e) {
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

	protected static function prepare(string $sql): void
	{
        self::$stmt = self::$conn->prepare(trim($sql));
	}

	private static function getValueType(mixed $type): int
	{
        $pdoType = [
            'integer' => PDO::PARAM_INT,
            'boolean' => PDO::PARAM_BOOL,
            'NULL' => PDO::PARAM_NULL,
            'HEX' => PDO::PARAM_LOB
        ];

        return $pdoType[$type] ?? PDO::PARAM_STR;
	}

	protected static function bindValue(string $code): void
	{
        if (!empty(self::$dataInfo[$code])) {
            $cont = 1;
            $value_type = null;

            foreach (self::$dataInfo[$code] as $value) {
                if ($value === null) {
                    $value_type = 'NULL';
                } else {
                    $value_type = !preg_match('/^0x/', $value) ? gettype($value) : 'HEX';
                }

                if ($value_type === 'HEX') {
                    self::$stmt->bindValue(
                        $cont,
                        hex2bin(str_replace('0x', '', $value)),
                        self::getValueType($value_type)
                    );
                } else {
                    self::$stmt->bindValue(
                        $cont,
                        $value,
                        self::getValueType($value_type)
                    );
                }

                $cont++;
            }
        }
	}

    public static function getQueryString(): object
    {
        $query = trim(self::$sql);
        $split = explode(';', trim(self::$sql));
        $newListSql = array_map(fn($value) => trim($value), array_filter($split, fn($value) => trim($value) != ''));
        self::$sql = '';
        self::$listSql = [];

        return (object) [
            'status' => 'success',
            'message' => 'SQL query generated successfully',
            'data' => (object) [
                'query' => $query,
                'split' => $newListSql
            ]
        ];
    }

    protected static function executeMySQL(): object
    {
        return self::mysql(function() {
            $response = (object) ['status' => 'success', 'message' => self::$message];

            if (self::$isTransaction) {
                self::$message = 'Transaction executed successfully';
            }

            try {
                self::$listSql = array_map(
                    fn($value) => trim($value),
                    array_filter(explode(';', trim(self::$sql)), fn($value) => trim($value) != '')
                );

                $data_info_keys = array_keys(self::$dataInfo);

                if (count($data_info_keys) > 0) {
                    foreach ($data_info_keys as $key => $code) {
                        self::prepare(self::$listSql[$key]);
                        self::bindValue($code);
                        self::$stmt->execute();
                        self::$stmt->closeCursor();
                    }
                } else {
                    self::prepare(self::$sql);
                    self::bindValue(self::$actualCode);
                    self::$stmt->execute();
                }

                if (self::$isTransaction) {
                    self::$conn->commit();
                }

                self::clean();
                return $response;
            } catch (PDOException $e) {
                if (self::$isTransaction) {
                    self::$conn->rollBack();
                }

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
        });
    }

    protected static function getMySQL(): array|object
    {
        return self::mysql(function() {
            $responses = [];

            self::$listSql = array_map(
                fn($value) => trim($value),
                array_filter(explode(';', trim(self::$sql)), fn($value) => trim($value) != '')
            );

            try {
                $codes = array_keys(self::$fetchMode);

                foreach (self::$listSql as $key => $sql) {
                    self::prepare($sql);
                    $code = isset($codes[$key]) ? $codes[$key] : null;

                    if ($code != null && isset(self::$dataInfo[$code])) {
                        self::bindValue($code);
                    }

                    if ($code != null && isset(self::$fetchMode[$code])) {
                        $get_fetch = self::$fetchMode[$codes[$key]];

                        if (is_array($get_fetch)) {
                            self::$stmt->setFetchMode($get_fetch[0], $get_fetch[1]);
                        } else {
                            self::$stmt->setFetchMode(self::$fetchMode[$codes[$key]]);
                        }
                    }

                    self::$stmt->execute();
                    $request = self::$stmt->fetch();

                    if (!$request) {
                        if (count(self::$fetchMode) > 1) {
                            $responses[] = (object) ['status' => 'success', 'message' => 'No data available'];
                        } else {
                            $responses = (object) ['status' => 'success', 'message' => 'No data available'];
                        }
                    } else {
                        if (count(self::$fetchMode) > 1) {
                            $responses[] = $request;
                        } else {
                            $responses = $request;
                        }
                    }
                }

                self::clean();
            } catch (PDOException $e) {
                self::clean();

                $responses[] = (object) [
                    'status' => 'database-error',
                    'message' => $e->getMessage(),
                    'data' => (object) [
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]
                ];
            }

            return $responses;
        });
    }

    protected static function getAllMySQL(): array|object
    {
        return self::mysql(function() {
            $responses = [];

            self::$listSql = array_map(
                fn($value) => trim($value),
                array_filter(explode(';', trim(self::$sql)), fn($value) => trim($value) != '')
            );

            try {
                $codes = array_keys(self::$fetchMode);

                foreach (self::$listSql as $key => $sql) {
                    self::prepare($sql);
                    $code = isset($codes[$key]) ? $codes[$key] : null;

                    if ($code != null && isset(self::$dataInfo[$code])) {
                        self::bindValue($code);
                    }

                    if ($code != null && isset(self::$fetchMode[$code])) {
                        $get_fetch = self::$fetchMode[$codes[$key]];

                        if (is_array($get_fetch)) {
                            self::$stmt->setFetchMode($get_fetch[0], $get_fetch[1]);
                        } else {
                            self::$stmt->setFetchMode(self::$fetchMode[$codes[$key]]);
                        }
                    }

                    self::$stmt->execute();
                    $request = self::$stmt->fetchAll();

                    if (!$request) {
                        if (count(self::$fetchMode) > 1) {
                            $responses[] = (object) ['status' => 'success', 'message' => 'No data available'];
                        } else {
                            $responses = (object) ['status' => 'success', 'message' => 'No data available'];
                        }
                    } else {
                        if (count(self::$fetchMode) > 1) {
                            $responses[] = $request;
                        } else {
                            $responses = $request;
                        }
                    }
                }

                self::clean();
            } catch (PDOException $e) {
                self::clean();

                $responses[] = (object) [
                    'status' => 'database-error',
                    'message' => $e->getMessage(),
                    'data' => (object) [
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]
                ];
            }

            return $responses;
        });
    }
}

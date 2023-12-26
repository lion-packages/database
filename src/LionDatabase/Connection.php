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
            $valueType = null;

            foreach (self::$dataInfo[$code] as $value) {
                if (null === $value) {
                    $valueType = 'NULL';
                } else {
                    if (is_string($value)) {
                        $valueType = !preg_match('/^0x/', $value) ? gettype($value) : 'HEX';
                    } else {
                        $valueType = gettype($value);
                    }
                }

                if ($valueType === 'HEX') {
                    self::$stmt->bindValue(
                        $cont,
                        hex2bin(str_replace('0x', '', $value)),
                        self::getValueType($valueType)
                    );
                } else {
                    self::$stmt->bindValue(
                        $cont,
                        $value,
                        self::getValueType($valueType)
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
}

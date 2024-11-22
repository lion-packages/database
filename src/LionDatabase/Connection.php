<?php

declare(strict_types=1);

namespace Lion\Database;

use Closure;
use InvalidArgumentException;
use Lion\Database\Helpers\FunctionsTrait;
use Lion\Database\Interface\ConnectionConfigInterface;
use Lion\Database\Interface\DatabaseCapsuleInterface;
use Lion\Database\Interface\DatabaseEngineInterface;
use PDO;
use PDOException;
use PDOStatement;
use stdClass;

/**
 * Class that manages the connection to databases on different drivers
 *
 * @property PDO $conn [PDO driver object to make connections to databases]
 * @property PDOStatement|bool $stmt [PDO declaration object to perform database
 * processes]
 * @property array<string, PDO> $databaseInstances [List of database
 * connections]
 *
 * @package Lion\Database
 */
abstract class Connection implements ConnectionConfigInterface, DatabaseEngineInterface
{
    use FunctionsTrait;

    /**
     * [Default settings for database connections]
     *
     * @const DEFAULT_DATABASE_OPTIONS
     */
    private const array DEFAULT_DATABASE_OPTIONS = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
    ];

    /**
     * [PDO driver object to make connections to databases]
     *
     * @var PDO $conn
     */
    protected static PDO $conn;

    /**
     * [PDO declaration object to perform database processes]
     *
     * @var PDOStatement|bool $stmt
     */
    protected static PDOStatement|bool $stmt;

    /**
     * [List of database connections]
     *
     * @var array<string, PDO> $databaseInstances
     */
    protected static array $databaseInstances;

    /**
     * {@inheritdoc}
     */
    public static function addConnection(string $connectionName, array $options): void
    {
        self::$connections['connections'][$connectionName] = $options;
    }

    /**
     * {@inheritdoc}
     */
    public static function getConnections(): array
    {
        return self::$connections['connections'];
    }

    /**
     * {@inheritdoc}
     */
    public static function removeConnection(string $connectionName): void
    {
        unset(self::$connections['connections'][$connectionName]);
    }

    /**
     * {@inheritdoc}
     */
    public static function mysql(Closure $callback): stdClass|array|DatabaseCapsuleInterface
    {
        try {
            self::$conn = self::getDatabaseInstance();

            if (self::$isTransaction) {
                self::$conn->beginTransaction();
            }

            $response = $callback();

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
                'code' => $e->getCode(),
                'status' => 'database-error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function postgresql(Closure $callback): stdClass|array|DatabaseCapsuleInterface
    {
        try {
            self::$conn = self::getDatabaseInstance();

            if (self::$isTransaction) {
                self::$conn->beginTransaction();
            }

            $response = $callback();

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
                'code' => $e->getCode(),
                'status' => 'database-error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Prepare the current sentence
     *
     * @param string $sql [Current sentence]
     *
     * @return void
     */
    protected static function prepare(string $sql): void
    {
        self::$stmt = self::$conn->prepare(trim($sql));
    }

    /**
     * Gets the object type for a parameter of a declaration
     *
     * @param mixed $type [Datatype]
     *
     * @return int
     */
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

    /**
     * Nests the values in the preparation of the current statement
     *
     * @param string $code [Current unique code]
     *
     * @return void
     */
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
                        $valueType = !((bool) preg_match('/^0x/', $value)) ? gettype($value) : 'HEX';
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

    /**
     * Gets an object with the current statement
     *
     * @return stdClass
     */
    public static function getQueryString(): stdClass
    {
        $query = trim(self::$sql);

        $split = explode(';', trim(self::$sql));

        $newListSql = array_map(fn ($value) => trim($value), array_filter($split, fn ($value) => trim($value) != ''));

        self::$sql = '';

        self::$listSql = [];

        return (object) [
            'code' => 200,
            'status' => 'success',
            'message' => 'SQL query generated successfully',
            'data' => (object) [
                'query' => $query,
                'split' => $newListSql,
            ],
        ];
    }

    /**
     * Stores database connections to avoid generating multiple connections
     *
     * @return PDO
     *
     * @throws InvalidArgumentException [If the database connection is not
     * supported]
     */
    private static function getDatabaseInstance(): PDO
    {
        if (empty(self::$databaseInstances[self::$activeConnection])) {
            $connection = self::$connections['connections'][self::$activeConnection];

            if (Driver::POSTGRESQL === $connection['type']) {
                self::$databaseInstances[self::$activeConnection] = new PDO(
                    "pgsql:host={$connection['host']};port={$connection['port']};dbname={$connection['dbname']}",
                    $connection['user'],
                    $connection['password'],
                    ($connection['options'] ?? self::DEFAULT_DATABASE_OPTIONS)
                );
            } else if (Driver::MYSQL === $connection['type']) {
                self::$databaseInstances[self::$activeConnection] = new PDO(
                    "mysql:host={$connection['host']};port={$connection['port']};dbname={$connection['dbname']}",
                    $connection['user'],
                    $connection['password'],
                    ($connection['options'] ?? self::DEFAULT_DATABASE_OPTIONS)
                );
            } else {
                throw new InvalidArgumentException('the database connection type is not supported', 500);
            }
        }

        return self::$databaseInstances[self::$activeConnection];
    }
}

<?php

declare(strict_types=1);

namespace Lion\Database;

use Closure;
use InvalidArgumentException;
use Lion\Database\Helpers\StringFactory;
use Lion\Database\Interface\ConnectionConfigInterface;
use Lion\Database\Interface\DatabaseCapsuleInterface;
use PDO;
use PDOException;
use PDOStatement;
use stdClass;

/**
 * Class that manages the connection to databases on different drivers
 *
 * @package Lion\Database
 */
abstract class Connection extends StringFactory implements ConnectionConfigInterface
{
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
     * @var PDOStatement $stmt
     */
    protected static PDOStatement $stmt;

    /**
     * [List of database connections]
     *
     * @var array<non-empty-string, PDO> $databaseInstances
     */
    protected static array $databaseInstances = [];

    /**
     * {@inheritDoc}
     */
    public static function addConnection(string $connectionName, array $options): void
    {
        self::$connections['connections'][$connectionName] = $options;
    }

    /**
     * {@inheritDoc}
     */
    public static function removeConnection(string $connectionName): void
    {
        unset(self::$connections['connections'][$connectionName]);
    }

    /**
     * {@inheritDoc}
     */
    public static function getConnections(): array
    {
        return self::$connections['connections'];
    }

    /**
     * Initializes a MySQL database connection and runs a process
     *
     * @param Closure $callback [Function that is executed]
     *
     * phpcs:ignore Generic.Files.LineLength
     * @return array<int, array<int|string, mixed>|DatabaseCapsuleInterface|stdClass>|DatabaseCapsuleInterface|int|stdClass
     *
     * @throws PDOException [If the database process fails]
     *
     * @internal
     */
    public static function process(Closure $callback): array|DatabaseCapsuleInterface|int|stdClass
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
     *
     * @throws PDOException [If something goes wrong when preparing the
     * consultation]
     */
    protected static function prepare(string $sql): void
    {
        /** @var PDOStatement $stmt */
        $stmt = self::$conn->prepare(trim($sql));

        self::$stmt = $stmt;
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
     * Stores database connections to avoid generating multiple connections
     *
     * @return PDO
     *
     * @throws InvalidArgumentException [If the database connection is not
     * supported]
     */
    private static function getDatabaseInstance(): PDO
    {
        $connection = self::$connections['connections'][self::$activeConnection];

        $key = empty($connection['dbname']) ? 'server-' . self::$activeConnection : self::$activeConnection;

        if (empty(self::$databaseInstances[$key])) {
            /** @var string $type */
            $type = $connection['type'];

            if (Driver::POSTGRESQL === $type) {
                self::$databaseInstances[$key] = self::getDatabaseInstancePostgreSQL($connection);
            } elseif (Driver::MYSQL === $type) {
                self::$databaseInstances[$key] = self::getDatabaseInstanceMySQL($connection);
            } elseif (Driver::SQLITE === $type) {
                self::$databaseInstances[$key] = self::getDatabaseInstanceSQLite($connection);
            } else {
                throw new InvalidArgumentException('The database connection type is not supported', 500);
            }
        }

        return self::$databaseInstances[$key];
    }

    /**
     * Gets a PDO instance for MySQL database connections
     *
     * @param array{
     *     type: string,
     *     host: string,
     *     port: int,
     *     dbname?: string,
     *     user: string,
     *     password: string,
     *     options?: array<int, int>
     * } $connection [Database connection data]
     *
     * @return PDO
     */
    private static function getDatabaseInstanceMySQL(array $connection): PDO
    {
        $dbName = !empty($connection['dbname']) ? ";dbname={$connection['dbname']}" : '';

        return new PDO(
            "mysql:host={$connection['host']};port={$connection['port']}{$dbName}",
            $connection['user'],
            $connection['password'],
            ($connection['options'] ?? self::DEFAULT_DATABASE_OPTIONS)
        );
    }

    /**
     * Gets a PDO instance for PostgreSQL database connections
     *
     * @param array{
     *     type: string,
     *     host: string,
     *     port: int,
     *     dbname?: string,
     *     user: string,
     *     password: string,
     *     options?: array<int, int>
     * } $connection [Database connection data]
     *
     * @return PDO
     */
    private static function getDatabaseInstancePostgreSQL(array $connection): PDO
    {
        $dbName = !empty($connection['dbname']) ? $connection['dbname'] : 'postgres';

        return new PDO(
            "pgsql:host={$connection['host']};port={$connection['port']};dbname={$dbName}",
            $connection['user'],
            $connection['password'],
            ($connection['options'] ?? self::DEFAULT_DATABASE_OPTIONS)
        );
    }

    /**
     * Gets a PDO instance for SQLite database connections
     *
     * @param array{
     *     type: string,
     *     dbname?: string,
     *     options?: array<int, int>
     * } $connection [Database connection data]
     *
     * @return PDO
     */
    private static function getDatabaseInstanceSQLite(array $connection): PDO
    {
        $dbName = !empty($connection['dbname']) ? $connection['dbname'] : ':memory:';

        return new PDO(
            "sqlite:{$dbName}",
            null,
            null,
            ($connection['options'] ?? self::DEFAULT_DATABASE_OPTIONS)
        );
    }

    /**
     * Clearing the PDO Object Initialization Cache
     *
     * @return void
     */
    public static function clearConnectionList(): void
    {
        self::$databaseInstances = [];
    }
}

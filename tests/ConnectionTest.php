<?php

declare(strict_types=1);

namespace Tests;

use InvalidArgumentException;
use Lion\Database\Connection;
use Lion\Database\Driver;
use Lion\Test\Test;
use PDO;
use PDOException;
use PDOStatement;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test as Testing;
use PHPUnit\Framework\Attributes\TestWith;
use ReflectionException;
use stdClass;
use Tests\Provider\ConnectionProviderTrait;

class ConnectionTest extends Test
{
    use ConnectionProviderTrait;

    private const array RESPONSE = [
        'status' => 'success',
        'message' => 'TEST-OK',
    ];

    private Connection $connection;

    /**
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $this->connection = new class extends Connection
        {
        };

        $this->initReflection($this->connection);

        /** @phpstan-ignore-next-line */
        $this->setPrivateProperty('connections', CONNECTIONS_CONNECTION);

        /** @phpstan-ignore-next-line */
        $this->setPrivateProperty('activeConnection', DATABASE_NAME_CONNECTION);

        $this->setPrivateProperty('isTransaction', false);
    }

    /**
     * @throws ReflectionException
     */
    protected function tearDown(): void
    {
        $this->setPrivateProperty('connections', []);

        $this->setPrivateProperty('activeConnection', '');

        $this->setPrivateProperty('isTransaction', false);

        $this->setPrivateProperty('actualCode', '');

        $this->setPrivateProperty('dataInfo', []);

        $this->setPrivateProperty('sql', '');

        $this->setPrivateProperty('listSql', []);

        $this->setPrivateProperty('databaseInstances', []);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function mysql(): void
    {
        $response = $this->getPrivateMethod('mysql', [fn () => (object) self::RESPONSE]);

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertSame(self::RESPONSE['status'], $response->status);
        $this->assertSame(self::RESPONSE['message'], $response->message);
        $this->assertInstanceOf(PDO::class, $this->getPrivateProperty('conn'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function mysqlIsTransactionTrue(): void
    {
        $this->setPrivateProperty('isTransaction', true);

        $response = $this->getPrivateMethod('mysql', [fn () => (object) self::RESPONSE]);

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertSame(self::RESPONSE['status'], $response->status);
        $this->assertSame('TEST-OK', $response->message);
        $this->assertInstanceOf(PDO::class, $this->getPrivateProperty('conn'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function mysqlWithException(): void
    {
        $response = $this->getPrivateMethod('mysql', [function (): void {
            throw new PDOException('Connection failed');
        }]);

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertSame('database-error', $response->status);
        $this->assertSame('Connection failed', $response->message);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function sqlite(): void
    {
        $response = $this->getPrivateMethod('sqlite', [fn () => (object) self::RESPONSE]);

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertSame(self::RESPONSE['status'], $response->status);
        $this->assertSame(self::RESPONSE['message'], $response->message);
        $this->assertInstanceOf(PDO::class, $this->getPrivateProperty('conn'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function sqliteIsTransactionTrue(): void
    {
        $this->setPrivateProperty('isTransaction', true);

        $response = $this->getPrivateMethod('mysql', [fn () => (object) self::RESPONSE]);

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertSame(self::RESPONSE['status'], $response->status);
        $this->assertSame('TEST-OK', $response->message);
        $this->assertInstanceOf(PDO::class, $this->getPrivateProperty('conn'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function sqliteWithException(): void
    {
        $response = $this->getPrivateMethod('sqlite', [function (): void {
            throw new PDOException('Connection failed');
        }]);

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertSame('database-error', $response->status);
        $this->assertSame('Connection failed', $response->message);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function prepare(): void
    {
        $this->getPrivateMethod('mysql', [
            'callback' => fn (): stdClass => (object) self::RESPONSE,
        ]);

        $this->getPrivateMethod('prepare', [
            'sql' => 'SELECT * FROM users',
        ]);

        $this->assertInstanceOf(PDOStatement::class, $this->getPrivateProperty('stmt'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('getValueTypeProvider')]
    public function getValueType(string $value, int $fetchMode): void
    {
        $type = $this->getPrivateMethod('getValueType', [$value]);

        $this->assertIsInt($type);
        $this->assertSame($fetchMode, $type);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('bindValueProvider')]
    public function bindValue(string $code, string $query, array $values): void
    {
        $this->getPrivateMethod('mysql', [fn () => (object) self::RESPONSE]);

        $this->getPrivateMethod('prepare', [$query]);

        $this->setPrivateProperty('actualCode', $code);

        $this->setPrivateProperty('dataInfo', [$code => $values]);

        $this->getPrivateMethod('bindValue', [$code]);

        $this->assertInstanceOf(PDOStatement::class, $this->getPrivateProperty('stmt'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('getQueryStringProvider')]
    public function getQueryString(string $query): void
    {
        $this->setPrivateProperty('sql', $query);

        $response = $this->connection->getQueryString();

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertObjectHasProperty('data', $response);
        $this->assertObjectHasProperty('query', $response->data);
        $this->assertObjectHasProperty('split', $response->data);
        $this->assertSame(self::RESPONSE['status'], $response->status);
        $this->assertSame('SQL query generated successfully', $response->message);
        $this->assertSame($query, $response->data->query);
        $this->assertSame(explode(';', $query), $response->data->split);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function addConnection(): void
    {
        /** @phpstan-ignore-next-line */
        $this->setPrivateProperty('connections', [
            'default' => DATABASE_NAME_SECOND_CONNECTION,
        ]);

        $this->connection->addConnection(DATABASE_NAME_SECOND_CONNECTION, CONNECTION_DATA_SECOND_CONNECTION);

        $connections = $this->getPrivateProperty('connections');

        $this->assertArrayHasKey('default', $connections);
        $this->assertSame(DATABASE_NAME_SECOND_CONNECTION, $connections['default']);
        $this->assertArrayHasKey(DATABASE_NAME_SECOND_CONNECTION, $connections['connections']);

        $this->assertSame(
            $connections['connections'][DATABASE_NAME_SECOND_CONNECTION],
            CONNECTION_DATA_SECOND_CONNECTION
        );
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function getConnections(): void
    {
        $this->setPrivateProperty('connections', CONNECTIONS_CONNECTION);

        $this->assertSame(CONNECTIONS_CONNECTION['connections'], $this->connection::getConnections());
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function removeConnection(): void
    {
        $this->setPrivateProperty('connections', [
            'default' => DATABASE_NAME_CONNECTION,
            'connections' => [
                DATABASE_NAME_CONNECTION => CONNECTION_DATA_CONNECTION,
            ],
        ]);

        $this->connection->removeConnection(DATABASE_NAME_CONNECTION);

        $this->assertSame([], $this->connection::getConnections());
    }

    /**
     * @param array{
     *     type: string,
     *     host: string,
     *     port: int,
     *     dbname: string,
     *     user: string,
     *     password: string
     * } $connectionData
     *
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('getDatabaseInstanceProvider')]
    public function getDatabaseInstance(string $driver, string $connectionName, array $connectionData): void
    {
        $this->setPrivateProperty('connections', [
            'default' => $connectionName,
            'connections' => [
                $connectionName => $connectionData,
            ],
        ]);

        $this->setPrivateProperty('activeConnection', $connectionName);

        $conn = $this->getPrivateMethod('getDatabaseInstance');

        $this->assertIsObject($conn);
        $this->assertInstanceOf(PDO::class, $conn);
        $this->assertSame($driver, $conn->getAttribute(PDO::ATTR_DRIVER_NAME));

        $databaseInstances = $this->getPrivateProperty('databaseInstances');

        $this->assertIsArray($databaseInstances);
        $this->assertNotEmpty($databaseInstances);
        $this->assertArrayHasKey($connectionName, $databaseInstances);
        $this->assertIsObject($databaseInstances[$connectionName]);
        $this->assertInstanceOf(PDO::class, $databaseInstances[$connectionName]);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function getDatabaseInstanceForServerConnection(): void
    {
        $this->setPrivateProperty('connections', [
            'default' => 'test-connection',
            'connections' => [
                'test-connection' => [
                    'type' => Driver::MYSQL,
                    'host' => DATABASE_HOST_MYSQL,
                    'port' => DATABASE_PORT_MYSQL,
                    'user' => DATABASE_USER_MYSQL,
                    'password' => DATABASE_PASSWORD_MYSQL
                ],
            ],
        ]);

        $this->setPrivateProperty('activeConnection', 'test-connection');

        $conn = $this->getPrivateMethod('getDatabaseInstance');

        $this->assertIsObject($conn);
        $this->assertInstanceOf(PDO::class, $conn);
        $this->assertSame('mysql', $conn->getAttribute(PDO::ATTR_DRIVER_NAME));

        $databaseInstances = $this->getPrivateProperty('databaseInstances');

        $this->assertIsArray($databaseInstances);
        $this->assertNotEmpty($databaseInstances);
        $this->assertArrayHasKey('server-test-connection', $databaseInstances);
        $this->assertIsObject($databaseInstances['server-test-connection']);
        $this->assertInstanceOf(PDO::class, $databaseInstances['server-test-connection']);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function getDatabaseInstanceIsError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('The database connection type is not supported');

        $this->setPrivateProperty('connections', [
            'default' => DATABASE_NAME_CONNECTION,
            'connections' => [
                DATABASE_NAME_CONNECTION => [
                    'type' => 'mixed',
                    'host' => DATABASE_HOST_MYSQL,
                    'port' => DATABASE_PORT_MYSQL,
                    'dbname' => DATABASE_NAME_CONNECTION,
                    'user' => DATABASE_USER_MYSQL,
                    'password' => DATABASE_PASSWORD_MYSQL,
                ],
            ],
        ]);

        $this->setPrivateProperty('activeConnection', DATABASE_NAME_CONNECTION);

        $this->getPrivateMethod('getDatabaseInstance');
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function getDatabaseInstanceMySQL(): void
    {
        $conn = $this->getPrivateMethod('getDatabaseInstanceMySQL', [
            /** @phpstan-ignore-next-line */
            'connection' => CONNECTION_DATA_CONNECTION,
        ]);

        $this->assertIsObject($conn);
        $this->assertInstanceOf(PDO::class, $conn);
        $this->assertSame('mysql', $conn->getAttribute(PDO::ATTR_DRIVER_NAME));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function getDatabaseInstanceMySQLNoDatabase(): void
    {
        $conn = $this->getPrivateMethod('getDatabaseInstanceMySQL', [
            'connection' => [
                'type' => Driver::MYSQL,
                'host' => DATABASE_HOST_MYSQL,
                'port' => DATABASE_PORT_MYSQL,
                'user' => DATABASE_USER_MYSQL,
                'password' => DATABASE_PASSWORD_MYSQL
            ],
        ]);

        $this->assertIsObject($conn);
        $this->assertInstanceOf(PDO::class, $conn);
        $this->assertSame('mysql', $conn->getAttribute(PDO::ATTR_DRIVER_NAME));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function getDatabaseInstancePostgreSQL(): void
    {
        $conn = $this->getPrivateMethod('getDatabaseInstancePostgreSQL', [
            'connection' => CONNECTION_DATA_THIRD_CONNECTION,
        ]);

        $this->assertIsObject($conn);
        $this->assertInstanceOf(PDO::class, $conn);
        $this->assertSame('pgsql', $conn->getAttribute(PDO::ATTR_DRIVER_NAME));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function getDatabaseInstancePostgreSQLNoDatabase(): void
    {
        $conn = $this->getPrivateMethod('getDatabaseInstancePostgreSQL', [
            'connection' => [
                'type' => Driver::POSTGRESQL,
                'host' => DATABASE_HOST_POSTGRESQL,
                'port' => DATABASE_PORT_POSTGRESQL,
                'user' => DATABASE_USER_POSTGRESQL,
                'password' => DATABASE_PASSWORD_POSTGRESQL,
            ],
        ]);

        $this->assertIsObject($conn);
        $this->assertInstanceOf(PDO::class, $conn);
        $this->assertSame('pgsql', $conn->getAttribute(PDO::ATTR_DRIVER_NAME));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function getDatabaseInstanceSQLite(): void
    {
        $copyDir = __DIR__ . '/Provider/copy/';

        $copyFile = $copyDir . 'lion_database.sqlite';

        if (!is_dir($copyDir)) {
            mkdir($copyDir, 0777, true);
        }

        copy(__DIR__ . '/Provider/lion_database.sqlite', $copyFile);

        chmod($copyFile, 0666);

        $this->assertFileExists($copyFile);

        $conn = $this->getPrivateMethod('getDatabaseInstanceSQLite', [
            'connection' => CONNECTION_DATA_QUARTER_CONNECTION,
        ]);

        $this->assertIsObject($conn);
        $this->assertInstanceOf(PDO::class, $conn);
        $this->assertSame('sqlite', $conn->getAttribute(PDO::ATTR_DRIVER_NAME));

        $this->rmdirRecursively(__DIR__ . '/Provider/copy/');

        $this->assertDirectoryDoesNotExist(__DIR__ . '/Provider/copy/');
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function getDatabaseInstanceSQLiteNoDatabase(): void
    {
        $conn = $this->getPrivateMethod('getDatabaseInstanceSQLite', [
            'connection' => [
                'type' => Driver::SQLITE,
            ],
        ]);

        $this->assertIsObject($conn);
        $this->assertInstanceOf(PDO::class, $conn);
        $this->assertSame('sqlite', $conn->getAttribute(PDO::ATTR_DRIVER_NAME));
    }
}

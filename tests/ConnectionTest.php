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
use stdClass;
use Tests\Provider\ConnectionProviderTrait;

class ConnectionTest extends Test
{
    use ConnectionProviderTrait;

    private const string DATABASE_HOST_MYSQL = 'mysql';
    private const string DATABASE_HOST_POSTGRESQL = 'postgres';
    private const int DATABASE_PORT_MYSQL = 3306;
    private const int DATABASE_PORT_POSTGRESQL = 5432;
    private const string DATABASE_NAME = 'lion_database';
    private const string DATABASE_NAME_SECOND = 'lion_database_second';
    private const string DATABASE_NAME_THIRD = 'lion_database_third';
    private const string DATABASE_USER = 'root';
    private const string DATABASE_PASSWORD = 'lion';
    private const array CONNECTION_DATA = [
        'type' => Driver::MYSQL,
        'host' => self::DATABASE_HOST_MYSQL,
        'port' => self::DATABASE_PORT_MYSQL,
        'dbname' => self::DATABASE_NAME,
        'user' => self::DATABASE_USER,
        'password' => self::DATABASE_PASSWORD
    ];
    private const array CONNECTION_DATA_SECOND = [
        'type' => Driver::MYSQL,
        'host' => self::DATABASE_HOST_MYSQL,
        'port' => self::DATABASE_PORT_MYSQL,
        'dbname' => self::DATABASE_NAME_SECOND,
        'user' => self::DATABASE_USER,
        'password' => self::DATABASE_PASSWORD
    ];
    private const array CONNECTION_DATA_THIRD = [
        'type' => Driver::POSTGRESQL,
        'host' => self::DATABASE_HOST_POSTGRESQL,
        'port' => self::DATABASE_PORT_POSTGRESQL,
        'dbname' => self::DATABASE_NAME,
        'user' => self::DATABASE_USER,
        'password' => self::DATABASE_PASSWORD
    ];
    private const array CONNECTIONS = [
        'default' => self::DATABASE_NAME,
        'connections' => [
            self::DATABASE_NAME => self::CONNECTION_DATA,
            self::DATABASE_NAME_SECOND => self::CONNECTION_DATA_SECOND,
        ],
    ];
    private const array RESPONSE = [
        'status' => 'success',
        'message' => 'TEST-OK'
    ];

    private Connection $customClass;

    protected function setUp(): void
    {
        $this->customClass = new class extends Connection
        {
        };

        $this->initReflection($this->customClass);

        $this->setPrivateProperty('connections', self::CONNECTIONS);

        $this->setPrivateProperty('activeConnection', self::DATABASE_NAME);

        $this->setPrivateProperty('isTransaction', false);
    }

    protected function tearDown(): void
    {
        $this->setPrivateProperty('connections', []);

        $this->setPrivateProperty('activeConnection', '');

        $this->setPrivateProperty('isTransaction', false);

        $this->setPrivateProperty('actualCode', '');

        $this->setPrivateProperty('dataInfo', []);

        $this->setPrivateProperty('stmt', false);

        $this->setPrivateProperty('sql', '');

        $this->setPrivateProperty('listSql', []);

        $this->setPrivateProperty('databaseInstances', []);
    }

    public function testMysql(): void
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

    public function testMysqlIsTransactionTrue(): void
    {
        $this->setPrivateProperty('isTransaction', true);

        $response = $this->getPrivateMethod('mysql', [fn () => (object) self::RESPONSE]);

        $this->assertIsObject($response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertSame(self::RESPONSE['status'], $response->status);
        $this->assertSame('TEST-OK', $response->message);
        $this->assertInstanceOf(PDO::class, $this->getPrivateProperty('conn'));
    }

    public function testMysqlWithException(): void
    {
        $response = $this->getPrivateMethod('mysql', [function (): void {
            throw new PDOException('Connection failed');
        }]);

        $this->assertIsObject($response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertSame('database-error', $response->status);
        $this->assertSame('Connection failed', $response->message);
    }

    public function testPrepare(): void
    {
        $this->getPrivateMethod('mysql', [fn (): stdClass => (object) self::RESPONSE]);

        $this->getPrivateMethod('prepare', ['SELECT * FROM users']);

        $this->assertInstanceOf(PDOStatement::class, $this->getPrivateProperty('stmt'));
    }

    #[DataProvider('getValueTypeProvider')]
    public function testGetValueType(string $value, int $fetchMode): void
    {
        $type = $this->getPrivateMethod('getValueType', [$value]);

        $this->assertIsInt($type);
        $this->assertSame($fetchMode, $type);
    }

    #[DataProvider('bindValueProvider')]
    public function testBindValue(string $code, string $query, array $values): void
    {
        $this->getPrivateMethod('mysql', [fn () => (object) self::RESPONSE]);

        $this->getPrivateMethod('prepare', [$query]);

        $this->setPrivateProperty('actualCode', $code);

        $this->setPrivateProperty('dataInfo', [$code => $values]);

        $this->getPrivateMethod('bindValue', [$code]);

        $this->assertInstanceOf(PDOStatement::class, $this->getPrivateProperty('stmt'));
    }

    #[DataProvider('getQueryStringProvider')]
    public function testGetQueryString(string $query): void
    {
        $this->setPrivateProperty('sql', $query);

        $response = $this->customClass->getQueryString();

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

    public function testAddConnection(): void
    {
        $this->setPrivateProperty('connections', ['default' => self::DATABASE_NAME_SECOND]);

        $this->customClass->addConnection(self::DATABASE_NAME_SECOND, self::CONNECTION_DATA_SECOND);

        $connections = $this->getPrivateProperty('connections');

        $this->assertArrayHasKey('default', $connections);
        $this->assertSame(self::DATABASE_NAME_SECOND, $connections['default']);
        $this->assertArrayHasKey(self::DATABASE_NAME_SECOND, $connections['connections']);
        $this->assertSame($connections['connections'][self::DATABASE_NAME_SECOND], self::CONNECTION_DATA_SECOND);
    }

    public function testGetConnections(): void
    {
        $this->setPrivateProperty('connections', [
            'default' => self::DATABASE_NAME,
            'connections' => [
                self::DATABASE_NAME => self::CONNECTION_DATA,
                self::DATABASE_NAME_SECOND => self::CONNECTION_DATA_SECOND,
            ],
        ]);

        $this->assertSame(self::CONNECTIONS['connections'], $this->customClass::getConnections());
    }

    public function testRemoveConnection(): void
    {
        $this->setPrivateProperty('connections', [
            'default' => self::DATABASE_NAME,
            'connections' => [
                self::DATABASE_NAME => self::CONNECTION_DATA,
            ],
        ]);

        $this->customClass->removeConnection(self::DATABASE_NAME);

        $this->assertSame([], $this->customClass::getConnections());
    }

    #[Testing]
    #[TestWith(['driver' => 'mysql', 'databaseName' => self::DATABASE_NAME])]
    #[TestWith(['driver' => 'pgsql', 'databaseName' => self::DATABASE_NAME_THIRD])]
    public function getDatabaseInstance(string $driver, string $databaseName): void
    {
        $this->setPrivateProperty('connections', [
            'default' => self::DATABASE_NAME,
            'connections' => [
                self::DATABASE_NAME => self::CONNECTION_DATA,
                self::DATABASE_NAME_THIRD => self::CONNECTION_DATA_THIRD,
            ],
        ]);

        $this->setPrivateProperty('activeConnection', $databaseName);

        $conn = $this->getPrivateMethod('getDatabaseInstance');

        $this->assertIsObject($conn);
        $this->assertInstanceOf(PDO::class, $conn);
        $this->assertSame($driver, $conn->getAttribute(PDO::ATTR_DRIVER_NAME));
    }

    #[Testing]
    public function getDatabaseInstanceIsError(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('the database connection type is not supported');

        $this->setPrivateProperty('connections', [
            'default' => self::DATABASE_NAME,
            'connections' => [
                self::DATABASE_NAME => [
                    'type' => 'mixed',
                    'host' => self::DATABASE_HOST_MYSQL,
                    'port' => self::DATABASE_PORT_MYSQL,
                    'dbname' => self::DATABASE_NAME,
                    'user' => self::DATABASE_USER,
                    'password' => self::DATABASE_PASSWORD
                ],
            ],
        ]);

        $this->setPrivateProperty('activeConnection', self::DATABASE_NAME);

        $this->getPrivateMethod('getDatabaseInstance');
    }
}

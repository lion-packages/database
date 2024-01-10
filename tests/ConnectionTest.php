<?php

declare(strict_types=1);

namespace Tests;

use Lion\Database\Connection;
use Lion\Test\Test;
use PDO;
use PDOException;
use PDOStatement;
use Tests\Provider\ConnectionProviderTrait;

class ConnectionTest extends Test
{
    use ConnectionProviderTrait;

    const DATABASE_TYPE = 'mysql';
    const DATABASE_HOST = 'db';
    const DATABASE_PORT = 3306;
    const DATABASE_NAME = 'lion_database';
    const DATABASE_NAME_SECOND = 'lion_database_second';
    const DATABASE_USER = 'root';
    const DATABASE_PASSWORD = 'lion';
    const CONNECTION_DATA = [
        'type' => self::DATABASE_TYPE,
        'host' => self::DATABASE_HOST,
        'port' => self::DATABASE_PORT,
        'dbname' => self::DATABASE_NAME,
        'user' => self::DATABASE_USER,
        'password' => self::DATABASE_PASSWORD
    ];
    const CONNECTION_DATA_SECOND = [
        'type' => self::DATABASE_TYPE,
        'host' => self::DATABASE_HOST,
        'port' => self::DATABASE_PORT,
        'dbname' => self::DATABASE_NAME_SECOND,
        'user' => self::DATABASE_USER,
        'password' => self::DATABASE_PASSWORD
    ];
    const CONNECTIONS = [
        'default' => self::DATABASE_NAME,
        'connections' => [
            self::DATABASE_NAME => self::CONNECTION_DATA,
            self::DATABASE_NAME_SECOND => self::CONNECTION_DATA_SECOND
        ]
    ];
    const RESPONSE = ['status' => 'success', 'message' => 'TEST-OK'];

    private object $customClass;

    protected function setUp(): void
    {
        $this->customClass = new class extends Connection {};

        $this->initReflection($this->customClass);
        $this->setPrivateProperty('connections', self::CONNECTIONS);
        $this->setPrivateProperty('activeConnection', self::DATABASE_NAME);
        $this->setPrivateProperty('isTransaction', false);
    }

    protected function tearDown(): void
    {
        $this->setPrivateProperty('connections', self::CONNECTIONS);
        $this->setPrivateProperty('activeConnection', self::DATABASE_NAME);
        $this->setPrivateProperty('isTransaction', false);
        $this->setPrivateProperty('actualCode', '');
        $this->setPrivateProperty('dataInfo', []);
        $this->setPrivateProperty('stmt', false);
        $this->setPrivateProperty('sql', '');
        $this->setPrivateProperty('listSql', []);
    }

    public function testMysql(): void
    {
        $response = $this->getPrivateMethod('mysql', [fn() => (object) self::RESPONSE]);

        $this->assertIsObject($response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertSame('success', $response->status);
        $this->assertSame('TEST-OK', $response->message);
        $this->assertInstanceOf(PDO::class, $this->getPrivateProperty('conn'));
    }

    public function testMysqlIsTransactionTrue(): void
    {
        $this->setPrivateProperty('isTransaction', true);
        $response = $this->getPrivateMethod('mysql', [fn() => (object) self::RESPONSE]);

        $this->assertIsObject($response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertSame('success', $response->status);
        $this->assertSame('TEST-OK', $response->message);
        $this->assertInstanceOf(PDO::class, $this->getPrivateProperty('conn'));
    }

    public function testMysqlWithException(): void
    {
        $response = $this->getPrivateMethod('mysql', [function() {
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
        $this->getPrivateMethod('mysql', [fn() => (object) self::RESPONSE]);
        $this->getPrivateMethod('prepare', ['SELECT * FROM users']);

        $this->assertInstanceOf(PDOStatement::class, $this->getPrivateProperty('stmt'));
    }

    /**
     * @dataProvider getValueTypeProvider
     * */
    public function testGetValueType(string $value, int $fetchMode): void
    {
        $type = $this->getPrivateMethod('getValueType', [$value]);

        $this->assertIsInt($type);
        $this->assertSame($fetchMode, $type);
    }

    /**
     * @dataProvider bindValueProvider
     * */
    public function testBindValue(string $code, string $query, array $values): void
    {
        $this->getPrivateMethod('mysql', [fn() => (object) self::RESPONSE]);
        $this->getPrivateMethod('prepare', [$query]);
        $this->setPrivateProperty('actualCode', $code);
        $this->setPrivateProperty('dataInfo', [$code => $values]);
        $this->getPrivateMethod('bindValue', [$code]);

        $this->assertInstanceOf(PDOStatement::class, $this->getPrivateProperty('stmt'));
    }

    /**
     * @dataProvider getQueryStringProvider
     * */
    public function testGetQueryString(string $query): void
    {
        $this->setPrivateProperty('sql', $query);
        $queryString = $this->customClass->getQueryString();

        $this->assertIsObject($queryString);
        $this->assertObjectHasProperty('status', $queryString);
        $this->assertObjectHasProperty('message', $queryString);
        $this->assertObjectHasProperty('data', $queryString);
        $this->assertObjectHasProperty('query', $queryString->data);
        $this->assertObjectHasProperty('split', $queryString->data);
        $this->assertSame('success', $queryString->status);
        $this->assertSame('SQL query generated successfully', $queryString->message);
        $this->assertSame($query, $queryString->data->query);
        $this->assertSame(explode(';', $query), $queryString->data->split);
    }
}

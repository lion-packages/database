<?php

declare(strict_types=1);

namespace Tests\Interface;

use LionDatabase\Interface\DatabaseInterface;
use LionTest\Test;

class DatabaseInterfaceTest extends Test
{
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

    private object $customClass;

    protected function setUp(): void
    {
        $this->customClass = new class implements DatabaseInterface {
            protected static array $connections = [];
            protected static string $activeConnection = '';
            protected static string $dbname = '';
            protected static bool $isTransaction = false;
            protected static bool $isSchema = false;
            protected static bool $enableInsert = false;

            public static function execute(): object
            {
                return (object) [
                    'status' => 'success',
                    'message' => 'Execution finished'
                ];
            }

            public static function get(): array|object
            {
                return [];
            }

            public static function getAll(): array|object
            {
                return [];
            }

            public static function run(array $connections): object
            {
                self::$connections = $connections;

                return new static;
            }

            public static function connection(string $connectionName): object
            {
                self::$activeConnection = $connectionName;
                self::$dbname = self::$connections['connections'][$connectionName]['dbname'];

                return new static;
            }

            public static function transaction(bool $isTransaction = true): object
            {
                self::$isTransaction = $isTransaction;

                return new static;
            }

            public static function isSchema(): object
            {
                self::$isSchema = true;

                return new static;
            }

            public static function enableInsert(bool $enable = false): object
            {
                self::$enableInsert = $enable;

                return new static;
            }
        };

        $this->initReflection($this->customClass);
    }

    protected function tearDown(): void
    {
        $this->setPrivateProperty('connections', []);
        $this->setPrivateProperty('activeConnection', '');
        $this->setPrivateProperty('dbname', '');
        $this->setPrivateProperty('isTransaction', false);
        $this->setPrivateProperty('isSchema', false);
        $this->setPrivateProperty('enableInsert', false);
    }

    public function testExecute(): void
    {
        $response = $this->customClass->execute();

        $this->assertIsObject($response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertSame('success', $response->status);
        $this->assertSame('Execution finished', $response->message);
    }

    public function testGet(): void
    {
        $response = $this->customClass->get();

        $this->assertIsArray($response);
        $this->assertSame([], $response);
    }

    public function testGetAll(): void
    {
        $response = $this->customClass->getAll();

        $this->assertIsArray($response);
        $this->assertSame([], $response);
    }

    public function testRun(): void
    {
        $run = $this->customClass->run(self::CONNECTIONS);

        $this->assertInstanceOf(DatabaseInterface::class, $run);
        $this->assertInstanceOf($this->customClass::class, $run);
        $this->assertSame(self::CONNECTIONS, $this->getPrivateProperty('connections'));
    }

    public function testConnection(): void
    {
        $run = $this->customClass->run(self::CONNECTIONS)->connection(self::DATABASE_NAME_SECOND);

        $this->assertInstanceOf(DatabaseInterface::class, $run);
        $this->assertInstanceOf($this->customClass::class, $run);
        $this->assertSame(self::DATABASE_NAME_SECOND, $this->getPrivateProperty('activeConnection'));
        $this->assertSame(self::DATABASE_NAME_SECOND, $this->getPrivateProperty('dbname'));
    }

    public function testTransaction(): void
    {
        $run = $this->customClass->transaction(true);

        $this->assertInstanceOf(DatabaseInterface::class, $run);
        $this->assertInstanceOf($this->customClass::class, $run);
        $this->assertTrue($this->getPrivateProperty('isTransaction'));
    }

    public function testIsSchema(): void
    {
        $run = $this->customClass->isSchema(true);

        $this->assertInstanceOf(DatabaseInterface::class, $run);
        $this->assertInstanceOf($this->customClass::class, $run);
        $this->assertTrue($this->getPrivateProperty('isSchema'));
    }

    public function testEnableInsert(): void
    {
        $run = $this->customClass->enableInsert(true);

        $this->assertInstanceOf(DatabaseInterface::class, $run);
        $this->assertInstanceOf($this->customClass::class, $run);
        $this->assertTrue($this->getPrivateProperty('enableInsert'));
    }
}

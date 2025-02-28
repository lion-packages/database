<?php

declare(strict_types=1);

namespace Tests;

use Exception;
use Lion\Database\Driver;
use Lion\Database\Drivers\MySQL;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;

class DriverTest extends Test
{
    private const string DATABASE_TYPE = 'mysql';
    private const string DATABASE_HOST = 'mysql';
    private const int DATABASE_PORT = 3306;
    private const string DATABASE_NAME = 'lion_database';
    private const string DATABASE_NAME_SECOND = 'lion_database_second';
    private const string DATABASE_USER = 'root';
    private const string DATABASE_PASSWORD = 'lion';
    private const array CONNECTION_DATA = [
        'type' => self::DATABASE_TYPE,
        'host' => self::DATABASE_HOST,
        'port' => self::DATABASE_PORT,
        'dbname' => self::DATABASE_NAME,
        'user' => self::DATABASE_USER,
        'password' => self::DATABASE_PASSWORD,
    ];
    private const array CONNECTION_DATA_SECOND = [
        'type' => self::DATABASE_TYPE . '-type',
        'host' => self::DATABASE_HOST,
        'port' => self::DATABASE_PORT,
        'dbname' => self::DATABASE_NAME_SECOND,
        'user' => self::DATABASE_USER,
        'password' => self::DATABASE_PASSWORD,
    ];
    private const array CONNECTIONS = [
        'default' => self::DATABASE_NAME,
        'connections' => [
            self::DATABASE_NAME => self::CONNECTION_DATA,
            self::DATABASE_NAME_SECOND => self::CONNECTION_DATA_SECOND,
        ],
    ];

    private MySQL $mysql;

    /**
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $this->mysql = new MySQL();

        $this->initReflection($this->mysql);
    }

    /**
     * @throws ReflectionException
     */
    protected function tearDown(): void
    {
        $this->setPrivateProperty('connections', []);

        $this->setPrivateProperty('activeConnection', '');

        $this->setPrivateProperty('dbname', '');
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function runDriver(): void
    {
        Driver::run(self::CONNECTIONS);

        $this->assertSame(self::CONNECTIONS, $this->getPrivateProperty('connections'));
        $this->assertSame(self::DATABASE_NAME, $this->getPrivateProperty('activeConnection'));
        $this->assertSame(self::DATABASE_NAME, $this->getPrivateProperty('dbname'));
    }

    #[Testing]
    public function runWithoutDefault(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('No connection has been defined by default');

        Driver::run([]);
    }

    #[Testing]
    public function runOptionDefault(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('The defined driver does not exist');

        Driver::run([
            'default' => self::DATABASE_NAME_SECOND,
            'connections' => [
                self::DATABASE_NAME_SECOND => self::CONNECTION_DATA_SECOND,
            ],
        ]);
    }
}

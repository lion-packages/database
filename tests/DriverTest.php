<?php

declare(strict_types=1);

namespace Tests;

use LionDatabase\Driver;
use LionDatabase\Drivers\MySQL;
use LionTest\Test;

class DriverTest extends Test
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
        'type' => self::DATABASE_TYPE . '-type',
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

    private MySQL $mysql;

    protected function setUp(): void
    {
        $this->mysql = new MySQL();

        $this->initReflection($this->mysql);
    }

    protected function tearDown(): void
    {
        $this->setPrivateProperty('connections', []);
        $this->setPrivateProperty('activeConnection', '');
        $this->setPrivateProperty('dbname', '');
    }

    public function testRun(): void
    {
        $response = Driver::run(self::CONNECTIONS);

        $this->assertIsObject($response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertSame('success', $response->status);
        $this->assertSame('enabled connections', $response->message);
        $this->assertSame(self::CONNECTIONS, $this->getPrivateProperty('connections'));
        $this->assertSame(self::DATABASE_NAME, $this->getPrivateProperty('activeConnection'));
        $this->assertSame(self::DATABASE_NAME, $this->getPrivateProperty('dbname'));
    }

    public function testRunWithoutDefault(): void
    {
        $response = Driver::run([]);

        $this->assertIsObject($response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertSame('database-error', $response->status);
        $this->assertSame('the default driver is required', $response->message);
    }

    public function testRunOptionDefault(): void
    {
        $response = Driver::run([
            'default' => self::DATABASE_NAME_SECOND,
            'connections' => [
                self::DATABASE_NAME => self::CONNECTION_DATA,
                self::DATABASE_NAME_SECOND => self::CONNECTION_DATA_SECOND
            ]
        ]);

        $this->assertIsObject($response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertSame('database-error', $response->status);
        $this->assertSame('the driver does not exist', $response->message);
    }
}

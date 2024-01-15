<?php

declare(strict_types=1);

namespace Tests\Interface;

use Lion\Database\Interface\DatabaseConfigInterface;
use Lion\Test\Test;
use Tests\Provider\CustomClassProvider;

class DatabaseConfigInterfaceTest extends Test
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
        $this->customClass = new CustomClassProvider();

        $this->initReflection($this->customClass);
    }

    protected function tearDown(): void
    {
        $this->setPrivateProperty('connections', []);
        $this->setPrivateProperty('activeConnection', '');
        $this->setPrivateProperty('dbname', '');
    }

    public function testRun(): void
    {
        $run = $this->customClass->run(self::CONNECTIONS);

        $this->assertInstanceOf(DatabaseConfigInterface::class, $run);
        $this->assertInstanceOf($this->customClass::class, $run);
        $this->assertSame(self::CONNECTIONS, $this->getPrivateProperty('connections'));
    }

    public function testConnection(): void
    {
        $run = $this->customClass->run(self::CONNECTIONS)->connection(self::DATABASE_NAME_SECOND);

        $this->assertInstanceOf(DatabaseConfigInterface::class, $run);
        $this->assertInstanceOf($this->customClass::class, $run);
        $this->assertSame(self::DATABASE_NAME_SECOND, $this->getPrivateProperty('activeConnection'));
        $this->assertSame(self::DATABASE_NAME_SECOND, $this->getPrivateProperty('dbname'));
    }
}

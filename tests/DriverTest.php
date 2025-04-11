<?php

declare(strict_types=1);

namespace Tests;

use Exception;
use Lion\Database\Driver;
use Lion\Database\Drivers\MySQL;
use Lion\Database\Drivers\PostgreSQL;
use Lion\Database\Drivers\SQLite;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;

class DriverTest extends Test
{
    private MySQL $mysql;
    private PostgreSQL $postgreSQL;
    private SQLite $SQLite;

    protected function setUp(): void
    {
        $this->mysql = new MySQL();

        $this->postgreSQL = new PostgreSQL();

        $this->SQLite = new SQLite();
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function runDriverForMySQL(): void
    {
        $this->initReflection($this->mysql);

        $connections = [
            'default' => DATABASE_NAME_MYSQL,
            'connections' => [
                DATABASE_NAME_MYSQL => [
                    'type' => DATABASE_TYPE_MYSQL,
                    'host' => DATABASE_HOST_MYSQL,
                    'port' => DATABASE_PORT_MYSQL,
                    'dbname' => DATABASE_NAME_MYSQL,
                    'user' => DATABASE_USER_MYSQL,
                    'password' => DATABASE_PASSWORD_MYSQL,
                ],
            ],
        ];

        Driver::run($connections);

        $this->assertSame($connections, $this->getPrivateProperty('connections'));
        $this->assertSame(DATABASE_NAME_MYSQL, $this->getPrivateProperty('activeConnection'));
        $this->assertSame(DATABASE_NAME_MYSQL, $this->getPrivateProperty('dbname'));

        $this->setPrivateProperty('connections', []);

        $this->setPrivateProperty('activeConnection', '');

        $this->setPrivateProperty('dbname', '');
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function runDriverForPostgreSQL(): void
    {
        $this->initReflection($this->postgreSQL);

        $connections = [
            'default' => DATABASE_NAME_POSTGRESQL,
            'connections' => [
                DATABASE_NAME_POSTGRESQL => [
                    'type' => DATABASE_TYPE_POSTGRESQL,
                    'host' => DATABASE_HOST_POSTGRESQL,
                    'port' => DATABASE_PORT_POSTGRESQL,
                    'dbname' => DATABASE_NAME_POSTGRESQL,
                    'user' => DATABASE_USER_POSTGRESQL,
                    'password' => DATABASE_PASSWORD_POSTGRESQL,
                ],
            ],
        ];

        Driver::run($connections);

        $this->assertSame($connections, $this->getPrivateProperty('connections'));
        $this->assertSame(DATABASE_NAME_MYSQL, $this->getPrivateProperty('activeConnection'));
        $this->assertSame(DATABASE_NAME_MYSQL, $this->getPrivateProperty('dbname'));

        $this->setPrivateProperty('connections', []);

        $this->setPrivateProperty('activeConnection', '');

        $this->setPrivateProperty('dbname', '');
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function runDriverForSQLite(): void
    {
        $this->initReflection($this->SQLite);

        $connections = [
            'default' => DATABASE_NAME_SQLITE,
            'connections' => [
                DATABASE_NAME_SQLITE => CONNECTION_DATA_SQLITE,
            ],
        ];

        Driver::run($connections);

        $this->assertSame($connections, $this->getPrivateProperty('connections'));
        $this->assertSame(DATABASE_NAME_SQLITE, $this->getPrivateProperty('activeConnection'));
        $this->assertSame(DATABASE_NAME_SQLITE, $this->getPrivateProperty('dbname'));

        $this->setPrivateProperty('connections', []);

        $this->setPrivateProperty('activeConnection', '');

        $this->setPrivateProperty('dbname', '');
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
            'default' => DATABASE_NAME_CONNECTION,
            'connections' => [
                DATABASE_NAME_CONNECTION => [
                    'type' => 'test',
                    'dbname' => DATABASE_NAME_SQLITE,
                ],
            ],
        ]);
    }
}

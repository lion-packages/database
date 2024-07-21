<?php

declare(strict_types=1);

namespace Tests\Drivers;

use InvalidArgumentException;
use Lion\Database\Connection;
use Lion\Database\Drivers\PostgreSQL;
use Lion\Database\Interface\DatabaseCapsuleInterface;
use Lion\Database\Interface\DatabaseConfigInterface;
use Lion\Test\Test;
use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test as Testing;
use PHPUnit\Framework\Attributes\TestWith;
use stdClass;
use Tests\Provider\IdInterface;
use Tests\Provider\PostgreSQLProviderTrait;

class PostgreSQLTest extends Test
{
    use PostgreSQLProviderTrait;

    private const int USERS_ID = 1;
    private const int USERS_SECOND_ID = 2;
    private const string DATABASE_TYPE = 'postgresql';
    private const string DATABASE_HOST = 'postgres';
    private const int DATABASE_PORT = 5432;
    private const string DATABASE_NAME = 'lion_database';
    private const string DATABASE_USER = 'root';
    private const string DATABASE_PASSWORD = 'lion';
    private const string DATABASE_NAME_SECOND = 'lion_database_second';
    private const array CONNECTION_DATA = [
        'type' => self::DATABASE_TYPE,
        'host' => self::DATABASE_HOST,
        'port' => self::DATABASE_PORT,
        'dbname' => self::DATABASE_NAME,
        'user' => self::DATABASE_USER,
        'password' => self::DATABASE_PASSWORD,
    ];
    private const array CONNECTION_DATA_SECOND = [
        'type' => self::DATABASE_TYPE,
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
        ],
    ];

    private PostgreSQL $postgresql;
    private string $actualCode;

    protected function setUp(): void
    {
        $this->postgresql = new PostgreSQL();

        $this->actualCode = uniqid();

        $this->initReflection($this->postgresql);
    }

    protected function tearDown(): void
    {
        $this->setPrivateProperty('connections', []);

        $this->setPrivateProperty('activeConnection', '');

        $this->setPrivateProperty('dbname', '');

        $this->setPrivateProperty('sql', '');

        $this->setPrivateProperty('table', '');

        $this->setPrivateProperty('view', '');

        $this->setPrivateProperty('dataInfo', []);

        $this->setPrivateProperty('isSchema', false);

        $this->setPrivateProperty('enableInsert', false);

        $this->setPrivateProperty('actualCode', '');

        $this->setPrivateProperty('fetchMode', []);

        $this->setPrivateProperty('message', 'execution finished');
    }

    private function setActualCode(): void
    {
        $this->setPrivateProperty('actualCode', $this->actualCode);

        $this->assertSame($this->actualCode, $this->getPrivateProperty('actualCode'));
    }

    #[Testing]
    public function runInterface(): void
    {
        $this->assertInstances($this->postgresql->run(self::CONNECTIONS), [
            PostgreSQL::class,
            Connection::class,
            DatabaseConfigInterface::class,
        ]);

        $this->assertSame(self::CONNECTIONS, $this->getPrivateProperty('connections'));
    }

    #[Testing]
    public function runInterfaceWithoutDefaultValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('no default database defined');

        $this->postgresql->run([]);
    }

    #[Testing]
    #[DataProvider('runInterfaceWithoutConnectionsProvider')]
    public function runInterfaceWithoutConnections(array $connections): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('no databases have been defined');

        $this->postgresql->run($connections);
    }

    #[Testing]
    public function connectionInterface(): void
    {
        $this->assertInstances($this->postgresql->run(self::CONNECTIONS), [
            PostgreSQL::class,
            Connection::class,
            DatabaseConfigInterface::class,
        ]);

        $this->assertSame(self::CONNECTIONS, $this->getPrivateProperty('connections'));

        $this->assertInstances($this->postgresql->connection(self::DATABASE_NAME), [
            PostgreSQL::class,
            Connection::class,
            DatabaseConfigInterface::class,
        ]);

        $this->assertSame(self::DATABASE_NAME, $this->getPrivateProperty('activeConnection'));
        $this->assertSame(self::DATABASE_NAME, $this->getPrivateProperty('dbname'));
    }

    #[Testing]
    #[TestWith(['connection' => self::DATABASE_NAME])]
    #[TestWith(['connection' => self::DATABASE_NAME_SECOND])]
    public function connectionInterfaceConnectionDoesNotExist(string $connection): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('the selected connection does not exist');

        $this->postgresql->connection($connection);
    }

    #[Testing]
    #[DataProvider('getProvider')]
    public function getInterface(string $dropSql, string $tableSql, string $insertSql, string $selectSql): void
    {
        $response = $this->postgresql
            ->run(self::CONNECTIONS)
            ->query($dropSql)
            ->query($tableSql)
            ->query($insertSql)
            ->execute();

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertSame(200, $response->code);
        $this->assertIsString($response->status);
        $this->assertSame('success', $response->status);
        $this->assertIsString($response->message);
        $this->assertSame('execution finished', $response->message);

        $response = $this->postgresql
            ->run(self::CONNECTIONS)
            ->query($selectSql)
            ->get();

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('id', $response);

        $response = $this->postgresql
            ->run(self::CONNECTIONS)
            ->query($dropSql)
            ->execute();

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertSame(200, $response->code);
        $this->assertIsString($response->status);
        $this->assertSame('success', $response->status);
        $this->assertIsString($response->message);
        $this->assertSame('execution finished', $response->message);
    }

    #[Testing]
    #[DataProvider('getProvider')]
    public function getInterfaceDataNoAvailable(
        string $dropSql,
        string $tableSql,
        string $insertSql,
        string $selectSql
    ): void {
        $response = $this->postgresql
            ->run(self::CONNECTIONS)
            ->query($dropSql)
            ->query($tableSql)
            ->execute();

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertSame(200, $response->code);
        $this->assertIsString($response->status);
        $this->assertSame('success', $response->status);
        $this->assertIsString($response->message);
        $this->assertSame('execution finished', $response->message);

        $response = $this->postgresql
            ->run(self::CONNECTIONS)
            ->query($selectSql)
            ->get();

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertSame(200, $response->code);
        $this->assertIsString($response->status);
        $this->assertSame('success', $response->status);
        $this->assertIsString($response->message);
        $this->assertSame('no data available', $response->message);

        $response = $this->postgresql
            ->run(self::CONNECTIONS)
            ->query($dropSql)
            ->execute();

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertSame(200, $response->code);
        $this->assertIsString($response->status);
        $this->assertSame('success', $response->status);
        $this->assertIsString($response->message);
        $this->assertSame('execution finished', $response->message);
    }

    #[Testing]
    #[DataProvider('getProviderWithFetchClass')]
    public function getInterfaceWithFetchClass(
        string $dropSql,
        string $tableSql,
        string $insertSql,
        string $selectSql,
        DatabaseCapsuleInterface|IdInterface $capsule
    ): void {
        $response = $this->postgresql
            ->run(self::CONNECTIONS)
            ->query($dropSql)
            ->query($tableSql)
            ->query($insertSql)
            ->execute();

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertSame(200, $response->code);
        $this->assertIsString($response->status);
        $this->assertSame('success', $response->status);
        $this->assertIsString($response->message);
        $this->assertSame('execution finished', $response->message);

        /** @var DatabaseCapsuleInterface|IdInterface $response */
        $response = $this->postgresql
            ->run(self::CONNECTIONS)
            ->query($selectSql)
            ->addRows([self::USERS_ID])
            ->fetchMode(PDO::FETCH_CLASS, $capsule::class)
            ->get();

        $this->assertIsObject($response);

        $this->assertInstances($response, [
            DatabaseCapsuleInterface::class,
            IdInterface::class,
        ]);

        $this->assertIsInt($response->getId());
        $this->assertSame(1, $response->getId());

        $response = $this->postgresql
            ->run(self::CONNECTIONS)
            ->query($dropSql)
            ->execute();

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertSame(200, $response->code);
        $this->assertIsString($response->status);
        $this->assertSame('success', $response->status);
        $this->assertIsString($response->message);
        $this->assertSame('execution finished', $response->message);
    }

    #[Testing]
    #[DataProvider('getProviderWithFetchClass')]
    public function getInterfaceWithMultipleFetchMode(
        string $dropSql,
        string $tableSql,
        string $insertSql,
        string $selectSql,
        DatabaseCapsuleInterface|IdInterface $capsule
    ): void {
        $response = $this->postgresql
            ->run(self::CONNECTIONS)
            ->query($dropSql)
            ->query($tableSql)
            ->query($insertSql)
            ->execute();

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertSame(200, $response->code);
        $this->assertIsString($response->status);
        $this->assertSame('success', $response->status);
        $this->assertIsString($response->message);
        $this->assertSame('execution finished', $response->message);

        /** @var array<int, array<int, DatabaseCapsuleInterface|IdInterface>> $response */
        $response = $this->postgresql
            ->run(self::CONNECTIONS)
            ->query($selectSql)
            ->addRows([self::USERS_ID])
            ->fetchMode(PDO::FETCH_CLASS, $capsule::class)
            ->query($selectSql)
            ->addRows([999])
            ->fetchMode(PDO::FETCH_CLASS, $capsule::class)
            ->get();

        $this->assertIsArray($response);
        $this->assertCount(2, $response);

        $firstUser = reset($response);

        $this->assertIsObject($firstUser);

        $this->assertInstances($firstUser, [
            DatabaseCapsuleInterface::class,
            IdInterface::class,
        ]);

        $this->assertIsInt($firstUser->getId());
        $this->assertSame(1, $firstUser->getId());

        $secondUser = end($response);

        $this->assertIsObject($secondUser);
        $this->assertInstanceOf(stdClass::class, $secondUser);
        $this->assertObjectHasProperty('code', $secondUser);
        $this->assertObjectHasProperty('status', $secondUser);
        $this->assertObjectHasProperty('message', $secondUser);
        $this->assertIsInt($secondUser->code);
        $this->assertSame(200, $secondUser->code);
        $this->assertIsString($secondUser->status);
        $this->assertSame('success', $secondUser->status);
        $this->assertIsString($secondUser->message);
        $this->assertSame('no data available', $secondUser->message);

        $response = $this->postgresql
            ->run(self::CONNECTIONS)
            ->query($dropSql)
            ->execute();

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertSame(200, $response->code);
        $this->assertIsString($response->status);
        $this->assertSame('success', $response->status);
        $this->assertIsString($response->message);
        $this->assertSame('execution finished', $response->message);
    }

    #[Testing]
    #[DataProvider('getAllProvider')]
    public function getAllInterface(string $dropSql, string $tableSql, string $insertSql, string $selectSql): void
    {
        $response = $this->postgresql
            ->run(self::CONNECTIONS)
            ->query($dropSql)
            ->query($tableSql)
            ->query($insertSql)
            ->execute();

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertSame(200, $response->code);
        $this->assertIsString($response->status);
        $this->assertSame('success', $response->status);
        $this->assertIsString($response->message);
        $this->assertSame('execution finished', $response->message);

        $response = $this->postgresql
            ->run(self::CONNECTIONS)
            ->query($selectSql)
            ->getAll();

        $this->assertIsArray($response);
        $this->assertCount(4, $response);

        $firstUser = reset($response);

        $this->assertIsObject($firstUser);
        $this->assertInstanceOf(stdClass::class, $firstUser);
        $this->assertObjectHasProperty('id', $firstUser);

        $response = $this->postgresql
            ->run(self::CONNECTIONS)
            ->query($dropSql)
            ->execute();

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertSame(200, $response->code);
        $this->assertIsString($response->status);
        $this->assertSame('success', $response->status);
        $this->assertIsString($response->message);
        $this->assertSame('execution finished', $response->message);
    }

    #[Testing]
    #[DataProvider('getAllProvider')]
    public function getAllInterfaceDataNoAvailable(
        string $dropSql,
        string $tableSql,
        string $insertSql,
        string $selectSql
    ): void {
        $response = $this->postgresql
            ->run(self::CONNECTIONS)
            ->query($dropSql)
            ->query($tableSql)
            ->execute();

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertSame(200, $response->code);
        $this->assertIsString($response->status);
        $this->assertSame('success', $response->status);
        $this->assertIsString($response->message);
        $this->assertSame('execution finished', $response->message);

        $response = $this->postgresql
            ->run(self::CONNECTIONS)
            ->query($selectSql)
            ->getAll();

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertSame(200, $response->code);
        $this->assertIsString($response->status);
        $this->assertSame('success', $response->status);
        $this->assertIsString($response->message);
        $this->assertSame('no data available', $response->message);

        $response = $this->postgresql
            ->run(self::CONNECTIONS)
            ->query($dropSql)
            ->execute();

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertSame(200, $response->code);
        $this->assertIsString($response->status);
        $this->assertSame('success', $response->status);
        $this->assertIsString($response->message);
        $this->assertSame('execution finished', $response->message);
    }

    #[Testing]
    #[DataProvider('getAllProviderWithFetchClass')]
    public function getAllInterfaceWithFetchClass(
        string $dropSql,
        string $tableSql,
        string $insertSql,
        string $selectSql,
        DatabaseCapsuleInterface|IdInterface $capsule
    ): void {
        $response = $this->postgresql
            ->run(self::CONNECTIONS)
            ->query($dropSql)
            ->query($tableSql)
            ->query($insertSql)
            ->execute();

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertSame(200, $response->code);
        $this->assertIsString($response->status);
        $this->assertSame('success', $response->status);
        $this->assertIsString($response->message);
        $this->assertSame('execution finished', $response->message);

        /** @var array $response */
        $response = $this->postgresql
            ->run(self::CONNECTIONS)
            ->query($selectSql)
            ->fetchMode(PDO::FETCH_CLASS, $capsule::class)
            ->getAll();

        $this->assertIsArray($response);
        $this->assertCount(4, $response);

        $firstUser = reset($response);

        $this->assertIsObject($firstUser);

        $this->assertInstances($firstUser, [
            DatabaseCapsuleInterface::class,
            IdInterface::class,
        ]);

        $this->assertIsInt($firstUser->getId());
        $this->assertSame(1, $firstUser->getId());

        $response = $this->postgresql
            ->run(self::CONNECTIONS)
            ->query($dropSql)
            ->execute();

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertSame(200, $response->code);
        $this->assertIsString($response->status);
        $this->assertSame('success', $response->status);
        $this->assertIsString($response->message);
        $this->assertSame('execution finished', $response->message);
    }

    #[Testing]
    #[DataProvider('getAllProviderWithFetchClassAndNotDataAvailable')]
    public function getAllInterfaceWithMultipleFetchMode(
        string $dropSql,
        string $tableSql,
        string $insertSql,
        string $selectSql,
        DatabaseCapsuleInterface|IdInterface $capsule
    ): void {
        $response = $this->postgresql
            ->run(self::CONNECTIONS)
            ->query($dropSql)
            ->query($tableSql)
            ->query($insertSql)
            ->execute();

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertSame(200, $response->code);
        $this->assertIsString($response->status);
        $this->assertSame('success', $response->status);
        $this->assertIsString($response->message);
        $this->assertSame('execution finished', $response->message);

        /** @var array<int, array<int, DatabaseCapsuleInterface|IdInterface>> $response */
        $response = $this->postgresql
            ->run(self::CONNECTIONS)
            ->query($selectSql)
            ->addRows([self::USERS_ID, self::USERS_SECOND_ID])
            ->fetchMode(PDO::FETCH_CLASS, $capsule::class)
            ->query($selectSql)
            ->addRows([999, 999])
            ->fetchMode(PDO::FETCH_CLASS, $capsule::class)
            ->getAll();

        $this->assertIsArray($response);
        $this->assertCount(2, $response);

        $firstList = reset($response);

        $this->assertIsArray($firstList);
        $this->assertCount(2, $firstList);

        $firstUser = reset($firstList);

        $this->assertIsObject($firstUser);

        $this->assertInstances($firstUser, [
            DatabaseCapsuleInterface::class,
            IdInterface::class,
        ]);

        $this->assertIsInt($firstUser->getId());
        $this->assertSame(1, $firstUser->getId());

        $secondList = end($response);

        $this->assertIsObject($secondList);
        $this->assertInstanceOf(stdClass::class, $secondList);
        $this->assertObjectHasProperty('code', $secondList);
        $this->assertObjectHasProperty('status', $secondList);
        $this->assertObjectHasProperty('message', $secondList);
        $this->assertIsInt($secondList->code);
        $this->assertSame(200, $secondList->code);
        $this->assertIsString($secondList->status);
        $this->assertSame('success', $secondList->status);
        $this->assertIsString($secondList->message);
        $this->assertSame('no data available', $secondList->message);

        $response = $this->postgresql
            ->run(self::CONNECTIONS)
            ->query($dropSql)
            ->execute();

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertSame(200, $response->code);
        $this->assertIsString($response->status);
        $this->assertSame('success', $response->status);
        $this->assertIsString($response->message);
        $this->assertSame('execution finished', $response->message);
    }

    #[Testing]
    #[DataProvider('executeInterfaceProvider')]
    public function executeInterface(string $dropSql, string $tableSql, string $insertSql): void
    {
        $response = $this->postgresql
            ->run(self::CONNECTIONS)
            ->query($dropSql)
            ->query($tableSql)
            ->query($insertSql)
            ->query($dropSql)
            ->execute();

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertSame(200, $response->code);
        $this->assertIsString($response->status);
        $this->assertSame('success', $response->status);
        $this->assertIsString($response->message);
        $this->assertSame('execution finished', $response->message);
    }

    #[Testing]
    public function executeInterfaceWithParams(): void
    {
        $response = $this->postgresql
            ->run(self::CONNECTIONS)
            ->query(self::QUERY_SQL_DROP_TABLE_ROLES)
            ->query(self::QUERY_SQL_TABLE_ROLES)
            ->query(self::QUERY_SQL_NESTED_INSERT_ROLES)
            ->addRows(['Example'])
            ->query(self::QUERY_SQL_DROP_TABLE_ROLES)
            ->execute();

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertSame(200, $response->code);
        $this->assertIsString($response->status);
        $this->assertSame('success', $response->status);
        $this->assertIsString($response->message);
        $this->assertSame('execution finished', $response->message);
    }

    #[Testing]
    public function executeInterfaceMultipleQueryAndParams(): void
    {
        $response = $this->postgresql
            ->run(self::CONNECTIONS)
            ->query(self::QUERY_SQL_DROP_TABLE_ROLES)
            ->query(self::QUERY_SQL_TABLE_ROLES)
            ->execute();

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertSame(200, $response->code);
        $this->assertIsString($response->status);
        $this->assertSame('success', $response->status);
        $this->assertIsString($response->message);
        $this->assertSame('execution finished', $response->message);

        $this->postgresql
            ->query(self::QUERY_SQL_INSERT_ROLES_WITH_PARAMS);

        $this->setPrivateProperty('dataInfo', []);

        $response = $this->postgresql
            ->execute();

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertSame(200, $response->code);
        $this->assertIsString($response->status);
        $this->assertSame('success', $response->status);
        $this->assertIsString($response->message);
        $this->assertSame('execution finished', $response->message);

        $response = $this->postgresql
            ->query(self::QUERY_SQL_DROP_TABLE_ROLES)
            ->execute();

        $this->assertIsObject($response);
        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertIsInt($response->code);
        $this->assertSame(200, $response->code);
        $this->assertIsString($response->status);
        $this->assertSame('success', $response->status);
        $this->assertIsString($response->message);
        $this->assertSame('execution finished', $response->message);
    }

    #[Testing]
    #[TestWith(['sql' => self::QUERY_SQL_TABLE_ROLES])]
    #[TestWith(['sql' => self::QUERY_SQL_TABLE_USERS])]
    public function query(string $sql): void
    {
        $this->assertInstances($this->postgresql->query($sql), [
            PostgreSQL::class,
            Connection::class,
            DatabaseConfigInterface::class,
        ]);

        $this->assertSame($sql, $this->getPrivateProperty('sql'));
    }
}

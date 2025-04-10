<?php

declare(strict_types=1);

namespace Tests\Drivers;

use Closure;
use InvalidArgumentException;
use Lion\Database\Connection;
use Lion\Database\Drivers\PostgreSQL;
use Lion\Database\Drivers\SQLite;
use Lion\Database\Interface\DatabaseCapsuleInterface;
use Lion\Database\Interface\DatabaseConfigInterface;
use Lion\Test\Test;
use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test as Testing;
use PHPUnit\Framework\Attributes\TestWith;
use ReflectionException;
use stdClass;
use Tests\Provider\IdInterface;
use Tests\Provider\SQLiteProviderTrait;

class SQLiteTest extends Test
{
    use SQLiteProviderTrait;

    private const int USERS_ID = 1;
    private const int USERS_SECOND_ID = 2;

    private SQLite $SQLite;
    private string $actualCode;

    /**
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $this->SQLite = new SQLite();

        $this->actualCode = uniqid();

        $this->initReflection($this->SQLite);

        $this->setPrivateProperty('actualCode', $this->actualCode);
    }

    /**
     * @throws ReflectionException
     */
    protected function tearDown(): void
    {
        $this->setPrivateProperty('withRowCount', false);

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

        $this->setPrivateProperty('message', 'Execution finished');

        $this->setPrivateProperty('databaseInstances', []);
    }

    private function getQuery(): string
    {
        $queryString = $this->SQLite->getQueryString();

        /** @var stdClass $data */
        $data = $queryString->data;

        /** @var string $query */
        $query = $data->query;

        return $query;
    }

    private function processes(Closure $callback): void
    {
        $copyDir = __DIR__ . '/../Provider/copy/';

        $copyFile = $copyDir . 'lion_database.sqlite';

        if (!is_dir($copyDir)) {
            mkdir($copyDir, 0777, true);
        }

        $this->assertDirectoryExists($copyDir);

        copy(__DIR__ . '/../Provider/lion_database.sqlite', $copyFile);

        chmod($copyDir, 0777);

        chmod($copyFile, 0666);

        $this->assertFileExists($copyFile);

        $callback();

        if (file_exists($copyDir . 'lion_database.sqlite')) {
            unlink($copyDir . 'lion_database.sqlite');
        }

        $this->rmdirRecursively($copyDir);

        $this->assertDirectoryDoesNotExist($copyDir);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function runInterface(): void
    {
        $this->assertInstanceOf(SQLite::class, $this->SQLite->run(CONNECTIONS_SQLITE));

        $this->assertSame(CONNECTIONS_SQLITE, $this->getPrivateProperty('connections'));
    }

    #[Testing]
    public function runInterfaceWithoutDefaultValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('No default database defined');

        $this->SQLite->run([]);
    }

    #[Testing]
    #[DataProvider('runInterfaceWithoutConnectionsProvider')]
    public function runInterfaceWithoutConnections(array $connections): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('No databases have been defined');

        $this->SQLite->run($connections);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function connectionInterface(): void
    {
        $this->assertInstanceOf(SQLite::class, $this->SQLite->run(CONNECTIONS_SQLITE));

        $this->assertSame(CONNECTIONS_SQLITE, $this->getPrivateProperty('connections'));

        $this->assertInstanceOf(SQLite::class, $this->SQLite->connection(DATABASE_NAME_SQLITE));

        $this->assertSame(DATABASE_NAME_SQLITE, $this->getPrivateProperty('activeConnection'));
        $this->assertSame(DATABASE_NAME_SQLITE, $this->getPrivateProperty('dbname'));
    }

    #[Testing]
    #[TestWith(['connection' => DATABASE_NAME_SQLITE])]
    #[TestWith(['connection' => 'test_' . DATABASE_NAME_SQLITE])]
    public function connectionInterfaceConnectionDoesNotExist(string $connection): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('The selected connection does not exist');

        $this->SQLite->connection($connection);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('getQueryStringProvider')]
    public function getQueryString(string $query): void
    {
        $this->setPrivateProperty('sql', $query);

        $response = $this->SQLite->getQueryString();

        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertObjectHasProperty('data', $response);
        $this->assertIsString($response->status);
        $this->assertIsString($response->message);
        $this->assertIsObject($response->data);
        $this->assertInstanceOf(stdClass::class, $response->data);
        $this->assertObjectHasProperty('query', $response->data);
        $this->assertObjectHasProperty('split', $response->data);
        $this->assertIsString($response->data->query);
        $this->assertIsArray($response->data->split);
        $this->assertSame('success', $response->status);
        $this->assertSame('SQL query generated successfully', $response->message);
        $this->assertSame($query, $response->data->query);
        $this->assertSame(explode(';', $query), $response->data->split);
    }

    #[Testing]
    #[DataProvider('getProvider')]
    public function getInterface(string $dropSql, string $tableSql, string $insertSql, string $selectSql): void
    {
        $this->processes(function () use ($dropSql, $tableSql, $insertSql, $selectSql): void {
            $response = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
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
            $this->assertSame('Execution finished', $response->message);

            $response = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
                ->query($selectSql)
                ->get();

            $this->assertIsObject($response);
            $this->assertInstanceOf(stdClass::class, $response);
            $this->assertObjectHasProperty('id', $response);

            $response = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
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
            $this->assertSame('Execution finished', $response->message);
        });
    }

    #[Testing]
    #[DataProvider('getProvider')]
    public function getInterfaceDataNoAvailable(
        string $dropSql,
        string $tableSql,
        string $insertSql,
        string $selectSql
    ): void {
        $this->processes(function () use ($dropSql, $tableSql, $selectSql): void {
            $response = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
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
            $this->assertSame('Execution finished', $response->message);

            $response = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
                ->query($selectSql)
                ->get();

            $this->assertIsArray($response);
            $this->assertEmpty($response);

            $response = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
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
            $this->assertSame('Execution finished', $response->message);
        });
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
        $this->processes(function () use ($dropSql, $tableSql, $insertSql, $selectSql, $capsule): void {
            $response = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
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
            $this->assertSame('Execution finished', $response->message);

            /** @var DatabaseCapsuleInterface|IdInterface $response */
            $response = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
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
            $this->assertSame(self::USERS_ID, $response->getId());

            $response = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
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
            $this->assertSame('Execution finished', $response->message);
        });
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
        $this->processes(function () use ($dropSql, $tableSql, $insertSql, $selectSql, $capsule): void {
            $response = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
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
            $this->assertSame('Execution finished', $response->message);

            /** @var array<int, array<int, DatabaseCapsuleInterface|IdInterface>> $response */
            $response = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
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

            $this->assertIsArray($secondUser);
            $this->assertEmpty($secondUser);

            $response = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
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
            $this->assertSame('Execution finished', $response->message);
        });
    }

    #[Testing]
    #[DataProvider('getAllProvider')]
    public function getAllInterface(string $dropSql, string $tableSql, string $insertSql, string $selectSql): void
    {
        $this->processes(function () use ($dropSql, $tableSql, $insertSql, $selectSql): void {
            $response = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
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
            $this->assertSame('Execution finished', $response->message);

            $response = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
                ->query($selectSql)
                ->getAll();

            $this->assertIsArray($response);
            $this->assertCount(4, $response);

            $firstUser = reset($response);

            $this->assertIsObject($firstUser);
            $this->assertInstanceOf(stdClass::class, $firstUser);
            $this->assertObjectHasProperty('id', $firstUser);

            $response = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
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
            $this->assertSame('Execution finished', $response->message);
        });
    }

    #[Testing]
    #[DataProvider('getAllProvider')]
    public function getAllInterfaceDataNoAvailable(
        string $dropSql,
        string $tableSql,
        string $insertSql,
        string $selectSql
    ): void {
        $this->processes(function () use ($dropSql, $tableSql, $insertSql, $selectSql): void {
            $response = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
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
            $this->assertSame('Execution finished', $response->message);

            $response = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
                ->query($selectSql)
                ->getAll();

            $this->assertIsArray($response);
            $this->assertEmpty($response);

            $response = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
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
            $this->assertSame('Execution finished', $response->message);
        });
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
        $this->processes(function () use ($dropSql, $tableSql, $insertSql, $selectSql, $capsule): void {
            $response = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
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
            $this->assertSame('Execution finished', $response->message);

            /** @var array<int, IdInterface> $response */
            $response = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
                ->query($selectSql)
                ->fetchMode(PDO::FETCH_CLASS, $capsule::class)
                ->getAll();

            $this->assertIsArray($response);
            $this->assertCount(4, $response);

            /** @var IdInterface $firstUser */
            $firstUser = reset($response);

            $this->assertIsObject($firstUser);

            $this->assertInstances($firstUser, [
                DatabaseCapsuleInterface::class,
                IdInterface::class,
            ]);

            $this->assertIsInt($firstUser->getId());
            $this->assertSame(1, $firstUser->getId());

            $response = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
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
            $this->assertSame('Execution finished', $response->message);
        });
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
        $this->processes(function () use ($dropSql, $tableSql, $insertSql, $selectSql, $capsule): void {
            $response = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
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
            $this->assertSame('Execution finished', $response->message);

            /** @var array<int, array<int, DatabaseCapsuleInterface|IdInterface>> $response */
            $response = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
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

            $this->assertIsArray($secondList);
            $this->assertEmpty($secondList);

            $response = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
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
            $this->assertSame('Execution finished', $response->message);
        });
    }

    #[Testing]
    #[DataProvider('executeInterfaceProvider')]
    public function executeInterface(string $dropSql, string $tableSql, string $insertSql): void
    {
        $this->processes(function () use ($dropSql, $tableSql, $insertSql): void {
            $response = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
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
            $this->assertSame('Execution finished', $response->message);
        });
    }

    #[Testing]
    public function executeInterfaceWithParams(): void
    {
        $this->processes(function (): void {
            $response = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
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
            $this->assertSame('Execution finished', $response->message);
        });
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function executeInterfaceMultipleQueryAndParams(): void
    {
        $this->processes(function (): void {
            $response = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
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
            $this->assertSame('Execution finished', $response->message);

            $this->SQLite
                ->query(self::QUERY_SQL_INSERT_ROLES_WITH_PARAMS);

            $this->setPrivateProperty('dataInfo', []);

            $response = $this->SQLite
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
            $this->assertSame('Execution finished', $response->message);

            $response = $this->SQLite
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
            $this->assertSame('Execution finished', $response->message);
        });
    }

    #[Testing]
    public function executeWithRowCount(): void
    {
        $this->processes(function (): void {
            $createTableResponse = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
                ->query(self::QUERY_SQL_DROP_TABLE_ROLES)
                ->query(self::QUERY_SQL_TABLE_ROLES)
                ->execute();

            $this->assertInstanceOf(stdclass::class, $createTableResponse);
            $this->assertObjectHasProperty('code', $createTableResponse);
            $this->assertObjectHasProperty('status', $createTableResponse);
            $this->assertObjectHasProperty('message', $createTableResponse);
            $this->assertIsInt($createTableResponse->code);
            $this->assertIsString($createTableResponse->status);
            $this->assertIsString($createTableResponse->message);
            $this->assertSame(200, $createTableResponse->code);
            $this->assertSame('success', $createTableResponse->status);
            $this->assertSame('Execution finished', $createTableResponse->message);

            $insertResponse = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
                ->table('roles', false)
                ->insert([
                    'roles_name' => 'Role test'
                ])
                ->rowCount()
                ->execute();

            $this->assertSame(1, $insertResponse);

            $dropTableResponse = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
                ->query(self::QUERY_SQL_DROP_TABLE_ROLES)
                ->execute();

            $this->assertInstanceOf(stdclass::class, $dropTableResponse);
            $this->assertObjectHasProperty('code', $dropTableResponse);
            $this->assertObjectHasProperty('status', $dropTableResponse);
            $this->assertObjectHasProperty('message', $dropTableResponse);
            $this->assertIsInt($dropTableResponse->code);
            $this->assertIsString($dropTableResponse->status);
            $this->assertIsString($dropTableResponse->message);
            $this->assertSame(200, $dropTableResponse->code);
            $this->assertSame('success', $dropTableResponse->status);
            $this->assertSame('Execution finished', $dropTableResponse->message);
        });
    }

    #[Testing]
    #[DataProvider('transactionInterfaceProvider')]
    public function transactionInterface(string $dropSql, string $tableSql, string $insertSql, string $selectSql): void
    {
        $this->processes(function () use ($dropSql, $tableSql, $insertSql, $selectSql): void {
            $createTableResponse = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
                ->query($dropSql)
                ->query($tableSql)
                ->execute();

            $this->assertIsObject($createTableResponse);
            $this->assertInstanceOf(stdClass::class, $createTableResponse);
            $this->assertObjectHasProperty('code', $createTableResponse);
            $this->assertObjectHasProperty('status', $createTableResponse);
            $this->assertObjectHasProperty('message', $createTableResponse);
            $this->assertIsInt($createTableResponse->code);
            $this->assertSame(200, $createTableResponse->code);
            $this->assertIsString($createTableResponse->status);
            $this->assertSame('success', $createTableResponse->status);
            $this->assertIsString($createTableResponse->message);
            $this->assertSame('Execution finished', $createTableResponse->message);

            $insertResponse = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
                ->transaction()
                ->query($insertSql)
                ->execute();

            $this->assertIsObject($insertResponse);
            $this->assertInstanceOf(stdClass::class, $insertResponse);
            $this->assertObjectHasProperty('code', $insertResponse);
            $this->assertObjectHasProperty('status', $insertResponse);
            $this->assertObjectHasProperty('message', $insertResponse);
            $this->assertIsInt($insertResponse->code);
            $this->assertSame(200, $insertResponse->code);
            $this->assertIsString($insertResponse->status);
            $this->assertSame('success', $insertResponse->status);
            $this->assertIsString($insertResponse->message);
            $this->assertSame('Execution finished', $insertResponse->message);

            $data = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
                ->query($selectSql)
                ->getAll();

            $this->assertIsArray($data);
            $this->assertCount(4, $data);

            $response = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
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
            $this->assertSame('Execution finished', $response->message);
        });
    }

    #[Testing]
    #[DataProvider('transactionInterfaceWithRollbackProvider')]
    public function transactionInterfaceWithRollback(
        string $dropSql,
        string $tableSql,
        string $insertSql,
        string $selectSql
    ): void {
        $this->processes(function () use ($dropSql, $tableSql, $insertSql, $selectSql): void {
            $createTableResponse = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
                ->transaction()
                ->query($dropSql)
                ->query($tableSql)
                ->execute();

            $this->assertIsObject($createTableResponse);
            $this->assertInstanceOf(stdClass::class, $createTableResponse);
            $this->assertObjectHasProperty('code', $createTableResponse);
            $this->assertObjectHasProperty('status', $createTableResponse);
            $this->assertObjectHasProperty('message', $createTableResponse);
            $this->assertIsInt($createTableResponse->code);
            $this->assertSame(200, $createTableResponse->code);
            $this->assertIsString($createTableResponse->status);
            $this->assertSame('success', $createTableResponse->status);
            $this->assertIsString($createTableResponse->message);
            $this->assertSame('Execution finished', $createTableResponse->message);

            $insertResponse = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
                ->transaction()
                ->query($insertSql)
                ->execute();

            $this->assertIsObject($insertResponse);
            $this->assertInstanceOf(stdClass::class, $insertResponse);
            $this->assertObjectHasProperty('code', $insertResponse);
            $this->assertObjectHasProperty('status', $insertResponse);
            $this->assertObjectHasProperty('message', $insertResponse);
            $this->assertIsString($insertResponse->code);
            $this->assertSame('23000', $insertResponse->code);
            $this->assertIsString($insertResponse->status);
            $this->assertSame('database-error', $insertResponse->status);
            $this->assertIsString($insertResponse->message);

            $data = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
                ->query($selectSql)
                ->getAll();

            $this->assertIsArray($data);
            $this->assertEmpty($data);

            $response = $this->SQLite
                ->run(CONNECTIONS_SQLITE)
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
            $this->assertSame('Execution finished', $response->message);
        });
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[TestWith(['sql' => self::QUERY_SQL_TABLE_ROLES])]
    #[TestWith(['sql' => self::QUERY_SQL_TABLE_USERS])]
    public function query(string $sql): void
    {
        $this->assertInstanceOf(SQLite::class, $this->SQLite->query($sql));

        $this->assertSame($sql, $this->getPrivateProperty('sql'));
    }

    #[Testing]
    #[DataProvider('tableProvider')]
    public function table(bool $table, bool $withDatabase, string $return): void
    {
        $this->assertInstanceOf(SQLite::class, $this->SQLite->table($table, $withDatabase));
        $this->assertSame($return, $this->getQuery());
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('insertProvider')]
    public function insert(string $table, array $params, string $return): void
    {
        $this->SQLite->run(CONNECTIONS_SQLITE);

        $this->assertInstanceOf(SQLite::class, $this->SQLite->table($table, false)->insert($params));

        $rows = $this->getPrivateProperty('dataInfo');

        $this->assertArrayHasKey($this->actualCode, $rows);
        $this->assertSame(array_values($params), $rows[$this->actualCode]);
        $this->assertSame($return, $this->getQuery());
    }

    #[Testing]
    public function andIsBool(): void
    {
        $this->assertInstanceOf(SQLite::class, $this->SQLite->and());
        $this->assertSame('AND', $this->getQuery());
    }

    #[Testing]
    public function andIsString(): void
    {
        $this->assertInstanceOf(SQLite::class, $this->SQLite->and('idusers'));
        $this->assertSame('AND idusers', $this->getQuery());
    }

    #[Testing]
    public function andWithCallback(): void
    {
        $this->assertInstanceOf(SQLite::class, $this->SQLite->and(function (): void {
        }));

        $this->assertSame('AND', $this->getQuery());
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function isSchema(): void
    {
        $this->assertInstanceOf(SQLite::class, $this->SQLite->isSchema());
        $this->assertTrue($this->getPrivateProperty('isSchema'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[TestWith(['enableInsert' => true])]
    #[TestWith(['enableInsert' => false])]
    public function enableInsert(bool $enableInsert): void
    {
        $this->assertInstanceOf(SQLite::class, $this->SQLite->enableInsert($enableInsert));
        $this->assertSame($enableInsert, $this->getPrivateProperty('enableInsert'));
    }

    /**
     * @param array<int, string> $columns
     * @param array<int, array<int, string>> $rows
     *
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('bulkProvider')]
    public function bulk(bool $enable, string $table, array $columns, array $rows, string $return): void
    {
        $this->setPrivateProperty('isSchema', $enable);

        $this->SQLite
            ->run(CONNECTIONS_SQLITE)
            ->enableInsert($enable)
            ->table($table, false)
            ->bulk($columns, $rows);

        $this->assertInstanceOf(SQLite::class, $this->SQLite);

        /** @var array<string, string> $rowsDataInfo */
        $rowsDataInfo = $this->getPrivateProperty('dataInfo');

        $this->assertArrayHasKey($this->actualCode, $rowsDataInfo);
        $this->assertSame(array_merge(...$rows), $rowsDataInfo[$this->actualCode]);
        $this->assertSame($return, $this->getQuery());
    }

    #[Testing]
    #[DataProvider('deleteProvider')]
    public function delete(string $table, string $return): void
    {
        $this->SQLite->run(CONNECTIONS_SQLITE);

        $this->assertInstanceOf(SQLite::class, $this->SQLite->table($table, false)->delete());
        $this->assertSame($return, $this->getQuery());
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function equalToTest(): void
    {
        $this->assertInstanceOf(SQLite::class, $this->SQLite->equalTo('idusers', self::USERS_ID));

        /** @var array<string, mixed> $rows */
        $rows = $this->getPrivateProperty('dataInfo');

        $this->assertArrayHasKey($this->actualCode, $rows);
        $this->assertSame([self::USERS_ID], $rows[$this->actualCode]);
        $this->assertSame('idusers = ?', $this->getQuery());
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function greaterThanTest(): void
    {
        $this->assertInstanceOf(SQLite::class, $this->SQLite->greaterThan('idusers', self::USERS_ID));

        /** @var array<string, mixed> $rows */
        $rows = $this->getPrivateProperty('dataInfo');

        $this->assertArrayHasKey($this->actualCode, $rows);
        $this->assertSame([self::USERS_ID], $rows[$this->actualCode]);
        $this->assertSame('idusers > ?', $this->getQuery());
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function greaterThanOrEqualTo(): void
    {
        $this->assertInstanceOf(SQLite::class, $this->SQLite->greaterThanOrEqualTo('idusers', self::USERS_ID));

        /** @var array<string, mixed> $rows */
        $rows = $this->getPrivateProperty('dataInfo');

        $this->assertArrayHasKey($this->actualCode, $rows);
        $this->assertSame([self::USERS_ID], $rows[$this->actualCode]);
        $this->assertSame('idusers >= ?', $this->getQuery());
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function notEqualTo(): void
    {
        $this->assertInstanceOf(SQLite::class, $this->SQLite->notEqualTo('idusers', self::USERS_ID));

        /** @var array<string, mixed> $rows */
        $rows = $this->getPrivateProperty('dataInfo');

        $this->assertArrayHasKey($this->actualCode, $rows);
        $this->assertSame([self::USERS_ID], $rows[$this->actualCode]);
        $this->assertSame('idusers <> ?', $this->getQuery());
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function lessThanTest(): void
    {
        $this->assertInstanceOf(SQLite::class, $this->SQLite->lessThan('idusers', self::USERS_ID));

        /** @var array<string, mixed> $rows */
        $rows = $this->getPrivateProperty('dataInfo');

        $this->assertArrayHasKey($this->actualCode, $rows);
        $this->assertSame([self::USERS_ID], $rows[$this->actualCode]);
        $this->assertSame('idusers < ?', $this->getQuery());
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function lessThanOrEqualTo(): void
    {
        $this->assertInstanceOf(SQLite::class, $this->SQLite->lessThanOrEqualTo('idusers', self::USERS_ID));

        /** @var array<string, mixed> $rows */
        $rows = $this->getPrivateProperty('dataInfo');

        $this->assertArrayHasKey($this->actualCode, $rows);
        $this->assertSame([self::USERS_ID], $rows[$this->actualCode]);
        $this->assertSame('idusers <= ?', $this->getQuery());
    }

    #[Testing]
    public function orIsBool(): void
    {
        $this->assertInstanceOf(SQLite::class, $this->SQLite->or());
        $this->assertSame('OR', $this->getQuery());
    }

    #[Testing]
    public function orIsString(): void
    {
        $this->assertInstanceOf(SQLite::class, $this->SQLite->or('idusers'));
        $this->assertSame('OR idusers', $this->getQuery());
    }

    #[Testing]
    public function orWithCallback(): void
    {
        $this->assertInstanceOf(SQLite::class, $this->SQLite->or(function (): void {
        }));

        $this->assertSame('OR', $this->getQuery());
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('updateProvider')]
    public function update(string $table, array $params, string $return): void
    {
        $this->SQLite->run(CONNECTIONS_SQLITE);

        $this->assertInstanceOf(SQLite::class, $this->SQLite->table($table, false)->update($params));

        /** @var array<string, mixed> $rows */
        $rows = $this->getPrivateProperty('dataInfo');

        $this->assertSame(array_values($params), $rows[$this->actualCode]);
        $this->assertSame($return, $this->getQuery());
    }

    #[Testing]
    public function whereIsBool(): void
    {
        $this->assertInstanceOf(SQLite::class, $this->SQLite->where());
        $this->assertSame('WHERE', $this->getQuery());
    }

    #[Testing]
    public function whereIsString(): void
    {
        $this->assertInstanceOf(SQLite::class, $this->SQLite->where('idusers'));
        $this->assertSame('WHERE idusers', $this->getQuery());
    }

    #[Testing]
    public function whereWithCallback(): void
    {
        $this->assertInstanceOf(SQLite::class, $this->SQLite->where(function (): void {
        }));

        $this->assertSame('WHERE', $this->getQuery());
    }
}

<?php

declare(strict_types=1);

namespace Tests\Drivers\Schema;

use Lion\Database\Driver;
use Lion\Database\Drivers\MySQL as DriversMySQL;
use Lion\Database\Drivers\Schema\MySQL;
use Lion\Database\Interface\DatabaseConfigInterface;
use Lion\Database\Interface\RunDatabaseProcessesInterface;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Provider\MySQLSchemaProviderTrait;

class MySQLTest extends Test
{
    use MySQLSchemaProviderTrait;

    const DATABASE_TYPE = 'mysql';
    const DATABASE_HOST = 'mysql';
    const DATABASE_PORT = 3306;
    const DATABASE_NAME = 'lion_database';
    const DATABASE_USER = 'root';
    const DATABASE_PASSWORD = 'lion';
    const DATABASE_NAME_SECOND = 'lion_database_second';
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
        'connections' => [self::DATABASE_NAME => self::CONNECTION_DATA]
    ];

    private MySQL $mysql;

    protected function setUp(): void
    {
        Driver::run([
            'default' => self::DATABASE_NAME,
            'connections' => [
                self::DATABASE_NAME => self::CONNECTION_DATA,
                self::DATABASE_NAME_SECOND => self::CONNECTION_DATA_SECOND
            ]
        ]);

        $this->mysql = new MySQL();

        $this->initReflection($this->mysql);
    }

    protected function tearDown(): void
    {
        $this->setPrivateProperty('connections', self::CONNECTIONS);

        $this->setPrivateProperty('activeConnection', self::DATABASE_NAME);

        $this->setPrivateProperty('dbname', self::DATABASE_NAME);

        $this->setPrivateProperty('sql', '');

        $this->setPrivateProperty('table', '');

        $this->setPrivateProperty('view', '');

        $this->setPrivateProperty('dataInfo', []);

        $this->setPrivateProperty('isSchema', false);

        $this->setPrivateProperty('enableInsert', false);

        $this->setPrivateProperty('actualCode', '');

        $this->setPrivateProperty('fetchMode', []);

        $this->setPrivateProperty('message', 'Execution finished');

        $this->setPrivateProperty('actualColumn', '');

        $this->setPrivateProperty('columns', []);

        $this->setPrivateProperty('in', false);
    }

    private function assertQuery(string $queryStr): void
    {
        $query = $this->mysql->getQueryString();

        $this->assertObjectHasProperty('status', $query);
        $this->assertObjectHasProperty('message', $query);
        $this->assertObjectHasProperty('data', $query);
        $this->assertIsString($query->data->query);
        $this->assertSame($queryStr, $query->data->query);
    }

    private function assertIntances(object $obj): void
    {
        $this->assertInstanceOf(MySQL::class, $obj);
        $this->assertInstanceOf(DatabaseConfigInterface::class, $obj);
        $this->assertInstanceOf(RunDatabaseProcessesInterface::class, $obj);
    }

    private function assertResponse(object $response, string $message = 'Execution finished'): void
    {
        $this->assertIsObject($response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertSame('success', $response->status);
        $this->assertSame($message, $response->message);
    }

    // public function testExample(): void
    // {
    //     $response = $this->mysql
    //         ->run(self::CONNECTIONS)
    //         ->connection(self::DATABASE_NAME)
    //         ->createTable('example_tests', function () {
    //             $this->mysql
    //                 ->int('id')->notNull()->autoIncrement()->primaryKey()
    //                 ->int('num')->notNull()->comment('comment num');
    //         })
    //         ->execute();

    //     $this->assertTrue(true);
    // }

    public function testRun(): void
    {
        $this->assertIntances($this->mysql->run(self::CONNECTIONS));
        $this->assertSame(self::CONNECTIONS, $this->getPrivateProperty('connections'));
        $this->assertSame(self::DATABASE_NAME, $this->getPrivateProperty('activeConnection'));
        $this->assertSame(self::DATABASE_NAME, $this->getPrivateProperty('dbname'));
    }

    public function testConnection(): void
    {
        $this->mysql->addConnections(self::DATABASE_NAME_SECOND, self::CONNECTION_DATA_SECOND);

        $this->assertInstanceOf(MySQL::class, $this->mysql->connection(self::DATABASE_NAME_SECOND));
        $this->assertSame(self::DATABASE_NAME_SECOND, $this->getPrivateProperty('activeConnection'));
        $this->assertSame(self::DATABASE_NAME_SECOND, $this->getPrivateProperty('dbname'));
        $this->assertInstanceOf(MySQL::class, $this->mysql->connection(self::DATABASE_NAME));
        $this->assertSame(self::DATABASE_NAME, $this->getPrivateProperty('activeConnection'));
        $this->assertSame(self::DATABASE_NAME, $this->getPrivateProperty('dbname'));
    }

    #[DataProvider('createDatabaseProvider')]
    public function testCreateDatabase(string $database, string $query): void
    {
        $this->assertIntances($this->mysql->createDatabase($database));
        $this->assertQuery($query);
        $this->assertResponse($this->mysql->createDatabase($database)->execute());
    }

    #[DataProvider('dropDatabaseProvider')]
    public function testDropDatabase(string $database, string $query, array $connection): void
    {
        $this->assertResponse($this->mysql->connection(self::DATABASE_NAME)->createDatabase($database)->execute());

        $this->mysql->addConnections($database, $connection);

        $this->assertIntances($this->mysql->connection($database)->dropDatabase($database));
        $this->assertQuery($query);
        $this->assertResponse($this->mysql->connection($database)->dropDatabase($database)->execute());
    }

    #[DataProvider('createTableProvider')]
    public function testCreateTable(string $table, string $query): void
    {
        $this->assertIntances(
            $this->mysql
                ->connection(self::DATABASE_NAME)
                ->createTable($table, function () {
                    $this->mysql
                        ->int('id')->notNull()->autoIncrement()->primaryKey()
                        ->int('num')->notNull()->comment('comment num')
                        ->int('idroles')->notNull()->foreign('roles', 'idroles');
                })
        );

        $this->assertQuery($query);

        $this->assertResponse(
            $this->mysql
                ->connection(self::DATABASE_NAME)
                ->createTable($table, function () {
                    $this->mysql
                        ->int('id')->notNull()->autoIncrement()->primaryKey()
                        ->int('num')->notNull()->comment('comment num')
                        ->int('idroles')->notNull()->foreign('roles', 'idroles');
                })
                ->execute()
        );

        $this->assertResponse($this->mysql->dropTable($table)->execute());
    }

    #[DataProvider('dropTableProvider')]
    public function testDropTable(string $table, string $query): void
    {
        $this->assertResponse(
            $this->mysql
                ->connection(self::DATABASE_NAME)
                ->createTable($table, function () {
                    $this->mysql
                        ->int('id')->notNull()->autoIncrement()->primaryKey()
                        ->int('num')->notNull()->comment('comment num');
                })
                ->execute()
        );

        $this->assertIntances($this->mysql->connection(self::DATABASE_NAME)->dropTable($table));
        $this->assertQuery($query);
        $this->assertResponse($this->mysql->connection(self::DATABASE_NAME)->dropTable($table)->execute());
    }

    public function testDropTables(): void
    {
        $this->assertResponse(
            $this->mysql
                ->connection(self::DATABASE_NAME)
                ->createTable('roles_lion', function () {
                    $this->mysql
                        ->int('idroles')->notNull()->autoIncrement()->primaryKey()
                        ->int('description')->notNull()->comment('comment desc');
                })
                ->execute()
        );

        $this->assertResponse(
            $this->mysql
                ->connection(self::DATABASE_NAME)
                ->createTable('users_lion', function () {
                    $this->mysql
                        ->int('id')->notNull()->autoIncrement()->primaryKey()
                        ->int('idroles')->notNull()->foreign('roles_lion', 'idroles');
                })
                ->execute()
        );

        $driversMysql = (new DriversMySQL())->run(self::CONNECTIONS);

        foreach ($driversMysql->show()->tables()->getAll() as $table) {
            $this->assertContains($table->Tables_in_lion_database, ['users_lion', 'roles_lion']);
        }

        $this->assertResponse($this->mysql->dropTables()->execute());

        $readTables = $driversMysql->show()->tables()->getAll();

        $this->assertIsObject($readTables);
        $this->assertObjectHasProperty('status', $readTables);
        $this->assertObjectHasProperty('message', $readTables);
        $this->assertSame('success', $readTables->status);
        $this->assertSame('no data available', $readTables->message);
    }

    #[DataProvider('truncateTableProvider')]
    public function testTruncateTable(string $table, bool $enableForeignKeyChecks): void
    {
        $this->assertResponse(
            $this->mysql
                ->connection(self::DATABASE_NAME)
                ->createTable($table, function () {
                    $this->mysql
                        ->int('id')->notNull()->autoIncrement()->primaryKey()
                        ->int('num')->notNull()->comment('comment num');
                })
                ->execute()
        );

        $driversMysql = (new DriversMySQL())->run(self::CONNECTIONS);

        $this->assertResponse($driversMysql->table($table)->insert(['num' => 1])->execute());
        $this->assertCount(1, $driversMysql->table($table)->select()->getAll());
        $this->assertResponse($this->mysql->truncateTable($table, $enableForeignKeyChecks)->execute());
        $this->assertResponse($driversMysql->table($table)->select()->getAll(), 'no data available');
        $this->assertResponse($this->mysql->dropTable($table)->execute());
    }

    #[DataProvider('createStoreProcedureProvider')]
    public function testCreateStoreProcedure(string $table, string $storeProcedure): void
    {
        $this->assertResponse(
            $this->mysql
                ->connection(self::DATABASE_NAME)
                ->createTable($table, function () {
                    $this->mysql
                        ->int('id')->notNull()->autoIncrement()->primaryKey()
                        ->int('num')->notNull()->comment('comment num')
                        ->int('idroles')->null();
                })
                ->execute()
        );

        $this->assertResponse(
            $this->mysql
                ->connection(self::DATABASE_NAME)
                ->createStoreProcedure($storeProcedure, function () {
                    $this->mysql
                        ->in()->int('_num')
                        ->in()->int('_idroles');
                }, function (DriversMySQL $driversMysql) use ($table) {
                    $driversMysql
                        ->table($table)
                        ->insert([
                            'num' => '_num',
                            'idroles' => '_idroles'
                        ]);
                })
                ->execute()
        );

        $this->assertResponse(
            $this->mysql
                ->connection(self::DATABASE_NAME)
                ->createStoreProcedure("update_{$storeProcedure}", function () {
                    $this->mysql
                        ->in()->int('_num')
                        ->in()->int('_idroles');
                }, function (DriversMySQL $driversMysql) use ($table) {
                    $driversMysql
                        ->table($table)
                        ->update(['num' => '_num'])
                        ->where()
                        ->equalTo('idroles', '_idroles');
                })
                ->execute()
        );

        $driversMysql = (new DriversMySQL())->run(self::CONNECTIONS);

        $this->assertResponse($driversMysql->call($storeProcedure, [1, 1])->execute());
        $this->assertResponse($driversMysql->call("update_{$storeProcedure}", [1, 1])->execute());

        $this->assertResponse(
            $this->mysql->connection(self::DATABASE_NAME)->dropStoreProcedure($storeProcedure)->execute()
        );

        $this->assertResponse(
            $this->mysql->connection(self::DATABASE_NAME)->dropStoreProcedure("update_{$storeProcedure}")->execute()
        );

        $this->assertCount(1, $driversMysql->table($table)->select()->getAll());
        $this->assertResponse($this->mysql->dropTable($table)->execute());
    }

    #[DataProvider('dropStoreProcedureProvider')]
    public function testDropStoreProcedure(string $table, string $storeProcedure): void
    {
        $this->assertResponse(
            $this->mysql
                ->connection(self::DATABASE_NAME)
                ->createStoreProcedure($storeProcedure, function () {
                    $this->mysql
                        ->in()->int('_num')
                        ->in()->int('_idroles');
                }, function (DriversMySQL $driversMysql) use ($table) {
                    $driversMysql
                        ->table($table)
                        ->insert([
                            'num' => '_num',
                            'idroles' => '_idroles'
                        ]);
                })
                ->execute()
        );

        $this->assertResponse(
            $this->mysql->connection(self::DATABASE_NAME)->dropStoreProcedure($storeProcedure)->execute()
        );
    }

    #[DataProvider('createViewProvider')]
    public function testCreateView(string $parentTable, string $childTable, string $view): void
    {
        $this->assertResponse(
            $this->mysql
                ->connection(self::DATABASE_NAME)
                ->createTable($parentTable, function (): void {
                    $this->mysql
                        ->int('id')->notNull()->autoIncrement()->primaryKey()
                        ->varchar('description', 25)->null()->comment('roles description');
                })
                ->execute()
        );

        $this->assertResponse(
            $this->mysql
                ->connection(self::DATABASE_NAME)
                ->createTable($childTable, function (): void {
                    $this->mysql
                        ->int('id')->notNull()->autoIncrement()->primaryKey()
                        ->int('num')->notNull()->comment('comment num')
                        ->int('idroles')->null();
                })
                ->execute()
        );

        $this->assertResponse(
            $this->mysql
                ->connection(self::DATABASE_NAME)
                ->createView($view, function (DriversMySQL $driversMysql) use ($parentTable, $childTable): void {
                    $driversMysql
                        ->table($childTable)
                        ->select(
                            $driversMysql->getColumn('id', $childTable),
                            $driversMysql->getColumn('num', $childTable),
                            $driversMysql->getColumn('idroles', $childTable),
                            $driversMysql->getColumn('description', $parentTable)
                        )
                        ->inner()->join(
                            $parentTable,
                            $driversMysql->getColumn('idroles', $childTable),
                            $driversMysql->getColumn('id', $parentTable)
                        );
                })
                ->execute()
        );

        $driversMysql = (new DriversMySQL())->run(self::CONNECTIONS);

        $this->assertResponse(
            $driversMysql->table($parentTable)->insert(['description' => 'roles_description'])->execute()
        );

        $this->assertResponse($driversMysql->table($childTable)->insert(['num' => 1, 'idroles' => 1])->execute());
        $this->assertCount(1, $driversMysql->table($parentTable)->select()->getAll());
        $this->assertCount(1, $driversMysql->table($childTable)->select()->getAll());
        $this->assertCount(1, $driversMysql->view($view)->select()->getAll());
        $this->assertResponse($this->mysql->dropTable($parentTable)->execute());
        $this->assertResponse($this->mysql->dropTable($childTable)->execute());
        $this->assertResponse($this->mysql->dropView($view)->execute());
    }

    #[DataProvider('dropViewProvider')]
    public function testDropView(string $table, string $view): void
    {
        $this->assertResponse(
            $this->mysql
                ->connection(self::DATABASE_NAME)
                ->createTable($table, function (): void {
                    $this->mysql
                        ->int('id')->notNull()->autoIncrement()->primaryKey()
                        ->int('num')->notNull()->comment('comment num');
                })
                ->execute()
        );

        $this->assertResponse(
            $this->mysql
                ->connection(self::DATABASE_NAME)
                ->createView($view, function (DriversMySQL $driversMysql) use ($table): void {
                    $driversMysql
                        ->table($table)
                        ->select();
                })
                ->execute()
        );

        $driversMysql = (new DriversMySQL())->run(self::CONNECTIONS);

        $this->assertResponse($driversMysql->table($table)->insert(['num' => 1])->execute());
        $this->assertCount(1, $driversMysql->table($table)->select()->getAll());
        $this->assertCount(1, $driversMysql->view($view)->select()->getAll());
        $this->assertResponse($this->mysql->dropTable($table)->execute());
        $this->assertResponse($this->mysql->dropView($view)->execute());
    }

    #[DataProvider('primaryKeyProvider')]
    public function testPrimaryKey(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);
        $this->setPrivateProperty('actualColumn', $column);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertIntances($this->mysql->primaryKey());
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('autoIncrementProvider')]
    public function testAutoIncrement(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);
        $this->setPrivateProperty('actualColumn', $column);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertIntances($this->mysql->autoIncrement());
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('notNullProvider')]
    public function testNotNull(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);
        $this->setPrivateProperty('actualColumn', $column);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertIntances($this->mysql->notNull());
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('nullProvider')]
    public function testNull(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);
        $this->setPrivateProperty('actualColumn', $column);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertIntances($this->mysql->null());
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('commentProvider')]
    public function testComment(string $table, string $column, string $comment, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);
        $this->setPrivateProperty('actualColumn', $column);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertIntances($this->mysql->comment($comment));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('uniqueProvider')]
    public function testUnique(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);
        $this->setPrivateProperty('actualColumn', $column);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertIntances($this->mysql->unique());
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('defaultProvider')]
    public function testDefault(string $table, string $column, mixed $default, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);
        $this->setPrivateProperty('actualColumn', $column);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertIntances($this->mysql->default($default));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('foreignProvider')]
    public function testForeign(string $table, string $column, array $relation, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);
        $this->setPrivateProperty('actualColumn', $column);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertIntances($this->mysql->foreign($relation['table'], $relation['column']));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('intProvider')]
    public function testInt(string $table, string $column, ?int $length, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->int($column, $length));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('bigIntProvider')]
    public function testBigInt(string $table, string $column, ?int $length, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->bigInt($column, $length));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('decimalProvider')]
    public function testDecimal(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->decimal($column));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('doubleProvider')]
    public function testDouble(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->double($column));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('floatProvider')]
    public function testFloat(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->float($column));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('mediumIntProvider')]
    public function testMediumInt(string $table, string $column, int $length, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->mediumInt($column, $length));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('realProvider')]
    public function testReal(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->real($column));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('smallIntProvider')]
    public function testSmallInt(string $table, string $column, int $length, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->smallInt($column, $length));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('tinyIntProvider')]
    public function testTinyInt(string $table, string $column, int $length, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->tinyInt($column, $length));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('blobProvider')]
    public function testBlob(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->blob($column));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('varBinaryProvider')]
    public function testVarBinary(string $table, string $column, string|int $length, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->varBinary($column, $length));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('charProvider')]
    public function testChar(string $table, string $column, int $length, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->char($column, $length));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('jsonProvider')]
    public function testJson(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->json($column));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('ncharProvider')]
    public function testNchar(string $table, string $column, int $length, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->nchar($column, $length));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('nvarcharProvider')]
    public function testNvarchar(string $table, string $column, int $length, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->nvarchar($column, $length));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('varcharProvider')]
    public function testVarchar(string $table, string $column, int $length, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->varchar($column, $length));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('longTextProvider')]
    public function testLongText(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->longText($column));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('mediumTextProvider')]
    public function testMediumText(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->mediumText($column));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('textProvider')]
    public function testText(string $table, string $column, int $length, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->text($column, $length));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('tinyTextProvider')]
    public function testTinyText(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->tinyText($column));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('enumProvider')]
    public function testEnum(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->enum($column, [1, 2, 3]));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('dateProvider')]
    public function testDate(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->date($column));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('timeProvider')]
    public function testTime(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->time($column));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('timeStampProvider')]
    public function testTimeStamp(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->timeStamp($column));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[DataProvider('dateTimeProvider')]
    public function testDateTime(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->dateTime($column));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }
}

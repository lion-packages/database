<?php

declare(strict_types=1);

namespace Tests\Drivers\Schema;

use Lion\Database\Driver;
use Lion\Database\Drivers\MySQL as DriversMySQL;
use Lion\Database\Drivers\Schema\MySQL;
use Lion\Database\Helpers\Constants\MySQLConstants;
use Lion\Database\Interface\DatabaseConfigInterface;
use Lion\Database\Interface\ExecuteInterface;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use stdClass;
use Tests\Provider\MySQLSchemaProviderTrait;

class MySQLTest extends Test
{
    use MySQLSchemaProviderTrait;

    private MySQL $mysql;

    /**
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        Driver::run([
            'default' => DATABASE_NAME_MYSQL,
            'connections' => [
                DATABASE_NAME_MYSQL => CONNECTION_DATA_MYSQL,
                DATABASE_NAME_SECOND_MYSQL => CONNECTION_DATA_SECOND_MYSQL,
            ],
        ]);

        $this->mysql = new MySQL();

        $this->initReflection($this->mysql);
    }

    /**
     * @throws ReflectionException
     */
    protected function tearDown(): void
    {
        $this->setPrivateProperty('connections', CONNECTIONS_MYSQL);

        $this->setPrivateProperty('activeConnection', DATABASE_NAME_MYSQL);

        $this->setPrivateProperty('dbname', DATABASE_NAME_MYSQL);

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

        $this->setPrivateProperty('databaseInstances', []);
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
        $this->assertInstanceOf(ExecuteInterface::class, $obj);
    }

    private function assertResponse(object $response, string $message = 'Execution finished'): void
    {
        $this->assertIsObject($response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertSame('success', $response->status);
        $this->assertSame($message, $response->message);
    }

    /**
     * @throws ReflectionException
     */
    public function testRun(): void
    {
        $this->assertIntances($this->mysql->run(CONNECTIONS_MYSQL));
        $this->assertSame(CONNECTIONS_MYSQL, $this->getPrivateProperty('connections'));
        $this->assertSame(DATABASE_NAME_MYSQL, $this->getPrivateProperty('activeConnection'));
        $this->assertSame(DATABASE_NAME_MYSQL, $this->getPrivateProperty('dbname'));
    }

    /**
     * @throws ReflectionException
     */
    public function testConnection(): void
    {
        $this->mysql->addConnection(DATABASE_NAME_SECOND_MYSQL, CONNECTION_DATA_SECOND_MYSQL);

        $this->assertInstanceOf(MySQL::class, $this->mysql->connection(DATABASE_NAME_SECOND_MYSQL));
        $this->assertSame(DATABASE_NAME_SECOND_MYSQL, $this->getPrivateProperty('activeConnection'));
        $this->assertSame(DATABASE_NAME_SECOND_MYSQL, $this->getPrivateProperty('dbname'));
        $this->assertInstanceOf(MySQL::class, $this->mysql->connection(DATABASE_NAME_MYSQL));
        $this->assertSame(DATABASE_NAME_MYSQL, $this->getPrivateProperty('activeConnection'));
        $this->assertSame(DATABASE_NAME_MYSQL, $this->getPrivateProperty('dbname'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('getQueryStringProvider')]
    public function getQueryString(string $query): void
    {
        $this->setPrivateProperty('sql', $query);

        $response = $this->mysql->getQueryString();

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
        $this->assertResponse($this->mysql->connection(DATABASE_NAME_MYSQL)->createDatabase($database)->execute());

        $this->mysql->addConnection($database, $connection);

        $this->assertIntances($this->mysql->connection($database)->dropDatabase($database));
        $this->assertQuery($query);
        $this->assertResponse($this->mysql->connection($database)->dropDatabase($database)->execute());
    }

    #[DataProvider('createTableProvider')]
    public function testCreateTable(string $table, string $query): void
    {
        $this->assertIntances(
            $this->mysql
                ->connection(DATABASE_NAME_MYSQL)
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
                ->connection(DATABASE_NAME_MYSQL)
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
                ->connection(DATABASE_NAME_MYSQL)
                ->createTable($table, function () {
                    $this->mysql
                        ->int('id')->notNull()->autoIncrement()->primaryKey()
                        ->int('num')->notNull()->comment('comment num');
                })
                ->execute()
        );

        $this->assertIntances($this->mysql->connection(DATABASE_NAME_MYSQL)->dropTable($table));
        $this->assertQuery($query);
        $this->assertResponse($this->mysql->connection(DATABASE_NAME_MYSQL)->dropTable($table)->execute());
    }

    public function testDropTables(): void
    {
        $this->assertResponse(
            $this->mysql
                ->connection(DATABASE_NAME_MYSQL)
                ->createTable('roles_lion', function () {
                    $this->mysql
                        ->int('idroles')->notNull()->autoIncrement()->primaryKey()
                        ->int('description')->notNull()->comment('comment desc');
                })
                ->execute()
        );

        $this->assertResponse(
            $this->mysql
                ->connection(DATABASE_NAME_MYSQL)
                ->createTable('users_lion', function () {
                    $this->mysql
                        ->int('id')->notNull()->autoIncrement()->primaryKey()
                        ->int('idroles')->notNull()->foreign('roles_lion', 'idroles');
                })
                ->execute()
        );

        $driversMysql = new DriversMySQL()
            ->run(CONNECTIONS_MYSQL);

        foreach ($driversMysql->show()->tables()->getAll() as $table) {
            $this->assertContains($table->Tables_in_lion_database, ['users_lion', 'roles_lion']);
        }

        $this->assertResponse($this->mysql->dropTables()->execute());

        $readTables = $driversMysql
            ->show()
            ->tables()
            ->getAll();

        $this->assertIsArray($readTables);
        $this->assertEmpty($readTables);
    }

    #[DataProvider('truncateTableProvider')]
    public function testTruncateTable(string $table, bool $enableForeignKeyChecks): void
    {
        $this->assertResponse(
            $this->mysql
                ->connection(DATABASE_NAME_MYSQL)
                ->createTable($table, function () {
                    $this->mysql
                        ->int('id')->notNull()->autoIncrement()->primaryKey()
                        ->int('num')->notNull()->comment('comment num');
                })
                ->execute()
        );

        $driversMysql = new DriversMySQL()->run(CONNECTIONS_MYSQL);

        $this->assertResponse($driversMysql->table($table)->insert(['num' => 1])->execute());
        $this->assertCount(1, $driversMysql->table($table)->select()->getAll());
        $this->assertResponse($this->mysql->truncateTable($table, $enableForeignKeyChecks)->execute());

        $response = $driversMysql->table($table)->select()->getAll();

        $this->assertIsArray($response);
        $this->assertEmpty($response);

        $this->assertResponse($this->mysql->dropTable($table)->execute());
    }

    #[DataProvider('createStoreProcedureProvider')]
    public function testCreateStoreProcedure(string $table, string $storeProcedure): void
    {
        $this->assertResponse(
            $this->mysql
                ->connection(DATABASE_NAME_MYSQL)
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
                ->connection(DATABASE_NAME_MYSQL)
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
                ->connection(DATABASE_NAME_MYSQL)
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

        $driversMysql = new DriversMySQL()->run(CONNECTIONS_MYSQL);

        $this->assertResponse($driversMysql->call($storeProcedure, [1, 1])->execute());
        $this->assertResponse($driversMysql->call("update_{$storeProcedure}", [1, 1])->execute());

        $this->assertResponse(
            $this->mysql->connection(DATABASE_NAME_MYSQL)->dropStoreProcedure($storeProcedure)->execute()
        );

        $this->assertResponse(
            $this->mysql->connection(DATABASE_NAME_MYSQL)->dropStoreProcedure("update_{$storeProcedure}")->execute()
        );

        $this->assertCount(1, $driversMysql->table($table)->select()->getAll());
        $this->assertResponse($this->mysql->dropTable($table)->execute());
    }

    #[DataProvider('dropStoreProcedureProvider')]
    public function testDropStoreProcedure(string $table, string $storeProcedure): void
    {
        $this->assertResponse(
            $this->mysql
                ->connection(DATABASE_NAME_MYSQL)
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
            $this->mysql->connection(DATABASE_NAME_MYSQL)->dropStoreProcedure($storeProcedure)->execute()
        );
    }

    #[DataProvider('createViewProvider')]
    public function testCreateView(string $parentTable, string $childTable, string $view): void
    {
        $this->assertResponse(
            $this->mysql
                ->connection(DATABASE_NAME_MYSQL)
                ->createTable($parentTable, function (): void {
                    $this->mysql
                        ->int('id')->notNull()->autoIncrement()->primaryKey()
                        ->varchar('description', 25)->null()->comment('roles description');
                })
                ->execute()
        );

        $this->assertResponse(
            $this->mysql
                ->connection(DATABASE_NAME_MYSQL)
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
                ->connection(DATABASE_NAME_MYSQL)
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

        $driversMysql = new DriversMySQL()->run(CONNECTIONS_MYSQL);

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
                ->connection(DATABASE_NAME_MYSQL)
                ->createTable($table, function (): void {
                    $this->mysql
                        ->int('id')->notNull()->autoIncrement()->primaryKey()
                        ->int('num')->notNull()->comment('comment num');
                })
                ->execute()
        );

        $this->assertResponse(
            $this->mysql
                ->connection(DATABASE_NAME_MYSQL)
                ->createView($view, function (DriversMySQL $driversMysql) use ($table): void {
                    $driversMysql
                        ->table($table)
                        ->select();
                })
                ->execute()
        );

        $driversMysql = new DriversMySQL()->run(CONNECTIONS_MYSQL);

        $this->assertResponse($driversMysql->table($table)->insert(['num' => 1])->execute());
        $this->assertCount(1, $driversMysql->table($table)->select()->getAll());
        $this->assertCount(1, $driversMysql->view($view)->select()->getAll());
        $this->assertResponse($this->mysql->dropTable($table)->execute());
        $this->assertResponse($this->mysql->dropView($view)->execute());
    }

    /**
     * @throws ReflectionException
     */
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

    /**
     * @throws ReflectionException
     */
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

    /**
     * @throws ReflectionException
     */
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

    /**
     * @throws ReflectionException
     */
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

    /**
     * @throws ReflectionException
     */
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

    /**
     * @throws ReflectionException
     */
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

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('defaultProvider')]
    public function defaultTest(string $table, string $column, mixed $default, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->setPrivateProperty('actualColumn', $column);

        $this->setPrivateProperty('columns', [
            $table => [
                $column => [
                    'default' => false,
                ],
            ],
        ]);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertIntances($this->mysql->default($default));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    /**
     * @throws ReflectionException
     */
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

    /**
     * @throws ReflectionException
     */
    #[DataProvider('intProvider')]
    public function testInt(string $table, string $column, ?int $length, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->int($column, $length));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    /**
     * @throws ReflectionException
     */
    #[DataProvider('bigIntProvider')]
    public function testBigInt(string $table, string $column, ?int $length, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->bigInt($column, $length));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('decimalProvider')]
    public function decimal(string $table, string $column, ?int $digits, ?int $bytes, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->decimal($column, $digits, $bytes));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    /**
     * @throws ReflectionException
     */
    #[DataProvider('doubleProvider')]
    public function testDouble(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->double($column));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    /**
     * @throws ReflectionException
     */
    #[DataProvider('floatProvider')]
    public function testFloat(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->float($column));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    /**
     * @throws ReflectionException
     */
    #[DataProvider('mediumIntProvider')]
    public function testMediumInt(string $table, string $column, int $length, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->mediumInt($column, $length));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    /**
     * @throws ReflectionException
     */
    #[DataProvider('realProvider')]
    public function testReal(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->real($column));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    /**
     * @throws ReflectionException
     */
    #[DataProvider('smallIntProvider')]
    public function testSmallInt(string $table, string $column, int $length, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->smallInt($column, $length));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    /**
     * @throws ReflectionException
     */
    #[DataProvider('tinyIntProvider')]
    public function testTinyInt(string $table, string $column, int $length, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->tinyInt($column, $length));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    /**
     * @throws ReflectionException
     */
    #[DataProvider('blobProvider')]
    public function testBlob(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->blob($column));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    /**
     * @throws ReflectionException
     */
    #[DataProvider('varBinaryProvider')]
    public function testVarBinary(string $table, string $column, string|int $length, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->varBinary($column, $length));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    /**
     * @throws ReflectionException
     */
    #[DataProvider('charProvider')]
    public function testChar(string $table, string $column, int $length, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->char($column, $length));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    /**
     * @throws ReflectionException
     */
    #[DataProvider('jsonProvider')]
    public function testJson(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->json($column));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    /**
     * @throws ReflectionException
     */
    #[DataProvider('ncharProvider')]
    public function testNchar(string $table, string $column, int $length, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->nchar($column, $length));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    /**
     * @throws ReflectionException
     */
    #[DataProvider('nvarcharProvider')]
    public function testNvarchar(string $table, string $column, int $length, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->nvarchar($column, $length));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    /**
     * @throws ReflectionException
     */
    #[DataProvider('varcharProvider')]
    public function testVarchar(string $table, string $column, int $length, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->varchar($column, $length));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    /**
     * @throws ReflectionException
     */
    #[DataProvider('longTextProvider')]
    public function testLongText(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->longText($column));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    /**
     * @throws ReflectionException
     */
    #[DataProvider('mediumTextProvider')]
    public function testMediumText(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->mediumText($column));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('textProvider')]
    public function text(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->text($column));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    /**
     * @throws ReflectionException
     */
    #[DataProvider('tinyTextProvider')]
    public function testTinyText(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->tinyText($column));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    /**
     * @throws ReflectionException
     */
    #[DataProvider('enumProvider')]
    public function testEnum(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->enum($column, [1, 2, 3]));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    /**
     * @throws ReflectionException
     */
    #[DataProvider('dateProvider')]
    public function testDate(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->date($column));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    /**
     * @throws ReflectionException
     */
    #[DataProvider('timeProvider')]
    public function testTime(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->time($column));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    /**
     * @throws ReflectionException
     */
    #[DataProvider('timeStampProvider')]
    public function testTimeStamp(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->timeStamp($column));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    /**
     * @throws ReflectionException
     */
    #[DataProvider('dateTimeProvider')]
    public function testDateTime(string $table, string $column, array $configColumn): void
    {
        $this->setPrivateProperty('table', $table);

        $this->assertSame($table, $this->getPrivateProperty('table'));
        $this->assertIntances($this->mysql->dateTime($column));
        $this->assertSame($column, $this->getPrivateProperty('actualColumn'));
        $this->assertSame($configColumn, $this->getPrivateProperty('columns'));
    }

    #[Testing]
    public function onUpdate(): void
    {
        $this->assertSame(' ON UPDATE CURRENT_TIMESTAMP', $this->mysql->onUpdate(MySQLConstants::CURRENT_TIMESTAMP));
    }
}

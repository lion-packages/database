<?php

declare(strict_types=1);

namespace Tests\Drivers;

use InvalidArgumentException;
use Lion\Database\Driver;
use Lion\Database\Drivers\MySQL;
use Lion\Database\Helpers\Constants\MySQLConstants;
use Lion\Test\Test;
use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test as Testing;
use PHPUnit\Framework\Attributes\TestWith;
use ReflectionException;
use stdClass;
use Tests\Provider\MySQLProviderTrait;

class MySQLTest extends Test
{
    use MySQLProviderTrait;

    private MySQL $mysql;
    private string $actualCode;

    /**
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $this->mysql = new MySQL();

        $this->actualCode = uniqid();

        $this->initReflection($this->mysql);

        $this->setActualCode();
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

    /**
     * @throws ReflectionException
     */
    private function setActualCode(): void
    {
        $this->setPrivateProperty('actualCode', $this->actualCode);

        $this->assertSame($this->actualCode, $this->getPrivateProperty('actualCode'));
    }

    private function getQuery(): string
    {
        $query = $this->mysql->getQueryString();

        $this->assertObjectHasProperty('status', $query);
        $this->assertObjectHasProperty('message', $query);
        $this->assertObjectHasProperty('data', $query);
        $this->assertIsString($query->data->query);

        return $query->data->query;
    }

    /**
     * @throws ReflectionException
     */
    private function assertMessage(string $message): void
    {
        $this->assertSame($message, $this->getPrivateProperty('message'));
    }

    /**
     * @throws ReflectionException
     */
    private function assertAddRows(mixed $assertValue): void
    {
        $rows = $this->getPrivateProperty('dataInfo');

        $this->assertArrayHasKey($this->actualCode, $rows);
        $this->assertSame($assertValue, $rows[$this->actualCode]);
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

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function runInterface(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->run(CONNECTIONS_MYSQL));

        $connections = $this->getPrivateProperty('connections');

        $this->assertSame(CONNECTIONS_MYSQL, $connections);
        $this->assertSame(DATABASE_NAME_MYSQL, $this->getPrivateProperty('activeConnection'));

        $this->assertSame(
            CONNECTIONS_MYSQL['connections'][DATABASE_NAME_MYSQL]['dbname'],
            $this->getPrivateProperty('dbname')
        );
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function runInterfaceConnectionIsEmpty(): void
    {
        $configConnections = [
            'default' => DATABASE_NAME_MYSQL,
            'connections' => [
                DATABASE_NAME_MYSQL => [
                    'type' => Driver::MYSQL,
                    'host' => DATABASE_HOST_MYSQL,
                    'port' => DATABASE_PORT_MYSQL,
                    'user' => DATABASE_USER_MYSQL,
                    'password' => DATABASE_PASSWORD_MYSQL,
                ],
            ],
        ];

        $this->mysql->run($configConnections);

        $connections = $this->getPrivateProperty('connections');

        $this->assertSame($configConnections, $connections);
        $this->assertSame(DATABASE_NAME_MYSQL, $this->getPrivateProperty('activeConnection'));
        $this->assertSame('', $this->getPrivateProperty('dbname'));
    }

    #[Testing]
    public function runInterfaceWithoutDefaultValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('No default database defined');

        $this->mysql->run([]);
    }

    #[Testing]
    public function runInterfaceWithoutConnections(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('No databases have been defined');

        $this->mysql->run([
            'default' => DATABASE_NAME_MYSQL,
        ]);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function connectionInterface(): void
    {
        $this->mysql
            ->run(CONNECTIONS_MYSQL)
            ->addConnection(DATABASE_NAME_SECOND_MYSQL, CONNECTION_DATA_SECOND_MYSQL);

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
    public function connectionInterfaceConnectionIsEmpty(): void
    {
        $this->mysql
            ->run([
                'default' => DATABASE_NAME_MYSQL,
                'connections' => [
                    DATABASE_NAME_MYSQL => [
                        'type' => Driver::MYSQL,
                        'host' => DATABASE_HOST_MYSQL,
                        'port' => DATABASE_PORT_MYSQL,
                        'user' => DATABASE_USER_MYSQL,
                        'password' => DATABASE_PASSWORD_MYSQL,
                    ],
                ],
            ]);

        $this->assertInstanceOf(MySQL::class, $this->mysql->connection(DATABASE_NAME_MYSQL));
        $this->assertSame(DATABASE_NAME_MYSQL, $this->getPrivateProperty('activeConnection'));
        $this->assertSame('', $this->getPrivateProperty('dbname'));
    }

    #[Testing]
    #[TestWith(['connection' => DATABASE_NAME_MYSQL])]
    #[TestWith(['connection' => DATABASE_NAME_SECOND_MYSQL])]
    public function connectionInterfaceConnectionDoesNotExist(string $connection): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionCode(500);
        $this->expectExceptionMessage('The selected connection does not exist');

        $this->mysql->connection($connection);
    }

    #[Testing]
    public function execute(): void
    {
        $createTableResponse = $this->mysql
            ->run(CONNECTIONS_MYSQL)
            ->isSchema()
            ->enableInsert(true)
            ->use(DATABASE_NAME_MYSQL)->closeQuery()
            ->drop()->table()->ifExists('roles')->closeQuery()
            ->create()->table()->addQuery('roles')
            ->groupQuery(function () {
                $this->mysql
                    ->int('idroles')->notNull()->autoIncrement()->closeQuery(',')
                    ->varchar('roles_name', 25)->notNull()->comment('roles name')->closeQuery(',')
                    ->primaryKey('idroles');
            })
            ->engine('INNODB')
            ->default()->character()->set(MySQLConstants::UTF8MB4)
            ->collate(MySQLConstants::UTF8MB4_SPANISH_CI)
            ->closeQuery()
            ->execute();

        $this->assertIsObject($createTableResponse);
        $this->assertObjectHasProperty('status', $createTableResponse);
        $this->assertObjectHasProperty('message', $createTableResponse);
        $this->assertSame('success', $createTableResponse->status);
        $this->assertSame('Execution finished', $createTableResponse->message);
    }

    #[Testing]
    public function executeInsert(): void
    {
        $this->mysql
            ->run(CONNECTIONS_MYSQL)
            ->isSchema()
            ->enableInsert(true)
            ->use(DATABASE_NAME_MYSQL)->closeQuery()
            ->drop()->table()->ifExists('roles')->closeQuery()
            ->create()->table()->addQuery('roles')
            ->groupQuery(function () {
                $this->mysql
                    ->int('idroles')->notNull()->autoIncrement()->closeQuery(',')
                    ->varchar('roles_name', 25)->notNull()->comment('roles name')->closeQuery(',')
                    ->primaryKey('idroles');
            })
            ->engine('INNODB')
            ->default()->character()->set(MySQLConstants::UTF8MB4)
            ->collate(MySQLConstants::UTF8MB4_SPANISH_CI)
            ->closeQuery()
            ->execute();

        $response = $this->mysql
            ->table('roles')
            ->insert([
                'roles_name' => ADMINISTRATOR_MYSQL,
            ])
            ->execute();

        $this->assertInstanceOf(stdClass::class, $response);
        $this->assertObjectHasProperty('code', $response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertSame(200, $response->code);
        $this->assertSame('success', $response->status);
        $this->assertSame('Execution finished', $response->message);
    }

    #[Testing]
    public function executeRowCount(): void
    {
        $createTableResponse = $this->mysql
            ->run(CONNECTIONS_MYSQL)
            ->isSchema()
            ->enableInsert(true)
            ->use(DATABASE_NAME_MYSQL)->closeQuery()
            ->drop()->table()->ifExists('roles')->closeQuery()
            ->create()->table()->addQuery('roles')
            ->groupQuery(function (): void {
                $this->mysql
                    ->int('idroles')->notNull()->autoIncrement()->closeQuery(',')
                    ->varchar('roles_name', 25)->notNull()->comment('roles name')->closeQuery(',')
                    ->primaryKey('idroles');
            })
            ->engine('INNODB')
            ->default()->character()->set(MySQLConstants::UTF8MB4)
            ->collate(MySQLConstants::UTF8MB4_SPANISH_CI)
            ->closeQuery()
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

        $insertResponse = $this->mysql
            ->run(CONNECTIONS_MYSQL)
            ->table('roles')
            ->insert([
                'roles_name' => 'Role test'
            ])
            ->rowCount()
            ->execute();

        $this->assertIsInt($insertResponse);
        $this->assertSame(1, $insertResponse);

        $dropTableResponse = $this->mysql
            ->run(CONNECTIONS_MYSQL)
            ->query(
                <<<SQL
                DROP TABLE IF EXISTS roles;
                SQL
            )
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
    }

    #[Testing]
    public function get(): void
    {
        $this->mysql
            ->run(CONNECTIONS_MYSQL)->isSchema()->enableInsert(true)
            ->use(DATABASE_NAME_MYSQL)->closeQuery()
            ->drop()->table()->ifExists('roles')->closeQuery()
            ->create()->table()->addQuery('roles')
            ->groupQuery(function (): void {
                $this->mysql
                    ->int('idroles')->notNull()->autoIncrement()->closeQuery(',')
                    ->varchar('roles_name', 25)->notNull()->comment('roles name')->closeQuery(',')
                    ->primaryKey('idroles');
            })
            ->engine('INNODB')
            ->default()->character()->set(MySQLConstants::UTF8MB4)
            ->collate(MySQLConstants::UTF8MB4_SPANISH_CI)
            ->closeQuery()
            ->execute();

        $this->mysql->table('roles')->insert(['roles_name' => ADMINISTRATOR_MYSQL])->execute();

        $validateRowResponse = $this->mysql
            ->table('roles')
            ->select('roles_name')
            ->where()
            ->equalTo('roles_name', ADMINISTRATOR_MYSQL)
            ->get();

        $this->assertIsObject($validateRowResponse);
        $this->assertInstanceOf(stdClass::class, $validateRowResponse);
        $this->assertObjectHasProperty('roles_name', $validateRowResponse);
        $this->assertSame(ADMINISTRATOR_MYSQL, $validateRowResponse->roles_name);
    }

    #[Testing]
    public function getAll(): void
    {
        $this->mysql
            ->run(CONNECTIONS_MYSQL)->isSchema()->enableInsert(true)
            ->use(DATABASE_NAME_MYSQL)->closeQuery()
            ->drop()->table()->ifExists('roles')->closeQuery()
            ->create()->table()->addQuery('roles')
            ->groupQuery(function () {
                $this->mysql
                    ->int('idroles')->notNull()->autoIncrement()->closeQuery(',')
                    ->varchar('roles_name', 25)->notNull()->comment('roles name')->closeQuery(',')
                    ->primaryKey('idroles');
            })
            ->engine('INNODB')
            ->default()->character()->set(MySQLConstants::UTF8MB4)
            ->collate(MySQLConstants::UTF8MB4_SPANISH_CI)
            ->closeQuery()
            ->execute();

        $this->mysql->table('roles')->bulk(['roles_name'], [
            [ADMINISTRATOR_MYSQL],
            [ADMINISTRATOR_MYSQL . '-2']
        ])->execute();

        $validateRowResponse = $this->mysql->table('roles')->select('roles_name')->getAll();

        $this->assertIsArray($validateRowResponse);

        foreach ($validateRowResponse as $row) {
            $this->assertObjectHasProperty('roles_name', $row);
            $this->assertContains($row->roles_name, [ADMINISTRATOR_MYSQL, (ADMINISTRATOR_MYSQL . '-2')]);
        }
    }

    #[DataProvider('transactionProvider')]
    public function testTransaction(bool $isTransaction): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->transaction($isTransaction));
        $this->assertSame($isTransaction, $this->getPrivateProperty('isTransaction'));
    }

    public function testIsSchema(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->isSchema());
        $this->assertTrue($this->getPrivateProperty('isSchema'));
    }

    #[DataProvider('enableInsertProvider')]
    public function testEnableInsert(bool $enable): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->enableInsert($enable));
        $this->assertSame($enable, $this->getPrivateProperty('enableInsert'));
    }

    public function testDatabase(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->database());
        $this->assertSame('DATABASE', $this->getQuery());
    }

    public function testTruncate(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->truncate());
        $this->assertSame('TRUNCATE', $this->getQuery());
    }

    public function testAutoIncrement(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->autoIncrement());
        $this->assertSame('AUTO_INCREMENT', $this->getQuery());
    }

    public function testAction(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->action());
        $this->assertSame('ACTION', $this->getQuery());
    }

    public function testNo(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->no());
        $this->assertSame('NO', $this->getQuery());
    }

    public function testCascade(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->cascade());
        $this->assertSame('CASCADE', $this->getQuery());
    }

    public function testRestrict(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->restrict());
        $this->assertSame('RESTRICT', $this->getQuery());
    }

    public function testOnDelete(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->onDelete());
        $this->assertSame('ON DELETE', $this->getQuery());
    }

    #[Testing]
    public function onUpdateIsNull(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->onUpdate());
        $this->assertSame('ON UPDATE', $this->getQuery());
    }

    #[Testing]
    public function onUpdateIsString(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->onUpdate(MySQLConstants::CURRENT_TIMESTAMP));
        $this->assertSame('ON UPDATE CURRENT_TIMESTAMP', $this->getQuery());
    }

    public function testOn(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->on());
        $this->assertSame('ON', $this->getQuery());
    }

    public function testReferences(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->references());
        $this->assertSame('REFERENCES', $this->getQuery());
    }

    public function testForeign(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->foreign());
        $this->assertSame('FOREIGN', $this->getQuery());
    }

    public function testConstraint(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->constraint());
        $this->assertSame('CONSTRAINT', $this->getQuery());
    }

    public function testAdd(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->add());
        $this->assertSame('ADD', $this->getQuery());
    }

    public function testAlter(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->alter());
        $this->assertSame('ALTER', $this->getQuery());
    }

    #[DataProvider('commentProvider')]
    public function testComment(string $value, string $return): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->comment($value));
        $this->assertSame($return, $this->getQuery());
    }

    public function testUnique(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->unique());
        $this->assertSame('UNIQUE', $this->getQuery());
    }

    public function testPrimaryKey(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->primaryKey('idusers'));
        $this->assertSame('PRIMARY KEY (idusers)', $this->getQuery());
    }

    public function testKey(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->key());
        $this->assertSame('KEY', $this->getQuery());
    }

    public function testPrimary(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->primary());
        $this->assertSame('PRIMARY', $this->getQuery());
    }

    #[DataProvider('engineProvider')]
    public function testEngine(string $value, string $return): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->engine($value));
        $this->assertSame($return, $this->getQuery());
    }

    public function testNotNull(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->notNull());
        $this->assertSame('NOT NULL', $this->getQuery());
    }

    public function testNull(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->null());
        $this->assertSame('NULL', $this->getQuery());
    }

    public function testInnoDB(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->innoDB());
        $this->assertSame('INNODB', $this->getQuery());
    }

    #[DataProvider('collateProvider')]
    public function testCollate(string $value, string $return): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->collate($value));
        $this->assertSame($return, $this->getQuery());
    }

    #[DataProvider('setProvider')]
    public function testSet(string $value, string $return): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->set($value));
        $this->assertSame($return, $this->getQuery());
    }

    public function testCharacter(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->character());
        $this->assertSame('CHARACTER', $this->getQuery());
    }

    #[DataProvider('defaultProvider')]
    public function testDefault(string|int $value, string $return): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->default($value));
        $this->assertSame($return, $this->getQuery());
    }

    public function testSchema(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->schema());
        $this->assertSame('SCHEMA', $this->getQuery());
    }

    public function testAddQuery(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->addQuery(DATABASE_NAME_MYSQL));
        $this->assertSame(DATABASE_NAME_MYSQL, $this->getQuery());
    }

    public function testIfExist(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->ifExists(DATABASE_NAME_MYSQL));
        $this->assertSame('IF EXISTS `lion_database`', $this->getQuery());
    }

    public function testIfNotExist(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->ifNotExists(DATABASE_NAME_MYSQL));
        $this->assertSame('IF NOT EXISTS `lion_database`', $this->getQuery());
    }

    public function testUse(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->use(DATABASE_NAME_MYSQL));
        $this->assertSame('USE `lion_database`', $this->getQuery());
    }

    public function testBegin(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->begin());
        $this->assertSame('BEGIN', $this->getQuery());
    }

    public function testEnd(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->end());
        $this->assertSame('END', $this->getQuery());
    }

    public function testCreate(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->create());
        $this->assertSame('CREATE', $this->getQuery());
    }

    public function testProcedure(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->procedure());
        $this->assertSame('PROCEDURE', $this->getQuery());
    }

    public function testStatus(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->status());
        $this->assertSame('STATUS', $this->getQuery());
    }

    #[DataProvider('closeQueryProvider')]
    public function testCloseQuery(string $close): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->closeQuery($close));
        $this->assertSame($close, $this->getQuery());
    }

    public function testFull(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->full());
        $this->assertSame('FULL', $this->getQuery());
    }

    public function testGroupQuery(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->groupQuery(fn () => $this->mysql->full()));
        $this->assertSame('( FULL )', $this->getQuery());
    }

    public function testRecursive(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->recursive(DATABASE_USER_MYSQL));
        $this->assertSame('RECURSIVE root AS', $this->getQuery());
    }

    public function testWith(): void
    {
        $with = $this->mysql->with(true);

        $this->assertIsString($with);
        $this->assertSame(' WITH', $with);
    }

    public function testWithNotString(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->with());
        $this->assertSame('WITH', $this->getQuery());
    }

    #[DataProvider('tableProvider')]
    public function testTable(bool $table, bool $withDatabase, string $return): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->table($table, $withDatabase));
        $this->assertSame($return, $this->getQuery());
    }

    #[DataProvider('tableIsStringProvider')]
    public function testTableIsString(string $table, bool $withDatabase, string $return): void
    {
        $this->mysql->run(CONNECTIONS_MYSQL);

        $this->assertInstanceOf(MySQL::class, $this->mysql->table($table, $withDatabase));
        $this->assertSame($return, $this->getPrivateProperty('table'));
    }

    #[DataProvider('viewProvider')]
    public function testView(bool $view, bool $withDatabase, string $return): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->view($view, $withDatabase));
        $this->assertSame($return, $this->getQuery());
    }

    #[DataProvider('viewIsStringProvider')]
    public function testViewIsString(string $view, bool $withDatabase, string $return): void
    {
        $this->mysql->run(CONNECTIONS_MYSQL);

        $this->assertInstanceOf(MySQL::class, $this->mysql->view($view, $withDatabase));
        $this->assertSame($return, $this->getPrivateProperty('view'));
    }

    public function testIsNull(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->isNull());
        $this->assertSame('IS NULL', $this->getQuery());
    }

    public function testIsNotNull(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->isNotNull());
        $this->assertSame('IS NOT NULL', $this->getQuery());
    }

    #[DataProvider('offsetProvider')]
    public function testOffset(int $increase): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->offset($increase));
        $this->assertSame('OFFSET ?', $this->getQuery());
        $this->assertAddRows([$increase]);
    }

    public function testUnionAll(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->unionAll());
        $this->assertSame('UNION ALL', $this->getQuery());
    }

    public function testUnion(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->union());
        $this->assertSame('UNION', $this->getQuery());
    }

    #[DataProvider('asProvider')]
    public function testAs(string $column, string $as, string $return): void
    {
        $as = $this->mysql->as($column, $as);

        $this->assertIsString($as);
        $this->assertSame($return, $as);
    }

    #[DataProvider('concatProvider')]
    public function testConcat(array $elements, string $return): void
    {
        $concat = $this->mysql->concat(...$elements);

        $this->assertIsString($concat);
        $this->assertSame($return, $concat);
    }

    public function testCreateTable(): void
    {
        $this->mysql->run(CONNECTIONS_MYSQL);

        $this->assertInstanceOf(MySQL::class, $this->mysql->table('users')->createTable());
        $this->assertSame('CREATE TABLE lion_database.users', $this->getQuery());
    }

    public function testShow(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->show());

        $fetchMode = $this->getPrivateProperty('fetchMode');

        $this->assertSame(PDO::FETCH_OBJ, $fetchMode[$this->actualCode]);
        $this->assertSame('SHOW', $this->getQuery());
    }

    public function testFrom(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->from('users'));
        $this->assertSame('FROM users', $this->getQuery());
    }

    #[DataProvider('fromWithFunctionsProvider')]
    public function testFromWithFunctions(string $callableFunction, string $value, string $return): void
    {
        $this->mysql->run(CONNECTIONS_MYSQL);

        $this->assertInstanceOf(MySQL::class, $this->mysql->$callableFunction($value)->from());
        $this->assertSame($return, $this->getQuery());
    }

    public function testIndex(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->index());
        $this->assertSame('INDEX', $this->getQuery());
    }

    public function testDrop(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->drop());
        $this->assertSame('DROP', $this->getQuery());
    }

    public function testConstraints(): void
    {
        $this->mysql->run(CONNECTIONS_MYSQL);

        $query = 'SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME ';

        $query .= 'FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=? AND TABLE_NAME=? ';

        $query .= 'AND REFERENCED_COLUMN_NAME IS NOT NULL';

        $this->assertInstanceOf(MySQL::class, $this->mysql->table('users')->constraints());
        $this->assertAddRows(explode('.', 'lion_database.users'));
        $this->assertSame($query, $this->getQuery());
    }

    public function testTables(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->tables());
        $this->assertSame('TABLES', $this->getQuery());
    }

    public function testColumns(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->columns());
        $this->assertSame('COLUMNS', $this->getQuery());
    }

    #[DataProvider('queryProvider')]
    public function testQuery(string $query): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->query($query));
        $this->assertMessage('Execution finished');
        $this->assertSame($query, $this->getQuery());
    }

    #[DataProvider('bulkProvider')]
    public function testBulk(bool $enable, string $table, array $columns, array $rows, string $return): void
    {
        $this->setPrivateProperty('isSchema', $enable);

        $this->mysql
            ->run(CONNECTIONS_MYSQL)
            ->enableInsert($enable)
            ->table($table)
            ->bulk($columns, $rows);

        $this->assertInstanceOf(MySQL::class, $this->mysql);
        $this->assertAddRows(array_merge(...$rows));
        $this->assertSame($return, $this->getQuery());
        $this->assertMessage('Execution finished');
    }

    #[DataProvider('inProvider')]
    public function testIn(?array $params, string $return): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->in($params));
        $this->assertSame($return, $this->getQuery());
    }

    public function testCall(): void
    {
        $this->mysql->run(CONNECTIONS_MYSQL);

        $this->assertInstanceOf(MySQL::class, $this->mysql->call('store_procedure', [1, 2, 3]));
        $this->assertAddRows([1, 2, 3]);
        $this->assertSame('CALL lion_database.store_procedure(?, ?, ?)', $this->getQuery());
        $this->assertMessage('Execution finished');
    }

    #[DataProvider('deleteProvider')]
    public function testDelete(string $table, string $return): void
    {
        $this->mysql->run(CONNECTIONS_MYSQL);

        $this->assertInstanceOf(MySQL::class, $this->mysql->table($table)->delete());
        $this->assertSame($return, $this->getQuery());
    }

    /**
     * @throws ReflectionException
     */
    #[DataProvider('updateProvider')]
    public function testUpdate(string $table, array $params, string $return): void
    {
        $this->mysql->run(CONNECTIONS_MYSQL);

        $this->assertInstanceOf(MySQL::class, $this->mysql->table($table)->update($params));
        $this->assertAddRows(array_values($params));
        $this->assertSame($return, $this->getQuery());
    }

    /**
     * @throws ReflectionException
     */
    #[DataProvider('insertProvider')]
    public function testInsert(string $table, array $params, string $return): void
    {
        $this->mysql->run(CONNECTIONS_MYSQL);

        $this->assertInstanceOf(MySQL::class, $this->mysql->table($table)->insert($params));
        $this->assertAddRows(array_values($params));
        $this->assertSame($return, $this->getQuery());
        $this->assertMessage('Execution finished');
    }

    public function testInsertIsSchema(): void
    {
        $this->mysql->run(CONNECTIONS_MYSQL);

        $sql = 'INSERT INTO lion_database.users (users_name, users_last_name) VALUES (_lion, _root)';

        $params = ['users_name' => '_lion', 'users_last_name' => '_root'];

        $mysql = $this->mysql->isSchema()->table('users')->insert($params);

        $this->assertInstanceOf(MySQL::class, $mysql);
        $this->assertAddRows(array_values($params));
        $this->assertSame($sql, $this->getQuery());
        $this->assertMessage('Execution finished');
        $this->assertTrue($this->getPrivateProperty('isSchema'));
    }

    public function testHaving(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->having());
        $this->assertSame('HAVING', $this->getQuery());
    }

    /**
     * @throws ReflectionException
     */
    #[DataProvider('selectProvider')]
    public function testSelect(string $function, string $value, array $columns, string $return): void
    {
        $this->mysql->run(CONNECTIONS_MYSQL);

        $this->assertInstanceOf(MySQL::class, $this->mysql->$function($value)->select(...$columns));

        $fetchMode = $this->getPrivateProperty('fetchMode');

        $this->assertSame(PDO::FETCH_OBJ, $fetchMode[$this->actualCode]);
        $this->assertSame($return, $this->getQuery());
    }

    #[DataProvider('selectDistinctProvider')]
    public function testSelectDistinct(string $function, string $value, array $columns, string $return): void
    {
        $this->mysql->run(CONNECTIONS_MYSQL);

        $this->assertInstanceOf(MySQL::class, $this->mysql->$function($value)->selectDistinct(...$columns));

        $fetchMode = $this->getPrivateProperty('fetchMode');

        $this->assertSame(PDO::FETCH_OBJ, $fetchMode[$this->actualCode]);
        $this->assertSame($return, $this->getQuery());
    }

    public function testBetween(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->between(1, 10));
        $this->assertAddRows([1, 10]);
        $this->assertSame('BETWEEN ? AND ?', $this->getQuery());
    }

    #[DataProvider('likeProvider')]
    public function testLike(string $like): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->like($like));
        $this->assertAddRows([$like]);
        $this->assertSame('LIKE ?', $this->getQuery());
    }

    #[DataProvider('groupByProvider')]
    public function testGroupBy(array $groupBy, string $return): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->groupBy(...$groupBy));
        $this->assertSame($return, $this->getQuery());
    }

    #[DataProvider('limitProvider')]
    public function testLimit(int $start, ?int $limit, array $add, string $return): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->limit($start, $limit));
        $this->assertAddRows($add);
        $this->assertSame($return, $this->getQuery());
    }

    public function testAsc(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->asc());
        $this->assertSame('ASC', $this->getQuery());
    }

    public function testAscIsString(): void
    {
        $asc = $this->mysql->asc(true);

        $this->assertIsString($asc);
        $this->assertSame(' ASC', $asc);
    }

    public function testDesc(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->desc());
        $this->assertSame('DESC', $this->getQuery());
    }

    public function testDescIsString(): void
    {
        $desc = $this->mysql->desc(true);

        $this->assertIsString($desc);
        $this->assertSame(' DESC', $desc);
    }

    #[DataProvider('orderByProvider')]
    public function testOrderBy(array $orderBy, string $return): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->orderBy(...$orderBy));
        $this->assertSame($return, $this->getQuery());
    }

    public function testInner(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->inner());
        $this->assertSame('INNER', $this->getQuery());
    }

    public function testLeft(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->left());
        $this->assertSame('LEFT', $this->getQuery());
    }

    public function testRight(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->right());
        $this->assertSame('RIGHT', $this->getQuery());
    }

    #[DataProvider('joinProvider')]
    public function testJoin(string $table, string $valueFrom, string $valueUpTo, bool $withAlias, string $return): void
    {
        $this->mysql->run(CONNECTIONS_MYSQL);

        $this->assertInstanceOf(MySQL::class, $this->mysql->join($table, $valueFrom, $valueUpTo, $withAlias));
        $this->assertSame($return, $this->getQuery());
    }

    public function testWhere(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->where()->equalTo('idusers', 1));
        $this->assertAddRows([1]);
        $this->assertSame('WHERE idusers = ?', $this->getQuery());
    }

    public function testWhereIsString(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->where('idusers'));
        $this->assertSame('WHERE idusers', $this->getQuery());
    }

    public function testWhereWithColumn(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->where(fn () => $this->mysql->column('idusers')));
        $this->assertSame('WHERE idusers', $this->getQuery());
    }

    public function testWhereWithCallback(): void
    {
        $query = $this->mysql->where(function (): void {
            $this->mysql
                ->equalTo('idusers', 1)
                ->and(function (): void {
                    $this->mysql->groupQuery(function (): void {
                        $this->mysql->notEqualTo('idusers', 2);
                    });
                });
        });

        $this->assertInstanceOf(MySQL::class, $query);
        $this->assertAddRows([1, 2]);
        $this->assertSame('WHERE idusers = ? AND ( idusers <> ? )', $this->getQuery());
    }

    public function testWhereIsBool(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->where(true));
        $this->assertSame('WHERE', $this->getQuery());
    }

    public function testAnd(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->and()->equalTo('idusers', 1));
        $this->assertAddRows([1]);
        $this->assertSame('AND idusers = ?', $this->getQuery());
    }

    public function testAndIsString(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->and('idusers'));
        $this->assertSame('AND idusers', $this->getQuery());
    }

    public function testAndWithColumn(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->and(fn () => $this->mysql->column('idusers')));
        $this->assertSame('AND idusers', $this->getQuery());
    }

    public function testAndWithCallback(): void
    {
        $query = $this->mysql->where(function (): void {
            $this->mysql
                ->notEqualTo('idusers', 1)
                ->and(function (): void {
                    $this->mysql->groupQuery(function (): void {
                        $this->mysql
                            ->notEqualTo('idusers', 2)
                            ->and(function (): void {
                                $this->mysql->notEqualTo('idusers', 3);
                            });
                    });
                });
        });

        $this->assertInstanceOf(MySQL::class, $query);
        $this->assertAddRows([1, 2, 3]);
        $this->assertSame('WHERE idusers <> ? AND ( idusers <> ? AND idusers <> ? )', $this->getQuery());
    }

    public function testAndIsBool(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->and(true));
        $this->assertSame('AND', $this->getQuery());
    }

    public function testOr(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->or()->equalTo('idusers', 1));
        $this->assertAddRows([1]);
        $this->assertSame('OR idusers = ?', $this->getQuery());
    }

    public function testOrIsString(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->or('idusers'));
        $this->assertSame('OR idusers', $this->getQuery());
    }

    public function testOrWithColumn(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->or(fn () => $this->mysql->column('idusers')));
        $this->assertSame('OR idusers', $this->getQuery());
    }

    public function testOrWithCallback(): void
    {
        $query = $this->mysql->where(function (): void {
            $this->mysql
                ->notEqualTo('idusers', 1)
                ->and(function (): void {
                    $this->mysql->groupQuery(function (): void {
                        $this->mysql
                            ->notEqualTo('idusers', 2)
                            ->or(function (): void {
                                $this->mysql->notEqualTo('idusers', 3);
                            });
                    });
                });
        });

        $this->assertInstanceOf(MySQL::class, $query);
        $this->assertAddRows([1, 2, 3]);
        $this->assertSame('WHERE idusers <> ? AND ( idusers <> ? OR idusers <> ? )', $this->getQuery());
    }

    public function testOrIsBool(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->or(true));
        $this->assertSame('OR', $this->getQuery());
    }

    #[DataProvider('getColumnProvider')]
    public function testGetColumn(string $column, string $table, string $return): void
    {
        $getColumn = $this->mysql->getColumn($column, $table);

        $this->assertIsString($getColumn);
        $this->assertSame($return, $getColumn);
    }

    #[DataProvider('columnProvider')]
    public function testColumn(string $column, string $table, string $return): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->column($column, $table));
        $this->assertSame($return, $this->getQuery());
    }

    public function testEqualTo(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->equalTo('idusers', 1));
        $this->assertAddRows([1]);
        $this->assertSame('idusers = ?', $this->getQuery());
    }

    #[DataProvider('equalToSchemaProvider')]
    public function testEqualToSchema(string $column, string $value, string $return): void
    {
        $this->setPrivateProperty('isSchema', true);

        $this->setPrivateProperty('enableInsert', true);

        $this->assertTrue($this->getPrivateProperty('isSchema'));
        $this->assertTrue($this->getPrivateProperty('enableInsert'));
        $this->assertInstanceOf(MySQL::class, $this->mysql->equalTo($column, $value));
        $this->assertSame($return, $this->getQuery());
    }

    public function testNotEqualTo(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->notEqualTo('idusers', 1));
        $this->assertAddRows([1]);
        $this->assertSame('idusers <> ?', $this->getQuery());
    }

    #[DataProvider('notEqualToSchemaProvider')]
    public function testNotEqualToSchema(string $column, string $value, string $return): void
    {
        $this->setPrivateProperty('isSchema', true);

        $this->setPrivateProperty('enableInsert', true);

        $this->assertTrue($this->getPrivateProperty('isSchema'));
        $this->assertTrue($this->getPrivateProperty('enableInsert'));
        $this->assertInstanceOf(MySQL::class, $this->mysql->notEqualTo($column, $value));
        $this->assertSame($return, $this->getQuery());
    }

    public function testGreaterThan(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->greaterThan('idusers', 1));
        $this->assertAddRows([1]);
        $this->assertSame('idusers > ?', $this->getQuery());
    }

    #[DataProvider('greaterThanSchemaProvider')]
    public function testGreaterThanSchema(string $column, string $value, string $return): void
    {
        $this->setPrivateProperty('isSchema', true);

        $this->setPrivateProperty('enableInsert', true);

        $this->assertTrue($this->getPrivateProperty('isSchema'));
        $this->assertTrue($this->getPrivateProperty('enableInsert'));
        $this->assertInstanceOf(MySQL::class, $this->mysql->greaterThan($column, $value));
        $this->assertSame($return, $this->getQuery());
    }

    public function testLessThan(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->lessThan('idusers', 1));
        $this->assertAddRows([1]);
        $this->assertSame('idusers < ?', $this->getQuery());
    }

    #[DataProvider('lessThanSchemaProvider')]
    public function testLessThanSchema(string $column, string $value, string $return): void
    {
        $this->setPrivateProperty('isSchema', true);

        $this->setPrivateProperty('enableInsert', true);

        $this->assertTrue($this->getPrivateProperty('isSchema'));
        $this->assertTrue($this->getPrivateProperty('enableInsert'));
        $this->assertInstanceOf(MySQL::class, $this->mysql->lessThan($column, $value));
        $this->assertSame($return, $this->getQuery());
    }

    public function testGreaterThanOrEqualTo(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->greaterThanOrEqualTo('idusers', 1));
        $this->assertAddRows([1]);
        $this->assertSame('idusers >= ?', $this->getQuery());
    }

    #[DataProvider('greaterThanOrEqualToSchemaProvider')]
    public function testGreaterThanOrEqualToSchema(string $column, string $value, string $return): void
    {
        $this->setPrivateProperty('isSchema', true);

        $this->setPrivateProperty('enableInsert', true);

        $this->assertTrue($this->getPrivateProperty('isSchema'));
        $this->assertTrue($this->getPrivateProperty('enableInsert'));
        $this->assertInstanceOf(MySQL::class, $this->mysql->greaterThanOrEqualTo($column, $value));
        $this->assertSame($return, $this->getQuery());
    }

    public function testLessThanOrEqualTo(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->lessThanOrEqualTo('idusers', 1));
        $this->assertAddRows([1]);
        $this->assertSame('idusers <= ?', $this->getQuery());
    }

    #[DataProvider('lessThanOrEqualToSchemaProvider')]
    public function testLessThanOrEqualToSchema(string $column, string $value, string $return): void
    {
        $this->setPrivateProperty('isSchema', true);

        $this->setPrivateProperty('enableInsert', true);

        $this->assertTrue($this->getPrivateProperty('isSchema'));
        $this->assertTrue($this->getPrivateProperty('enableInsert'));
        $this->assertInstanceOf(MySQL::class, $this->mysql->lessThanOrEqualTo($column, $value));
        $this->assertSame($return, $this->getQuery());
    }

    public function testMin(): void
    {
        $min = $this->mysql->min('price');

        $this->assertIsString($min);
        $this->assertSame('MIN(price)', $min);
    }

    public function testMax(): void
    {
        $max = $this->mysql->max('price');

        $this->assertIsString($max);
        $this->assertSame('MAX(price)', $max);
    }

    public function testAvg(): void
    {
        $avg = $this->mysql->avg('price');

        $this->assertIsString($avg);
        $this->assertSame('AVG(price)', $avg);
    }

    public function testSum(): void
    {
        $sum = $this->mysql->sum('price');

        $this->assertIsString($sum);
        $this->assertSame('SUM(price)', $sum);
    }

    public function testCount(): void
    {
        $count = $this->mysql->count('*');

        $this->assertIsString($count);
        $this->assertSame('COUNT(*)', $count);
    }

    public function testDay(): void
    {
        $day = $this->mysql->day('2023-12-22');

        $this->assertIsString($day);
        $this->assertSame('DAY(2023-12-22)', $day);
    }

    public function testMonth(): void
    {
        $month = $this->mysql->month('2023-12-22');

        $this->assertIsString($month);
        $this->assertSame('MONTH(2023-12-22)', $month);
    }

    public function testYear(): void
    {
        $year = $this->mysql->year('2023-12-22');

        $this->assertIsString($year);
        $this->assertSame('YEAR(2023-12-22)', $year);
    }

    #[DataProvider('intProvider')]
    public function testInt(string $column, ?int $length, string $query): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->int($column, $length));
        $this->assertSame($query, $this->getQuery());
    }

    #[DataProvider('bigIntProvider')]
    public function testBigInt(string $column, ?int $length, string $query): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->bigInt($column, $length));
        $this->assertSame($query, $this->getQuery());
    }

    public function testDecimal(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->decimal('idusers'));
        $this->assertSame('idusers DECIMAL', $this->getQuery());
    }

    public function testDouble(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->double('idusers'));
        $this->assertSame('idusers DOUBLE', $this->getQuery());
    }

    public function testFloat(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->float('idusers'));
        $this->assertSame('idusers FLOAT', $this->getQuery());
    }

    public function testMediumInt(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->mediumInt('idusers', 11));
        $this->assertSame('idusers MEDIUMINT(11)', $this->getQuery());
    }

    public function testReal(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->real('idusers'));
        $this->assertSame('idusers REAL', $this->getQuery());
    }

    public function testSmallInt(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->smallInt('idusers', 11));
        $this->assertSame('idusers SMALLINT(11)', $this->getQuery());
    }

    public function testTinyInt(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->tinyInt('idusers', 11));
        $this->assertSame('idusers TINYINT(11)', $this->getQuery());
    }

    public function testBlob(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->blob('idusers'));
        $this->assertSame('idusers BLOB', $this->getQuery());
    }

    #[DataProvider('varBinaryProvider')]
    public function testVarBinary(string $name, string|int $length, string $return): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->varBinary($name, $length));
        $this->assertSame($return, $this->getQuery());
    }

    public function testChar(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->char('idusers', 11));
        $this->assertSame('idusers CHAR(11)', $this->getQuery());
    }

    public function testJson(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->json('idusers'));
        $this->assertSame('idusers JSON', $this->getQuery());
    }

    public function testNChar(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->nchar('idusers', 11));
        $this->assertSame('idusers NCHAR(11)', $this->getQuery());
    }

    public function testNVarchar(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->nvarchar('idusers', 11));
        $this->assertSame('idusers NVARCHAR(11)', $this->getQuery());
    }

    public function testVarchar(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->varchar('idusers', 11));
        $this->assertSame('idusers VARCHAR(11)', $this->getQuery());
    }

    public function testLongText(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->longText('idusers'));
        $this->assertSame('idusers LONGTEXT', $this->getQuery());
    }

    public function testMediumText(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->mediumText('idusers'));
        $this->assertSame('idusers MEDIUMTEXT', $this->getQuery());
    }

    public function testText(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->text('idusers', 11));
        $this->assertSame('idusers TEXT(11)', $this->getQuery());
    }

    public function testTinyText(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->tinyText('idusers'));
        $this->assertSame('idusers TINYTEXT', $this->getQuery());
    }

    public function testEnum(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->enum('idusers', [1, 2, 3]));
        $this->assertSame("idusers ENUM('1', '2', '3')", $this->getQuery());
    }

    public function testDate(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->date('idusers'));
        $this->assertSame('idusers DATE', $this->getQuery());
    }

    public function testTime(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->time('idusers'));
        $this->assertSame('idusers TIME', $this->getQuery());
    }

    public function testTimeStamp(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->timeStamp('idusers'));
        $this->assertSame('idusers TIMESTAMP', $this->getQuery());
    }

    public function testDateTime(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->dateTime('idusers'));
        $this->assertSame('idusers DATETIME', $this->getQuery());
    }
}

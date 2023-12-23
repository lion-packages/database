<?php

declare(strict_types=1);

namespace Tests\Drivers;

use LionDatabase\Drivers\MySQL;
use LionTest\Test;
use PDO;
use Tests\Provider\MySQLProviderTrait;

class MySQLTest extends Test
{
    use MySQLProviderTrait;

    const DATABASE_TYPE = 'mysql';
    const DATABASE_HOST = 'db';
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
    const ADMINISTRATOR = 'administrator';

    private MySQL $mysql;
    private string $actualCode;

    protected function setUp(): void
    {
        $this->mysql = new MySQL();

        $this->actualCode = uniqid();
        $this->initReflection($this->mysql);
        $this->setActualCode();
    }

    protected function tearDown(): void
    {
        $this->setPrivateProperty('sql', '');
        $this->setPrivateProperty('table', '');
        $this->setPrivateProperty('view', '');
        $this->setPrivateProperty('dataInfo', []);
        $this->setPrivateProperty('isSchema', false);
        $this->setPrivateProperty('enableInsert', false);
        $this->setPrivateProperty('actualCode', '');
        $this->setPrivateProperty('fetchMode', []);
        $this->setPrivateProperty('message', 'Execution finished');

        $this->mysql->drop()->table()->addQuery('roles')->execute();
    }

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

    private function assertMessage(string $message): void
    {
        $this->assertSame($message, $this->getPrivateProperty('message'));
    }

    private function assertAddRows(mixed $assertValue): void
    {
        $rows = $this->getPrivateProperty('dataInfo');

        $this->assertArrayHasKey($this->actualCode, $rows);
        $this->assertSame($assertValue, $rows[$this->actualCode]);
    }

    public function testExecute(): void
    {
        $createTableResponse = $this->mysql
            ->run(self::CONNECTIONS)->isSchema()->enableInsert(true)
            ->use(self::DATABASE_NAME)->closeQuery()
            ->drop()->table()->ifExists('roles')->closeQuery()
            ->create()->table()->addQuery('roles')
            ->groupQuery(function() {
                $this->mysql
                    ->int('idroles')->notNull()->autoIncrement()->closeQuery(',')
                    ->varchar('roles_name', 25)->notNull()->comment('roles name')->closeQuery(',')
                    ->primaryKey('idroles');
            })
            ->engine('INNODB')
            ->default()->character()->set(MySQL::UTF8MB4)
            ->collate(MySQL::UTF8MB4_SPANISH_CI)
            ->closeQuery()
            ->execute();

        $this->assertIsObject($createTableResponse);
        $this->assertObjectHasProperty('status', $createTableResponse);
        $this->assertObjectHasProperty('message', $createTableResponse);
        $this->assertSame('success', $createTableResponse->status);
        $this->assertSame('Execution finished', $createTableResponse->message);
    }

    public function testExecuteInsert(): void
    {
        $this->mysql
            ->run(self::CONNECTIONS)->isSchema()->enableInsert(true)
            ->use(self::DATABASE_NAME)->closeQuery()
            ->drop()->table()->ifExists('roles')->closeQuery()
            ->create()->table()->addQuery('roles')
            ->groupQuery(function() {
                $this->mysql
                    ->int('idroles')->notNull()->autoIncrement()->closeQuery(',')
                    ->varchar('roles_name', 25)->notNull()->comment('roles name')->closeQuery(',')
                    ->primaryKey('idroles');
            })
            ->engine('INNODB')
            ->default()->character()->set(MySQL::UTF8MB4)
            ->collate(MySQL::UTF8MB4_SPANISH_CI)
            ->closeQuery()
            ->execute();

        $response = $this->mysql->table('roles')->insert(['roles_name' => self::ADMINISTRATOR])->execute();

        $this->assertIsObject($response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertSame('success', $response->status);
        $this->assertSame('Rows inserted successfully', $response->message);
    }

    public function testGet(): void
    {
        $this->mysql
            ->run(self::CONNECTIONS)->isSchema()->enableInsert(true)
            ->use(self::DATABASE_NAME)->closeQuery()
            ->drop()->table()->ifExists('roles')->closeQuery()
            ->create()->table()->addQuery('roles')
            ->groupQuery(function() {
                $this->mysql
                    ->int('idroles')->notNull()->autoIncrement()->closeQuery(',')
                    ->varchar('roles_name', 25)->notNull()->comment('roles name')->closeQuery(',')
                    ->primaryKey('idroles');
            })
            ->engine('INNODB')
            ->default()->character()->set(MySQL::UTF8MB4)
            ->collate(MySQL::UTF8MB4_SPANISH_CI)
            ->closeQuery()
            ->execute();

        $this->mysql->table('roles')->insert(['roles_name' => self::ADMINISTRATOR])->execute();

        $validateRowResponse = $this->mysql
            ->table('roles')
            ->select('roles_name')
            ->where()
            ->equalTo('roles_name', self::ADMINISTRATOR)
            ->get();

        $this->assertIsObject($validateRowResponse);
        $this->assertObjectHasProperty('roles_name', $validateRowResponse);
        $this->assertSame(self::ADMINISTRATOR, $validateRowResponse->roles_name);
    }

    public function testGetAll(): void
    {
        $this->mysql
            ->run(self::CONNECTIONS)->isSchema()->enableInsert(true)
            ->use(self::DATABASE_NAME)->closeQuery()
            ->drop()->table()->ifExists('roles')->closeQuery()
            ->create()->table()->addQuery('roles')
            ->groupQuery(function() {
                $this->mysql
                    ->int('idroles')->notNull()->autoIncrement()->closeQuery(',')
                    ->varchar('roles_name', 25)->notNull()->comment('roles name')->closeQuery(',')
                    ->primaryKey('idroles');
            })
            ->engine('INNODB')
            ->default()->character()->set(MySQL::UTF8MB4)
            ->collate(MySQL::UTF8MB4_SPANISH_CI)
            ->closeQuery()
            ->execute();

        $this->mysql->table('roles')->bulk(['roles_name'], [
            [self::ADMINISTRATOR],
            [self::ADMINISTRATOR . '-2']
        ])->execute();

        $validateRowResponse = $this->mysql->table('roles')->select('roles_name')->getAll();

        $this->assertIsArray($validateRowResponse);

        foreach ($validateRowResponse as $row) {
            $this->assertObjectHasProperty('roles_name', $row);
            $this->assertContains($row->roles_name, [self::ADMINISTRATOR, (self::ADMINISTRATOR . '-2')]);
        }
    }

    public function testRun(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->run(self::CONNECTIONS));
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

    /**
     * @dataProvider transactionProvider
     * */
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

    /**
     * @dataProvider enableInsertProvider
     * */
    public function testEnableInsert(bool $enable): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->enableInsert($enable));
        $this->assertSame($enable, $this->getPrivateProperty('enableInsert'));
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

    public function testOnUpdate(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->onUpdate());
        $this->assertSame('ON UPDATE', $this->getQuery());
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
        -$this->assertInstanceOf(MySQL::class, $this->mysql->constraint());
        $this->assertSame('CONSTRAINT', $this->getQuery());
    }

    public function testAdd(): void
    {
        -$this->assertInstanceOf(MySQL::class, $this->mysql->add());
        $this->assertSame('ADD', $this->getQuery());
    }

    public function testAlter(): void
    {
        -$this->assertInstanceOf(MySQL::class, $this->mysql->alter());
        $this->assertSame('ALTER', $this->getQuery());
    }

    /**
     * @dataProvider commentProvider
     * */
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

    /**
     * @dataProvider engineProvider
     * */
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

    /**
     * @dataProvider collateProvider
     * */
    public function testCollate(string $value, string $return): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->collate($value));
        $this->assertSame($return, $this->getQuery());
    }

    /**
     * @dataProvider setProvider
     * */
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

    /**
     * @dataProvider defaultProvider
     * */
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
        $this->assertInstanceOf(MySQL::class, $this->mysql->addQuery(self::DATABASE_NAME));
        $this->assertSame(self::DATABASE_NAME, $this->getQuery());
    }

    public function testIfExist(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->ifExists(self::DATABASE_NAME));
        $this->assertSame('IF EXISTS `lion_database`', $this->getQuery());
    }

    public function testUse(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->use(self::DATABASE_NAME));
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

    /**
     * @dataProvider closeQueryProvider
     * */
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
        $this->assertInstanceOf(MySQL::class, $this->mysql->groupQuery(fn() => $this->mysql->full()));
        $this->assertSame('( FULL )', $this->getQuery());
    }

    public function testRecursive(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->recursive(self::DATABASE_USER));
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

    /**
     * @dataProvider tableProvider
     * */
    public function testTable(bool $table, bool $withDatabase, string $return): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->table($table, $withDatabase));
        $this->assertSame($return, $this->getQuery());
    }

    /**
     * @dataProvider tableIsStringProvider
     * */
    public function testTableIsString(string $table, bool $withDatabase, string $return): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->table($table, $withDatabase));
        $this->assertSame($return, $this->getPrivateProperty('table'));
    }

    /**
     * @dataProvider viewProvider
     * */
    public function testView(bool $view, bool $withDatabase, string $return): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->view($view, $withDatabase));
        $this->assertSame($return, $this->getQuery());
    }

    /**
     * @dataProvider viewIsStringProvider
     * */
    public function testViewIsString(string $view, bool $withDatabase, string $return): void
    {
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

    /**
     * @dataProvider offsetProvider
     * */
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

    /**
     * @dataProvider asProvider
     * */
    public function testAs(string $column, string $as, string $return): void
    {
        $as = $this->mysql->as($column, $as);

        $this->assertIsString($as);
        $this->assertSame($return, $as);
    }

    /**
     * @dataProvider concatProvider
     * */
    public function testConcat(array $elements, string $return): void
    {
        $concat = $this->mysql->concat(...$elements);

        $this->assertIsString($concat);
        $this->assertSame($return, $concat);
    }

    public function testCreateTable(): void
    {
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

    /**
     * @dataProvider fromWithFunctionsProvider
     * */
    public function testFromWithFunctions(string $callableFunction, string $value, string $return): void
    {
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

    /**
     * @dataProvider queryProvider
     * */
    public function testQuery(string $query): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->query($query));
        $this->assertMessage('Execution finished');
        $this->assertSame($query, $this->getQuery());
    }

    /**
     * @dataProvider bulkProvider
     * */
    public function testBulk(bool $enable, string $table, array $columns, array $rows, string $return): void
    {
        $this->setPrivateProperty('isSchema', $enable);
        $mysql = $this->mysql->enableInsert($enable)->table($table)->bulk($columns, $rows);

        $this->assertInstanceOf(MySQL::class, $mysql);
        $this->assertAddRows(array_merge(...$rows));
        $this->assertSame($return, $this->getQuery());
        $this->assertMessage('Rows inserted successfully');
    }

    /**
     * @dataProvider inProvider
     * */
    public function testIn(?array $params, string $return): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->in($params));
        $this->assertSame($return, $this->getQuery());

    }

    public function testCall(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->call('store_procedure', [1, 2, 3]));
        $this->assertAddRows([1, 2, 3]);
        $this->assertSame('CALL lion_database.store_procedure(?, ?, ?)', $this->getQuery());
        $this->assertMessage('Procedure executed successfully');
    }

    /**
     * @dataProvider deleteProvider
     * */
    public function testDelete(string $table, string $return): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->table($table)->delete());
        $this->assertSame($return, $this->getQuery());
        $this->assertMessage('Rows deleted successfully');
    }

    /**
     * @dataProvider updateProvider
     * */
    public function testUpdate(string $table, array $params, string $return): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->table($table)->update($params));
        $this->assertAddRows(array_values($params));
        $this->assertSame($return, $this->getQuery());
        $this->assertMessage('Rows updated successfully');
    }

    /**
     * @dataProvider insertProvider
     * */
    public function testInsert(string $table, array $params, string $return): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->table($table)->insert($params));
        $this->assertAddRows(array_values($params));
        $this->assertSame($return, $this->getQuery());
        $this->assertMessage('Rows inserted successfully');
    }

    public function testInsertIsSchema(): void
    {
        $sql = 'INSERT INTO lion_database.users (users_name, users_last_name) VALUES (_lion, _root)';
        $params = ['users_name' => '_lion', 'users_last_name' => '_root'];
        $mysql = $this->mysql->isSchema()->table('users')->insert($params);

        $this->assertInstanceOf(MySQL::class, $mysql);
        $this->assertAddRows(array_values($params));
        $this->assertSame($sql, $this->getQuery());
        $this->assertMessage('Rows inserted successfully');
        $this->assertTrue($this->getPrivateProperty('isSchema'));
    }

    /**
     * @dataProvider havingProvider
     * */
    public function testHaving(string $condition, int $value, string $return): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->having($condition, $value));
        $this->assertAddRows([$value]);
        $this->assertSame($return, $this->getQuery());
    }

    /**
     * @dataProvider selectProvider
     * */
    public function testSelect(string $function, string $value, array $columns, string $return): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->$function($value)->select(...$columns));

        $fetchMode = $this->getPrivateProperty('fetchMode');

        $this->assertSame(PDO::FETCH_OBJ, $fetchMode[$this->actualCode]);
        $this->assertSame($return, $this->getQuery());
    }

    /**
     * @dataProvider selectDistinctProvider
     * */
    public function testSelectDistinct(string $function, string $value, array $columns, string $return): void
    {
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

    /**
     * @dataProvider likeProvider
     * */
    public function testLike(string $like): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->like($like));
        $this->assertAddRows([$like]);
        $this->assertSame('LIKE ?', $this->getQuery());
    }

    /**
     * @dataProvider groupByProvider
     * */
    public function testGroupBy(array $groupBy, string $return): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->groupBy(...$groupBy));
        $this->assertSame($return, $this->getQuery());
    }

    /**
     * @dataProvider limitProvider
     * */
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

    /**
     * @dataProvider orderByProvider
     * */
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

    /**
     * @dataProvider joinProvider
     * */
    public function testJoin(string $table, string $valueFrom, string $valueUpTo, bool $withAlias, string $return): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->join($table, $valueFrom, $valueUpTo, $withAlias));
        $this->assertSame($return, $this->getQuery());
    }

    public function testWhere(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->where(fn() => $this->mysql->equalTo('idusers', 1)));
        $this->assertAddRows([1]);
        $this->assertSame('WHERE idusers = ?', $this->getQuery());
    }

    public function testWhereWithColumn(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->where(fn() => $this->mysql->column('idusers')));
        $this->assertSame('WHERE idusers', $this->getQuery());
    }

    public function testWhereWithCallback(): void
    {
        $query = $this->mysql->where(function() {
            $this->mysql
                ->equalTo('idusers', 1)
                ->and(function() {
                    $this->mysql->groupQuery(function() {
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
        $this->assertInstanceOf(MySQL::class, $this->mysql->and(fn() => $this->mysql->equalTo('idusers', 1)));
        $this->assertAddRows([1]);
        $this->assertSame('AND idusers = ?', $this->getQuery());
    }

    public function testAndWithColumn(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->and(fn() => $this->mysql->column('idusers')));
        $this->assertSame('AND idusers', $this->getQuery());
    }

    public function testAndWithCallback(): void
    {
        $query = $this->mysql->where(function() {
            $this->mysql
                ->notEqualTo('idusers', 1)
                ->and(function() {
                    $this->mysql->groupQuery(function() {
                        $this->mysql
                            ->notEqualTo('idusers', 2)
                            ->and(function() {
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
        $this->assertInstanceOf(MySQL::class, $this->mysql->or(fn() => $this->mysql->equalTo('idusers', 1)));
        $this->assertAddRows([1]);
        $this->assertSame('OR idusers = ?', $this->getQuery());
    }

    public function testOrWithColumn(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->or(fn() => $this->mysql->column('idusers')));
        $this->assertSame('OR idusers', $this->getQuery());
    }

    public function testOrWithCallback(): void
    {
        $query = $this->mysql->where(function() {
            $this->mysql
                ->notEqualTo('idusers', 1)
                ->and(function() {
                    $this->mysql->groupQuery(function() {
                        $this->mysql
                            ->notEqualTo('idusers', 2)
                            ->or(function() {
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

    /**
     * @dataProvider getColumnProvider
     * */
    public function testGetColumn(string $column, string $table, string $return): void
    {
        $getColumn = $this->mysql->getColumn($column, $table);

        $this->assertIsString($getColumn);
        $this->assertSame($return, $getColumn);
    }

    /**
     * @dataProvider columnProvider
     * */
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

    public function testNotEqualTo(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->notEqualTo('idusers', 1));
        $this->assertAddRows([1]);
        $this->assertSame('idusers <> ?', $this->getQuery());
    }

    public function testGreaterThan(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->greaterThan('idusers', 1));
        $this->assertAddRows([1]);
        $this->assertSame('idusers > ?', $this->getQuery());
    }

    public function testLessThan(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->lessThan('idusers', 1));
        $this->assertAddRows([1]);
        $this->assertSame('idusers < ?', $this->getQuery());
    }

    public function testGreaterThanOrEqualTo(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->greaterThanOrEqualTo('idusers', 1));
        $this->assertAddRows([1]);
        $this->assertSame('idusers >= ?', $this->getQuery());
    }

    public function testLessThanOrEqualTo(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->lessThanOrEqualTo('idusers', 1));
        $this->assertAddRows([1]);
        $this->assertSame('idusers <= ?', $this->getQuery());
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

    public function testInt(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->int('idusers', 11));
        $this->assertSame('idusers INT(11)', $this->getQuery());
    }

    public function testBigInt(): void
    {
        $this->assertInstanceOf(MySQL::class, $this->mysql->bigInt('idusers', 11));
        $this->assertSame('idusers BIGINT(11)', $this->getQuery());
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

    /**
     * @dataProvider varBinaryProvider
     * */
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

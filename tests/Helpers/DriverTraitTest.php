<?php

declare(strict_types=1);

namespace Tests\Helpers;

use LionDatabase\Helpers\DriverTrait;
use LionTest\Test;
use PDO;
use Tests\Provider\DriverTraitProviderTrait;

class DriverTraitTest extends Test
{
    use DriverTraitProviderTrait;

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
    const ROWS = [1, 2, 3, 4, 'root'];
    const ROWS_CLEAN_SETTINGS = [null, ' ', 1, 2, 3, '', 4, 'root'];
    const EMPTY_ARRAY = [];
    const EMPTY_STRING = '';
    const FALSE = false;
    const TRUE = true;
    const SCHEMA_OPTIONS = [
        'columns' => [],
        'indexes' => [],
        'foreign' => [
            'index' => [],
            'constraint' => []
        ]
    ];
    const INNODB = 'INNODB';
    const UTF8 = 'UTF8';
    const UTF8_SPANISH_CI = 'UTF8_SPANISH_CI';

    private object $customClass;
    private string $customCode;

    protected function setUp(): void
    {
        $this->customClass = new class {
            use DriverTrait;

            public static function testClean(): void
            {
                self::clean();
            }

            public static function testAddNewQueryList(array $queryList): void
            {
                self::addNewQueryList($queryList);
            }

            public static function testAddQueryList(array $queryList): void
            {
                self::addQueryList($queryList);
            }

            public static function testOpenGroup(): void
            {
                self::openGroup();
            }

            public static function testCloseGroup(): void
            {
                self::closeGroup();
            }

            public static function testCleanSettings(array $columns): array
            {
                return self::cleanSettings($columns);
            }
        };

        $this->initReflection($this->customClass);
        $this->customCode = uniqid();
        $this->setActualCode();
    }

    protected function tearDown(): void
    {
        $this->customClass::testClean();
    }

    private function setActualCode(): void
    {
        $this->setPrivateProperty('actualCode', $this->customCode);

        $this->assertSame($this->customCode, $this->getPrivateProperty('actualCode'));
    }

    private function addDefault(string $defaultConnection): void
    {
        $this->setPrivateProperty('connections', ['default' => $defaultConnection]);
    }

    public function testClean(): void
    {
        $this->addDefault(self::DATABASE_NAME);
        $this->customClass::addConnections(self::DATABASE_NAME, self::CONNECTION_DATA);
        $this->customClass::addConnections(self::DATABASE_NAME_SECOND, self::CONNECTION_DATA_SECOND);
        $this->customClass::testClean();

        $this->assertSame(self::EMPTY_ARRAY, $this->getPrivateProperty('listSql'));
        $this->assertSame(self::EMPTY_STRING, $this->getPrivateProperty('actualCode'));
        $this->assertSame(self::EMPTY_STRING, $this->getPrivateProperty('sql'));
        $this->assertSame(self::EMPTY_STRING, $this->getPrivateProperty('table'));
        $this->assertSame(self::EMPTY_STRING, $this->getPrivateProperty('view'));
        $this->assertSame(self::EMPTY_STRING, $this->getPrivateProperty('procedure'));
        $this->assertSame(self::EMPTY_STRING, $this->getPrivateProperty('schemaStr'));
        $this->assertSame(self::EMPTY_ARRAY, $this->getPrivateProperty('dataInfo'));
        $this->assertSame(self::DATABASE_NAME, $this->getPrivateProperty('activeConnection'));
        $this->assertSame(self::DATABASE_NAME, $this->getPrivateProperty('dbname'));
        $this->assertSame(self::EMPTY_ARRAY, $this->getPrivateProperty('fetchMode'));
        $this->assertSame(self::INNODB, $this->getPrivateProperty('engine'));
        $this->assertSame(self::UTF8, $this->getPrivateProperty('characterSet'));
        $this->assertSame(self::UTF8_SPANISH_CI, $this->getPrivateProperty('collate'));
        $this->assertSame(self::SCHEMA_OPTIONS, $this->getPrivateProperty('schemaOptions'));
        $this->assertSame(self::FALSE, $this->getPrivateProperty('isSchema'));
        $this->assertSame(self::FALSE, $this->getPrivateProperty('isCreateSchema'));
        $this->assertSame(self::FALSE, $this->getPrivateProperty('isCreateTable'));
        $this->assertSame(self::FALSE, $this->getPrivateProperty('isCreateView'));
        $this->assertSame(self::FALSE, $this->getPrivateProperty('isCreateProcedure'));
        $this->assertSame(self::FALSE, $this->getPrivateProperty('isTransaction'));
    }

    /**
     * @dataProvider addNewQueryListProvider
     * */
    public function testNewAddQueryList(array $queryList, string $return): void
    {
        $this->customClass::testAddQueryList($queryList);

        $this->assertSame($return, $this->getPrivateProperty('sql'));
    }

    /**
     * @dataProvider addNewQueryListProvider
     * */
    public function testAddQueryList(array $queryList, string $return): void
    {
        $this->customClass::testAddQueryList($queryList);

        $this->assertSame($return, $this->getPrivateProperty('sql'));
    }

    public function testAddConnections(): void
    {
        $this->addDefault(self::DATABASE_NAME_SECOND);
        $this->customClass::addConnections(self::DATABASE_NAME_SECOND, self::CONNECTION_DATA_SECOND);

        $connections = $this->getPrivateProperty('connections');

        $this->assertArrayHasKey('default', $connections);
        $this->assertSame(self::DATABASE_NAME_SECOND, $connections['default']);
        $this->assertArrayHasKey(self::DATABASE_NAME_SECOND, $connections['connections']);
        $this->assertSame($connections['connections'][self::DATABASE_NAME_SECOND], self::CONNECTION_DATA_SECOND);
    }

    public function testGetConnections(): void
    {
        $this->addDefault(self::DATABASE_NAME);
        $this->customClass::addConnections(self::DATABASE_NAME, self::CONNECTION_DATA);

        $connections = $this->getPrivateProperty('connections');

        $this->assertArrayHasKey('default', $connections);
        $this->assertSame(self::DATABASE_NAME, $connections['default']);
        $this->assertSame(self::CONNECTIONS, $this->customClass::getConnections());
    }

    public function testRemoveConnection(): void
    {
        $this->addDefault(self::DATABASE_NAME);
        $this->customClass::addConnections(self::DATABASE_NAME, self::CONNECTION_DATA);

        $this->assertSame(self::CONNECTIONS, $this->customClass::getConnections());
    }

    public function testOpenGroup(): void
    {
        $this->customClass::testOpenGroup();

        $this->assertSame(' (', $this->getPrivateProperty('sql'));
    }

    public function testCloseGroup(): void
    {
        $this->customClass::testCloseGroup();

        $this->assertSame(' )', $this->getPrivateProperty('sql'));
    }

    /**
     * @dataProvider fetchModeProvider
     * */
    public function testFetchMode(int $fetchMode): void
    {
        $this->assertInstanceOf($this->customClass::class, $this->customClass::fetchMode($fetchMode));

        $fetchModeList = $this->getPrivateProperty('fetchMode');

        $this->assertIsArray($fetchModeList);
        $this->assertArrayHasKey($this->customCode, $fetchModeList);
        $this->assertSame($fetchMode, $fetchModeList[$this->customCode]);
    }

    public function testFetchModeWithValue(): void
    {
        $this->assertInstanceOf(
            $this->customClass::class,
            $this->customClass::fetchMode(PDO::FETCH_CLASS, $this->customClass)
        );

        $fetchModeList = $this->getPrivateProperty('fetchMode');

        $this->assertIsArray($fetchModeList);
        $this->assertArrayHasKey($this->customCode, $fetchModeList);
        $this->assertSame([PDO::FETCH_CLASS, $this->customClass], $fetchModeList[$this->customCode]);
    }

    public function testAddRows(): void
    {
        $this->customClass::addRows(self::ROWS);

        $this->assertSame([$this->customCode => self::ROWS], $this->getPrivateProperty('dataInfo'));
    }

    public function testCleanSettings(): void
    {
        $this->assertSame(self::ROWS, $this->customClass::testCleanSettings(self::ROWS_CLEAN_SETTINGS));
    }
}

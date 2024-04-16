<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Lion\Database\Helpers\DriverTrait;
use Lion\Test\Test;
use PDO;
use Tests\Provider\DriverTraitProviderTrait;

class DriverTraitTest extends Test
{
    use DriverTraitProviderTrait;

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
    const ROWS = [1, 2, 3, 4, 'root'];
    const ROWS_CLEAN_SETTINGS = [null, ' ', 1, 2, 3, '', 4, 'root'];
    const EMPTY_ARRAY = [];
    const EMPTY_STRING = '';
    const FALSE = false;
    const TRUE = true;

    private object $customClass;
    private string $customCode;

    protected function setUp(): void
    {
        $this->customClass = new class
        {
            use DriverTrait;
        };

        $this->initReflection($this->customClass);

        $this->customCode = uniqid();

        $this->setActualCode();
    }

    protected function tearDown(): void
    {
        $this->getPrivateMethod('clean');
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

        $this->getPrivateMethod('clean');

        $this->assertSame(self::EMPTY_ARRAY, $this->getPrivateProperty('listSql'));
        $this->assertSame(self::EMPTY_STRING, $this->getPrivateProperty('actualCode'));
        $this->assertSame(self::EMPTY_STRING, $this->getPrivateProperty('sql'));
        $this->assertSame(self::EMPTY_STRING, $this->getPrivateProperty('table'));
        $this->assertSame(self::EMPTY_STRING, $this->getPrivateProperty('view'));
        $this->assertSame(self::EMPTY_ARRAY, $this->getPrivateProperty('dataInfo'));
        $this->assertSame(self::DATABASE_NAME, $this->getPrivateProperty('activeConnection'));
        $this->assertSame(self::DATABASE_NAME, $this->getPrivateProperty('dbname'));
        $this->assertSame(self::EMPTY_ARRAY, $this->getPrivateProperty('fetchMode'));
        $this->assertSame(self::FALSE, $this->getPrivateProperty('isTransaction'));
        $this->assertSame(self::FALSE, $this->getPrivateProperty('isSchema'));
        $this->assertSame(self::FALSE, $this->getPrivateProperty('enableInsert'));
    }

    /**
     * @dataProvider addNewQueryListProvider
     * */
    public function testNewAddQueryList(array $queryList, string $return): void
    {
        $this->getPrivateMethod('addNewQueryList', [$queryList]);

        $this->assertSame($return, $this->getPrivateProperty('sql'));
    }

    /**
     * @dataProvider addNewQueryListProvider
     * */
    public function testAddQueryList(array $queryList, string $return): void
    {
        $this->getPrivateMethod('addQueryList', [$queryList]);

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
        $this->getPrivateMethod('openGroup');

        $this->assertSame(' (', $this->getPrivateProperty('sql'));
    }

    public function testCloseGroup(): void
    {
        $this->getPrivateMethod('closeGroup');

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
        $this->assertSame(self::ROWS, $this->getPrivateMethod('cleanSettings', [self::ROWS_CLEAN_SETTINGS]));
    }

    public function testBuildTable(): void
    {
        $this->setPrivateProperty('table', 'users');

        $return = $this->getPrivateMethod('buildTable');

        $this->assertInstanceOf($this->customClass::class, $return);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Lion\Database\Helpers\DriverTrait;
use Lion\Database\Helpers\KeywordsTrait;
use Lion\Test\Test;
use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionException;
use Tests\Provider\DriverTraitProviderTrait;

class DriverTraitTest extends Test
{
    use DriverTraitProviderTrait;

    private const string DATABASE_TYPE = 'mysql';
    private const string DATABASE_HOST = 'mysql';
    private const int DATABASE_PORT = 3306;
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
    private const array ROWS = [
        1,
        2,
        3,
        4,
        'root',
    ];
    private const array ROWS_CLEAN_SETTINGS = [
        null,
        ' ',
        1,
        2,
        3,
        '',
        4,
        'root',
    ];
    private const array EMPTY_ARRAY = [];
    private const string EMPTY_STRING = '';

    private object $customClass;
    private string $customCode;

    /**
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $this->customClass = new class
        {
            use DriverTrait;
            use KeywordsTrait;
        };

        $this->initReflection($this->customClass);

        $this->customCode = uniqid();

        $this->setActualCode();
    }

    /**
     * @throws ReflectionException
     */
    protected function tearDown(): void
    {
        $this->getPrivateMethod('clean');
    }

    /**
     * @throws ReflectionException
     */
    private function setActualCode(): void
    {
        $this->setPrivateProperty('actualCode', $this->customCode);

        $this->assertSame($this->customCode, $this->getPrivateProperty('actualCode'));
    }

    /**
     * @throws ReflectionException
     */
    public function testClean(): void
    {
        $this->setPrivateProperty('connections', [
            'default' => self::DATABASE_NAME,
            'connections' => [
                self::DATABASE_NAME => self::CONNECTION_DATA,
                self::DATABASE_NAME_SECOND => self::CONNECTION_DATA_SECOND,
            ],
        ]);

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
        $this->assertSame(false, $this->getPrivateProperty('isTransaction'));
        $this->assertSame(false, $this->getPrivateProperty('isSchema'));
        $this->assertSame(false, $this->getPrivateProperty('enableInsert'));
    }

    /**
     * @param array<int, string> $queryList
     *
     * @throws ReflectionException
     */
    #[DataProvider('addNewQueryListProvider')]
    public function testNewAddQueryList(array $queryList, string $return): void
    {
        $this->getPrivateMethod('addNewQueryList', [
            'queryList' => $queryList,
        ]);

        $this->assertSame($return, $this->getPrivateProperty('sql'));
    }

    /**
     * @param array<int, string> $queryList
     *
     * @throws ReflectionException
     */
    #[DataProvider('addNewQueryListProvider')]
    public function testAddQueryList(array $queryList, string $return): void
    {
        $this->getPrivateMethod('addQueryList', [
            'queryList' => $queryList,
        ]);

        $this->assertSame($return, $this->getPrivateProperty('sql'));
    }

    /**
     * @throws ReflectionException
     */
    public function testOpenGroup(): void
    {
        $this->getPrivateMethod('openGroup');

        $this->assertSame(' (', $this->getPrivateProperty('sql'));
    }

    /**
     * @throws ReflectionException
     */
    public function testCloseGroup(): void
    {
        $this->getPrivateMethod('closeGroup');

        $this->assertSame(' )', $this->getPrivateProperty('sql'));
    }

    /**
     * @throws ReflectionException
     */
    #[DataProvider('fetchModeProvider')]
    public function testFetchMode(int $fetchMode, ?string $value): void
    {
        /** @phpstan-ignore-next-line */
        $this->assertInstanceOf($this->customClass::class, $this->customClass::fetchMode($fetchMode));

        $fetchModeList = $this->getPrivateProperty('fetchMode');

        $this->assertIsArray($fetchModeList);
        $this->assertArrayHasKey($this->customCode, $fetchModeList);
        $this->assertSame($fetchMode, $fetchModeList[$this->customCode]);
    }

    /**
     * @throws ReflectionException
     */
    public function testFetchModeWithValue(): void
    {
        $this->assertInstanceOf(
            $this->customClass::class,
            /** @phpstan-ignore-next-line */
            $this->customClass::fetchMode(PDO::FETCH_CLASS, $this->customClass)
        );

        $fetchModeList = $this->getPrivateProperty('fetchMode');

        $this->assertIsArray($fetchModeList);
        $this->assertArrayHasKey($this->customCode, $fetchModeList);
        $this->assertSame([PDO::FETCH_CLASS, $this->customClass], $fetchModeList[$this->customCode]);
    }

    /**
     * @throws ReflectionException
     */
    public function testAddRows(): void
    {
        /** @phpstan-ignore-next-line */
        $this->customClass::addRows(self::ROWS);

        $this->assertSame([$this->customCode => self::ROWS], $this->getPrivateProperty('dataInfo'));
    }

    /**
     * @throws ReflectionException
     */
    public function testCleanSettings(): void
    {
        $this->assertSame(self::ROWS, $this->getPrivateMethod('cleanSettings', [
            'columns' => self::ROWS_CLEAN_SETTINGS,
        ]));
    }

    /**
     * @param array<string, array<string, array{
     *     primary: bool,
     *     auto-increment: bool,
     *     unique: bool,
     *     comment: bool,
     *     default: bool,
     *     null: bool,
     *     in: bool,
     *     type: string,
     *     column: string,
     *     foreign?: array{
     *         index: string,
     *         constraint: string
     *     },
     *     indexes?: array<int, string>,
     *     default-value?: string
     * }>> $row
     *
     * @throws ReflectionException
     */
    #[DataProvider('buildTable')]
    public function testBuildTable(string $table, string $actualColumn, array $row, string $return): void
    {
        $this->setPrivateProperty('table', $table);

        $this->setPrivateProperty('actualColumn', $actualColumn);

        $this->setPrivateProperty('columns', $row);

        $this->setPrivateProperty('sql', '(--REPLACE-PARAMS--); --REPLACE-INDEXES--');

        $this->assertInstanceOf($this->customClass::class, $this->getPrivateMethod('buildTable'));
        $this->assertSame($return, $this->getPrivateProperty('sql'));
    }
}

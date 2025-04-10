<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Lion\Database\Helpers\StringFactory;
use Lion\Test\Test;
use PDO;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Tests\Provider\StringFactoryProviderTrait;

class StringFactoryTest extends Test
{
    use StringFactoryProviderTrait;

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

    private StringFactory $stringFactory;

    /**
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $this->stringFactory = new StringFactory();

        $this->initReflection($this->stringFactory);
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
    #[Testing]
    #[DataProvider('addCharacterBulkProvider')]
    public function addCharacterBulk(bool $isSchema, bool $enableInsert, bool $addQuotes, string $return): void
    {
        $this->setPrivateProperty('isSchema', $isSchema);

        $this->setPrivateProperty('enableInsert', $enableInsert);

        $str = $this->getPrivateMethod('addCharacterBulk', [
            'rows' => self::BULK_ROWS,
            'addQuotes' => $addQuotes,
        ]);

        $this->assertIsString($str);
        $this->assertSame($return, $str);
    }

    /**
     * @param array<string, int|string> $columns
     *
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('addCharacterEqualToProvider')]
    public function addCharacterEqualTo(array $columns, string $return, bool $isSchema, bool $enableInsert): void
    {
        $this->setPrivateProperty('isSchema', $isSchema);

        $this->setPrivateProperty('enableInsert', $enableInsert);

        $this->assertSame($isSchema, $this->getPrivateProperty('isSchema'));
        $this->assertSame($enableInsert, $this->getPrivateProperty('enableInsert'));

        $str = $this->getPrivateMethod('addCharacterEqualTo', [
            'columns' => $columns,
        ]);

        $this->assertIsString($str);
        $this->assertSame($return, $str);
    }

    /**
     * @param array<string, int|string> $columns
     *
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('addCharacterAssocProvider')]
    public function addCharacterAssoc(array $columns, string $return): void
    {
        $str = $this->getPrivateMethod('addCharacterAssoc', [
            'rows' => $columns,
        ]);

        $this->assertIsString($str);
        $this->assertSame($return, $str);
    }

    /**
     * @param array<int, int|string> $columns
     *
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('addCharacterProvider')]
    public function addCharacter(array $columns, string $return): void
    {
        $str = $this->getPrivateMethod('addCharacter', [
            'rows' => $columns,
        ]);

        $this->assertIsString($str);
        $this->assertSame($return, $str);
    }

    /**
     * @param array<int, string> $columns
     *
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('addColumnsProvider')]
    public function addColumns(
        bool $isSchema,
        bool $enableInsert,
        array $columns,
        bool $spacing,
        bool $addQuotes,
        string $return
    ): void {
        $this->setPrivateProperty('isSchema', $isSchema);

        $this->setPrivateProperty('enableInsert', $enableInsert);

        $str = $this->getPrivateMethod('addColumns', [
            'columns' => $columns,
            'spacing' => $spacing,
            'addQuotes' => $addQuotes,
        ]);

        $this->assertIsString($str);
        $this->assertSame($return, $str);
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
        $actualCode = uniqid('code-');

        $this->setPrivateProperty('actualCode', $actualCode);

        $this->assertInstanceOf($this->stringFactory::class, $this->stringFactory::fetchMode($fetchMode));

        $fetchModeList = $this->getPrivateProperty('fetchMode');

        $this->assertIsArray($fetchModeList);
        $this->assertArrayHasKey($actualCode, $fetchModeList);
        $this->assertSame($fetchMode, $fetchModeList[$actualCode]);
    }

    /**
     * @throws ReflectionException
     */
    public function testFetchModeWithValue(): void
    {
        $actualCode = uniqid('code-');

        $this->setPrivateProperty('actualCode', $actualCode);

        $this->assertInstanceOf(
            $this->stringFactory::class,
            $this->stringFactory::fetchMode(PDO::FETCH_CLASS, $this->stringFactory)
        );

        $fetchModeList = $this->getPrivateProperty('fetchMode');

        $this->assertIsArray($fetchModeList);
        $this->assertArrayHasKey($actualCode, $fetchModeList);
        $this->assertSame([PDO::FETCH_CLASS, $this->stringFactory], $fetchModeList[$actualCode]);
    }

    /**
     * @throws ReflectionException
     */
    public function testAddRows(): void
    {
        $actualCode = uniqid('code-');

        $this->setPrivateProperty('actualCode', $actualCode);

        $this->stringFactory::addRows(self::ROWS);

        $this->assertSame([$actualCode => self::ROWS], $this->getPrivateProperty('dataInfo'));
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

        $this->assertInstanceOf($this->stringFactory::class, $this->getPrivateMethod('buildTable'));
        $this->assertSame($return, $this->getPrivateProperty('sql'));
    }

    #[DataProvider('getKeyProvider')]
    public function testGetKey(string $type, string $key, ?string $return): void
    {
        $this->assertSame($return, $this->stringFactory::getKey($type, $key));
    }
}

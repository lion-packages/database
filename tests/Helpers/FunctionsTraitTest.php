<?php

declare(strict_types=1);

namespace Tests\Helpers;

use LionDatabase\Helpers\FunctionsTrait;
use LionTest\Test;
use Tests\Provider\FunctionsTraitProviderTrait;

class FunctionsTraitTest extends Test
{
    use FunctionsTraitProviderTrait;

    private object $customClass;

    protected function setUp(): void
    {
        $this->customClass = new class {
            use FunctionsTrait;

            public static function testAddCharacterBulk(array $rows, bool $addQuotes = false): string
            {
                return self::addCharacterBulk($rows, $addQuotes);
            }

            public static function testAddCharacterEqualTo(array $rows): string
            {
                return self::addCharacterEqualTo($rows);
            }

            public static function testAddCharacterAssoc(array $rows): string
            {
                return self::addCharacterAssoc($rows);
            }

            public static function testAddCharacter(array $rows): string
            {
                return self::addCharacter($rows);
            }

            public static function testAddColumns(array $columns, bool $spacing, bool $addQuotes): string
            {
                return self::addColumns($columns, $spacing, $addQuotes);
            }

            public static function testAddEnumColumns(array $columns, bool $spacing): string
            {
                return self::addEnumColumns($columns, $spacing);
            }
        };

        $this->initReflection($this->customClass);
    }

    protected function tearDown(): void
    {
        $this->setPrivateProperty('isSchema', false);
        $this->setPrivateProperty('enableInsert', false);
    }

    /**
     * @dataProvider addCharacterBulkProvider
     * */
    public function testAddCharacterBulk(bool $isSchema, bool $enableInsert, bool $addQuotes, string $return): void
    {
        $this->setPrivateProperty('isSchema', $isSchema);
        $this->setPrivateProperty('enableInsert', $enableInsert);
        $str = $this->customClass->testAddCharacterBulk(self::BULK_ROWS, $addQuotes);

        $this->assertIsString($str);
        $this->assertSame($return, $str);
    }

    /**
     * @dataProvider addCharacterEqualToProvider
     * */
    public function testAddCharacterEqualTo(array $columns, string $return): void
    {
        $str = $this->customClass->testAddCharacterEqualTo($columns);

        $this->assertIsString($str);
        $this->assertSame($return, $str);
    }

    /**
     * @dataProvider addCharacterAssocProvider
     * */
    public function testAddCharacterAssoc(array $rows, string $return): void
    {
        $str = $this->customClass->testAddCharacterAssoc($rows);

        $this->assertIsString($str);
        $this->assertSame($return, $str);
    }

    /**
     * @dataProvider addCharacterProvider
     * */
    public function testAddCharacter(array $rows, string $return): void
    {
        $str = $this->customClass->testAddCharacter($rows);

        $this->assertIsString($str);
        $this->assertSame($return, $str);
    }

    /**
     * @dataProvider addColumnsProvider
     * */
    public function testAddColumns(
        bool $isSchema,
        bool $enableInsert,
        array $columns,
        bool $spacing,
        bool $addQuotes,
        string $return
    ): void
    {
        $this->setPrivateProperty('isSchema', $isSchema);
        $this->setPrivateProperty('enableInsert', $enableInsert);
        $str = $this->customClass->testAddColumns($columns, $spacing, $addQuotes);

        $this->assertIsString($str);
        $this->assertSame($return, $str);
    }

    /**
     * @dataProvider addEnumColumnsProvider
     * */
    public function testAddEnumColumns(array $columns, bool $spacing, string $return): void
    {
        $str = $this->customClass->testAddEnumColumns($columns, $spacing);

        $this->assertIsString($str);
        $this->assertSame($return, $str);
    }
}

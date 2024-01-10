<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Lion\Database\Helpers\FunctionsTrait;
use Lion\Test\Test;
use Tests\Provider\FunctionsTraitProviderTrait;

class FunctionsTraitTest extends Test
{
    use FunctionsTraitProviderTrait;

    private object $customClass;

    protected function setUp(): void
    {
        $this->customClass = new class {
            use FunctionsTrait;
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
        $str = $this->getPrivateMethod('addCharacterBulk', [self::BULK_ROWS, $addQuotes]);

        $this->assertIsString($str);
        $this->assertSame($return, $str);
    }

    /**
     * @dataProvider addCharacterEqualToProvider
     * */
    public function testAddCharacterEqualTo(array $columns, string $return): void
    {
        $str = $this->getPrivateMethod('addCharacterEqualTo', [$columns]);

        $this->assertIsString($str);
        $this->assertSame($return, $str);
    }

    /**
     * @dataProvider addCharacterAssocProvider
     * */
    public function testAddCharacterAssoc(array $rows, string $return): void
    {
        $str = $this->getPrivateMethod('addCharacterAssoc', [$rows]);

        $this->assertIsString($str);
        $this->assertSame($return, $str);
    }

    /**
     * @dataProvider addCharacterProvider
     * */
    public function testAddCharacter(array $rows, string $return): void
    {
        $str = $this->getPrivateMethod('addCharacter', [$rows]);

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
    ): void {
        $this->setPrivateProperty('isSchema', $isSchema);
        $this->setPrivateProperty('enableInsert', $enableInsert);
        $str = $this->getPrivateMethod('addColumns', [$columns, $spacing, $addQuotes]);

        $this->assertIsString($str);
        $this->assertSame($return, $str);
    }

    /**
     * @dataProvider addEnumColumnsProvider
     * */
    public function testAddEnumColumns(array $columns, bool $spacing, string $return): void
    {
        $str = $this->getPrivateMethod('addEnumColumns', [$columns, $spacing]);

        $this->assertIsString($str);
        $this->assertSame($return, $str);
    }
}

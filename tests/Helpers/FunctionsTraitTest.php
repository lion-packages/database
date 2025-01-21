<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Lion\Database\Helpers\FunctionsTrait;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Provider\FunctionsTraitProviderTrait;

class FunctionsTraitTest extends Test
{
    use FunctionsTraitProviderTrait;

    private object $customClass;

    protected function setUp(): void
    {
        $this->customClass = new class
        {
            use FunctionsTrait;
        };

        $this->initReflection($this->customClass);
    }

    protected function tearDown(): void
    {
        $this->setPrivateProperty('isSchema', false);

        $this->setPrivateProperty('enableInsert', false);
    }

    #[DataProvider('addCharacterBulkProvider')]
    public function testAddCharacterBulk(bool $isSchema, bool $enableInsert, bool $addQuotes, string $return): void
    {
        $this->setPrivateProperty('isSchema', $isSchema);

        $this->setPrivateProperty('enableInsert', $enableInsert);

        $str = $this->getPrivateMethod('addCharacterBulk', [self::BULK_ROWS, $addQuotes]);

        $this->assertIsString($str);
        $this->assertSame($return, $str);
    }

    #[DataProvider('addCharacterEqualToProvider')]
    public function testAddCharacterEqualTo(array $columns, string $return, bool $isSchema, bool $enableInsert): void
    {
        $this->setPrivateProperty('isSchema', $isSchema);

        $this->setPrivateProperty('enableInsert', $enableInsert);

        $this->assertSame($isSchema, $this->getPrivateProperty('isSchema'));
        $this->assertSame($enableInsert, $this->getPrivateProperty('enableInsert'));

        $str = $this->getPrivateMethod('addCharacterEqualTo', [$columns]);

        $this->assertIsString($str);
        $this->assertSame($return, $str);
    }

    #[DataProvider('addCharacterAssocProvider')]
    public function testAddCharacterAssoc(array $columns, string $return): void
    {
        $str = $this->getPrivateMethod('addCharacterAssoc', [$columns]);

        $this->assertIsString($str);
        $this->assertSame($return, $str);
    }

    #[DataProvider('addCharacterProvider')]
    public function testAddCharacter(array $columns, string $return): void
    {
        $str = $this->getPrivateMethod('addCharacter', [$columns]);

        $this->assertIsString($str);
        $this->assertSame($return, $str);
    }

    #[DataProvider('addColumnsProvider')]
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
}

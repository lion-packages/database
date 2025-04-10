<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Lion\Database\Helpers\StringFactory;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Tests\Provider\FunctionsTraitProviderTrait;

class StringFactoryTest extends Test
{
    use FunctionsTraitProviderTrait;

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
        $this->setPrivateProperty('isSchema', false);

        $this->setPrivateProperty('enableInsert', false);
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
}

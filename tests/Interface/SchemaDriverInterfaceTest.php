<?php

declare(strict_types=1);

namespace Tests\Interface;

use Lion\Database\Interface\SchemaDriverInterface;
use Lion\Test\Test;
use Tests\Provider\CustomClassProvider;

class SchemaDriverInterfaceTest extends Test
{
    private object $customClass;

    protected function setUp(): void
    {
        $this->customClass = new CustomClassProvider();

        $this->initReflection($this->customClass);
    }

    protected function tearDown(): void
    {
        $this->setPrivateProperty('isSchema', false);
        $this->setPrivateProperty('enableInsert', false);
    }

    public function testIsSchema(): void
    {
        $run = $this->customClass->isSchema(true);

        $this->assertInstanceOf(SchemaDriverInterface::class, $run);
        $this->assertInstanceOf($this->customClass::class, $run);
        $this->assertTrue($this->getPrivateProperty('isSchema'));
    }

    public function testEnableInsert(): void
    {
        $run = $this->customClass->enableInsert(true);

        $this->assertInstanceOf(SchemaDriverInterface::class, $run);
        $this->assertInstanceOf($this->customClass::class, $run);
        $this->assertTrue($this->getPrivateProperty('enableInsert'));
    }
}

<?php

declare(strict_types=1);

namespace Tests\Interface;

use Lion\Test\Test;
use stdClass;
use Tests\Provider\CustomClassProvider;

class ReadDatabaseDataInterfaceTest extends Test
{
    private CustomClassProvider $customClass;

    protected function setUp(): void
    {
        $this->customClass = new CustomClassProvider();

        $this->initReflection($this->customClass);
    }

    public function testGet(): void
    {
        $response = $this->customClass->get();

        $this->assertInstanceOf(stdClass::class, $response);
    }

    public function testGetAll(): void
    {
        $response = $this->customClass->getAll();

        $this->assertIsArray($response);
        $this->assertContainsOnly(stdClass::class, $response);
    }
}

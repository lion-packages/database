<?php

declare(strict_types=1);

namespace Tests\Interface;

use Lion\Database\Interface\TransactionInterface;
use Lion\Test\Test;
use Tests\Provider\CustomClassProvider;

class TransactionInterfaceTest extends Test
{
    private object $customClass;

    protected function setUp(): void
    {
        $this->customClass = new CustomClassProvider();

        $this->initReflection($this->customClass);
    }

    protected function tearDown(): void
    {
        $this->setPrivateProperty('isTransaction', false);
    }

    public function testTransaction(): void
    {
        $run = $this->customClass->transaction(true);

        $this->assertInstanceOf(TransactionInterface::class, $run);
        $this->assertInstanceOf($this->customClass::class, $run);
        $this->assertTrue($this->getPrivateProperty('isTransaction'));
    }
}

<?php

declare(strict_types=1);

namespace Tests\Interface;

use Lion\Test\Test;
use Tests\Provider\CustomClassProvider;

class RunDatabaseProcessesInterfaceTest extends Test
{
    private object $customClass;

    protected function setUp(): void
    {
        $this->customClass = new CustomClassProvider();

        $this->initReflection($this->customClass);
    }

    public function testExecute(): void
    {
        $response = $this->customClass->execute();

        $this->assertIsObject($response);
        $this->assertObjectHasProperty('status', $response);
        $this->assertObjectHasProperty('message', $response);
        $this->assertSame('success', $response->status);
        $this->assertSame('Execution finished', $response->message);
    }
}

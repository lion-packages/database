<?php

declare(strict_types=1);

namespace Tests\Helpers\Constants;

use Lion\Database\Helpers\Constants\MySQLConstants;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;

class MySQLConstantsTest extends Test
{
    #[Testing]
    public function construct(): void
    {
        $this->assertInstanceOf(MySQLConstants::class, new MySQLConstants());
    }
}

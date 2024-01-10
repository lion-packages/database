<?php

declare(strict_types=1);

namespace Tests\Helpers\Constants;

use Lion\Database\Helpers\Constants\MySQLConstants;
use Lion\Test\Test;

class MySQLConstantsTest extends Test
{
    public function testKeywords(): void
    {
        $this->assertIsArray(MySQLConstants::KEYWORDS);
    }
}

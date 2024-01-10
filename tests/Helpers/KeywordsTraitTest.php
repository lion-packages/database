<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Lion\Database\Helpers\KeywordsTrait;
use Lion\Test\Test;
use Tests\Provider\KeywordsTraitProviderTrait;

class KeywordsTraitTest extends Test
{
    use KeywordsTraitProviderTrait;

    private object $customClass;

    protected function setUp(): void
    {
        $this->customClass = new class {
            use KeywordsTrait;
        };
    }

    /**
     * @dataProvider getKeyProvider
     * */
    public function testGetKey(string $type, string $key, ?string $return): void
    {
        $this->assertSame($return, $this->customClass::getKey($type, $key));
    }
}

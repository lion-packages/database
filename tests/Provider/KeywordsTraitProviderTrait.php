<?php

declare(strict_types=1);

namespace Tests\Provider;

trait KeywordsTraitProviderTrait
{
    public static function getKeyProvider(): array
    {
        return [
            [
                'type' => 'mysql',
                'key' => 'charset',
                'return' => ' CHARSET'
            ],
            [
                'type' => 'mysql',
                'key' => 'status',
                'return' => ' STATUS'
            ],
            [
                'type' => 'mysql',
                'key' => 'month',
                'return' => ' MONTH(?)'
            ],
            [
                'type' => 'mysql',
                'key' => 'order-by',
                'return' => ' ORDER BY'
            ],
            [
                'type' => 'mysql',
                'key' => 'primary-key',
                'return' => ' PRIMARY KEY (?)'
            ],
            [
                'type' => 'mysql',
                'key' => 'testing',
                'return' => null
            ]
        ];
    }
}

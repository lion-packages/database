<?php

declare(strict_types=1);

namespace Tests\Provider;

use Lion\Database\Driver;

trait KeywordsTraitProviderTrait
{
    /**
     * @return array<int, array{
     *     type: string,
     *     key: string,
     *     return: string|null
     * }>
     */
    public static function getKeyProvider(): array
    {
        return [
            [
                'type' => Driver::MYSQL,
                'key' => 'charset',
                'return' => ' CHARSET',
            ],
            [
                'type' => Driver::MYSQL,
                'key' => 'status',
                'return' => ' STATUS',
            ],
            [
                'type' => Driver::MYSQL,
                'key' => 'month',
                'return' => ' MONTH(?)',
            ],
            [
                'type' => Driver::MYSQL,
                'key' => 'order-by',
                'return' => ' ORDER BY',
            ],
            [
                'type' => Driver::MYSQL,
                'key' => 'primary-key',
                'return' => ' PRIMARY KEY (?)',
            ],
            [
                'type' => Driver::MYSQL,
                'key' => 'testing',
                'return' => null,
            ],
        ];
    }
}

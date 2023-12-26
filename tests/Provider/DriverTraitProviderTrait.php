<?php

declare(strict_types=1);

namespace Tests\Provider;

use PDO;

trait DriverTraitProviderTrait
{
    public static function addNewQueryListProvider(): array
    {
        return [
            [
                'queryList' => [' SELECT *', ' FROM', ' ', 'testing'],
                'return' => ' SELECT * FROM testing'
            ],
            [
                'queryList' => [' UPDATE', ' ', 'testing', ' SET'],
                'return' => ' UPDATE testing SET'
            ],
            [
                'queryList' => [' RECURSIVE', ' ', 'testing', ' AS'],
                'return' => ' RECURSIVE testing AS'
            ]
        ];
    }

    public static function fetchModeProvider(): array
    {
        return [
            [
                'fetchMode' => PDO::FETCH_ASSOC,
                'value' => null
            ],
            [
                'fetchMode' => PDO::FETCH_BOTH,
                'value' => null
            ],
            [
                'fetchMode' => PDO::FETCH_OBJ,
                'value' => null
            ],
            [
                'fetchMode' => PDO::FETCH_CLASS,
                'value' => 'App\\Http\\Controllers\\HomeController'
            ]
        ];
    }
}

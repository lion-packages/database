<?php

declare(strict_types=1);

namespace Lion\Database\Traits;

use stdClass;

/**
 * Declare the rowCount method of the interface
 *
 * @package Lion\Database\Traits
 */
trait GetQueryStringInterfaceTrait
{
    /**
     * {@inheritDoc}
     */
    public static function getQueryString(): stdClass
    {
        $query = trim(self::$sql);

        $split = explode(';', trim(self::$sql));

        $newListSql = array_map(fn ($value) => trim($value), array_filter($split, fn ($value) => trim($value) != ''));

        self::$sql = '';

        self::$listSql = [];

        return (object) [
            'code' => 200,
            'status' => 'success',
            'message' => 'SQL query generated successfully',
            'data' => (object) [
                'query' => $query,
                'split' => $newListSql,
            ],
        ];
    }
}

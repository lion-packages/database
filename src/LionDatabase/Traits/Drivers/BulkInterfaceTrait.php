<?php

declare(strict_types=1);

namespace Lion\Database\Traits\Drivers;

use Lion\Database\Driver;

/**
 * Declare the bulk method of the interface
 *
 * @package Lion\Database\Traits\Drivers
 */
trait BulkInterfaceTrait
{
    /**
     * {@inheritDoc}
     */
    public static function bulk(array $columns, array $rows): static
    {
        if (empty(self::$actualCode)) {
            self::$actualCode = uniqid('code-');
        }

        /** @var array<int, mixed> $row */
        foreach ($rows as $row) {
            self::addRows($row);
        }

        self::addQueryList([
            self::getKey(Driver::MYSQL, 'insert'),
            self::getKey(Driver::MYSQL, 'into'),
            ' ',
            self::$table,
            ' (',
            self::addColumns($columns),
            ')',
            self::getKey(Driver::MYSQL, 'values'),
            ' ',
            self::addCharacterBulk($rows, (self::$isSchema && self::$enableInsert))
        ]);

        return new static();
    }
}

<?php

declare(strict_types=1);

namespace Lion\Database\Traits\Drivers;

use Lion\Database\Driver;

/**
 * Declare the insert method of the interface
 *
 * @package Lion\Database\Traits\Drivers
 */
trait InsertInterfaceTrait
{
    /**
     * {@inheritDoc}
     */
    public static function insert(array $rows): static
    {
        if (empty(self::$actualCode)) {
            self::$actualCode = uniqid('code-');
        }

        self::addRows($rows);

        if (!self::$isSchema) {
            $columns = self::addCharacterAssoc($rows);
        } else {
            $columns = self::addColumns(
                array_values($rows),
                true,
                !(self::$isSchema && self::$enableInsert && self::$isProcedure)
            );
        }

        self::addQueryList([
            self::getKey(Driver::MYSQL, 'insert'),
            self::getKey(Driver::MYSQL, 'into'),
            ' ',
            self::$table,
            ' (',
            self::addColumns(array_keys($rows)),
            ')',
            self::getKey(Driver::MYSQL, 'values'),
            ' (',
            $columns,
            ')'
        ]);

        return new static();
    }
}

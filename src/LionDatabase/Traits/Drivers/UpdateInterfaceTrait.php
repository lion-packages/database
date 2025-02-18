<?php

declare(strict_types=1);

namespace Lion\Database\Traits\Drivers;

use Lion\Database\Driver;

/**
 * Declare the update method of the interface
 *
 * @package Lion\Database\Traits\Drivers
 */
trait UpdateInterfaceTrait
{
    /**
     * {@inheritDoc}
     */
    public static function update(array $rows): static
    {
        if (empty(self::$actualCode)) {
            self::$actualCode = uniqid('code-');
        }

        self::addRows($rows);

        self::addQueryList([
            self::getKey(Driver::MYSQL, 'update'),
            ' ',
            self::$table,
            self::getKey(Driver::MYSQL, 'set'),
            ' ',
            self::addCharacterEqualTo($rows),
        ]);

        return new static();
    }
}

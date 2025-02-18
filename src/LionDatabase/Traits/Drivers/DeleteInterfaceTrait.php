<?php

declare(strict_types=1);

namespace Lion\Database\Traits\Drivers;

use Lion\Database\Driver;

/**
 * Declare the delete method of the interface
 *
 * @package Lion\Database\Traits\Drivers
 */
trait DeleteInterfaceTrait
{
    /**
     * {@inheritDoc}
     */
    public static function delete(): static
    {
        if (empty(self::$actualCode)) {
            self::$actualCode = uniqid('code-');
        }

        self::addQueryList([
            self::getKey(Driver::MYSQL, 'delete'),
            self::getKey(Driver::MYSQL, 'from'),
            ' ',
            self::$table
        ]);

        return new static();
    }
}

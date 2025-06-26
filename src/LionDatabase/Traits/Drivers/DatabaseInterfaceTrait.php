<?php

declare(strict_types=1);

namespace Lion\Database\Traits\Drivers;

use Lion\Database\Driver;

/**
 * Declare the database method of the interface
 *
 * @package Lion\Database\Traits\Drivers
 */
trait DatabaseInterfaceTrait
{
    /**
     * {@inheritDoc}
     */
    public static function database(): self
    {
        self::addQueryList([
            self::getKey(Driver::MYSQL, 'database'),
        ]);

        return new self();
    }
}

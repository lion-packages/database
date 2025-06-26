<?php

declare(strict_types=1);

namespace Lion\Database\Traits\Drivers;

use Closure;
use Lion\Database\Driver;

/**
 * Declare the 'and' method of the interface
 *
 * @package Lion\Database\Traits\Drivers
 */
trait AndInterfaceTrait
{
    /**
     * {@inheritDoc}
     */
    public static function and(bool|Closure|string $and = true): self
    {
        $andString = self::getKey(Driver::MYSQL, 'and');

        if (is_callable($and)) {
            self::addQueryList([
                $andString,
            ]);

            $and();
        } elseif (is_string($and)) {
            self::addQueryList([
                $andString,
                " {$and}",
            ]);
        } elseif (is_bool($and) && $and) {
            self::addQueryList([
                $andString,
            ]);
        }

        return new self();
    }
}

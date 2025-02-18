<?php

declare(strict_types=1);

namespace Lion\Database\Traits\Drivers;

use Closure;
use Lion\Database\Driver;

/**
 * Declare the 'or' method of the interface
 *
 * @package Lion\Database\Traits\Drivers
 */
trait OrInterfaceTrait
{
    /**
     * {@inheritDoc}
     */
    public static function or(bool|Closure|string $or = true): static
    {
        $orString = self::getKey(Driver::MYSQL, 'or');

        if (is_callable($or)) {
            self::addQueryList([
                $orString,
            ]);

            $or();
        } elseif (is_string($or)) {
            self::addQueryList([
                $orString,
                " {$or}",
            ]);
        } elseif (is_bool($or) && $or) {
            self::addQueryList([
                $orString,
            ]);
        }

        return new static();
    }
}

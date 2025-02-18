<?php

declare(strict_types=1);

namespace Lion\Database\Traits\Drivers;

use Closure;
use Lion\Database\Driver;

/**
 * Declare the where method of the interface
 *
 * @package Lion\Database\Traits\Drivers
 */
trait WhereInterfaceTrait
{
    /**
     * {@inheritDoc}
     */
    public static function where(Closure|string|bool $where = true): static
    {
        $whereString = self::getKey(Driver::MYSQL, 'where');

        if (is_callable($where)) {
            self::addQueryList([
                $whereString,
            ]);

            $where();
        } elseif (is_string($where)) {
            self::addQueryList([
                $whereString,
                " {$where}",
            ]);
        } elseif (is_bool($where) && $where) {
            self::addQueryList([
                $whereString,
            ]);
        }

        return new static();
    }
}

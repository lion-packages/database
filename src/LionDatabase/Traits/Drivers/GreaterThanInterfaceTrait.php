<?php

declare(strict_types=1);

namespace Lion\Database\Traits\Drivers;

/**
 * Declare the greaterThan method of the interface
 *
 * @package Lion\Database\Traits\Drivers
 */
trait GreaterThanInterfaceTrait
{
    /**
     * {@inheritDoc}
     */
    public static function greaterThan(string $column, mixed $greaterThan): static
    {
        if (self::$isSchema && self::$enableInsert) {
            self::addQueryList([
                ' ',
                trim($column),
                ' > ',
                $greaterThan,
            ]);
        } else {
            self::addRows([
                $greaterThan,
            ]);

            self::addQueryList([
                ' ',
                trim($column . ' > ?'),
            ]);
        }

        return new static();
    }
}

<?php

declare(strict_types=1);

namespace Lion\Database\Traits\Drivers;

/**
 * Declare the lessThan method of the interface
 *
 * @package Lion\Database\Traits\Drivers
 */
trait LessThanInterfaceTrait
{
    /**
     * {@inheritDoc}
     */
    public static function lessThan(string $column, mixed $lessThan): static
    {
        if (self::$isSchema && self::$enableInsert) {
            self::addQueryList([
                ' ',
                trim($column),
                ' < ',
                $lessThan,
            ]);
        } else {
            self::addRows([
                $lessThan,
            ]);

            self::addQueryList([
                ' ',
                trim($column . ' < ?'),
            ]);
        }

        return new static();
    }
}
